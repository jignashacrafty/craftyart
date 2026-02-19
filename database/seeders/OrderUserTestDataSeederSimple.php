<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderUserTestDataSeederSimple extends Seeder
{
    /**
     * Simple seeder with minimal fields
     * Run: php artisan db:seed --class=OrderUserTestDataSeederSimple
     */
    public function run()
    {
        echo "🌱 Seeding simple test data...\n\n";

        // 1. Create test users (minimal fields only)
        echo "1️⃣ Creating test users...\n";
        
        // User 1: Active
        $user1Exists = DB::connection('mysql')->table('user_data')
            ->where('email', 'testuser1@craftyart.com')
            ->exists();
        
        if (!$user1Exists) {
            DB::connection('mysql')->table('user_data')->insert([
                'uid' => 'test_user_001',
                'name' => 'Test User 1',
                'email' => 'testuser1@craftyart.com',
                'number' => '9876543210',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 0,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "   ✅ Created: testuser1@craftyart.com\n";
        } else {
            echo "   ⏭️  Already exists: testuser1@craftyart.com\n";
        }

        // User 2: Premium (without total_validity)
        $user2Exists = DB::connection('mysql')->table('user_data')
            ->where('email', 'testuser2@craftyart.com')
            ->exists();
        
        if (!$user2Exists) {
            DB::connection('mysql')->table('user_data')->insert([
                'uid' => 'test_user_002',
                'name' => 'Test User 2',
                'email' => 'testuser2@craftyart.com',
                'number' => '9876543211',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 1,
                'status' => 1,
                'validity' => now()->addDays(365)->format('Y-m-d H:i:s'),
                'subscription' => 1,
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ]);
            echo "   ✅ Created: testuser2@craftyart.com (Premium)\n";
        } else {
            echo "   ⏭️  Already exists: testuser2@craftyart.com\n";
        }

        // User 3: Inactive
        $user3Exists = DB::connection('mysql')->table('user_data')
            ->where('email', 'inactive@craftyart.com')
            ->exists();
        
        if (!$user3Exists) {
            DB::connection('mysql')->table('user_data')->insert([
                'uid' => 'test_user_003',
                'name' => 'Inactive User',
                'email' => 'inactive@craftyart.com',
                'number' => '9876543212',
                'refer_id' => strtoupper(Str::random(8)),
                'is_premium' => 0,
                'status' => 0, // Inactive
                'created_at' => now()->subDays(60),
                'updated_at' => now(),
            ]);
            echo "   ✅ Created: inactive@craftyart.com (Inactive)\n";
        } else {
            echo "   ⏭️  Already exists: inactive@craftyart.com\n";
        }

        // 2. Create test orders
        echo "\n2️⃣ Creating test orders...\n";
        
        $orders = [
            [
                'user_id' => 'test_user_001',
                'type' => 'new_sub',
                'plan_id' => '1',
                'amount' => 999,
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
            ],
            [
                'user_id' => 'test_user_001',
                'type' => 'new_sub',
                'plan_id' => '2',
                'amount' => 1499,
                'status' => 'failed',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'followup_call' => 1,
                'followup_note' => 'Customer interested',
                'followup_label' => 'interested',
            ],
            [
                'user_id' => 'test_user_002',
                'type' => 'old_sub',
                'plan_id' => '1',
                'amount' => 799,
                'status' => 'success',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
            ],
            [
                'user_id' => 'test_user_001',
                'type' => 'template',
                'plan_id' => 'template_001',
                'amount' => 299,
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
            ],
            [
                'user_id' => 'test_user_002',
                'type' => 'video',
                'plan_id' => 'video_001',
                'amount' => 499,
                'status' => 'pending',
                'crafty_id' => 'CRAFT_' . strtolower(Str::random(10)),
                'followup_call' => 1,
                'followup_label' => 'highly_interested',
            ],
        ];

        foreach ($orders as $order) {
            $orderData = array_merge([
                'emp_id' => 0,
                'paid' => 0,
                'currency' => 'INR',
                'razorpay_order_id' => 'RZP_' . strtolower(Str::random(10)),
                'contact_no' => $order['user_id'] === 'test_user_001' ? '9876543210' : '9876543211',
                'followup_call' => 0,
                'email_template_count' => 0,
                'whatsapp_template_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ], $order);

            DB::connection('mysql2')->table('orders')->insert($orderData);
            echo "   ✅ Order: {$order['crafty_id']} - {$order['status']}\n";
        }

        // 3. Create personal details
        echo "\n3️⃣ Creating personal details...\n";
        
        $details = [
            ['uid' => 'test_user_001', 'user_name' => 'Test User 1', 'usage' => 'professional'],
            ['uid' => 'test_user_002', 'user_name' => 'Test User 2', 'usage' => 'personal'],
        ];

        foreach ($details as $detail) {
            $exists = DB::connection('mysql2')->table('personal_details')
                ->where('uid', $detail['uid'])
                ->exists();
            
            if (!$exists) {
                DB::connection('mysql2')->table('personal_details')->insert(array_merge($detail, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
                echo "   ✅ Personal details: {$detail['uid']}\n";
            } else {
                echo "   ⏭️  Already exists: {$detail['uid']}\n";
            }
        }

        echo "\n✅ Test data seeding completed!\n\n";
        echo "📝 Test Users:\n";
        echo "   - testuser1@craftyart.com (Active)\n";
        echo "   - testuser2@craftyart.com (Premium)\n";
        echo "   - inactive@craftyart.com (Inactive)\n\n";
        echo "📮 Now import Postman collection and test!\n\n";
    }
}
