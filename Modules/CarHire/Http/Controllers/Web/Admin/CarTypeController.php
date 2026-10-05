<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CarHire\Entities\CarType;
use Illuminate\Support\Str;

class CarTypeController extends Controller
{
    public function index()
    {
        $types = CarType::latest()->paginate(10);
        //dd($types);
        return view('carhire::admin.car_types.index', compact('types'));
    }

    public function create()
    {
        //dd('djkfh');
        return view('carhire::admin.car_types.create');
    }

    public function store(Request $request)
    {
        //dd('store');
        $request->validate([
            'name' => 'required|unique:car_types,name'
        ]);
        //dd($request->all());
        CarType::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'status' => 1
        ]);

        return redirect()->route('admin.car-types.index')->with('success','Car Type Added');
    }

    public function edit($id)
    {
        $type = CarType::findOrFail($id);
        return view('carhire::admin.car_types.edit', compact('type'));
    }

    public function update(Request $request, $id)
    {
        $type = CarType::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:car_types,name,'.$id
        ]);

        $type->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name)
        ]);

        return redirect()->route('admin.car-types.index')->with('success','Car Type Updated');
    }

    public function destroy($id)
    {
        CarType::destroy($id);
        return back()->with('success','Deleted Successfully');
    }

    public function toggleStatus($id)
    {
        $type = CarType::findOrFail($id);
        $type->status = $type->status == 1 ? 0 : 1;
        $type->save();

        return back();
    }
}
