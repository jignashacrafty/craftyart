<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderUserTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds for testing Order User APIs
     * 
     * Run this seeder: php artisan db:seed --class=OrderUserTestDataSeeder
     */
    public function run()
    {
        echo "🌱 Seeding test data for Order User APIs...\n\n";

        // 1. Create test users in user_data table
        echo "1️⃣ Creating test users...\n";
        $testUsers = [
            [
                'uid' => 'test_user_001',
                'name' => 'Test User 1',
                'email' => 'testuser1@craftyart.com',
                'number' => '9876543210',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 0,
                'can_update' => 1,
                'web_update' => 0,
                'cheap_rate' => 0,
                'status' => 1, // Active
                'creator' => 0,
                'profile_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uid' => 'test_user_002',
                'name' => 'Test User 2',
                'email' => 'testuser2@craftyart.com',
                'number' => '9876543211',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 1,
                'can_update' => 1,
                'web_update' => 0,
                'cheap_rate' => 0,
                'status' => 1,
                'creator' => 0,
                'profile_count' => 0,
                'validity' => now()->addDays(365)->format('Y-m-d H:i:s'),
                'subscription' => 1,
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
            [
                'uid' => 'test_user_003',
                'name' => 'Inactive User',
                'email' => 'inactive@craftyart.com',
                'number' => '9876543212',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 0,
                'can_update' => 1,
                'web_update' => 0,
                'cheap_rate' => 0,
                'status' => 0, // Inactive
                'creator' => 0,
                'profile_count' => 0,
                'created_at' => now()->subDays(60),
                'updated_at' => now(),
            ],
        ];

        foreach ($testUsers as $user) {
            // Check if user already exists
            $exists = DB::connection('mysql')->table('user_data')
                ->where('email', $user['email'])
                ->exists();
            
            if (!$exists) {
                DB::connection('mysql')->table('user_data')->insert($user);
                echo "   ✅ Created user: {$user['email']}\n";
            } else {
                echo "   ⏭️  User already exists: {$user['email']}\n";
            }
        }

        // 2. Create test orders
        echo "\n2️⃣ Creating test orders...\n";
        $testOrders = [
            [
                'user_id' => 'test_user_001',
                'emp_id' => 0,
                'type' => 'new_sub',
                'plan_id' => '1',
                'amount' => 999,
                'paid' => 0,
                'currency' => 'INR',
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => '9876543210',
                'followup_call' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 'test_user_001',
                'emp_id' => 0,
                'type' => 'new_sub',
                'plan_id' => '2',
                'amount' => 1499,
                'paid' => 0,
                'currency' => 'INR',
                'status' => 'failed',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => '9876543210',
                'followup_call' => 1,
                'followup_note' => 'Customer interested, will call back',
                'followup_label' => 'interested',
                'email_template_count' => 1,
                'whatsapp_template_count' => 1,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(1),
            ],
            [
                'user_id' => 'test_user_002',
                'emp_id' => 0,
                'type' => 'old_sub',
                'plan_id' => '1',
                'amount' => 799,
                'paid' => 799,
                'currency' => 'INR',
                'status' => 'success',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'razorpay_payment_id' => 'pay_' . strtolower(Str::random(14)),
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => '9876543211',
                'followup_call' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
            [
                'user_id' => 'test_user_001',
                'emp_id' => 0,
                'type' => 'template',
                'plan_id' => 'template_001',
                'amount' => 299,
                'paid' => 0,
                'currency' => 'INR',
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => '9876543210',
                'followup_call' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ],
            [
                'user_id' => 'test_user_002',
                'emp_id' => 0,
                'type' => 'video',
                'plan_id' => 'video_001',
                'amount' => 499,
                'paid' => 0,
                'currency' => 'INR',
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => '9876543211',
                'followup_call' => 1,
                'followup_note' => 'Highly interested in video templates',
                'followup_label' => 'highly_interested',
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(4),
            ],
        ];

        foreach ($testOrders as $order) {
            DB::connection('mysql2')->table('orders')->insert($order);
            echo "   ✅ Created order: {$order['crafty_id']} - {$order['status']}\n";
        }

        // 3. Create personal details
        echo "\n3️⃣ Creating personal details...\n";
        $personalDetails = [
            [
                'uid' => 'test_user_001',
                'user_name' => 'Test User 1',
                'usage' => 'professional',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uid' => 'test_user_002',
                'user_name' => 'Test User 2',
                'usage' => 'personal',
                'created_at' => now()->subDays(30),
                'updated_at' => now()->subDays(30),
            ],
        ];

        foreach ($personalDetails as $detail) {
            $exists = DB::connection('mysql2')->table('personal_details')
                ->where('uid', $detail['uid'])
                ->exists();
            
            if (!$exists) {
                DB::connection('mysql2')->table('personal_details')->insert($detail);
                echo "   ✅ Created personal details for: {$detail['uid']}\n";
            } else {
                echo "   ⏭️  Personal details already exist for: {$detail['uid']}\n";
            }
        }

        echo "\n✅ Test data seeding completed!\n\n";
        echo "📝 Test Data Summary:\n";
        echo "   - 3 test users created (2 active, 1 inactive)\n";
        echo "   - 5 test orders created (3 pending, 1 failed, 1 success)\n";
        echo "   - 2 personal details records created\n\n";
        
        echo "🧪 You can now test the APIs with:\n";
        echo "   - Email: testuser1@craftyart.com (Active user)\n";
        echo "   - Email: testuser2@craftyart.com (Active premium user)\n";
        echo "   - Email: inactive@craftyart.com (Inactive user)\n\n";
        
        echo "📮 Import Postman collection and start testing!\n";
        echo "   File: ORDER_USER_API_POSTMAN_COLLECTION_COMPLETE.json\n\n";
    }
}
