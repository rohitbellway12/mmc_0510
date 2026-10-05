@extends('adminmodule::layouts.master')

@section('title', 'Edit Car Type')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Car Type</h2>
            <a href="{{ route('admin.car-types.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('admin.car-types.update', $type->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label>Car Type Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $type->name }}" required>
                    </div>

                    <button class="btn btn-success">Update</button>
                </form>
            </div>
        </div>

    </div>
@endsection
