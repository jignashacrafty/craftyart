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
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-inactive {
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
        min-width: 150px;
    }
    
    .btn-info-action {
        background: #17a2b8;
        color: white;
    }
    
    .btn-info-action:hover {
        background: #138496;
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
    
    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.5;
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

        <div class="system-card">
            <div class="system-header">
                <h1 class="system-title">All Designers</h1>
                <p class="system-subtitle">View and manage all approved designers</p>
            </div>

            <div class="system-table-wrapper">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>DISPLAY NAME</th>
                            <th>EMAIL</th>
                            <th>COMMISSION RATE</th>
                            <th>TOTAL DESIGNS</th>
                            <th>LIVE DESIGNS</th>
                            <th>TOTAL EARNINGS</th>
                            <th>WALLET BALANCE</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designers as $designer)
                            <tr>
                                <td><strong>#{{ $designer->id }}</strong></td>
                                <td>{{ $designer->display_name }}</td>
                                <td>{{ $designer->user->email }}</td>
                                <td>{{ $designer->commission_rate }}%</td>
                                <td>{{ $designer->total_designs }}</td>
                                <td>{{ $designer->live_designs }}</td>
                                <td>₹{{ number_format($designer->total_earnings, 2) }}</td>
                                <td>₹{{ number_format($designer->wallet->balance ?? 0, 2) }}</td>
                                <td>
                                    @if($designer->is_active)
                                        <span class="status-badge status-active">Active</span>
                                    @else
                                        <span class="status-badge status-inactive">Inactive</span>
                                    @endif
                                </td>
                                <td class="actions-cell">
                                    <button type="button" class="btn-action btn-info-action" 
                                            data-toggle="modal" 
                                            data-target="#viewDesignerModal{{ $designer->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                </td>
                            </tr>

                            <!-- View Designer Modal -->
                            <div class="modal fade" id="viewDesignerModal{{ $designer->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Designer Profile - {{ $designer->display_name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6>Basic Information</h6>
                                                    <p><strong>Display Name:</strong> {{ $designer->display_name }}</p>
                                                    <p><strong>Email:</strong> {{ $designer->user->email }}</p>
                                                    <p><strong>User Name:</strong> {{ $designer->user->name }}</p>
                                                    <p><strong>Commission Rate:</strong> {{ $designer->commission_rate }}%</p>
                                                    <p><strong>Status:</strong> 
                                                        <span class="status-badge status-{{ $designer->is_active ? 'active' : 'inactive' }}">
                                                            {{ $designer->is_active ? 'Active' : 'Inactive' }}
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6>Statistics</h6>
                                                    <p><strong>Total Designs:</strong> {{ $designer->total_designs }}</p>
                                                    <p><strong>Approved Designs:</strong> {{ $designer->approved_designs }}</p>
                                                    <p><strong>Live Designs:</strong> {{ $designer->live_designs }}</p>
                                                    <p><strong>Total Earnings:</strong> ₹{{ number_format($designer->total_earnings, 2) }}</p>
                                                </div>
                                            </div>
                                            
                                            @if($designer->wallet)
                                            <hr>
                                            <h6>Wallet Information</h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Current Balance:</strong> ₹{{ number_format($designer->wallet->balance, 2) }}</p>
                                                    <p><strong>Total Earned:</strong> ₹{{ number_format($designer->wallet->total_earned, 2) }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Total Withdrawn:</strong> ₹{{ number_format($designer->wallet->total_withdrawn, 2) }}</p>
                                                    <p><strong>Pending Amount:</strong> ₹{{ number_format($designer->wallet->pending_amount, 2) }}</p>
                                                </div>
                                            </div>
                                            @endif
                                            
                                            <hr>
                                            <p><strong>Joined:</strong> {{ $designer->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="10">
                                    <div class="empty-state">
                                        <i class="fa fa-users"></i>
                                        <p>No designers found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($designers->hasPages())
            <div class="pagination-wrapper">
                {{ $designers->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

@include('layouts.footer')
