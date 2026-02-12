<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonepeAutopayTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('phonepe_autopay_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('merchant_subscription_id');
            $table->string('merchant_order_id')->unique();
            $table->string('phonepe_transaction_id')->nullable();
            
            // Transaction Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('transaction_type', ['setup', 'recurring', 'manual'])->default('recurring');
            
            // Status
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('payment_status', 50)->nullable();
            
            // Error Details
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->text('failure_reason')->nullable();
            
            // Metadata
            $table->json('webhook_data')->nullable();
            $table->boolean('is_autopay')->default(true);
            
            $table->timestamps();
            
            $table->index('subscription_id');
            $table->index('merchant_order_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_autopay_transactions');
    }
}
