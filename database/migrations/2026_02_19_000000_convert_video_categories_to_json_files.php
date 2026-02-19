<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\StorageUtils;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all video categories that have contents or faqs as TEXT/JSON data
        $categories = DB::connection('crafty_video_mysql')
            ->table('main_categories')
            ->whereNotNull('contents')
            ->orWhereNotNull('faqs')
            ->get();

        foreach ($categories as $category) {
            $updated = false;
            
            // Generate folder string if not exists
            if (empty($category->fldr_str)) {
                $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
                DB::connection('crafty_video_mysql')
                    ->table('main_categories')
                    ->where('id', $category->id)
                    ->update(['fldr_str' => $fldrStr]);
                $category->fldr_str = $fldrStr;
            }
            
            // Convert contents if it's not a file path
            if (!empty($category->contents) && !str_starts_with($category->contents, 'ct/')) {
                try {
                    // Try to decode if it's JSON string
                    $contentsData = json_decode($category->contents, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // If not valid JSON, treat as plain text
                        $contentsData = $category->contents;
                    }
                    
                    // Convert to proper format if needed
                    if (is_string($contentsData)) {
                        // Plain text, wrap in array
                        $contentsData = [['type' => 'text', 'value' => $contentsData]];
                    }
                    
                    // Save to JSON file
                    $contentPath = 'ct/' . $category->fldr_str . '/jn/' . StorageUtils::getNewName() . ".json";
                    StorageUtils::put($contentPath, json_encode($contentsData));
                    
                    DB::connection('crafty_video_mysql')
                        ->table('main_categories')
                        ->where('id', $category->id)
                        ->update(['contents' => $contentPath]);
                    
                    $updated = true;
                    echo "✓ Converted contents for category ID: {$category->id}\n";
                } catch (\Exception $e) {
                    echo "✗ Error converting contents for category ID: {$category->id} - {$e->getMessage()}\n";
                }
            }
            
            // Convert faqs if it's not a file path
            if (!empty($category->faqs) && !str_starts_with($category->faqs, 'ct/')) {
                try {
                    // Try to decode if it's JSON string
                    $faqsData = json_decode($category->faqs, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // If not valid JSON, treat as plain text
                        $faqsData = ['title' => '', 'faqs' => []];
                    }
                    
                    // Ensure proper structure
                    if (!isset($faqsData['title'])) {
                        $faqsData = ['title' => '', 'faqs' => $faqsData];
                    }
                    
                    // Save to JSON file
                    $faqPath = 'ct/' . $category->fldr_str . '/fq/' . StorageUtils::getNewName() . ".json";
                    StorageUtils::put($faqPath, json_encode($faqsData));
                    
                    DB::connection('crafty_video_mysql')
                        ->table('main_categories')
                        ->where('id', $category->id)
                        ->update(['faqs' => $faqPath]);
                    
                    $updated = true;
                    echo "✓ Converted faqs for category ID: {$category->id}\n";
                } catch (\Exception $e) {
                    echo "✗ Error converting faqs for category ID: {$category->id} - {$e->getMessage()}\n";
                }
            }
            
            if ($updated) {
                echo "✓ Category ID {$category->id} updated successfully\n";
            }
        }
        
        echo "\n✓ Migration completed!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot reverse this migration as we're converting data format
        echo "⚠ This migration cannot be reversed automatically\n";
    }
};
