# Migration Files Content

This document contains the full content of all migration files that need to be uploaded to the live server.

---

## 1. 2026_02_17_000000_add_cat_link_to_video_categories.php

**Purpose:** Add `cat_link` column to main_categories table

**Location:** `database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->string('cat_link', 255)->nullable()->after('id_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->dropColumn('cat_link');
        });
    }
};
```

---

## 2. 2026_02_18_175744_add_fldr_str_to_main_categories_table.php

**Purpose:** Add `fldr_str` column to main_categories table for JSON file storage

**Location:** `database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFldrStrToMainCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->string('fldr_str', 50)->nullable()->after('id_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('crafty_video_mysql')->table('main_categories', function (Blueprint $table) {
            $table->dropColumn('fldr_str');
        });
    }
}
```

---

## 3. 2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php

**Purpose:** Convert `contents` and `faqs` columns from JSON to TEXT type

**Location:** `database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php`

**IMPORTANT:** This migration changes column types. Ensure database backup before running!

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyContentsFaqsColumnsInMainCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to modify columns and remove JSON constraint
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN contents TEXT NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN faqs TEXT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to JSON columns
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN contents JSON NULL');
        DB::connection('crafty_video_mysql')->statement('ALTER TABLE main_categories MODIFY COLUMN faqs JSON NULL');
    }
}
```

---

## 4. 2026_02_18_000000_add_noindex_to_video_templates.php

**Purpose:** Add `no_index` column to items table (video templates)

**Location:** `database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'crafty_video_mysql';

    public function up()
    {
        Schema::connection($this->connection)->table('items', function (Blueprint $table) {
            if (!Schema::connection($this->connection)->hasColumn('items', 'no_index')) {
                $table->tinyInteger('no_index')->default(1)->after('status')->comment('1 = noindex, 0 = index');
            }
        });
    }

    public function down()
    {
        Schema::connection($this->connection)->table('items', function (Blueprint $table) {
            if (Schema::connection($this->connection)->hasColumn('items', 'no_index')) {
                $table->dropColumn('no_index');
            }
        });
    }
};
```

---

## Migration Execution Order

**CRITICAL:** Run migrations in this exact order:

```bash
# Step 1: Add cat_link column
php artisan migrate --path=database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php

# Step 2: Add fldr_str column
php artisan migrate --path=database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php

# Step 3: Modify contents and faqs columns (BACKUP DATABASE FIRST!)
php artisan migrate --path=database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php

# Step 4: Add no_index column to items table
php artisan migrate --path=database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php
```

---

## Database Changes Summary

### main_categories table (crafty_video_mysql)
- **Added:** `cat_link` VARCHAR(255) NULL - Stores category link path
- **Added:** `fldr_str` VARCHAR(50) NULL - Stores folder string for JSON files
- **Modified:** `contents` JSON → TEXT - Now stores file path instead of JSON data
- **Modified:** `faqs` JSON → TEXT - Now stores file path instead of JSON data

### items table (crafty_video_mysql)
- **Added:** `no_index` TINYINT DEFAULT 1 - Controls search engine indexing (1=noindex, 0=index)

---

## Pre-Migration Checklist

- [ ] Database backup completed
- [ ] Confirmed database connection: `crafty_video_mysql`
- [ ] Verified `main_categories` table exists
- [ ] Verified `items` table exists
- [ ] Checked for existing data in `contents` and `faqs` columns
- [ ] Ensured storage folder `storage/app/public/ct/` exists with write permissions

---

## Post-Migration Verification

```sql
-- Verify main_categories columns
DESCRIBE crafty_video_db.main_categories;

-- Check cat_link column
SELECT id, category_name, id_name, cat_link FROM crafty_video_db.main_categories LIMIT 5;

-- Check fldr_str column
SELECT id, category_name, fldr_str FROM crafty_video_db.main_categories LIMIT 5;

-- Check contents and faqs column types
SHOW COLUMNS FROM crafty_video_db.main_categories LIKE 'contents';
SHOW COLUMNS FROM crafty_video_db.main_categories LIKE 'faqs';

-- Verify items no_index column
DESCRIBE crafty_video_db.items;
SELECT id, video_name, no_index FROM crafty_video_db.items LIMIT 5;
```

---

## Rollback Instructions

If you need to rollback these migrations:

```bash
# Rollback all 4 migrations
php artisan migrate:rollback --step=4

# Or rollback individually in reverse order
php artisan migrate:rollback --path=database/migrations/2026_02_18_000000_add_noindex_to_video_templates.php
php artisan migrate:rollback --path=database/migrations/2026_02_18_180436_modify_contents_faqs_columns_in_main_categories.php
php artisan migrate:rollback --path=database/migrations/2026_02_18_175744_add_fldr_str_to_main_categories_table.php
php artisan migrate:rollback --path=database/migrations/2026_02_17_000000_add_cat_link_to_video_categories.php
```

**WARNING:** Rolling back migration #3 will convert TEXT columns back to JSON. Ensure data compatibility!

---

**END OF MIGRATION FILES CONTENT**
