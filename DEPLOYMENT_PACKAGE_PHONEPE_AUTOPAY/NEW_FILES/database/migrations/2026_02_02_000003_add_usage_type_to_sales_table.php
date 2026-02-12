<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageTypeToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_revenue')->table('sales', function (Blueprint $table) {
            $table->string('usage_type')->nullable()->after('plan_type')->comment('personal or professional');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_revenue')->table('sales', function (Blueprint $table) {
            $table->dropColumn('usage_type');
        });
    }
}
