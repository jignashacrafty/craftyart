<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'crafty_video_mysql';

    public function up()
    {
        Schema::connection($this->connection)->table('items', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('items', 'no_index')) {
                $table->tinyInteger('no_index')->default(1)->after('status')->comment('1 = noindex, 0 = index');
            }
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->table('items', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('items', 'no_index')) {
                $table->dropColumn('no_index');
            }
        });
    }
};
