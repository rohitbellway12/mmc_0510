<?php

namespace Modules\CarHire\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\CarHire\Entities\Car;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;
use Modules\CarHire\Entities\FuelTypes;
use Modules\CarHire\Entities\Transmissions;
use Modules\CategoryManagement\Entities\Category;

class ProviderCarController extends Controller
{
    private Car $car;

    public function __construct(Car $car)
    {
        $this->car = $car;
    }

    /**
     * Get Provider Fleet Cars
     */
    public function index(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $serviceCategory = $request->get('service_category', 'all');
        $search = $request->get('search', '');
        $status = $request->get('status', 'all');

        $cars = $this->car->where('provider_id', $provider->id)
            ->when($serviceCategory != 'all', function ($q) use ($serviceCategory) {
                $q->where('service_category', $serviceCategory);
            })
            ->when($status !== 'all', function ($q) use ($status) {
                $q->where('status', (int)$status);
            })
            ->when(!empty($search), function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->with(['type', 'category'])
            ->latest()
            ->paginate($request->get('limit', 15), ['*'], 'offset', $request->get('offset', 1));

        $cars->getCollection()->transform(function ($car) {
            $car->image_full_paths = $this->getImageFullPaths($car->images);
            return $car;
        });

        return response()->json(response_formatter(DEFAULT_200, $cars), 200);
    }

    /**
     * Get master attributes for adding/editing cars
     */
    public function getAttributes(Request $request): JsonResponse
    {
        $brands = CarBrands::where('status', 1)->with(['models' => function ($q) {
            $q->where('status', 1);
        }])->get();
        $types = CarType::all();
        $fuelTypes = FuelTypes::where('status', 1)->get();
        $transmissions = Transmissions::all();
        $categories = Category::ofStatus(1)->ofType('main')->get();

        $data = [
            'brands' => $brands,
            'types' => $types,
            'fuel_types' => $fuelTypes,
            'transmissions' => $transmissions,
            'categories' => $categories,
            'chauffeur_tiers' => [
                ['id' => 'business_class', 'name' => 'Business Class (e.g. Mercedes E-Class, BMW 5 Series)'],
                ['id' => 'first_class', 'name' => 'First Class / Luxury (e.g. Mercedes S-Class, BMW 7 Series)'],
                ['id' => 'luxury_mpv', 'name' => 'Luxury MPV / Group (e.g. Mercedes V-Class)'],
                ['id' => 'wedding', 'name' => 'Wedding & VIP Classic (e.g. Rolls Royce, Bentley)'],
            ],
            'amenities_list' => [
                ['id' => 'wifi', 'name' => 'Free High-Speed Wi-Fi'],
                ['id' => 'water', 'name' => 'Complimentary Bottled Water'],
                ['id' => 'chargers', 'name' => 'Phone Charging Cables (iPhone / Type-C)'],
                ['id' => 'meet_and_greet', 'name' => 'Meet & Greet (Airport Arrivals)'],
                ['id' => 'child_seat', 'name' => 'Child / Baby Seat Available'],
                ['id' => 'suited_chauffeur', 'name' => 'Professional Suited Chauffeur'],
            ],
            'mileage_policy_options' => ['Unlimited', '100 miles/day', '150 miles/day', '200 miles/day', '250 miles/day'],
            'fuel_policy_options' => ['Full to Full', 'Same to Same'],
            'available_for_options' => ['Both', 'Pickup', 'Delivery'],
        ];

        return response()->json(response_formatter(DEFAULT_200, $data), 200);
    }

    /**
     * Get models for specific brand
     */
    public function getModelsByBrand($brandId): JsonResponse
    {
        $models = CarModels::where('brand_id', $brandId)->where('status', 1)->get(['id', 'name']);
        return response()->json(response_formatter(DEFAULT_200, $models), 200);
    }

    /**
     * Create Car (Car Hire or Chauffeur)
     */
    public function store(Request $request): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $serviceCategory = $request->get('service_category', 'car_hire');

        $rules = [
            'service_category' => 'required|in:car_hire,chauffeur',
            'category_id' => 'required',
            'car_type_id' => 'required',
            'brand' => 'required|string|max:191',
            'model' => 'nullable|string|max:191',
            'registration_number' => 'nullable|string|max:191',
            'manufacture_year' => 'nullable|string',
            'fuel_type' => 'nullable|string',
            'transmission_type' => 'nullable|string',
            'seating_capacity' => 'nullable|numeric|min:1',
            'security_deposit' => 'nullable|numeric|min:0',
            'postcode' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ];

        if ($serviceCategory === 'car_hire') {
            $rules['daily_rate'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['mileage_limit'] = 'nullable|string';
            $rules['extra_mileage_charge'] = 'nullable|numeric|min:0';
            $rules['fuel_policy'] = 'nullable|string';
            $rules['delivery_fee'] = 'nullable|numeric|min:0';
            $rules['min_driver_age'] = 'nullable|numeric|min:18';
            $rules['available_for'] = 'nullable|string';
        } else {
            // Chauffeur
            $rules['service_type'] = 'nullable|in:hourly,full_day,both';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['daily_rate'] = 'nullable|numeric|min:0';
            $rules['min_booking_hours'] = 'nullable|numeric|min:1';
            $rules['luggage_capacity'] = 'nullable|numeric|min:0';
            $rules['chauffeur_tier'] = 'nullable|string';
            $rules['preferred_areas'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $car = new Car();
        $car->provider_id = $provider->id;
        $car->category_id = $request->category_id;
        $car->car_type_id = $request->car_type_id;
        $car->service_category = $serviceCategory;
        $car->brand = $request->brand;
        $car->setAttribute('model', $request->model);
        $car->year = $request->manufacture_year ?? $request->year;
        $car->manufacture_year = $request->manufacture_year ?? $request->year;
        $car->registration_number = $request->registration_number;
        $car->fuel_type = $request->fuel_type ?? 'Petrol';
        $car->transmission_type = $request->transmission_type ?? 'Automatic';
        $car->transmission = $request->transmission_type ?? 'Automatic';
        $car->seating_capacity = $request->seating_capacity ?? 5;
        $car->air_conditioning = $request->air_conditioning ? 1 : 0;
        $car->status = 1;

        if ($serviceCategory === 'car_hire') {
            $car->daily_rate = floatval($request->daily_rate);
            $car->hourly_rate = floatval($request->hourly_rate ?? 0);
            $car->pricing_type = $car->hourly_rate > 0 ? 'both' : 'daily';
            $car->security_deposit = floatval($request->security_deposit ?? 0);
            $car->mileage_limit = $request->mileage_limit ?? 'Unlimited';
            $car->extra_mileage_charge = floatval($request->extra_mileage_charge ?? 0);
            $car->fuel_policy = $request->fuel_policy ?? 'Full to Full';
            $car->delivery_fee = floatval($request->delivery_fee ?? 0);
            $car->min_driver_age = intval($request->min_driver_age ?? 21);
            $car->available_for = $request->available_for ?? 'Both';
        } else {
            // Chauffeur
            $car->service_type = $request->service_type ?? 'hourly';
            $car->hourly_rate = floatval($request->hourly_rate ?? 0);
            $car->daily_rate = floatval($request->daily_rate ?? 0);
            $car->pricing_type = ($car->service_type === 'hourly') ? 'hourly' : (($car->service_type === 'full_day') ? 'daily' : 'both');
            $car->min_booking_hours = intval($request->min_booking_hours ?? 1);
            $car->luggage_capacity = intval($request->luggage_capacity ?? 2);
            $car->chauffeur_tier = $request->chauffeur_tier ?? 'business_class';
            $car->preferred_areas = $request->preferred_areas;
            if ($request->has('amenities')) {
                $car->amenities = is_array($request->amenities) ? $request->amenities : json_decode($request->amenities, true);
            }
        }

        $car->postcode = $request->postcode;
        $car->address = $request->address;
        $car->available_hours_start = $request->available_hours_start;
        $car->available_hours_end = $request->available_hours_end;
        $car->terms_conditions = $request->terms_conditions;
        $car->description = $request->description;

        // Features
        if ($request->has('features')) {
            $car->features = is_array($request->features) ? $request->features : json_decode($request->features, true);
        }

        // Images upload
        $images = [];
        if ($request->has('car_images')) {
            $views = ['front_view', 'rear_view', 'interior', 'dashboard'];
            foreach ($views as $view) {
                if ($request->hasFile("car_images.$view")) {
                    $images[] = file_uploader('car/', 'png', $request->file("car_images.$view"));
                }
            }
        } elseif ($request->has('images')) {
            foreach ($request->images as $img) {
                if ($request->hasFile('images')) {
                    $images[] = file_uploader('car/', 'png', $img);
                }
            }
        }
        if (!empty($images)) {
            $car->images = array_values(array_filter($images));
        }

        // Documents
        if ($request->hasFile('driving_license')) {
            $car->driving_license = file_uploader('car/documents/', 'png', $request->file('driving_license'));
        }
        if ($request->hasFile('vehicle_registration')) {
            $car->vehicle_registration = file_uploader('car/documents/', 'png', $request->file('vehicle_registration'));
        }
        if ($request->hasFile('insurance_documents')) {
            $car->insurance_documents = file_uploader('car/documents/', 'png', $request->file('insurance_documents'));
        }
        if ($request->hasFile('mot_certificate')) {
            $car->mot_certificate = file_uploader('car/documents/', 'png', $request->file('mot_certificate'));
        }

        $car->save();
        $car->load(['type', 'category']);
        $car->image_full_paths = $this->getImageFullPaths($car->images);

        return response()->json(response_formatter(DEFAULT_STORE_200, $car), 200);
    }

    /**
     * Show single car details
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $car = $this->car->where('provider_id', $provider->id)->with(['type', 'category'])->find($id);
        if (!$car) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $car->image_full_paths = $this->getImageFullPaths($car->images);
        $car->driving_license_full_path = $car->driving_license ? asset('storage/app/public/car/documents/' . $car->driving_license) : null;
        $car->vehicle_registration_full_path = $car->vehicle_registration ? asset('storage/app/public/car/documents/' . $car->vehicle_registration) : null;
        $car->insurance_documents_full_path = $car->insurance_documents ? asset('storage/app/public/car/documents/' . $car->insurance_documents) : null;
        $car->mot_certificate_full_path = $car->mot_certificate ? asset('storage/app/public/car/documents/' . $car->mot_certificate) : null;

        return response()->json(response_formatter(DEFAULT_200, $car), 200);
    }

    /**
     * Update car details
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        /** @var Car|null $car */
        $car = $this->car->where('provider_id', $provider->id)->find($id);
        if (!$car) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $serviceCategory = $request->get('service_category', $car->service_category ?? 'car_hire');

        $rules = [
            'service_category' => 'required|in:car_hire,chauffeur',
            'category_id' => 'required',
            'car_type_id' => 'required',
            'brand' => 'required|string|max:191',
            'model' => 'nullable|string|max:191',
            'registration_number' => 'nullable|string|max:191',
            'manufacture_year' => 'nullable|string',
            'fuel_type' => 'nullable|string',
            'transmission_type' => 'nullable|string',
            'seating_capacity' => 'nullable|numeric|min:1',
            'security_deposit' => 'nullable|numeric|min:0',
            'postcode' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ];

        if ($serviceCategory === 'car_hire') {
            $rules['daily_rate'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['mileage_limit'] = 'nullable|string';
            $rules['extra_mileage_charge'] = 'nullable|numeric|min:0';
            $rules['fuel_policy'] = 'nullable|string';
            $rules['delivery_fee'] = 'nullable|numeric|min:0';
            $rules['min_driver_age'] = 'nullable|numeric|min:18';
            $rules['available_for'] = 'nullable|string';
        } else {
            // Chauffeur
            $rules['service_type'] = 'nullable|in:hourly,full_day,both';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['daily_rate'] = 'nullable|numeric|min:0';
            $rules['min_booking_hours'] = 'nullable|numeric|min:1';
            $rules['luggage_capacity'] = 'nullable|numeric|min:0';
            $rules['chauffeur_tier'] = 'nullable|string';
            $rules['preferred_areas'] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(response_formatter(DEFAULT_400, null, error_processor($validator)), 400);
        }

        $car->category_id = $request->category_id;
        $car->car_type_id = $request->car_type_id;
        $car->service_category = $serviceCategory;
        $car->brand = $request->brand;
        $car->setAttribute('model', $request->model);
        $car->year = $request->manufacture_year ?? $request->year ?? $car->year;
        $car->manufacture_year = $request->manufacture_year ?? $request->year ?? $car->manufacture_year;
        $car->registration_number = $request->registration_number;
        $car->fuel_type = $request->fuel_type ?? $car->fuel_type;
        $car->transmission_type = $request->transmission_type ?? $car->transmission_type;
        $car->transmission = $request->transmission_type ?? $car->transmission;
        $car->seating_capacity = $request->seating_capacity ?? $car->seating_capacity;
        $car->air_conditioning = $request->air_conditioning ? 1 : 0;

        if ($serviceCategory === 'car_hire') {
            $car->daily_rate = floatval($request->daily_rate);
            $car->hourly_rate = floatval($request->hourly_rate ?? 0);
            $car->pricing_type = $car->hourly_rate > 0 ? 'both' : 'daily';
            $car->security_deposit = floatval($request->security_deposit ?? 0);
            $car->mileage_limit = $request->mileage_limit ?? 'Unlimited';
            $car->extra_mileage_charge = floatval($request->extra_mileage_charge ?? 0);
            $car->fuel_policy = $request->fuel_policy ?? 'Full to Full';
            $car->delivery_fee = floatval($request->delivery_fee ?? 0);
            $car->min_driver_age = intval($request->min_driver_age ?? 21);
            $car->available_for = $request->available_for ?? 'Both';
        } else {
            // Chauffeur
            $car->service_type = $request->service_type ?? 'hourly';
            $car->hourly_rate = floatval($request->hourly_rate ?? 0);
            $car->daily_rate = floatval($request->daily_rate ?? 0);
            $car->pricing_type = ($car->service_type === 'hourly') ? 'hourly' : (($car->service_type === 'full_day') ? 'daily' : 'both');
            $car->min_booking_hours = intval($request->min_booking_hours ?? 1);
            $car->luggage_capacity = intval($request->luggage_capacity ?? 2);
            $car->chauffeur_tier = $request->chauffeur_tier ?? 'business_class';
            $car->preferred_areas = $request->preferred_areas;
            if ($request->has('amenities')) {
                $car->amenities = is_array($request->amenities) ? $request->amenities : json_decode($request->amenities, true);
            }
        }

        $car->postcode = $request->postcode;
        $car->address = $request->address;
        $car->available_hours_start = $request->available_hours_start;
        $car->available_hours_end = $request->available_hours_end;
        $car->terms_conditions = $request->terms_conditions;
        $car->description = $request->description;

        // Features
        if ($request->has('features')) {
            $car->features = is_array($request->features) ? $request->features : json_decode($request->features, true);
        }

        // Images upload
        if ($request->has('car_images')) {
            $images = $car->images ?? [null, null, null, null];
            $views = ['front_view', 'rear_view', 'interior', 'dashboard'];
            foreach ($views as $index => $view) {
                if ($request->hasFile("car_images.$view")) {
                    $images[$index] = file_uploader('car/', 'png', $request->file("car_images.$view"));
                }
            }
            $car->images = array_values(array_filter($images));
        } elseif ($request->has('images')) {
            $images = $car->images ?? [];
            foreach ($request->images as $img) {
                if ($request->hasFile('images')) {
                    $images[] = file_uploader('car/', 'png', $img);
                }
            }
            $car->images = $images;
        }

        // Documents
        if ($request->hasFile('driving_license')) {
            $car->driving_license = file_uploader('car/documents/', 'png', $request->file('driving_license'), $car->driving_license);
        }
        if ($request->hasFile('vehicle_registration')) {
            $car->vehicle_registration = file_uploader('car/documents/', 'png', $request->file('vehicle_registration'), $car->vehicle_registration);
        }
        if ($request->hasFile('insurance_documents')) {
            $car->insurance_documents = file_uploader('car/documents/', 'png', $request->file('insurance_documents'), $car->insurance_documents);
        }
        if ($request->hasFile('mot_certificate')) {
            $car->mot_certificate = file_uploader('car/documents/', 'png', $request->file('mot_certificate'), $car->mot_certificate);
        }

        $car->save();
        $car->load(['type', 'category']);
        $car->image_full_paths = $this->getImageFullPaths($car->images);

        return response()->json(response_formatter(DEFAULT_UPDATE_200, $car), 200);
    }

    /**
     * Delete car
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $car = $this->car->where('provider_id', $provider->id)->find($id);
        if (!$car) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $car->delete();
        return response()->json(response_formatter(DEFAULT_DELETE_200), 200);
    }

    /**
     * Toggle car active status
     */
    public function toggleStatus(Request $request, ?string $id = null): JsonResponse
    {
        $id = $id ?? $request->id;
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(response_formatter(DEFAULT_403), 403);
        }

        $car = $this->car->where('provider_id', $provider->id)->find($id);
        if (!$car) {
            return response()->json(response_formatter(DEFAULT_404), 404);
        }

        $car->status = $car->status == 1 ? 0 : 1;
        $car->save();

        return response()->json(response_formatter(DEFAULT_STATUS_200, $car), 200);
    }

    private function getImageFullPaths($images): array
    {
        $fullPaths = [];
        if (is_array($images)) {
            foreach ($images as $image) {
                if ($image) {
                    $fullPaths[] = asset('storage/app/public/car/' . $image);
                }
            }
        }
        return $fullPaths;
    }
}
