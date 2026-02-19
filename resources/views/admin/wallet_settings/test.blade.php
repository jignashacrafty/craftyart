<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
</head>
<body>
    <h1>Test - Wallet Settings</h1>
    <p>Settings count: {{ $settings->count() }}</p>
    @foreach($settings as $setting)
        <div>
            <h3>{{ $setting->setting_name }}</h3>
            <p>Key: {{ $setting->setting_key }}</p>
            <p>Min: ₹{{ $setting->min_withdrawal_threshold }}</p>
            <p>Max: ₹{{ $setting->max_withdrawal_limit }}</p>
        </div>
    @endforeach
</body>
</html>
