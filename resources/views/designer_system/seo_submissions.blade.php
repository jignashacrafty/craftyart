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
    
    .preview-thumb {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
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
    
    .status-live {
        background: #d4edda;
        color: #155724;
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
        min-width: 180px;
    }
    
    .btn-info-action {
        background: #17a2b8;
        color: white;
    }
    
    .btn-success-action {
        background: #28a745;
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

        <div class="system-card">
            <div class="system-header">
                <div>
                    <h1 class="system-title">SEO Submissions</h1>
                    <p class="system-subtitle">Review SEO details and publish designs</p>
                </div>
                <div class="filter-tabs">
                    <a href="{{ route('designer_system.seo_submissions', ['status' => 'pending_seo']) }}" 
                       class="filter-tab {{ $status == 'pending_seo' ? 'active' : '' }}">
                        Pending
                    </a>
                    <a href="{{ route('designer_system.seo_submissions', ['status' => 'live']) }}" 
                       class="filter-tab {{ $status == 'live' ? 'active' : '' }}">
                        Live
                    </a>
                </div>
            </div>

            <div class="system-table-wrapper">
                <table class="system-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>PREVIEW</th>
                            <th>TITLE</th>
                            <th>DESIGNER</th>
                            <th>CATEGORY</th>
                            <th>STATUS</th>
                            <th>SUBMITTED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($designs as $design)
                            <tr>
                                <td><strong>#{{ $design->id }}</strong></td>
                                <td>
                                    @if($design->preview_images && count($design->preview_images) > 0)
                                        <img src="{{ Storage::url($design->preview_images[0]) }}" class="preview-thumb">
                                    @else
                                        <span class="text-muted">No preview</span>
                                    @endif
                                </td>
                                <td>{{ $design->title }}</td>
                                <td>{{ $design->designer->display_name }}</td>
                                <td><span class="status-badge" style="background: #e3f2fd; color: #1565c0;">{{ ucfirst($design->category) }}</span></td>
                                <td>
                                    @if($design->status == 'pending_seo')
                                        <span class="status-badge status-pending">Pending SEO</span>
                                    @elseif($design->status == 'live')
                                        <span class="status-badge status-live">Live</span>
                                    @endif
                                </td>
                                <td>{{ $design->created_at->format('d M Y') }}</td>
                                <td class="actions-cell">
                                    <button type="button" class="btn-action btn-info-action" 
                                            data-toggle="modal" 
                                            data-target="#viewSeoModal{{ $design->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>
                                    
                                    @if($design->status == 'pending_seo')
                                        <button type="button" class="btn-action btn-success-action" 
                                                data-toggle="modal" 
                                                data-target="#publishModal{{ $design->id }}">
                                            <i class="fa fa-check"></i> Publish
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <i class="fa fa-search"></i>
                                        <p>No SEO submissions found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($designs->hasPages())
            <div class="pagination-wrapper">
                {{ $designs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
    console.log('🚀 Initializing Real-Time SEO Submissions System...');
    
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
            
            const channel = pusher.subscribe('seo-submissions');
            
            channel.bind('pusher:subscription_succeeded', function() {
                console.log('📡 Successfully subscribed to "seo-submissions" channel');
            });
            
            channel.bind('seo-status-changed', function(data) {
                console.log('🔄 SEO STATUS CHANGED:', data);
                if (data && data.should_remove) {
                    console.log('⚡ WebSocket: SEO status changed, reloading...');
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
