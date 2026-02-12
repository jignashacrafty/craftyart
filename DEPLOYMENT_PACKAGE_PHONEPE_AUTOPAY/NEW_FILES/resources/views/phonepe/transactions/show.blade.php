@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>📱 Transaction Details</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.transactions.index') }}">Transactions</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Transaction Info Card -->
                <div class="col-md-8 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="text-blue h4 mb-20">Transaction Information</h4>
                        
                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Transaction IDs</h5>
                            <ul>
                                <li>
                                    <span>Merchant Order ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $transaction->merchant_order_id }}</code>
                                </li>
                                @if($transaction->merchant_subscription_id)
                                <li>
                                    <span>Merchant Subscription ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $transaction->merchant_subscription_id }}</code>
                                </li>
                                @endif
                                @if($transaction->phonepe_order_id)
                                <li>
                                    <span>PhonePe Order ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $transaction->phonepe_order_id }}</code>
                                </li>
                                @endif
                                @if($transaction->phonepe_transaction_id)
                                <li>
                                    <span>PhonePe Transaction ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $transaction->phonepe_transaction_id }}</code>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Customer Information</h5>
                            <ul>
                                @if($transaction->upi_id)
                                <li>
                                    <span>UPI ID:</span>
                                    {{ $transaction->upi_id }}
                                </li>
                                @endif
                                @if($transaction->mobile)
                                <li>
                                    <span>Mobile:</span>
                                    {{ $transaction->mobile }}
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Payment Details</h5>
                            <ul>
                                <li>
                                    <span>Transaction Type:</span>
                                    <span class="badge badge-info">{{ $transaction->transaction_type }}</span>
                                </li>
                                <li>
                                    <span>Amount:</span>
                                    <strong style="font-size: 18px; color: #5f259f;">₹{{ number_format($transaction->amount, 2) }}</strong>
                                </li>
                                <li>
                                    <span>Status:</span>
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'warning',
                                            'ACTIVE' => 'success',
                                            'COMPLETED' => 'info',
                                            'FAILED' => 'danger'
                                        ];
                                        $color = $statusColors[$transaction->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ $transaction->status }}</span>
                                </li>
                                @if($transaction->payment_state)
                                <li>
                                    <span>Payment State:</span>
                                    {{ $transaction->payment_state }}
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">AutoPay Information</h5>
                            <ul>
                                <li>
                                    <span>AutoPay Status:</span>
                                    @if($transaction->is_autopay_active)
                                        <span class="badge badge-success">✅ Active</span>
                                    @else
                                        <span class="badge badge-secondary">⏸️ Inactive</span>
                                    @endif
                                </li>
                                <li>
                                    <span>AutoPay Count:</span>
                                    <span class="badge badge-primary">{{ $transaction->autopay_count }}x</span>
                                </li>
                                @if($transaction->last_autopay_at)
                                <li>
                                    <span>Last AutoPay:</span>
                                    {{ $transaction->last_autopay_at->format('d M Y, h:i A') }}
                                </li>
                                @endif
                                @if($transaction->next_autopay_at)
                                <li>
                                    <span>Next AutoPay:</span>
                                    {{ $transaction->next_autopay_at->format('d M Y, h:i A') }}
                                </li>
                                @endif
                            </ul>
                        </div>

                        @if($transaction->notes)
                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Notes</h5>
                            <p>{{ $transaction->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Timeline Card -->
                <div class="col-md-4 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="text-blue h4 mb-20">Timeline</h4>
                        <div class="profile-timeline">
                            <div class="timeline-item">
                                <div class="timeline-badge success"></div>
                                <div class="timeline-content">
                                    <h6>Created</h6>
                                    <p>{{ $transaction->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                            @if($transaction->updated_at != $transaction->created_at)
                            <div class="timeline-item">
                                <div class="timeline-badge info"></div>
                                <div class="timeline-content">
                                    <h6>Last Updated</h6>
                                    <p>{{ $transaction->updated_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <h5 class="mt-30 mb-20 h5 text-blue">Quick Actions</h5>
                        <div class="btn-list">
                            <a href="{{ route('phonepe.transactions.notifications', $transaction->id) }}" class="btn btn-primary btn-block">
                                <i class="fa fa-bell"></i> View Notifications ({{ $transaction->notifications->count() }})
                            </a>
                            <a href="{{ route('phonepe.transactions.index') }}" class="btn btn-secondary btn-block">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request/Response Data -->
            <div class="row">
                <div class="col-md-6 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="text-blue h4 mb-20">Request Payload</h4>
                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto;">{{ json_encode($transaction->request_payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                <div class="col-md-6 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="text-blue h4 mb-20">Response Data</h4>
                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto;">{{ json_encode($transaction->response_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
