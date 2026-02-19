# Live Deployment Changelog
**Date:** February 18, 2026  
**Purpose:** Deploy uncommitted changes for Order User, Video Category, and Video Template features

---

## Overview

This document contains all uncommitted changes that need to be deployed to the live server. The changes include:

1. **Order User Followup System** - Admin/Manager can now edit followup for all orders
2. **Video Category Management** - Fixed cat_link update logic and JSON file storage for contents/faqs
3. **Video Template Management** - Added role-based access control and No Index functionality
4. **Helper Controller** - Added caricature URL generation support

---

## Files Modified

### Backend Controllers (4 files)
1. `app/Http/Controllers/OrderUserController.php`
2. `app/Http/Controllers/Lottie/VideoCatController.php`
3. `app/Http/Controllers/Lottie/VideoTemplateController.php`
4. `app/Http/Controllers/Utils/HelperController.php`

### Models (1 file)
5. `app/Models/Video/VideoCat.php`

### Views (5 files)
6. `resources/views/order_user/index.blade.php`
7. `resources/views/videos/edit_cat.blade.php`
8. `resources/views/videos/edit_seo_item.blade.php`
9. `resources/views/videos/show_cat.blade.php`
10. `resources/views/videos/show_item.blade.php`

---

## Database Migrations Required

### Video Category Migrations (2 files)

```bash
# Run these migrations in order:
php artisan migrate --path=database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php
php artisan migrate --path=database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php
php artisan migrate --path=database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
php artisan migrate --path=database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php
```

---

## Detailed Changes

### 1. app/Http/Controllers/OrderUserController.php

**Purpose:** Allow Admin/Manager/Sales Manager to edit followup for all orders

**Key Changes:**
- Removed restriction that prevented Admin/Manager from updating followup
- Added Sales Manager to the list of roles that can edit all followups
- Removed `uses_type` field handling from followup update
- Added new `addTransactionManually()` method for manual transaction creation
- Fixed GROUP BY clause in checkout drop query

**Lines Changed:** ~150 lines modified/added

**Critical Code:**
```php
// Admin, Manager, and Sales Manager can always update followup
if ($isAdminOrManager) {
    // Allow update
}
// Sales user can only update if order is not assigned or assigned to them
elseif ($isSalesUser) {
    if (!empty($orderUser->emp_id) && $orderUser->emp_id != 0 && $orderUser->emp_id != $currentUser->id) {
        return response()->json([
            'success' => false,
            'message' => 'This order is assigned to another Sales user.'
        ], 403);
    }
}
```

---

### 2. app/Http/Controllers/Lottie/VideoCatController.php

**Purpose:** Fix cat_link generation and store contents/faqs as JSON files

**Key Changes:**

- Generate `cat_link` based on parent category (same logic as NewCategoryController)
- Generate unique `fldr_str` for each category
- Store `contents` and `faqs` as JSON files in storage instead of database
- Delete old JSON files when updating
- Added `updateChildCatLink()` method to update child categories when parent id_name changes
- Fixed access control to use SEO roles instead of design roles
- Load contents/faqs from JSON files in edit method

**Lines Changed:** ~200 lines modified/added

**Critical Code:**
```php
// Generate cat_link based on parent category
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

// Store contents as JSON file
if ($request->input('contents')) {
    $contents = \App\Http\Controllers\Utils\ContentManager::getContents($request->input('contents'), $fldrStr);
    $contentPath = 'ct/' . $fldrStr . '/jn/' . StorageUtils::getNewName() . ".json";
    StorageUtils::put($contentPath, $contents);
    $res->contents = $contentPath;
}
```

---

### 3. app/Http/Controllers/Lottie/VideoTemplateController.php

**Purpose:** Add role-based access control and No Index functionality

**Key Changes:**
- Added role-based filtering (Admin/SEO Manager see all, SEO Executive sees unassigned + own, others see only own)
- Added validation for `relation_id` to ensure it's numeric
- Added access control in `editSeo()` and `updateSeo()` methods
- Auto-assign unassigned items to SEO Executive when they edit
- Added `no_index` field handling in update
- Added new `noindex_update()` method for toggling No Index status
- Cleaned up unused imports

**Lines Changed:** ~150 lines modified/added

**Critical Code:**
```php
// Role-based filtering
if ($isSeoExecutive) {
    // SEO Executive can see: emp_id = 0 (unassigned) OR emp_id = current_user_id (their own)
    $itemsQuery->where(function($q) use ($currentuserid) {
        $q->where('emp_id', 0)
          ->orWhere('emp_id', $currentuserid);
    });
} elseif (!$isAdminOrManager) {
    // Other users can only see their own items
    $itemsQuery->where('emp_id', $currentuserid);
}
// Admin and SEO Manager can see all items (no filter)
```

---

### 4. app/Http/Controllers/Utils/HelperController.php

**Purpose:** Add caricature URL generation support

**Key Changes:**
- Added type 5 case for caricature URLs
- Returns frontend URL with 'caricature/p' prefix

**Lines Changed:** 2 lines added

**Code:**
```php
} else if($type == 5) {
    return self::$frontendUrl .'caricature/p' . $slug;
}
```

---

### 5. app/Models/Video/VideoCat.php

**Purpose:** Update model to support JSON file storage

**Key Changes:**
- Added `cat_link` and `fldr_str` to fillable fields
- Removed `contents` and `faqs` from casts (no longer stored as JSON in database)
- Kept `top_keywords` as array cast

**Lines Changed:** 5 lines modified

**Code:**
```php
protected $fillable = [
    'category_name',
    'id_name',
    'cat_link',  // Added
    // ... other fields
    'fldr_str'   // Added
];

protected $casts = [
    'top_keywords' => 'array',  // Only this remains
];
```

---

### 6. resources/views/order_user/index.blade.php

**Purpose:** Improve followup UI and fix modal display

**Key Changes:**

- Removed hover-note divs, replaced with modal for followup info
- Added beautiful gradient-styled `followupInfoModal`
- Info icon now shows modal on click instead of hover
- Removed edit button from followup modal
- Updated JavaScript to populate modal with followup details
- Fixed row click behavior - only Order ID column is clickable now
- Removed `uses_type` field from followup form
- Redesigned Add Transaction modal with modern UI
- Added `usage_purpose` field to transaction form
- Updated WebSocket handlers to use new modal format
- Added filter-based row hiding when followup status changes

**Lines Changed:** ~500 lines modified/added

**Critical Changes:**
```javascript
// Info Icon Click - Show modal with followup details
$(document).off("click", ".info-icon").on("click", ".info-icon", function(e) {
    e.stopPropagation();
    e.stopImmediatePropagation();

    let id = $(this).data("id");
    let note = $(this).data("note");
    let label = $(this).data("label");
    let labelDisplay = $(this).data("label-display");

    // Populate modal
    $("#followupInfoLabel").text(labelDisplay || '-');
    $("#followupInfoNote").text(note || '-');

    // Show modal
    $("#followupInfoModal").modal("show");
});
```

**CSS Changes:**
- Only Order ID column is clickable (cursor: pointer)
- Removed hover-note styles
- Added responsive styles for transaction modal
- Added gradient header styles for modals

---

### 7. resources/views/videos/edit_cat.blade.php

**Purpose:** Fix image preview and use dynamic file component

**Key Changes:**
- Replaced manual image preview HTML with dynamic-file component
- Added `data-value` attribute with full storage URL
- Uses `$contentManager::getStorageLink()` for URL generation
- Handles both full URLs and relative paths
- Added reinitialization of dynamic file component in JavaScript

**Lines Changed:** ~80 lines modified

**Code:**
```blade
<input type="file" 
    class="form-control-file form-control height-auto dynamic-file"
    data-accept=".jpg, .jpeg, .webp, .svg"
    data-imgstore-id="category_thumb" 
    data-nameset=true
    data-required=false
    data-value="{{ $datas['cat']->category_thumb && !str_contains($datas['cat']->category_thumb, 'no_image') ? $contentManager::getStorageLink($datas['cat']->category_thumb) : '' }}">
```

---

### 8. resources/views/videos/edit_seo_item.blade.php

**Purpose:** Add No Index field to SEO edit form

**Key Changes:**
- Added No Index dropdown field
- Default value is 1 (TRUE - noindex)
- Includes helper text explaining the field

**Lines Changed:** 19 lines added

**Code:**
```blade
<div class="form-group">
    <h6>No Index</h6>
    <select class="selectpicker form-control" data-style="btn-outline-primary" name="no_index">
        @if (($dataArray['item']->no_index ?? 1) == '1')
            <option value="1" selected>TRUE</option>
            <option value="0">FALSE</option>
        @else
            <option value="1">TRUE</option>
            <option value="0" selected>FALSE</option>
        @endif
    </select>
    <small class="text-muted">TRUE = noindex (not indexed by search engines)</small>
</div>
```

---

### 9. resources/views/videos/show_cat.blade.php

**Purpose:** Fix image display for full URLs

**Key Changes:**
- Check if image path is full URL before prepending storage URL
- Handles both full URLs and relative paths
- Applied to category_thumb and mockup fields

**Lines Changed:** 14 lines modified

**Code:**
```blade
@php
    $thumbUrl = filter_var($cat->category_thumb, FILTER_VALIDATE_URL) 
        ? $cat->category_thumb 
        : config('filesystems.storage_url') . $cat->category_thumb;
@endphp
<img src="{{ $thumbUrl }}" ... />
```

---

### 10. resources/views/videos/show_item.blade.php

**Purpose:** Add No Index toggle and restrict Add New button

**Key Changes:**
- Added No Index column to table
- Added toggle switch for Admin/SEO Manager to change No Index status
- Show static badge for other users
- Restricted "Add New Item" button to Admin/SEO Manager only
- Restricted "Edit" action to Admin/SEO Manager only
- Added `noindex_click()` JavaScript function

**Lines Changed:** ~60 lines added

**Code:**
```javascript
function noindex_click(parentElement, $id) {
    let element = parentElement.firstElementChild;
    const originalChecked = element.checked;

    $.ajax({
        url: "{{ route('v_item.noindex', ':id') }}".replace(':id', $id),
        type: 'POST',
        success: function(data) {
            if (data.success) {
                var x = document.getElementById("noindex_label_" + $id);
                if (x.innerHTML === "TRUE") {
                    x.innerHTML = "FALSE";
                } else {
                    x.innerHTML = "TRUE";
                }
            }
        }
    });
}
```

---

## Deployment Steps

### Step 1: Backup Current Files
```bash
# On live server, backup these files first
cp app/Http/Controllers/OrderUserController.php app/Http/Controllers/OrderUserController.php.backup
cp app/Http/Controllers/Lottie/VideoCatController.php app/Http/Controllers/Lottie/VideoCatController.php.backup
cp app/Http/Controllers/Lottie/VideoTemplateController.php app/Http/Controllers/Lottie/VideoTemplateController.php.backup
cp app/Http/Controllers/Utils/HelperController.php app/Http/Controllers/Utils/HelperController.php.backup
cp app/Models/Video/VideoCat.php app/Models/Video/VideoCat.php.backup
cp resources/views/order_user/index.blade.php resources/views/order_user/index.blade.php.backup
cp resources/views/videos/edit_cat.blade.php resources/views/videos/edit_cat.blade.php.backup
cp resources/views/videos/edit_seo_item.blade.php resources/views/videos/edit_seo_item.blade.php.backup
cp resources/views/videos/show_cat.blade.php resources/views/videos/show_cat.blade.php.backup
cp resources/views/videos/show_item.blade.php resources/views/videos/show_item.blade.php.backup
```

### Step 2: Upload Modified Files
Upload all 10 modified files to their respective locations on the live server.

### Step 3: Upload Migration Files
Upload these 4 new migration files to `database/migrations/`:
- `2026_02_17_000000_add_cat_link_to_video_categories.php`
- `2026_02_18_000000_add_noindex_to_video_templates.php`
- `2026_02_18_175744_add_fldr_str_to_main_categories_table.php`
- `2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php`

### Step 4: Run Migrations
```bash
cd /path/to/live/server
php artisan migrate --path=database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php
php artisan migrate --path=database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php
php artisan migrate --path=database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
php artisan migrate --path=database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php
```

### Step 5: Clear All Caches
```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear compiled classes
php artisan clear-compiled

# Optimize for production
php artisan optimize
```

### Step 6: Verify Deployment
1. Test Order User followup editing as Admin/Manager
2. Test Video Category creation/editing with cat_link generation
3. Test Video Template No Index toggle
4. Check that contents/faqs are stored as JSON files
5. Verify followup info modal displays correctly

---

## Rollback Plan

If issues occur, rollback using:

```bash
# Restore backup files
mv app/Http/Controllers/OrderUserController.php.backup app/Http/Controllers/OrderUserController.php
mv app/Http/Controllers/Lottie/VideoCatController.php.backup app/Http/Controllers/Lottie/VideoCatController.php
mv app/Http/Controllers/Lottie/VideoTemplateController.php.backup app/Http/Controllers/Lottie/VideoTemplateController.php
mv app/Http/Controllers/Utils/HelperController.php.backup app/Http/Controllers/Utils/HelperController.php
mv app/Models/Video/VideoCat.php.backup app/Models/Video/VideoCat.php
mv resources/views/order_user/index.blade.php.backup resources/views/order_user/index.blade.php
mv resources/views/videos/edit_cat.blade.php.backup resources/views/videos/edit_cat.blade.php
mv resources/views/videos/edit_seo_item.blade.php.backup resources/views/videos/edit_seo_item.blade.php
mv resources/views/videos/show_cat.blade.php.backup resources/views/videos/show_cat.blade.php
mv resources/views/videos/show_item.blade.php.backup resources/views/videos/show_item.blade.php

# Rollback migrations (if needed)
php artisan migrate:rollback --step=4

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## Testing Checklist

### Order User Module
- [ ] Admin can edit followup for all orders
- [ ] Manager can edit followup for all orders
- [ ] Sales Manager can edit followup for all orders
- [ ] Sales user can edit followup for unassigned or own orders
- [ ] Followup info modal displays correctly on info icon click
- [ ] Modal shows label and note properly
- [ ] WebSocket updates work correctly
- [ ] Add Transaction modal works with new usage_purpose field

### Video Category Module
- [ ] New categories generate correct cat_link
- [ ] Child categories update when parent id_name changes
- [ ] Contents stored as JSON file in storage
- [ ] FAQs stored as JSON file in storage
- [ ] Old JSON files deleted on update
- [ ] Images display correctly (both URLs and paths)
- [ ] IMP toggle works for Admin/SEO Manager only

### Video Template Module
- [ ] Admin sees all items
- [ ] SEO Manager sees all items
- [ ] SEO Executive sees unassigned + own items
- [ ] Other users see only own items
- [ ] No Index toggle works for Admin/SEO Manager
- [ ] No Index field in SEO edit form works
- [ ] Add New button restricted to Admin/SEO Manager
- [ ] Edit action restricted to Admin/SEO Manager

---

## Notes

1. **Database Changes:** The migrations modify the `main_categories` table structure. Ensure database backup before running migrations.

2. **Storage Folder:** Ensure `storage/app/public/ct/` folder exists and has write permissions.

3. **WebSocket:** If WebSocket is running, restart it after deployment:
   ```bash
   php artisan websockets:serve
   ```

4. **Route Cache:** If routes are cached in production, clear route cache after deployment.

5. **Session:** Users may need to logout/login to see role-based changes properly.

---

## Contact

For issues or questions during deployment, contact the development team.

**Deployment Date:** _____________  
**Deployed By:** _____________  
**Verified By:** _____________

---

**END OF CHANGELOG**
