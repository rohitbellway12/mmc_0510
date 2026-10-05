@extends('adminmodule::layouts.master')

@section('title', 'Add Fuel Type')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-3">Add Fuel Type</h4>
            <a href="{{ route('admin.fuel_types.index') }}" class="btn btn--primary mb-3">Back</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.fuel_types.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <input type="checkbox" name="status" checked> Active
                    </div>

                    <button class="btn btn-success">Save</button>

                </form>

            </div>
        </div>

    </div>
@endsection
