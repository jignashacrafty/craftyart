<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::connection('crafty_pricing_mysql')->table('payment_configurations', function (Blueprint $table) {
      $table->dropColumn('is_active');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::connection('crafty_pricing_mysql')->table('payment_configurations', function (Blueprint $table) {
      $table->boolean('is_active')->default(0)->after('payment_types');
    });
  }
};
