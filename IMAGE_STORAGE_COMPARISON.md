# Image Storage Comparison: Regular Templates vs Video Templates

## Summary
Regular templates અને video templates **exactly same pattern** use કરે છે images save કરવા માટે. બંને `StorageUtils::storeAs()` use કરે છે અને database માં relative path save કરે છે.

## Regular Templates (TemplateController.php)

### Post Thumb Storage:
```php
$post_thumb = $request->file('post_thumb');
$bytes = random_bytes(20);
$new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $post_thumb->getClientOriginalExtension();
StorageUtils::storeAs($post_thumb, 'uploadedFiles/thumb_file', $new_name);
$res->post_thumb = 'uploadedFiles/thumb_file/' . $new_name;
```

**Folder:** `uploadedFiles/thumb_file/`
**Database Value:** `uploadedFiles/thumb_file/abc123xyz456.jpg`
**Full URL:** `https://media.craftyartapp.com/uploadedFiles/thumb_file/abc123xyz456.jpg`

### Background Image Storage:
```php
StorageUtils::storeAs($back_image, 'uploadedFiles/bg_file', $new_name);
$res->back_image = 'uploadedFiles/bg_file/' . $new_name;
```

**Folder:** `uploadedFiles/bg_file/`

### Sticker Image Storage:
```php
StorageUtils::storeAs($st_image[$i], 'uploadedFiles/sticker_file', $new_name);
$st_data['st_image'] = 'uploadedFiles/sticker_file/' . $new_name;
```

**Folder:** `uploadedFiles/sticker_file/`

## Video Templates (Lottie/VideoTemplateController.php)

### Video Thumb Storage:
```php
$video_thumb = $request->file('video_thumb');
$bytes = random_bytes(20);
$new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $video_thumb->getClientOriginalExtension();
StorageUtils::storeAs($video_thumb, 'uploadedFiles/vThumb_file', $new_name);
$res->video_thumb = 'uploadedFiles/vThumb_file/' . $new_name;
```

**Folder:** `uploadedFiles/vThumb_file/`
**Database Value:** `uploadedFiles/vThumb_file/abc123xyz456.jpg`
**Full URL:** `https://media.craftyartapp.com/uploadedFiles/vThumb_file/abc123xyz456.jpg`

### Video File Storage:
```php
StorageUtils::storeAs($video_file, 'uploadedFiles/video_file', $new_name);
$res->video_url = 'uploadedFiles/video_file/' . $new_name;
```

**Folder:** `uploadedFiles/video_file/`

### Zip File Storage:
```php
StorageUtils::storeAs($zip_file, 'uploadedFiles/vZip_file', $new_name);
$res->video_zip_url = 'uploadedFiles/vZip_file/' . $new_name;
```

**Folder:** `uploadedFiles/vZip_file/`

## Video Categories (Lottie/VideoCatController.php)

### Category Thumb Storage:
```php
$image = $request->file('category_thumb');
$bytes = random_bytes(20);
$new_name = bin2hex($bytes) . Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
StorageUtils::storeAs($image, 'uploadedFiles/vCatThumb', $new_name);
$res->category_thumb = 'uploadedFiles/vCatThumb/' . $new_name;
```

**Folder:** `uploadedFiles/vCatThumb/`
**Database Value:** `uploadedFiles/vCatThumb/abc123xyz456.jpg`
**Full URL:** `https://media.craftyartapp.com/uploadedFiles/vCatThumb/abc123xyz456.jpg`

### Mockup Storage:
```php
StorageUtils::storeAs($mockup, 'uploadedFiles/vCatMockup', $new_name);
$res->mockup = 'uploadedFiles/vCatMockup/' . $new_name;
```

**Folder:** `uploadedFiles/vCatMockup/`

### Banner Storage:
```php
StorageUtils::storeAs($banner, 'uploadedFiles/vCatBanner', $new_name);
$res->banner = 'uploadedFiles/vCatBanner/' . $new_name;
```

**Folder:** `uploadedFiles/vCatBanner/`

## Storage Pattern Comparison

### ✅ SAME Pattern Used:

| Aspect | Regular Templates | Video Templates | Video Categories |
|--------|------------------|-----------------|------------------|
| **Method** | `StorageUtils::storeAs()` | `StorageUtils::storeAs()` | `StorageUtils::storeAs()` |
| **Filename** | `random_bytes(20) + timestamp + extension` | `random_bytes(20) + timestamp + extension` | `random_bytes(20) + timestamp + extension` |
| **DB Format** | `uploadedFiles/folder/file.jpg` | `uploadedFiles/folder/file.jpg` | `uploadedFiles/folder/file.jpg` |
| **Display** | `config('filesystems.storage_url') . $path` | `config('filesystems.storage_url') . $path` | `config('filesystems.storage_url') . $path` |

### Folder Structure:

```
storage/app/public/uploadedFiles/
├── thumb_file/          ← Regular template thumbs
├── bg_file/             ← Regular template backgrounds
├── sticker_file/        ← Regular template stickers
├── vThumb_file/         ← Video template thumbs
├── video_file/          ← Video files
├── vZip_file/           ← Video zip files
├── vCatThumb/           ← Video category thumbs
├── vCatMockup/          ← Video category mockups
└── vCatBanner/          ← Video category banners
```

## Display Pattern Comparison

### Regular Template View (item/edit_item.blade.php):
```php
<img src="{{ config('filesystems.storage_url') }}{{ $thumb }}"
```

### Video Template View (videos/edit_item.blade.php):
```php
<img src="{{ config('filesystems.storage_url') }}{{ $dataArray['item']->video_thumb }}"
```

### Video Category View (videos/show_cat.blade.php):
```php
<img src="{{ config('filesystems.storage_url') }}{{ $cat->category_thumb }}"
```

**બધા exactly same pattern use કરે છે!** ✅

## Configuration

### config/filesystems.php:
```php
'storage_url' => env('APP_ENV') != 'local' 
    ? env('STORAGE_URL', '') 
    : env('APP_URL') . '/storage/',
```

### Local Environment:
```
APP_ENV=local
APP_URL=http://localhost/git_jignasha/craftyart/public
Result: http://localhost/git_jignasha/craftyart/public/storage/
```

### Live Environment:
```
APP_ENV=production
STORAGE_URL=https://media.craftyartapp.com/
Result: https://media.craftyartapp.com/
```

## Why Live Images Not Showing

### ✅ Code is Correct!
Video templates use **exactly same pattern** as regular templates.

### ❌ Issue is File Upload
Files are not uploaded to media server:

1. **Local Storage:**
   ```
   C:\xampp\htdocs\git_jignasha\craftyart\storage\app\public\uploadedFiles\vThumb_file\
   C:\xampp\htdocs\git_jignasha\craftyart\storage\app\public\uploadedFiles\vCatThumb\
   ```

2. **Live Storage (Missing):**
   ```
   /path/to/media/server/uploadedFiles/vThumb_file/
   /path/to/media/server/uploadedFiles/vCatThumb/
   ```

## Solution: Upload Files to Media Server

### Step 1: Check Local Files
```bash
# Check what files exist locally
dir storage\app\public\uploadedFiles\vThumb_file
dir storage\app\public\uploadedFiles\vCatThumb
dir storage\app\public\uploadedFiles\vCatMockup
dir storage\app\public\uploadedFiles\vCatBanner
dir storage\app\public\uploadedFiles\video_file
dir storage\app\public\uploadedFiles\vZip_file
```

### Step 2: Upload to Media Server
```bash
# Using SCP (from local machine)
scp -r storage/app/public/uploadedFiles/vThumb_file/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/vThumb_file/
scp -r storage/app/public/uploadedFiles/vCatThumb/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/vCatThumb/
scp -r storage/app/public/uploadedFiles/vCatMockup/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/vCatMockup/
scp -r storage/app/public/uploadedFiles/vCatBanner/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/vCatBanner/
scp -r storage/app/public/uploadedFiles/video_file/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/video_file/
scp -r storage/app/public/uploadedFiles/vZip_file/* user@media.craftyartapp.com:/path/to/storage/uploadedFiles/vZip_file/
```

### Step 3: Set Permissions on Media Server
```bash
# SSH to media server
ssh user@media.craftyartapp.com

# Set permissions
cd /path/to/storage/uploadedFiles/
chmod -R 755 vThumb_file vCatThumb vCatMockup vCatBanner video_file vZip_file
chown -R www-data:www-data vThumb_file vCatThumb vCatMockup vCatBanner video_file vZip_file
```

### Step 4: Test Direct URLs
```
https://media.craftyartapp.com/uploadedFiles/vThumb_file/[filename-from-database]
https://media.craftyartapp.com/uploadedFiles/vCatThumb/[filename-from-database]
```

## Conclusion

### ✅ Video Templates Code is Perfect!
- Same storage method as regular templates
- Same display pattern as regular templates
- Same folder structure as regular templates
- Same database format as regular templates

### ❌ Only Issue: Files Not Uploaded
- Local files exist in `storage/app/public/uploadedFiles/`
- Live files missing from `https://media.craftyartapp.com/uploadedFiles/`
- Solution: Upload files and set permissions

**કોઈ code change કરવાની જરૂર નથી!** ફક્ત files upload કરો.
