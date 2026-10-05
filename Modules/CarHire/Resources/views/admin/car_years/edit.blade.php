@extends('adminmodule::layouts.master')

@section('title', 'Edit Car Year')

@section('content')
    <div class="container-fluid">

        <h4 class="mb-3">Edit Car Year</h4>
        <a href="{{ route('admin.car-years.index') }}" class="btn btn-secondary mb-3">Back</a>

        <div class="card">
            <div class="card-body">

                <form action="{{ route('admin.car-years.update', $year->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        {{-- Car Type --}}
                        <div class="col-md-4 mb-3">
                            <label>Car Type</label>
                            <select name="car_type_id" id="car_type_id" class="form-control" required>
                                <option value="">Select Type</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}"
                                        {{ $year->car_type_id == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Brand --}}
                        <div class="col-md-4 mb-3">
                            <label>Brand</label>
                            <select name="brand_id" id="brand_id" class="form-control" required>
                                <option value="">Select Brand</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                        {{ $year->brand_id == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Model --}}
                        <div class="col-md-4 mb-3">
                            <label>Model</label>
                            <select name="model_id" id="model_id" class="form-control" required>
                                <option value="">Select Model</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model->id }}"
                                        {{ $year->model_id == $model->id ? 'selected' : '' }}>
                                        {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label>Year</label>
                            <input type="number" name="year" class="form-control" value="{{ $year->year }}" required>
                        </div>

                        {{-- <div class="col-md-4 mb-3">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="1" {{ $year->status ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ !$year->status ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div> --}}
                        <div class="col-md-12 mb-3">
                            <input type="checkbox" name="status" {{ $year->status ? 'checked' : '' }}> Active
                        </div>

                    </div>

                    <button class="btn btn-success">Update</button>
                </form>

            </div>
        </div>

    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {

            let selectedBrandId = "{{ $year->brand_id }}";
            let selectedModelId = "{{ $year->model_id }}";

            // Load brands for a type
            function loadBrands(typeId, callback = null) {
                if (!typeId) return;
                $.get("{{ route('admin.ajax.brands.by_type', '') }}/" + typeId, function(data) {
                    let html = '<option value="">Select Brand</option>';
                    data.forEach(row => {
                        let selected = (selectedBrandId && selectedBrandId == row.id) ? 'selected' :
                            '';
                        html += `<option value="${row.id}" ${selected}>${row.name}</option>`;
                    });
                    $('#brand_id').html(html);

                    // If callback (load models) is provided
                    if (callback) callback();
                });
            }

            // Load models for a brand
            function loadModels(brandId, callback = null) {
                if (!brandId) return;
                $.get("{{ route('admin.ajax.models.by_brand', '') }}/" + brandId, function(data) {
                    let html = '<option value="">Select Model</option>';
                    data.forEach(row => {
                        let selected = (selectedModelId && selectedModelId == row.id) ? 'selected' :
                            '';
                        html += `<option value="${row.id}" ${selected}>${row.name}</option>`;
                    });
                    $('#model_id').html(html);

                    // If callback is provided
                    if (callback) callback();
                });
            }

            // PAGE LOAD: Load brands then models in sequence
            let typeId = $('#car_type_id').val();
            if (typeId && selectedBrandId) {
                loadBrands(typeId, function() {
                    // After brands are loaded, set the brand value and load models
                    $('#brand_id').val(selectedBrandId);
                    if (selectedBrandId) {
                        loadModels(selectedBrandId, function() {
                            // After models are loaded, set the model value
                            $('#model_id').val(selectedModelId);
                        });
                    }
                });
            }

            // On type change
            $('#car_type_id').on('change', function() {
                selectedBrandId = null;
                selectedModelId = null;
                loadBrands($(this).val());
                $('#model_id').html('<option value="">Select Model</option>');
            });

            // On brand change
            $('#brand_id').on('change', function() {
                selectedModelId = null;
                let brandId = $(this).val();
                loadModels(brandId);
            });

        });
    </script>
@endpush
