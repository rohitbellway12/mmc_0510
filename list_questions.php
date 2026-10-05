<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\ProviderManagement\Entities\ProviderQuestion;

$questions = ProviderQuestion::where('category_id', '5d98d5c9-509e-4ab7-859d-806174384e27')->get();
foreach ($questions as $q) {
    echo $q->question_text . " | " . $q->id . "\n";
}
