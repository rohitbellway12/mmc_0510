<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CarHire\Entities\CarModel;
use Modules\CarHire\Entities\CarYear;

class YearController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $years = CarYears::with('model.brand.type')->orderBy('id','DESC')->get();
        return view('carhire::years.index', compact('years'));
    }

    public function create()
    {
        // dd('method');
        $models = CarModels::with('brand.type')->where('status',1)->get();
        dd($models);
        return view('carhire::admin.car_years.create', compact('models'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'car_model_id' => 'required',
            'year' => 'required|integer|min:1990|max:'.date('Y'),
        ]);

        CarYears::create($request->all());

        return redirect()->route('admin.carhire.year.index')->with('success','Model year added');
    }

    public function edit($id)
    {
        $year = CarYears::findOrFail($id);
        $models = CarModels::with('brand.type')->where('status',1)->get();

        return view('carhire::years.edit', compact('year','models'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'car_model_id' => 'required',
            'year' => 'required|integer|min:1990|max:'.date('Y'),
        ]);

        $year = CarYears::findOrFail($id);
        $year->update($request->all());

        return redirect()->route('admin.carhire.year.index')->with('success','Model year updated');
    }

    public function destroy($id)
    {
        CarYears::findOrFail($id)->delete();
        return back()->with('success','Deleted successfully');
    }
}
