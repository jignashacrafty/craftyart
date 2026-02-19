<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('designer_wallets', function (Blueprint $table) {
            $table->decimal('total_sold_amount', 10, 2)->default(0)->after('total_earned');
            $table->decimal('platform_commission', 10, 2)->default(0)->after('total_sold_amount');
            $table->integer('total_sales_count')->default(0)->after('platform_commission');
            $table->decimal('average_sale_amount', 10, 2)->default(0)->after('total_sales_count');
            $table->timestamp('last_sale_at')->nullable()->after('average_sale_amount');
            $table->timestamp('last_withdrawal_at')->nullable()->after('last_sale_at');
            $table->foreignId('wallet_setting_id')->nullable()->after('designer_id')->constrained('wallet_settings')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('designer_wallets', function (Blueprint $table) {
            $table->dropForeign(['wallet_setting_id']);
            $table->dropColumn([
                'total_sold_amount',
                'platform_commission',
                'total_sales_count',
                'average_sale_amount',
                'last_sale_at',
                'last_withdrawal_at',
                'wallet_setting_id'
            ]);
        });
    }
};
