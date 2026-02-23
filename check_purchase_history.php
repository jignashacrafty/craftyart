<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking purchase_history table in databases...\n\n";

// Check crafty_db (mysql connection)
try {
  $count = DB::connection('mysql')->table('purchase_history')->count();
  echo "✅ crafty_db (mysql): {$count} records found\n";

  // Get sample record
  $sample = DB::connection('mysql')->table('purchase_history')->first();
  if ($sample) {
    echo "   Sample ID: {$sample->id}\n";
    echo "   Columns: " . implode(', ', array_keys((array) $sample)) . "\n";
  }
} catch (\Exception $e) {
  echo "❌ crafty_db (mysql) error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check if table exists in crafty_revenue
try {
  $count = DB::connection('crafty_revenue_mysql')->table('purchase_history')->count();
  echo "⚠️  crafty_revenue_mysql: {$count} records found (DUPLICATE TABLE!)\n";
} catch (\Exception $e) {
  echo "✅ crafty_revenue_mysql: Table does not exist (correct)\n";
}

echo "\n";

// Check PurchaseHistory model
try {
  $modelCount = \App\Models\PurchaseHistory::count();
  echo "✅ PurchaseHistory Model: {$modelCount} records\n";
  echo "   Connection: " . (new \App\Models\PurchaseHistory())->getConnectionName() . "\n";
} catch (\Exception $e) {
  echo "❌ PurchaseHistory Model error: " . $e->getMessage() . "\n";
}

echo "\n";

// Check business_support_purchase_history in crafty_revenue
try {
  $count = DB::connection('crafty_revenue_mysql')->table('business_support_purchase_history')->count();
  echo "✅ business_support_purchase_history in crafty_revenue_mysql: {$count} records\n";
} catch (\Exception $e) {
  echo "❌ business_support_purchase_history error: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
