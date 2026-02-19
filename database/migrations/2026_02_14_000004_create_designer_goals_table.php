<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('designer_goals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default goals based on Canva's creator motivations
        $goals = [
            ['name' => 'Earnings', 'slug' => 'earnings', 'description' => 'Generate income from designs', 'sort_order' => 1],
            ['name' => 'Marketing', 'slug' => 'marketing', 'description' => 'Market and promote work', 'sort_order' => 2],
            ['name' => 'Community', 'slug' => 'community', 'description' => 'Build community and connections', 'sort_order' => 3],
            ['name' => 'Building a Portfolio', 'slug' => 'portfolio', 'description' => 'Build professional portfolio', 'sort_order' => 4],
            ['name' => 'Building a Profile', 'slug' => 'profile', 'description' => 'Build online presence', 'sort_order' => 5],
            ['name' => 'Creative Freedom', 'slug' => 'creative-freedom', 'description' => 'Express creative freedom', 'sort_order' => 6],
            ['name' => 'Contribution to Platform Success', 'slug' => 'platform-contribution', 'description' => 'Contribute to platform growth', 'sort_order' => 7],
            ['name' => 'Exposure and Marketing', 'slug' => 'exposure', 'description' => 'Gain exposure and visibility', 'sort_order' => 8],
            ['name' => 'Impact and Influence', 'slug' => 'impact', 'description' => 'Make impact and influence', 'sort_order' => 9],
            ['name' => 'Professional Growth and Recognition', 'slug' => 'growth', 'description' => 'Professional development', 'sort_order' => 10],
        ];

        foreach ($goals as $goal) {
            DB::table('designer_goals')->insert(array_merge($goal, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('designer_goals');
    }
};
