<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonepeSubscriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('phonepe_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('plan_id');
            
            // PhonePe IDs
            $table->string('merchant_subscription_id')->unique();
            $table->string('phonepe_subscription_id')->nullable();
            $table->string('merchant_order_id')->nullable();
            
            // Subscription Details
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('frequency', ['Daily', 'Weekly', 'Monthly', 'Yearly'])->default('Monthly');
            $table->decimal('max_amount', 10, 2);
            
            // Dates
            $table->date('start_date');
            $table->date('next_billing_date');
            $table->date('end_date')->nullable();
            $table->date('last_payment_date')->nullable();
            
            // Status
            $table->enum('status', ['ACTIVE', 'PAUSED', 'CANCELLED', 'EXPIRED', 'PAYMENT_FAILED'])->default('ACTIVE');
            $table->string('subscription_status', 50)->nullable();
            
            // Payment Details
            $table->integer('total_payments')->default(0);
            $table->integer('failed_payments')->default(0);
            $table->string('last_payment_status', 50)->nullable();
            
            // Error Tracking
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->text('failure_reason')->nullable();
            
            // Metadata
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('merchant_subscription_id');
            $table->index('status');
            $table->index('next_billing_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_subscriptions');
    }
}
