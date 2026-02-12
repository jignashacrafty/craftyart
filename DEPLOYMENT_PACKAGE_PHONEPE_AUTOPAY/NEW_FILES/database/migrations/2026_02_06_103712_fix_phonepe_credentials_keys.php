<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix PhonePe gateway name and credentials
        $phonepe = DB::table('payment_configurations')
            ->where('gateway', 'LIKE', '%phonepe%')
            ->first();

        if ($phonepe) {
            $credentials = json_decode($phonepe->credentials, true);
            
            // Map old keys to new keys
            $newCredentials = [
                'merchant_id' => $credentials['PHONEPE_MERCHANT_ID'] ?? $credentials['merchant_id'] ?? '',
                'client_id' => $credentials['PHONEPE_CLIENT_ID'] ?? $credentials['client_id'] ?? '',
                'salt_key' => $credentials['PHONEPE_SALT_KEY'] ?? $credentials['salt_key'] ?? '',
                'salt_index' => $credentials['PHONEPE_SALT_INDEX'] ?? $credentials['salt_index'] ?? '1',
                'webhook_username' => $credentials['PHONE_PE_WEBHOOK_USERNAME'] ?? $credentials['webhook_username'] ?? '',
                'webhook_password' => $credentials['PHONE_PE_WEBHOOK_PASS'] ?? $credentials['webhook_password'] ?? '',
                'environment' => strtolower($credentials['PHONEPE_ENV'] ?? $credentials['environment'] ?? 'sandbox'),
            ];
            
            DB::table('payment_configurations')
                ->where('id', $phonepe->id)
                ->update([
                    'gateway' => 'PhonePe',
                    'credentials' => json_encode($newCredentials)
                ]);
        }
        
        // Fix Razorpay gateway name if needed
        DB::table('payment_configurations')
            ->where('gateway', 'razorpay')
            ->update(['gateway' => 'Razorpay']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse
    }
};
