# Deployment Quick Reference

**Date:** February 18, 2026  
**Files Changed:** 10 files  
**Migrations:** 4 new migrations  

---

## Quick File List

### Modified Files (10)
```
app/Http/Controllers/OrderUserController.php
app/Http/Controllers/Lottie/VideoCatController.php
app/Http/Controllers/Lottie/VideoTemplateController.php
app/Http/Controllers/Utils/HelperController.php
app/Models/Video/VideoCat.php
resources/views/order_user/index.blade.php
resources/views/videos/edit_cat.blade.php
resources/views/videos/edit_seo_item.blade.php
resources/views/videos/show_cat.blade.php
resources/views/videos/show_item.blade.php
```

### New Migration Files (4)
```
database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php
database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php
database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php
database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
```

---

## Quick Deploy Commands

```bash
# 1. Backup (on live server)
mkdir -p backups/$(date +%Y%m%d_%H%M%S)
cp app/Http/Controllers/OrderUserController.php backups/$(date +%Y%m%d_%H%M%S)/
cp app/Http/Controllers/Lottie/VideoCatController.php backups/$(date +%Y%m%d_%H%M%S)/
cp app/Http/Controllers/Lottie/VideoTemplateController.php backups/$(date +%Y%m%d_%H%M%S)/
cp app/Http/Controllers/Utils/HelperController.php backups/$(date +%Y%m%d_%H%M%S)/
cp app/Models/Video/VideoCat.php backups/$(date +%Y%m%d_%H%M%S)/
cp resources/views/order_user/index.blade.php backups/$(date +%Y%m%d_%H%M%S)/
cp resources/views/videos/edit_cat.blade.php backups/$(date +%Y%m%d_%H%M%S)/
cp resources/views/videos/edit_seo_item.blade.php backups/$(date +%Y%m%d_%H%M%S)/
cp resources/views/videos/show_cat.blade.php backups/$(date +%Y%m%d_%H%M%S)/
cp resources/views/videos/show_item.blade.php backups/$(date +%Y%m%d_%H%M%S)/

# 2. Upload files (use FTP/SFTP or git pull)

# 3. Run migrations
php artisan migrate --path=database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php
php artisan migrate --path=database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php
php artisan migrate --path=database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
php artisan migrate --path=database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php

# 4. Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

# 5. Restart services (if needed)
# Restart WebSocket
php artisan websockets:serve &

# Restart queue workers (if running)
php artisan queue:restart
```

---

## What Changed?

### Order User Module
✅ Admin/Manager/Sales Manager can now edit all followups  
✅ Followup info shows in beautiful modal instead of hover  
✅ Removed uses_type field from followup form  
✅ Redesigned Add Transaction modal  

### Video Category Module
✅ Auto-generate cat_link based on parent category  
✅ Store contents/faqs as JSON files (not in database)  
✅ Update child categories when parent changes  
✅ Fixed image preview in edit form  

### Video Template Module
✅ Role-based access control (Admin/SEO Manager/SEO Executive)  
✅ Added No Index toggle functionality  
✅ SEO Executive auto-assigned when editing unassigned items  
✅ Restricted Add New and Edit buttons  

### Helper Controller
✅ Added caricature URL generation (type 5)  

---

## Testing Checklist

### Critical Tests
- [ ] Admin can edit any order followup
- [ ] Followup modal displays correctly
- [ ] Video category cat_link generates properly
- [ ] Contents/FAQs save as JSON files
- [ ] No Index toggle works
- [ ] Role-based filtering works correctly

### Quick Test URLs
```
Order User: /order_user
Video Categories: /show_v_cat
Video Templates: /show_v_item
```

---

## Rollback (if needed)

```bash
# Restore files from backup
BACKUP_DIR="backups/YYYYMMDD_HHMMSS"  # Replace with actual backup directory
cp $BACKUP_DIR/* ./

# Rollback migrations
php artisan migrate:rollback --step=4

# Clear caches
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## Support

**Full Documentation:**
- `LIVE_DEPLOYMENT_CHANGELOG.md` - Complete changelog with all details
- `MIGRATION_FILES_CONTENT.md` - Full migration file content

**Issues?**
Contact development team immediately if:
- Migrations fail
- Followup modal doesn't show
- Video categories don't save
- Role-based access not working

---

**Deployment Status:**

Date Deployed: _______________  
Deployed By: _______________  
Tested By: _______________  
Status: ⬜ Success  ⬜ Failed  ⬜ Rolled Back

---

**END OF QUICK REFERENCE**
