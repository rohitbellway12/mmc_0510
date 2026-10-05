@extends('adminmodule::layouts.master')

@section('title', 'Add Brand')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Add Brand</h2>
            <a href="{{ route('admin.car-brands.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.car-brands.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Car Type</label>
                            <select name="car_type_id" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach ($car_types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('car_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('car_type_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Brand Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" id="status" checked>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn--primary">Create Brand</button>
                </form>

            </div>
        </div>

    </div>
@endsection
