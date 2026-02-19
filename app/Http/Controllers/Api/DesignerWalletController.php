<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerProfile;
use App\Models\DesignerWithdrawal;
use App\Models\DesignerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DesignerWalletController extends Controller
{
    /**
     * Get wallet balance and details
     */
    public function getWallet(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::with('wallet')->where('user_id', $user->id)->first();

        if (!$profile || !$profile->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found'
            ], 404);
        }

        $wallet = $profile->wallet;

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance,
                'total_earned' => $wallet->total_earned,
                'total_withdrawn' => $wallet->total_withdrawn,
                'pending_amount' => $wallet->pending_amount,
                'withdrawal_threshold' => $wallet->withdrawal_threshold,
                'can_withdraw' => $wallet->canWithdraw(),
            ]
        ]);
    }

    /**
     * Get transaction history
     */
    public function getTransactions(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        $perPage = $request->input('per_page', 20);
        $type = $request->input('type'); // credit or debit

        $query = DesignerTransaction::where('designer_id', $profile->id);

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions->map(function ($txn) {
                return [
                    'id' => $txn->id,
                    'type' => $txn->type,
                    'amount' => $txn->amount,
                    'balance_before' => $txn->balance_before,
                    'balance_after' => $txn->balance_after,
                    'transaction_type' => $txn->transaction_type,
                    'description' => $txn->description,
                    'created_at' => $txn->created_at,
                ];
            }),
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'total' => $transactions->total(),
                'per_page' => $transactions->perPage(),
            ]
        ]);
    }

    /**
     * Request withdrawal
     */
    public function requestWithdrawal(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::with('wallet')->where('user_id', $user->id)->first();

        if (!$profile || !$profile->wallet) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found'
            ], 404);
        }

        $wallet = $profile->wallet;

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:' . $wallet->withdrawal_threshold . '|max:' . $wallet->balance,
            'payment_method' => 'required|in:bank_transfer,upi',
            'bank_name' => 'required_if:payment_method,bank_transfer|string|max:100',
            'account_number' => 'required_if:payment_method,bank_transfer|string|max:50',
            'ifsc_code' => 'required_if:payment_method,bank_transfer|string|max:20',
            'account_holder_name' => 'required_if:payment_method,bank_transfer|string|max:100',
            'upi_id' => 'required_if:payment_method,upi|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!$wallet->canWithdraw()) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance. Minimum withdrawal amount is ₹' . $wallet->withdrawal_threshold
            ], 400);
        }

        if ($request->amount > $wallet->balance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create withdrawal request
            $withdrawal = DesignerWithdrawal::create([
                'designer_id' => $profile->id,
                'amount' => $request->amount,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'ifsc_code' => $request->ifsc_code,
                'account_holder_name' => $request->account_holder_name,
                'upi_id' => $request->upi_id,
            ]);

            // Deduct from balance and add to pending
            $wallet->balance -= $request->amount;
            $wallet->pending_amount += $request->amount;
            $wallet->save();

            // Create transaction record
            DesignerTransaction::create([
                'designer_id' => $profile->id,
                'type' => 'debit',
                'amount' => $request->amount,
                'balance_before' => $wallet->balance + $request->amount,
                'balance_after' => $wallet->balance,
                'transaction_type' => 'withdrawal',
                'description' => 'Withdrawal request #' . $withdrawal->id,
                'reference_id' => $withdrawal->id,
                'reference_type' => 'withdrawal',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully!',
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'new_balance' => $wallet->balance,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get withdrawal history
     */
    public function getWithdrawals(Request $request)
    {
        $user = Auth::user();
        $profile = DesignerProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Designer profile not found'
            ], 404);
        }

        $perPage = $request->input('per_page', 20);
        $status = $request->input('status');

        $query = DesignerWithdrawal::where('designer_id', $profile->id);

        if ($status) {
            $query->where('status', $status);
        }

        $withdrawals = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'payment_method' => $withdrawal->payment_method,
                    'transaction_reference' => $withdrawal->transaction_reference,
                    'admin_notes' => $withdrawal->admin_notes,
                    'rejection_reason' => $withdrawal->rejection_reason,
                    'requested_at' => $withdrawal->created_at,
                    'processed_at' => $withdrawal->processed_at,
                ];
            }),
            'pagination' => [
                'current_page' => $withdrawals->currentPage(),
                'total_pages' => $withdrawals->lastPage(),
                'total' => $withdrawals->total(),
                'per_page' => $withdrawals->perPage(),
            ]
        ]);
    }
}
