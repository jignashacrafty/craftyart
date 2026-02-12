@include('layouts.masterhead')

<div class="main-container">
    <div class="pd-ltr-20 xs-pd-20-10">
        <div class="min-height-200px">
            <div class="page-header">
                <div class="row">
                    <div class="col-md-12">
                        <div class="title">
                            <h4>🔔 Transaction Notifications</h4>
                        </div>
                        <nav aria-label="breadcrumb" role="navigation">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.dashboard') }}">PhonePe Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.transactions.index') }}">Transactions</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('phonepe.transactions.show', $transaction->id) }}">Transaction #{{ $transaction->id }}</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Transaction Summary Card -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h5 class="text-blue h5">📱 Transaction Summary</h5>
                </div>
                <div class="pb-20 px-20">
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Merchant Order ID:</strong></p>
                            <code>{{ $transaction->merchant_order_id }}</code>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Subscription ID:</strong></p>
                            <code>{{ $transaction->merchant_subscription_id ?? 'N/A' }}</code>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Amount:</strong></p>
                            <h5 class="text-primary">₹{{ number_format($transaction->amount, 2) }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Status:</strong></p>
                            @php
                                $statusColors = [
                                    'PENDING' => 'warning',
                                    'ACTIVE' => 'success',
                                    'COMPLETED' => 'info',
                                    'FAILED' => 'danger'
                                ];
                                $color = $statusColors[$transaction->status] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $color }} badge-lg">{{ $transaction->status }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <p class="mb-1"><strong>UPI ID:</strong></p>
                            <p>{{ $transaction->upi_id ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>Mobile:</strong></p>
                            <p>{{ $transaction->mobile ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>AutoPay Status:</strong></p>
                            @if($transaction->is_autopay_active)
                                <span class="badge badge-success">✅ Active</span>
                            @else
                                <span class="badge badge-secondary">⏸️ Inactive</span>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1"><strong>AutoPay Count:</strong></p>
                            <span class="badge badge-primary">{{ $transaction->autopay_count }}x</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="card-box mb-30">
                <div class="pd-20">
                    <h5 class="text-blue h5">🔔 All Notifications ({{ $transaction->notifications->count() }})</h5>
                    <p class="mb-0">Complete history of all notifications and webhook responses for this transaction</p>
                </div>
                <div class="pb-20">
                    @if($transaction->notifications->count() > 0)
                        <div class="timeline">
                            @foreach($transaction->notifications->sortByDesc('created_at') as $notification)
                                <div class="timeline-item mb-30 px-20">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="card">
                                                <div class="card-header" style="background-color: {{ $notification->status == 'SUCCESS' ? '#e8f5e9' : ($notification->status == 'FAILED' ? '#ffebee' : '#fff3e0') }}">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-6">
                                                            <h6 class="mb-0">
                                                                @if($notification->notification_type == 'SUBSCRIPTION_SETUP')
                                                                    📲 Subscription Setup
                                                                @elseif($notification->notification_type == 'PAYMENT_SUCCESS')
                                                                    ✅ Payment Success
                                                                @elseif($notification->notification_type == 'PAYMENT_FAILED')
                                                                    ❌ Payment Failed
                                                                @elseif($notification->notification_type == 'WEBHOOK')
                                                                    🔗 Webhook Received
                                                                @else
                                                                    📬 {{ $notification->notification_type }}
                                                                @endif
                                                            </h6>
                                                            <small class="text-muted">{{ $notification->created_at->format('d M Y, h:i:s A') }}</small>
                                                        </div>
                                                        <div class="col-md-6 text-right">
                                                            @php
                                                                $notifStatusColors = [
                                                                    'SUCCESS' => 'success',
                                                                    'PENDING' => 'warning',
                                                                    'FAILED' => 'danger',
                                                                    'COMPLETED' => 'info'
                                                                ];
                                                                $notifColor = $notifStatusColors[$notification->status] ?? 'secondary';
                                                            @endphp
                                                            <span class="badge badge-{{ $notifColor }}">{{ $notification->status }}</span>
                                                            @if($notification->is_processed)
                                                                <span class="badge badge-success">✓ Processed</span>
                                                            @else
                                                                <span class="badge badge-warning">⏳ Pending</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Event Type:</strong></p>
                                                            <code>{{ $notification->event_type }}</code>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Amount:</strong></p>
                                                            <strong class="text-primary">₹{{ number_format($notification->amount, 2) }}</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    @if($notification->phonepe_order_id)
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>PhonePe Order ID:</strong></p>
                                                            <code>{{ $notification->phonepe_order_id }}</code>
                                                        </div>
                                                        @if($notification->phonepe_transaction_id)
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>PhonePe Transaction ID:</strong></p>
                                                            <code>{{ $notification->phonepe_transaction_id }}</code>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    
                                                    @if($notification->payment_method)
                                                    <div class="row mt-2">
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Payment Method:</strong></p>
                                                            <span class="badge badge-info">{{ $notification->payment_method }}</span>
                                                        </div>
                                                        @if($notification->processed_at)
                                                        <div class="col-md-6">
                                                            <p class="mb-1"><strong>Processed At:</strong></p>
                                                            <small>{{ $notification->processed_at->format('d M Y, h:i:s A') }}</small>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    @endif
                                                    
                                                    @if($notification->notes)
                                                    <div class="row mt-2">
                                                        <div class="col-md-12">
                                                            <p class="mb-1"><strong>Notes:</strong></p>
                                                            <p class="text-muted">{{ $notification->notes }}</p>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    <!-- Webhook Payload -->
                                                    @if($notification->webhook_payload)
                                                    <div class="row mt-3">
                                                        <div class="col-md-12">
                                                            <p class="mb-1"><strong>Webhook Payload:</strong></p>
                                                            <div class="alert alert-info">
                                                                <button class="btn btn-sm btn-primary mb-2" type="button" data-toggle="collapse" data-target="#webhook-{{ $notification->id }}" aria-expanded="false">
                                                                    <i class="fa fa-code"></i> Show/Hide Webhook Data
                                                                </button>
                                                                <div class="collapse" id="webhook-{{ $notification->id }}">
                                                                    <pre style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px;"><code>{{ json_encode($notification->webhook_payload, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    <!-- Response Data -->
                                                    @if($notification->response_data)
                                                    <div class="row mt-3">
                                                        <div class="col-md-12">
                                                            <p class="mb-1"><strong>Response Data:</strong></p>
                                                            <div class="alert alert-secondary">
                                                                <button class="btn btn-sm btn-secondary mb-2" type="button" data-toggle="collapse" data-target="#response-{{ $notification->id }}" aria-expanded="false">
                                                                    <i class="fa fa-code"></i> Show/Hide Response Data
                                                                </button>
                                                                <div class="collapse" id="response-{{ $notification->id }}">
                                                                    <pre style="max-height: 300px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 5px;"><code>{{ json_encode($notification->response_data, JSON_PRETTY_PRINT) }}</code></pre>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                    
                                                    <div class="row mt-3">
                                                        <div class="col-md-12 text-right">
                                                            <a href="{{ route('phonepe.notifications.show', $notification->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="fa fa-eye"></i> View Full Details
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-bell-slash-o" style="font-size: 48px; color: #ccc;"></i>
                            <h5 class="mt-3 text-muted">No Notifications Yet</h5>
                            <p class="text-muted">No notifications have been received for this transaction.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Back Button -->
            <div class="row">
                <div class="col-md-12 mb-30">
                    <a href="{{ route('phonepe.transactions.show', $transaction->id) }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Transaction Details
                    </a>
                    <a href="{{ route('phonepe.transactions.index') }}" class="btn btn-primary">
                        <i class="fa fa-list"></i> All Transactions
                    </a>
                    <a href="{{ route('phonepe.dashboard') }}" class="btn btn-info">
                        <i class="fa fa-dashboard"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item::after {
    content: '🔔';
    position: absolute;
    left: -30px;
    top: 10px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    font-size: 12px;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
}

.card-header {
    border-bottom: 1px solid #e0e0e0;
}

pre code {
    font-size: 11px;
    line-height: 1.4;
}
</style>
