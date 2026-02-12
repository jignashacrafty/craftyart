<?php

namespace App\Http\Controllers;

use App\Models\PhonePeTransaction;
use App\Models\PhonePeNotification;
use Illuminate\Support\Facades\DB;

class PhonePeDashboardController extends Controller
{
    /**
     * PhonePe AutoPay Dashboard
     */
    public function index()
    {
        // Statistics
        $stats = [
            'total_requests' => PhonePeTransaction::count(),
            'pending_approvals' => PhonePeTransaction::where('status', 'PENDING')->count(),
            'active_subscriptions' => PhonePeTransaction::where('is_autopay_active', true)->count(),
            'total_amount' => PhonePeTransaction::sum('amount'),
            'total_autopay_payments' => PhonePeTransaction::sum('autopay_count'),
            'today_requests' => PhonePeTransaction::whereDate('created_at', today())->count(),
            'month_requests' => PhonePeTransaction::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'failed_requests' => PhonePeTransaction::where('status', 'FAILED')->count(),
        ];
        
        // Recent transactions (last 10)
        $recentTransactions = PhonePeTransaction::with(['user', 'latestNotification'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Status breakdown
        $statusBreakdown = PhonePeTransaction::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
        
        // Users waiting for approval
        $pendingUsers = PhonePeTransaction::with('user')
            ->where('status', 'PENDING')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Active AutoPay users
        $activeUsers = PhonePeTransaction::with('user')
            ->where('is_autopay_active', true)
            ->orderBy('last_autopay_at', 'desc')
            ->get();
        
        // Recent notifications
        $recentNotifications = PhonePeNotification::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('phonepe.dashboard', compact(
            'stats',
            'recentTransactions',
            'statusBreakdown',
            'pendingUsers',
            'activeUsers',
            'recentNotifications'
        ));
    }
}
