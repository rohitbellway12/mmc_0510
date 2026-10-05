<?php
define('LARAVEL_START', microtime(true));
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\ServiceManagement\Entities\Service;

$service_id = '014f26c6-727c-4ebc-b6ed-725109d0ea3f';
$s = Service::find($service_id);
if ($s) {
    echo $s->name . " | Category: " . $s->category_id . " | Sub: " . $s->sub_category_id . "\n";
    $v = \Modules\ServiceManagement\Entities\Variation::where('service_id', $service_id)->first();
    if ($v) {
        echo "Variant Key: " . $v->variant_key . "\n";
    }
} else {
    echo "Service not found\n";
}
