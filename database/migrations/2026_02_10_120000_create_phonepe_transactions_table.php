<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('phonepe_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_order_id')->unique();
            $table->string('merchant_subscription_id')->nullable()->index();
            $table->string('phonepe_order_id')->nullable()->index();
            $table->string('phonepe_transaction_id')->nullable()->index();
            $table->string('transaction_type')->default('SUBSCRIPTION_SETUP'); // SUBSCRIPTION_SETUP, SUBSCRIPTION_REDEMPTION
            $table->string('upi_id')->nullable();
            $table->string('mobile')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('PENDING'); // PENDING, ACTIVE, COMPLETED, FAILED
            $table->string('payment_state')->nullable(); // PhonePe payment state
            $table->boolean('is_autopay_active')->default(false);
            $table->integer('autopay_count')->default(0);
            $table->timestamp('last_autopay_at')->nullable();
            $table->timestamp('next_autopay_at')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for faster searching
            $table->index('status');
            $table->index('transaction_type');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_transactions');
    }
};
