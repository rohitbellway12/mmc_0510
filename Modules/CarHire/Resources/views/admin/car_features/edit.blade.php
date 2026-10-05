@extends('adminmodule::layouts.master')


@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-3">Edit Feature</h4>
            <a href="{{ route('admin.car_features.index') }}" class="btn btn-secondary mb-3">Back</a>
        </div>

        <form action="{{ route('admin.car_features.update', $feature->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Feature Name</label>
                    <input type="text" name="name" value="{{ $feature->name }}" class="form-control" required>
                </div>


                {{-- <div class="col-md-6 mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="1" {{ $feature->status ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$feature->status ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div> --}}

                <div class="col-md-12 mb-3">
                    <input type="checkbox" name="status" {{ $feature->status ? 'checked' : '' }}> Active
                </div>
            </div>

            <button class="btn btn-success">Update Feature</button>
        </form>
    </div>
@endsection
