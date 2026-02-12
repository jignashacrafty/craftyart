<?php
/**
 * PhonePe AutoPay - Production Cleanup Script
 * 
 * This script helps prepare your PhonePe AutoPay integration for production
 * by removing test code and cleaning up test data.
 * 
 * USAGE: php cleanup_for_production.php [--dry-run]
 * 
 * --dry-run: Show what would be done without actually doing it
 */

$dryRun = in_array('--dry-run', $argv);

if ($dryRun) {
    echo "🔍 DRY RUN MODE - No changes will be made\n\n";
} else {
    echo "⚠️  PRODUCTION CLEANUP MODE - Changes will be made!\n";
    echo "Press Ctrl+C to cancel, or Enter to continue...\n";
    fgets(STDIN);
    echo "\n";
}

// Files to remove
$filesToRemove = [
    'app/Http/Controllers/PhonePeSimplePaymentTestController.php',
    'app/Http/Controllers/PhonePeAutoPayTestController.php',
    'resources/views/phonepe_simple_payment_test.blade.php',
    'resources/views/phonepe_autopay_test/index.blade.php',
    'resources/views/phonepe_autopay_test/callback.blade.php',
    'app/Models/PhonePeAutoPayTestHistory.php',
];

// Documentation files to keep (optional - you can remove these too)
$docsToKeep = [
    'PHONEPE_PRODUCTION_READINESS_CHECKLIST.md',
    'PHONEPE_PRODUCTION_SETUP.md',
    'PHONEPE_COMPLETE_INTEGRATION_SUMMARY.md',
];

// Test documentation to remove (optional)
$testDocsToRemove = [
    'PHONEPE_QUICK_TEST_NOW.md',
    'PHONEPE_AUTOPAY_SIMULATION_GUIDE.md',
    'PHONEPE_SIMULATION_READY.md',
    'SYNTAX_ERROR_FIX_SUMMARY.md',
    'PHONEPE_INVALID_ORDER_FIX.md',
    'PHONEPE_COMPLETE_TESTING_MANUAL.md',
    'PHONEPE_AUTOPAY_TESTING_GUIDE.md',
    'PHONEPE_AUTOPAY_QUICK_TEST.md',
    'PHONEPE_AUTOPAY_COMPLETE_TESTING_GUIDE.md',
    'PHONEPE_AUTOPAY_TEST_SUMMARY.md',
    'PHONEPE_AUTOPAY_TESTING_WITH_YOUR_UPI.md',
    'PHONEPE_AUTOPAY_MOCK_MODE_GUIDE.md',
    'PHONEPE_AUTOPAY_UI_TEST_GUIDE.md',
    'TEST_PHONEPE_NOW.md',
    'TESTING_PAYMENT_FLOW.md',
    'SEND_REAL_PAYMENT_REQUEST_TO_UPI.md',
    'HOW_TO_VERIFY_YOUR_UPI.md',
];

echo "📋 CLEANUP PLAN\n";
echo "================\n\n";

// 1. Remove test files
echo "1️⃣  Files to Remove:\n";
foreach ($filesToRemove as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✓ Found' : '✗ Not found';
    echo "   $status: $file\n";
}
echo "\n";

// 2. Test docs to remove
echo "2️⃣  Test Documentation to Remove (optional):\n";
foreach ($testDocsToRemove as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✓ Found' : '✗ Not found';
    echo "   $status: $file\n";
}
echo "\n";

// 3. Routes to remove
echo "3️⃣  Routes to Remove from routes/web.php:\n";
$routesToRemove = [
    "Route::get('/phonepe/simple-payment-test'",
    "Route::get('/phonepe/autopay/test'",
    "Route::post('/phonepe/autopay/test/create'",
    "Route::post('/phonepe/autopay/test/predebit'",
    "Route::post('/phonepe/autopay/test/debit'",
    "Route::post('/phonepe/autopay/test/delete'",
    "Route::get('/phonepe/autopay/test/list'",
    "Route::get('/phonepe/autopay/test/status'",
    "Route::post('/phonepe/simulate-autodebit'",
    "Route::post('/phonepe/trigger-autodebit'",
    "Route::post('/phonepe/send-predebit'",
    "Route::get('/phonepe/get-history'",
];
foreach ($routesToRemove as $route) {
    echo "   - $route\n";
}
echo "\n";

// 4. Database cleanup
echo "4️⃣  Database Cleanup (SQL to run manually):\n";
echo "   -- Remove simulated notifications\n";
echo "   DELETE FROM phonepe_notifications WHERE event_type LIKE '%SIMULATED%';\n";
echo "   \n";
echo "   -- Remove test transactions (optional)\n";
echo "   DELETE FROM phonepe_transactions WHERE notes LIKE '%test%';\n";
echo "   \n";
echo "   -- Drop test history table (optional)\n";
echo "   DROP TABLE IF EXISTS phonepe_autopay_test_history;\n";
echo "\n";

if ($dryRun) {
    echo "🔍 DRY RUN COMPLETE - No changes were made\n";
    echo "Run without --dry-run to actually perform cleanup\n";
    exit(0);
}

// Perform actual cleanup
echo "🚀 STARTING CLEANUP...\n\n";

$removed = 0;
$failed = 0;

// Remove files
echo "Removing files...\n";
foreach ($filesToRemove as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "   ✓ Removed: $file\n";
            $removed++;
        } else {
            echo "   ✗ Failed to remove: $file\n";
            $failed++;
        }
    }
}

// Remove test docs (optional - ask user)
echo "\nDo you want to remove test documentation files? (y/n): ";
$removeTestDocs = trim(fgets(STDIN));
if (strtolower($removeTestDocs) === 'y') {
    echo "Removing test documentation...\n";
    foreach ($testDocsToRemove as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                echo "   ✓ Removed: $file\n";
                $removed++;
            } else {
                echo "   ✗ Failed to remove: $file\n";
                $failed++;
            }
        }
    }
}

// Remove test views directory
if (is_dir('resources/views/phonepe_autopay_test')) {
    echo "\nRemoving test views directory...\n";
    $files = glob('resources/views/phonepe_autopay_test/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (rmdir('resources/views/phonepe_autopay_test')) {
        echo "   ✓ Removed: resources/views/phonepe_autopay_test/\n";
        $removed++;
    }
}

echo "\n";
echo "✅ CLEANUP COMPLETE\n";
echo "===================\n";
echo "Files removed: $removed\n";
echo "Failed: $failed\n";
echo "\n";

echo "⚠️  MANUAL STEPS REQUIRED:\n";
echo "1. Edit routes/web.php and remove test routes (see list above)\n";
echo "2. Edit resources/views/layouts/header.blade.php and remove test menu items\n";
echo "3. Run database cleanup SQL (see above)\n";
echo "4. Clear caches: php artisan cache:clear && php artisan route:clear\n";
echo "5. Test your production features\n";
echo "\n";

echo "📚 KEEP THESE DOCS FOR REFERENCE:\n";
foreach ($docsToKeep as $doc) {
    if (file_exists($doc)) {
        echo "   - $doc\n";
    }
}
echo "\n";

echo "🎉 Your PhonePe AutoPay integration is now ready for production!\n";
