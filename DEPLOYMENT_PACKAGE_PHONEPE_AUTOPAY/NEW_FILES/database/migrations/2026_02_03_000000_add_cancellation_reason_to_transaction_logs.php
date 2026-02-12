<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql')->table('transaction_logs', function (Blueprint $table) {
            if (!Schema::connection('mysql')->hasColumn('transaction_logs', 'cancellation_reason')) {
                $table->string('cancellation_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->table('transaction_logs', function (Blueprint $table) {
            if (Schema::connection('mysql')->hasColumn('transaction_logs', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });
    }
};
