<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaricatureToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_revenue')->table('sales', function (Blueprint $table) {
            $table->unsignedTinyInteger('caricature')->default(0)->after('plan_type');
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
            $table->dropColumn('caricature');
        });
    }
}
