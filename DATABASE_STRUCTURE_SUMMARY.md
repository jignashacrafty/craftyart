# Database Structure Summary - Crafty Art

## Database Connections Configuration

### ✅ Properly Configured Connections

All database connections now follow the consistent naming convention: `{name}_mysql`

#### 1. Main Database
- **Connection Name**: `mysql`
- **Database**: `crafty_db`
- **Location**: `config/database.php`

#### 2. Pricing Database
- **Connection Name**: `crafty_pricing_mysql` ✅
- **Database**: `crafty_pricing`
- **Env Variables**:
  - `CRAFTY_PRICING_DB_HOST`
  - `CRAFTY_PRICING_DB_PORT`
  - `CRAFTY_PRICING_DB_DATABASE`
  - `CRAFTY_PRICING_DB_USERNAME`
  - `CRAFTY_PRICING_DB_PASSWORD`

#### 3. Revenue Database
- **Connection Name**: `crafty_revenue_mysql` ✅ (UPDATED)
- **Database**: `crafty_revenue`
- **Env Variables**:
  - `CRAFTY_REVENUE_DB_HOST`
  - `CRAFTY_REVENUE_DB_PORT`
  - `CRAFTY_REVENUE_DB_DATABASE`
  - `CRAFTY_REVENUE_DB_USERNAME`
  - `CRAFTY_REVENUE_DB_PASSWORD`

#### 4. Other Databases
- `crafty_automation_mysql` ✅
- `crafty_ai_mysql` ✅
- `crafty_video_mysql` ✅
- `crafty_caricature_mysql` ✅
- `custom_order_mysql` ✅
- `brand_kit_mysql` ✅
- `special_page_mysql` ✅

---

## Folder Structure

### Models Organization

```
app/Models/
├── Pricing/                          # Pricing-related models
│   ├── BonusPackage.php             (uses: crafty_pricing_mysql)
│   ├── OfferPackage.php             (uses: crafty_pricing_mysql)
│   ├── PaymentConfiguration.php     (uses: crafty_pricing_mysql)
│   ├── Plan.php                     (uses: crafty_pricing_mysql)
│   ├── PlanCategoryFeature.php      (uses: crafty_pricing_mysql)
│   ├── PlanDuration.php             (uses: crafty_pricing_mysql)
│   ├── PlanFeature.php              (uses: crafty_pricing_mysql)
│   ├── PlanUserDiscount.php         (uses: crafty_pricing_mysql)
│   └── SubPlan.php                  (uses: crafty_pricing_mysql)
│
├── Revenue/                          # Revenue-related models ✅
│   ├── Sale.php                     (uses: crafty_revenue_mysql)
│   └── BusinessSupportPurchaseHistory.php (uses: crafty_revenue_mysql)
│
├── Automation/                       # Automation-related models
│   ├── AutomationSendDetail.php     (uses: crafty_automation_mysql)
│   ├── AutomationSendLog.php        (uses: crafty_automation_mysql)
│   ├── CampaignFailedDetail.php     (uses: crafty_automation_mysql)
│   ├── CampaignSendLog.php          (uses: crafty_automation_mysql)
│   ├── Config.php                   (uses: crafty_automation_mysql)
│   ├── EmailTemplate.php            (uses: crafty_automation_mysql)
│   ├── MailSendDetail.php           (uses: crafty_automation_mysql)
│   ├── MailSendLog.php              (uses: crafty_automation_mysql)
│   └── WhatsappTemplate.php         (uses: crafty_automation_mysql)
│
├── Video/                            # Video-related models
│   ├── VideoCat.php                 (uses: crafty_video_mysql)
│   ├── VideoCmd.php                 (uses: crafty_video_mysql)
│   ├── VideoPurchaseHistory.php     (uses: crafty_video_mysql)
│   ├── VideoTemplate.php            (uses: crafty_video_mysql)
│   └── VideoType.php                (uses: crafty_video_mysql)
│
├── Caricature/                       # Caricature-related models
│   ├── Attire.php                   (uses: crafty_caricature_mysql)
│   ├── CaricatureCategory.php       (uses: crafty_caricature_mysql)
│   ├── CaricatureCategory2.php      (uses: crafty_caricature_mysql)
│   ├── CaricaturePurchaseHistory.php (uses: crafty_caricature_mysql)
│   └── CreatedCaricature.php        (uses: crafty_caricature_mysql)
│
├── CustomOrder/                      # Custom Order models
│   ├── OrderTable.php               (uses: custom_order_mysql)
│   ├── PricingTable.php             (uses: custom_order_mysql)
│   └── SizeTable.php                (uses: custom_order_mysql)
│
└── [Other root models]               # Main database models (uses: mysql)
```

---

## Changes Made

### 1. Database Configuration (`config/database.php`)
- ✅ Renamed connection from `crafty_revenue` to `crafty_revenue_mysql`
- ✅ Maintains consistency with other database connections

### 2. Models Updated
- ✅ `app/Models/Revenue/Sale.php` - Already using `crafty_revenue_mysql`
- ✅ `app/Models/Revenue/BusinessSupportPurchaseHistory.php` - Already using `crafty_revenue_mysql`
- ✅ `app/Models/Sale.php` - Updated to use `crafty_revenue_mysql`
- ✅ `app/Models/BusinessSupportPurchaseHistory.php` - Updated to use `crafty_revenue_mysql`

### 3. Migrations Updated
All migrations now use `crafty_revenue_mysql`:
- ✅ `2026_01_30_000000_create_sales_table.php`
- ✅ `2026_02_02_000000_add_phonepe_order_id_to_sales_table.php`
- ✅ `2026_02_02_000003_add_usage_type_to_sales_table.php`
- ✅ `2026_02_05_102756_add_caricature_to_sales_table.php`
- ✅ `2026_02_12_000000_make_sales_person_id_nullable.php`
- ✅ `2026_02_21_120000_add_description_to_business_support_purchase_history.php`

### 4. Seeders Updated
- ✅ `database/seeders/BusinessSupportPurchaseHistorySeeder.php`

---

## Controllers Using Revenue Models

The following controllers are already properly using the Revenue namespace:

1. `app/Http/Controllers/OrderUserController.php`
   - Uses: `App\Models\Revenue\Sale`

2. `app/Http/Controllers/BusinessSupportController.php`
   - Uses: `App\Models\Revenue\BusinessSupportPurchaseHistory`

3. `app/Http/Controllers/Api/OrderUserApiController.php`
   - Uses: `App\Models\Revenue\Sale`

4. `app/Http/Controllers/Api/SimplePaymentController.php`
   - Uses: `App\Models\Revenue\Sale`

---

## Environment Variables (.env)

All required environment variables are properly configured:

```env
# Revenue Database
CRAFTY_REVENUE_DB_CONNECTION=mysql
CRAFTY_REVENUE_DB_HOST=127.0.0.1
CRAFTY_REVENUE_DB_PORT=3306
CRAFTY_REVENUE_DB_DATABASE=crafty_revenue
CRAFTY_REVENUE_DB_USERNAME=root
CRAFTY_REVENUE_DB_PASSWORD=

# Pricing Database
CRAFTY_PRICING_DB_CONNECTION=mysql
CRAFTY_PRICING_DB_HOST=127.0.0.1
CRAFTY_PRICING_DB_PORT=3306
CRAFTY_PRICING_DB_DATABASE=crafty_pricing
CRAFTY_PRICING_DB_USERNAME=root
CRAFTY_PRICING_DB_PASSWORD=
```

---

## Summary

✅ **All database connections now follow the consistent naming pattern**: `{name}_mysql`

✅ **Revenue database structure matches Pricing database structure**:
- Connection name: `crafty_revenue_mysql`
- Models folder: `app/Models/Revenue/`
- Proper namespace: `App\Models\Revenue`

✅ **All models, migrations, and seeders updated** to use the new connection name

✅ **Controllers already using the correct Revenue namespace**

✅ **Environment variables properly configured**

---

## Next Steps (If Needed)

1. Run migrations to ensure database structure is up to date:
   ```bash
   php artisan migrate
   ```

2. Clear configuration cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. Test database connections:
   ```bash
   php artisan tinker
   # Then test:
   DB::connection('crafty_revenue_mysql')->getPdo();
   DB::connection('crafty_pricing_mysql')->getPdo();
   ```
