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
        // Update PhonePe credentials to include OAuth fields
        $phonepe = DB::table('payment_configurations')
            ->where('gateway', 'PhonePe')
            ->first();

        if ($phonepe) {
            $credentials = json_decode($phonepe->credentials, true);
            
            // Add OAuth credentials from .env
            $credentials['client_secret'] = env('PHONEPE_CLIENT_SECRET', 'ZWM3ZTQ5YTQtMDFlMi00N2M1LTk3YWEtNTMwMDgyNzI2Njhm');
            $credentials['client_version'] = env('PHONEPE_CLIENT_VERSION', '1');
            
            DB::table('payment_configurations')
                ->where('id', $phonepe->id)
                ->update([
                    'credentials' => json_encode($credentials)
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
