<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // Fix manage_subscriptions table id to auto_increment
    DB::statement('ALTER TABLE manage_subscriptions MODIFY COLUMN id INT UNSIGNED AUTO_INCREMENT');
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    // Remove auto_increment from id
    DB::statement('ALTER TABLE manage_subscriptions MODIFY COLUMN id INT UNSIGNED');
  }
};
