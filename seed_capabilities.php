<?php
use Modules\ProviderManagement\Entities\SubscribedService;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Provider ID from valid curl
$providerId = 'cffcce91-5498-4b73-b571-8e6e69bbd89d';
// Category ID for Tyre Fitter
$categoryId = '5d98d5c9-509e-4ab7-859d-806174384e27';

$s = SubscribedService::where('provider_id', $providerId)
    // ->where('category_id', $categoryId) // sometimes subscribed service link is via sub_category_id only. Let's check provider_id primarily.
    ->first();

if($s) {
    // Save as array of strings matching the options
    $s->service_capabilities = ['Mobile Tyre Service', 'Recovery Truck'];
    $s->save();
    echo "Capabilities updated for Provider: " . $s->provider->company_name . "\n";
    print_r($s->toArray());
} else {
    echo "SubscribedService not found for provider $providerId.\n";
}
