<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
foreach (App\Models\Announcement::orderByDesc('id')->take(10)->get(['id','title','is_pinned','sort_order','created_at']) as $a) {
    echo $a->id . ' | ' . $a->title . ' | pinned=' . (int)$a->is_pinned . ' | sort=' . $a->sort_order . ' | created=' . $a->created_at . PHP_EOL;
}
echo '---HOMEPAGE ORDER---' . PHP_EOL;
foreach (App\Models\Announcement::published()->homepageOrder()->take(10)->get(['id','title','is_pinned','sort_order']) as $a) {
    echo $a->id . ' | ' . $a->title . ' | pinned=' . (int)$a->is_pinned . ' | sort=' . $a->sort_order . PHP_EOL;
}
