@extends('adminmodule::layouts.master')

@section('title', 'Car Details')

@section('content')
    <div class="container-fluid">


        <div class="d-flex justify-content-between mb-3">
            <h2 class="mb-4">Car Details</h2>
            <div>
                <a href="{{ route('admin.carhire.index') }}" class="btn btn-secondary">Back</a>
                <a href="{{ route('admin.carhire.edit', $car->id) }}" class="btn btn--primary">Edit Car</a>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h4 class="mb-0">Basic Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Car Type</label>
                        <p class="fs-5">{{ $car->type?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Brand</label>
                        <p class="fs-5">{{ $car->brand?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Model</label>
                        <p class="fs-5">{{ $car->model?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Year</label>
                        <p class="fs-5">{{ $car->year?->year ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Feature</label>
                        <p class="fs-5">{{ $car->feature?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Fuel Type</label>
                        <p class="fs-5">{{ $car->fuel_type?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Transmission</label>
                        <p class="fs-5">{{ $car->transmission?->name ?? 'N/A' }}</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Seating Capacity</label>
                        <p class="fs-5">{{ $car->seating_capacity }} Persons</p>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="fw-bold text-muted">Daily Rent</label>
                        <p class="fs-5">₹{{ number_format($car->daily_rent, 2) }}</p>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="fw-bold text-muted">Description</label>
                        <p class="fs-5">{{ $car->description ?? 'No description available.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAP SECTION --}}
        <div class="card mb-3">
            <div class="card-header">
                <h4 class="mb-0">Location</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Latitude</label>
                        <p>{{ $car->coordinates['latitude'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold text-muted">Longitude</label>
                        <p>{{ $car->coordinates['longitude'] ?? 'N/A' }}</p>
                    </div>

                    <div class="col-12">
                        @if (isset($car->coordinates['latitude']) && isset($car->coordinates['longitude']))
                            <div id="location_map_canvas" class="overflow-hidden rounded"
                                style="height: 300px; width: 100%;"></div>
                        @else
                            <p class="text-danger">Location coordinates not available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- IMAGE SECTION --}}
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Images</h4>
            </div>
            <div class="card-body">
                @if (!empty($car->images) && is_array($car->images) && count($car->images) > 0)
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($car->images as $img)
                            <div style="width:200px">
                                <a href="{{ asset('storage/app/public/' . $img) }}" target="_blank">
                                    <img src="{{ asset('storage/app/public/' . $img) }}" class="img-thumbnail"
                                        style="height:150px; width:100%; object-fit:cover">
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No images available.</p>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('script')
    @if (isset($car->coordinates['latitude']) && isset($car->coordinates['longitude']))
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ business_config('google_map', 'third_party')?->live_values['map_api_key_client'] }}&v=3.45.8">
        </script>
        <script>
            "use strict";
            $(document).ready(function() {
                var myLatLng = {
                    lat: {{ $car->coordinates['latitude'] }},
                    lng: {{ $car->coordinates['longitude'] }}
                };

                const map = new google.maps.Map(document.getElementById("location_map_canvas"), {
                    center: myLatLng,
                    zoom: 15,
                    mapTypeId: "roadmap",
                });

                new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                    title: "{{ $car->brand }} {{ $car->model }}"
                });
            });
        </script>
    @endif
@endpush
