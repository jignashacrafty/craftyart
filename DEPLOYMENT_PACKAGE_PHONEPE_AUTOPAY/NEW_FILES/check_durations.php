<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Plan Durations ===\n\n";

$durations = DB::connection('crafty_pricing_mysql')->table('plan_durations')->get();

foreach ($durations as $duration) {
    echo "ID: {$duration->id}\n";
    echo "Name: {$duration->name}\n";
    echo "Duration in Months: {$duration->duration_in_months}\n";
    echo "\n";
}
