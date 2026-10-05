@extends('adminmodule::layouts.master')

@section('title', 'Add Transmission')

@section('content')
    <div class="container-fluid">

        <h4 class="mb-3">Add Transmission</h4>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.transmissions.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <input type="checkbox" name="status" checked> Active
                    </div>

                    <button class="btn btn-success">Save</button>
                    <a href="{{ route('admin.transmissions.index') }}" class="btn btn-secondary">Back</a>
                </form>

            </div>
        </div>

    </div>
@endsection
