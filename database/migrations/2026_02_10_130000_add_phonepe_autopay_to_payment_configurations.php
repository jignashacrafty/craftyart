<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check if PhonePe configuration exists
        $phonePe = DB::table('payment_configurations')
            ->where('gateway', 'PhonePe')
            ->first();
        
        if ($phonePe) {
            // Update existing PhonePe configuration with AutoPay credentials
            $credentials = [
                'client_id' => 'SU2512031928441979485878',
                'client_secret' => '04652cf1-d98d-4f48-8ae8-0ecf60fac76f',
                'merchant_user_id' => 'M22EOXLUSO1LA',
                'client_version' => '1',
                'webhook_url' => 'https://www.craftyartapp.com/api/phonepe/webhook'
            ];
            
            $paymentTypes = ['subscription', 'autopay', 'recurring', 'one_time'];
            
            DB::table('payment_configurations')
                ->where('id', $phonePe->id)
                ->update([
                    'credentials' => json_encode($credentials),
                    'payment_types' => json_encode($paymentTypes),
                    'is_active' => 1,
                    'updated_at' => now()
                ]);
        }
    }

    public function down()
    {
        // Optionally revert changes
    }
};
