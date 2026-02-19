<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFldrStrToMainCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->string('fldr_str', 50)->nullable()->after('id_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->dropColumn('fldr_str');
        });
    }
}
