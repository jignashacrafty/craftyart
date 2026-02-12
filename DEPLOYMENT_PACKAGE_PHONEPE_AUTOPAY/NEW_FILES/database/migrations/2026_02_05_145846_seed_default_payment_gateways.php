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
        // Check if Cashfree already exists
        $cashfreeExists = DB::table('payment_configurations')
            ->where('gateway', 'Cashfree')
            ->exists();

        // Add default payment gateways if they don't exist
        $gateways = [
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Razorpay',
                'credentials' => json_encode([
                    'key_id' => '',
                    'key_secret' => '',
                    'merchant_id' => ''
                ]),
                'payment_types' => json_encode([]),
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'PhonePe',
                'credentials' => json_encode([
                    'client_id' => '',
                    'salt_key' => '',
                    'merchant_id' => '',
                    'salt_index' => '',
                    'webhook_username' => '',
                    'webhook_password' => ''
                ]),
                'payment_types' => json_encode([]),
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'payment_scope' => 'INTERNATIONAL',
                'gateway' => 'Stripe',
                'credentials' => json_encode([
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => ''
                ]),
                'payment_types' => json_encode([]),
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // If Cashfree doesn't exist, add it too
        if (!$cashfreeExists) {
            $gateways[] = [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Cashfree',
                'credentials' => json_encode([
                    'api_key' => '',
                    'secret_key' => ''
                ]),
                'payment_types' => json_encode([]),
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert only gateways that don't already exist
        foreach ($gateways as $gateway) {
            $exists = DB::table('payment_configurations')
                ->where('gateway', $gateway['gateway'])
                ->exists();
            
            if (!$exists) {
                DB::table('payment_configurations')->insert($gateway);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the seeded gateways
        DB::table('payment_configurations')
            ->whereIn('gateway', ['Razorpay', 'PhonePe', 'Stripe'])
            ->delete();
    }
};
