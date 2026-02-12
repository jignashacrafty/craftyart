<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'crafty_video_mysql';

    public function up()
    {
        Schema::connection($this->connection)->table('main_categories', function (Blueprint $table) {
            $table->string('id_name')->nullable()->after('category_name');
            $table->string('canonical_link')->nullable()->after('id_name');
            $table->integer('seo_emp_id')->nullable()->after('canonical_link');
            $table->string('meta_title', 60)->nullable()->after('seo_emp_id');
            $table->string('primary_keyword')->nullable()->after('meta_title');
            $table->string('h1_tag', 60)->nullable()->after('primary_keyword');
            $table->string('tag_line')->nullable()->after('h1_tag');
            $table->text('meta_desc')->nullable()->after('tag_line');
            $table->text('short_desc')->nullable()->after('meta_desc');
            $table->string('h2_tag')->nullable()->after('short_desc');
            $table->text('long_desc')->nullable()->after('h2_tag');
            $table->string('mockup')->nullable()->after('category_thumb');
            $table->string('banner')->nullable()->after('mockup');
            $table->integer('app_id')->nullable()->after('banner');
            $table->json('contents')->nullable()->after('app_id');
            $table->json('faqs')->nullable()->after('contents');
            $table->json('top_keywords')->nullable()->after('faqs');
            $table->tinyInteger('imp')->default(0)->after('status');
            $table->tinyInteger('no_index')->default(0)->after('imp');
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->table('main_categories', function (Blueprint $table) {
            $table->dropColumn([
                'id_name',
                'canonical_link',
                'seo_emp_id',
                'meta_title',
                'primary_keyword',
                'h1_tag',
                'tag_line',
                'meta_desc',
                'short_desc',
                'h2_tag',
                'long_desc',
                'mockup',
                'banner',
                'app_id',
                'contents',
                'faqs',
                'top_keywords',
                'imp',
                'no_index'
            ]);
        });
    }
};
