<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LichHoc;

$l = LichHoc::first();
if ($l) {
    echo "LichHoc ID: " . $l->id . "\n";
    try {
        echo "Query for phongHocLives:\n";
        echo $l->phongHocLives()->toSql() . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No LichHoc found.\n";
}
