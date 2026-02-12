<?php

namespace App\Http\Controllers;

use App\Models\PhonePeTransaction;
use App\Models\PhonePeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PhonePeTransactionController extends Controller
{
    /**
     * Display transaction list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $transactions = PhonePeTransaction::with(['user', 'latestNotification'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return DataTables::of($transactions)
                ->addIndexColumn()
                ->addColumn('user_info', function ($row) {
                    if ($row->user) {
                        return '<div style="font-size: 12px;">
                            <div style="font-weight: 600;">' . $row->user->name . '</div>
                            <small style="color: #999;">' . $row->user->email . '</small>
                        </div>';
                    }
                    return '<span class="badge badge-secondary">N/A</span>';
                })
                ->addColumn('transaction_ids', function ($row) {
                    $html = '<div style="font-size: 12px;">';
                    $html .= '<div><strong>Merchant Order:</strong> <code>' . $row->merchant_order_id . '</code></div>';
                    if ($row->merchant_subscription_id) {
                        $html .= '<div><strong>Subscription:</strong> <code>' . $row->merchant_subscription_id . '</code></div>';
                    }
                    if ($row->phonepe_order_id) {
                        $html .= '<div><strong>PhonePe Order:</strong> <code>' . $row->phonepe_order_id . '</code></div>';
                    }
                    if ($row->phonepe_transaction_id) {
                        $html .= '<div><strong>Transaction:</strong> <code>' . $row->phonepe_transaction_id . '</code></div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('customer_info', function ($row) {
                    $html = '<div style="font-size: 12px;">';
                    if ($row->upi_id) {
                        $html .= '<div><i class="fa fa-mobile"></i> ' . $row->upi_id . '</div>';
                    }
                    if ($row->mobile) {
                        $html .= '<div><i class="fa fa-phone"></i> ' . $row->mobile . '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('amount_display', function ($row) {
                    return '<strong style="font-size: 14px;">₹' . number_format($row->amount, 2) . '</strong>';
                })
                ->addColumn('status_badge', function ($row) {
                    $colors = [
                        'PENDING' => 'warning',
                        'ACTIVE' => 'success',
                        'COMPLETED' => 'info',
                        'FAILED' => 'danger'
                    ];
                    $color = $colors[$row->status] ?? 'secondary';
                    return '<span class="badge badge-' . $color . '">' . $row->status . '</span>';
                })
                ->addColumn('autopay_info', function ($row) {
                    $html = '<div style="font-size: 12px;">';
                    if ($row->is_autopay_active) {
                        $html .= '<span class="badge badge-success">✅ Active</span><br>';
                    } else {
                        $html .= '<span class="badge badge-secondary">⏸️ Inactive</span><br>';
                    }
                    $html .= '<small>Count: ' . $row->autopay_count . 'x</small>';
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('notification_count', function ($row) {
                    $count = $row->notifications()->count();
                    if ($count > 0) {
                        return '<span class="badge badge-primary">' . $count . ' notifications</span>';
                    }
                    return '<span class="badge badge-secondary">No notifications</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="' . route('phonepe.transactions.show', $row->id) . '"><i class="dw dw-eye"></i> View Details</a>
                            <a class="dropdown-item" href="' . route('phonepe.transactions.notifications', $row->id) . '"><i class="dw dw-notification"></i> View Notifications</a>
                            <a class="dropdown-item check-status-btn" href="javascript:void(0)" data-id="' . $row->id . '" data-order="' . $row->merchant_order_id . '"><i class="dw dw-refresh"></i> Check Status</a>
                        </div>
                    </div>';
                    return $btn;
                })
                ->rawColumns(['user_info', 'transaction_ids', 'customer_info', 'amount_display', 'status_badge', 'autopay_info', 'notification_count', 'action'])
                ->make(true);
        }
        
        // Get all transactions for the view
        $transactions = PhonePeTransaction::with(['user', 'notifications'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('phonepe.transactions.index', compact('transactions'));
    }
    
    /**
     * Show transaction details
     */
    public function show($id)
    {
        $transaction = PhonePeTransaction::with(['user', 'notifications'])->findOrFail($id);
        return view('phonepe.transactions.show', compact('transaction'));
    }
    
    /**
     * Show transaction notifications
     */
    public function notifications($id)
    {
        $transaction = PhonePeTransaction::with(['user', 'notifications'])->findOrFail($id);
        return view('phonepe.transactions.notifications', compact('transaction'));
    }
    
    /**
     * Check and update transaction status from PhonePe API
     */
    public function checkStatus($id)
    {
        try {
            $transaction = PhonePeTransaction::findOrFail($id);
            
            // Use PhonePeAutoPayService to check status
            $service = new \App\Services\PhonePeAutoPayService();
            $result = $service->checkStatus($transaction->merchant_subscription_id);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'data' => $result['data'],
                    'transaction' => $transaction->fresh()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check status: ' . ($result['error'] ?? 'Unknown error')
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Transaction status check failed', [
                'transaction_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
