<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignerWithdrawal;
use App\Models\DesignerWallet;
use App\Models\DesignerTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AdminDesignerController extends Controller
{
    /**
     * Get all withdrawal requests
     */
    public function getWithdrawals(Request $request)
    {
        $status = $request->input('status', 'pending');
        $perPage = $request->input('per_page', 20);

        $withdrawals = DesignerWithdrawal::with(['designer.user', 'processor'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $withdrawals->map(function ($withdrawal) {
                return [
                    'id' => $withdrawal->id,
                    'designer_name' => $withdrawal->designer->display_name,
                    'designer_email' => $withdrawal->designer->user->email,
                    'amount' => $withdrawal->amount,
                    'status' => $withdrawal->status,
                    'payment_method' => $withdrawal->payment_method,
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'ifsc_code' => $withdrawal->ifsc_code,
                    'account_holder_name' => $withdrawal->account_holder_name,
                    'upi_id' => $withdrawal->upi_id,
                    'transaction_reference' => $withdrawal->transaction_reference,
                    'admin_notes' => $withdrawal->admin_notes,
                    'requested_at' => $withdrawal->created_at,
                    'processed_by' => $withdrawal->processor ? $withdrawal->processor->name : null,
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

    /**
     * Process withdrawal (approve and mark as completed)
     */
    public function processWithdrawal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_reference' => 'required|string|max:255',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $withdrawal = DesignerWithdrawal::with('designer.wallet')->find($id);

        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal request not found'
            ], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal already processed'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $wallet = $withdrawal->designer->wallet;

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'completed',
                'transaction_reference' => $request->transaction_reference,
                'admin_notes' => $request->admin_notes,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Update wallet
            $wallet->pending_amount -= $withdrawal->amount;
            $wallet->total_withdrawn += $withdrawal->amount;
            $wallet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal processed successfully!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject withdrawal
     */
    public function rejectWithdrawal(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $withdrawal = DesignerWithdrawal::with('designer.wallet')->find($id);

        if (!$withdrawal) {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal request not found'
            ], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Withdrawal already processed'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $wallet = $withdrawal->designer->wallet;

            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_by' => Auth::id(),
                'processed_at' => now(),
            ]);

            // Refund to wallet
            $wallet->balance += $withdrawal->amount;
            $wallet->pending_amount -= $withdrawal->amount;
            $wallet->save();

            // Create refund transaction
            DesignerTransaction::create([
                'designer_id' => $withdrawal->designer_id,
                'type' => 'credit',
                'amount' => $withdrawal->amount,
                'balance_before' => $wallet->balance - $withdrawal->amount,
                'balance_after' => $wallet->balance,
                'transaction_type' => 'withdrawal_refund',
                'description' => 'Withdrawal request #' . $withdrawal->id . ' rejected and refunded',
                'reference_id' => $withdrawal->id,
                'reference_type' => 'withdrawal',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal rejected and amount refunded to designer wallet'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject withdrawal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
