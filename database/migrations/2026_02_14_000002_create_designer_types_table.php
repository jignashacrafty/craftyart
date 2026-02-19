<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('designer_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default types based on Canva's creative specialties
        $types = [
            ['name' => 'Social Media', 'slug' => 'social-media', 'description' => 'Social media content creators', 'sort_order' => 1],
            ['name' => 'Print', 'slug' => 'print', 'description' => 'Print design specialists', 'sort_order' => 2],
            ['name' => 'Presentations', 'slug' => 'presentations', 'description' => 'Presentation designers', 'sort_order' => 3],
            ['name' => 'Whiteboards', 'slug' => 'whiteboards', 'description' => 'Whiteboard and diagram creators', 'sort_order' => 4],
            ['name' => 'Documents', 'slug' => 'documents', 'description' => 'Document designers', 'sort_order' => 5],
            ['name' => 'Websites', 'slug' => 'websites', 'description' => 'Website designers', 'sort_order' => 6],
            ['name' => 'Video', 'slug' => 'video', 'description' => 'Video content creators', 'sort_order' => 7],
            ['name' => 'Photos', 'slug' => 'photos', 'description' => 'Photo editors and creators', 'sort_order' => 8],
            ['name' => 'Graphics', 'slug' => 'graphics', 'description' => 'Graphic designers', 'sort_order' => 9],
            ['name' => 'Motion', 'slug' => 'motion', 'description' => 'Motion graphics designers', 'sort_order' => 10],
        ];

        foreach ($types as $type) {
            DB::table('designer_types')->insert(array_merge($type, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('designer_types');
    }
};
