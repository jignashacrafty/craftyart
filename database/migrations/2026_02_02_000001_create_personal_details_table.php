<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql')->create('personal_details', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique()->comment('User ID from user_data table');
            $table->string('user_name')->nullable();
            $table->text('bio')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('interest')->nullable();
            $table->string('purpose')->nullable();
            $table->string('usage')->nullable()->comment('personal or professional');
            $table->string('reference')->nullable();
            $table->string('language')->nullable();
            $table->timestamps();
            
            // Index for faster lookups
            $table->index('uid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql')->dropIfExists('personal_details');
    }
}
