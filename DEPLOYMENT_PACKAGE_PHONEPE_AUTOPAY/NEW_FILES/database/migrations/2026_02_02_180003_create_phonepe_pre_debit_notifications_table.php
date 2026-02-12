<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonepePreDebitNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('phonepe_pre_debit_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('merchant_subscription_id');
            
            // Notification Details
            $table->date('notification_date');
            $table->date('debit_date');
            $table->decimal('amount', 10, 2);
            
            // PhonePe API
            $table->string('phonepe_order_id')->nullable();
            $table->enum('phonepe_status', ['sent', 'failed', 'pending'])->default('pending');
            $table->json('phonepe_response')->nullable();
            
            // WhatsApp Notification
            $table->enum('whatsapp_status', ['sent', 'failed', 'pending', 'skipped'])->default('pending');
            $table->json('whatsapp_response')->nullable();
            
            // User Details
            $table->string('user_id');
            $table->string('phone', 20);
            
            // Status
            $table->enum('overall_status', ['sent', 'failed', 'partial', 'pending'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            
            $table->timestamps();
            
            $table->index('subscription_id');
            $table->index('notification_date');
            $table->index('debit_date');
            $table->unique(['merchant_subscription_id', 'notification_date'], 'unique_notification');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_pre_debit_notifications');
    }
}
