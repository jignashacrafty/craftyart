@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>📊 PhonePe AutoPay Dashboard</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">PhonePe Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Statistics -->
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['total_requests'] }}</div>
                                <div class="font-14 text-secondary weight-500">Total Requests</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #5f259f"><i class="icon-copy fa fa-paper-plane"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['active_subscriptions'] }}</div>
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
                                <div class="weight-700 font-24 text-dark">{{ $stats['pending_approvals'] }}</div>
                                <div class="font-14 text-secondary weight-500">Pending Approval</div>
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
                                <div class="weight-700 font-24 text-dark">₹{{ number_format($stats['total_amount'], 0) }}</div>
                                <div class="font-14 text-secondary weight-500">Total Amount</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #2196F3"><i class="icon-copy fa fa-rupee"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Statistics -->
            <div class="row pb-10">
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['total_autopay_payments'] }}</div>
                                <div class="font-14 text-secondary weight-500">AutoPay Payments</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #9c27b0"><i class="icon-copy fa fa-refresh"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['today_requests'] }}</div>
                                <div class="font-14 text-secondary weight-500">Today's Requests</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #00bcd4"><i class="icon-copy fa fa-calendar-check-o"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['month_requests'] }}</div>
                                <div class="font-14 text-secondary weight-500">This Month</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #5f259f"><i class="icon-copy fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-lg-3 col-md-6 mb-20">
                    <div class="card-box height-100-p widget-style3">
                        <div class="d-flex flex-wrap">
                            <div class="widget-data">
                                <div class="weight-700 font-24 text-dark">{{ $stats['failed_requests'] }}</div>
                                <div class="font-14 text-secondary weight-500">Failed Requests</div>
                            </div>
                            <div class="widget-icon">
                                <div class="icon" style="color: #f44336"><i class="icon-copy fa fa-times-circle"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Users Table -->
            @if($pendingUsers->count() > 0)
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Pending Approvals ({{ $pendingUsers->count() }})</h4>
                </div>
                <div class="pb-20">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>Plan</th>
                                <th>Requested</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->user->name ?? 'N/A' }}</strong><br>
                                    <small style="color: #999;">{{ $user->user->email ?? '' }}</small>
                                </td>
                                <td><i class="fa fa-phone"></i> {{ $user->mobile }}</td>
                                <td><strong style="color: #5f259f;">₹{{ number_format($user->amount, 2) }}</strong></td>
                                <td><span class="badge badge-info">{{ $user->plan_id ?? 'N/A' }}</span></td>
                                <td><small>{{ $user->created_at->diffForHumans() }}</small></td>
                                <td><span class="badge badge-warning">Pending</span></td>
                                <td>
                                    <a href="{{ route('phonepe.transactions.show', $user->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Active Users Table -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Active Subscriptions ({{ $activeUsers->count() }})</h4>
                </div>
                <div class="pb-20">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>AutoPay Count</th>
                                <th>Last Payment</th>
                                <th>Next Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeUsers as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->user->name ?? 'N/A' }}</strong><br>
                                    <small style="color: #999;">{{ $user->user->email ?? '' }}</small>
                                </td>
                                <td><i class="fa fa-phone"></i> {{ $user->mobile }}</td>
                                <td><strong style="color: #28a745;">₹{{ number_format($user->amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge badge-primary">
                                        {{ $user->autopay_count }} payments
                                    </span>
                                </td>
                                <td>
                                    @if($user->last_autopay_at)
                                        <small>{{ $user->last_autopay_at->format('d M Y') }}</small>
                                    @else
                                        <small style="color: #999;">-</small>
                                    @endif
                                </td>
                                <td>
                                    @if($user->next_autopay_at)
                                        <small style="font-weight: 600; color: #ff9800;">
                                            {{ $user->next_autopay_at->format('d M Y') }}
                                        </small>
                                    @else
                                        <small style="color: #999;">-</small>
                                    @endif
                                </td>
                                <td><span class="badge badge-success">Active</span></td>
                                <td>
                                    <a href="{{ route('phonepe.transactions.show', $user->id) }}" class="btn btn-sm btn-success">
                                        <i class="fa fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h4 class="text-blue h4">Recent Transactions ({{ $recentTransactions->count() }})</h4>
                </div>
                <div class="pb-20">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>AutoPay</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td><strong>#{{ $transaction->id }}</strong></td>
                                <td>
                                    <strong>{{ $transaction->user->name ?? 'N/A' }}</strong><br>
                                    <small style="color: #999;">{{ $transaction->user->email ?? '' }}</small>
                                </td>
                                <td><i class="fa fa-phone"></i> {{ $transaction->mobile }}</td>
                                <td><strong style="color: #5f259f;">₹{{ number_format($transaction->amount, 2) }}</strong></td>
                                <td>
                                    @if($transaction->status === 'ACTIVE')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($transaction->status === 'PENDING')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $transaction->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->is_autopay_active)
                                        <i class="fa fa-check-circle" style="color: #28a745;"></i> Active
                                    @else
                                        <i class="fa fa-times-circle" style="color: #999;"></i> Inactive
                                    @endif
                                </td>
                                <td><small>{{ $transaction->created_at->diffForHumans() }}</small></td>
                                <td>
                                    <a href="{{ route('phonepe.transactions.show', $transaction->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fa fa-eye"></i> View
                                    </a>
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
