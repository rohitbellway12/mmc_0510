<?php

use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Modules\BusinessSettingsModule\Entities\BusinessPageSetting;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderSetting;
use Ramsey\Uuid\Uuid;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/image-proxy', function () {
    $url = request('url');
    if (!$url) {
        abort(400, 'Missing url parameter');
    }

    $response = Http::withHeaders([
        'User-Agent' => 'Laravel-Image-Proxy'
    ])->get($url);

    return response($response->body(), $response->status())
        ->header('Content-Type', $response->header('Content-Type'))
        ->header('Access-Control-Allow-Origin', '*');
});

Route::get('lang/{locale}', [LandingController::class, 'lang'])->name('lang');
Route::get('/', [LandingController::class, 'home'])->name('home');
//Route::get('page/about-us', [LandingController::class, 'aboutUs'])->name('page.about-us');
//Route::get('page/privacy-policy', [LandingController::class, 'privacyPolicy'])->name('page.privacy-policy');
//Route::get('page/terms-and-conditions', [LandingController::class, 'termsAndConditions'])->name('page.terms-and-conditions');
Route::get('page/contact-us', [LandingController::class, 'contactUs'])->name('page.contact-us');
//Route::get('page/cancellation-policy', [LandingController::class, 'cancellationPolicy'])->name('page.cancellation-policy');
//Route::get('page/refund-policy', [LandingController::class, 'refundPolicy'])->name('page.refund-policy');

Route::get('business-page/{slug}', [LandingController::class, 'dynamicPage'])->name('business.page.dynamic');

Route::get('maintenance-mode', [LandingController::class, 'maintenanceMode'])->name('maintenance-mode');
Route::post('subscribe-newsletter',[LandingController::class, 'subscribeNewsletter'])->name('subscribe-newsletter');

Route::fallback(function () {
    return redirect('admin/auth/login');
});



Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return "All cache cleared successfully!";
});




Route::get('test', function () {

    // Run artisan command
    Artisan::call('app:generate-admin-routes-json', ['type' => 'admin']);

    // Optionally return the output
    return Artisan::output();

//    $language = business_config('system_language', 'business_information');
//    $liveValues = $language->live_values;
//
//    foreach ($liveValues as $key => $value) {
//        if (!array_key_exists('name', $value)) {
//            $liveValues[$key]['name'] = $value['code'];
//        }
//    }
//
//    BusinessSettings::updateOrCreate(
//        ['key_name' => 'system_language'],
//        [
//            'live_values' => $liveValues,
//            'test_values' => $liveValues,
//        ]
//    );
//
//
//
//    $notifications = \Modules\BusinessSettingsModule\Entities\NotificationSetup::whereNull('key_type')->get();
//
//    $NOTIFICATION_KEY = [
//        'refer_earn' => 'wallet',
//        'wallet' => 'wallet',
//        'booking' => 'booking',
//        'terms_&_conditions_update' => 'business_page',
//        'verification' => 'authentication',
//        'chatting' => 'message',
//        'transaction' => 'transaction',
//        'subscription' => 'subscription',
//        'privacy_policy_update' => 'business_page',
//        'registration' => 'registration',
//        'system_update' => 'system',
//        'loyality_point' => 'wallet',
//        'advertisement' => 'advertisement',
//    ];
//
//    foreach ($notifications as $notification) {
//        if (isset($NOTIFICATION_KEY[$notification->key])) {
//            $notification->key_type = $NOTIFICATION_KEY[$notification->key];
//            $notification->save();
//        }
//    }
//
//    //update social media status
//    $socialMedia = BusinessSettings::where('settings_type', 'landing_social_media')->first();
//
//    $array = [];
//    if ($socialMedia && is_array($socialMedia->live_values)) {
//        $array = $socialMedia->live_values;
//    }
//
//    foreach ($array as &$item) {
//        if (!isset($item['status'])) {
//            $item['status'] = 1;
//        }
//    }
//
//    $socialMedia->live_values = $array;
//    $socialMedia->save();
//
//
//    //update business page
//    $dataSettings = DataSetting::where('type','pages_setup')
//        ->withoutGlobalScope('translate')
//        ->with('translations')
//        ->get();
//
//    foreach ($dataSettings as $dataSetting) {
//
//        if ($dataSetting->type !== 'pages_setup') continue;
//
//        $pageKey = strtolower(str_replace('_', '-', trim($dataSetting->key)));
//        $pageTitle = strtolower(str_replace('_', ' ', trim($dataSetting->key)));
//
//        if (!BusinessPageSetting::where('page_key', $pageKey)->exists()) {
//            $imageValue = optional($dataSettings->firstWhere('key', $dataSetting->key . '_image'))->value;
//
//            $page = new BusinessPageSetting();
//            $page->page_key = $pageKey;
//            $page->title = $pageTitle;
//            $page->content = $dataSetting->value;
//            $page->is_active = $dataSetting->is_active ?? 1;
//            $page->is_default = 1;
//            $page->image = $imageValue;
//            $page->save();
//
//            foreach ($dataSetting->translations as $translation) {
//                if (!empty($translation->value)) {
//                    $page->translations()->updateOrCreate(
//                        [
//                            'locale' => $translation->locale,
//                            'key' => $pageKey . '_content',
//                        ],
//                        [
//                            'value' => $translation->value,
//                        ]
//                    );
//
//                    $page->translations()->updateOrCreate(
//                        [
//                            'locale' => $translation->locale,
//                            'key' => $pageKey . '_title',
//                        ],
//                        [
//                            'value' => $pageTitle,
//                        ]
//                    );
//                }
//            }
//        }
//    }


});



