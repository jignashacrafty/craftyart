<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Pricing\PaymentConfiguration;
use Carbon\Carbon;

class PaymentConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Razorpay Configuration (Predefined empty fields)
        $razorpay = PaymentConfiguration::updateOrCreate(
            ['gateway' => 'Razorpay'],
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Razorpay',
                'credentials' => [
                    'key_id' => '',
                    'key_secret' => '',
                    'webhook_secret' => '',
                    'webhook_url' => url('/api/razorpay/webhook')
                ],
                'payment_types' => [],
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // PhonePe Configuration (Predefined empty fields)
        $phonepe = PaymentConfiguration::updateOrCreate(
            ['gateway' => 'PhonePe'],
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'PhonePe',
                'credentials' => [
                    'merchant_id' => '',
                    'client_id' => '',
                    'client_secret' => '',
                    'client_version' => '1',
                    'salt_key' => '',
                    'salt_index' => '1',
                    'webhook_username' => '',
                    'webhook_password' => '',
                    'webhook_url' => url('/api/phonepe/webhook'),
                    'environment' => 'sandbox'
                ],
                'payment_types' => [],
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Cashfree Configuration (placeholder)
        $cashfree = PaymentConfiguration::updateOrCreate(
            ['gateway' => 'Cashfree'],
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Cashfree',
                'credentials' => [
                    'api_key' => '',
                    'secret_key' => '',
                    'webhook_url' => url('/api/cashfree/webhook')
                ],
                'payment_types' => [],
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Easebuzz Configuration (placeholder)
        $easebuzz = PaymentConfiguration::updateOrCreate(
            ['gateway' => 'Easebuzz'],
            [
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Easebuzz',
                'credentials' => [
                    'merchant_key' => '',
                    'salt' => '',
                    'environment' => 'production',
                    'webhook_url' => url('/api/easebuzz/webhook')
                ],
                'payment_types' => [],
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Stripe Configuration (placeholder)
        $stripe = PaymentConfiguration::updateOrCreate(
            ['gateway' => 'Stripe'],
            [
                'payment_scope' => 'INTERNATIONAL',
                'gateway' => 'Stripe',
                'credentials' => [
                    'publishable_key' => '',
                    'secret_key' => '',
                    'webhook_secret' => '',
                    'webhook_url' => url('/api/stripe/webhook')
                ],
                'payment_types' => [],
                'is_active' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('Payment configurations seeded successfully!');
        $this->command->info('All gateways created with predefined empty credential fields');
        $this->command->info('Users can now add credentials through the UI');
    }
}
