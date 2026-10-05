<?php

namespace Modules\CarHire\Http\Controllers\Web\Provider;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Brian2694\Toastr\Facades\Toastr;
use Modules\CarHire\Entities\Car;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;
use Modules\CarHire\Entities\CarYears;
use Modules\CarHire\Entities\Features;
use Modules\CarHire\Entities\FuelTypes;
use Modules\CarHire\Entities\Transmissions;
use Modules\CategoryManagement\Entities\Category;

class ProviderCarController extends Controller
{
    private $car;

    public function __construct(Car $car)
    {
        $this->car = $car;
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        Log::info('ProviderCarController@index started');
        $search = $request->has('search') ? $request['search'] : '';
        $category = $request->has('category') ? $request['category'] : 'all';
        $providerId = $request->user()->provider->id;
        Log::info('Provider ID: ' . $providerId);

        try {
            Log::info('Querying cars started');
            $cars = $this->car
                ->where('provider_id', $providerId)
                ->with(['category', 'type'])
                ->when($request->has('search'), function ($query) use ($search) {
                    $query->where('brand', 'like', "%{$search}%");
                })
                ->when($category != 'all', function ($query) use ($category) {
                    $query->where('service_category', $category);
                })
                ->latest()
                ->paginate(pagination_limit())->withQueryString();
            Log::info('Querying cars finished');

            return view('carhire::provider.index', compact('cars', 'search', 'category'));
        } catch (\Exception $e) {
            Log::error('Error in ProviderCarController@index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            Toastr::error(translate('Something went wrong!'));
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $categories = Category::ofStatus(1)->ofType('main')->get();
        $types = CarType::all();
        return view('carhire::provider.create', compact('categories', 'types'));
    }

    public function createCarHire()
    {
        $categories = Category::ofStatus(1)->ofType('main')->get();
        $types = CarType::all();
        $brands = CarBrands::where('status', 1)->get();
        $fuelTypes = FuelTypes::where('status', 1)->get();
        $transmissions = Transmissions::all();
        return view('carhire::provider.car-hire-create', compact('categories', 'types', 'brands', 'fuelTypes', 'transmissions'));
    }

    public function createChauffeur()
    {
        $categories = Category::ofStatus(1)->ofType('main')->get();
        $types = CarType::all();
        $brands = CarBrands::where('status', 1)->get();
        $fuelTypes = FuelTypes::where('status', 1)->get();
        $transmissions = Transmissions::all();
        return view('carhire::provider.chauffeur-create', compact('categories', 'types', 'brands', 'fuelTypes', 'transmissions'));
    }

    public function store(Request $request)
    {
        $serviceCategory = $request->get('service_category', 'car_hire');

        $rules = [
            'service_category' => 'required|in:car_hire,chauffeur',
            'category_id' => 'required',
            'car_type_id' => 'required',
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'manufacture_year' => 'nullable|string',
            'fuel_type' => 'nullable|string',
            'seating_capacity' => 'nullable|numeric|min:1',
            'transmission_type' => 'nullable|string',
            'security_deposit' => 'nullable|numeric|min:0',
            'postcode' => 'nullable|string',
            'address' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ];

        if ($serviceCategory == 'car_hire') {
            $rules['daily_rate'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['mileage_limit'] = 'nullable|string';
            $rules['extra_mileage_charge'] = 'nullable|numeric|min:0';
            $rules['fuel_policy'] = 'nullable|string';
            $rules['delivery_fee'] = 'nullable|numeric|min:0';
            $rules['min_driver_age'] = 'nullable|numeric|min:18';
            $rules['available_for'] = 'nullable|string';
        } else {
            // chauffeur
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
            return back()->withErrors($validator)->withInput();
        }

        $providerId = $request->user()->provider->id;

        $car = new Car();
        $car->provider_id = $providerId;
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

        if ($serviceCategory == 'car_hire') {
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
            // chauffeur
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
            $features = is_array($request->features) ? $request->features : json_decode($request->features, true);
            $car->features = $features;
        }

        // Images
        if ($request->has('car_images')) {
            $images = [];
            $views = ['front_view', 'rear_view', 'interior', 'dashboard'];
            foreach ($views as $view) {
                if ($request->hasFile("car_images.$view")) {
                    $images[] = file_uploader('car/', 'png', $request->file("car_images.$view"));
                }
            }
            if (!empty(array_filter($images))) {
                $car->images = array_values(array_filter($images));
            }
        } elseif ($request->has('images')) {
            $images = [];
            foreach ($request->images as $image) {
                if ($image) {
                    $images[] = file_uploader('car/', 'png', $image);
                }
            }
            if (!empty($images)) {
                $car->images = $images;
            }
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

        Toastr::success(translate('Car added successfully'), translate('Success'));
        return redirect()->route('provider.car.index');
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('carhire::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $providerId = auth()->user()->provider->id;
        $car = $this->car->where('id', $id)->where('provider_id', $providerId)->firstOrFail();

        $categories = Category::ofStatus(1)->ofType('main')->get();
        $types = CarType::all();
        $brands = CarBrands::where('status', 1)->get();
        $fuelTypes = FuelTypes::where('status', 1)->get();
        $transmissions = Transmissions::all();

        $selectedBrand = $brands->firstWhere('name', $car->brand);
        $models = $selectedBrand ? CarModels::where('brand_id', $selectedBrand->id)->where('status', 1)->get() : collect();

        if ($car->service_category == 'car_hire') {
            return view('carhire::provider.edit-car-hire', compact('car', 'categories', 'types', 'brands', 'fuelTypes', 'transmissions', 'models'));
        }
        return view('carhire::provider.edit-chauffeur', compact('car', 'categories', 'types', 'brands', 'fuelTypes', 'transmissions', 'models'));
    }

    public function update(Request $request, $id)
    {
        $providerId = $request->user()->provider->id;
        /** @var Car $car */
        $car = $this->car->where('id', $id)->where('provider_id', $providerId)->firstOrFail();
        $serviceCategory = $request->get('service_category', $car->service_category ?? 'car_hire');

        $rules = [
            'service_category' => 'required|in:car_hire,chauffeur',
            'category_id' => 'required',
            'car_type_id' => 'required',
            'brand' => 'required|string',
            'model' => 'nullable|string',
            'registration_number' => 'nullable|string',
            'manufacture_year' => 'nullable|string',
            'fuel_type' => 'nullable|string',
            'seating_capacity' => 'nullable|numeric|min:1',
            'transmission_type' => 'nullable|string',
            'security_deposit' => 'nullable|numeric|min:0',
            'postcode' => 'nullable|string',
            'address' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ];

        if ($serviceCategory == 'car_hire') {
            $rules['daily_rate'] = 'required|numeric|min:0';
            $rules['hourly_rate'] = 'nullable|numeric|min:0';
            $rules['mileage_limit'] = 'nullable|string';
            $rules['extra_mileage_charge'] = 'nullable|numeric|min:0';
            $rules['fuel_policy'] = 'nullable|string';
            $rules['delivery_fee'] = 'nullable|numeric|min:0';
            $rules['min_driver_age'] = 'nullable|numeric|min:18';
            $rules['available_for'] = 'nullable|string';
        } else {
            // chauffeur
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
            return back()->withErrors($validator)->withInput();
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

        if ($serviceCategory == 'car_hire') {
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
            // chauffeur
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
            $features = is_array($request->features) ? $request->features : json_decode($request->features, true);
            $car->features = $features;
        }

        // Images
        if ($request->has('car_images') || $request->has('existing_car_images')) {
            $images = [];
            $views = ['front_view', 'rear_view', 'interior', 'dashboard'];
            $existingViews = $request->input('existing_car_images', []);
            $oldImages = is_array($car->images) ? $car->images : [];

            foreach ($views as $index => $view) {
                if ($request->hasFile("car_images.$view")) {
                    $images[$index] = file_uploader('car/', 'png', $request->file("car_images.$view"));
                    if (!empty($oldImages[$index]) && $oldImages[$index] !== $images[$index]) {
                        file_remover('car/', $oldImages[$index]);
                    }
                } elseif (!empty($existingViews[$view])) {
                    $images[$index] = $existingViews[$view];
                } else {
                    $images[$index] = null;
                    if (!empty($oldImages[$index])) {
                        file_remover('car/', $oldImages[$index]);
                    }
                }
            }
            $car->images = array_values(array_filter($images));
        } else {
            // Check for existing_images retention
            $currentImages = is_array($car->images) ? $car->images : [];

            if ($request->has('existing_images') || $request->has('images_edited')) {
                $keptImages = $request->input('existing_images', []);
                if (!is_array($keptImages)) {
                    $keptImages = [];
                }
                // Remove deleted images from storage
                foreach ($currentImages as $oldImg) {
                    if (!in_array($oldImg, $keptImages)) {
                        file_remover('car/', $oldImg);
                    }
                }
                $finalImages = array_values(array_filter($keptImages));
            } else {
                $finalImages = $currentImages;
            }

            // Upload any new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    if ($image) {
                        $finalImages[] = file_uploader('car/', 'png', $image);
                    }
                }
            }

            $car->images = array_values(array_filter($finalImages));
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

        Toastr::success(translate('Car updated successfully'), translate('Success'));
        return redirect()->route('provider.car.index');
    }

    /**
     * Delete a single image from car gallery via AJAX
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $providerId = $request->user()->provider->id;
        $car = $this->car->where('id', $id)->where('provider_id', $providerId)->first();

        if (!$car) {
            return response()->json(['success' => false, 'message' => translate('Car not found')], 404);
        }

        $imageName = $request->input('image_name');
        $images = is_array($car->images) ? $car->images : [];

        if (($key = array_search($imageName, $images)) !== false) {
            file_remover('car/', $imageName);
            unset($images[$key]);
            $car->images = array_values($images);
            $car->save();

            return response()->json([
                'success' => true,
                'message' => translate('Image deleted successfully')
            ]);
        }

        return response()->json(['success' => false, 'message' => translate('Image not found in gallery')], 404);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy(Request $request, $id)
    {
        $providerId = $request->user()->provider->id;
        $car = $this->car->where('id', $id)->where('provider_id', $providerId)->firstOrFail();
        $car->delete();

        Toastr::success(translate('Car deleted successfully'));
        return back();
    }
}
