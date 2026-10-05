@extends('adminmodule::layouts.master')

@section('title', 'Add New Car')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-4">Add New Car</h2>
            <a href="{{ route('admin.carhire.index') }}" class="btn btn-secondary mb-3">Back to List</a>
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.carhire.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="category_id" value="{{ $category->id }}">

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Car Type</label>
                    {{-- <select name="car_type_id" class="form-control" required>
                        <option value="">Select Car Type</option>
                        @foreach ($car_types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select> --}}

                    <select name="car_type_id" id="car_type_id" class="form-control" required>
                        <option value="">Select Car Type</option>
                        @foreach ($car_types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>

                </div>
                {{-- <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    <select name="brand" class="form-control" required>
                        <option value="">Select Brand</option>
                        <option value="Toyota">Toyota</option>
                        <option value="Hyundai">Hyundai</option>
                        <option value="Mahindra">Mahindra</option>
                        <option value="Tata">Tata</option>
                        <option value="Honda">Honda</option>
                        <option value="Kia">Kia</option>
                        <option value="BMW">BMW</option>
                        <option value="Mercedes">Mercedes</option>
                        <option value="Audi">Audi</option>
                    </select>
                </div> --}}
                <div class="col-md-4 mb-3">
                    <label>Brand</label>
                    <select name="brand_id" id="brand_id" class="form-control" required>
                        <option value="">Select Brand</option>
                    </select>

                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Model</label>
                    <select name="model" class="form-control" required>
                        <option value="">Select Model</option>
                        <option value="Swift">Swift</option>
                        <option value="Creta">Creta</option>
                        <option value="Innova">Innova</option>
                        <option value="XUV 700">XUV 700</option>
                        <option value="Harrier">Harrier</option>
                        <option value="City">City</option>
                        <option value="Seltos">Seltos</option>
                        <option value="Fortuner">Fortuner</option>
                        <option value="A4">A4</option>
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
                        <option value="">Select Year</option>
                        @for ($year = 2000; $year <= 2025; $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
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
                    <label>Features</label>
                    <select name="feature_id" class="form-control" required>
                        <option value="">Select Feature</option>
                        @foreach ($features as $feature)
                            <option value="{{ $feature->id }}">{{ $feature->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Fuel Type</label>
                    <select name="fuel_type" class="form-control" required>
                        <option value="">Select Fuel Type</option>
                        <option value="Petrol">Petrol</option>
                        <option value="Diesel">Diesel</option>
                        <option value="CNG">CNG</option>
                        <option value="Electric">Electric</option>
                        <option value="Hybrid">Hybrid</option>
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Fuel Type</label>
                    <select name="fuel_type_id" class="form-control" required>
                        <option value="">Select Fuel Type</option>
                        @foreach ($fuel_types as $fuel_type)
                            <option value="{{ $fuel_type->id }}">{{ $fuel_type->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- <div class="col-md-4 mb-3">
                    <label>Transmission</label>
                    <select name="transmission" class="form-control" required>
                        <option value="">Select Transmission</option>
                        <option value="Manual">Manual</option>
                        <option value="Automatic">Automatic</option>
                    </select>
                </div> --}}

                <div class="col-md-4 mb-3">
                    <label>Transmission</label>
                    <select name="transmission_id" class="form-control" required>
                        <option value="">Select Transmission</option>
                        @foreach ($transmissions as $transmission)
                            <option value="{{ $transmission->id }}">{{ $transmission->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Seating Capacity</label>
                    <input type="number" name="seating_capacity" class="form-control" required>
                </div>

                <div class="col-md-4 mb-3">
                    <label>Daily Rent</label>
                    <input type="number" step="0.01" name="daily_rent" class="form-control" required>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

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
                                                placeholder="{{ translate('latitude') }} *" value="" required
                                                readonly data-bs-toggle="tooltip" data-bs-placement="top"
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
                                                placeholder="{{ translate('longitude') }} *" value="" required
                                                readonly data-bs-toggle="tooltip" data-bs-placement="top"
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
                <div class="col-md-12 mb-3">
                    <label>Images</label>
                    <input type="file" name="images[]" class="form-control" multiple>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Add Car</button>
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
            function initAutocomplete() {
                var myLatLng = {
                    lat: 23.811842872190343,
                    lng: 90.356331
                };
                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: {
                        lat: 23.811842872190343,
                        lng: 90.356331
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
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

    {{-- brands --}}
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

    {{-- models --}}
    <script>
        $(document).ready(function() {

            $('#brand_id').on('change', function() {
                let brandId = $(this).val();

                if (brandId) {
                    $.ajax({
                        url: "{{ route('admin.ajax.models.by_brand', '') }}/" + brandId,
                        type: "GET",
                        success: function(data) {

                            $('#model_id').empty();
                            $('#model_id').append('<option value="">Select Model</option>');

                            $.each(data, function(key, model) {
                                $('#model_id').append(
                                    '<option value="' + model.id + '">' + model
                                    .name + '</option>'
                                );
                            });
                        }
                    });
                } else {
                    $('#model_id').empty().append('<option value="">Select Model</option>');
                }
            });

        });
    </script>

    {{-- years --}}
    <script>
        $(document).ready(function() {

            $('#model_id').on('change', function() {
                let modelId = $(this).val();

                if (modelId) {
                    $.ajax({
                        url: "{{ route('admin.ajax.years.by_model', '') }}/" + modelId,
                        type: "GET",
                        success: function(data) {

                            $('#year_id').empty();
                            $('#year_id').append('<option value="">Select Year</option>');

                            $.each(data, function(key, year) {
                                $('#year_id').append(
                                    '<option value="' + year.id + '">' + year
                                    .year + '</option>'
                                );
                            });
                        }
                    });
                } else {
                    $('#year_id').empty().append('<option value="">Select Year</option>');
                }
            });

        });
    </script>
@endpush
