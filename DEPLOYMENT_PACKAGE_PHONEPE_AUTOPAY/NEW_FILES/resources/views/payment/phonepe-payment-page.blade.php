<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PhonePe Payment - CraftyArt</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #5f259f 0%, #3d1766 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .phonepe-logo {
            width: 120px;
            height: 40px;
            background: white;
            border-radius: 8px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #5f259f;
            font-size: 18px;
        }
        
        .payment-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .payment-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            color: #6c757d;
            font-size: 14px;
        }
        
        .detail-value {
            color: #212529;
            font-weight: 600;
            font-size: 14px;
        }
        
        .amount-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 24px;
            font-weight: bold;
        }
        
        .pay-button {
            width: 100%;
            background: linear-gradient(135deg, #5f259f 0%, #3d1766 100%);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .pay-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(95, 37, 159, 0.3);
        }
        
        .pay-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .pay-button.loading .spinner {
            display: block;
        }
        
        .pay-button.loading .button-text {
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .security-badge {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .security-badge p {
            color: #6c757d;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        
        .lock-icon {
            color: #28a745;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
            font-size: 14px;
        }
        
        .success-badge {
            background: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (max-width: 480px) {
            .payment-container {
                border-radius: 0;
            }
            
            .payment-header {
                padding: 20px;
            }
            
            .payment-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-header">
            <div class="phonepe-logo">PhonePe</div>
            <h1>Complete Payment</h1>
            <p>Secure payment powered by PhonePe</p>
        </div>
        
        <div class="payment-body">
            @if($sale->status === 'paid')
                <div class="success-badge">
                    <span>✓</span>
                    <span>This payment has already been completed</span>
                </div>
            @endif
            
            <div class="error-message" id="errorMessage"></div>
            
            <div class="payment-details">
                <div class="detail-row">
                    <span class="detail-label">Customer Name</span>
                    <span class="detail-value">{{ $sale->user_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">{{ $sale->email }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Mobile</span>
                    <span class="detail-value">{{ $sale->contact_no }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Plan Type</span>
                    <span class="detail-value">{{ ucfirst($sale->plan_type) }} Use</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount to Pay</span>
                    <span class="amount-highlight">₹{{ number_format($sale->amount, 2) }}</span>
                </div>
            </div>
            
            @if($sale->status !== 'paid')
                <button class="pay-button" id="payButton" onclick="initiatePayment()">
                    <div class="spinner"></div>
                    <span class="button-text">Proceed to Pay ₹{{ number_format($sale->amount, 2) }}</span>
                </button>
            @else
                <button class="pay-button" disabled>
                    <span>Payment Already Completed</span>
                </button>
            @endif
            
            <div class="security-badge">
                <p>
                    <span class="lock-icon">🔒</span>
                    <span>Secured by PhonePe | Your payment is safe and encrypted</span>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        const referenceId = '{{ $sale->reference_id }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function initiatePayment() {
            const button = document.getElementById('payButton');
            button.classList.add('loading');
            button.disabled = true;
            
            fetch('{{ route("phonepe.initiate_payment") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    reference_id: referenceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.redirect_url) {
                    // Redirect to PhonePe payment page
                    window.location.href = data.redirect_url;
                } else {
                    button.classList.remove('loading');
                    button.disabled = false;
                    showError(data.message || 'Failed to initiate payment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Payment initiation error:', error);
                button.classList.remove('loading');
                button.disabled = false;
                showError('Network error. Please check your connection and try again.');
            });
        }
    </script>
</body>
</html>
