# Designer System - Complete Guide

## Overview
Designer Onboarding & Portfolio Management System - એક complete platform જે designers ને onboard કરે છે, તેમના designs manage કરે છે, અને earnings track કરે છે.

## System Architecture

### User Types
1. **Admin** (`user_type = 1`) - Full system access
2. **Designer Head** (`user_type = 2`) - Design approval authority
3. **SEO Head** (`user_type = 3`) - SEO approval authority
4. **Designer** (`user_type = 4`) - Design submission
5. **App Users/Creators** (`creator = 1` in user_data) - Can become designers

### Database Tables
- `designer_applications` - Application submissions
- `designer_profiles` - Approved designer profiles
- `designer_wallets` - Designer earnings & balance
- `designer_transactions` - Transaction history
- `design_submissions` - Submitted designs
- `design_seo_details` - SEO information for designs
- `designer_withdrawals` - Withdrawal requests
- `design_sales` - Sales tracking
- `designer_types` - Designer specialization types
- `designer_categories` - Design categories
- `designer_goals` - Designer goals/objectives
- `wallet_settings` - System-wide wallet configuration

## Web Pages (Admin Panel)

### 1. Applications Management
**URL:** `/designer-system/applications`
**Purpose:** View and manage designer applications

**Features:**
- View all applications (pending/approved/rejected)
- Filter by status
- Approve applications
- Reject applications with reason
- Real-time updates via WebSocket
- Pagination support

**Status Flow:**
```
pending → approved (creates user + profile + wallet)
pending → rejected (with reason, can reapply)
```

### 2. Designers Management
**URL:** `/designer-system/designers`
**Purpose:** Manage approved designers

**Features:**
- View all approved designers
- See designer stats (designs, earnings)
- Activate/deactivate designers
- View designer profile details
- Commission rate management

### 3. Design Submissions
**URL:** `/designer-system/design-submissions`
**Purpose:** Review and approve design submissions

**Features:**
- View all design submissions
- Filter by status (pending/approved/rejected/live)
- Designer Head approval workflow
- Add notes/feedback
- Preview designs
- Approve/reject designs

**Status Flow:**
```
pending_designer_head → approved_by_designer_head → pending_seo
pending_designer_head → rejected_by_designer_head
```

### 4. SEO Submissions
**URL:** `/designer-system/seo-submissions`
**Purpose:** SEO team reviews approved designs

**Features:**
- View designs approved by Designer Head
- Add SEO details (meta title, description, keywords)
- SEO Head approval
- Make designs live
- Featured/trending flags

**Status Flow:**
```
pending_seo → approved_by_seo → live
pending_seo → rejected_by_seo
```

### 5. Withdrawals Management
**URL:** `/designer-system/withdrawals`
**Purpose:** Process designer withdrawal requests

**Features:**
- View all withdrawal requests
- Filter by status (pending/approved/rejected)
- Process withdrawals
- Add payment details
- Transaction tracking

**Status Flow:**
```
pending → approved (money transferred)
pending → rejected (with reason)
```

### 6. Wallet Settings
**URL:** `/designer-system/wallet-settings`
**Purpose:** Configure system-wide wallet settings

**Settings:**
- Minimum withdrawal amount
- Commission rates
- Payment methods
- Processing fees
- Auto-approval thresholds

### 7. Designer Types
**URL:** `/designer-system/types`
**Purpose:** Manage designer specialization types

**Examples:**
- Graphic Designer
- Video Editor
- Illustrator
- UI/UX Designer
- Motion Graphics

### 8. Designer Categories
**URL:** `/designer-system/categories`
**Purpose:** Manage design categories

**Examples:**
- Templates
- Videos
- Stickers
- Frames
- GIFs
- Vectors
- Backgrounds

### 9. Designer Goals
**URL:** `/designer-system/goals`
**Purpose:** Manage designer goals/objectives

**Examples:**
- Earn extra income
- Build portfolio
- Full-time career
- Learn new skills
- Freelance work

## API Endpoints

### Authentication APIs

#### 1. Admin Login
```
POST /api/admin/login
```
**Body:**
```json
{
  "email": "admin@gmail.com",
  "password": "123456"
}
```
**Response:**
```json
{
  "statusCode": 200,
  "success": true,
  "msg": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "name": "Admin",
    "email": "admin@gmail.com",
    "user_type": 1
  }
}
```

### Application APIs

#### 2. Submit Application (Public)
```
POST /api/designer/apply
```
**Body:**
```json
{
  "name": "John Designer",
  "email": "john@example.com",
  "phone": "9876543210",
  "city": "Ahmedabad",
  "state": "Gujarat",
  "country": "India",
  "experience": "3 years of graphic design",
  "experience_level": "mid-level",
  "skills": "Photoshop, Illustrator, Figma",
  "portfolio_links": ["https://behance.net/john"],
  "selected_types": [1, 2, 7],
  "selected_categories": [1, 2, 5],
  "selected_goals": [1, 4, 8]
}
```

#### 3. Check Status / Approve / Reject (Combined)
```
POST /api/designer/check-status
```

**Check Status (No Auth):**
```json
{
  "email": "john@example.com"
}
```

**Approve (Admin/Designer Head):**
```json
{
  "email": "john@example.com",
  "action": "approve"
}
```
Headers: `Authorization: Bearer {{admin_token}}`

**Reject (Admin/Designer Head):**
```json
{
  "email": "john@example.com",
  "action": "reject",
  "rejection_reason": "Portfolio quality needs improvement"
}
```
Headers: `Authorization: Bearer {{admin_token}}`

### Designer APIs (Auth Required)

#### 4. Get Profile
```
GET /api/designer/profile
```
Headers: `Authorization: Bearer {{token}}`

**Response Types:**
- Approved Designer: Full profile with wallet
- Creator: Basic profile with message to apply
- Regular User: 404 with message

#### 5. Submit Design
```
POST /api/designer/design/submit
```
Headers: `Authorization: Bearer {{token}}`

**Body (multipart/form-data):**
- `title` (required)
- `description`
- `category` (required): template/video/sticker/frame/gif/vector/bg
- `category_id`
- `design_file` (required): File upload
- `preview_images[]`: Multiple files
- `tags[]`: Array of tags
- `meta_title`: SEO title
- `meta_description`: SEO description
- `keywords[]`: SEO keywords

#### 6. Get Designs
```
GET /api/designer/designs?status=pending&per_page=20
```
Headers: `Authorization: Bearer {{token}}`

**Query Parameters:**
- `status`: pending_designer_head/approved_by_designer_head/rejected/live
- `per_page`: Number of results (default: 20)

#### 7. Get Design Details
```
GET /api/designer/design/{id}
```
Headers: `Authorization: Bearer {{token}}`

### Designer Head APIs

#### 8. Get Applications
```
GET /api/designer-head/applications?status=pending&per_page=20
```
Headers: `Authorization: Bearer {{admin_token}}`

#### 9. Approve Application
```
POST /api/designer-head/application/{id}/approve
```
Headers: `Authorization: Bearer {{admin_token}}`

#### 10. Reject Application
```
POST /api/designer-head/application/{id}/reject
```
Headers: `Authorization: Bearer {{admin_token}}`

**Body:**
```json
{
  "rejection_reason": "Portfolio does not meet quality standards"
}
```

#### 11. Get Design Submissions
```
GET /api/designer-head/designs?status=pending&per_page=20
```

#### 12. Approve Design
```
POST /api/designer-head/design/{id}/approve
```

**Body:**
```json
{
  "notes": "Great work! Approved for SEO review"
}
```

#### 13. Reject Design
```
POST /api/designer-head/design/{id}/reject
```

**Body:**
```json
{
  "notes": "Please improve the color scheme and resubmit"
}
```

### SEO Head APIs

#### 14. Get SEO Submissions
```
GET /api/seo-head/designs?status=pending&per_page=20
```

#### 15. Approve Design (Make Live)
```
POST /api/seo-head/design/{id}/approve
```

**Body:**
```json
{
  "notes": "SEO optimized and ready to go live",
  "is_featured": true,
  "is_trending": false
}
```

#### 16. Reject Design
```
POST /api/seo-head/design/{id}/reject
```

### Wallet APIs

#### 17. Get Wallet
```
GET /api/designer/wallet
```

**Response:**
```json
{
  "balance": 2500.00,
  "total_earned": 5000.00,
  "total_withdrawn": 2500.00,
  "pending_amount": 0.00,
  "can_withdraw": true,
  "withdrawal_threshold": 500.00
}
```

#### 18. Get Transactions
```
GET /api/designer/transactions?type=credit&per_page=20
```

#### 19. Request Withdrawal
```
POST /api/designer/withdrawal/request
```

**Body:**
```json
{
  "amount": 1000.00,
  "payment_method": "bank_transfer",
  "account_details": {
    "account_number": "1234567890",
    "ifsc_code": "SBIN0001234",
    "account_holder": "John Designer"
  }
}
```

#### 20. Get Withdrawals
```
GET /api/designer/withdrawals?status=pending
```

### Admin Designer APIs

#### 21. Get Withdrawals (Admin)
```
GET /api/admin/designer/withdrawals?status=pending
```

#### 22. Process Withdrawal
```
POST /api/admin/designer/withdrawal/{id}/process
```

**Body:**
```json
{
  "transaction_id": "TXN123456789",
  "notes": "Payment processed via bank transfer"
}
```

#### 23. Reject Withdrawal
```
POST /api/admin/designer/withdrawal/{id}/reject
```

**Body:**
```json
{
  "reason": "Insufficient balance or invalid account details"
}
```

### Enrollment APIs

#### 24. Check Enrollment
```
GET /api/designer/enrollment/check
```

#### 25. Submit Enrollment
```
POST /api/designer/enrollment/submit
```

#### 26. Choose Plan
```
POST /api/designer/enrollment/choose-plan
```

#### 27. Get Enrollment Status
```
GET /api/designer/enrollment/status
```

## Design Workflow

### Complete Flow
```
1. Designer submits design
   ↓
2. Status: pending_designer_head
   ↓
3. Designer Head reviews
   ↓
4a. Approved → Status: approved_by_designer_head → pending_seo
4b. Rejected → Status: rejected_by_designer_head (can resubmit)
   ↓
5. SEO Head reviews
   ↓
6a. Approved → Status: approved_by_seo → live (visible to users)
6b. Rejected → Status: rejected_by_seo (back to designer)
   ↓
7. Design is live and can generate sales
   ↓
8. Sales generate earnings for designer
   ↓
9. Designer can withdraw earnings
```

## Token Types

### Admin Token (from `/api/admin/login`)
```json
{
  "id": 1,
  "email": "admin@gmail.com",
  "user_type": 1,
  "name": "Admin"
}
```
**Use for:** Admin panel APIs, approve/reject actions

### App User Token (from mobile app login)
```json
{
  "uid": "user123",
  "email": "user@example.com",
  "device_id": "device123",
  "session_id": "session123"
}
```
**Use for:** Designer profile, design submission (if creator=1)

## Encryption

All APIs support encryption using `ResponseHandler`:

**Development (Plain JSON):**
```
?showDecoded=1
```

**Production (Encrypted):**
No parameter - returns encrypted response

**Encryption Details:**
- Algorithm: AES-256-CBC
- Passphrase: `E@7r1K7!6v#KZx^m`
- Library: CryptoJS compatible

## Testing with Postman

### Setup
1. Import `DESIGNER_SYSTEM_POSTMAN_COLLECTION.json`
2. Set `base_url` variable
3. Login to get token (auto-saved)
4. Use `?showDecoded=1` for testing

### Test Sequence
1. Admin Login → Get token
2. Submit Application (public)
3. Check Status
4. Approve Application (admin)
5. Designer Login → Get profile
6. Submit Design
7. Designer Head → Approve design
8. SEO Head → Make design live
9. Request Withdrawal
10. Admin → Process withdrawal

## Real-time Features

### WebSocket Events
- New application submitted
- Application approved/rejected
- Design submitted
- Design approved/rejected
- Withdrawal requested
- Withdrawal processed

**Broadcast Controller:**
`App\Http\Controllers\WebSocketBroadcastController`

## Commission & Earnings

### Default Settings
- Commission Rate: 30% (designer gets 70%)
- Minimum Withdrawal: ₹500
- Processing Time: 2-3 business days

### Calculation Example
```
Design Sale Price: ₹1000
Designer Commission (70%): ₹700
Platform Fee (30%): ₹300

Designer Earnings: ₹700 added to wallet
```

## Security Features

1. **JWT Authentication** - Secure token-based auth
2. **Role-based Access** - Different permissions for different roles
3. **Encryption** - All responses encrypted in production
4. **Validation** - Strict input validation
5. **File Upload Security** - File type and size restrictions
6. **SQL Injection Protection** - Laravel Eloquent ORM
7. **XSS Protection** - Input sanitization

## Error Handling

### Common Error Codes
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Files Structure

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   ├── DesignerSystemController.php
│   │   ├── DesignerSystemSettingsController.php
│   │   └── WalletSettingController.php
│   └── Api/
│       ├── AdminAuthController.php
│       ├── DesignerApplicationController.php
│       ├── DesignerController.php
│       ├── DesignerHeadController.php
│       ├── SeoHeadController.php
│       ├── DesignerWalletController.php
│       ├── AdminDesignerController.php
│       └── DesignerEnrollmentController.php
├── Models/
│   ├── DesignerApplication.php
│   ├── DesignerProfile.php
│   ├── DesignerWallet.php
│   ├── DesignerTransaction.php
│   ├── DesignSubmission.php
│   ├── DesignSeoDetail.php
│   ├── DesignerWithdrawal.php
│   └── DesignSale.php
└── Auth/
    └── JwtGuard.php

resources/views/designer_system/
├── applications.blade.php
├── designers.blade.php
├── design_submissions.blade.php
├── seo_submissions.blade.php
└── withdrawals.blade.php

database/migrations/
└── 2026_02_12_100000_create_designer_system_tables.php

database/seeders/
├── DesignerSystemTestSeeder.php
└── AdminDesignerProfileSeeder.php
```

## Quick Start

### For Developers
1. Run migrations: `php artisan migrate`
2. Seed test data: `php artisan db:seed --class=DesignerSystemTestSeeder`
3. Create admin designer profile: `php artisan db:seed --class=AdminDesignerProfileSeeder`
4. Import Postman collection
5. Test APIs with `?showDecoded=1`

### For Designers
1. Visit application page
2. Fill application form
3. Wait for approval
4. Login with credentials
5. Submit designs
6. Track earnings
7. Request withdrawals

### For Admins
1. Login to admin panel
2. Review applications
3. Approve/reject applications
4. Monitor design submissions
5. Process withdrawals
6. Configure settings

## Support & Documentation

- **Postman Collection:** `DESIGNER_SYSTEM_POSTMAN_COLLECTION.json`
- **API Documentation:** This file
- **Database Schema:** Check migrations
- **Test Data:** Use seeders

## Summary

આ એક complete designer management system છે જે:
- ✅ Designer applications manage કરે છે
- ✅ Multi-level approval workflow છે
- ✅ Earnings અને withdrawals track કરે છે
- ✅ Real-time updates આપે છે
- ✅ Secure authentication છે
- ✅ Encryption support છે
- ✅ Admin panel છે
- ✅ Complete API documentation છે

બધું ready છે અને working છે!
