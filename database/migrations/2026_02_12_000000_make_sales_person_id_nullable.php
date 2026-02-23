<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('crafty_revenue_mysql')->table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_person_id')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_revenue_mysql')->table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_person_id')->default(null)->change();
        });
    }
};
