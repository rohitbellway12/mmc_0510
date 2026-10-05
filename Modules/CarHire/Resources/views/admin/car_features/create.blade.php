@extends('adminmodule::layouts.master')


@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-3">Add Feature</h4>
            <a href="{{ route('admin.car_features.index') }}" class="btn btn--primary mb-3">Back</a>
        </div>
        <form action="{{ route('admin.car_features.store') }}" method="POST">
            @csrf


            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Feature Name</label>
                    <input type="text" name="name" class="form-control" placeholder="AC, GPS, Airbags" required>
                </div>


                {{-- <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div> --}}

                <div class="col-md-12 mb-3">
                    <input type="checkbox" name="status" checked> Active
                </div>
            </div>


            <button class="btn btn-success">Save Feature</button>
        </form>
    </div>
@endsection
