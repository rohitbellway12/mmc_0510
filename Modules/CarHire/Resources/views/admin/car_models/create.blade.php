@extends('adminmodule::layouts.master')

@section('title', 'Add Car Model')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between mb-4">
            <h2>Add Car Model</h2>
            <a href="{{ route('admin.car-models.index') }}" class="btn btn-secondary">Back</a>
        </div>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.car-models.store') }}" method="POST">
                    @csrf
                    <div class="row">

                        <div class="col-md-4 mb-3">
                            <label>Car Type</label>
                            <select id="car_type_id" class="form-control">
                                <option value="">Select Type</option>
                                @foreach ($car_types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Brand</label>
                            <select name="brand_id" id="brand_id" class="form-control" required>
                                <option value="">Select Type First</option>
                            </select>
                            @error('brand_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Model Name</label>
                            <input name="name" type="text" class="form-control" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

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

            $('#car_type_id').on('change', function() {
                let typeId = $(this).val();

                if (typeId) {
                    $.ajax({
                        url: "{{ route('admin.ajax.brands.by_type', '') }}/" + typeId,
                        type: "GET",
                        success: function(data) {

                            $('#brand_id').empty();
                            $('#brand_id').append('<option value="">Select Brand</option>');

                            $.each(data, function(key, brand) {
                                $('#brand_id').append(
                                    '<option value="' + brand.id + '">' + brand
                                    .name + '</option>'
                                );
                            });
                        }
                    });
                } else {
                    $('#brand_id').empty().append('<option value="">Select Brand</option>');
                }
            });
        });
    </script>
@endpush
