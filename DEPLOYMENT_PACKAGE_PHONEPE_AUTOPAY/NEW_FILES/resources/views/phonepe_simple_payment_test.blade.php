@include('layouts.masterhead')

<style>
    .payment-test-container {
        padding: 20px;
        background: #f5f7fa;
        min-height: 100vh;
    }
    
    .payment-test-card {
        max-width: 1200px;
        margin: 0 auto 30px;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .card-header h3 {
        color: #5f259f;
        margin: 0;
        font-size: 24px;
        font-weight: 700;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #5f259f;
        box-shadow: 0 0 0 3px rgba(95, 37, 159, 0.1);
    }
    
    .btn-send-payment {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #5f259f 0%, #8b3dff 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }
    
    .btn-send-payment:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(95, 37, 159, 0.3);
    }
    
    .result-box {
        margin-top: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        display: none;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .result-box.success {
        background: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }
    
    .result-box.error {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }
    
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px 20px;
        margin-bottom: 25px;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .loading {
        display: none;
        text-align: center;
        padding: 30px;
    }
    
    .spinner {
        border: 3px solid #f3f3f3;
        border-top: 3px solid #5f259f;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Transaction History Table */
    .history-section {
        margin-top: 40px;
    }
    
    .history-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .history-header h4 {
        color: #333;
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }
    
    .btn-refresh {
        padding: 8px 16px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
    }
    
    .btn-refresh:hover {
        background: #5a6268;
        transform: translateY(-1px);
    }
    
    .transaction-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .transaction-table thead {
        background: linear-gradient(135deg, #5f259f 0%, #7b3db8 100%);
        color: white;
    }
    
    .transaction-table th {
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .transaction-table td {
        padding: 15px 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
        color: #333;
    }
    
    .transaction-table tbody tr {
        transition: all 0.2s;
    }
    
    .transaction-table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .transaction-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-active {
        background: #d4edda;
        color: #155724;
    }
    
    .status-completed {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-failed {
        background: #f8d7da;
        color: #721c24;
    }
    
    .autopay-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .autopay-active {
        background: #d4edda;
        color: #155724;
    }
    
    .autopay-inactive {
        background: #e2e3e5;
        color: #6c757d;
    }
    
    .action-buttons-cell {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .btn-action {
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    
    .btn-check-status {
        background: #17a2b8;
        color: white;
    }
    
    .btn-check-status:hover {
        background: #138496;
    }
    
    .btn-predebit {
        background: #ffc107;
        color: #333;
    }
    
    .btn-predebit:hover {
        background: #e0a800;
    }
    
    .btn-autodebit {
        background: #28a745;
        color: white;
    }
    
    .btn-autodebit:hover {
        background: #218838;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }
    
    .empty-state-icon {
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .count-badge {
        background: #5f259f;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
    }
    
    .text-center {
        text-align: center;
    }
    
    .text-right {
        text-align: right;
    }
    
    @media (max-width: 768px) {
        .transaction-table {
            font-size: 12px;
        }
        
        .transaction-table th,
        .transaction-table td {
            padding: 10px 8px;
        }
        
        .btn-action {
            padding: 5px 8px;
            font-size: 11px;
        }
    }
</style>

<div class="main-container">
    <div class="payment-test-container">
        <!-- Payment Request Form -->
        <div class="payment-test-card">
            <div class="card-header">
                <h3>📱 PhonePe AutoPay Testing (OAuth)</h3>
            </div>
            
            <div class="info-box">
                <strong>ℹ️ Note:</strong> This will send a real AutoPay subscription request to your UPI ID. 
                You'll receive a notification on your phone to approve the mandate.
            </div>
            
            <form id="paymentRequestForm">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Your UPI ID *</label>
                        <input type="text" class="form-control" name="upi_id" 
                               value="vrajsurani606@okaxis" required 
                               placeholder="yourname@okaxis">
                    </div>
                    
                    <div class="form-group">
                        <label>Amount (₹) *</label>
                        <input type="number" class="form-control" name="amount" 
                               value="1" min="1" max="1000" required 
                               placeholder="Enter amount">
                    </div>
                    
                    <div class="form-group">
                        <label>Mobile Number *</label>
                        <input type="text" class="form-control" name="mobile" 
                               value="9724085965" required 
                               placeholder="10-digit mobile">
                    </div>
                </div>
                
                <button type="submit" class="btn-send-payment">
                    📲 Send AutoPay Request to My UPI
                </button>
            </form>
            
            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p style="color: #6c757d; font-weight: 600;">Sending payment request...</p>
            </div>
            
            <div class="result-box" id="resultBox"></div>
        </div>
        
        <!-- Transaction History -->
        <div class="payment-test-card history-section">
            <div class="history-header">
                <h4>📜 Transaction History</h4>
                <button class="btn-refresh" onclick="loadHistory()">
                    🔄 Refresh
                </button>
            </div>
            
            <div id="historyContainer">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <p>Loading transaction history...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@include('layouts.masterscript')

<script>
$(document).ready(function() {
    // Load history on page load
    loadHistory();
    
    // Form submission
    $('#paymentRequestForm').on('submit', function(e) {
        e.preventDefault();
        
        $('#loading').show();
        $('#resultBox').hide();
        
        $.ajax({
            url: '{{ route("phonepe.send_payment_request") }}',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#loading').hide();
                
                if (response.success) {
                    let html = `
                        <h5 style="margin: 0 0 10px 0; font-size: 18px;">✅ AutoPay Request Sent Successfully!</h5>
                        <p style="margin: 5px 0;"><strong>📱 Check your phone now!</strong></p>
                        <p style="margin: 5px 0;">You should receive a UPI mandate approval request.</p>
                        <p style="margin: 5px 0;">Approve it to activate AutoPay subscription.</p>
                        <div style="margin-top: 15px; padding: 10px; background: rgba(95, 37, 159, 0.1); border-radius: 6px;">
                            <p style="margin: 3px 0;"><strong>Order ID:</strong> ${response.data.merchant_order_id}</p>
                            <p style="margin: 3px 0;"><strong>Subscription ID:</strong> ${response.data.merchant_subscription_id}</p>
                            <p style="margin: 3px 0;"><strong>Amount:</strong> ₹${response.data.amount}</p>
                        </div>
                    `;
                    $('#resultBox').removeClass('error').addClass('success').html(html).fadeIn();
                    
                    // Reload history after 2 seconds
                    setTimeout(function() {
                        loadHistory();
                    }, 2000);
                } else {
                    $('#resultBox').removeClass('success').addClass('error')
                        .html('<strong>❌ Error:</strong> ' + response.message).fadeIn();
                }
            },
            error: function(xhr) {
                $('#loading').hide();
                let errorMsg = 'Unknown error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#resultBox').removeClass('success').addClass('error')
                    .html('<strong>❌ Error:</strong> ' + errorMsg).fadeIn();
            }
        });
    });
});

function loadHistory() {
    $.ajax({
        url: '{{ route("phonepe.get_history") }}',
        type: 'GET',
        success: function(response) {
            if (response.success && response.data.length > 0) {
                let html = '<table class="transaction-table">';
                html += '<thead><tr>';
                html += '<th>Date & Time</th>';
                html += '<th>PhonePe IDs (Click to Copy)</th>';
                html += '<th>UPI ID</th>';
                html += '<th class="text-right">Amount</th>';
                html += '<th class="text-center">Status</th>';
                html += '<th class="text-center">AutoPay</th>';
                html += '<th class="text-center">Pre-Debit</th>';
                html += '<th class="text-center">Count</th>';
                html += '<th>Actions</th>';
                html += '</tr></thead><tbody>';
                
                response.data.forEach(function(item) {
                    let statusClass = 'status-pending';
                    if (item.subscription_state === 'ACTIVE' || item.subscription_state === 'COMPLETED') {
                        statusClass = 'status-active';
                    } else if (item.subscription_state === 'FAILED') {
                        statusClass = 'status-failed';
                    }
                    
                    let autopayClass = item.is_autopay_active ? 'autopay-active' : 'autopay-inactive';
                    let autopayText = item.is_autopay_active ? '✅ Active' : '⏸️ Inactive';
                    let predebitIcon = item.predebit_sent ? '✅' : '❌';
                    
                    let date = new Date(item.created_at);
                    let formattedDate = date.toLocaleDateString('en-IN', { 
                        day: '2-digit', 
                        month: 'short', 
                        year: 'numeric' 
                    });
                    let formattedTime = date.toLocaleTimeString('en-IN', { 
                        hour: '2-digit', 
                        minute: '2-digit' 
                    });
                    
                    // Build IDs section with copy buttons
                    let idsHtml = '<div style="font-size: 11px; line-height: 1.6;">';
                    
                    // Merchant Order ID
                    if (item.merchant_order_id) {
                        idsHtml += '<div style="margin-bottom: 4px;">';
                        idsHtml += '<strong style="color: #5f259f;">Order:</strong> ';
                        idsHtml += '<span style="font-family: monospace; background: #f0f0f0; padding: 2px 6px; border-radius: 3px; cursor: pointer;" ';
                        idsHtml += 'onclick="copyToClipboard(\'' + item.merchant_order_id + '\', \'Order ID\')" ';
                        idsHtml += 'title="Click to copy">' + item.merchant_order_id + '</span>';
                        idsHtml += '</div>';
                    }
                    
                    // Merchant Subscription ID
                    if (item.merchant_subscription_id) {
                        idsHtml += '<div style="margin-bottom: 4px;">';
                        idsHtml += '<strong style="color: #5f259f;">Sub:</strong> ';
                        idsHtml += '<span style="font-family: monospace; background: #f0f0f0; padding: 2px 6px; border-radius: 3px; cursor: pointer;" ';
                        idsHtml += 'onclick="copyToClipboard(\'' + item.merchant_subscription_id + '\', \'Subscription ID\')" ';
                        idsHtml += 'title="Click to copy">' + item.merchant_subscription_id + '</span>';
                        idsHtml += '</div>';
                    }
                    
                    // PhonePe Order ID
                    if (item.phonepe_order_id) {
                        idsHtml += '<div style="margin-bottom: 4px;">';
                        idsHtml += '<strong style="color: #5f259f;">PhonePe:</strong> ';
                        idsHtml += '<span style="font-family: monospace; background: #e8f5e9; padding: 2px 6px; border-radius: 3px; cursor: pointer;" ';
                        idsHtml += 'onclick="copyToClipboard(\'' + item.phonepe_order_id + '\', \'PhonePe Order ID\')" ';
                        idsHtml += 'title="Click to copy - Use this in PhonePe Dashboard">' + item.phonepe_order_id + '</span>';
                        idsHtml += '</div>';
                    }
                    
                    idsHtml += '</div>';
                    
                    html += '<tr>';
                    html += '<td><div style="font-weight: 600;">' + formattedDate + '</div><div style="font-size: 12px; color: #6c757d;">' + formattedTime + '</div></td>';
                    html += '<td>' + idsHtml + '</td>';
                    html += '<td>' + item.upi_id + '</td>';
                    html += '<td class="text-right" style="font-weight: 600;">₹' + parseFloat(item.amount).toFixed(2) + '</td>';
                    html += '<td class="text-center"><span class="status-badge ' + statusClass + '">' + (item.subscription_state || 'PENDING') + '</span></td>';
                    html += '<td class="text-center"><span class="autopay-badge ' + autopayClass + '">' + autopayText + '</span></td>';
                    html += '<td class="text-center" style="font-size: 18px;">' + predebitIcon + '</td>';
                    html += '<td class="text-center"><span class="count-badge">' + item.autopay_count + 'x</span></td>';
                    html += '<td><div class="action-buttons-cell">';
                    html += '<button class="btn-action btn-check-status" onclick="checkStatus(\'' + item.merchant_subscription_id + '\', \'' + item.merchant_order_id + '\')">🔍 Status</button>';
                    html += '<button class="btn-action btn-predebit" onclick="sendPreDebit(\'' + item.merchant_subscription_id + '\', ' + item.amount + ')">📧 Pre-Debit</button>';
                    html += '<button class="btn-action btn-autodebit" onclick="triggerDebit(\'' + item.merchant_subscription_id + '\', ' + item.amount + ')">💳 Debit</button>';
                    html += '<button class="btn-action btn-simulate" onclick="simulateDebit(\'' + item.merchant_subscription_id + '\', ' + item.amount + ')" style="background: #ff9800;">🧪 Simulate</button>';
                    html += '</div></td>';
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#historyContainer').html(html);
            } else {
                $('#historyContainer').html(`
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <p style="font-size: 16px; font-weight: 600; margin-bottom: 5px;">No transactions yet</p>
                        <p style="font-size: 14px; color: #6c757d;">Send your first AutoPay request to see it here</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#historyContainer').html(`
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <p style="color: #dc3545;">Failed to load transaction history</p>
                </div>
            `);
        }
    });
}

// Copy to clipboard function
function copyToClipboard(text, label) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            let msg = $('<div style="position: fixed; top: 20px; right: 20px; background: #28a745; color: white; padding: 12px 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 10000; font-weight: 600;">✅ ' + label + ' copied!</div>');
            $('body').append(msg);
            setTimeout(function() {
                msg.fadeOut(300, function() { $(this).remove(); });
            }, 2000);
        }).catch(function(err) {
            alert('Failed to copy: ' + err);
        });
    } else {
        // Fallback for older browsers
        let textArea = document.createElement("textarea");
        textArea.value = text;
        textArea.style.position = "fixed";
        textArea.style.top = "-1000px";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        try {
            document.execCommand('copy');
            alert(label + ' copied: ' + text);
        } catch (err) {
            alert('Failed to copy');
        }
        document.body.removeChild(textArea);
    }
}

function checkStatus(subscriptionId, orderId) {
    if (!subscriptionId) {
        alert('Invalid subscription ID');
        return;
    }
    
    // Show loading in button
    event.target.disabled = true;
    event.target.innerHTML = '⏳ Checking...';
    
    $.ajax({
        url: '{{ route("phonepe.check_subscription_status") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            merchantSubscriptionId: subscriptionId
        },
        success: function(response) {
            event.target.disabled = false;
            event.target.innerHTML = '🔍 Status';
            
            if (response.success) {
                let state = response.data.state || 'UNKNOWN';
                let message = `
                    <h5 style="margin: 0 0 10px 0; font-size: 18px;">📊 Subscription Status</h5>
                    <div style="padding: 10px; background: rgba(95, 37, 159, 0.1); border-radius: 6px; margin-top: 10px;">
                        <p style="margin: 5px 0;"><strong>Subscription ID:</strong> ${subscriptionId}</p>
                        <p style="margin: 5px 0;"><strong>Order ID:</strong> ${orderId}</p>
                        <p style="margin: 5px 0;"><strong>Current State:</strong> <span style="color: #5f259f; font-weight: 700;">${state}</span></p>
                    </div>
                `;
                
                $('#resultBox').removeClass('error').addClass('success').html(message).fadeIn();
                
                // Reload history to show updated status
                setTimeout(function() {
                    loadHistory();
                }, 1500);
            } else {
                $('#resultBox').removeClass('success').addClass('error')
                    .html('<strong>❌ Error:</strong> Failed to check status').fadeIn();
            }
        },
        error: function() {
            event.target.disabled = false;
            event.target.innerHTML = '🔍 Status';
            $('#resultBox').removeClass('success').addClass('error')
                .html('<strong>❌ Error:</strong> Failed to check status').fadeIn();
        }
    });
}

function sendPreDebit(subscriptionId, amount) {
    if (!subscriptionId) {
        alert('Invalid subscription ID');
        return;
    }
    
    // Show loading in button
    event.target.disabled = true;
    event.target.innerHTML = '⏳ Checking...';
    
    $.ajax({
        url: '{{ route("phonepe.send_predebit") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            merchantSubscriptionId: subscriptionId,
            amount: amount
        },
        success: function(response) {
            event.target.disabled = false;
            event.target.innerHTML = '📧 Pre-Debit';
            
            if (response.success) {
                let message = `
                    <h5 style="margin: 0 0 10px 0; font-size: 18px;">ℹ️ Pre-Debit Notification Info</h5>
                    <div style="padding: 15px; background: rgba(33, 150, 243, 0.1); border-radius: 6px; margin-top: 10px;">
                        <p style="margin: 5px 0; font-weight: 600;">📱 How PhonePe Pre-Debit Works:</p>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>PhonePe OAuth API sends pre-debit notifications automatically</li>
                            <li>Notifications are sent when you trigger the auto-debit</li>
                            <li>Your subscription is <strong>ACTIVE</strong> and ready</li>
                        </ul>
                        <p style="margin: 10px 0 5px 0; font-weight: 600; color: #5f259f;">✅ Next Step:</p>
                        <p style="margin: 5px 0;">Click the <strong>"💳 Debit"</strong> button to trigger payment. PhonePe will automatically send the pre-debit notification to your phone.</p>
                    </div>
                `;
                $('#resultBox').removeClass('error').addClass('success').html(message).fadeIn();
                
                // Reload history
                setTimeout(function() {
                    loadHistory();
                }, 1500);
            } else {
                $('#resultBox').removeClass('success').addClass('error')
                    .html('<strong>❌ Error:</strong> ' + (response.error_message || response.message || 'Failed to check subscription')).fadeIn();
            }
        },
        error: function() {
            event.target.disabled = false;
            event.target.innerHTML = '📧 Pre-Debit';
            $('#resultBox').removeClass('success').addClass('error')
                .html('<strong>❌ Error:</strong> Failed to check subscription status').fadeIn();
        }
    });
}

function triggerDebit(subscriptionId, amount) {
    if (!subscriptionId) {
        alert('Invalid subscription ID');
        return;
    }
    
    if (!confirm('⚠️ This will charge ₹' + amount + ' from your UPI account. Continue?')) {
        return;
    }
    
    // Show loading in button
    event.target.disabled = true;
    event.target.innerHTML = '⏳ Processing...';
    
    $.ajax({
        url: '{{ route("phonepe.trigger_autodebit") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            merchantSubscriptionId: subscriptionId,
            amount: amount
        },
        success: function(response) {
            event.target.disabled = false;
            event.target.innerHTML = '💳 Debit';
            
            if (response.success) {
                let message = `
                    <h5 style="margin: 0 0 10px 0; font-size: 18px;">✅ Auto-Debit Triggered!</h5>
                    <p style="margin: 5px 0;"><strong>💰 Payment processing...</strong></p>
                    <p style="margin: 5px 0;">Amount: ₹${amount}</p>
                    <p style="margin: 5px 0;">The payment will be processed automatically.</p>
                    <div style="margin-top: 15px; padding: 10px; background: rgba(40, 167, 69, 0.1); border-radius: 6px;">
                        <p style="margin: 3px 0;"><strong>Order ID:</strong> ${response.merchant_order_id}</p>
                        <p style="margin: 3px 0;"><strong>PhonePe Order ID:</strong> ${response.phonepe_order_id || 'Processing...'}</p>
                    </div>
                `;
                $('#resultBox').removeClass('error').addClass('success').html(message).fadeIn();
                
                // Reload history
                setTimeout(function() {
                    loadHistory();
                }, 2000);
            } else {
                $('#resultBox').removeClass('success').addClass('error')
                    .html('<strong>❌ Error:</strong> ' + (response.message || 'Failed to trigger auto-debit')).fadeIn();
            }
        },
        error: function() {
            event.target.disabled = false;
            event.target.innerHTML = '💳 Debit';
            $('#resultBox').removeClass('success').addClass('error')
                .html('<strong>❌ Error:</strong> Failed to trigger auto-debit').fadeIn();
        }
    });
}

// Simulate auto-debit for testing (PhonePe doesn't allow manual redemption)
function simulateDebit(subscriptionId, amount) {
    if (!subscriptionId) {
        alert('Invalid subscription ID');
        return;
    }
    
    if (!confirm('🧪 Simulate Auto-Debit?\n\nThis will:\n✅ Increase AutoPay count\n✅ Update last payment date\n✅ Set next payment date\n\n⚠️ Note: This is a SIMULATION for testing.\nIn production, PhonePe handles this automatically.')) {
        return;
    }
    
    // Show loading in button
    event.target.disabled = true;
    event.target.innerHTML = '⏳ Simulating...';
    
    $.ajax({
        url: '{{ route("phonepe.simulate_autodebit") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            merchantSubscriptionId: subscriptionId,
            amount: amount
        },
        success: function(response) {
            event.target.disabled = false;
            event.target.innerHTML = '🧪 Simulate';
            
            if (response.success) {
                let message = `
                    <h5 style="margin: 0 0 10px 0; font-size: 18px;">✅ Auto-Debit Simulated!</h5>
                    <div style="padding: 10px; background: rgba(255, 152, 0, 0.1); border-left: 4px solid #ff9800; margin: 10px 0;">
                        <p style="margin: 3px 0; font-weight: 600; color: #ff9800;">⚠️ This is a SIMULATION for testing</p>
                        <p style="margin: 3px 0; font-size: 13px;">In production, PhonePe automatically debits based on schedule</p>
                    </div>
                    <div style="margin-top: 15px; padding: 10px; background: rgba(40, 167, 69, 0.1); border-radius: 6px;">
                        <p style="margin: 3px 0;"><strong>💰 Amount:</strong> ${response.data.amount}</p>
                        <p style="margin: 3px 0;"><strong>🔢 AutoPay Count:</strong> ${response.data.autopay_count}</p>
                        <p style="margin: 3px 0;"><strong>📅 Last Payment:</strong> ${response.data.last_payment}</p>
                        <p style="margin: 3px 0;"><strong>📅 Next Payment:</strong> ${response.data.next_payment}</p>
                    </div>
                    <p style="margin-top: 10px; font-size: 13px; color: #666;">${response.note}</p>
                `;
                $('#resultBox').removeClass('error').addClass('success').html(message).fadeIn();
                
                // Reload history
                setTimeout(function() {
                    loadHistory();
                }, 2000);
            } else {
                $('#resultBox').removeClass('success').addClass('error')
                    .html('<strong>❌ Error:</strong> ' + (response.message || 'Failed to simulate auto-debit')).fadeIn();
            }
        },
        error: function(xhr) {
            event.target.disabled = false;
            event.target.innerHTML = '🧪 Simulate';
            let errorMsg = xhr.responseJSON?.message || 'Failed to simulate auto-debit';
            $('#resultBox').removeClass('success').addClass('error')
                .html('<strong>❌ Error:</strong> ' + errorMsg).fadeIn();
        }
    });
}
</script>
