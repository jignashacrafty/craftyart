<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSeoFieldsToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
            $table->string('h2_tag')->nullable()->after('keyword');
            $table->string('canonical_link')->nullable()->after('h2_tag');
            $table->string('meta_title')->nullable()->after('canonical_link');
            $table->text('meta_description')->nullable()->after('meta_title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
            $table->dropColumn(['h2_tag', 'canonical_link', 'meta_title', 'meta_description']);
        });
    }
}
