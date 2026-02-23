<?php

/**
 * Database Connection Verification Script
 * 
 * Run this script to verify all database connections are working properly:
 * php artisan tinker
 * include('.kiro/verify_database_connections.php');
 */

echo "=== Database Connection Verification ===\n\n";

$connections = [
  'mysql' => 'Main Database (crafty_db)',
  'crafty_pricing_mysql' => 'Pricing Database (crafty_pricing)',
  'crafty_revenue_mysql' => 'Revenue Database (crafty_revenue)',
  'crafty_automation_mysql' => 'Automation Database (crafty_automation)',
  'crafty_video_mysql' => 'Video Database (crafty_video_db)',
  'crafty_caricature_mysql' => 'Caricature Database (marrycature)',
  'custom_order_mysql' => 'Custom Order Database (custom_order_db)',
  'brand_kit_mysql' => 'Brand Kit Database (brand_kit_db)',
  'special_page_mysql' => 'Special Page Database (crafty_pages)',
  'crafty_ai_mysql' => 'AI Database (crafty_ai)',
];

foreach ($connections as $connection => $description) {
  try {
    DB::connection($connection)->getPdo();
    echo "✅ {$connection}: {$description} - Connected\n";
  } catch (\Exception $e) {
    echo "❌ {$connection}: {$description} - Failed\n";
    echo "   Error: " . $e->getMessage() . "\n";
  }
}

echo "\n=== Model Connection Verification ===\n\n";

// Test Pricing Models
try {
  $pricingTest = \App\Models\Pricing\SubPlan::getConnectionName();
  echo "✅ Pricing Models: Using connection '{$pricingTest}'\n";
} catch (\Exception $e) {
  echo "❌ Pricing Models: Error - " . $e->getMessage() . "\n";
}

// Test Revenue Models
try {
  $revenueTest = \App\Models\Revenue\Sale::getConnectionName();
  echo "✅ Revenue Models: Using connection '{$revenueTest}'\n";
} catch (\Exception $e) {
  echo "❌ Revenue Models: Error - " . $e->getMessage() . "\n";
}

echo "\n=== Verification Complete ===\n";
