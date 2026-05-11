<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'config_app_timezone=' . config('app.timezone') . PHP_EOL;
echo 'php_timezone=' . date_default_timezone_get() . PHP_EOL;
echo 'now=' . now()->format('Y-m-d H:i:s') . PHP_EOL;
