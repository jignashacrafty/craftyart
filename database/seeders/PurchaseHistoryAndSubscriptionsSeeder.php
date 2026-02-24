<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseHistoryAndSubscriptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Purchase History ane User Subscriptions ma proper relationship sathe fake data add kare che
     * 10 users mate data generate thase - har user na existing user_data table ma hovu joiye
     *
     * @return void
     */
    public function run()
    {
        // First, get existing user IDs from user_data table
        $existingUsers = DB::table('user_data')->select('uid')->limit(10)->get();

        if ($existingUsers->isEmpty()) {
            $this->command->error('❌ user_data table ma koi users nathi! Pehla users add karo.');
            return;
        }

        $this->command->info('🔍 ' . $existingUsers->count() . ' users malya user_data table ma');

        // Get subscription plans
        $plans = DB::connection('mysql')->table('subscriptions')->get();

        if ($plans->isEmpty()) {
            $this->command->error('❌ subscriptions table ma koi plans nathi! Pehla SubscriptionPlansSeeder run karo.');
            return;
        }

        $this->command->info('📦 ' . $plans->count() . ' subscription plans malya');

        $purchaseHistoryEntries = [];
        $userSubscriptionEntries = [];
        $timestamp = time();

        foreach ($existingUsers as $index => $user) {
            $userId = $user->uid;

            // Random plan select karo (Free plan skip karo)
            $plan = $plans->where('package_name', '!=', 'Free')->random();

            // Purchase date - last 30 days ma random
            $purchaseDate = Carbon::now()->subDays(rand(1, 30));

            // Expiry date calculate karo plan validity thi
            $expiryDate = (clone $purchaseDate)->addDays($plan->validity);

            // Payment methods
            $paymentMethods = ['PhonePe', 'Razorpay', 'UPI', 'Card', 'NetBanking'];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            // Payment status - 80% success, 20% pending/failed
            $isSuccess = rand(1, 100) <= 80;
            $paymentStatus = $isSuccess ? 1 : 0;
            $status = $isSuccess ? 1 : 0;

            // Transaction IDs generate karo
            $transactionId = 'TXN' . $timestamp . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $paymentId = 'PAY' . $timestamp . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

            // PhonePe specific fields (50% chance)
            $isPhonePe = $paymentMethod === 'PhonePe' && rand(0, 1) === 1;
            $phonePeFields = [];

            if ($isPhonePe) {
                $phonePeFields = [
                    'phonepe_merchant_order_id' => 'MERCHANT_' . $timestamp . '_' . ($index + 1),
                    'phonepe_subscription_id' => 'SUB_' . $timestamp . '_' . ($index + 1),
                    'phonepe_order_id' => 'ORDER_' . $timestamp . '_' . ($index + 1),
                    'phonepe_transaction_id' => 'PHONEPE_TXN_' . $timestamp . '_' . ($index + 1),
                    'is_autopay_enabled' => rand(0, 1),
                    'autopay_status' => rand(0, 1) ? 'ACTIVE' : null,
                    'autopay_activated_at' => rand(0, 1) ? $purchaseDate->format('Y-m-d H:i:s') : null,
                    'next_autopay_date' => rand(0, 1) ? $expiryDate->format('Y-m-d') : null,
                    'autopay_count' => rand(0, 5),
                ];
            }

            // Contact number
            $contactNo = '98765432' . str_pad($index, 2, '0', STR_PAD_LEFT);

            // Purchase History Entry
            $purchaseHistoryEntries[] = array_merge([
                'user_id' => $userId,
                'product_id' => $plan->id,
                'product_type' => 1, // 1 = subscription
                'subscription_id' => null, // Will be updated after user_subscription is created
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId,
                'currency_code' => 'INR',
                'amount' => $plan->price,
                'payment_method' => $paymentMethod,
                'from_where' => rand(0, 1) ? 'Web' : 'Mobile',
                'isManual' => 0,
                'status' => $status,
                'contact_no' => $contactNo,
                'payment_status' => $paymentStatus,
                'created_at' => $purchaseDate->format('Y-m-d H:i:s'),
                'updated_at' => $purchaseDate->format('Y-m-d H:i:s'),
            ], $phonePeFields);

            // User Subscription Entry (manage_subscriptions table)
            // Only successful payments mate subscription create thase
            if ($isSuccess) {
                $userSubscriptionEntries[] = [
                    'user_id' => $userId,
                    'is_base_price' => $plan->is_base_price,
                    'package_name' => $plan->package_name,
                    'desc' => $plan->desc,
                    'validity' => $plan->validity,
                    'actual_price' => $plan->actual_price,
                    'actual_price_dollar' => $plan->actual_price_dollar,
                    'price' => $plan->price,
                    'price_dollar' => $plan->price_dollar,
                    'months' => $plan->months,
                    'has_offer' => $plan->has_offer,
                    'sequence_number' => $plan->sequence_number,
                    'status' => 1, // Active subscription
                    'created_at' => $purchaseDate->format('Y-m-d H:i:s'),
                    'updated_at' => $purchaseDate->format('Y-m-d H:i:s'),
                ];
            }
        }

        // Insert purchase history entries
        DB::table('purchase_history')->insert($purchaseHistoryEntries);
        $this->command->info('✅ ' . count($purchaseHistoryEntries) . ' entries purchase_history table ma add thaya!');

        // Insert user subscription entries
        if (!empty($userSubscriptionEntries)) {
            DB::connection('mysql')->table('manage_subscriptions')->insert($userSubscriptionEntries);
            $this->command->info('✅ ' . count($userSubscriptionEntries) . ' entries manage_subscriptions table ma add thaya!');
        }

        // Summary
        $this->command->info('');
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->info('Total Users: ' . $existingUsers->count());
        $this->command->info('Purchase History Entries: ' . count($purchaseHistoryEntries));
        $this->command->info('User Subscriptions: ' . count($userSubscriptionEntries));
        $this->command->info('');

        // Payment method wise breakdown
        $paymentMethodCounts = [];
        foreach ($purchaseHistoryEntries as $entry) {
            $method = $entry['payment_method'];
            $paymentMethodCounts[$method] = ($paymentMethodCounts[$method] ?? 0) + 1;
        }

        $this->command->info('Payment Methods:');
        foreach ($paymentMethodCounts as $method => $count) {
            $this->command->info("  - $method: $count");
        }

        $this->command->info('');
        $this->command->info('✅ Badha data successfully add thaya!');
        $this->command->info('🎉 Purchase History ane User Subscriptions ready che!');
    }
}
