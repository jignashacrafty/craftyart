# Database Structure Summary

## Database Connections

All database connections follow the naming pattern: `crafty_[name]_mysql`

### Main Database
- **Connection Name**: `mysql`
- **Database**: `crafty_db`
- **Usage**: Main application database

### Pricing Database
- **Connection Name**: `crafty_pricing_mysql` ✅
- **Database**: `crafty_pricing`
- **Models Location**: `app/Models/Pricing/`
- **Models**:
  - BonusPackage
  - OfferPackage
  - PaymentConfiguration
  - Plan
  - PlanCategoryFeature
  - PlanDuration
  - PlanFeature
  - PlanUserDiscount
  - SubPlan

### Revenue Database
- **Connection Name**: `crafty_revenue_mysql` ✅
- **Database**: `crafty_revenue`
- **Models Location**: `app/Models/Revenue/`
- **Models**:
  - Sale
  - BusinessSupportPurchaseHistory

### Automation Database
- **Connection Name**: `crafty_automation_mysql` ✅
- **Database**: `crafty_automation`
- **Models Location**: `app/Models/Automation/`

### Video Database
- **Connection Name**: `crafty_video_mysql` ✅
- **Database**: `crafty_video_db`
- **Models Location**: `app/Models/Video/`

### Caricature Database
- **Connection Name**: `crafty_caricature_mysql` ✅
- **Database**: `marrycature`
- **Models Location**: `app/Models/Caricature/`

### Custom Order Database
- **Connection Name**: `custom_order_mysql` ✅
- **Database**: `custom_order_db`
- **Models Location**: `app/Models/CustomOrder/`

### Brand Kit Database
- **Connection Name**: `brand_kit_mysql` ✅
- **Database**: `brand_kit_db`

### Special Page Database
- **Connection Name**: `special_page_mysql` ✅
- **Database**: `crafty_pages`

### AI Database
- **Connection Name**: `crafty_ai_mysql` ✅
- **Database**: `crafty_ai`

## Folder Structure

```
app/Models/
├── Pricing/              # crafty_pricing_mysql models
│   ├── BonusPackage.php
│   ├── OfferPackage.php
│   ├── PaymentConfiguration.php
│   ├── Plan.php
│   ├── PlanCategoryFeature.php
│   ├── PlanDuration.php
│   ├── PlanFeature.php
│   ├── PlanUserDiscount.php
│   └── SubPlan.php
├── Revenue/              # crafty_revenue_mysql models (NEW)
│   ├── Sale.php
│   └── BusinessSupportPurchaseHistory.php
├── Automation/           # crafty_automation_mysql models
├── Video/                # crafty_video_mysql models
├── Caricature/           # crafty_caricature_mysql models
├── CustomOrder/          # custom_order_mysql models
└── [Other models]        # mysql (main database)
```

## Changes Made

1. ✅ Renamed database connection from `crafty_revenue` to `crafty_revenue_mysql` in `config/database.php`
2. ✅ Created new folder `app/Models/Revenue/`
3. ✅ Moved `Sale.php` to `app/Models/Revenue/Sale.php` with updated namespace
4. ✅ Moved `BusinessSupportPurchaseHistory.php` to `app/Models/Revenue/BusinessSupportPurchaseHistory.php` with updated namespace
5. ✅ Updated all controller imports:
   - `app/Http/Controllers/BusinessSupportController.php`
   - `app/Http/Controllers/Api/SimplePaymentController.php`
   - `app/Http/Controllers/OrderUserController.php`
   - `app/Http/Controllers/Api/OrderUserApiController.php`
   - `app/Http/Controllers/UserController.php`
6. ✅ All migrations already use `crafty_revenue_mysql`
7. ✅ Seeder already uses `crafty_revenue_mysql`
8. ✅ Deleted old model files from root Models folder

## Naming Convention

All database connections follow this pattern:
- Format: `crafty_[purpose]_mysql` or `[name]_mysql`
- Examples:
  - `crafty_pricing_mysql`
  - `crafty_revenue_mysql`
  - `crafty_automation_mysql`
  - `crafty_video_mysql`
  - `custom_order_mysql`
  - `brand_kit_mysql`

## Environment Variables

All database connections are configured via `.env` file with this pattern:
```
CRAFTY_[NAME]_DB_CONNECTION=mysql
CRAFTY_[NAME]_DB_HOST=127.0.0.1
CRAFTY_[NAME]_DB_PORT=3306
CRAFTY_[NAME]_DB_DATABASE=database_name
CRAFTY_[NAME]_DB_USERNAME=root
CRAFTY_[NAME]_DB_PASSWORD=
```
