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
        // Fix Razorpay credentials - decode double-encoded JSON and fix key names
        $razorpay = DB::table('payment_configurations')
            ->where('gateway', 'razorpay')
            ->first();

        if ($razorpay) {
            $credentials = $razorpay->credentials;
            
            // Decode if it's double-encoded
            if (is_string($credentials)) {
                $decoded = json_decode($credentials, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                
                // Map old keys to new keys
                $newCredentials = [];
                if (isset($decoded['RAZORPAY_KEY'])) {
                    $newCredentials['key_id'] = $decoded['RAZORPAY_KEY'];
                }
                if (isset($decoded['RAZORPAY_SECRET'])) {
                    $newCredentials['key_secret'] = $decoded['RAZORPAY_SECRET'];
                }
                
                // If no mapping needed, keep original
                if (empty($newCredentials) && is_array($decoded)) {
                    $newCredentials = $decoded;
                }
                
                // Update with properly encoded credentials
                DB::table('payment_configurations')
                    ->where('gateway', 'razorpay')
                    ->update([
                        'gateway' => 'Razorpay',
                        'credentials' => json_encode($newCredentials)
                    ]);
            }
        }

        // Fix Cashfree credentials if double-encoded
        $cashfree = DB::table('payment_configurations')
            ->where('gateway', 'Cashfree')
            ->first();

        if ($cashfree) {
            $credentials = $cashfree->credentials;
            
            if (is_string($credentials)) {
                $decoded = json_decode($credentials, true);
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                }
                
                if (is_array($decoded)) {
                    DB::table('payment_configurations')
                        ->where('gateway', 'Cashfree')
                        ->update([
                            'credentials' => json_encode($decoded)
                        ]);
                }
            }
        }

        // Fix any other double-encoded credentials
        $configs = DB::table('payment_configurations')->get();
        
        foreach ($configs as $config) {
            if (is_string($config->credentials)) {
                $decoded = json_decode($config->credentials, true);
                
                // Check if it's double-encoded
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                    
                    if (is_array($decoded)) {
                        DB::table('payment_configurations')
                            ->where('id', $config->id)
                            ->update([
                                'credentials' => json_encode($decoded)
                            ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we don't know the original state
    }
};
