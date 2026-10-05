<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\CategoryManagement\Entities\Category;

$categories = Category::select('id', 'name')->get();
foreach ($categories as $cat) {
    echo $cat->name . " | " . $cat->id . "\n";
}
