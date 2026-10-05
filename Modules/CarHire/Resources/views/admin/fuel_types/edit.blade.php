@extends('adminmodule::layouts.master')

@section('title', 'Edit Fuel Type')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-3">Edit Fuel Type</h4>
            <a href="{{ route('admin.fuel_types.index') }}" class="btn btn--primary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.fuel_types.update', $fuelType->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $fuelType->name }}" required>
                    </div>

                    <div class="mb-3">
                        <input type="checkbox" name="status" {{ $fuelType->status ? 'checked' : '' }}>
                        Active
                    </div>

                    <button class="btn btn-success">Update</button>
                </form>

            </div>
        </div>

    </div>
@endsection
