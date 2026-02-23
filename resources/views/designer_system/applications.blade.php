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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        position: sticky;
        top: 0;
        z-index: 10;
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

    .status-approved {
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

    .btn-info-action:hover {
        background: #138496;
    }

    .btn-success-action {
        background: #28a745;
        color: white;
    }

    .btn-success-action:hover {
        background: #218838;
    }

    .btn-danger-action {
        background: #dc3545;
        color: white;
    }

    .btn-danger-action:hover {
        background: #c82333;
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

    .badge {
        margin: 2px;
        padding: 6px 12px;
        font-size: 13px;
        font-weight: 500;
    }

    .gap-2 {
        gap: 8px;
    }
</style>

<div class="main-container">
    <div class="designer-system-container">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="system-card">
            <div class="system-header">
                <div>
                    <h1 class="system-title">Designer Applications</h1>
                    <p class="system-subtitle">Manage and review designer application submissions</p>
                </div>
                <div class="filter-tabs">
                    <a href="{{ route('designer_system.applications', ['status' => 'pending']) }}"
                        class="filter-tab {{ $status == 'pending' ? 'active' : '' }}">
                        Pending
                    </a>
                    <a href="{{ route('designer_system.applications', ['status' => 'approved']) }}"
                        class="filter-tab {{ $status == 'approved' ? 'active' : '' }}">
                        Approved
                    </a>
                    <a href="{{ route('designer_system.applications', ['status' => 'rejected']) }}"
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
                            <th>NAME</th>
                            <th>EMAIL</th>
                            <th>PHONE</th>
                            <th>CITY</th>
                            <th>EXPERIENCE</th>
                            <th>STATUS</th>
                            <th>SUBMITTED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $application)
                            <tr>
                                <td><strong>#{{ $application->id }}</strong></td>
                                <td>{{ $application->name }}</td>
                                <td>{{ $application->email }}</td>
                                <td>{{ $application->phone }}</td>
                                <td>{{ $application->city }}, {{ $application->state }}</td>
                                <td>{{ Str::limit($application->experience, 50) }}</td>
                                <td>
                                    @if($application->status == 'pending')
                                        <span class="status-badge status-pending">Pending</span>
                                    @elseif($application->status == 'approved')
                                        <span class="status-badge status-approved">Approved</span>
                                    @else
                                        <span class="status-badge status-rejected">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $application->created_at->format('d M Y') }}</td>
                                <td class="actions-cell">
                                    <button type="button" class="btn-action btn-info-action" data-toggle="modal"
                                        data-target="#viewModal{{ $application->id }}">
                                        <i class="fa fa-eye"></i> View
                                    </button>

                                    @if($application->status == 'pending')
                                        <button type="button" class="btn-action btn-success-action" data-toggle="modal"
                                            data-target="#approveModal{{ $application->id }}">
                                            <i class="fa fa-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn-action btn-danger-action" data-toggle="modal"
                                            data-target="#rejectModal{{ $application->id }}">
                                            <i class="fa fa-times"></i> Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- View Modal -->
                            <div class="modal fade" id="viewModal{{ $application->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Application Details - {{ $application->name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Name:</strong> {{ $application->name }}</p>
                                                    <p><strong>Email:</strong> {{ $application->email }}</p>
                                                    <p><strong>Phone:</strong> {{ $application->phone }}</p>
                                                    <p><strong>Address:</strong> {{ $application->address }}</p>
                                                    <p><strong>City:</strong> {{ $application->city }}</p>
                                                    <p><strong>State:</strong> {{ $application->state }}</p>
                                                    <p><strong>Country:</strong> {{ $application->country }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Experience:</strong><br>{{ $application->experience }}</p>
                                                    @if($application->experience_level)
                                                        <p><strong>Experience Level:</strong>
                                                            {{ ucfirst(str_replace('-', ' ', $application->experience_level)) }}
                                                        </p>
                                                    @endif
                                                    <p><strong>Skills:</strong><br>{{ $application->skills }}</p>
                                                    <p><strong>Status:</strong>
                                                        <span class="status-badge status-{{ $application->status }}">
                                                            {{ ucfirst($application->status) }}
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>

                                            @if($application->selected_types && is_array($application->selected_types) && count($application->selected_types) > 0)
                                                <hr>
                                                <h6>Selected Types (What they create):</h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($application->selected_types as $typeId)
                                                        @php
                                                            $type = \App\Models\DesignerType::find($typeId);
                                                        @endphp
                                                        @if($type)
                                                            <span class="badge badge-primary">{{ $type->name }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($application->selected_categories && is_array($application->selected_categories) && count($application->selected_categories) > 0)
                                                <hr>
                                                <h6>Selected Categories (Interests):</h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($application->selected_categories as $categoryId)
                                                        @php
                                                            $category = \App\Models\DesignerCategory::find($categoryId);
                                                        @endphp
                                                        @if($category)
                                                            <span class="badge badge-info">{{ $category->name }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($application->selected_goals && is_array($application->selected_goals) && count($application->selected_goals) > 0)
                                                <hr>
                                                <h6>Selected Goals (Motivations):</h6>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($application->selected_goals as $goalId)
                                                        @php
                                                            $goal = \App\Models\DesignerGoal::find($goalId);
                                                        @endphp
                                                        @if($goal)
                                                            <span class="badge badge-success">{{ $goal->name }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if($application->portfolio_links && is_array($application->portfolio_links) && count($application->portfolio_links) > 0)
                                                <hr>
                                                <h6>Portfolio Links:</h6>
                                                <ul>
                                                    @foreach($application->portfolio_links as $link)
                                                        <li><a href="{{ $link }}" target="_blank">{{ $link }}</a></li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            @if($application->status != 'pending')
                                                <hr>
                                                <div class="review-info">
                                                    <p><strong>Reviewed By:</strong>
                                                        {{ $application->reviewer ? $application->reviewer->name : 'N/A' }}</p>
                                                    <p><strong>Reviewed At:</strong>
                                                        {{ $application->reviewed_at ? $application->reviewed_at->format('d M Y, h:i A') : 'N/A' }}
                                                    </p>
                                                    @if($application->status == 'rejected' && $application->rejection_reason)
                                                        <div class="alert alert-danger mt-3">
                                                            <strong><i class="fa fa-exclamation-circle"></i> Rejection
                                                                Reason:</strong>
                                                            <p class="mb-0 mt-2">{{ $application->rejection_reason }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approve Modal -->
                            @if($application->status == 'pending')
                                <div class="modal fade" id="approveModal{{ $application->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST"
                                                action="{{ route('designer_system.application.approve', $application->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Approve Application</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to approve
                                                        <strong>{{ $application->name }}</strong>'s application?
                                                    </p>
                                                    <p class="text-info">This will create a designer account with login
                                                        credentials.</p>

                                                    <div class="form-group">
                                                        <label>Commission Rate (%)</label>
                                                        <input type="number" name="commission_rate" class="form-control"
                                                            value="30" min="0" max="100" step="0.01">
                                                        <small class="form-text text-muted">Default: 30%</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $application->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <form method="POST"
                                                action="{{ route('designer_system.application.reject', $application->id) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Application</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Are you sure you want to reject
                                                        <strong>{{ $application->name }}</strong>'s application?
                                                    </p>

                                                    <div class="form-group">
                                                        <label>Rejection Reason <span class="text-danger">*</span></label>
                                                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                                            placeholder="Please provide a clear reason for rejection (minimum 10 characters)..."
                                                            minlength="10"></textarea>
                                                        <small class="form-text text-muted">
                                                            <i class="fa fa-info-circle"></i> This reason will be shown to the
                                                            applicant. Be professional and constructive.
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state">
                                        <i class="fa fa-inbox"></i>
                                        <p>No applications found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="pagination-wrapper">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Pusher JS for WebSocket Real-Time Updates -->
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>

<script>
    // Real-time WebSocket System for Designer Applications
    document.addEventListener('DOMContentLoaded', function () {
        let pusher = null;
        let useWebSocket = false;

        console.log('🚀 Initializing Real-Time Designer Application System...');

        function initializeWebSocket() {
            try {
                console.log('🔌 Attempting WebSocket connection...');

                // Enable Pusher logging for debugging
                Pusher.logToConsole = true;

                // Initialize Pusher
                pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
                    wsHost: '{{ env("PUSHER_HOST", "127.0.0.1") }}',
                    wsPort: {{ env('PUSHER_PORT', 6001) }},
                    wssPort: {{ env('PUSHER_PORT', 6001) }},
                    forceTLS: false,
                    encrypted: false,
                    disableStats: true,
                    enabledTransports: ['ws', 'wss'],
                    cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }
                });

                // Connection state logging
                pusher.connection.bind('connecting', function () {
                    console.log('🔄 WebSocket connecting...');
                });

                pusher.connection.bind('connected', function () {
                    console.log('✅ WebSocket CONNECTED! Real-time updates enabled');
                    useWebSocket = true;
                    showNotification('success', 'Real-Time Mode Active', 'New applications will appear automatically!');
                });

                pusher.connection.bind('disconnected', function () {
                    console.log('❌ WebSocket disconnected');
                    useWebSocket = false;
                });

                pusher.connection.bind('failed', function () {
                    console.log('❌ WebSocket connection failed');
                    useWebSocket = false;
                });

                // Subscribe to designer-applications channel
                const channel = pusher.subscribe('designer-applications');

                channel.bind('pusher:subscription_succeeded', function () {
                    console.log('📡 Successfully subscribed to "designer-applications" channel');
                });

                channel.bind('pusher:subscription_error', function (error) {
                    console.error('❌ Subscription error:', error);
                });

                // Listen for new application event
                channel.bind('new-application', function (data) {
                    if (data && data.id) {
                        console.log('⚡ WebSocket: New application received INSTANTLY!', data);

                        // Only add if we're on pending tab
                        const currentStatus = '{{ $status }}';
                        if (currentStatus === 'pending') {
                            addApplicationToTable(data);
                            showNotification('info', 'New Application', `${data.name} has submitted an application`);
                        }
                    }
                });

            } catch (error) {
                console.error('❌ WebSocket initialization failed:', error);
                useWebSocket = false;
            }
        }

        function addApplicationToTable(data) {
            const tbody = document.querySelector('.system-table tbody');
            if (!tbody) return;

            // Check if empty state exists and remove it
            const emptyState = tbody.querySelector('.empty-state');
            if (emptyState) {
                emptyState.closest('tr').remove();
            }

            // Check if application already exists
            const existingRow = tbody.querySelector(`tr[data-application-id="${data.id}"]`);
            if (existingRow) {
                console.log('Application already exists in table, skipping...');
                return;
            }

            // Create new row with highlight
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-application-id', data.id);
            newRow.className = 'new-application-highlight';
            newRow.innerHTML = `
            <td><strong>#${data.id}</strong></td>
            <td>${data.name}</td>
            <td>${data.email}</td>
            <td>${data.phone}</td>
            <td>${data.city}, ${data.state}</td>
            <td>${data.experience ? data.experience.substring(0, 50) + '...' : 'N/A'}</td>
            <td>
                <span class="status-badge status-pending">Pending</span>
            </td>
            <td>${data.created_at}</td>
            <td class="actions-cell">
                <button type="button" class="btn-action btn-info-action" 
                        onclick="location.reload()">
                    <i class="fa fa-eye"></i> View
                </button>
                <button type="button" class="btn-action btn-success-action" 
                        onclick="location.reload()">
                    <i class="fa fa-check"></i> Approve
                </button>
                <button type="button" class="btn-action btn-danger-action" 
                        onclick="location.reload()">
                    <i class="fa fa-times"></i> Reject
                </button>
            </td>
        `;

            // Insert at the beginning of tbody
            tbody.insertBefore(newRow, tbody.firstChild);

            console.log('✅ New application added to table');

            // Remove highlight after 3 seconds
            setTimeout(() => {
                newRow.classList.remove('new-application-highlight');
            }, 3000);
        }

        function showNotification(type, title, message) {
            // Check if browser supports notifications
            if ("Notification" in window && Notification.permission === "granted") {
                new Notification(title, {
                    body: message,
                    icon: '/assets/vendors/images/deskapp-logo.svg'
                });
            }

            // Also show in-page alert
            const alertClass = type === 'success' ? 'alert-success' :
                type === 'info' ? 'alert-info' :
                    type === 'warning' ? 'alert-warning' : 'alert-danger';

            const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 80px; right: 20px; z-index: 9999; min-width: 300px;">
                <strong>${title}:</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', alertHtml);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    if (alert.textContent.includes(title)) {
                        alert.remove();
                    }
                });
            }, 5000);
        }

        // Request notification permission on page load
        if ("Notification" in window && Notification.permission === "default") {
            Notification.requestPermission();
        }

        // Start WebSocket connection after 2 seconds
        setTimeout(initializeWebSocket, 2000);
    });
</script>

<style>
    .new-application-highlight {
        animation: highlightFade 3s ease-in-out;
    }

    @keyframes highlightFade {
        0% {
            background-color: #d4edda;
        }

        100% {
            background-color: transparent;
        }
    }
</style>

@include('layouts.footer')