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
        // Get all payment configurations
        $configs = DB::table('payment_configurations')->get();
        
        foreach ($configs as $config) {
            $needsUpdate = false;
            $updates = [];
            
            // Fix gateway name casing
            if ($config->gateway === 'razorpay') {
                $updates['gateway'] = 'Razorpay';
                $needsUpdate = true;
            }
            
            // Fix double-encoded credentials
            if (is_string($config->credentials)) {
                $decoded = json_decode($config->credentials, true);
                
                // Check if it's double-encoded (decoded value is still a string)
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                    
                    if (is_array($decoded)) {
                        $updates['credentials'] = json_encode($decoded);
                        $needsUpdate = true;
                    }
                }
            }
            
            // Fix double-encoded payment_types
            if (is_string($config->payment_types)) {
                $decoded = json_decode($config->payment_types, true);
                
                // Check if it's double-encoded
                if (is_string($decoded)) {
                    $decoded = json_decode($decoded, true);
                    
                    if (is_array($decoded)) {
                        $updates['payment_types'] = json_encode($decoded);
                        $needsUpdate = true;
                    }
                }
            }
            
            // Update if needed
            if ($needsUpdate) {
                DB::table('payment_configurations')
                    ->where('id', $config->id)
                    ->update($updates);
            }
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
