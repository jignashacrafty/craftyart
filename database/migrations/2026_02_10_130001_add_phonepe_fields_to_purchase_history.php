<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_history', function (Blueprint $table) {
            // PhonePe AutoPay fields
            $table->string('phonepe_merchant_order_id')->nullable()->after('payment_id');
            $table->string('phonepe_subscription_id')->nullable()->after('phonepe_merchant_order_id');
            $table->string('phonepe_order_id')->nullable()->after('phonepe_subscription_id');
            $table->string('phonepe_transaction_id')->nullable()->after('phonepe_order_id');
            $table->boolean('is_autopay_enabled')->default(false)->after('phonepe_transaction_id');
            $table->string('autopay_status')->nullable()->after('is_autopay_enabled'); // PENDING, ACTIVE, CANCELLED
            $table->timestamp('autopay_activated_at')->nullable()->after('autopay_status');
            $table->timestamp('next_autopay_date')->nullable()->after('autopay_activated_at');
            $table->integer('autopay_count')->default(0)->after('next_autopay_date');
            
            // Indexes for faster searching
            $table->index('phonepe_merchant_order_id');
            $table->index('phonepe_subscription_id');
            $table->index('phonepe_order_id');
            $table->index('is_autopay_enabled');
            $table->index('autopay_status');
        });
    }

    public function down()
    {
        Schema::table('purchase_history', function (Blueprint $table) {
            $table->dropIndex(['phonepe_merchant_order_id']);
            $table->dropIndex(['phonepe_subscription_id']);
            $table->dropIndex(['phonepe_order_id']);
            $table->dropIndex(['is_autopay_enabled']);
            $table->dropIndex(['autopay_status']);
            
            $table->dropColumn([
                'phonepe_merchant_order_id',
                'phonepe_subscription_id',
                'phonepe_order_id',
                'phonepe_transaction_id',
                'is_autopay_enabled',
                'autopay_status',
                'autopay_activated_at',
                'next_autopay_date',
                'autopay_count'
            ]);
        });
    }
};
