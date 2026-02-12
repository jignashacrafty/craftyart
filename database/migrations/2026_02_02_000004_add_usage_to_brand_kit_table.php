<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsageToBrandKitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('brand_kit_mysql')->table('brand_kit', function (Blueprint $table) {
            if (!Schema::connection('brand_kit_mysql')->hasColumn('brand_kit', 'usage')) {
                $table->string('usage')->nullable()->after('role')->comment('Usage type: personal or professional');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('brand_kit_mysql')->table('brand_kit', function (Blueprint $table) {
            $table->dropColumn('usage');
        });
    }
}
