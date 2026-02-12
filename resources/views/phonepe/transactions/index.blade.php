@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>📱 PhonePe Transactions</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.dashboard') }}">PhonePe</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Transactions</li>
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
                                <div class="weight-700 font-24 text-dark">{{ $transactions->total() }}</div>
                                <div class="font-14 text-secondary weight-500">Total Transactions</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #5f259f"><i class="icon-copy fa fa-list"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $transactions->where('is_autopay_active', true)->count() }}</div>
                                <div class="font-14 text-secondary weight-500">Active AutoPay</div>
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
                                <div class="weight-700 font-24 text-dark">{{ $transactions->where('status', 'PENDING')->count() }}</div>
                                <div class="font-14 text-secondary weight-500">Pending</div>
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
                                <div class="weight-700 font-24 text-dark">₹{{ number_format($transactions->sum('amount'), 0) }}</div>
                                <div class="font-14 text-secondary weight-500">Total Amount</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #2196F3"><i class="icon-copy fa fa-rupee"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">All Transactions</h4>
                </div>
                <div class="pb-20">
                    <table class="data-table table stripe hover nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>AutoPay</th>
                                <th>Count</th>
                                <th>Last Payment</th>
                                <th>Next Payment</th>
                                <th>Created</th>
                                <th class="datatable-nosort">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td><strong>#{{ $transaction->id }}</strong></td>
                                <td>
                                    <div style="font-weight: 600;">{{ $transaction->user->name ?? 'N/A' }}</div>
                                    <small style="color: #999;">{{ $transaction->user->email ?? '' }}</small>
                                </td>
                                <td><i class="fa fa-phone"></i> {{ $transaction->mobile }}</td>
                                <td><strong style="color: #5f259f;">₹{{ number_format($transaction->amount, 2) }}</strong></td>
                                <td>
                                    @if($transaction->status === 'ACTIVE')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($transaction->status === 'PENDING')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($transaction->status === 'FAILED')
                                        <span class="badge badge-danger">Failed</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->is_autopay_active)
                                        <span style="color: #28a745; font-weight: 600;">
                                            <i class="fa fa-check-circle"></i> Active
                                        </span>
                                    @else
                                        <span style="color: #999;">
                                            <i class="fa fa-times-circle"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $transaction->autopay_count }} payments
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->last_autopay_at)
                                        <small>{{ $transaction->last_autopay_at->format('d M Y') }}</small>
                                    @else
                                        <small style="color: #999;">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->next_autopay_at)
                                        <small style="font-weight: 600; color: #ff9800;">{{ $transaction->next_autopay_at->format('d M Y') }}</small>
                                    @else
                                        <small style="color: #999;">-</small>
                                    @endif
                                </td>
                                <td><small>{{ $transaction->created_at->format('d M Y, h:i A') }}</small></td>
                                <td>
                                    <div class="dropdown">
                                        <a class="btn btn-link font-24 p-0 line-height-1 no-arrow dropdown-toggle" href="#" role="button" data-toggle="dropdown">
                                            <i class="dw dw-more"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-icon-list">
                                            <a class="dropdown-item" href="{{ route('phonepe.transactions.show', $transaction->id) }}">
                                                <i class="dw dw-eye"></i> View Details
                                            </a>
                                            @if($transaction->status === 'PENDING')
                                            <a class="dropdown-item check-status-btn" href="javascript:void(0)" data-id="{{ $transaction->id }}">
                                                <i class="dw dw-refresh"></i> Check Status
                                            </a>
                                            @endif
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

    // Check Status functionality
    $(document).on('click', '.check-status-btn', function() {
        var transactionId = $(this).data('id');
        
        if (confirm('Check the current status of this transaction from PhonePe?')) {
            $.ajax({
                url: '/phonepe/transactions/' + transactionId + '/check-status',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Status updated successfully!\n\nStatus: ' + response.transaction.status);
                        location.reload();
                    } else {
                        alert('❌ Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    alert('❌ Error checking status. Please try again.');
                }
            });
        }
    });
</script>
