<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'crafty_video_mysql';

    public function up()
    {
        Schema::connection($this->connection)->table('main_categories', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('main_categories', 'imp')) {
                $table->tinyInteger('imp')->default(0)->after('status');
            }
            if (!Schema::connection($this->connection)->hasColumn('main_categories', 'no_index')) {
                $table->tinyInteger('no_index')->default(0)->after('imp');
            }
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->table('main_categories', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('main_categories', 'imp')) {
                $table->dropColumn('imp');
            }
            if (Schema::connection($this->connection)->hasColumn('main_categories', 'no_index')) {
                $table->dropColumn('no_index');
            }
        });
    }
};
