# Rejection Reason Fading Out - Fixed

## Issue
The rejection reason was showing in the modal but then automatically fading out/disappearing after a few seconds.

## Root Cause
The `showNotification()` function in the WebSocket real-time update script was using `document.querySelectorAll('.alert')` to remove notification alerts after 5 seconds. This was selecting ALL alerts on the page, including the rejection reason alert inside the modal, causing it to disappear.

## Solution Applied

### Changed Alert Selector to be More Specific
**File**: `resources/views/designer_system/applications.blade.php`

**Before:**
```javascript
// Auto dismiss after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');  // ❌ Selects ALL alerts
    alerts.forEach(alert => {
        if (alert.textContent.includes(title)) {
            alert.remove();
        }
    });
}, 5000);
```

**After:**
```javascript
// Auto dismiss after 5 seconds - ONLY notification alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert.notification-alert');  // ✅ Only notification alerts
    alerts.forEach(alert => {
        if (alert.textContent.includes(title)) {
            alert.remove();
        }
    });
}, 5000);
```

### Added Specific Class to Notification Alerts
Added `notification-alert` class to the dynamically created notification alerts:

```javascript
const alertHtml = `
    <div class="alert ${alertClass} alert-dismissible fade show notification-alert" role="alert" ...>
        ...
    </div>
`;
```

## How It Works Now

1. **Notification Alerts** (from WebSocket events):
   - Have class: `alert notification-alert`
   - Auto-dismiss after 5 seconds
   - Appear in top-right corner

2. **Modal Alerts** (rejection reason):
   - Have class: `alert alert-danger`
   - Do NOT have `notification-alert` class
   - Stay visible permanently
   - Inside the modal

3. **Auto-dismiss Script**:
   - Only targets alerts with BOTH classes: `.alert.notification-alert`
   - Ignores modal alerts that don't have `notification-alert` class

## Testing

1. Open a rejected application
2. Click "View" button
3. Modal opens showing rejection reason in red alert box
4. Rejection reason should stay visible (not fade out)
5. Close modal and reopen - rejection reason still shows

## Files Modified
- `resources/views/designer_system/applications.blade.php` - Fixed alert auto-dismiss selector

## Result
✅ Rejection reason now stays visible in the modal
✅ Notification alerts still auto-dismiss after 5 seconds
✅ No interference between different types of alerts
