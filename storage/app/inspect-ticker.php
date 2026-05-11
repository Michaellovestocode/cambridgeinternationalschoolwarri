<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo 'NOW=' . now()->format('Y-m-d H:i:s') . PHP_EOL;
foreach (App\Models\Announcement::orderByDesc('id')->take(10)->get(['id','title','is_published','is_pinned','show_in_ticker','sort_order','published_at']) as $a) {
    echo $a->id . ' | ' . $a->title . ' | pub=' . (int)$a->is_published . ' | pinned=' . (int)$a->is_pinned . ' | ticker=' . (int)$a->show_in_ticker . ' | sort=' . $a->sort_order . ' | published_at=' . ($a->published_at ? $a->published_at->format('Y-m-d H:i:s') : 'null') . PHP_EOL;
}
echo '---TICKER_QUERY---' . PHP_EOL;
foreach (App\Models\Announcement::published()->where('show_in_ticker', true)->homepageOrder()->take(10)->get(['id','title','ticker_text']) as $a) {
    echo $a->id . ' | ' . $a->title . ' | ticker_text=' . $a->ticker_text . PHP_EOL;
}
