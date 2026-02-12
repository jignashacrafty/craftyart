<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToBrandKitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('brand_kit_mysql')->table('brand_kit', function (Blueprint $table) {
            if (!Schema::connection('brand_kit_mysql')->hasColumn('brand_kit', 'website')) {
                $table->string('website')->nullable()->after('user_id');
            }
            if (!Schema::connection('brand_kit_mysql')->hasColumn('brand_kit', 'role')) {
                $table->string('role')->nullable()->after('website');
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
            $table->dropColumn(['website', 'role']);
        });
    }
}
