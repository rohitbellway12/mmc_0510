@extends('adminmodule::layouts.master')

@section('title', 'Edit Car')

@section('content')
    <div class="container-fluid">


        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-4">Edit Car</h2>

            <a href="{{ route('admin.carhire.index') }}" class="btn btn-secondary mb-3">Back</a>
        </div>

        <form action="{{ route('admin.carhire.update', $car->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <input type="hidden" name="category_id" value="{{ $category->id }}">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <label>Car Type</label>
                    {{-- <select name="car_type_id" class="form-control" required>
                        <option value="">Select Car Type</option>
                        @foreach ($car_types as $type)
                            <option value="{{ $type->id }}" {{ $car->car_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select> --}}
                    <select name="car_type_id" id="car_type_id" class="form-control" required>
                        @foreach ($car_types as $type)
                            <option value="{{ $type->id }}" {{ $car->car_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>

                </div>

                <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    {{-- <select name="brand" class="form-control" required>
                        <option value="">Select Brand</option>
                        @foreach (['Toyota', 'Hyundai', 'Mahindra', 'Tata', 'Honda', 'Kia', 'BMW', 'Mercedes', 'Audi'] as $brand)
                            <option value="{{ $brand }}" {{ $car->brand == $brand ? 'selected' : '' }}>
                                {{ $brand }}</option>
                        @endforeach
                    </select> --}}
                    <select name="brand_id" id="brand_id" class="form-control" required>
                        <option value="">Select Brand</option>
                    </select>

                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Model</label>
                    <select name="model" class="form-control" required>
                        @foreach (['Swift', 'Creta', 'Innova', 'XUV 700', 'Harrier', 'City', 'Seltos', 'Fortuner', 'A4'] as $m)
                            <option value="{{ $m }}" {{ $car->model == $m ? 'selected' : '' }}>
                                {{ $m }}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Model</label>
                    <select name="model_id" id="model_id" class="form-control" required>
                        <option value="">Select Model</option>
                    </select>
                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Year</label>
                    <select name="year" class="form-control" required>
                        @for ($year = 2000; $year <= 2025; $year++)
                            <option value="{{ $year }}" {{ $car->year == $year ? 'selected' : '' }}>
                                {{ $year }}</option>
                        @endfor
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Year</label>
                    <select name="year_id" id="year_id" class="form-control" required>
                        <option value="">Select Year</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Feature</label>
                    <select name="feature_id" id="feature_id" class="form-control" required>
                        <option value="">Select Feature</option>
                        @foreach ($features as $feature)
                            <option value="{{ $feature->id }}" {{ $car->feature_id == $feature->id ? 'selected' : '' }}>
                                {{ $feature->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Fuel Type</label>
                    <select name="fuel_type" class="form-control">
                        @foreach (['Petrol', 'Diesel', 'CNG', 'Electric', 'Hybrid'] as $f)
                            <option value="{{ $f }}" {{ $car->fuel_type == $f ? 'selected' : '' }}>
                                {{ $f }}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Fuel Type</label>
                    <select name="fuel_type_id" id="fuel_type_id" class="form-control" required>
                        <option value="">Select Fuel Type</option>
                        @foreach ($fuel_types as $fuel_type)
                            <option value="{{ $fuel_type->id }}"
                                {{ $car->fuel_type_id == $fuel_type->id ? 'selected' : '' }}>
                                {{ $fuel_type->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Transmission</label>
                    <select name="transmission" class="form-control">
                        @foreach (['Manual', 'Automatic'] as $t)
                            <option value="{{ $t }}" {{ $car->transmission == $t ? 'selected' : '' }}>
                                {{ $t }}</option>
                        @endforeach
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Transmission</label>
                    <select name="transmission_id" id="transmission_id" class="form-control" required>
                        <option value="">Select Transmission</option>
                        @foreach ($transmissions as $transmission)
                            <option value="{{ $transmission->id }}"
                                {{ $car->transmission_id == $transmission->id ? 'selected' : '' }}>
                                {{ $transmission->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Seating Capacity</label>
                    <input type="number" name="seating_capacity" class="form-control"
                        value="{{ $car->seating_capacity }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label>Daily Rent</label>
                    <input type="number" name="daily_rent" step="0.01" class="form-control"
                        value="{{ $car->daily_rent }}">
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $car->description }}</textarea>
                </div>

                {{-- MAP SECTION --}}
                <div class="col-12 mt-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between gap-3 mb-20">
                                <h4 class="c1">{{ translate('Select Address from Map') }}</h4>
                            </div>
                            <div class="row gx-2">
                                <div class="col-md-6 col-12">
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control" name="latitude" id="latitude"
                                                placeholder="{{ translate('latitude') }} *"
                                                value="{{ $car->coordinates['latitude'] ?? '' }}" required readonly
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ translate('Select from map') }}">
                                            <label>{{ translate('latitude') }} *</label>
                                            <span class="material-symbols-outlined">location_on</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-12">
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control" name="longitude" id="longitude"
                                                placeholder="{{ translate('longitude') }} *"
                                                value="{{ $car->coordinates['longitude'] ?? '' }}" required readonly
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ translate('Select from map') }}">
                                            <label>{{ translate('longitude') }} *</label>
                                            <span class="material-symbols-outlined">location_on</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mb-4">
                                    <div id="location_map_div" class="location_map_class">
                                        <input id="pac-input" class="form-control w-auto" data-toggle="tooltip"
                                            data-placement="right"
                                            data-original-title="{{ translate('search_your_location_here') }}"
                                            type="text" placeholder="{{ translate('search_here') }}" />
                                        <div id="location_map_canvas" class="overflow-hidden rounded canvas_class"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- IMAGE SECTION --}}
                <div class="col-12 mt-3">
                    <h5 class="mb-3">Current Images</h5>
                    @if (!empty($car->images) && is_array($car->images) && count($car->images) > 0)
                        <div class="d-flex flex-wrap gap-3 mb-3">
                            @foreach ($car->images as $index => $img)
                                <div style="width:150px">
                                    <img src="{{ asset('storage/app/public/' . $img) }}" class="img-thumbnail mb-2"
                                        style="height:120px; width:100%; object-fit:cover">

                                    <button type="button" class="btn btn-sm btn-danger w-100"
                                        onclick="deleteImage('{{ route('admin.carhire.image.delete', [$car->id, $index]) }}')">
                                        Delete
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No images uploaded yet.</p>
                    @endif
                </div>

                <div class="col-md-12 mb-3">
                    <label>Add New Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                </div>

            </div>

            <button class="btn btn-success mt-3">Update Car</button>

        </form>

        <form id="deleteImageForm" method="POST" style="display:none">
            @csrf
            @method('DELETE')
        </form>

    </div>
@endsection

@push('script')
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ business_config('google_map', 'third_party')?->live_values['map_api_key_client'] }}&libraries=places&v=3.45.8">
    </script>

    <script>
        "use strict";

        $(document).ready(function() {
            window.deleteImage = function(url) {
                if (confirm('Delete this image?')) {
                    let form = document.getElementById('deleteImageForm');
                    form.action = url;
                    form.submit();
                }
            }

            function initAutocomplete() {
                var existingLat = parseFloat('{{ $car->coordinates['latitude'] ?? 23.811842872190343 }}');
                var existingLng = parseFloat('{{ $car->coordinates['longitude'] ?? 90.356331 }}');

                var myLatLng = {
                    lat: existingLat,
                    lng: existingLng
                };

                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: myLatLng,
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = new google.maps.Geocoder();

                google.maps.event.addListener(map, 'click', function(mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];

                    geocoder.geocode({
                        'latLng': latlng
                    }, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                console.log(results[1].formatted_address);
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];
                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }
                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];
                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function(event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position
                                .lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete();
        });
    </script>


    {{-- <script>
        $(document).ready(function() {

            let selectedBrandId = "{{ $car->brand_id }}"; // edit ke liye
            let selectedModelId = "{{ $car->model_id }}"; // edit ke liye
            let selectedYearId = "{{ $car->year_id }}"; // edit ke liye
            // PAGE LOAD ME AGAR CAR TYPE SET HAI TO AJAX CALL AUTOMATICALLY
            let typeId = $('#car_type_id').val();
            if (typeId) {
                loadBrands(typeId);
            }

            // JAB USER CAR TYPE CHANGE KARE → NEW BRANDS LOAD HO
            $('#car_type_id').on('change', function() {
                selectedBrandId = null; // user changing type → reset selected brand
                selectedModelId = null; // reset model too
                selectedYearId = null; // reset year too
                loadBrands($(this).val());
            });

            function loadBrands(typeId) {
                if (!typeId) return;

                $.ajax({
                    url: "{{ route('admin.ajax.brands.by_type', '') }}/" + typeId,
                    type: "GET",
                    success: function(data) {

                        $('#brand_id').empty().append('<option value="">Select Brand</option>');

                        $.each(data, function(key, brand) {
                            $('#brand_id').append(
                                '<option value="' + brand.id + '" ' +
                                (brand.id == selectedBrandId ? "selected" : "") +
                                '>' + brand.name + '</option>'
                            );
                        });

                        // BRAND SELECT HO GAYA → ab models load karo
                        let brandIdToLoad = $('#brand_id').val();
                        if (brandIdToLoad) {
                            loadModels(brandIdToLoad);
                        }
                    }
                });
            }

            function loadModels(brandId) {
                if (!brandId) return;

                $.ajax({
                    url: "{{ route('admin.ajax.models.by_brand', '') }}/" + brandId,
                    type: "GET",
                    success: function(data) {
                        $('#model_id').empty().append('<option value="">Select Model</option>');

                        $.each(data, function(key, model) {
                            $('#model_id').append(
                                '<option value="' + model.id + '" ' +
                                (model.id == selectedModelId ? "selected" : "") +
                                '>' + model.name + '</option>'
                            );
                        });
                    }
                });
            }

            function loadYears(modelId) {
                if (!modelId) return;

                $.ajax({
                    url: "{{ route('admin.ajax.years.by_model', '') }}/" + modelId,
                    type: "GET",
                    success: function(data) {
                        $('#year_id').empty().append('<option value="">Select Year</option>');

                        $.each(data, function(key, year) {
                            $('#year_id').append(
                                '<option value="' + year.id + '" ' +
                                (year.id == selectedYearId ? "selected" : "") +
                                '>' + year.year + '</option>'
                            );
                        });
                    }
                });
            }

            // JAB USER BRAND CHANGE KARE → NEW MODELS LOAD HO
            $('#brand_id').on('change', function() {
                selectedModelId = null; // user changing brand → reset model
                selectedYearId = null; // reset year too
                loadModels($(this).val());
            });

            // JAB USER MODEL CHANGE KARE → NEW YEARS LOAD HO
            $('#model_id').on('change', function() {
                selectedYearId = null; // user changing model → reset year
                loadYears($(this).val());
            });

        });
    </script> --}}

    <script>
        $(document).ready(function() {

            let selectedBrandId = "{{ $car->brand_id }}";
            let selectedModelId = "{{ $car->model_id }}";
            let selectedYearId = "{{ $car->year_id }}";

            // =============================
            // PAGE LOAD (EDIT MODE)
            // =============================
            let typeId = $('#car_type_id').val();
            if (typeId) {
                loadBrands(typeId);
            }

            // =============================
            // TYPE CHANGE
            // =============================
            $('#car_type_id').on('change', function() {
                selectedBrandId = null;
                selectedModelId = null;
                selectedYearId = null;

                resetBrand();
                resetModel();
                resetYear();

                loadBrands($(this).val());
            });

            // =============================
            // BRAND CHANGE
            // =============================
            $('#brand_id').on('change', function() {
                selectedModelId = null;
                selectedYearId = null;

                resetModel();
                resetYear();

                loadModels($(this).val());
            });

            // =============================
            // MODEL CHANGE
            // =============================
            $('#model_id').on('change', function() {
                selectedYearId = null;
                resetYear();
                loadYears($(this).val());
            });

            // =============================
            // AJAX FUNCTIONS
            // =============================

            function loadBrands(typeId) {
                if (!typeId) return;

                $.get("{{ route('admin.ajax.brands.by_type', '') }}/" + typeId, function(data) {

                    let html = '<option value="">Select Brand</option>';
                    data.forEach(brand => {
                        html +=
                            `<option value="${brand.id}" ${brand.id == selectedBrandId ? 'selected':''}>${brand.name}</option>`;
                    });

                    $('#brand_id').html(html);

                    let brandId = $('#brand_id').val();
                    if (brandId) loadModels(brandId);
                });
            }

            function loadModels(brandId) {
                if (!brandId) return;

                $.get("{{ route('admin.ajax.models.by_brand', '') }}/" + brandId, function(data) {

                    let html = '<option value="">Select Model</option>';
                    data.forEach(model => {
                        html +=
                            `<option value="${model.id}" ${model.id == selectedModelId ? 'selected':''}>${model.name}</option>`;
                    });

                    $('#model_id').html(html);

                    let modelId = $('#model_id').val();
                    if (modelId) loadYears(modelId); // 🔥 IMPORTANT LINE
                });
            }

            function loadYears(modelId) {
                if (!modelId) return;

                $.get("{{ route('admin.ajax.years.by_model', '') }}/" + modelId, function(data) {

                    let html = '<option value="">Select Year</option>';
                    data.forEach(year => {
                        html +=
                            `<option value="${year.id}" ${year.id == selectedYearId ? 'selected':''}>${year.year}</option>`;
                    });

                    $('#year_id').html(html);
                });
            }

            // =============================
            // RESET HELPERS (VERY IMPORTANT)
            // =============================
            function resetBrand() {
                $('#brand_id').html('<option value="">Select Brand</option>');
            }

            function resetModel() {
                $('#model_id').html('<option value="">Select Model</option>');
            }

            function resetYear() {
                $('#year_id').html('<option value="">Select Year</option>');
            }

        });
    </script>
@endpush
