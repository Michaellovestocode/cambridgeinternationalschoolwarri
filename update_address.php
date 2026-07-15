<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$settings = \App\Models\SchoolSettings::first();
if ($settings) {
    echo "Current address: " . $settings->school_address . "\n";
    $settings->update(['school_address' => 'Delta, Nigeria']);
    echo "Updated address to: " . $settings->school_address . "\n";
} else {
    echo "No school settings found\n";
}
