<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('designer_applications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_enrolled')->default(false)->after('status');
            $table->timestamp('enrolled_at')->nullable()->after('is_enrolled');
            $table->json('selected_types')->nullable()->after('skills'); // What they create
            $table->json('selected_categories')->nullable()->after('selected_types'); // Their interests
            $table->json('selected_goals')->nullable()->after('selected_categories'); // Their motivations
            $table->enum('experience_level', ['entry-level', 'mid-level', 'senior', 'expert'])->nullable()->after('experience');
            $table->boolean('has_chosen_plan')->default(false)->after('is_enrolled');
            $table->string('chosen_plan')->nullable()->after('has_chosen_plan');
        });
    }

    public function down()
    {
        Schema::table('designer_applications', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id',
                'is_enrolled',
                'enrolled_at',
                'selected_types',
                'selected_categories',
                'selected_goals',
                'experience_level',
                'has_chosen_plan',
                'chosen_plan'
            ]);
        });
    }
};
