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
        // Fix PhonePe credentials with correct keys
        $phonepe = DB::table('payment_configurations')
            ->where('gateway', 'LIKE', '%phonepe%')
            ->first();

        if ($phonepe) {
            $credentials = json_decode($phonepe->credentials, true);
            
            // Create new credentials with correct keys
            $newCredentials = [
                'merchant_id' => $credentials['merchant_id'] ?? 'M23LAMPVYPELC',
                'client_id' => $credentials['Client Id'] ?? $credentials['client_id'] ?? 'M23LAMPVYPELC_2602021028',
                'client_secret' => $credentials['Client Secret'] ?? $credentials['client_secret'] ?? 'ZWM3ZTQ5YTQtMDFlMi00N2M1LTk3YWEtNTMwMDgyNzI2Njhm',
                'client_version' => $credentials['Client Version'] ?? $credentials['client_version'] ?? '1',
                'salt_key' => $credentials['salt_key'] ?? 'NTc5YWFmZjEtYmQ2NS00Njg5LThiZTAtNDYwZDI5YzM3ZWIw',
                'salt_index' => $credentials['salt_index'] ?? '1',
                'webhook_username' => $credentials['webhook_username'] ?? 'sanjay_test',
                'webhook_password' => $credentials['webhook_password'] ?? '123456',
                'environment' => $credentials['environment'] ?? 'sandbox',
            ];
            
            DB::table('payment_configurations')
                ->where('id', $phonepe->id)
                ->update([
                    'gateway' => 'PhonePe',
                    'credentials' => json_encode($newCredentials)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse
    }
};
