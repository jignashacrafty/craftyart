# Deployment Package Creation Instructions

## When User Asks for Deployment Package

જ્યારે user કહે: "give me deployment package" અથવા "live krvanu che" અથવા "changes and new files apo"

## Steps to Follow:

### 1. Check Git Status
```bash
git status --short
```

### 2. Identify Files
- **Modified files (M)** → CHANGES folder માં જાય
- **New files (??)** → NEW_FILES folder માં જાય
- **Deleted files (D)** → Ignore કરો

### 3. Create Package Structure
```
DEPLOYMENT_PACKAGE_[FEATURE_NAME]/
├── CHANGES/
│   ├── app/
│   ├── resources/
│   ├── public/
│   ├── config/
│   └── database/ (only if migrations modified)
├── NEW_FILES/
│   ├── database/migrations/
│   ├── resources/views/
│   └── app/ (only if new files)
└── DEPLOY.md (deployment commands)
```

### 4. Copy Files to Package

**CHANGES Folder:**
- Copy only modified files related to current feature
- Maintain directory structure
- Exclude unrelated changes

**NEW_FILES Folder:**
- Copy only new files created for current feature
- Maintain directory structure
- Typically: migrations, new views, new controllers

### 5. Create DEPLOY.md

Include:
- Step-by-step deployment commands
- Backup commands
- Migration commands (with correct database connection)
- Cache clear commands
- Rollback commands
- Testing URLs
- Common issues & solutions

### 6. Format for DEPLOY.md

```markdown
# Deployment Guide - [Feature Name]

## Files Summary
- CHANGES: X modified files
- NEW_FILES: Y new files

## Step 1: Backup
[backup commands]

## Step 2: Upload Package
[upload instructions]

## Step 3: Deploy CHANGES
[copy commands for each file]

## Step 4: Deploy NEW_FILES
[copy commands for each file]

## Step 5: Run Migrations
[migration commands with correct database]

## Step 6: Clear Caches
[cache clear commands]

## Step 7: Test URLs
[list of URLs to test]

## Rollback
[rollback commands]
```

### 7. Important Rules

✅ **DO:**
- Only include files related to current feature
- Maintain exact directory structure
- Provide specific copy commands for each file
- Include database name in migration commands
- Add testing URLs
- Add rollback instructions

❌ **DON'T:**
- Create summary MD files (README, SUMMARY, COMPLETE_FILE_LIST)
- Include unrelated modified files
- Mix different features in one package
- Forget migration database connection name

### 8. Example Commands Format

**For CHANGES:**
```bash
cp DEPLOYMENT_PACKAGE/CHANGES/app/Http/Controllers/XController.php app/Http/Controllers/
```

**For NEW_FILES:**
```bash
cp DEPLOYMENT_PACKAGE/NEW_FILES/database/migrations/2026_xx_xx_xxx.php database/migrations/
```

**For Migrations:**
```bash
php artisan migrate --database=crafty_video_mysql
# or
php artisan migrate --database=mysql
```

### 9. Verification

Before giving package to user:
- Count files in CHANGES folder
- Count files in NEW_FILES folder
- Verify all paths are correct
- Test one copy command to ensure path is right
- Check migration database connection name

### 10. Response Format

```
✅ Deployment Package Ready!

**Feature:** [Feature Name]
**Total Files:** X (Y modified + Z new)

**CHANGES Folder:** Y files
- Controllers: X
- Models: X
- Views: X
- Config: X
- JS: X

**NEW_FILES Folder:** Z files
- Migrations: X
- Views: X
- Controllers: X

**Deployment Guide:** DEPLOYMENT_PACKAGE_[FEATURE]/DEPLOY.md

તમે હવે DEPLOY.md માં આપેલા commands follow કરીને production માં deploy કરી શકો છો.
```

## Database Connections to Remember

- **crafty_video_mysql** → Video categories, video items (crafty_video_db)
- **mysql** → Main application database (default)

## Common Features & Their Files

**Video Categories:**
- Controllers: VideoCatController, VideoTemplateController
- Models: Video/VideoCat
- Views: videos/show_cat, videos/create_cat, videos/edit_cat
- Database: crafty_video_mysql

**Template Categories:**
- Controllers: NewCategoryController
- Models: NewCategory
- Views: main_new_cat/show_new_cat
- Database: mysql

**Payment:**
- Controllers: PaymentController, OrderUserController
- Models: Order, PaymentConfiguration
- Views: payment/*, order/*
- Database: mysql

---

**Last Updated:** February 9, 2026
**Created By:** Kiro AI Assistant
