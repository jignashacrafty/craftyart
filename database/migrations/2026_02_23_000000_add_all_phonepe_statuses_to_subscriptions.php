<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddAllPhonepeStatusesToSubscriptions extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    // Add all possible PhonePe status values to the ENUM
    DB::statement("ALTER TABLE `phonepe_subscriptions` MODIFY COLUMN `status` ENUM('PENDING', 'ACTIVE', 'COMPLETED', 'FAILED', 'CANCELLED', 'EXPIRED', 'PAUSED', 'PAYMENT_FAILED', 'UNKNOWN') DEFAULT 'PENDING'");
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    // Revert back to previous ENUM values
    DB::statement("ALTER TABLE `phonepe_subscriptions` MODIFY COLUMN `status` ENUM('PENDING', 'ACTIVE', 'PAUSED', 'CANCELLED', 'EXPIRED', 'PAYMENT_FAILED') DEFAULT 'PENDING'");
  }
}
