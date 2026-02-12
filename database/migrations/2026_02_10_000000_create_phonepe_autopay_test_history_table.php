<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('phonepe_autopay_test_history', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_order_id')->unique();
            $table->string('merchant_subscription_id')->index();
            $table->string('phonepe_order_id')->nullable();
            $table->string('upi_id');
            $table->string('mobile');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('PENDING'); // PENDING, ACTIVE, COMPLETED, FAILED, CANCELLED
            $table->string('subscription_state')->nullable(); // PhonePe subscription state
            $table->boolean('is_autopay_active')->default(false);
            $table->integer('autopay_count')->default(0); // How many times auto-debited
            $table->timestamp('last_autopay_at')->nullable();
            $table->timestamp('next_autopay_at')->nullable();
            $table->boolean('predebit_sent')->default(false);
            $table->timestamp('predebit_sent_at')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_data')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_autopay_test_history');
    }
};
