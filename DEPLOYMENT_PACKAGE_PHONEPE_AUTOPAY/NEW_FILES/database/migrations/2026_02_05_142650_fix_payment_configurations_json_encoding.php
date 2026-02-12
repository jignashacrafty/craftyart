<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix double-encoded JSON data
        $configs = DB::table('payment_configurations')->get();
        
        foreach ($configs as $config) {
            $credentials = $config->credentials;
            $paymentTypes = $config->payment_types;
            
            // Check if credentials is double-encoded
            if (is_string($credentials)) {
                $decoded = json_decode($credentials, true);
                if (is_string($decoded)) {
                    // It's double-encoded, decode again
                    $credentials = json_decode($decoded, true);
                } else {
                    $credentials = $decoded;
                }
            }
            
            // Check if payment_types is double-encoded
            if (is_string($paymentTypes)) {
                $decoded = json_decode($paymentTypes, true);
                if (is_string($decoded)) {
                    // It's double-encoded, decode again
                    $paymentTypes = json_decode($decoded, true);
                } else {
                    $paymentTypes = $decoded;
                }
            }
            
            // Update with properly encoded JSON
            DB::table('payment_configurations')
                ->where('id', $config->id)
                ->update([
                    'credentials' => json_encode($credentials),
                    'payment_types' => json_encode($paymentTypes),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};
