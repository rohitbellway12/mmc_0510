<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CarHire\Entities\Features;

class CarFeatureController extends Controller
{
    public function index()
    {
        $features = Features::orderBy('id','DESC')->get();
        return view('carhire::admin.car_features.index', compact('features'));
    }

    public function create()
    {
        // dd('test');
        return view('carhire::admin.car_features.create');
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'name' => 'required|unique:features,name'
        ]);

        Features::create([
            'name' => $request->name,
            // 'status' => $request->status ?? 1
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car_features.index')
            ->with('success','Feature Added');
    }

    public function edit($id)
    {
        $feature = Features::findOrFail($id);
        return view('carhire::admin.car_features.edit', compact('feature'));
    }

    public function update(Request $request, $id)
    {
        //dd($request->all());
        $request->validate([
            'name' => 'required|unique:features,name,'.$id
        ]);

        Features::findOrFail($id)->update([
            'name' => $request->name,
            // 'status' => $request->status
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.car_features.index')
            ->with('success','Feature Updated');
    }

    public function destroy($id)
    {
        Features::findOrFail($id)->delete();
        return back()->with('success','Feature Deleted');
    }

    // Toggle Status
    public function toggleStatus($id)
    {
        $feature = Features::findOrFail($id);
        $feature->status = !$feature->status;
        $feature->save();

        return back()->with('success','Status Updated');
    }

    // Active Features (For Car Create/Edit Dropdown)
    public function activeFeatures()
    {
        return Features::where('status',1)->get();
    }

}

