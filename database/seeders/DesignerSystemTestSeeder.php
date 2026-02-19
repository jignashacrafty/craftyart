<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DesignerApplication;
use App\Models\DesignerProfile;
use App\Models\DesignerWallet;
use App\Models\DesignSubmission;
use App\Models\DesignSeoDetail;
use App\Models\DesignerWithdrawal;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;

class DesignerSystemTestSeeder extends Seeder
{
    public function run()
    {
        // Get first admin user
        $adminUser = User::first();
        if (!$adminUser) {
            $this->command->error('No users found in database. Please create an admin user first.');
            return;
        }

        // Create 3 test applications
        $applications = [
            [
                'name' => 'Raj Patel',
                'email' => 'raj.designer@test.com',
                'phone' => '9876543210',
                'address' => '123 Design Street',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'experience' => '5 years of graphic design experience in wedding cards and social media posts',
                'skills' => 'Photoshop, Illustrator, Figma, CorelDRAW',
                'portfolio_links' => [
                    'https://behance.net/rajpatel',
                    'https://dribbble.com/rajpatel'
                ],
                'status' => 'pending',
            ],
            [
                'name' => 'Priya Shah',
                'email' => 'priya.designer@test.com',
                'phone' => '9876543211',
                'address' => '456 Creative Avenue',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'country' => 'India',
                'experience' => '3 years specializing in invitation cards and festival designs',
                'skills' => 'Adobe Creative Suite, Canva Pro, Sketch',
                'portfolio_links' => [
                    'https://behance.net/priyashah'
                ],
                'status' => 'pending',
            ],
            [
                'name' => 'Amit Desai',
                'email' => 'amit.designer@test.com',
                'phone' => '9876543212',
                'address' => '789 Art Colony',
                'city' => 'Rajkot',
                'state' => 'Gujarat',
                'country' => 'India',
                'experience' => '7 years in logo design and branding',
                'skills' => 'Illustrator, Photoshop, InDesign, After Effects',
                'portfolio_links' => [
                    'https://behance.net/amitdesai',
                    'https://dribbble.com/amitdesai',
                    'https://instagram.com/amitdesigns'
                ],
                'status' => 'pending',
            ],
        ];

        foreach ($applications as $appData) {
            DesignerApplication::create($appData);
        }

        // Create 2 approved designers with profiles
        $approvedDesigners = [
            [
                'name' => 'Kiran Mehta',
                'email' => 'kiran.designer@test.com',
                'display_name' => 'Kiran Mehta Designs',
                'commission_rate' => 30.00,
            ],
            [
                'name' => 'Neha Joshi',
                'email' => 'neha.designer@test.com',
                'display_name' => 'Neha Creative Studio',
                'commission_rate' => 35.00,
            ],
        ];

        foreach ($approvedDesigners as $designerData) {
            // Create application
            $application = DesignerApplication::create([
                'name' => $designerData['name'],
                'email' => $designerData['email'],
                'phone' => '9876543213',
                'address' => 'Test Address',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'country' => 'India',
                'experience' => 'Experienced designer',
                'skills' => 'Design skills',
                'status' => 'approved',
                'reviewed_by' => $adminUser->id,
                'reviewed_at' => now(),
            ]);

            // Create user
            $user = User::create([
                'name' => $designerData['name'],
                'email' => $designerData['email'],
                'password' => Hash::make('Designer@123'),
                'user_type' => UserRole::DESIGNER_EMPLOYEE->id(),
                'status' => 1,
            ]);

            // Create profile
            $profile = DesignerProfile::create([
                'user_id' => $user->id,
                'application_id' => $application->id,
                'display_name' => $designerData['display_name'],
                'commission_rate' => $designerData['commission_rate'],
                'is_active' => true,
                'total_designs' => rand(5, 15),
                'approved_designs' => rand(3, 10),
                'live_designs' => rand(2, 8),
                'total_earnings' => rand(5000, 15000),
            ]);

            // Create wallet
            $balance = rand(1000, 5000);
            DesignerWallet::create([
                'designer_id' => $profile->id,
                'balance' => $balance,
                'total_earned' => rand(10000, 20000),
                'total_withdrawn' => rand(5000, 10000),
                'pending_amount' => 0,
                'withdrawal_threshold' => 500.00,
            ]);

            // Create some design submissions
            for ($i = 1; $i <= 3; $i++) {
                $statuses = ['pending_designer_head', 'pending_seo', 'live'];
                $status = $statuses[array_rand($statuses)];
                
                $uniqueId = uniqid();
                $design = DesignSubmission::create([
                    'designer_id' => $profile->id,
                    'title' => "Wedding Invitation Card Design $i - {$designerData['name']}",
                    'description' => "Beautiful wedding invitation design with floral elements and elegant typography",
                    'category' => 'template',
                    'design_file_path' => 'designs/sample.jpg',
                    'preview_images' => ['designs/preview1.jpg', 'designs/preview2.jpg'],
                    'tags' => ['wedding', 'invitation', 'floral'],
                    'status' => $status,
                    'total_sales' => $status == 'live' ? rand(10, 50) : 0,
                    'total_revenue' => $status == 'live' ? rand(1000, 5000) : 0,
                ]);

                // Add SEO details for live designs
                if ($status == 'live' || $status == 'pending_seo') {
                    DesignSeoDetail::create([
                        'design_submission_id' => $design->id,
                        'meta_title' => "Wedding Invitation Card Design - Free Template",
                        'meta_description' => "Download beautiful wedding invitation card template. Customize and create stunning invitations for your special day.",
                        'slug' => "wedding-invitation-card-design-{$uniqueId}",
                        'keywords' => ['wedding', 'invitation', 'card', 'template'],
                        'is_featured' => rand(0, 1) == 1,
                        'is_trending' => rand(0, 1) == 1,
                    ]);
                }
            }

            // Create a withdrawal request
            if ($balance >= 500) {
                DesignerWithdrawal::create([
                    'designer_id' => $profile->id,
                    'amount' => 1000,
                    'status' => 'pending',
                    'payment_method' => 'upi',
                    'upi_id' => $designerData['email'],
                ]);
            }
        }

        $this->command->info('✅ Designer System test data created successfully!');
        $this->command->info('📧 Test Designer Logins:');
        $this->command->info('   Email: kiran.designer@test.com | Password: Designer@123');
        $this->command->info('   Email: neha.designer@test.com | Password: Designer@123');
    }
}
