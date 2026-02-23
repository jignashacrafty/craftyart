<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BusinessSupportPurchaseHistorySeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    $entries = [
      [
        'user_id' => 'USER001',
        'product_id' => 'BS-PREMIUM-001',
        'product_type' => 1,
        'transaction_id' => 'TXN' . time() . '001',
        'payment_id' => 'PAY' . time() . '001',
        'currency_code' => 'INR',
        'amount' => 999.00,
        'payment_method' => 'PhonePe',
        'from_where' => 'Web',
        'contact_no' => '9876543210',
        'payment_status' => 1,
        'status' => 1,
        'created_at' => Carbon::now()->subDays(5),
        'updated_at' => Carbon::now()->subDays(5),
      ],
      [
        'user_id' => 'USER002',
        'product_id' => 'BS-STANDARD-002',
        'product_type' => 1,
        'transaction_id' => 'TXN' . time() . '002',
        'payment_id' => 'PAY' . time() . '002',
        'currency_code' => 'INR',
        'amount' => 499.00,
        'payment_method' => 'Razorpay',
        'from_where' => 'Mobile',
        'contact_no' => '9876543211',
        'payment_status' => 1,
        'status' => 1,
        'created_at' => Carbon::now()->subDays(4),
        'updated_at' => Carbon::now()->subDays(4),
      ],
      [
        'user_id' => 'USER003',
        'product_id' => 'BS-ENTERPRISE-003',
        'product_type' => 1,
        'transaction_id' => 'TXN' . time() . '003',
        'payment_id' => 'PAY' . time() . '003',
        'currency_code' => 'INR',
        'amount' => 1999.00,
        'payment_method' => 'PhonePe',
        'from_where' => 'Web',
        'contact_no' => '9876543212',
        'payment_status' => 1,
        'status' => 1,
        'created_at' => Carbon::now()->subDays(3),
        'updated_at' => Carbon::now()->subDays(3),
      ],
      [
        'user_id' => 'USER004',
        'product_id' => 'BS-BASIC-004',
        'product_type' => 1,
        'transaction_id' => 'TXN' . time() . '004',
        'payment_id' => 'PAY' . time() . '004',
        'currency_code' => 'INR',
        'amount' => 299.00,
        'payment_method' => 'UPI',
        'from_where' => 'Mobile',
        'contact_no' => '9876543213',
        'payment_status' => 0,
        'status' => 0,
        'created_at' => Carbon::now()->subDays(2),
        'updated_at' => Carbon::now()->subDays(2),
      ],
      [
        'user_id' => 'USER005',
        'product_id' => 'BS-PREMIUM-005',
        'product_type' => 1,
        'transaction_id' => 'TXN' . time() . '005',
        'payment_id' => 'PAY' . time() . '005',
        'currency_code' => 'INR',
        'amount' => 1499.00,
        'payment_method' => 'PhonePe',
        'from_where' => 'Web',
        'contact_no' => '9876543214',
        'payment_status' => 1,
        'status' => 1,
        'created_at' => Carbon::now()->subDays(1),
        'updated_at' => Carbon::now()->subDays(1),
      ],
    ];

    DB::connection('crafty_revenue')->table('business_support_purchase_history')->insert($entries);

    $this->command->info('5 fake entries added to business_support_purchase_history table successfully!');
  }
}
