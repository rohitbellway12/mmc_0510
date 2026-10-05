<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CarHire\Entities\CarBrands;
use Modules\CarHire\Entities\CarYears;
use Modules\CarHire\Entities\CarModels;
use Modules\CarHire\Entities\CarType;

class CarYearController extends Controller
{
    public function index()
    {
        // dd('method first');
        $years = CarYears::with('model.brand')->orderBy('id', 'desc')->paginate(10);
        //  dd($years);
        return view('carhire::admin.car_years.index', compact('years'));
    }

    public function create()
    {
        // dd('method second');
        $types = CarType::where('status',1)->get();
        // dd($types);
        return view('carhire::admin.car_years.create', compact('types'));
    }

    public function store(Request $request)
    {
        //dd($request->all(), $request->has('status') ?1:0);
        $request->validate([
            'model_id' => 'required',
            'year'     => 'required|digits:4'
        ]);

        CarYears::create([
            'model_id' => $request->model_id,
            'year'     => $request->year,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car-years.index')
                         ->with('success', 'Car Model Year added successfully!');
    }

    // public function edit($id)
    // {
    //     // dd('edit method');
    //     $year = CarYears::findOrFail($id);
    //     $models = CarModels::with('brand')->where('status', 1)->get();
    // // dd('edit method1');

    //     return view('carhire::admin.car_years.edit', compact('year','models'));
    // }

    // public function edit($id)
    // {
    //     $year = CarYears::findOrFail($id);

    //     // YE MISSING THA!!!
    //     $types = CarType::where('status', 1)->get();

    //     return view('carhire::admin.car_years.edit', compact('year', 'types'));
    // }

   public function edit($id)
    {
        // Load year with model, brand, and carType relationships
        $year = CarYears::with('model.brand.carType')->findOrFail($id);

        // LOAD ALL TYPES
        $types = CarType::where('status', 1)->get();

        // Get car_type_id and brand_id from relationships
        $carTypeId = $year->model->brand->car_type_id ?? null;
        $brandId = $year->model->brand_id ?? null;

        // LOAD BRANDS OF SELECTED TYPE
        $brands = CarBrands::where('car_type_id', $carTypeId)
                        ->where('status', 1)
                        ->get();

        // LOAD MODELS OF SELECTED BRAND
        $models = CarModels::where('brand_id', $brandId)
                        ->where('status', 1)
                        ->get();

        return view('carhire::admin.car_years.edit', compact('year','types','brands','models'));
    }




public function update(Request $request, $id)
    {
        $year = CarYears::findOrFail($id);
        //dd($request->all() ,$year->all());
        $request->validate([
            'model_id' => 'required',
            'year'     => 'required|digits:4'
        ]);

        $year->update([
            'model_id' => $request->model_id,
            'year'     => $request->year,
            'status' => $request->has('status') ? 1 : 0,
        ]);
        // dd($year);
        return redirect()->route('admin.car-years.index')
                         ->with('success', 'Car Model Year updated!');
    }

public function destroy($id)
    {
        // dd($id);
        CarYears::where('id', $id)->delete();

        return back()->with('success', 'Deleted!');
    }

    public function status($id)
    {
        $year = CarYears::findOrFail($id);
        $year->status = !$year->status;
        $year->save();

        return back()->with('success', 'Status Updated!');
    }
}
