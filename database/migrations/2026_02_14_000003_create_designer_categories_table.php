<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('designer_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories based on Canva's creative interests
        $categories = [
            ['name' => 'Design Trends', 'slug' => 'design-trends', 'description' => 'Latest design trends and styles', 'sort_order' => 1],
            ['name' => 'UI/UX Design', 'slug' => 'ui-ux-design', 'description' => 'User interface and experience design', 'sort_order' => 2],
            ['name' => 'Typography', 'slug' => 'typography', 'description' => 'Typography and font design', 'sort_order' => 3],
            ['name' => 'Creative Collaboration', 'slug' => 'creative-collaboration', 'description' => 'Collaborative design work', 'sort_order' => 4],
            ['name' => 'Branding and Identity', 'slug' => 'branding-identity', 'description' => 'Brand identity design', 'sort_order' => 5],
            ['name' => 'Digital Illustration', 'slug' => 'digital-illustration', 'description' => 'Digital illustration and art', 'sort_order' => 6],
            ['name' => 'Motion Design and Animation', 'slug' => 'motion-animation', 'description' => 'Motion graphics and animation', 'sort_order' => 7],
            ['name' => 'Web and App Design', 'slug' => 'web-app-design', 'description' => 'Web and mobile app design', 'sort_order' => 8],
            ['name' => 'Design Systems', 'slug' => 'design-systems', 'description' => 'Design system creation', 'sort_order' => 9],
            ['name' => '3D Design and AR/VR', 'slug' => '3d-ar-vr', 'description' => '3D design and virtual reality', 'sort_order' => 10],
            ['name' => 'Case Studies and Portfolios', 'slug' => 'case-studies', 'description' => 'Portfolio and case study design', 'sort_order' => 11],
            ['name' => 'Creative Process and Workflow', 'slug' => 'creative-process', 'description' => 'Design process and workflow', 'sort_order' => 12],
            ['name' => 'Design Tools and Mastery', 'slug' => 'design-tools', 'description' => 'Design tool expertise', 'sort_order' => 13],
            ['name' => 'Prototyping and Interaction Design', 'slug' => 'prototyping', 'description' => 'Prototyping and interaction', 'sort_order' => 14],
        ];

        foreach ($categories as $category) {
            DB::table('designer_categories')->insert(array_merge($category, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('designer_categories');
    }
};
