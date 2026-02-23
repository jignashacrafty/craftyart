<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DesignerProfile;
use App\Models\DesignerWallet;
use App\Models\DesignerApplication;

class AdminDesignerProfileSeeder extends Seeder
{
  /**
   * Run the database seeds.
   * Creates a designer profile for the admin user for testing purposes.
   */
  public function run()
  {
    // Find admin user
    $admin = User::where('email', 'admin@gmail.com')->first();

    if (!$admin) {
      $this->command->error('Admin user not found with email: admin@gmail.com');
      $this->command->info('Please update the email in the seeder or create the admin user first.');
      return;
    }

    // Check if designer profile already exists
    $existingProfile = DesignerProfile::where('user_id', $admin->id)->first();

    if ($existingProfile) {
      $this->command->info('Designer profile already exists for admin user.');
      $this->command->info('Profile ID: ' . $existingProfile->id);
      return;
    }

    // Create designer application first
    $application = DesignerApplication::create([
      'name' => $admin->name,
      'email' => $admin->email,
      'phone' => '1234567890',
      'address' => 'Admin Address',
      'city' => 'Admin City',
      'state' => 'Admin State',
      'country' => 'India',
      'experience' => 'Admin user with full access',
      'skills' => 'All design skills',
      'portfolio_links' => [],
      'uploaded_samples' => [],
      'status' => 'approved',
      'reviewed_by' => $admin->id,
      'reviewed_at' => now(),
    ]);

    $this->command->info('✅ Designer application created');
    $this->command->info('Application ID: ' . $application->id);

    // Create designer profile
    $profile = DesignerProfile::create([
      'user_id' => $admin->id,
      'application_id' => $application->id,
      'display_name' => 'Admin Designer',
      'bio' => 'Admin user with designer access for testing',
      'profile_image' => null,
      'specializations' => ['template', 'video', 'sticker'],
      'commission_rate' => 30,
      'is_active' => true,
      'total_designs' => 0,
      'approved_designs' => 0,
      'live_designs' => 0,
      'total_earnings' => 0,
    ]);

    $this->command->info('✅ Designer profile created for admin user');
    $this->command->info('Profile ID: ' . $profile->id);

    // Create wallet
    $wallet = DesignerWallet::create([
      'designer_id' => $profile->id,
      'balance' => 0,
      'total_earned' => 0,
      'total_withdrawn' => 0,
      'withdrawal_threshold' => 500,
    ]);

    $this->command->info('✅ Designer wallet created');
    $this->command->info('Wallet ID: ' . $wallet->id);

    $this->command->info('');
    $this->command->info('🎉 Admin user can now access designer APIs!');
    $this->command->info('Test with: GET /api/designer/profile?showDecoded=1');
  }
}
