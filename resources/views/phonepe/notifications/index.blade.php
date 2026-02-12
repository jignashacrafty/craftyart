@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>🔔 PhonePe Notifications</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.dashboard') }}">PhonePe</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $notifications->total() }}</div>
                                <div class="font-14 text-secondary weight-500">Total Notifications</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #5f259f"><i class="icon-copy fa fa-bell"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $notifications->where('notification_type', 'PAYMENT_SUCCESS')->count() }}</div>
                                <div class="font-14 text-secondary weight-500">Payment Success</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #28a745"><i class="icon-copy fa fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $notifications->where('notification_type', 'PRE_DEBIT_NOTIFICATION')->count() }}</div>
                                <div class="font-14 text-secondary weight-500">Pre-Debit</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #ff9800"><i class="icon-copy fa fa-clock-o"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $notifications->where('is_processed', true)->count() }}</div>
                                <div class="font-14 text-secondary weight-500">Processed</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #2196F3"><i class="icon-copy fa fa-check"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Table -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">All Notifications</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Event</th>
                                <th>Subscription ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Processed</th>
                                <th>Received At</th>
                                <th class="datatable-nosort">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                            <tr>
                                <td><strong>#{{ $notification->id }}</strong></td>
                                <td>
                                    @if($notification->notification_type === 'PAYMENT_SUCCESS')
                                        <span class="badge badge-success">
                                            <i class="fa fa-check"></i> Success
                                        </span>
                                    @elseif($notification->notification_type === 'PRE_DEBIT_NOTIFICATION')
                                        <span class="badge badge-warning">
                                            <i class="fa fa-clock-o"></i> Pre-Debit
                                        </span>
                                    @elseif($notification->notification_type === 'PAYMENT_FAILED')
                                        <span class="badge badge-danger">
                                            <i class="fa fa-times"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge badge-info">
                                            {{ $notification->notification_type }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small style="font-weight: 600;">
                                        {{ str_replace('_', ' ', $notification->event_type) }}
                                    </small>
                                </td>
                                <td>
                                    <code style="font-size: 11px;">{{ Str::limit($notification->merchant_subscription_id, 20) }}</code>
                                </td>
                                <td>
                                    @if($notification->amount)
                                        <strong style="color: #28a745;">₹{{ number_format($notification->amount, 2) }}</strong>
                                    @else
                                        <span style="color: #999;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->status === 'SUCCESS')
                                        <span class="badge badge-success">Success</span>
                                    @elseif($notification->status === 'FAILED')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $notification->status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($notification->is_processed)
                                        <span class="badge badge-success">
                                            <i class="fa fa-check-circle"></i> Yes
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fa fa-times-circle"></i> No
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $notification->created_at->format('d M Y') }}</small><br>
                                    <small style="color: #999;">{{ $notification->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="{{ route('phonepe.notifications.show', $notification->id) }}">
                                                <i class="dw dw-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
    $('.data-table').DataTable({
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        columnDefs: [{
            targets: "datatable-nosort",
            orderable: false,
        }],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search",
            paginate: {
                next: '<i class="ion-chevron-right"></i>',
                previous: '<i class="ion-chevron-left"></i>'
            }
        },
    });
</script>
