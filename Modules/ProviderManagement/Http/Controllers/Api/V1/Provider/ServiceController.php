<?php

namespace Modules\ProviderManagement\Http\Controllers\Api\V1\Provider;

use Carbon\Carbon as CarbonDateTime;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\BusinessSettingsModule\Entities\PackageSubscriber;
use Modules\BusinessSettingsModule\Entities\PackageSubscriberLimit;
use Modules\CategoryManagement\Entities\Category;
use Modules\ProviderManagement\Entities\SubscribedService;


class ServiceController extends Controller
{
    private $subscribedService, $category;
    private PackageSubscriber $packageSubscriber;
    private PackageSubscriberLimit $packageSubscriberLimit;

    public function __construct(SubscribedService $subscribedService, Category $category, PackageSubscriber $packageSubscriber, PackageSubscriberLimit $packageSubscriberLimit)
    {
        $this->subscribedService = $subscribedService;
        $this->packageSubscriber = $packageSubscriber;
        $this->packageSubscriberLimit = $packageSubscriberLimit;
        $this->category = $category;
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return JsonResponse
     */
    public function availableServices(Request $request): JsonResponse
    {
        $categoryId = $request->query('category_id');

        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_404), 200);
        }
        $providerId = $provider->id;

        // Get active subscribed services for this provider
        $subscribedServices = $this->subscribedService->where('provider_id', $providerId)
            ->where('is_subscribed', 1)
            ->whereNotNull('service_id')
            ->get()
            ->keyBy('service_id');

        $subscribedServiceIds = $subscribedServices->keys()->toArray();

        // Get all active main categories in the provider's zone
        $categories = $this->category->ofStatus(1)->ofType('main')
            ->whereHas('zones', function ($query) use ($provider) {
                return $query->where('zone_id', $provider->zone_id);
            })->latest()->get();

        // Query services with zone filtering
        $services = \Modules\ServiceManagement\Entities\Service::with([
            'category',
            'variations' => function ($query) use ($provider) {
                $query->where('zone_id', $provider->zone_id);
            }
        ])
            ->where('is_active', 1)
            ->when($categoryId && $categoryId != 'all', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->whereHas('category.zones', function ($query) use ($provider) {
                $query->where('zone_id', $provider->zone_id);
            })
            ->whereHas('category', function ($query) {
                $query->where('is_active', 1);
            })
            ->latest()
            ->paginate($request['limit'] ?? 100, ['*'], 'offset', $request['offset'] ?? 1)->withPath('');

        // Map is_subscribed status and configuration details to each service
        $services->getCollection()->transform(function ($service) use ($subscribedServices) {
            $sub = $subscribedServices->get($service->id);
            $service->is_subscribed = $sub ? true : false;

            if ($sub) {
                $service->service_types = !empty($sub->service_types) ? $sub->service_types : ['mobile', 'workshop'];
                $service->estimated_time = $sub->estimated_time;
                $service->service_price = $sub->service_price !== null ? (float) $sub->service_price : null;

                $images = [];
                if (!empty($sub->completed_service_images) && is_array($sub->completed_service_images)) {
                    foreach ($sub->completed_service_images as $img) {
                        $images[] = asset('storage/app/public/subscribed_service/' . $img);
                    }
                }
                $service->completed_service_images = $images;
            } else {
                $service->service_types = [];
                $service->estimated_time = null;
                $service->service_price = null;
                $service->completed_service_images = [];
            }
            return $service;
        });

        // Get provider's custom prices
        $customPrices = \Modules\ProviderManagement\Entities\ProviderServicePrice::where('provider_id', $provider->id)
            ->where('zone_id', $provider->zone_id)
            ->get()
            ->keyBy(function ($item) {
                return $item->service_id . '_' . $item->variation_id;
            })
            ->toArray();

        $data = [
            'categories' => $categories,
            'services' => $services,
            'custom_prices' => $customPrices
        ];

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSubscription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sub_category_id' => 'array',
            'sub_category_id.*' => 'uuid',
            'service_id' => 'array',
            'service_id.*' => 'uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $packageSubscriber = $this->packageSubscriber->where('provider_id', $request->user()->provider->id)->first();
        $limit = $this->packageSubscriberLimit
            ->where('provider_id', $request->user()->provider->id)
            ->where('subscription_package_id', $packageSubscriber?->subscription_package_id)
            ->where('key', 'category')
            ->first();

        $packageSubscriberLimit = $limit?->limit_count;
        $isLimit = $limit?->is_limited;
        $startDate = $packageSubscriber?->package_start_date;
        $endDate = $packageSubscriber?->package_end_date;
        $providerId = $packageSubscriber?->provider_id;
        $currentDate = CarbonDateTime::now()->subDays();
        $packageEndDate = $endDate ? CarbonDateTime::parse($endDate)->endOfDay() : null;
        $isPackageEnded = $packageEndDate ? $currentDate->diffInDays($packageEndDate, false) : null;

        $categoryCount = $this->subscribedService->where('provider_id', $providerId)->where('is_subscribed', 1)
            ->count();

        // Handle Request based on service_id (New Logic)
        if ($request->has('service_id')) {
            foreach ($request['service_id'] as $id) {
                // Get category_id from the service first to ensure validity and fix 500 error
                $service = \Modules\ServiceManagement\Entities\Service::withoutGlobalScope('zone_wise_data')->find($id);

                if (!$service) {
                    continue;
                }

                // Check if already subscribed
                $subscribedService = $this->subscribedService->where('service_id', $id)
                    ->where('provider_id', $request->user()->provider->id)
                    ->first();

                // Logic to check limits (only if subscribing)
                $isSubscribing = !$subscribedService || ($subscribedService && $subscribedService->is_subscribed == 0);

                if ($isSubscribing) {
                    if ($packageSubscriberLimit !== null && $categoryCount >= $packageSubscriberLimit && $packageSubscriber && $isLimit && $isPackageEnded) {
                        return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
                    }
                }

                if (!$subscribedService) {
                    $subscribedService = new $this->subscribedService;
                    $subscribedService->is_subscribed = 1;
                } else {
                    $subscribedService->is_subscribed = !$subscribedService->is_subscribed;
                }

                $subscribedService->provider_id = $request->user()->provider->id;
                $subscribedService->service_id = $id;
                $subscribedService->sub_category_id = null; // Direct service has no sub-category context here
                $subscribedService->category_id = $service->category_id;

                $subscribedService->save();
            }
        }
        // Handle Request based on sub_category_id (Legacy/Existing Logic)
        elseif ($request->has('sub_category_id')) {
            foreach ($request['sub_category_id'] as $id) {
                $subscribedService = $this->subscribedService::where('sub_category_id', $id)->where('provider_id', $request->user()->provider->id)->first();
                if (!$subscribedService) {
                    if ($packageSubscriberLimit !== null && $categoryCount >= $packageSubscriberLimit && $packageSubscriber && $isLimit && $isPackageEnded) {
                        return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
                    }

                    $subscribedService = new $this->subscribedService;
                    $subscribedService->is_subscribed = 1;

                } elseif ($subscribedService) {
                    if ($subscribedService->is_subscribed == 0) {
                        if ($packageSubscriberLimit !== null && $categoryCount >= $packageSubscriberLimit && $packageSubscriber && $isLimit && $isPackageEnded) {
                            return response()->json(response_formatter(CATEGORY_LIMIT_END), 400);
                        }
                    }

                    $subscribedService->is_subscribed = !$subscribedService->is_subscribed;
                }
                $subscribedService->provider_id = $request->user()->provider->id;
                $subscribedService->sub_category_id = $id;

                $parent = $this->category->where('id', $id)->first();
                if ($parent) {
                    $subscribedService->category_id = $parent->parent_id;
                }

                $subscribedService->save();
            }
        }

        return response()->json(response_formatter(DEFAULT_200), 200);
    }

    public function updateServiceDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|uuid',
            'estimated_time' => 'nullable|string|max:255',
            'service_types' => 'array',
            'service_types.*' => 'in:mobile,workshop',
            'variations' => 'array',
            'service_price' => 'nullable|numeric|min:0',
            'service_capabilities' => 'nullable|array',
            'service_capabilities.*' => 'string',
            'tyres' => 'nullable|array',
            'tyres.*.brand' => 'required_with:tyres|string',
            'tyres.*.size' => 'required_with:tyres|string',
            'tyres.*.price' => 'nullable|numeric',
            'tyres.*.stock' => 'nullable|integer',
            'tyres.*.images' => 'nullable|array',
            'cars' => 'nullable|array',
            'cars.*.brand' => 'required_with:cars|string',
            'cars.*.model' => 'required_with:cars|string',
            'cars.*.pricing_type' => 'nullable|in:both,hourly,daily',
            'cars.*.hourly_rate' => 'required_if:cars.*.pricing_type,both,hourly|numeric|min:0',
            'cars.*.daily_rate' => 'required_if:cars.*.pricing_type,both,daily|numeric|min:0',
            'cars.*.service_category' => 'nullable|in:car_hire,chauffeur',
            'cars.*.car_type_id' => 'nullable',
            'cars.*.air_conditioning' => 'nullable|boolean',
            'cars.*.available_hours_start' => 'nullable',
            'cars.*.available_hours_end' => 'nullable',
            'cars.*.preferred_areas' => 'nullable',
            'cars.*.images' => 'nullable|array',
            'cars.*.car_images' => 'nullable|array',
            'cars.*.car_images.front_view' => 'nullable|image|max:5120',
            'cars.*.car_images.rear_view' => 'nullable|image|max:5120',
            'cars.*.car_images.interior' => 'nullable|image|max:5120',
            'cars.*.car_images.dashboard' => 'nullable|image|max:5120',
            'cars.*.manufacture_year' => 'nullable|string',
            'cars.*.seating_capacity' => 'nullable|string',
            'cars.*.transmission_type' => 'nullable|string',
            'cars.*.security_deposit' => 'nullable|numeric|min:0',
            'cars.*.postcode' => 'nullable|string',
            'cars.*.address' => 'nullable|string',
            'cars.*.available_for' => 'nullable|string',
            'cars.*.terms_conditions' => 'nullable|string',
            'cars.*.driving_license' => 'nullable',
            'cars.*.vehicle_registration' => 'nullable',
            'cars.*.insurance_documents' => 'nullable',
            'cars.*.mot_certificate' => 'nullable',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:10240',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;
        $serviceId = trim(str_replace(['"', "'"], '', $request['service_id'])); // Robust sanitation

        $subscribedService = $this->subscribedService
            ->where('provider_id', $provider->id)
            ->whereRaw('service_id = ?', [$serviceId])
            ->first();

        if ($subscribedService) {
            if ($request->has('estimated_time')) {
                $subscribedService->estimated_time = $request->estimated_time;
            }
            if ($request->has('service_types')) {
                $subscribedService->service_types = $request->service_types;
            }
            if ($request->has('service_capabilities')) {
                $subscribedService->service_capabilities = $request->service_capabilities;
            }
            if ($request->has('service_price')) {
                $subscribedService->service_price = $request->service_price;
            }

            // Handle Image Upload and Removal
            $images = $subscribedService->completed_service_images ?? [];

            // Removal logic
            if ($request->has('remove_images')) {
                foreach ($request->remove_images as $imgName) {
                    if (($key = array_search($imgName, $images)) !== false) {
                        unset($images[$key]);
                        file_remover('subscribed_service/', $imgName);
                    }
                }
                $images = array_values($images); // Re-index
            }

            // Upload logic
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $images[] = file_uploader('subscribed_service/', $image->getClientOriginalExtension(), $image);
                }
            } elseif ($request->has('images')) {
                // Support for Base64 if needed by Mobile App
                foreach ($request->images as $image) {
                    if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                        $data = substr($image, strpos($image, ',') + 1);
                        $type = strtolower($type[1]);
                        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png']))
                            $type = 'png';
                        $data = base64_decode($data);
                        if ($data === false)
                            continue;
                        $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $type;
                        \Illuminate\Support\Facades\Storage::disk(getDisk())->put('subscribed_service/' . $imageName, $data);
                        $images[] = $imageName;
                    }
                }
            }

            $subscribedService->completed_service_images = $images;
            $subscribedService->save();

            // Save variations pricing
            if ($request->has('variations')) {
                foreach ($request->variations as $variationId => $price) {
                    if ($price === null || $price === '')
                        continue;

                    $providerPrice = \Modules\ProviderManagement\Entities\ProviderServicePrice::where('provider_id', $provider->id)
                        ->where('service_id', $serviceId)
                        ->where(function ($query) use ($provider) {
                            if ($provider->zone_id) {
                                $query->where('zone_id', $provider->zone_id);
                            } else {
                                $query->whereNull('zone_id');
                            }
                        })
                        ->first();

                    if ($providerPrice) {
                        $providerPrice->price = $price;
                        $providerPrice->save();
                    } else {
                        \Modules\ProviderManagement\Entities\ProviderServicePrice::create([
                            'provider_id' => $provider->id,
                            'service_id' => $serviceId,
                            'zone_id' => $provider->zone_id, // Will be null if provider has no zone
                            'price' => $price,
                            'is_active' => 1,
                        ]);
                    }
                }
            }

            // Save Tyres
            if ($request->has('tyres')) {
                foreach ($request->tyres as $tyreData) {
                    // Basic validation could be done here or relied upon via request validation rules if expanded
                    if (!isset($tyreData['brand']) || !isset($tyreData['size'])) {
                        continue;
                    }

                    $tyreId = $tyreData['id'] ?? null;
                    $tyre = null;

                    if ($tyreId) {
                        $tyre = \Modules\ServiceManagement\Entities\Tyre::where('id', $tyreId)
                            ->where('provider_id', $provider->id)
                            ->first();
                    }

                    if (!$tyre) {
                        $tyre = new \Modules\ServiceManagement\Entities\Tyre();
                        $tyre->provider_id = $provider->id;
                        $tyre->category_id = $subscribedService->category_id; // Use the service's category
                    }

                    $tyre->brand = $tyreData['brand'];
                    $tyre->model = $tyreData['model'] ?? null;
                    $tyre->tyre_type = $tyreData['tyre_type'] ?? ($tyre->tyre_type ?? 'tubeless');
                    $tyre->season = $tyreData['season'] ?? ($tyre->season ?? 'all_season');
                    $tyre->vehicle_type = $tyreData['vehicle_type'] ?? ($tyre->vehicle_type ?? 'passenger_car');
                    $tyre->width = $tyreData['width'] ?? $tyre->width;
                    $tyre->profile = $tyreData['profile'] ?? $tyre->profile;
                    $tyre->rim_size = $tyreData['rim_size'] ?? $tyre->rim_size;
                    $tyre->speed_rating = $tyreData['speed_rating'] ?? $tyre->speed_rating;
                    $tyre->load_index = $tyreData['load_index'] ?? $tyre->load_index;
                    $tyre->size = $tyreData['size'];
                    $tyre->price = $tyreData['price'] ?? 0;
                    $tyre->stock = $tyreData['stock'] ?? 0;
                    $tyre->status = 1;

                    // Handle Images
                    if (isset($tyreData['images']) && is_array($tyreData['images'])) {
                        $images = $tyre->images ?? [];
                        foreach ($tyreData['images'] as $image) {
                            if (!empty($image)) {
                                // Check if it's a Base64 string
                                if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                                    $data = substr($image, strpos($image, ',') + 1);
                                    $type = strtolower($type[1]); // jpg, png, gif

                                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                                        $type = 'png'; // Default fallback
                                    }

                                    $data = base64_decode($data);
                                    if ($data === false) {
                                        continue;
                                    }

                                    $imageName = CarbonDateTime::now()->toDateString() . "-" . uniqid() . "." . $type;
                                    \Illuminate\Support\Facades\Storage::disk(getDisk())->put('tyre/' . $imageName, $data);
                                    $images[] = $imageName;
                                } else {
                                    // Fallback for file upload logic (non-base64)
                                    $images[] = file_uploader('tyre/', 'png', $image);
                                }
                            }
                        }
                        $tyre->images = $images;
                    }

                    $tyre->save();
                }
            }

            // Save Cars
            if ($request->has('cars')) {
                foreach ($request->cars as $carData) {
                    $carId = $carData['id'] ?? null;
                    $car = null;

                    if ($carId) {
                        $car = \Modules\CarHire\Entities\Car::where('id', $carId)
                            ->where('provider_id', $provider->id)
                            ->first();
                    }

                    if (!$car) {
                        $car = new \Modules\CarHire\Entities\Car();
                        $car->provider_id = $provider->id;
                        $car->category_id = $subscribedService->category_id;
                    }

                    if (isset($carData['brand']))
                        $car->brand = $carData['brand'];
                    if (isset($carData['model']))
                        $car['model'] = $carData['model'];
                    if (isset($carData['service_category']))
                        $car->service_category = $carData['service_category'];
                    if (isset($carData['car_type_id']))
                        $car->car_type_id = $carData['car_type_id'];
                    if (isset($carData['air_conditioning']))
                        $car->air_conditioning = $carData['air_conditioning'];
                    if (isset($carData['pricing_type']))
                        $car->pricing_type = $carData['pricing_type'];
                    if (isset($carData['hourly_rate']))
                        $car->hourly_rate = $carData['hourly_rate'];
                    if (isset($carData['daily_rate']))
                        $car->daily_rate = $carData['daily_rate'];

                    // New fields synchronization
                    if (isset($carData['manufacture_year']))
                        $car->manufacture_year = $carData['manufacture_year'];
                    if (isset($carData['seating_capacity']))
                        $car->seating_capacity = $carData['seating_capacity'];
                    if (isset($carData['transmission_type']))
                        $car->transmission_type = $carData['transmission_type'];
                    if (isset($carData['security_deposit']))
                        $car->security_deposit = $carData['security_deposit'];
                    if (isset($carData['postcode']))
                        $car->postcode = $carData['postcode'];
                    if (isset($carData['address']))
                        $car->address = $carData['address'];
                    if (isset($carData['available_for']))
                        $car->available_for = $carData['available_for'];
                    if (isset($carData['terms_conditions']))
                        $car->terms_conditions = $carData['terms_conditions'];

                    // Zero out rates based on pricing type
                    if ($car->pricing_type === 'daily') {
                        $car->hourly_rate = 0;
                    } elseif ($car->pricing_type === 'hourly') {
                        $car->daily_rate = 0;
                    }

                    if (isset($carData['available_hours_start']))
                        $car->available_hours_start = $carData['available_hours_start'];
                    if (isset($carData['available_hours_end']))
                        $car->available_hours_end = $carData['available_hours_end'];
                    if (isset($carData['preferred_areas']))
                        $car->preferred_areas = $carData['preferred_areas'];
                    if (isset($carData['registration_number']))
                        $car->registration_number = $carData['registration_number'];
                    if (isset($carData['features']))
                        $car->features = is_array($carData['features']) ? $carData['features'] : json_decode($carData['features'], true);

                    // Defaults
                    if (!$car->service_category)
                        $car->service_category = 'car_hire';
                    $car->status = 1;

                    // Handle Images (View-Specific or Generic)
                    if (isset($carData['car_images']) && is_array($carData['car_images'])) {
                        // Advanced view-specific logic (Front, Rear, etc.)
                        $images = $car->images ?? [null, null, null, null];
                        $views = ['front_view', 'rear_view', 'interior', 'dashboard'];
                        foreach ($views as $index => $view) {
                            if (isset($carData['car_images'][$view]) && !empty($carData['car_images'][$view])) {
                                $image = $carData['car_images'][$view];
                                if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                                    $data = substr($image, strpos($image, ',') + 1);
                                    $type = strtolower($type[1]);
                                    $data = base64_decode($data);
                                    if ($data !== false) {
                                        $imageName = CarbonDateTime::now()->toDateString() . "-" . uniqid() . "." . $type;
                                        \Illuminate\Support\Facades\Storage::disk(getDisk())->put('car/' . $imageName, $data);
                                        $images[$index] = $imageName;
                                    }
                                } else {
                                    $images[$index] = file_uploader('car/', 'png', $image);
                                }
                            }
                        }
                        $car->images = $images;
                    } elseif (isset($carData['images']) && is_array($carData['images'])) {
                        // Legacy generic array logic
                        $images = $car->images ?? [];
                        foreach ($carData['images'] as $image) {
                            if (!empty($image)) {
                                if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
                                    $data = substr($image, strpos($image, ',') + 1);
                                    $type = strtolower($type[1]);
                                    if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png']))
                                        $type = 'png';
                                    $data = base64_decode($data);
                                    if ($data === false)
                                        continue;
                                    $imageName = CarbonDateTime::now()->toDateString() . "-" . uniqid() . "." . $type;
                                    \Illuminate\Support\Facades\Storage::disk(getDisk())->put('car/' . $imageName, $data);
                                    $images[] = $imageName;
                                } else {
                                    $images[] = file_uploader('car/', 'png', $image);
                                }
                            }
                        }
                        $car->images = $images;
                    }

                    // Handle Documents helper function
                    $uploadDoc = function ($docData, $path) {
                        if (is_string($docData) && preg_match('/^data:image\/(\w+);base64,/', $docData, $type)) {
                            $data = substr($docData, strpos($docData, ',') + 1);
                            $type = strtolower($type[1]);
                            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png']))
                                $type = 'png';
                            $data = base64_decode($data);
                            if ($data === false)
                                return null;
                            $imageName = CarbonDateTime::now()->toDateString() . "-" . uniqid() . "." . $type;
                            \Illuminate\Support\Facades\Storage::disk(getDisk())->put($path . $imageName, $data);
                            return $imageName;
                        } else {
                            return file_uploader($path, 'png', $docData);
                        }
                    };

                    if (isset($carData['driving_license']))
                        $car->driving_license = $uploadDoc($carData['driving_license'], 'car/documents/');
                    if (isset($carData['vehicle_registration']))
                        $car->vehicle_registration = $uploadDoc($carData['vehicle_registration'], 'car/documents/');
                    if (isset($carData['insurance_documents']))
                        $car->insurance_documents = $uploadDoc($carData['insurance_documents'], 'car/documents/');
                    if (isset($carData['mot_certificate']))
                        $car->mot_certificate = $uploadDoc($carData['mot_certificate'], 'car/documents/');

                    $car->save();
                }
            }

            return response()->json(response_formatter(DEFAULT_200), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }

    public function getServiceDetails(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $provider = $request->user()->provider;
        $serviceId = trim(str_replace(['"', "'"], '', $request['service_id']));

        $subscribedService = $this->subscribedService
            ->where('provider_id', $provider->id)
            ->whereRaw('service_id = ?', [$serviceId])
            ->first();

        if ($subscribedService) {
            $service = \Modules\ServiceManagement\Entities\Service::withoutGlobalScope('zone_wise_data')
                ->with([
                    'variations' => function ($query) {
                        $query->withoutGlobalScope('zone_wise_data');
                    }
                ])
                ->find($serviceId);

            if (!$service) {
                return response()->json(response_formatter(DEFAULT_404), 200);
            }

            // Map provider price to variations
            $service->variations->map(function ($variation) use ($provider, $serviceId) {
                $providerPrice = \Modules\ProviderManagement\Entities\ProviderServicePrice::where('provider_id', $provider->id)
                    ->where('service_id', $serviceId)
                    ->where('variation_id', $variation->id)
                    ->first();

                $variation->price = $providerPrice ? $providerPrice->price : $variation->price;
                return $variation;
            });

            // Fetch Tyres
            $tyres = \Modules\ServiceManagement\Entities\Tyre::where('provider_id', $provider->id)
                ->where('category_id', $subscribedService->category_id)
                ->get();

            // Fetch Cars and map full paths
            $cars = \Modules\CarHire\Entities\Car::where('provider_id', $provider->id)
                ->where('category_id', $subscribedService->category_id)
                ->get()
                ->map(function ($car) {
                    $carArray = $car->toArray();

                    // Car images full path
                    if (!empty($car->images)) {
                        $carArray['images_full_path'] = collect($car->images)->map(function ($img) {
                            return asset('storage/app/public/car/' . $img);
                        });
                    } else {
                        $carArray['images_full_path'] = [];
                    }

                    // Document full paths
                    $docFields = ['driving_license', 'vehicle_registration', 'insurance_documents', 'mot_certificate'];
                    foreach ($docFields as $field) {
                        if ($car->{$field}) {
                            $carArray[$field . '_full_path'] = asset('storage/app/public/car/documents/' . $car->{$field});
                        } else {
                            $carArray[$field . '_full_path'] = null;
                        }
                    }

                    return $carArray;
                });

            $subscribedServiceArray = $subscribedService->toArray();
            if ($subscribedService->completed_service_images) {
                $subscribedServiceArray['completed_service_images_full_path'] = collect($subscribedService->completed_service_images)->map(function ($img) {
                    return asset('storage/app/public/subscribed_service/' . $img);
                });
            }

            $data = [
                'service' => $service,
                'subscribed_service' => $subscribedServiceArray,
                'tyres' => $tyres,
                'cars' => $cars
            ];

            return response()->json(response_formatter(DEFAULT_200, $data), 200);
        }

        return response()->json(response_formatter(DEFAULT_404), 200);
    }
}
