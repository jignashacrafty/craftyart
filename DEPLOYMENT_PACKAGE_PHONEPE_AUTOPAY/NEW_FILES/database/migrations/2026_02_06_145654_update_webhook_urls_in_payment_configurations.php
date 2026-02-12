<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentConfiguration;

class UpdateWebhookUrlsInPaymentConfigurations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update Razorpay webhook URL
        $razorpay = PaymentConfiguration::where('gateway', 'Razorpay')->first();
        if ($razorpay) {
            $credentials = $razorpay->credentials;
            if (is_string($credentials)) {
                $credentials = json_decode($credentials, true);
            }
            $credentials['webhook_url'] = url('/api/razorpay/webhook');
            $razorpay->credentials = $credentials;
            $razorpay->save();
        }

        // Update PhonePe webhook URL
        $phonepe = PaymentConfiguration::where('gateway', 'PhonePe')->first();
        if ($phonepe) {
            $credentials = $phonepe->credentials;
            if (is_string($credentials)) {
                $credentials = json_decode($credentials, true);
            }
            $credentials['webhook_url'] = url('/api/phonepe/webhook');
            $phonepe->credentials = $credentials;
            $phonepe->save();
        }

        // Update Cashfree webhook URL if exists
        $cashfree = PaymentConfiguration::where('gateway', 'Cashfree')->first();
        if ($cashfree) {
            $credentials = $cashfree->credentials;
            if (is_string($credentials)) {
                $credentials = json_decode($credentials, true);
            }
            $credentials['webhook_url'] = url('/api/cashfree/webhook');
            $cashfree->credentials = $credentials;
            $cashfree->save();
        }

        // Update Easebuzz webhook URL if exists
        $easebuzz = PaymentConfiguration::where('gateway', 'Easebuzz')->first();
        if ($easebuzz) {
            $credentials = $easebuzz->credentials;
            if (is_string($credentials)) {
                $credentials = json_decode($credentials, true);
            }
            $credentials['webhook_url'] = url('/api/easebuzz/webhook');
            $easebuzz->credentials = $credentials;
            $easebuzz->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No need to reverse - webhook URLs can stay
    }
}
