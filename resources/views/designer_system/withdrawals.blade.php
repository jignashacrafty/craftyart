@inject('roleManager', 'App\Http\Controllers\Utils\RoleManager')
@inject('contentManager', '\App\Http\Controllers\Utils\ContentManager')
@include('layouts.masterhead')

<style>
    .designer-system-container {
        background: #f8f9fa;
        min-height: 100vh;
        padding: 20px;
    }
    
    .system-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .system-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .system-title {
        font-size: 20px;
        font-weight: 600;
        color: #212529;
        margin: 0;
    }
    
    .system-subtitle {
        font-size: 14px;
        color: #6c757d;
        margin: 5px 0 0 0;
    }
    
    .filter-tabs {
        display: flex;
        gap: 8px;
    }
    
    .filter-tab {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #dee2e6;
        background: white;
        color: #6c757d;
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .filter-tab:hover {
        border-color: #007bff;
        color: #007bff;
        text-decoration: none;
    }
    
    .filter-tab.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .system-table-wrapper {
        overflow-x: auto;
    }
    
    .system-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .system-table thead th {
        background: #f8f9fa;
        color: #495057;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        padding: 15px 12px;
        border-bottom: 2px solid #dee2e6;
        white-space: nowrap;
    }
    
    .system-table tbody td {
        padding: 12px;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 14px;
        color: #495057;
    }
    
    .system-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-completed {
        background: #d4edda;
        color: #155724;
    }
    
    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }
    
    .btn-action {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 13px;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        margin: 2px;
        display: inline-block;
        white-space: nowrap;
    }
    
    .actions-cell {
        white-space: nowrap;
        min-width: 200px;
    }
    
    .btn-info-action {
        background: #17a2b8;
        color: white;
    }
    
    .btn-success-action {
        background: #28a745;
        color: white;
    }
    
    .btn-danger-action {
        background: #dc3545;
        color: white;
    }
    
    .pagination-wrapper {
        padding: 20px;
        background: white;
        border-top: 1px solid #e9ecef;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
</style>

<div class="main-container">
    <div class="designer-system-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        @endif

        <div class="system-card">
            <div class="system-header">
                <div>
                    <h1 class="system-title">Designer Withdrawals</h1>
                    <p class="system-subtitle">Process and manage withdrawal requests</p>
                </div>
                <div class="filter-tabs">
                    <a href="{{ route('designer_system.withdrawals', ['status' => 'pending']) }}" 
                       class="filter-tab {{ $status == 'pending' ? 'active' : '' }}">
                        Pending
                    </a>
                    <a href="{{ route('designer_system.withdrawals', ['status' => 'completed']) }}" 
                       class="filter-tab {{ $status == 'completed' ? 'active' : '' }}">
                        Completed
                    </a>
                    <a href="{{ route('designer_system.withdrawals', ['status' => 'rejected']) }}" 
                       class="filter-tab {{ $status == 'rejected' ? 'active' : '' }}">
                        Rejected
                    </a>
                </div>
            </div>

            <div class="system-table-wrapper">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DESIGNER</th>
                            <th>AMOUNT</th>
                            <th>PAYMENT METHOD</th>
                            <th>STATUS</th>
                            <th>REQUESTED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td><strong>#{{ $withdrawal->id }}</strong></td>
                                <td>
                                    {{ $withdrawal->designer->display_name }}<br>
                                    <small class="text-muted">{{ $withdrawal->designer->user->email }}</small>
                                </td>
                                <td><strong>₹{{ number_format($withdrawal->amount, 2) }}</strong></td>
                                <td>
                                    @if($withdrawal->payment_method == 'upi')
                                        <span class="status-badge" style="background: #e3f2fd; color: #1565c0;">UPI</span>
                                    @else
                                        <span class="status-badge" style="background: #f3e5f5; color: #6a1b9a;">Bank Transfer</span>
                                    @endif
                                </td>
                                <td>
                                    @if($withdrawal->status == 'pending')
                                        <span class="status-badge status-pending">Pending</span>
                                    @elseif($withdrawal->status == 'completed')
                                        <span class="status-badge status-completed">Completed</span>
                                    @else
                                        <span class="status-badge status-rejected">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                                <td class="actions-cell">
                                    <button type="button" class="btn-action btn-info-action" 
                                            data-toggle="modal" 
                                            data-target="#viewWithdrawalModal{{ $withdrawal->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    
                                    @if($withdrawal->status == 'pending')
                                        <button type="button" class="btn-action btn-success-action" 
                                                data-toggle="modal" 
                                                data-target="#processModal{{ $withdrawal->id }}">
                                            <i class="fa fa-check"></i> Process
                                        </button>
                                        <button type="button" class="btn-action btn-danger-action" 
                                                data-toggle="modal" 
                                                data-target="#rejectWithdrawalModal{{ $withdrawal->id }}">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fa fa-wallet"></i>
                                        <p>No withdrawal requests found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($withdrawals->hasPages())
            <div class="pagination-wrapper">
                {{ $withdrawals->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    console.log('🚀 Initializing Real-Time Withdrawals System...');
    
    let pusher = null;
    
    function initializeWebSocket() {
        try {
            console.log('🔌 Attempting WebSocket connection...');
            
            Pusher.logToConsole = true;
            
            pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
                wsHost: '{{ env("PUSHER_HOST", "127.0.0.1") }}',
                wsPort: {{ env('PUSHER_PORT', 6001) }},
                wssPort: {{ env('PUSHER_PORT', 6001) }},
                forceTLS: false,
                encrypted: false,
                enabledTransports: ['ws', 'wss'],
                cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
            });
            
            pusher.connection.bind('connected', function() {
                console.log('✅ WebSocket CONNECTED! Real-time updates enabled');
            });
            
            const channel = pusher.subscribe('designer-withdrawals');
            
            channel.bind('pusher:subscription_succeeded', function() {
                console.log('📡 Successfully subscribed to "designer-withdrawals" channel');
            });
            
            channel.bind('new-withdrawal', function(data) {
                console.log('🎯 NEW WITHDRAWAL EVENT:', data);
                if (data && data.id) {
                    console.log('⚡ WebSocket: New withdrawal received!', data);
                    location.reload();
                }
            });
            
            channel.bind('withdrawal-status-changed', function(data) {
                console.log('🔄 WITHDRAWAL STATUS CHANGED:', data);
                if (data && data.should_remove) {
                    console.log('⚡ WebSocket: Withdrawal status changed, reloading...');
                    location.reload();
                }
            });
            
        } catch (error) {
            console.error('❌ WebSocket initialization error:', error);
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeWebSocket();
    });
</script>

@include('layouts.footer')
