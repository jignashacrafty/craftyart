<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix the cashfree gateway name to proper case
        DB::table('payment_configurations')
            ->where('gateway', 'cashfree')
            ->update(['gateway' => 'Cashfree']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_configurations')
            ->where('gateway', 'Cashfree')
            ->update(['gateway' => 'cashfree']);
    }
};
