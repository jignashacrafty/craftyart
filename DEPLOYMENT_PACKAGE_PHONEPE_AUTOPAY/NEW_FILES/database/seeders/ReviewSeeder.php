<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some existing user IDs from user_data table
        $userIds = DB::table('user_data')
            ->whereNotNull('uid')
            ->limit(5)
            ->pluck('uid')
            ->toArray();

        if (empty($userIds)) {
            $this->command->warn('⚠️  No users found in user_data table!');
            $this->command->info('Creating reviews without user_id (anonymous reviews)...');
            $userIds = [null, null, null, null, null];
        } else {
            $this->command->info('✅ Found ' . count($userIds) . ' users in database');
        }

        $reviews = [
            [
                'user_id' => $userIds[0] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Excellent service! The team was very professional and delivered exactly what we needed. Highly recommend!',
                'rate' => 5,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => $userIds[1] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Great experience overall. The product quality is top-notch and customer support was very helpful.',
                'rate' => 4,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
            [
                'user_id' => $userIds[2] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Very satisfied with the service. Fast delivery and excellent quality. Will definitely order again!',
                'rate' => 5,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'user_id' => $userIds[3] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Good product but delivery took longer than expected. Overall satisfied with the purchase.',
                'rate' => 3,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ],
            [
                'user_id' => $userIds[4] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Amazing quality and attention to detail! The team went above and beyond to ensure everything was perfect. This is a very long review to test the comment icon functionality and the modal popup that shows the full message when clicked. The service was exceptional from start to finish, and I would highly recommend this to anyone looking for quality work.',
                'rate' => 5,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'user_id' => $userIds[0] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Professional service with great results. The team was responsive and delivered on time.',
                'rate' => 4,
                'is_approve' => 0,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ],
            [
                'user_id' => null,
                'name' => 'Robert Taylor',
                'email' => 'robert.t@example.com',
                'photo_uri' => null,
                'feedback' => 'Decent service. Met my expectations but nothing extraordinary.',
                'rate' => 3,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => $userIds[1] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Outstanding work! Every detail was perfect and the final result exceeded my expectations. The communication throughout the project was excellent.',
                'rate' => 5,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'user_id' => null,
                'name' => 'James Brown',
                'email' => 'james.brown@example.com',
                'photo_uri' => null,
                'feedback' => 'Very happy with the results. Professional team and great customer service.',
                'rate' => 4,
                'is_approve' => 1,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'user_id' => $userIds[2] ?? null,
                'name' => null,
                'email' => null,
                'photo_uri' => null,
                'feedback' => 'Excellent experience from start to finish! The quality of work is exceptional and the team was very professional. I particularly appreciated the attention to detail and the willingness to make adjustments based on my feedback. This is another long review to test the functionality of the comment icon and modal display. The entire process was smooth and hassle-free.',
                'rate' => 5,
                'is_approve' => 0,
                'is_deleted' => 0,
                'created_at' => Carbon::now()->subHours(6),
                'updated_at' => Carbon::now()->subHours(6),
            ],
        ];

        DB::table('reviews')->insert($reviews);
        
        $this->command->info('✅ Successfully created 10 test reviews!');
        $this->command->info('   - 7 reviews with user_id (from existing users)');
        $this->command->info('   - 3 anonymous reviews (without user_id)');
        $this->command->info('   - 7 approved reviews');
        $this->command->info('   - 3 pending approval');
        $this->command->info('   - 2 long reviews with comment icons');
        $this->command->info('   - Various ratings (3-5 stars)');
        $this->command->info('');
        $this->command->info('🧪 To test deleted user scenario:');
        $this->command->info('   1. Note a user_id from the reviews table');
        $this->command->info('   2. Delete that user from user_datas table');
        $this->command->info('   3. Refresh reviews page to see "Deleted User" handling');
    }
}
