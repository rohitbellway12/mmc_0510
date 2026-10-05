<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\CarHire\Entities\Car;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;
use Modules\CarHire\Entities\Features;
use Modules\CarHire\Entities\FuelTypes;
use Modules\CarHire\Entities\Transmissions;
use Modules\CategoryManagement\Entities\Category;

class CarHireController extends Controller
{

//    public function index()
//     {
//         $cars = Car::with('type', 'category')->latest()->paginate(20);
//         //dd($cars);
//         return view('carhire::admin.carhire.index', compact('cars'));
//     }

public function index()
{
    $cars = Car::with(['type', 'category', 'brand', 'model', 'year','feature','fuel_type','transmission'])->latest()->paginate(20);
   //dd($cars);
   // dd(Car::with(['brand', 'model'])->first());

    return view('carhire::admin.carhire.index', compact('cars'));
}

    public function create()
    {
        // dd('jhb');
        $car_types = CarType::where('status', 1)->get();
        $category = Category::where('name', 'Car Hire')->first();
        // $brands  = CarBrands::where('status',1)->get();
         //dd($category,$car_types);
         $features = Features::where('status', 1)->get();
         $fuel_types = FuelTypes::where('status', 1)->get();
         $transmissions = Transmissions::where('status', 1)->get();
         return view('carhire::admin.carhire.create', compact('car_types', 'category','features','fuel_types','transmissions'));
    }

    // public function store(Request $request)
    // {
    //     //dd($request->all());

    //     $request->validate([
    //         'car_type_id' => 'required|exists:car_types,id',
    //         'brand_id' => 'required|exists:car_brands,id',
    //         'model_id' => 'required|exists:car_models,id',
    //         'year' => 'required|digits:4|integer',
    //         'fuel_type' => 'required|string',
    //         'transmission' => 'required|string',
    //         'seating_capacity' => 'required|integer',
    //         'daily_rent' => 'required|numeric',
    //         'images.*' => 'nullable|image|mimes:jpeg,png,jpg',
    //         'latitude' => 'required',
    //         'longitude' => 'required',
    //     ]);

    //     $category = Category::where('name', 'Car Hire')->first();

    //     file_put_contents(
    //     storage_path('logs/carhire.log'),
    //     "[" . now() . "] STORE REQUEST: " . json_encode($request->all()) . PHP_EOL,
    //     FILE_APPEND
    // );
    //     $car = Car::create([
    //         'category_id' => $category->id,
    //         'car_type_id' => $request->car_type_id,
    //         'brand_id' => $request->brand_id,
    //         'model_id' => $request->model_id,
    //         'year' => $request->year,
    //         'fuel_type' => $request->fuel_type,
    //         'transmission' => $request->transmission,
    //         'seating_capacity' => $request->seating_capacity,
    //         'daily_rent' => $request->daily_rent,
    //         'description' => $request->description,
    //         'images' => $request->hasFile('images') ? array_map(function($img) {
    //             return $img->store('car_images', 'public');
    //         }, $request->file('images')) : [],
    //         'coordinates' => ['latitude' => $request->latitude, 'longitude' => $request->longitude],
    //     ]);

    //     return redirect()->route('admin.carhire.index')->with('success', 'Car added successfully');
    // }

    public function store(Request $request)
    {
        //dd($request->all());
        // Validation
        $request->validate([
            'car_type_id' => 'required|exists:car_types,id',
            'brand_id' => 'required|exists:car_brands,id',
            'model_id' => 'required|exists:car_models,id',
            // 'year' => 'required|digits:4|integer',
            'year_id' => 'required|exists:car_years,id',
            'feature_id' => 'required|exists:features,id',
            // 'fuel_type' => 'required|string',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            // 'transmission' => 'required|string',
            'transmission_id' => 'required|exists:transmissions,id',
            'seating_capacity' => 'required|integer',
            'daily_rent' => 'required|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        file_put_contents(
            storage_path('logs/carhire.log'),
            "[" . now() . "] VALIDATED DATA: " . json_encode($request->all()) . PHP_EOL,
            FILE_APPEND
        );

        try {
            $category = Category::where('name', 'Car Hire')->first();

            $car = Car::create([
                'category_id' => $category->id,
                'car_type_id' => $request->car_type_id,
                'brand_id' => $request->brand_id,
                'model_id' => $request->model_id,
                'year_id' => $request->year_id,
                'feature_id' => $request->feature_id,
                'fuel_type_id' => $request->fuel_type_id,
                // 'fuel_type' => $request->fuel_type,
                // 'transmission' => $request->transmission,
                'transmission_id' => $request->transmission_id,
                'seating_capacity' => $request->seating_capacity,
                'daily_rent' => $request->daily_rent,
                'description' => $request->description,
                'images' => $request->hasFile('images') ? array_map(function($img) {
                    return $img->store('car_images', 'public');
                }, $request->file('images')) : [],
                'coordinates' => ['latitude' => $request->latitude, 'longitude' => $request->longitude],
            ]);

            file_put_contents(
                storage_path('logs/carhire.log'),
                "[" . now() . "] CAR CREATED SUCCESS: " . json_encode($car) . PHP_EOL,
                FILE_APPEND
            );

        } catch (\Exception $ex) {

            file_put_contents(
                storage_path('logs/carhire.log'),
                "[" . now() . "] ERROR: " . $ex->getMessage() . " at line " . $ex->getLine() . PHP_EOL,
                FILE_APPEND
            );

            return back()->with('error', $ex->getMessage());
        }

        return redirect()->route('admin.carhire.index')->with('success', 'Car added successfully');
    }


    public function show($id)
    {
        $car = Car::with(['type', 'category','feature'])->findOrFail($id);
        //dd($car);
        return view('carhire::admin.carhire.show', compact('car'));
    }

    public function edit($id)
    {
        //dd($id);
        $car=Car::find($id);
        $car_types=CarType::where('status',1)->get();

        $category=Category::where('name', 'car hire')->first();
        $features=Features::where('status',1)->get();
        $fuel_types=FuelTypes::where('status',1)->get();
        $transmissions=Transmissions::where('status',1)->get();
        return view('carhire::admin.carhire.edit', compact('car', 'car_types', 'category','features','fuel_types','transmissions'));
    }


   public function update(Request $request, $id)
    {
        //dd($request->all());
        $car = Car::findOrFail($id);
        $request->validate([
            'car_type_id' => 'required|exists:car_types,id',
            'brand_id' => 'required|exists:car_brands,id',
            'model_id' => 'required|exists:car_models,id',
            // 'year' => 'required|digits:4|integer',
             'year_id' => 'required|exists:car_years,id',
            'feature_id' => 'required|exists:features,id',
            // 'fuel_type' => 'required|string',
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'transmission_id' => 'required|exists:transmissions,id',
            'seating_capacity' => 'required|integer',
            'daily_rent' => 'required|numeric',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $car->update([
            'car_type_id' => $request->car_type_id,
            'brand_id' => $request->brand_id,
            'model_id' => $request->model_id,
            // 'year' => $request->year,
            'year_id' => $request->year_id,
            'feature_id' => $request->feature_id,
            // 'fuel_type' => $request->fuel_type,
            'fuel_type_id' => $request->fuel_type_id,
            'transmission_id' => $request->transmission_id,
            'seating_capacity' => $request->seating_capacity,
            'daily_rent' => $request->daily_rent,
            'description' => $request->description,
            'coordinates' => ['latitude' => $request->latitude, 'longitude' => $request->longitude],
        ]);

        // Handle new images - merge with existing ones
        if ($request->hasFile('images')) {
            $existingImages = $car->images ?? [];
            $newImages = array_map(function($img) {
                return $img->store('car_images', 'public');
            }, $request->file('images'));

            $car->images = array_merge($existingImages, $newImages);
            $car->save();
        }

        return redirect()->route('admin.carhire.index')->with('success', 'Car updated successfully');
    }


public function deleteImage($id, $index)
{
    $car = Car::findOrFail($id);

    $images = $car->images;

    if (!isset($images[$index])) {
        return back()->with('error', 'Image not found.');
    }

    // delete file
    Storage::disk('public')->delete($images[$index]);

    // remove from array
    unset($images[$index]);

    // reset keys
    $car->images = array_values($images);

    $car->save();

    return back()->with('success', 'Image removed successfully.');
}

    public function destroy($id)
    {
        $car = Car::findOrFail($id);

        // Delete images from storage
        if (!empty($car->images) && is_array($car->images)) {
            foreach ($car->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $car->delete();

        return redirect()->route('admin.carhire.index')->with('success', 'Car deleted successfully');
    }


    // public function getBrandsByType($typeId)
    // {
    //     $brands = CarBrands::where('car_type_id', $typeId)
    //                 ->where('status', 1)
    //                 ->get();

    //     return response()->json($brands);
    // }

}
