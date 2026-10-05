<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CarHire\Entities\FuelTypes;

class CarFuleTypeController extends Controller
{
    // LIST
    public function index()
    {
        $fuelTypes = FuelTypes::orderBy('id', 'desc')->get();
        return view('carhire::admin.fuel_types.index', compact('fuelTypes'));
    }

    // CREATE FORM
    public function create()
    {
        //dd('chle gya');
        return view('carhire::admin.fuel_types.create');
    }

    // STORE
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
             'name' => 'required|unique:fuel_types,name'

        ]);

        //dd($request->all());
        FuelTypes::create([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.fuel_types.index')
            ->with('success', 'Fuel Type added successfully');
    }

    // EDIT FORM
    public function edit($id)
    {
        $fuelType = FuelTypes::findOrFail($id);
        return view('carhire::admin.fuel_types.edit', compact('fuelType'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $fuelType = FuelTypes::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $fuelType->update([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.fuel_types.index')
            ->with('success', 'Fuel Type updated successfully');
    }

    // DELETE
    public function destroy($id)
    {
        FuelTypes::findOrFail($id)->delete();

        return redirect()->route('admin.fuel_types.index')
            ->with('success', 'Fuel Type deleted');
    }

    // STATUS TOGGLE
    public function toggleStatus($id)
    {
        $fuelType = FuelTypes::findOrFail($id);
        $fuelType->status = !$fuelType->status;
        $fuelType->save();

        return back();
    }

}
