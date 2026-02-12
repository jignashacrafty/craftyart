<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonepeAutopayTokensTable extends Migration
{
    public function up()
    {
        Schema::create('phonepe_autopay_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('merchant_transaction_id')->unique();
            $table->string('auth_request_id')->nullable();
            $table->enum('status', ['pending', 'ACTIVE', 'FAILED', 'EXPIRED'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('upi_id')->nullable();
            $table->string('contact_no')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('merchant_transaction_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_autopay_tokens');
    }
}
