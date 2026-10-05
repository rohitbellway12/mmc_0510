@extends('adminmodule::layouts.master')

@section('title', 'Add Car Type')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Add Car Type</h2>
            <a href="{{ route('admin.car-types.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.car-types.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label>Car Type Name</label>
                        <input type="text" name="name" class="form-control" required>
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <button class="btn btn-success">Save</button>
                </form>
            </div>
        </div>

    </div>
@endsection
