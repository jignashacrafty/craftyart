<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cat = App\Models\Video\VideoCat::find(14);

if ($cat) {
    echo "ID: " . $cat->id . "\n";
    echo "Category Name: " . $cat->category_name . "\n";
    echo "Category Thumb: " . ($cat->category_thumb ?? 'NULL') . "\n";
    echo "Mockup: " . ($cat->mockup ?? 'NULL') . "\n";
    echo "Banner: " . ($cat->banner ?? 'NULL') . "\n";
} else {
    echo "Category not found\n";
}
