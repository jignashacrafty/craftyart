<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('phonepe_transactions', function (Blueprint $table) {
            $table->string('user_id')->nullable()->after('id')->index();
        });
    }

    public function down()
    {
        Schema::table('phonepe_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
