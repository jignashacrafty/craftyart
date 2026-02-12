<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonepeTokensTable extends Migration
{
    public function up()
    {
        Schema::create('phonepe_tokens', function (Blueprint $table) {
            $table->id();
            $table->text('access_token');
            $table->string('token_type', 50)->default('Bearer');
            $table->integer('expires_in');
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('phonepe_tokens');
    }
}
