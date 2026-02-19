<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallet_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->string('setting_name');
            $table->text('description')->nullable();
            $table->decimal('min_withdrawal_threshold', 10, 2)->default(500.00);
            $table->decimal('max_withdrawal_limit', 10, 2)->nullable();
            $table->integer('payout_day_of_month')->default(1); // 1-31
            $table->enum('payout_frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->decimal('platform_commission_rate', 5, 2)->default(30.00); // percentage
            $table->integer('min_days_between_withdrawals')->default(7);
            $table->integer('max_pending_withdrawals')->default(3);
            $table->boolean('auto_approve_withdrawals')->default(false);
            $table->decimal('auto_approve_threshold', 10, 2)->nullable();
            $table->json('payment_methods')->nullable(); // ['bank_transfer', 'upi', 'paypal']
            $table->json('additional_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('wallet_settings')->insert([
            'setting_key' => 'default',
            'setting_name' => 'Default Wallet Settings',
            'description' => 'Default wallet configuration for all designers',
            'min_withdrawal_threshold' => 500.00,
            'max_withdrawal_limit' => 50000.00,
            'payout_day_of_month' => 1,
            'payout_frequency' => 'monthly',
            'platform_commission_rate' => 30.00,
            'min_days_between_withdrawals' => 7,
            'max_pending_withdrawals' => 3,
            'auto_approve_withdrawals' => false,
            'auto_approve_threshold' => 5000.00,
            'payment_methods' => json_encode(['bank_transfer', 'upi']),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('wallet_settings');
    }
};
