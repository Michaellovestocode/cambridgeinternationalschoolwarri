<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'NOW=' . now()->format('Y-m-d H:i:s') . PHP_EOL;
foreach (App\Models\Announcement::orderByDesc('id')->take(10)->get() as $a) {
    echo $a->id . ' | ' . $a->title . ' | pub=' . (int) $a->is_published . ' | ticker=' . (int) $a->show_in_ticker . ' | published_at=' . ($a->published_at ? $a->published_at->format('Y-m-d H:i:s') : 'null') . ' | expires_at=' . ($a->expires_at ? $a->expires_at->format('Y-m-d H:i:s') : 'null') . PHP_EOL;
}
echo '---PUBLISHED_QUERY---' . PHP_EOL;
foreach (App\Models\Announcement::published()->homepageOrder()->take(10)->get() as $a) {
    echo $a->id . ' | ' . $a->title . PHP_EOL;
}
echo '---TICKER_QUERY---' . PHP_EOL;
foreach (App\Models\Announcement::published()->where('show_in_ticker', true)->homepageOrder()->take(10)->get() as $a) {
    echo $a->id . ' | ' . $a->title . PHP_EOL;
}
