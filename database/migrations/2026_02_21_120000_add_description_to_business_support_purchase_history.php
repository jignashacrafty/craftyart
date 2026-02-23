<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::connection('crafty_revenue_mysql')->table('business_support_purchase_history', function (Blueprint $table) {
      $table->text('description')->nullable()->after('status');
    });
  }

  /**
   * Down the migrations.
   */
  public function down(): void
  {
    Schema::connection('crafty_revenue_mysql')->table('business_support_purchase_history', function (Blueprint $table) {
      $table->dropColumn('description');
    });
  }
};
