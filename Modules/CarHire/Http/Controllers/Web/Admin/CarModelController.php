<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;

class CarModelController extends Controller
{
    public function index()
    {
        //dd('jhsd');
        $models = CarModels::with('brand.carType')->latest()->paginate(10);
        //dd($models);
        return view('carhire::admin.car_models.index', compact('models'));
    }

    public function create()
    {
        $car_types = CarType::where('status',1)->get(); // for cascading dropdown
        //dd($car_types);
        return view('carhire::admin.car_models.create', compact('car_types'));
    }

    public function store(Request $request)
    {
       // dd($request->all());
        $request->validate([
            'brand_id' => 'required|exists:car_brands,id',
            'name' => 'required|string|max:191',
        ]);

        CarModels::create([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time()),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car-models.index')->with('success', 'Model Created');
    }

    public function edit($id)
    {
        $model = CarModels::findOrFail($id);
        $car_types = CarType::where('status',1)->get();
        return view('carhire::admin.car_models.edit', compact('model', 'car_types'));
    }

    public function update(Request $request, $id)
    {
        $model = CarModels::findOrFail($id);

        $request->validate([
            'brand_id' => 'required|exists:car_brands,id',
            'name' => 'required|string|max:191',
        ]);

        $model->update([
            'brand_id' => $request->brand_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name . '-' . time()),
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car-models.index')->with('success', 'Model Updated');
    }

    public function destroy($id)
    {
        CarModels::destroy($id);
        return back()->with('success', 'Model Deleted');
    }

    public function toggleStatus($id)
    {
        $model = CarModels::findOrFail($id);
        $model->status = $model->status ? 0 : 1;
        $model->save();

        return back();
    }
}
