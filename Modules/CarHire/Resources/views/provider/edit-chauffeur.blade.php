@extends('providermanagement::layouts.master')

@section('title', translate('Edit Chauffeur Vehicle & Service'))

@push('css_or_js')
    <style>
        .form-section-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tier-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            height: 100%;
        }

        .tier-card:hover {
            border-color: #0461A5;
            background: #f8fafc;
        }

        .tier-card.selected {
            border-color: #0461A5;
            background: #eff6ff;
        }

        .upload-box {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            background: #f8fafc;
            transition: 0.3s;
        }

        .upload-box:hover {
            border-color: #0461A5;
            background: #fff;
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 14px;
        }

        .image-preview-item {
            width: 110px;
            height: 85px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            position: relative;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .image-preview-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.12);
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-item .delete-img-btn {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.9);
            color: #fff;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: all 0.2s ease;
            padding: 0;
            z-index: 10;
        }

        .image-preview-item .delete-img-btn:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .image-preview-item .delete-img-btn .material-icons {
            font-size: 15px;
            line-height: 1;
        }

        .image-preview-item .new-badge {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: #10b981;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }

        .amenity-chip {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            transition: all 0.15s ease;
            font-size: 13px;
            font-weight: 500;
        }

        .amenity-chip input {
            cursor: pointer;
        }

        .amenity-chip:hover {
            border-color: #0461A5;
            background: #f0fdf4;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="page-title">{{ translate('Edit Chauffeur Vehicle & Service') }}</h2>
                    <p class="text-muted fz-14 mb-0">{{ translate('Update luxury vehicle class, minimum hours, VIP inclusions, and chauffeur rates.') }}</p>
                </div>
                <a href="{{ route('provider.car.index') }}" class="btn btn--secondary d-flex align-items-center gap-2">
                    <span class="material-icons">arrow_back</span>
                    {{ translate('Back to Fleet') }}
                </a>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('provider.car.update', [$car->id]) }}" method="POST" enctype="multipart/form-data" id="chauffeur-form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="service_category" value="chauffeur">
                        <input type="hidden" name="category_id" value="{{ $car->category_id ?? ($categories->firstWhere('name', 'Chauffeur')?->id ?? '') }}">

                        {{-- Section 1: Chauffeur Service Tier / Class --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">stars</span>
                                {{ translate('Chauffeur Class / Tier') }}
                            </h4>
                            @php
                                $tier = $car->chauffeur_tier ?? 'business_class';
                            @endphp
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card {{ $tier == 'business_class' ? 'selected' : '' }} d-block">
                                        <input type="radio" name="chauffeur_tier" value="business_class" class="d-none" {{ $tier == 'business_class' ? 'checked' : '' }}>
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('Business Class') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes E-Class, BMW 5 Series, Audi A6') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card {{ $tier == 'first_class' ? 'selected' : '' }} d-block">
                                        <input type="radio" name="chauffeur_tier" value="first_class" class="d-none" {{ $tier == 'first_class' ? 'checked' : '' }}>
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('First Class / Luxury') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes S-Class, BMW 7 Series, Range Rover') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card {{ $tier == 'luxury_mpv' ? 'selected' : '' }} d-block">
                                        <input type="radio" name="chauffeur_tier" value="luxury_mpv" class="d-none" {{ $tier == 'luxury_mpv' ? 'checked' : '' }}>
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('Luxury MPV / Group') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes V-Class (Up to 7-8 Passengers)') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card {{ $tier == 'wedding' ? 'selected' : '' }} d-block">
                                        <input type="radio" name="chauffeur_tier" value="wedding" class="d-none" {{ $tier == 'wedding' ? 'checked' : '' }}>
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('Wedding & VIP Classic') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Rolls Royce, Bentley, Classic Vintage') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Section 2: Vehicle Information --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">directions_car</span>
                                {{ translate('Vehicle & Capacity Information') }}
                            </h4>
                            <div class="row g-3">
                                {{-- Brand --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Car Brand / Make') }}</label>
                                    <select name="brand" id="chauffeur_brand_select" class="form-select" required>
                                        <option value="">{{ translate('-- Select Brand --') }}</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}" data-id="{{ $brand->id }}" {{ $car->brand == $brand->name ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                        <option value="other">{{ translate('+ Other / Custom Brand') }}</option>
                                    </select>
                                    <input type="text" name="custom_brand" id="chauffeur_custom_brand_input" class="form-control mt-2" 
                                           placeholder="{{ translate('Type brand...') }}" style="display: none;">
                                </div>

                                {{-- Model --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Car Model') }}</label>
                                    <select name="model_select" id="chauffeur_model_select" class="form-select">
                                        <option value="">{{ translate('-- Select Model --') }}</option>
                                        @foreach($models as $m)
                                            <option value="{{ $m->name }}" {{ $car->model == $m->name ? 'selected' : '' }}>{{ $m->name }}</option>
                                        @endforeach
                                        <option value="other" {{ $models->isEmpty() || !$models->contains('name', $car->model) ? 'selected' : '' }}>{{ translate('+ Enter Custom Model') }}</option>
                                    </select>
                                    <input type="text" name="model" id="chauffeur_model_input" class="form-control mt-2" 
                                           value="{{ $car->model }}" placeholder="{{ translate('e.g. S-Class 350d, E-Class, V-Class...') }}" required>
                                </div>

                                {{-- Registration Number --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Registration Number (Plate)') }}</label>
                                    <input type="text" name="registration_number" class="form-control text-uppercase" 
                                           value="{{ $car->registration_number }}" placeholder="{{ translate('e.g. LD69 XYZ') }}" required>
                                </div>

                                {{-- Vehicle Type --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Vehicle Category / Type') }}</label>
                                    <select name="car_type_id" class="form-select" required>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" {{ $car->car_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Manufacture Year --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Manufacture Year') }}</label>
                                    <input type="number" name="manufacture_year" class="form-control" 
                                           min="2010" max="{{ date('Y') + 1 }}" value="{{ $car->manufacture_year ?? $car->year ?? 2022 }}">
                                </div>

                                {{-- Fuel Type --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Fuel Type') }}</label>
                                    <select name="fuel_type" class="form-select">
                                        @foreach($fuelTypes as $ft)
                                            <option value="{{ $ft->name }}" {{ $car->fuel_type == $ft->name ? 'selected' : '' }}>{{ $ft->name }}</option>
                                        @endforeach
                                        <option value="Hybrid" {{ $car->fuel_type == 'Hybrid' ? 'selected' : '' }}>{{ translate('Hybrid') }}</option>
                                        <option value="Electric" {{ $car->fuel_type == 'Electric' ? 'selected' : '' }}>{{ translate('Electric') }}</option>
                                        <option value="Diesel" {{ $car->fuel_type == 'Diesel' ? 'selected' : '' }}>{{ translate('Diesel') }}</option>
                                    </select>
                                </div>

                                {{-- Passenger Capacity --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label required-field fw-medium">{{ translate('Max Passengers') }}</label>
                                    <input type="number" name="seating_capacity" class="form-control" value="{{ $car->seating_capacity ?? 3 }}" min="1" max="16" required>
                                </div>

                                {{-- Luggage Capacity --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label required-field fw-medium">{{ translate('Large Suitcases Capacity') }}</label>
                                    <input type="number" name="luggage_capacity" class="form-control" value="{{ $car->luggage_capacity ?? 2 }}" min="0" max="10" required>
                                </div>

                                {{-- Transmission --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label fw-medium">{{ translate('Transmission') }}</label>
                                    <select name="transmission_type" class="form-select">
                                        <option value="Automatic" {{ ($car->transmission_type == 'Automatic' || $car->transmission == 'Automatic') ? 'selected' : '' }}>{{ translate('Automatic') }}</option>
                                        <option value="Manual" {{ ($car->transmission_type == 'Manual' || $car->transmission == 'Manual') ? 'selected' : '' }}>{{ translate('Manual') }}</option>
                                    </select>
                                </div>

                                {{-- Air Conditioning --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label fw-medium">{{ translate('Climate Control / A/C') }}</label>
                                    <select name="air_conditioning" class="form-select">
                                        <option value="1" {{ $car->air_conditioning ? 'selected' : '' }}>{{ translate('Yes (Equipped)') }}</option>
                                        <option value="0" {{ !$car->air_conditioning ? 'selected' : '' }}>{{ translate('No') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Section 3: Chauffeur Pricing & Hours --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">payments</span>
                                {{ translate('Chauffeur Pricing & Minimum Booking') }}
                            </h4>
                            @php
                                $serviceType = $car->service_type ?? 'hourly';
                            @endphp
                            <div class="row g-3">
                                {{-- Service Type --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Service Pricing Mode') }}</label>
                                    <select name="service_type" id="chauffeur_service_mode" class="form-select" required>
                                        <option value="hourly" {{ $serviceType == 'hourly' ? 'selected' : '' }}>{{ translate('Hourly Hire / As Directed') }}</option>
                                        <option value="full_day" {{ $serviceType == 'full_day' ? 'selected' : '' }}>{{ translate('Full Day Package Only') }}</option>
                                        <option value="both" {{ $serviceType == 'both' ? 'selected' : '' }}>{{ translate('Both (Hourly & Full Day)') }}</option>
                                    </select>
                                </div>

                                {{-- Hourly Rate --}}
                                <div class="col-md-4" id="wrap_hourly_rate" style="{{ $serviceType == 'full_day' ? 'display: none;' : '' }}">
                                    <label class="form-label required-field fw-medium">{{ translate('Hourly Rate') }} ({{ currency_symbol() }}/hr)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="hourly_rate" id="chauffeur_hourly_input" class="form-control fw-bold text-primary" 
                                               value="{{ $car->hourly_rate ?? 45.00 }}" step="0.01">
                                    </div>
                                </div>

                                {{-- Minimum Booking Hours --}}
                                <div class="col-md-4" id="wrap_min_hours" style="{{ $serviceType == 'full_day' ? 'display: none;' : '' }}">
                                    <label class="form-label required-field fw-medium">{{ translate('Minimum Booking Hours') }}</label>
                                    <select name="min_booking_hours" class="form-select">
                                        @php $minH = $car->min_booking_hours ?? 3; @endphp
                                        <option value="1" {{ $minH == 1 ? 'selected' : '' }}>1 {{ translate('Hour') }}</option>
                                        <option value="2" {{ $minH == 2 ? 'selected' : '' }}>2 {{ translate('Hours') }}</option>
                                        <option value="3" {{ $minH == 3 ? 'selected' : '' }}>3 {{ translate('Hours (Recommended)') }}</option>
                                        <option value="4" {{ $minH == 4 ? 'selected' : '' }}>4 {{ translate('Hours') }}</option>
                                        <option value="5" {{ $minH == 5 ? 'selected' : '' }}>5 {{ translate('Hours') }}</option>
                                        <option value="8" {{ $minH == 8 ? 'selected' : '' }}>8 {{ translate('Hours (Full Day)') }}</option>
                                    </select>
                                </div>

                                {{-- Full Day Rate --}}
                                <div class="col-md-4" id="wrap_daily_rate" style="{{ $serviceType == 'hourly' ? 'display: none;' : '' }}">
                                    <label class="form-label fw-medium">{{ translate('Full Day (8-Hour) Package Rate') }} ({{ currency_symbol() }})</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="daily_rate" class="form-control" 
                                               value="{{ $car->daily_rate ?? 0 }}" placeholder="320.00" step="0.01">
                                    </div>
                                </div>

                                {{-- Available Hours --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Chauffeur Service Hours') }}</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="time" name="available_hours_start" class="form-control" value="{{ $car->available_hours_start ?? '07:00' }}">
                                        </div>
                                        <div class="col-6">
                                            <input type="time" name="available_hours_end" class="form-control" value="{{ $car->available_hours_end ?? '23:00' }}">
                                        </div>
                                    </div>
                                </div>

                                {{-- Preferred Areas --}}
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">{{ translate('Service Coverage Areas / Airports') }}</label>
                                    <input type="text" name="preferred_areas" class="form-control" 
                                           value="{{ $car->preferred_areas }}" placeholder="{{ translate('e.g. Greater London, M25, Heathrow, Gatwick, City Airport, Birmingham...') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: VIP Inclusions & Amenities --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">check_circle</span>
                                {{ translate('VIP Inclusions & Amenities (What customer receives)') }}
                            </h4>
                            @php
                                $amenities = is_array($car->amenities) ? $car->amenities : (json_decode($car->amenities, true) ?? []);
                            @endphp
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="wifi" class="form-check-input mt-0" {{ in_array('wifi', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Free Onboard Wi-Fi') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="water" class="form-check-input mt-0" {{ in_array('water', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Complimentary Bottled Water') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="chargers" class="form-check-input mt-0" {{ in_array('chargers', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Phone Charging (Lightning & Type-C)') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="meet_and_greet" class="form-check-input mt-0" {{ in_array('meet_and_greet', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Airport Meet & Greet (Name Board)') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="child_seat" class="form-check-input mt-0" {{ in_array('child_seat', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Child / Booster Seat on Request') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="suited_chauffeur" class="form-check-input mt-0" {{ in_array('suited_chauffeur', $amenities) ? 'checked' : '' }}>
                                        <span>{{ translate('Professional Suited Chauffeur') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{-- Section 5: Gallery & Compliance Documents --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">photo_library</span>
                                {{ translate('Vehicle Gallery & Operator Documents') }}
                            </h4>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Car Images (Manage Gallery)') }}</label>
                                    <div class="upload-box" onclick="document.getElementById('chauffeur_images').click()">
                                        <span class="material-icons text-primary fs-36 mb-2">add_photo_alternate</span>
                                        <p class="mb-1 fw-medium text-dark">{{ translate('Click to upload new photos') }}</p>
                                        <span class="fz-12 text-muted">{{ translate('JPG, PNG up to 10MB each. Multiple files allowed.') }}</span>
                                    </div>
                                    <input type="file" name="images[]" id="chauffeur_images" multiple class="d-none" accept="image/*">
                                    <input type="hidden" name="images_edited" value="1">
                                    
                                    {{-- Existing Photos --}}
                                    <div class="mt-3" id="existing_photos_wrap" style="{{ empty($car->images) ? 'display: none;' : '' }}">
                                        <label class="fz-12 text-muted fw-bold d-block mb-2">
                                            {{ translate('Current Photos') }} (<span id="existing_count">{{ count($car->images ?? []) }}</span>):
                                        </label>
                                        <div class="image-preview-container" id="existing_images_container">
                                            @if(!empty($car->images))
                                                @foreach($car->images as $img)
                                                    @if($img)
                                                        <div class="image-preview-item" id="img_item_{{ $loop->index }}">
                                                            <img src="{{ asset('storage/app/public/car/' . $img) }}" alt="Car Image">
                                                            <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                                            <button type="button" class="delete-img-btn" onclick="removeCarImage('{{ $car->id }}', '{{ $img }}', 'img_item_{{ $loop->index }}')" title="{{ translate('Delete photo') }}">
                                                                <span class="material-icons">close</span>
                                                            </button>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>

                                    {{-- New Selected Photos Preview --}}
                                    <div class="mt-3" id="new_photos_wrap" style="display: none;">
                                        <label class="fz-12 text-success fw-bold d-block mb-2">{{ translate('New Selected Photos to Upload') }}:</label>
                                        <div id="chauffeur_images_preview" class="image-preview-container"></div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Private Hire / Chauffeur License (PCO/PHV)') }}</label>
                                            <input type="file" name="driving_license" class="form-control" accept="image/*,application/pdf">
                                            @if($car->driving_license)
                                                <a href="{{ asset('storage/app/public/car/documents/' . $car->driving_license) }}" target="_blank" class="fz-12 text-success">
                                                    <span class="material-icons fz-14 align-middle">check_circle</span> {{ translate('View uploaded file') }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Vehicle Registration (V5C)') }}</label>
                                            <input type="file" name="vehicle_registration" class="form-control" accept="image/*,application/pdf">
                                            @if($car->vehicle_registration)
                                                <a href="{{ asset('storage/app/public/car/documents/' . $car->vehicle_registration) }}" target="_blank" class="fz-12 text-success">
                                                    <span class="material-icons fz-14 align-middle">check_circle</span> {{ translate('View uploaded file') }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Hire & Reward / Commercial Chauffeur Insurance') }}</label>
                                            <input type="file" name="insurance_documents" class="form-control" accept="image/*,application/pdf">
                                            @if($car->insurance_documents)
                                                <a href="{{ asset('storage/app/public/car/documents/' . $car->insurance_documents) }}" target="_blank" class="fz-12 text-success">
                                                    <span class="material-icons fz-14 align-middle">check_circle</span> {{ translate('View uploaded file') }}
                                                </a>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('MOT Certificate') }}</label>
                                            <input type="file" name="mot_certificate" class="form-control" accept="image/*,application/pdf">
                                            @if($car->mot_certificate)
                                                <a href="{{ asset('storage/app/public/car/documents/' . $car->mot_certificate) }}" target="_blank" class="fz-12 text-success">
                                                    <span class="material-icons fz-14 align-middle">check_circle</span> {{ translate('View uploaded file') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                            <a href="{{ route('provider.car.index') }}" class="btn btn--secondary px-4">{{ translate('Cancel') }}</a>
                            <button type="submit" class="btn btn--primary px-5 fw-bold">{{ translate('Update Chauffeur Vehicle') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $('.tier-card').on('click', function() {
            $('.tier-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        $('#chauffeur_brand_select').on('change', function() {
            let brandVal = $(this).val();
            let selectedOpt = $(this).find(':selected');
            let brandId = selectedOpt.data('id');

            if (brandVal === 'other') {
                $('#chauffeur_custom_brand_input').show().prop('required', true);
                $('#chauffeur_model_select').hide();
                $('#chauffeur_model_input').show().val('').prop('required', true);
                return;
            } else {
                $('#chauffeur_custom_brand_input').hide().prop('required', false);
            }

            if (brandId) {
                $.ajax({
                    url: "{{ url('provider/car/ajax/models-by-brand') }}/" + brandId,
                    type: "GET",
                    dataType: "json",
                    success: function(models) {
                        let $modelSelect = $('#chauffeur_model_select');
                        $modelSelect.empty();
                        $modelSelect.append('<option value="">{{ translate('-- Select Model --') }}</option>');

                        if (models && models.length > 0) {
                            models.forEach(function(m) {
                                $modelSelect.append('<option value="' + m.name + '">' + m.name + '</option>');
                            });
                            $modelSelect.append('<option value="other">{{ translate('+ Enter Custom Model') }}</option>');
                            $modelSelect.show();
                            $('#chauffeur_model_input').hide().val('');
                        } else {
                            $modelSelect.hide();
                            $('#chauffeur_model_input').show().prop('required', true);
                        }
                    },
                    error: function() {
                        $('#chauffeur_model_select').hide();
                        $('#chauffeur_model_input').show().prop('required', true);
                    }
                });
            } else {
                $('#chauffeur_model_select').hide();
                $('#chauffeur_model_input').show().prop('required', true);
            }
        });

        $('#chauffeur_model_select').on('change', function() {
            if ($(this).val() === 'other') {
                $('#chauffeur_model_input').show().val('').focus().prop('required', true);
            } else {
                $('#chauffeur_model_input').hide().val($(this).val()).prop('required', true);
            }
        });

        $('#chauffeur_service_mode').on('change', function() {
            let mode = $(this).val();
            if (mode === 'hourly') {
                $('#wrap_hourly_rate').show();
                $('#wrap_min_hours').show();
                $('#wrap_daily_rate').hide();
            } else if (mode === 'full_day') {
                $('#wrap_hourly_rate').hide();
                $('#wrap_min_hours').hide();
                $('#wrap_daily_rate').show();
            } else {
                $('#wrap_hourly_rate').show();
                $('#wrap_min_hours').show();
                $('#wrap_daily_rate').show();
            }
        });

        function removeCarImage(carId, imageName, elementId) {
            if (confirm("{{ translate('Are you sure you want to delete this photo?') }}")) {
                $.ajax({
                    url: "{{ url('provider/car/delete-image') }}/" + carId,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        image_name: imageName
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $('#' + elementId).css('opacity', '0.3');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#' + elementId).fadeOut(300, function() {
                                $(this).remove();
                                let remaining = $('#existing_images_container .image-preview-item').length;
                                $('#existing_count').text(remaining);
                                if (remaining === 0) {
                                    $('#existing_photos_wrap').hide();
                                }
                            });
                            toastr.success(response.message || "{{ translate('Image deleted successfully') }}");
                        } else {
                            $('#' + elementId).css('opacity', '1');
                            toastr.error(response.message || "{{ translate('Failed to delete image') }}");
                        }
                    },
                    error: function() {
                        // In case of any error, still remove it from DOM and form input so on Update it gets deleted
                        $('#' + elementId).fadeOut(300, function() {
                            $(this).remove();
                            let remaining = $('#existing_images_container .image-preview-item').length;
                            $('#existing_count').text(remaining);
                            if (remaining === 0) {
                                $('#existing_photos_wrap').hide();
                            }
                        });
                        toastr.info("{{ translate('Image removed. Click Update to save changes.') }}");
                    }
                });
            }
        }

        document.getElementById('chauffeur_images').addEventListener('change', function(e) {
            const preview = document.getElementById('chauffeur_images_preview');
            const wrap = document.getElementById('new_photos_wrap');
            preview.innerHTML = '';
            const files = e.target.files;

            if (files && files.length > 0) {
                wrap.style.display = 'block';
                for (let i = 0; i < files.length; i++) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const item = document.createElement('div');
                        item.className = 'image-preview-item';
                        item.innerHTML = `
                            <img src="${e.target.result}">
                            <span class="new-badge">{{ translate('New') }}</span>
                        `;
                        preview.appendChild(item);
                    }
                    reader.readAsDataURL(files[i]);
                }
            } else {
                wrap.style.display = 'none';
            }
        });
    </script>
@endpush
