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
     * Database: crafty_revenue
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
            
            // Contact number
            $contactNo = '98765432' . str_pad($index, 2, '0', STR_PAD_LEFT);
            
            // Purchase History Entry - crafty_revenue database fields
            $purchaseEntry = [
                'user_id' => $userId,
                'contact_no' => $contactNo,
                'product_id' => $plan->id,
                'product_type' => 1, // 1 = subscription
                'order_id' => null,
                'transaction_id' => $transactionId,
                'payment_id' => $paymentId,
                'currency_code' => 'INR',
                'amount' => $plan->price,
                'paid_amount' => $plan->price,
                'net_amount' => $plan->price,
                'promo_code_id' => 0,
                'payment_method' => $paymentMethod,
                'from_where' => rand(0, 1) ? 'Web' : 'Mobile',
                'fbc' => null,
                'gclid' => null,
                'isManual' => 0,
                'payment_status' => $paymentStatus,
                'status' => $status,
                'email_sent' => 0,
                'wp_sent' => 0,
                'used' => 0,
                'created_at' => $purchaseDate->format('Y-m-d H:i:s'),
                'updated_at' => $purchaseDate->format('Y-m-d H:i:s'),
            ];
            
            // Insert purchase history entry in crafty_revenue database
            DB::connection('crafty_revenue_mysql')->table('purchase_history')->insert($purchaseEntry);
            
            // User Subscription Entry (user_subscriptions table)
            // Only successful payments mate subscription create thase
            if ($isSuccess) {
                $userSubscriptionEntries[] = [
                    'user_id' => $userId,
                    'plan_id' => $plan->id,
                    'payment_gateway' => $paymentMethod,
                    'gateway_subscription_id' => $transactionId,
                    'currency' => 'INR',
                    'first_amount' => $plan->price,
                    'amount' => $plan->price,
                    'status' => 'ACTIVE',
                    'is_trial' => 0,
                    'trial_start' => null,
                    'trial_end' => null,
                    'current_start' => $purchaseDate->format('Y-m-d H:i:s'),
                    'current_end' => $expiryDate->format('Y-m-d H:i:s'),
                    'total_count' => null,
                    'paid_count' => 1,
                    'is_final' => 1,
                    'created_at' => $purchaseDate->format('Y-m-d H:i:s'),
                    'updated_at' => $purchaseDate->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->command->info('✅ ' . $existingUsers->count() . ' entries purchase_history table (crafty_revenue) ma add thaya!');

        // Insert user subscription entries one by one (to handle auto-increment)
        $subscriptionCount = 0;
        if (!empty($userSubscriptionEntries)) {
            foreach ($userSubscriptionEntries as $entry) {
                try {
                    // user_subscriptions table crafty_revenue_mysql connection ma che
                    DB::connection('crafty_revenue_mysql')->table('user_subscriptions')->insert($entry);
                    $subscriptionCount++;
                } catch (\Exception $e) {
                    $this->command->warn('⚠️ Subscription entry skip thayo: ' . $e->getMessage());
                }
            }
            $this->command->info('✅ ' . $subscriptionCount . ' entries user_subscriptions table (crafty_revenue) ma add thaya!');
        }

        // Summary
        $this->command->info('');
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->info('Database: crafty_revenue');
        $this->command->info('Total Users: ' . $existingUsers->count());
        $this->command->info('Purchase History Entries: ' . $existingUsers->count());
        $this->command->info('User Subscriptions: ' . $subscriptionCount);
        $this->command->info('');
        $this->command->info('✅ Badha data successfully add thaya!');
        $this->command->info('🎉 Purchase History ane User Subscriptions ready che!');
    }
}
