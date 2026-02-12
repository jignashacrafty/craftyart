<?php

namespace App\Http\Controllers;

use App\Models\PhonePeNotification;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PhonePeNotificationController extends Controller
{
    /**
     * Display notification list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $notifications = PhonePeNotification::with('transaction')
                ->orderBy('created_at', 'desc')
                ->get();
            
            return DataTables::of($notifications)
                ->addIndexColumn()
                ->addColumn('notification_info', function ($row) {
                    $html = '<div style="font-size: 12px;">';
                    $html .= '<div><strong>Type:</strong> ' . $row->notification_type . '</div>';
                    if ($row->event_type) {
                        $html .= '<div><strong>Event:</strong> ' . $row->event_type . '</div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('transaction_ids', function ($row) {
                    $html = '<div style="font-size: 11px;">';
                    $html .= '<div><code>' . $row->merchant_order_id . '</code></div>';
                    if ($row->phonepe_order_id) {
                        $html .= '<div><code>' . $row->phonepe_order_id . '</code></div>';
                    }
                    if ($row->phonepe_transaction_id) {
                        $html .= '<div><code>' . $row->phonepe_transaction_id . '</code></div>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('amount_display', function ($row) {
                    if ($row->amount) {
                        return '₹' . number_format($row->amount, 2);
                    }
                    return '-';
                })
                ->addColumn('status_badge', function ($row) {
                    if ($row->status) {
                        $colors = [
                            'SUCCESS' => 'success',
                            'COMPLETED' => 'success',
                            'FAILED' => 'danger',
                            'PENDING' => 'warning'
                        ];
                        $color = $colors[$row->status] ?? 'secondary';
                        return '<span class="badge badge-' . $color . '">' . $row->status . '</span>';
                    }
                    return '-';
                })
                ->addColumn('processed_badge', function ($row) {
                    if ($row->is_processed) {
                        return '<span class="badge badge-success">✅ Processed</span>';
                    }
                    return '<span class="badge badge-warning">⏳ Pending</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<div class="dropdown">
                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                            <i class="dw dw-more"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                            <a class="dropdown-item" href="' . route('phonepe.notifications.show', $row->id) . '"><i class="dw dw-eye"></i> View Details</a>';
                    
                    if ($row->transaction) {
                        $btn .= '<a class="dropdown-item" href="' . route('phonepe.transactions.show', $row->transaction->id) . '"><i class="dw dw-invoice"></i> View Transaction</a>';
                    }
                    
                    $btn .= '</div></div>';
                    return $btn;
                })
                ->rawColumns(['notification_info', 'transaction_ids', 'amount_display', 'status_badge', 'processed_badge', 'action'])
                ->make(true);
        }
        
        // Get all notifications for the view
        $notifications = PhonePeNotification::with('transaction')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('phonepe.notifications.index', compact('notifications'));
    }
    
    /**
     * Show notification details
     */
    public function show($id)
    {
        $notification = PhonePeNotification::with('transaction')->findOrFail($id);
        return view('phonepe.notifications.show', compact('notification'));
    }
}
