<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('phonepe_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_order_id')->index();
            $table->string('merchant_subscription_id')->nullable()->index();
            $table->string('phonepe_order_id')->nullable()->index();
            $table->string('phonepe_transaction_id')->nullable()->index();
            $table->string('notification_type'); // PRE_DEBIT, PAYMENT_SUCCESS, PAYMENT_FAILED, MANDATE_APPROVED, MANDATE_REJECTED
            $table->string('event_type')->nullable(); // Webhook event type
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('payment_method')->nullable();
            $table->json('webhook_payload')->nullable(); // Complete webhook data
            $table->json('response_data')->nullable();
            $table->boolean('is_processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('notification_type');
            $table->index('is_processed');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_notifications');
    }
};
