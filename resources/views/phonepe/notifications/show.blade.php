@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>🔔 Notification Details</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.notifications.index') }}">Notifications</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Details</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Notification Info Card -->
                <div class="col-md-8 mb-30">
                    <div class="card-box height-100-p pd-20">
                        <h4 class="text-blue h4 mb-20">Notification Information</h4>
                        
                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Notification Details</h5>
                            <ul>
                                <li>
                                    <span>Notification Type:</span>
                                    <span class="badge badge-primary">{{ $notification->notification_type }}</span>
                                </li>
                                @if($notification->event_type)
                                <li>
                                    <span>Event Type:</span>
                                    <span class="badge badge-info">{{ $notification->event_type }}</span>
                                </li>
                                @endif
                                <li>
                                    <span>Status:</span>
                                    @php
                                        $statusColors = [
                                            'SUCCESS' => 'success',
                                            'COMPLETED' => 'success',
                                            'FAILED' => 'danger',
                                            'PENDING' => 'warning',
                                            'INFO' => 'info'
                                        ];
                                        $color = $statusColors[$notification->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ $notification->status ?? 'N/A' }}</span>
                                </li>
                                @if($notification->amount)
                                <li>
                                    <span>Amount:</span>
                                    <strong style="font-size: 18px; color: #5f259f;">₹{{ number_format($notification->amount, 2) }}</strong>
                                </li>
                                @endif
                                @if($notification->payment_method)
                                <li>
                                    <span>Payment Method:</span>
                                    {{ $notification->payment_method }}
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Transaction IDs</h5>
                            <ul>
                                <li>
                                    <span>Merchant Order ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $notification->merchant_order_id }}</code>
                                </li>
                                @if($notification->merchant_subscription_id)
                                <li>
                                    <span>Merchant Subscription ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $notification->merchant_subscription_id }}</code>
                                </li>
                                @endif
                                @if($notification->phonepe_order_id)
                                <li>
                                    <span>PhonePe Order ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $notification->phonepe_order_id }}</code>
                                </li>
                                @endif
                                @if($notification->phonepe_transaction_id)
                                <li>
                                    <span>PhonePe Transaction ID:</span>
                                    <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px;">{{ $notification->phonepe_transaction_id }}</code>
                                </li>
                                @endif
                            </ul>
                        </div>

                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Processing Status</h5>
                            <ul>
                                <li>
                                    <span>Processed:</span>
                                    @if($notification->is_processed)
                                        <span class="badge badge-success">✅ Yes</span>
                                    @else
                                        <span class="badge badge-warning">⏳ Pending</span>
                                    @endif
                                </li>
                                @if($notification->processed_at)
                                <li>
                                    <span>Processed At:</span>
                                    {{ $notification->processed_at->format('d M Y, h:i A') }}
                                </li>
                                @endif
                            </ul>
                        </div>

                        @if($notification->notes)
                        <div class="profile-info">
                            <h5 class="mb-20 h5 text-blue">Notes</h5>
                            <p>{{ $notification->notes }}</p>
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
                                    <h6>Received</h6>
                                    <p>{{ $notification->created_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                            @if($notification->processed_at)
                            <div class="timeline-item">
                                <div class="timeline-badge info"></div>
                                <div class="timeline-content">
                                    <h6>Processed</h6>
                                    <p>{{ $notification->processed_at->format('d M Y, h:i A') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <h5 class="mt-30 mb-20 h5 text-blue">Quick Actions</h5>
                        <div class="btn-list">
                            @if($notification->transaction)
                            <a href="{{ route('phonepe.transactions.show', $notification->transaction->id) }}" class="btn btn-primary btn-block">
                                <i class="fa fa-file-text"></i> View Transaction
                            </a>
                            @endif
                            <a href="{{ route('phonepe.notifications.index') }}" class="btn btn-secondary btn-block">
                                <i class="fa fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Webhook/Response Data -->
            <div class="row">
                @if($notification->webhook_payload)
                <div class="col-md-6 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="text-blue h4 mb-20">Webhook Payload</h4>
                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto;">{{ json_encode($notification->webhook_payload, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
                @if($notification->response_data)
                <div class="col-md-6 mb-30">
                    <div class="card-box pd-20">
                        <h4 class="text-blue h4 mb-20">Response Data</h4>
                        <pre style="background: #f8f9fa; padding: 15px; border-radius: 8px; max-height: 400px; overflow-y: auto;">{{ json_encode($notification->response_data, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')
