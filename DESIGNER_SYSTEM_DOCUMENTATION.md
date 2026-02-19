# Designer Onboarding & Portfolio Management System

## 📋 Overview

Complete backend system for designer onboarding, design submission, approval workflow, and payment management.

## 🎯 Features

### 1. Designer Onboarding
- Public application form
- Application review by Designer Head
- Automatic user account creation on approval
- Role assignment (Designer Employee)

### 2. Design Submission & Approval
- Designers can submit designs with files and previews
- Two-level approval process:
  - Designer Head approval
  - SEO Head approval (with SEO details)
- Design goes live after SEO approval

### 3. Payment & Wallet System
- Commission-based earnings
- Wallet management
- Transaction history
- Withdrawal requests
- Admin withdrawal processing

### 4. Profile & Portfolio
- Designer profile with stats
- Portfolio of submitted designs
- Design performance tracking

## 🗄️ Database Tables

### 1. `designer_applications`
Stores designer application data from the public form.

**Fields:**
- `id`, `name`, `email`, `phone`, `address`, `city`, `state`, `country`
- `experience`, `skills`, `portfolio_links` (JSON), `uploaded_samples` (JSON)
- `status` (pending/approved/rejected)
- `rejection_reason`, `reviewed_by`, `reviewed_at`

### 2. `designer_profiles`
Designer profile after application approval.

**Fields:**
- `id`, `user_id`, `application_id`, `display_name`, `bio`, `profile_image`
- `specializations` (JSON), `commission_rate` (default 30%)
- `is_active`, `total_designs`, `approved_designs`, `live_designs`, `total_earnings`

### 3. `designer_wallets`
Wallet for each designer.

**Fields:**
- `id`, `designer_id`, `balance`, `total_earned`, `total_withdrawn`
- `pending_amount`, `withdrawal_threshold` (default ₹500)

### 4. `designer_transactions`
All wallet transactions (credit/debit).

**Fields:**
- `id`, `designer_id`, `type` (credit/debit), `amount`
- `balance_before`, `balance_after`, `transaction_type`, `description`
- `reference_id`, `reference_type`

### 5. `design_submissions`
Designs submitted by designers.

**Fields:**
- `id`, `designer_id`, `title`, `description`, `category`, `category_id`
- `design_file_path`, `preview_images` (JSON), `tags` (JSON)
- `status` (pending_designer_head/approved_by_designer_head/rejected_by_designer_head/pending_seo/approved_by_seo/rejected_by_seo/live)
- `designer_head_notes`, `seo_head_notes`
- `designer_head_reviewed_by`, `designer_head_reviewed_at`
- `seo_head_reviewed_by`, `seo_head_reviewed_at`
- `published_at`, `total_sales`, `total_revenue`

### 6. `design_seo_details`
SEO details for each design.

**Fields:**
- `id`, `design_submission_id`, `meta_title`, `meta_description`, `slug`
- `keywords` (JSON), `og_image`, `is_featured`, `is_trending`, `priority`

### 7. `designer_withdrawals`
Withdrawal requests from designers.

**Fields:**
- `id`, `designer_id`, `amount`, `status` (pending/processing/completed/rejected)
- `bank_name`, `account_number`, `ifsc_code`, `account_holder_name`, `upi_id`
- `payment_method` (bank_transfer/upi), `transaction_reference`
- `admin_notes`, `rejection_reason`, `processed_by`, `processed_at`

### 8. `design_sales`
Track sales of each design for commission calculation.

**Fields:**
- `id`, `design_submission_id`, `designer_id`, `purchase_history_id`, `user_id`
- `sale_amount`, `designer_commission`, `commission_rate`
- `payment_status` (pending/paid), `paid_at`

## 🔌 API Endpoints

### Public APIs (No Authentication)

#### 1. Submit Designer Application
```
POST /api/designer/apply
```

**Request Body:**
```json
{
  "name": "Designer Name",
  "email": "designer@example.com",
  "phone": "9876543210",
  "address": "123 Street",
  "city": "Ahmedabad",
  "state": "Gujarat",
  "country": "India",
  "experience": "3 years of graphic design",
  "skills": "Photoshop, Illustrator, Figma",
  "portfolio_links": [
    "https://behance.net/designer",
    "https://dribbble.com/designer"
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Application submitted successfully!",
  "data": {
    "application_id": 1,
    "status": "pending"
  }
}
```

#### 2. Check Application Status
```
POST /api/designer/check-status
```

**Request Body:**
```json
{
  "email": "designer@example.com"
}
```

### Designer Head APIs (Auth Required)

#### 3. Get Applications
```
GET /api/designer-head/applications?status=pending&per_page=20
```

**Headers:**
```
Authorization: Bearer YOUR_TOKEN
```

#### 4. Approve Application
```
POST /api/designer-head/application/{id}/approve
```

**Request Body:**
```json
{
  "commission_rate": 30
}
```

**Response:**
```json
{
  "success": true,
  "message": "Application approved successfully! Designer account created.",
  "data": {
    "user_id": 123,
    "profile_id": 45,
    "email": "designer@example.com",
    "default_password": "Designer@123"
  }
}
```

#### 5. Reject Application
```
POST /api/designer-head/application/{id}/reject
```

**Request Body:**
```json
{
  "rejection_reason": "Portfolio does not meet quality standards"
}
```

#### 6. Get Design Submissions
```
GET /api/designer-head/design-submissions?status=pending_designer_head
```

#### 7. Approve Design
```
POST /api/designer-head/design/{id}/approve
```

**Request Body:**
```json
{
  "notes": "Great design! Approved for SEO review."
}
```

#### 8. Reject Design
```
POST /api/designer-head/design/{id}/reject
```

**Request Body:**
```json
{
  "notes": "Design quality needs improvement"
}
```

### Designer APIs (Auth Required)

#### 9. Get Profile
```
GET /api/designer/profile
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "display_name": "Designer Name",
    "bio": null,
    "profile_image": null,
    "specializations": [],
    "commission_rate": 30.00,
    "is_active": true,
    "stats": {
      "total_designs": 10,
      "approved_designs": 8,
      "live_designs": 5,
      "total_earnings": 5000.00
    },
    "wallet": {
      "balance": 2500.00,
      "total_earned": 5000.00,
      "total_withdrawn": 2500.00,
      "can_withdraw": true,
      "withdrawal_threshold": 500.00
    }
  }
}
```

#### 10. Submit Design
```
POST /api/designer/design/submit
```

**Content-Type:** `multipart/form-data`

**Form Data:**
- `title`: Design title
- `description`: Design description
- `category`: template/video/sticker/frame/gif/vector/bg
- `category_id`: Category ID (optional)
- `design_file`: Design file (required)
- `preview_images[]`: Preview images (optional)
- `tags[]`: Tags array
- `meta_title`: SEO title (optional)
- `meta_description`: SEO description (optional)
- `keywords[]`: Keywords array (optional)

#### 11. Get My Designs
```
GET /api/designer/designs?status=pending_designer_head&per_page=20
```

#### 12. Get Design Details
```
GET /api/designer/design/{id}
```

#### 13. Get Wallet
```
GET /api/designer/wallet
```

#### 14. Get Transactions
```
GET /api/designer/transactions?type=credit&per_page=20
```

#### 15. Request Withdrawal
```
POST /api/designer/withdrawal/request
```

**Request Body (UPI):**
```json
{
  "amount": 1000,
  "payment_method": "upi",
  "upi_id": "designer@paytm"
}
```

**Request Body (Bank Transfer):**
```json
{
  "amount": 1000,
  "payment_method": "bank_transfer",
  "bank_name": "HDFC Bank",
  "account_number": "1234567890",
  "ifsc_code": "HDFC0001234",
  "account_holder_name": "Designer Name"
}
```

#### 16. Get Withdrawals
```
GET /api/designer/withdrawals?status=pending
```

### SEO Head APIs (Auth Required)

#### 17. Get Design Submissions
```
GET /api/seo-head/design-submissions?status=pending_seo
```

#### 18. Approve Design (Publish)
```
POST /api/seo-head/design/{id}/approve
```

**Content-Type:** `multipart/form-data`

**Form Data:**
- `meta_title`: SEO title (required)
- `meta_description`: SEO description (required)
- `slug`: URL slug (optional, auto-generated)
- `keywords[]`: Keywords array
- `og_image`: OG image file (optional)
- `is_featured`: true/false
- `is_trending`: true/false
- `priority`: 0-100
- `notes`: Admin notes

#### 19. Reject Design
```
POST /api/seo-head/design/{id}/reject
```

**Request Body:**
```json
{
  "notes": "SEO details need improvement"
}
```

#### 20. Update SEO Details
```
POST /api/seo-head/design/{id}/update-seo
```

**Request Body:**
```json
{
  "meta_title": "Updated Title",
  "meta_description": "Updated description",
  "keywords": ["keyword1", "keyword2"],
  "is_featured": true,
  "is_trending": false,
  "priority": 15
}
```

### Admin APIs (Auth Required)

#### 21. Get Withdrawal Requests
```
GET /api/admin/designer/withdrawals?status=pending
```

#### 22. Process Withdrawal
```
POST /api/admin/designer/withdrawal/{id}/process
```

**Request Body:**
```json
{
  "transaction_reference": "TXN123456789",
  "admin_notes": "Payment processed successfully"
}
```

#### 23. Reject Withdrawal
```
POST /api/admin/designer/withdrawal/{id}/reject
```

**Request Body:**
```json
{
  "rejection_reason": "Invalid bank details"
}
```

## 🔄 Complete Workflow

### 1. Designer Onboarding
```
User fills form → Application submitted (pending) →
Designer Head reviews → Approve/Reject →
If approved: User account created + Designer profile created + Wallet created →
Designer receives login credentials (email: their email, password: Designer@123)
```

### 2. Design Submission & Approval
```
Designer logs in → Submits design with files →
Design status: pending_designer_head →
Designer Head reviews → Approve/Reject →
If approved: Design status: pending_seo →
SEO Head reviews and adds SEO details → Approve/Reject →
If approved: Design status: live (published) →
Design appears on platform
```

### 3. Commission & Payment
```
User purchases design → Sale recorded in design_sales →
Commission calculated (sale_amount × commission_rate) →
Designer wallet credited →
Transaction recorded →
Designer can withdraw when balance >= threshold (₹500) →
Withdrawal request created → Admin processes →
Money transferred → Wallet updated
```

## 📦 Installation & Setup

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Test Public APIs
```bash
php test_designer_system.php
```

### 3. Import Postman Collection
Import `DESIGNER_SYSTEM_POSTMAN_COLLECTION.json` into Postman for testing all APIs.

### 4. Update Environment Variables
Add to `.env` if needed:
```
DESIGNER_COMMISSION_RATE=30
DESIGNER_WITHDRAWAL_THRESHOLD=500
```

## 🧪 Testing Guide

### Step 1: Submit Application (Public)
Use Postman or test script to submit designer application.

### Step 2: Login as Designer Head
Login with Designer Head credentials and get auth token.

### Step 3: Approve Application
Approve the application - this creates user account and designer profile.

### Step 4: Login as Designer
Login with designer credentials (email from application, password: Designer@123).

### Step 5: Submit Design
Submit a design with files using the designer auth token.

### Step 6: Approve Design (Designer Head)
Login as Designer Head and approve the design.

### Step 7: Approve Design (SEO Head)
Login as SEO Head, add SEO details, and approve - design goes live.

### Step 8: Test Wallet & Withdrawal
- Check wallet balance
- Request withdrawal
- Login as Admin and process withdrawal

## 🔐 User Roles

- **Designer Manager** (user_type: 7) - Designer Head
- **Designer Employee** (user_type: 8) - Designer
- **SEO Manager** (user_type: 4) - SEO Head
- **Admin** (user_type: 1) - Admin

## 📝 Notes

1. Default designer password is `Designer@123` - designers should change it after first login
2. Minimum withdrawal amount is ₹500 (configurable)
3. Default commission rate is 30% (configurable per designer)
4. All file uploads go to `storage/app/public/designs/`
5. Design categories: template, video, sticker, frame, gif, vector, bg

## 🚀 Production Checklist

- [ ] Run migrations
- [ ] Test all APIs
- [ ] Configure file storage
- [ ] Set up email notifications
- [ ] Configure withdrawal thresholds
- [ ] Set commission rates
- [ ] Test complete workflow
- [ ] Create admin users
- [ ] Create Designer Head users
- [ ] Create SEO Head users

## 📧 Support

For any issues or questions, contact the development team.
