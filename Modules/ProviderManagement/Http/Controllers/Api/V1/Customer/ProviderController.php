<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Customer;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BookingModule\Entities\Booking;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\FavoriteProvider;
use Modules\ProviderManagement\Entities\Provider;
use Modules\ProviderManagement\Entities\ProviderServicePrice;
use Modules\ProviderManagement\Entities\SubscribedService;
use Modules\ReviewModule\Entities\Review;
use Modules\ServiceManagement\Entities\FavoriteService;
use Modules\ServiceManagement\Entities\Service;
use Modules\ServiceManagement\Entities\Variation;

class ProviderController extends Controller
{
    private Provider $provider;
    private Category $category;
    private SubscribedService $subscribed_service;
    private Booking $booking;

    private Service $service;
    private Variation $variation;
    private FavoriteProvider $favoriteProvider;
    private FavoriteService $favoriteService;
    private Review $review;
    private bool $is_customer_logged_in;
    private string|null $customer_user_id;

    public function __construct(Provider $provider, Review $review, Category $category, SubscribedService $subscribed_service, Booking $booking, Service $service, Variation $variation, FavoriteProvider $favoriteProvider, FavoriteService $favoriteService, Request $request)
    {
        $this->provider = $provider;
        $this->category = $category;
        $this->subscribed_service = $subscribed_service;
        $this->booking = $booking;
        $this->service = $service;
        $this->variation = $variation;
        $this->favoriteProvider = $favoriteProvider;
        $this->favoriteService = $favoriteService;
        $this->review = $review;

        $this->is_customer_logged_in = (bool) auth('api')->user();
        $this->customer_user_id = $this->is_customer_logged_in ? auth('api')->id() : $request['guest_id'];
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviderList(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'sort_by' => 'in:asc,desc,default,popular',
            'service_availability' => 'in:0,1',
            'category_ids' => 'array',
            'category_ids.*' => 'uuid',
            'rating' => '',
            'is_emergency' => 'in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $providersIds = $this->provider->ofStatus(1)->pluck('id');

        $eligibleProviderIds = $providersIds->filter(function ($id) {
            return nextBookingEligibility($id);
        })->values()->all();


        $providersQuery = $this->provider->with([
            'owner',
            'subscribed_services.sub_category' => function ($query) {
                $query->withoutGlobalScopes();
            }
        ])
            ->where('zone_id', Config::get('zone_id'))
            ->whereIn('id', $eligibleProviderIds)
            ->ofStatus(1)
            ->withCount([
                'bookings as total_service_served' => function ($query) {
                    $query->where('booking_status', 'completed');
                },
                'subscribed_services'
            ])
            ->when($request->has('category_ids'), function ($query) use ($request) {
                $query->whereHas('subscribed_services', function ($query) use ($request) {
                    if ($request->has('category_ids'))
                        $query->whereIn('category_id', $request['category_ids']);
                });
            })
            ->when($request->has('rating'), function ($query) use ($request) {
                $query->where('avg_rating', '>=', $request['rating']);
            })
            ->when($request->has('service_availability'), function ($query) use ($request) {
                $query->where('service_availability', $request['service_availability']);
            })
            ->when($request->has('is_emergency') && $request['is_emergency'] == '1', function ($query) {
                $query->where('is_emergency_active', 1);
            })
            ->when($request->has('sort_by'), function ($query) use ($request) {
                if ($request['sort_by'] == 'asc' || $request['sort_by'] == 'desc') {
                    $query->orderBy('company_name', $request['sort_by']);
                } elseif ($request['sort_by'] == 'popular') {
                    $query->orderBy('avg_rating', 'desc');
                }
            })
            ->when(!$request->has('sort_by') || $request['sort_by'] === 'default', function ($query) {
                $query->latest();
            })
            ->where('is_suspended', 0);

        $providers = $providersQuery->paginate($request['limit'], ['*'], 'page', $request['offset'])->withPath('');

        foreach ($providers as $provider) {
            $provider['is_favorite'] = $this->favoriteProvider
                ->where('customer_user_id', $this->customer_user_id)
                ->where('provider_id', $provider->id)
                ->exists() ? 1 : 0;
        }

        return response()->json(response_formatter(DEFAULT_200, $providers), 200);

    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviderDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $this->provider->with('owner')->withCount([
            'bookings as total_service_served' => function ($query) {
                $query->where('booking_status', 'completed');
            },
            'subscribed_services'
        ])->find($request['id']);

        $provider['is_favorite'] = $this->favoriteProvider
            ->where('customer_user_id', $this->customer_user_id)
            ->where('provider_id', $provider->id)
            ->exists() ? 1 : 0;

        if (!isset($provider))
            return response()->json(response_formatter(DEFAULT_404), 404);

        $review = $this->review
            ->with('customer', 'reviewReply')
            ->where('provider_id', $provider->id)
            ->where('review_comment', '!=', null)
            ->ofStatus(1)
            ->latest()
            ->paginate($request['limit'], ['*'], 'page', $request['offset'])
            ->withPath('');

        $timeSchedule = provider_config('time_schedule', 'service_schedule', $provider['id'])?->live_values;
        $weekEnds = provider_config('weekends', 'service_schedule', $provider['id'])->live_values ?? '';
        $weekEnds = json_decode($weekEnds);
        $timeSchedule = json_decode($timeSchedule);

        $provider['time_schedule'] = $timeSchedule ?? null;
        $provider['weekends'] = $weekEnds ?? [];

        // Terms and Conditions
        $terms = getProviderSettings($provider['id'], 'terms_and_conditions', 'terms_conditions');
        $defaultTerms = "By booking this service, you agree that if any additional faults or requirements are identified during the inspection or service that were not included in the initial booking, additional charges will apply. These charges will be discussed and agreed upon before proceeding with the extra work.";
        $provider['terms_and_conditions'] = $terms['terms'] ?? $defaultTerms;

        // About Us
        $aboutUs = getProviderSettings($provider['id'], 'about_us', 'about_us');
        $provider['about_us'] = $aboutUs['about_us'] ?? "";


        $provider['nextBookingEligibility'] = nextBookingEligibility($provider->id);
        $provider['scheduleBookingEligibility'] = scheduleBookingEligibility($provider->id);


        $limitStatus = provider_warning_amount_calculate($provider?->owner?->account->account_payable, $provider?->owner?->account->account_receivable);
        $provider['cash_limit_status'] = $limitStatus == false ? 'available' : $limitStatus;

        // Final fix for missing sub-categories: Aggregate both direct sub-category and service subscriptions
        $subscriptions = $this->subscribed_service
            ->ofStatus(1)
            ->where('provider_id', $provider->id)
            ->get();

        $directSubCategoryIds = $subscriptions->whereNotNull('sub_category_id')->pluck('sub_category_id')->unique()->toArray();
        $directServiceIds = $subscriptions->whereNotNull('service_id')->pluck('service_id')->unique()->toArray();

        // Get sub-category IDs from individually subscribed services
        $serviceSubCategoryIds = [];
        if (!empty($directServiceIds)) {
            $serviceSubCategoryIds = \Modules\ServiceManagement\Entities\Service::whereIn('id', $directServiceIds)
                ->pluck('sub_category_id')
                ->unique()
                ->toArray();
        }

        $allSubCategoryIds = array_unique(array_merge($directSubCategoryIds, $serviceSubCategoryIds));

        $subCategories = $this->category->withoutGlobalScopes()
            ->with([
                'services' => function ($query) use ($directSubCategoryIds, $directServiceIds) {
                    $query->ofStatus(1)
                        ->where(function ($query) use ($directSubCategoryIds, $directServiceIds) {
                            $query->whereIn('sub_category_id', $directSubCategoryIds)
                                ->orWhereIn('id', $directServiceIds);
                        })
                        ->with(['variations', 'service_discount', 'category.category_discount']);
                }
            ])
            ->whereHas('services', function ($query) use ($directSubCategoryIds, $directServiceIds) {
                $query->ofStatus(1)
                    ->where(function ($query) use ($directSubCategoryIds, $directServiceIds) {
                        $query->whereIn('sub_category_id', $directSubCategoryIds)
                            ->orWhereIn('id', $directServiceIds);
                    });
            })
            ->whereIn('id', $allSubCategoryIds)
            ->get();

        foreach ($subCategories as $item) {
            if ($item->services) {
                $item->services = self::variationMapper($item->services);

                foreach ($item->services as $service) {
                    $service->is_favorite = $this->favoriteService
                        ->where('customer_user_id', $this->customer_user_id)
                        ->where('service_id', $service->id)
                        ->exists() ? 1 : 0;

                    // Attach provider's configured service details (Service Configuration)
                    $sub = $subscriptions->first(function ($s) use ($service) {
                        return (string)$s->service_id === (string)$service->id;
                    });

                    if (!$sub) {
                        $sub = $subscriptions->first(function ($s) use ($service) {
                            return (string)$s->sub_category_id === (string)$service->sub_category_id && empty($s->service_id);
                        });
                    }

                    if ($sub) {
                        $service->service_types = !empty($sub->service_types) ? $sub->service_types : ['mobile', 'workshop'];
                        $service->estimated_time = $sub->estimated_time ?? null;
                        $service->service_price = $sub->service_price !== null ? (float)$sub->service_price : null;

                        $images = [];
                        if (!empty($sub->completed_service_images) && is_array($sub->completed_service_images)) {
                            foreach ($sub->completed_service_images as $img) {
                                $images[] = asset('storage/app/public/subscribed_service/' . $img);
                            }
                        }
                        $service->completed_service_images = $images;
                    } else {
                        $service->service_types = ['mobile', 'workshop'];
                        $service->estimated_time = null;
                        $service->service_price = null;
                        $service->completed_service_images = [];
                    }
                }
            }
        }

        $ratingGroupCount = DB::table('reviews')->where('provider_id', $provider->id)
            ->where('is_active', 1)
            ->select('review_rating', DB::raw('count(review_comment) as total_comment'), DB::raw('count(*) as total'))
            ->groupBy('review_rating')
            ->get();

        $totalRating = 0;
        $ratingCount = 0;
        $reviewCount = 0;

        foreach ($ratingGroupCount as $count) {
            $totalRating += round($count->review_rating * $count->total, 2);
            $ratingCount += $count->total;
            $reviewCount += $count->total_comment;
        }

        $ratingInfo = [
            'rating_count' => $ratingCount,
            'review_count' => $reviewCount,
            'average_rating' => round(divnum($totalRating, $ratingCount), 2),
            'rating_group_count' => $ratingGroupCount,
        ];

        return response()->json(response_formatter(DEFAULT_200, ['provider' => $provider, 'sub_categories' => $subCategories, 'reviews' => $review, 'rating' => $ratingInfo]), 200);
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function getProviderListBySubCategory(Request $request): JsonResponse
    {
        $providers = $this->provider->with(['owner'])
            ->where('zone_id', Config::get('zone_id'))
            ->whereHas('subscribed_services', function ($query) use ($request) {
                $query->where('sub_category_id', $request['sub_category_id']);
            })
            ->where('service_availability', 1)
            ->where('is_suspended', 0)
            ->where('is_active', 1)
            ->get();

        $eligibleProviders = [];

        foreach ($providers as $provider) {
            if (!nextBookingEligibility($provider->id)) {
                continue;
            }

            $limitStatus = provider_warning_amount_calculate(
                $provider->owner->account->account_payable,
                $provider->owner->account->account_receivable
            );
            $provider['cash_limit_status'] = $limitStatus === false ? 'available' : $limitStatus;

            $provider['is_favorite'] = $this->favoriteProvider
                ->where('customer_user_id', $this->customer_user_id)
                ->where('provider_id', $provider->id)
                ->exists() ? 1 : 0;

            $eligibleProviders[] = $provider;
        }

        return response()->json(response_formatter(DEFAULT_200, $eligibleProviders), 200);
    }

    private function variationMapper($services)
    {
        $services->map(function ($service) {
            $service['variations_app_format'] = self::variationsAppFormat($service);
            return $service;
        });
        return $services;
    }

    // OLD CODE - Kept for reference (before provider custom pricing)
    // private function variationsAppFormat($service): array
    // {
    //     $formatting = [];
    //     $filtered = $service['variations']->where('zone_id', Config::get('zone_id'));
    //     $formatting['zone_id'] = Config::get('zone_id');
    //     $formatting['default_price'] = $filtered->first() ? $filtered->first()->price : 0;
    //     foreach ($filtered as $data) {
    //         $formatting['zone_wise_variations'][] = [
    //             'variant_key' => $data['variant_key'],
    //             'variant_name' => $data['variant'],
    //             'price' => $data['price']
    //         ];
    //     }
    //     return $formatting;
    // }

    // NEW CODE - With provider custom pricing support
    private function variationsAppFormat($service): array
    {
        $formatting = [];
        $filtered = $service['variations']->where('zone_id', Config::get('zone_id'));
        $formatting['zone_id'] = Config::get('zone_id');
        $formatting['default_price'] = $filtered->first() ? $filtered->first()->price : 0;

        // Get provider ID if available (from service context or provider details request)
        $providerId = request()->input('provider_id') ?? request()->input('id') ?? null;

        foreach ($filtered as $data) {
            // Check for provider custom price
            $customPrice = null;
            if ($providerId) {
                $customPrice = \Modules\ProviderManagement\Entities\ProviderServicePrice::where('provider_id', $providerId)
                    ->where('service_id', $service->id)
                    ->where('variation_id', $data->id)
                    ->where('zone_id', Config::get('zone_id'))
                    ->where('is_active', 1)
                    ->first();
            }

            // Use provider custom price if available, otherwise use admin base price
            $finalPrice = $customPrice ? $customPrice->price : $data['price'];

            $formatting['zone_wise_variations'][] = [
                'variant_key' => $data['variant_key'],
                'variant_name' => $data['variant'],
                'price' => $finalPrice,
                'admin_price' => $data['price'], // Keep admin price for reference
                'has_custom_price' => $customPrice !== null ? 1 : 0
            ];
        }
        return $formatting;
    }

    public function getAvailableProvider(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'sort_by' => 'in:asc,desc',
            'booking_id' => 'required|uuid',
            'rating' => '',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->where('id', $request->booking_id)->first();

        $providers = $this->provider
            ->where('zone_id', $booking->zone_id)
            ->ofStatus(1)
            ->when(isset($booking->sub_category_id), function ($query) use ($request, $booking) {
                $query->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                    $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
                });
            })
            ->when($request->has('rating'), function ($query) use ($request) {
                $query->where('avg_rating', '>=', $request['rating']);
            })
            ->when($request->has('sort_by'), function ($query) use ($request) {
                $query->orderBy('company_name', $request['sort_by']);
            })
            ->when(!$request->has('sort_by'), function ($query) use ($request) {
                $query->latest();
            })
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        foreach ($providers as $provider) {
            $provider['is_favorite'] = $this->favoriteProvider->where('customer_user_id', $this->customer_user_id)->where('provider_id', $provider->id)->exists() ? 1 : 0;
        }


        return response()->json(response_formatter(DEFAULT_200, $providers), 200);
    }

    public function getAvailableService(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'service_ids' => 'array',
            'service_ids.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $serivces = $this->service
            ->where('is_active', 1)
            ->whereIn('id', $request['service_ids'])
            ->paginate($request['limit'], ['*'], 'offset', $request['offset'])->withPath('');

        return response()->json(response_formatter(DEFAULT_200, $serivces), 200);
    }

    public function rebookingInformation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required|numeric|min:1|max:200',
            'offset' => 'required|numeric|min:1|max:100000',
            'booking_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $booking = $this->booking->with('detail')->where('id', $request->booking_id)->first();
        $bookingServices = $booking->detail ?? [];

        //provider ...
        $provider = $this->provider
            ->where('id', $booking?->provider?->id)
            ->ofStatus(1)
            ->whereHas('owner', function ($query) {
                $query->ofStatus(1);
            })
            ->where('zone_id', $request->header('zoneid'))
            ->when(business_config('suspend_on_exceed_cash_limit_provider', 'provider_config')->live_values, function ($query) {
                $query->where('is_suspended', 0);
            })
            ->whereHas('subscribed_services', function ($query) use ($request, $booking) {
                $query->where('sub_category_id', $booking->sub_category_id)->where('is_subscribed', 1);
            })
            ->first();

        //service ...
        $services = [];
        foreach ($bookingServices as $key => $service) {
            $serviceData = $this->service->with([
                'variations' => function ($query) use ($service, $booking, $request) {
                    $query->where('variant_key', $service->variant_key)->where('zone_id', $request->header('zoneid'));
                }
            ])->where('id', $service->service_id)->active()->first();

            $services[] = [
                'service_id' => $service->service_id,
                'service_name' => $service->service_name,
                'variant_key' => $service->variant_key,

                'service_unit_cost' => $serviceData?->variations?->first()?->price,
                'booking_service_unit_cost' => $service->service_cost,

                'is_available' => $serviceData?->variations?->first() ? 1 : 0,
                'is_price_changed' => ($serviceData?->variations?->first()?->price == $service->service_cost) || $serviceData?->variations?->first()?->price == null ? 0 : 1,
            ];
        }

        $isServiceInfoUnchanged = count(array_filter($services, function ($service) {
            return $service['is_price_changed'] === 1;
        })) === 0 ? 1 : 0;

        $data = [
            'is_provider_available' => $provider ? 1 : 0,
            'is_service_info_unchanged' => $isServiceInfoUnchanged,
            'services' => $services,
        ];

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

    /**
     * Get providers by service ID and car details.
     * @param Request $request
     * @return JsonResponse
     */
    public function getProvidersByServiceAndDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'nullable|uuid', // Support single for backward compatibility
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'uuid',
            'car_registration_number' => 'nullable|string',
            'damage_description' => 'nullable|string',
            'car_image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:10240',
            'enter_postcode' => 'nullable|string',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'lat' => 'nullable',
            'lon' => 'nullable',
            'lng' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        try {
            $serviceIds = $request->has('service_ids') ? $request->service_ids : ($request->has('service_id') ? [$request->service_id] : []);
            if (empty($serviceIds)) {
                return response()->json(response_formatter(DEFAULT_400, null, 'At least one service ID is required.'), 400);
            }

            $userLat = $request->latitude ?? $request->lat;
            $userLon = $request->longitude ?? ($request->lon ?? $request->lng);
            $hasUserCoords = !empty($userLat) && !empty($userLon) && is_numeric($userLat) && is_numeric($userLon);

            $postcode = $request->enter_postcode;

            $services = Service::withoutGlobalScopes()->whereIn('id', $serviceIds)->get();
            if ($services->count() !== count($serviceIds)) {
                return response()->json(response_formatter(DEFAULT_404, null, 'One or more services not found.'), 404);
            }

            if ($request->has('car_image')) {
                file_uploader('user/car/', 'png', $request->file('car_image'));
            }

            $zoneId = Config::get('zone_id') ?? $request->header('zoneid');

            // Find providers that have ALL the requested services
            $providers = Provider::ofStatus(1)
                ->where(function ($query) use ($serviceIds) {
                    foreach ($serviceIds as $id) {
                        $query->whereHas('subscribed_services', function ($q) use ($id) {
                            $q->where('service_id', $id)->where('is_subscribed', 1);
                        });
                    }
                })
                ->where('zone_id', $zoneId)
                ->with(['owner', 'reviews'])
                ->get();

            // Load variations using DB::table to bypass Eloquent global scopes (zone_wise_data scope)
            $allServiceVariations = DB::table('variations')
                ->whereIn('service_id', $serviceIds)
                ->where('zone_id', $zoneId)
                ->get();

            // Load provider custom prices (variation-based) using DB::table
            $allCustomPrices = DB::table('provider_service_prices')
                ->whereIn('service_id', $serviceIds)
                ->where('is_active', 1)
                ->get();

            // Load subscribed_services details (flat price, service_types, estimated_time, images set by provider per service)
            $providerIds = $providers->pluck('id')->toArray();
            $allSubscribedPrices = DB::table('subscribed_services')
                ->whereIn('provider_id', $providerIds)
                ->whereIn('service_id', $serviceIds)
                ->where('is_subscribed', 1)
                ->select('provider_id', 'service_id', 'service_price', 'service_types', 'estimated_time', 'completed_service_images')
                ->get();

            $providers = $providers->map(function ($provider) use ($services, $allServiceVariations, $allCustomPrices, $allSubscribedPrices, $hasUserCoords, $userLat, $userLon) {
                $providerZoneId = $provider->zone_id;
                $providerSelectedServices = [];
                $totalPrice = 0;

                foreach ($services as $service) {
                    // Variations already filtered by zone_id from DB query above
                    $variationsForService = $allServiceVariations
                        ->where('service_id', $service->id);

                    // Custom prices for this specific provider + service + zone
                    $customPricesForProvider = $allCustomPrices
                        ->where('provider_id', $provider->id)
                        ->where('service_id', $service->id)
                        ->where('zone_id', $providerZoneId);

                    $formattedVariations = $variationsForService->map(function ($variation) use ($customPricesForProvider) {
                        // variation->id is integer; cast both sides for safe comparison
                        $customPrice = $customPricesForProvider
                            ->first(fn($p) => (string) $p->variation_id === (string) $variation->id);

                        $price = $customPrice ? $customPrice->price : $variation->price;

                        return [
                            'variant_key' => $variation->variant_key,
                            'variant' => $variation->variant,
                            'price' => (float) $price,
                            'admin_price' => (float) $variation->price,
                            'is_custom' => $customPrice ? 1 : 0,
                        ];
                    })->values();

                    $subscribedRow = $allSubscribedPrices
                        ->where('provider_id', $provider->id)
                        ->where('service_id', $service->id)
                        ->first();

                    $serviceTypes = ['mobile', 'workshop'];
                    if (!empty($subscribedRow?->service_types)) {
                        $parsedTypes = is_array($subscribedRow->service_types) ? $subscribedRow->service_types : json_decode($subscribedRow->service_types, true);
                        if (!empty($parsedTypes) && is_array($parsedTypes)) {
                            $serviceTypes = array_values($parsedTypes);
                        }
                    }

                    $completedImages = [];
                    if (!empty($subscribedRow?->completed_service_images)) {
                        $parsedImgs = is_array($subscribedRow->completed_service_images) ? $subscribedRow->completed_service_images : json_decode($subscribedRow->completed_service_images, true);
                        if (!empty($parsedImgs) && is_array($parsedImgs)) {
                            foreach ($parsedImgs as $img) {
                                $completedImages[] = asset('storage/app/public/subscribed_service/' . $img);
                            }
                        }
                    }

                    $estimatedTime = $subscribedRow?->estimated_time ?? null;

                    // If no zone-wise variations exist for this service, fall back to
                    // subscribed_services.service_price (flat price provider set in Service Configuration)
                    if ($formattedVariations->isEmpty()) {
                        $minPrice = (float) ($subscribedRow->service_price ?? 0);
                        $totalPrice += $minPrice;

                        $providerSelectedServices[] = [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'min_price' => $minPrice,
                            'service_price' => $minPrice,
                            'variations' => [],
                            'price_type' => 'flat',
                            'service_types' => $serviceTypes,
                            'estimated_time' => $estimatedTime,
                            'completed_service_images' => $completedImages,
                        ];
                    } else {
                        $minPrice = $formattedVariations->min('price') ?? 0;
                        $totalPrice += $minPrice;

                        $providerSelectedServices[] = [
                            'service_id' => $service->id,
                            'service_name' => $service->name,
                            'min_price' => $minPrice,
                            'service_price' => (float) ($subscribedRow->service_price ?? $minPrice),
                            'variations' => $formattedVariations,
                            'price_type' => 'variation',
                            'service_types' => $serviceTypes,
                            'estimated_time' => $estimatedTime,
                            'completed_service_images' => $completedImages,
                        ];
                    }
                }

                $provider->selected_services = $providerSelectedServices;
                $provider->total_selected_services_price = (float) $totalPrice;

                // Also attach service configuration directly to provider for convenience
                $provider->service_types = $providerSelectedServices[0]['service_types'] ?? ['mobile', 'workshop'];
                $provider->estimated_time = $providerSelectedServices[0]['estimated_time'] ?? null;
                $provider->completed_service_images = $providerSelectedServices[0]['completed_service_images'] ?? [];

                // Calculate distance between user and provider
                $coords = is_array($provider->coordinates) ? $provider->coordinates : (is_string($provider->coordinates) ? json_decode($provider->coordinates, true) : null);
                $providerLat = $coords['latitude'] ?? ($coords['lat'] ?? ($provider->latitude ?? null));
                $providerLon = $coords['longitude'] ?? ($coords['lon'] ?? ($coords['lng'] ?? ($provider->longitude ?? null)));

                $distanceKm = null;
                $distanceMiles = null;
                $distanceFormatted = null;

                if ($hasUserCoords && !empty($providerLat) && !empty($providerLon) && is_numeric($providerLat) && is_numeric($providerLon)) {
                    $rawDist = get_distance([(float) $userLat, (float) $userLon], [(float) $providerLat, (float) $providerLon], 'K');
                    if (is_numeric($rawDist) && !is_nan($rawDist) && !is_infinite($rawDist)) {
                        $distanceKm = (float) number_format($rawDist, 2, '.', '');
                        $distanceMiles = (float) number_format($rawDist * 0.621371, 2, '.', '');
                        $distanceFormatted = number_format($rawDist, 2) . ' km';
                    }
                }

                $provider->distance = $distanceKm;
                $provider->distance_in_km = $distanceKm;
                $provider->distance_in_miles = $distanceMiles;
                $provider->distance_formatted = $distanceFormatted;

                return $provider;
            });

            // Sort providers by distance (closest first) when user location is provided
            if ($hasUserCoords) {
                $providers = $providers->sortBy(function ($p) {
                    return $p->distance !== null ? $p->distance : 99999999;
                })->values();
            }

            if ($providers->count() > 0) {
                return response()->json(response_formatter(DEFAULT_200, $providers), 200);
            }

            return response()->json(response_formatter(DEFAULT_204), 200);

        } catch (\Exception $e) {
            return response()->json([
                'response_code' => 'server_error_500',
                'message' => 'Internal Server Error',
                'content' => null,
                'errors' => [['error_code' => '500', 'message' => $e->getMessage()]]
            ], 500);
        }
    }

    /**
     * Get all providers with emergency mode active (Customer side)
     * @param Request $request
     * @return JsonResponse
     */
    public function getEmergencyProviders(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'nullable|uuid',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $providers = $this->provider
            ->where('is_emergency_active', 1)
            ->where('is_active', 1)
            ->where('is_approved', 1)
            ->when($request->filled('zone_id'), fn($q) => $q->where('zone_id', $request->zone_id))
            ->select([
                'id',
                'user_id',
                'zone_id',
                'logo',
                'avg_rating',
                'rating_count',
                'order_count',
                'after_hours_available',
                'weekend_emergency_available',
                'emergency_response_time',
            ])
            ->with(['owner:id,f_name,l_name,phone,email'])
            ->skip($offset)
            ->take($limit)
            ->get();

        return response()->json(response_formatter(DEFAULT_200, [
            'total' => $providers->count(),
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'providers' => $providers,
        ]), 200);
    }

}
