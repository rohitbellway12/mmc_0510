@extends('adminmodule::layouts.master')

@section('title', 'Edit Transmission')

@section('content')
    <div class="container-fluid">

        <h4 class="mb-3">Edit Transmission</h4>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.transmissions.update', $transmission->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $transmission->name }}" required>
                    </div>

                    <div class="mb-3">
                        <input type="checkbox" name="status" {{ $transmission->status ? 'checked' : '' }}>
                        Active
                    </div>

                    <button class="btn btn-success">Update</button>
                    <a href="{{ route('admin.transmissions.index') }}" class="btn btn-secondary">Back</a>
                </form>

            </div>
        </div>

    </div>
@endsection
