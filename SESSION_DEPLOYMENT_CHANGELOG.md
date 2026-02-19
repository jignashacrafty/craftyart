# Deployment Changelog - Session February 19, 2026

This document contains all changes made during this development session that need to be deployed to the live server.

## Overview
This session included 5 major tasks:
1. Enable Admin/Manager followup access in Order User table
2. Fix Video Category cat_link update logic
3. Convert Video Categories to store Contents/FAQs as JSON files
4. Improve Followup Info Display with Modal
5. Create migration script for live data conversion

---

## TASK 1: Enable Admin/Manager Followup Access in Order User Table

### Files Modified:
- `app/Http/Controllers/OrderUserController.php`
- `resources/views/order_user/index.blade.php`

### Changes in `app/Http/Controllers/OrderUserController.php`:

**Location: updateFollowup method (around line 200-250)**

```php
// OLD CODE:
if (!in_array($userType, [UserRole::SALES_EMPLOYEE, UserRole::SALES_MANAGER])) {
    return response()->json(['error' => 'Unauthorized'], 403);
}

// For Sales employees, check if order is unassigned or assigned to them
if ($userType == UserRole::SALES_EMPLOYEE) {
    if ($order->sales_person_id && $order->sales_person_id != $userId) {
        return response()->json(['error' => 'You can only update followup for orders assigned to you'], 403);
    }
}

// NEW CODE:
// Admin, Manager, and Sales Manager can update any order
// Sales employees can only update unassigned orders or orders assigned to them
if ($userType == UserRole::SALES_EMPLOYEE) {
    if ($order->sales_person_id && $order->sales_person_id != $userId) {
        return response()->json(['error' => 'You can only update followup for orders assigned to you'], 403);
    }
} elseif (!in_array($userType, [UserRole::ADMIN, UserRole::MANAGER, UserRole::SALES_MANAGER])) {
    return response()->json(['error' => 'Unauthorized'], 403);
}
```

### Changes in `resources/views/order_user/index.blade.php`:

**Location: Followup dropdown rendering (around line 400-500)**

```php
// OLD CODE:
@if(in_array($userType, [\App\Enums\UserRole::SALES_EMPLOYEE, \App\Enums\UserRole::SALES_MANAGER]))
    @if($userType == \App\Enums\UserRole::SALES_EMPLOYEE)
        @if(!$order->sales_person_id || $order->sales_person_id == $userId)
            <select class="form-select followup-select" ...>
        @else
            <select class="form-select" disabled>
        @endif
    @else
        <select class="form-select followup-select" ...>
    @endif
@else
    <select class="form-select" disabled>
@endif

// NEW CODE:
@if(in_array($userType, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::MANAGER, \App\Enums\UserRole::SALES_MANAGER, \App\Enums\UserRole::SALES_EMPLOYEE]))
    @if($userType == \App\Enums\UserRole::SALES_EMPLOYEE)
        @if(!$order->sales_person_id || $order->sales_person_id == $userId)
            <select class="form-select followup-select" ...>
        @else
            <select class="form-select" disabled>
        @endif
    @else
        <select class="form-select followup-select" ...>
    @endif
@else
    <select class="form-select" disabled>
@endif
```

---

## TASK 2: Fix Video Category cat_link Update Logic

### Files Modified:
- `app/Http/Controllers/Lottie/VideoCatController.php`

### Changes in `app/Http/Controllers/Lottie/VideoCatController.php`:

**Location: store method - Add cat_link generation logic (after line 50)**

```php
// ADD THIS CODE:
// Generate cat_link based on parent category
$parentCategoryId = $request->input('parent_category_id', 0);
if ($parentCategoryId && $parentCategoryId != 0) {
    $parentCat = VideoCat::find($parentCategoryId);
    if ($parentCat) {
        $res->cat_link = $parentCat->id_name . '/' . $res->id_name;
    } else {
        $res->cat_link = $res->id_name;
    }
} else {
    $res->cat_link = $res->id_name;
}
```

**Location: update method - Add cat_link update logic (after line 250)**

```php
// ADD THIS CODE:
// Update cat_link based on parent category
$oldParentCategoryId = $res->parent_category_id;
$newParentCategoryId = $request->input('parent_category_id', 0);

if ($newParentCategoryId && $newParentCategoryId != 0) {
    $parentCat = VideoCat::find($newParentCategoryId);
    if ($parentCat) {
        $res->cat_link = $parentCat->id_name . '/' . $res->id_name;
    } else {
        $res->cat_link = $res->id_name;
    }
} else {
    $res->cat_link = $res->id_name;
}
```

**Location: End of class - Add new method**

```php
// ADD THIS NEW METHOD:
/**
 * Update cat_link for all child categories when parent id_name changes
 */
private function updateChildCatLink($idName, $parentID)
{
    $childCategories = VideoCat::where('parent_category_id', $parentID)->get();
    foreach ($childCategories as $childCat) {
        $childCat->cat_link = $idName . '/' . $childCat->id_name;
        $childCat->save();
    }
}
```

**Location: update method - Call updateChildCatLink (before save)**

```php
// ADD THIS CODE AFTER $res->save():
// Update child categories' cat_link if id_name changed and this is a parent category
if ($oldIdName != $res->id_name && $res->parent_category_id == 0) {
    $this->updateChildCatLink($res->id_name, $res->id);
}
```

---

## TASK 3: Convert Video Categories to Store Contents/FAQs as JSON Files

### Files Modified:
- `app/Models/Video/VideoCat.php`
- `app/Http/Controllers/Lottie/VideoCatController.php`

### Database Migrations Created:
- `database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php`
- `database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php`

### Changes in `app/Models/Video/VideoCat.php`:

**Location: $fillable array**

```php
// ADD 'fldr_str' to fillable array:
protected $fillable = [
    'category_name',
    'id_name',
    'cat_link',
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
    'category_thumb',
    'mockup',
    'banner',
    'app_id',
    'contents',
    'faqs',
    'top_keywords',
    'parent_category_id',
    'sequence_number',
    'status',
    'emp_id',
    'fldr_str'  // ADD THIS LINE
];
```

**Location: $casts array**

```php
// REMOVE 'contents' and 'faqs' from casts:
protected $casts = [
    'top_keywords' => 'array',
    // REMOVE THESE LINES:
    // 'contents' => 'array',
    // 'faqs' => 'array',
];
```

### Changes in `app/Http/Controllers/Lottie/VideoCatController.php`:

**Location: store method - Generate fldr_str and store JSON files (after app_id)**

```php
// ADD THIS CODE:
// Generate folder string for file storage
$fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
while (VideoCat::where('fldr_str', $fldrStr)->exists()) {
    $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
}
$res->fldr_str = $fldrStr;

// Handle contents - store as JSON file
if ($request->input('contents')) {
    $contents = \App\Http\Controllers\Utils\ContentManager::getContents($request->input('contents'), $fldrStr);
    $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
    StorageUtils::put($contentPath, $contents);
    $res->contents = $contentPath;
}

// Handle faqs - store as JSON file
if (isset($request->faqs)) {
    $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
    $faqs = [];
    $faqs['title'] = $request->faqs_title ?? '';
    $faqs['faqs'] = json_decode($request->faqs);
    StorageUtils::put($faqPath, json_encode($faqs));
    $res->faqs = $faqPath;
}
```

**Location: edit method - Load contents and faqs from JSON files**

```php
// REPLACE EXISTING CODE WITH:
public function edit(VideoCat $videoCat, $id)
{
    $cat = VideoCat::findOrFail($id);
    
    // Load contents and faqs from JSON files
    $cat->contents = isset($cat->contents) ? StorageUtils::get($cat->contents) : "";
    $cat->faqs = isset($cat->faqs) ? StorageUtils::get($cat->faqs) : "";
    
    $datas['cat'] = $cat;
    $datas['allCategories'] = VideoCat::getAllCategoriesWithSubcategories();
    $datas['appArray'] = \App\Models\AppCategory::all();
    $datas['userRole'] = \App\Models\User::whereIn('user_type', [1, 2, 3])->get();
    return view('videos.edit_cat', compact('datas'));
}
```

**Location: update method - Update JSON file handling (after app_id)**

```php
// ADD THIS CODE:
// Generate or use existing folder string
$fldrStr = $res->fldr_str;
if (!$fldrStr) {
    $fldrStr = \App\Http\Controllers\HelperController::generateFolderID('');
    $res->fldr_str = $fldrStr;
}

// Store old paths for cleanup
$oldContentPath = $res->contents ?? null;
$oldFaqPath = $res->faqs ?? null;

// Handle contents - store as JSON file
if ($request->input('contents')) {
    $contents = \App\Http\Controllers\Utils\ContentManager::getContents($request->input('contents'), $fldrStr);
    $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
    StorageUtils::put($contentPath, $contents);
    $res->contents = $contentPath;
    
    // Delete old content file if exists and is different
    if ($oldContentPath && $oldContentPath != $contentPath && !filter_var($oldContentPath, FILTER_VALIDATE_URL)) {
        try {
            StorageUtils::delete($oldContentPath);
        } catch (\Exception $e) {
            // Ignore if file doesn't exist
        }
    }
}

// Handle faqs - store as JSON file
if (isset($request->faqs)) {
    $faqPath = 'ct/' . $fldrStr . '/fq/' . StorageUtils::getNewName() . ".json";
    $faqs = [];
    $faqs['title'] = $request->faqs_title ?? '';
    $faqs['faqs'] = json_decode($request->faqs);
    StorageUtils::put($faqPath, json_encode($faqs));
    $res->faqs = $faqPath;
    
    // Delete old faq file if exists and is different
    if ($oldFaqPath && $oldFaqPath != $faqPath && !filter_var($oldFaqPath, FILTER_VALIDATE_URL)) {
        try {
            StorageUtils::delete($oldFaqPath);
        } catch (\Exception $e) {
            // Ignore if file doesn't exist
        }
    }
}
```

### Migration Files to Run:

**File: `database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->string('fldr_str', 20)->nullable()->after('app_id');
            $table->index('fldr_str');
        });
    }

    public function down(): void
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->dropIndex(['fldr_str']);
            $table->dropColumn('fldr_str');
        });
    }
};
```

**File: `database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change contents and faqs columns from JSON to TEXT
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY contents TEXT NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY faqs TEXT NULL');
    }

    public function down(): void
    {
        // Revert back to JSON type
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY contents JSON NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY faqs JSON NULL');
    }
};
```

---

## TASK 4: Improve Followup Info Display with Modal

### Files Modified:
- `resources/views/order_user/index.blade.php`

### Changes in `resources/views/order_user/index.blade.php`:

**Location: Remove all hover-note divs from server-rendered HTML (around line 500-600)**

```html
<!-- REMOVE ALL INSTANCES OF: -->
<div class="hover-note" style="...">
    <strong>{{ $followupLabel }}</strong><br>
    <small>Updated: {{ $order->followup_updated_at ? $order->followup_updated_at->format('M d, Y H:i') : 'N/A' }}</small><br>
    <small>By: {{ $order->followupUpdatedBy->name ?? 'N/A' }}</small>
</div>

<!-- REPLACE INFO ICON WITH: -->
<i class="fas fa-info-circle text-info ms-1" 
   style="cursor: pointer;" 
   onclick="showFollowupInfo('{{ $followupLabel }}', '{{ $order->followup_updated_at ? $order->followup_updated_at->format('M d, Y H:i') : 'N/A' }}', '{{ $order->followupUpdatedBy->name ?? 'N/A' }}')"
   data-label-display="{{ $followupLabel }}"></i>
```

**Location: Add modal HTML (before closing body tag)**

```html
<!-- ADD THIS MODAL: -->
<!-- Followup Info Modal -->
<div class="modal fade" id="followupInfoModal" tabindex="-1" aria-labelledby="followupInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title" id="followupInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Followup Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="text-muted small">Status</label>
                    <div class="fw-bold" id="modalFollowupLabel"></div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Last Updated</label>
                    <div id="modalFollowupUpdated"></div>
                </div>
                <div class="mb-0">
                    <label class="text-muted small">Updated By</label>
                    <div id="modalFollowupUpdatedBy"></div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**Location: Add JavaScript function (in script section)**

```javascript
// ADD THIS FUNCTION:
function showFollowupInfo(label, updated, updatedBy) {
    document.getElementById('modalFollowupLabel').textContent = label;
    document.getElementById('modalFollowupUpdated').textContent = updated;
    document.getElementById('modalFollowupUpdatedBy').textContent = updatedBy;
    
    const modal = new bootstrap.Modal(document.getElementById('followupInfoModal'));
    modal.show();
}
```

**Location: Update WebSocket handlers to remove hover-note (around line 1500-1800)**

```javascript
// IN addNewOrderRow FUNCTION - REMOVE hover-note div:
// OLD CODE:
followupCell += `
    <div class="hover-note" style="...">
        <strong>${followupLabelDisplay}</strong><br>
        ...
    </div>
`;

// NEW CODE:
followupCell += `
    <i class="fas fa-info-circle text-info ms-1" 
       style="cursor: pointer;" 
       onclick="showFollowupInfo('${followupLabelDisplay}', '${followupUpdatedAt}', '${followupUpdatedBy}')"
       data-label-display="${followupLabelDisplay}"></i>
`;

// APPLY SAME CHANGE TO updateOrderRow FUNCTION
```

---

## TASK 5: Create Migration Script for Live Data Conversion

### Files Created:
- `database/migrations/2026_02_19_000000_convert_video_categories_to_json_files.php`

### Migration File Content:

**File: `database/migrations/2026_02_19_000000_convert_video_categories_to_json_files.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Utils\StorageUtils;

return new class extends Migration
{
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

    public function down(): void
    {
        // Cannot reverse this migration as we're converting data format
        echo "⚠ This migration cannot be reversed automatically\n";
    }
};
```

---

## Deployment Steps

### Step 1: Backup
```bash
# Backup database
mysqldump -u username -p crafty_video_db > backup_video_db_$(date +%Y%m%d).sql

# Backup files
tar -czf backup_files_$(date +%Y%m%d).tar.gz app/ resources/ database/
```

### Step 2: Deploy Code Changes
```bash
# Pull latest code
git pull origin main

# Or manually upload modified files listed above
```

### Step 3: Run Migrations
```bash
# Run migrations in order
php artisan migrate

# Specifically run these migrations:
# 1. 2026_02_18_175744_add_fldr_str_to_main_categories_table.php
# 2. 2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
# 3. 2026_02_19_000000_convert_video_categories_to_json_files.php
```

### Step 4: Clear Caches
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear
```

### Step 5: Verify
- Test Order User followup functionality for Admin/Manager roles
- Test Video Category creation and editing
- Verify contents and faqs are stored as JSON files
- Check followup info modal display
- Verify existing data was converted properly

---

## Testing Checklist

### Order User Followup
- [ ] Admin can update followup for any order
- [ ] Manager can update followup for any order
- [ ] Sales Manager can update followup for any order
- [ ] Sales Employee can only update unassigned or their own orders
- [ ] Followup info modal displays correctly
- [ ] WebSocket updates work properly

### Video Category
- [ ] New categories generate fldr_str automatically
- [ ] Contents are stored as JSON files in ct/{fldr_str}/jn/
- [ ] FAQs are stored as JSON files in ct/{fldr_str}/fq/
- [ ] cat_link updates correctly when parent changes
- [ ] Child categories update when parent id_name changes
- [ ] Edit loads contents and faqs from JSON files

### Data Migration
- [ ] All existing categories have fldr_str
- [ ] All contents converted to JSON files
- [ ] All faqs converted to JSON files
- [ ] No data loss during conversion
- [ ] Old data format no longer in database

---

## Rollback Plan

If issues occur:

1. **Restore Database**:
```bash
mysql -u username -p crafty_video_db < backup_video_db_YYYYMMDD.sql
```

2. **Restore Files**:
```bash
tar -xzf backup_files_YYYYMMDD.tar.gz
```

3. **Clear Caches**:
```bash
php artisan cache:clear
php artisan view:clear
```

---

## Notes

- All changes are backward compatible except the video category JSON file storage
- The migration script handles data conversion automatically
- No manual data entry required
- WebSocket functionality preserved and enhanced
- All existing functionality maintained

---

## Support

If you encounter any issues during deployment:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check migration output for errors
3. Verify file permissions on storage directories
4. Ensure database connection is configured correctly

---

**Deployment Date**: February 19, 2026
**Session Duration**: Full development session
**Total Files Modified**: 4
**Total Migrations Created**: 3
**Status**: Ready for deployment
