<?php

namespace Modules\CarHire\Http\Controllers\Web\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\CarHire\Entities\Transmissions;

class CarTransmissionController extends Controller
{
    // LIST
    public function index()
    {
        $transmissions = Transmissions::orderBy('id', 'desc')->get();
        return view('carhire::admin.transmissions.index', compact('transmissions'));
    }

    // CREATE FORM
    public function create()
    {
        return view('carhire::admin.transmissions.create');
    }

    // STORE
    public function store(Request $request)
    {
        $request->validate([
           'name' => 'required|unique:transmissions,name'
        ]);

        Transmissions::create([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.transmissions.index')
            ->with('success', 'Transmission added successfully');
    }

    // EDIT FORM
    public function edit($id)
    {
        $transmission = Transmissions::findOrFail($id);
        return view('carhire::admin.transmissions.edit', compact('transmission'));
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $transmission = Transmissions::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $transmission->update([
            'name'   => $request->name,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.transmissions.index')
            ->with('success', 'Transmission updated successfully');
    }

    // DELETE
    public function destroy($id)
    {
        Transmissions::findOrFail($id)->delete();

        return redirect()->route('admin.transmissions.index')
            ->with('success', 'Transmission deleted');
    }

    // STATUS TOGGLE
    public function toggleStatus($id)
    {
        $transmission = Transmissions::findOrFail($id);
        $transmission->status = !$transmission->status;
        $transmission->save();

        return back();
    }
}
