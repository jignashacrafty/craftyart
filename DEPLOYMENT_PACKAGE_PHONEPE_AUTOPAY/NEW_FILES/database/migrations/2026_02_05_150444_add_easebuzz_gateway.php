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
        // Check if Easebuzz already exists
        $exists = DB::table('payment_configurations')
            ->where('gateway', 'Easebuzz')
            ->exists();

        if (!$exists) {
            DB::table('payment_configurations')->insert([
                'payment_scope' => 'NATIONAL',
                'gateway' => 'Easebuzz',
                'credentials' => json_encode([
                    'merchant_key' => '',
                    'salt' => '',
                    'environment' => 'production'
                ]),
                'payment_types' => json_encode([]),
                'is_active' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_configurations')
            ->where('gateway', 'Easebuzz')
            ->delete();
    }
};
