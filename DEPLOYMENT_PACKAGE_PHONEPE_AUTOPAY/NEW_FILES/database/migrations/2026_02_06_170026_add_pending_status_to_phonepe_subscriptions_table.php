<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddPendingStatusToPhonepeSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the status ENUM to include PENDING
        DB::statement("ALTER TABLE `phonepe_subscriptions` MODIFY COLUMN `status` ENUM('PENDING', 'ACTIVE', 'PAUSED', 'CANCELLED', 'EXPIRED', 'PAYMENT_FAILED') DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE `phonepe_subscriptions` MODIFY COLUMN `status` ENUM('ACTIVE', 'PAUSED', 'CANCELLED', 'EXPIRED', 'PAYMENT_FAILED') DEFAULT 'ACTIVE'");
    }
}
