<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookingIds = [100014, 100015];

foreach ($bookingIds as $rid) {
    $booking = DB::table('bookings')->where('readable_id', $rid)->first();
    if (!$booking) {
        echo "Booking $rid not found\n";
        continue;
    }

    echo "--- Booking $rid ---\n";
    echo "ID: " . $booking->id . "\n";
    echo "Status: " . $booking->booking_status . "\n";
    echo "Zone ID: " . $booking->zone_id . "\n";
    echo "Sub Cat ID: " . $booking->sub_category_id . "\n";
    echo "Amount: " . $booking->total_booking_amount . "\n";
    echo "Payment: " . $booking->payment_method . "\n";
    echo "Verified: " . $booking->is_verified . "\n";
    echo "Location: " . $booking->service_location . "\n";
    echo "Provider ID: " . ($booking->provider_id ?? 'NULL') . "\n";

    $providers = DB::table('providers')->get();
    foreach ($providers as $p) {
        echo "\nChecking Provider: " . $p->company_name . " (" . $p->id . ")\n";
        echo "  Zone Match: " . ($p->zone_id == $booking->zone_id ? "YES" : "NO") . " (P:" . $p->zone_id . " vs B:" . $booking->zone_id . ")\n";
        echo "  Available: " . ($p->is_active ? "YES" : "NO") . "\n";
        echo "  Suspended: " . ($p->is_suspended ? "YES" : "NO") . "\n";
        echo "  Service Availability: " . ($p->service_availability ? "YES" : "NO") . "\n";

        $subscribed = DB::table('subscribed_services')
            ->where('provider_id', $p->id)
            ->where('sub_category_id', $booking->sub_category_id)
            ->where('is_subscribed', 1)
            ->first();
        echo "  Subscribed to Sub-Cat: " . ($subscribed ? "YES" : "NO") . "\n";

        // Check ignores
        $ignored = DB::table('booking_ignores')
            ->where('booking_id', $booking->id)
            ->where('provider_id', $p->id)
            ->first();
        echo "  Ignored by this provider: " . ($ignored ? "YES" : "NO") . "\n";
    }
    echo "\n";
}

$maxAmount = DB::table('business_settings')->where('key_name', 'max_booking_amount')->where('settings_type', 'booking_setup')->first();
echo "Config Max Booking Amount: " . ($maxAmount ? $maxAmount->live_values : "NOT SET") . "\n";

$serviceAtProvider = DB::table('business_settings')->where('key_name', 'service_at_provider_place')->where('settings_type', 'provider_config')->first();
echo "Config Service at Provider Place: " . ($serviceAtProvider ? $serviceAtProvider->live_values : "NOT SET") . "\n";
