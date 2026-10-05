<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;

class CarBrandController extends Controller
{
    public function index()
    {
        // dd('sbjd');
        $brands = CarBrands::with('carType')->latest()->paginate(15);
        //dd($brands);
        return view('carhire::admin.car_brands.index', compact('brands'));
    }

    public function create()
    {
        $car_types = CarType::where('status',1)->get();
        return view('carhire::admin.car_brands.create', compact('car_types'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'car_type_id' => 'required|exists:car_types,id',
            'name' => 'required|string|max:191'
        ]);

        CarBrands::create([
            'car_type_id' => $request->car_type_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time()),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car-brands.index')->with('success', 'Brand created.');
    }

    public function edit($id)
    {
        $brand = CarBrands::findOrFail($id);
        $car_types = CarType::where('status',1)->get();
        return view('carhire::admin.car_brands.edit', compact('brand', 'car_types'));
    }

    public function update(Request $request, $id)
    {
        $brand = CarBrands::findOrFail($id);

        $request->validate([
            'car_type_id' => 'required|exists:car_types,id',
            'name' => 'required|string|max:191'
        ]);

        $brand->update([
            'car_type_id' => $request->car_type_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time()),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car-brands.index')->with('success', 'Brand updated.');
    }

    public function destroy($id)
    {
        CarBrands::destroy($id);
        return back()->with('success', 'Brand deleted.');
    }

    public function toggleStatus($id)
    {
        $brand = CarBrands::findOrFail($id);
        $brand->status = $brand->status ? 0 : 1;
        $brand->save();

        return back();
    }



 public function getModelsByBrand($brandId)
    {
        $models = CarModels::where('brand_id', $brandId)
            ->where('status',1)
            ->select('id','name')
            ->get();

        return response()->json($models);
    }



}
