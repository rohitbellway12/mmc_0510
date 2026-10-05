@extends('adminmodule::layouts.master')

@section('title', 'Add Car Year')

@section('content')
    <div class="container-fluid">

        <h4 class="mb-3">Add Car Year</h4>
        <a href="{{ route('admin.car-years.index') }}" class="btn btn-secondary mb-3">Back</a>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.car-years.store') }}" method="POST">
                    @csrf

                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Car Type</label>
                            <select name="car_type_id" id="car_type_id" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Brand</label>
                            <select name="brand_id" id="brand_id" class="form-control" required>
                                <option value="">Select Type First</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Model</label>
                            <select name="model_id" id="model_id" class="form-control" required>
                                <option value="">Select Brand First</option>
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Year</label>
                            <input type="number" name="year" class="form-control" placeholder="2020" required>
                        </div>

                        {{-- <div class="col-md-4 mb-3">
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

                    <button class="btn btn-success">Save</button>

                </form>

            </div>
        </div>

    </div>
@endsection


@push('script')
    <script>
        $(document).ready(function() {

            // When car type changes → Load brands
            $('#car_type_id').on('change', function() {
                let typeId = $(this).val();

                $('#brand_id').empty().append('<option value="">Loading...</option>');
                $('#model_id').empty().append('<option value="">Select Brand First</option>');

                if (typeId) {
                    $.ajax({
                        url: "{{ route('admin.ajax.brands.by_type', '') }}/" + typeId,
                        type: "GET",
                        success: function(data) {

                            $('#brand_id').empty().append(
                                '<option value="">Select Brand</option>');

                            $.each(data, function(key, brand) {
                                $('#brand_id').append('<option value="' + brand.id +
                                    '">' + brand.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#brand_id').empty().append('<option value="">Select Type First</option>');
                }
            });

            // When brand changes → Load models
            $('#brand_id').on('change', function() {
                let brandId = $(this).val();

                $('#model_id').empty().append('<option value="">Loading...</option>');

                if (brandId) {
                    $.ajax({
                        url: "{{ route('admin.ajax.models.by_brand', '') }}/" + brandId,
                        type: "GET",
                        success: function(data) {

                            $('#model_id').empty().append(
                                '<option value="">Select Model</option>');

                            $.each(data, function(key, model) {
                                $('#model_id').append('<option value="' + model.id +
                                    '">' + model.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#model_id').empty().append('<option value="">Select Brand First</option>');
                }
            });

        });
    </script>
@endpush
