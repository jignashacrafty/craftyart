# Rejection Reason Display - Fixed

## Issue
When viewing a rejected application, the rejection reason was not being displayed in the View modal.

## Root Cause
The application was rejected without a rejection reason being saved in the database. This could happen if:
1. The application was rejected before the rejection reason feature was added
2. The rejection reason field was empty when submitted
3. There was a validation issue that was bypassed

## Solution Applied

### 1. Updated View Modal Display
**File**: `resources/views/designer_system/applications.blade.php`

Changed from:
```blade
@if($application->status == 'rejected' && $application->rejection_reason)
    <div class="alert alert-danger mt-3">
        <strong><i class="fa fa-exclamation-circle"></i> Rejection Reason:</strong>
        <p class="mb-0 mt-2">{{ $application->rejection_reason }}</p>
    </div>
@endif
```

To:
```blade
@if($application->status == 'rejected')
    <div class="alert alert-danger mt-3">
        <strong><i class="fa fa-exclamation-circle"></i> Rejection Reason:</strong>
        <p class="mb-0 mt-2">
            @if($application->rejection_reason)
                {{ $application->rejection_reason }}
            @else
                <em class="text-muted">No rejection reason provided</em>
            @endif
        </p>
    </div>
@endif
```

### 2. Rejection Form Validation
The rejection form already has proper validation:
- **Required field**: `required` attribute on textarea
- **Minimum length**: `minlength="10"` attribute
- **Server-side validation**: Controller validates `rejection_reason` as required

### 3. Controller Validation
**File**: `app/Http/Controllers/Admin/DesignerSystemController.php`

```php
public function rejectApplication(Request $request, $id)
{
    $request->validate([
        'rejection_reason' => 'required|string',
    ]);

    $application = DesignerApplication::findOrFail($id);

    if ($application->status !== 'pending') {
        return redirect()->back()->with('error', 'Application already processed');
    }

    $application->update([
        'status' => 'rejected',
        'rejection_reason' => $request->rejection_reason,
        'reviewed_by' => auth()->id(),
        'reviewed_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Application rejected');
}
```

## Current Behavior

### For New Rejections
1. Admin clicks "Reject" button
2. Modal opens with rejection reason textarea (required, min 10 characters)
3. Admin enters rejection reason
4. Clicks "Reject" button
5. Reason is saved to database
6. When viewing the application, rejection reason is displayed

### For Existing Rejected Applications
- If rejection reason exists: Shows the reason
- If rejection reason is empty: Shows "No rejection reason provided" in italics

## Testing

### Test New Rejection
1. Go to pending applications
2. Click "Reject" on any application
3. Try to submit without entering a reason - should show validation error
4. Enter a reason (at least 10 characters)
5. Submit the form
6. Click "View" on the rejected application
7. Verify rejection reason is displayed

### Test Existing Rejected Application
1. Go to rejected applications
2. Click "View" on the application from the screenshot
3. Should now show "No rejection reason provided" instead of nothing

## Database Schema

The `designer_applications` table has the `rejection_reason` column:
```php
$table->text('rejection_reason')->nullable();
```

## Fix for Existing Data

If you want to add rejection reasons to existing rejected applications, you can either:

### Option 1: Re-reject the application
1. Change status back to 'pending' in database
2. Reject it again through the UI with a proper reason

### Option 2: Update database directly
```sql
UPDATE designer_applications 
SET rejection_reason = 'Application rejected - reason not recorded' 
WHERE status = 'rejected' AND rejection_reason IS NULL;
```

## Files Modified
1. `resources/views/designer_system/applications.blade.php` - Updated rejection reason display logic
