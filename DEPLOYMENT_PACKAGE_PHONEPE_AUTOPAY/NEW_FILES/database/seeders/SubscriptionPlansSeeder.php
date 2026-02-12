<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use Carbon\Carbon;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $this->command->info('Seeding Subscription Plans...');

        // ============================================
        // SUBSCRIPTION PLANS (subscriptions table)
        // ============================================
        
        // Free Plan
        Subscription::updateOrCreate(
            ['package_name' => 'Free'],
            [
                'is_base_price' => 0,
                'package_name' => 'Free',
                'desc' => 'Utilize Your Endless Creativity with Crafty Art Unlimited Downloads, Save Limitlessly!',
                'validity' => 0, // Lifetime
                'actual_price' => 0,
                'actual_price_dollar' => 0,
                'price' => 0,
                'price_dollar' => 0,
                'months' => 0,
                'has_offer' => 0,
                'sequence_number' => 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Crafty Art Pro - Monthly
        Subscription::updateOrCreate(
            ['package_name' => 'Crafty Art Pro - Monthly'],
            [
                'is_base_price' => 0,
                'package_name' => 'Crafty Art Pro - Monthly',
                'desc' => 'Utilize Your Endless Creativity with Crafty Art Unlimited Downloads, No Limitations!',
                'validity' => 30,
                'actual_price' => 1999,
                'actual_price_dollar' => 25,
                'price' => 1499,
                'price_dollar' => 19,
                'months' => 1,
                'has_offer' => 1,
                'sequence_number' => 2,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Crafty Art Pro - 6 Months
        Subscription::updateOrCreate(
            ['package_name' => 'Crafty Art Pro - 6 Months'],
            [
                'is_base_price' => 0,
                'package_name' => 'Crafty Art Pro - 6 Months',
                'desc' => 'Go Premium - Unlock Advanced Features & Full Access for 6 Months',
                'validity' => 180,
                'actual_price' => 8999,
                'actual_price_dollar' => 115,
                'price' => 6999,
                'price_dollar' => 89,
                'months' => 6,
                'has_offer' => 1,
                'sequence_number' => 3,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Crafty Art Pro - Yearly (Best Value)
        Subscription::updateOrCreate(
            ['package_name' => 'Crafty Art Pro - Yearly'],
            [
                'is_base_price' => 0,
                'package_name' => 'Crafty Art Pro - Yearly',
                'desc' => 'Annual Plan - Limited Time Offer! Best Value (25% off)',
                'validity' => 365,
                'actual_price' => 14999,
                'actual_price_dollar' => 199,
                'price' => 11999,
                'price_dollar' => 149,
                'months' => 12,
                'has_offer' => 1,
                'sequence_number' => 4,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ Subscription plans seeded successfully!');

        // Summary
        $this->command->info('');
        $this->command->info('=== SEEDING SUMMARY ===');
        $this->command->info('Total Plans: ' . Subscription::count());
        $this->command->info('');
        $this->command->info('Plans by duration:');
        $this->command->info('  - Free: ' . Subscription::where('months', 0)->count());
        $this->command->info('  - Monthly (1 month): ' . Subscription::where('months', 1)->count());
        $this->command->info('  - 6 Months: ' . Subscription::where('months', 6)->count());
        $this->command->info('  - Yearly (12 months): ' . Subscription::where('months', 12)->count());
        $this->command->info('');
        $this->command->info('✅ All subscription plans ready!');
        $this->command->info('🎉 Ready for testing PhonePe AutoPay!');
    }
}
