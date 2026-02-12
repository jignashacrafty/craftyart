<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Crafty Art</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out 0.2s both;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }

        .success-icon i {
            font-size: 50px;
            color: white;
        }

        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .success-message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 600;
        }

        .amount {
            font-size: 24px;
            color: #667eea;
        }

        .btn-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 150px;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
        }

        .reference-id {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .reference-id strong {
            color: #856404;
        }

        @media (max-width: 600px) {
            .success-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 24px;
            }

            .btn-container {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>

        <h1>Payment Successful!</h1>
        <p class="success-message">
            Thank you for your payment. Your subscription has been activated successfully.
        </p>

        @if(isset($referenceId))
        <div class="reference-id">
            <strong>Reference ID:</strong> {{ $referenceId }}
        </div>
        @endif

        @if(isset($sale))
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Customer Name</span>
                <span class="detail-value">{{ $sale->user_name }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email</span>
                <span class="detail-value">{{ $sale->email }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact</span>
                <span class="detail-value">{{ $sale->contact_no }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Plan Type</span>
                <span class="detail-value">{{ ucfirst($sale->plan_type) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Amount Paid</span>
                <span class="detail-value amount">₹{{ number_format($sale->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Payment Method</span>
                <span class="detail-value">{{ ucfirst($sale->payment_method) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status</span>
                <span class="detail-value" style="color: #28a745;">
                    <i class="fas fa-check-circle"></i> Paid
                </span>
            </div>
        </div>
        @endif

        <div class="btn-container">
            @if(isset($sale) && $sale->order_id)
            <a href="{{ url('/order_user#order-' . $sale->order_id) }}" class="btn btn-primary" onclick="openPurchaseHistory({{ $sale->order_id }}, '{{ $sale->user_name }}')">
                <i class="fas fa-history"></i> View Purchase History
            </a>
            @else
            <a href="{{ url('/order_user') }}" class="btn btn-primary">
                <i class="fas fa-list"></i> View Orders
            </a>
            @endif
            <a href="{{ url('/') }}" class="btn btn-secondary">
                <i class="fas fa-home"></i> Go to Dashboard
            </a>
        </div>
    </div>
    
    @if(isset($sale) && $sale->order_id)
    <script>
        function openPurchaseHistory(orderId, userName) {
            // Store in sessionStorage to open purchase history on page load
            sessionStorage.setItem('openPurchaseHistory', JSON.stringify({
                orderId: orderId,
                userName: userName
            }));
        }
    </script>
    @endif
</body>
</html>
