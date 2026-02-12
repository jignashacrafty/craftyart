<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilterFieldsToItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_video_mysql')->table('items', function (Blueprint $table) {
            $table->string('id_name')->nullable()->after('keyword');
            $table->text('description')->nullable()->after('meta_description');
            $table->string('lang_id')->nullable()->after('description');
            $table->string('theme_id')->nullable()->after('lang_id');
            $table->string('style_id')->nullable()->after('theme_id');
            $table->string('orientation')->nullable()->after('style_id');
            $table->integer('template_size')->nullable()->after('orientation');
            $table->string('religion_id')->nullable()->after('template_size');
            $table->string('interest_id')->nullable()->after('religion_id');
            $table->tinyInteger('is_freemium')->default(0)->after('is_premium');
            $table->date('start_date')->nullable()->after('is_freemium');
            $table->date('end_date')->nullable()->after('start_date');
            $table->text('color_ids')->nullable()->after('end_date');
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
            $table->dropColumn([
                'id_name',
                'description',
                'lang_id',
                'theme_id',
                'style_id',
                'orientation',
                'template_size',
                'religion_id',
                'interest_id',
                'is_freemium',
                'start_date',
                'end_date',
                'color_ids'
            ]);
        });
    }
}
