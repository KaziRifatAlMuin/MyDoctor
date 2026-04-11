<?php
// Bootstrap Laravel and drop all tables (use with care)
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = \DB::select('SHOW TABLES');
\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
foreach ($rows as $r) {
    $arr = (array)$r;
    $name = array_values($arr)[0];
    try {
        \DB::statement("DROP TABLE IF EXISTS `" . $name . "`");
        echo "Dropped: $name\n";
    } catch (Exception $e) {
        echo "Failed to drop $name: " . $e->getMessage() . "\n";
    }
}
\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

echo "Done\n";
