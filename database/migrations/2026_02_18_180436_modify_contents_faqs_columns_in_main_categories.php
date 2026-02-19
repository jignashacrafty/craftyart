<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyContentsFaqsColumnsInMainCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to modify columns and remove JSON constraint
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN contents TEXT NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN faqs TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to JSON columns
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN contents JSON NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN faqs JSON NULL');
    }
}
