<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'published_count=' . App\Models\Announcement::published()->count() . PHP_EOL;
echo 'ticker_count=' . App\Models\Announcement::published()->where('show_in_ticker', true)->count() . PHP_EOL;
