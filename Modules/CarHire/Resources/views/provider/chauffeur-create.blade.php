@extends('providermanagement::layouts.master')

@section('title', translate('Add Chauffeur Vehicle & Service'))

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
        }

        .image-preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
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
            <div class="page-title-wrap mb-4">
                <h2 class="page-title">{{ translate('Register Vehicle for Chauffeur Services') }}</h2>
                <p class="text-muted fz-14">{{ translate('Add luxury chauffeur vehicle with minimum booking hours, luggage allowance, and premium VIP inclusions.') }}</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('provider.car.store') }}" method="POST" enctype="multipart/form-data" id="chauffeur-form">
                        @csrf
                        <input type="hidden" name="service_category" value="chauffeur">
                        <input type="hidden" name="category_id" value="{{ $categories->firstWhere('name', 'Chauffeur')?->id ?? ($categories->firstWhere('name', 'Chauffeur Service')?->id ?? ($categories->first()->id ?? '')) }}">

                        {{-- Section 1: Chauffeur Service Tier / Class --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">stars</span>
                                {{ translate('Select Chauffeur Class / Tier') }}
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card selected d-block">
                                        <input type="radio" name="chauffeur_tier" value="business_class" class="d-none" checked>
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('Business Class') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes E-Class, BMW 5 Series, Audi A6') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card d-block">
                                        <input type="radio" name="chauffeur_tier" value="first_class" class="d-none">
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('First Class / Luxury') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes S-Class, BMW 7 Series, Range Rover') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card d-block">
                                        <input type="radio" name="chauffeur_tier" value="luxury_mpv" class="d-none">
                                        <div class="fw-bold fs-15 text-dark mb-1">{{ translate('Luxury MPV / Group') }}</div>
                                        <div class="fz-12 text-muted">{{ translate('Mercedes V-Class (Up to 7-8 Passengers)') }}</div>
                                    </label>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <label class="tier-card d-block">
                                        <input type="radio" name="chauffeur_tier" value="wedding" class="d-none">
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
                                            <option value="{{ $brand->name }}" data-id="{{ $brand->id }}">{{ $brand->name }}</option>
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
                                        <option value="other">{{ translate('+ Enter Custom Model') }}</option>
                                    </select>
                                    <input type="text" name="model" id="chauffeur_model_input" class="form-control mt-2" 
                                           placeholder="{{ translate('e.g. S-Class 350d, E-Class, V-Class...') }}" required>
                                </div>

                                {{-- Registration Number --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Registration Number (Plate)') }}</label>
                                    <input type="text" name="registration_number" class="form-control text-uppercase" 
                                           placeholder="{{ translate('e.g. LD69 XYZ') }}" required>
                                </div>

                                {{-- Vehicle Type --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Vehicle Category / Type') }}</label>
                                    <select name="car_type_id" class="form-select" required>
                                        <option value="" disabled selected>{{ translate('-- Select Type --') }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Manufacture Year --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Manufacture Year') }}</label>
                                    <input type="number" name="manufacture_year" class="form-control" 
                                           min="2010" max="{{ date('Y') + 1 }}" value="{{ date('Y') - 1 }}">
                                </div>

                                {{-- Fuel Type --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Fuel Type') }}</label>
                                    <select name="fuel_type" class="form-select">
                                        @foreach($fuelTypes as $ft)
                                            <option value="{{ $ft->name }}">{{ $ft->name }}</option>
                                        @endforeach
                                        <option value="Hybrid">{{ translate('Hybrid') }}</option>
                                        <option value="Electric">{{ translate('Electric') }}</option>
                                    </select>
                                </div>

                                {{-- Passenger Capacity --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label required-field fw-medium">{{ translate('Max Passengers') }}</label>
                                    <input type="number" name="seating_capacity" class="form-control" value="3" min="1" max="16" required>
                                </div>

                                {{-- Luggage Capacity (Crucial for Chauffeur) --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label required-field fw-medium">{{ translate('Large Suitcases Capacity') }}</label>
                                    <input type="number" name="luggage_capacity" class="form-control" value="2" min="0" max="10" required>
                                    <span class="fz-11 text-muted">{{ translate('Standard check-in bags (23kg).') }}</span>
                                </div>

                                {{-- Transmission --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label fw-medium">{{ translate('Transmission') }}</label>
                                    <select name="transmission_type" class="form-select">
                                        <option value="Automatic" selected>{{ translate('Automatic') }}</option>
                                        <option value="Manual">{{ translate('Manual') }}</option>
                                    </select>
                                </div>

                                {{-- Air Conditioning --}}
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label fw-medium">{{ translate('Climate Control / A/C') }}</label>
                                    <select name="air_conditioning" class="form-select">
                                        <option value="1" selected>{{ translate('Yes (Equipped)') }}</option>
                                        <option value="0">{{ translate('No') }}</option>
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
                            <div class="row g-3">
                                {{-- Service Type --}}
                                <div class="col-md-4">
                                    <label class="form-label required-field fw-medium">{{ translate('Service Pricing Mode') }}</label>
                                    <select name="service_type" id="chauffeur_service_mode" class="form-select" required>
                                        <option value="hourly" selected>{{ translate('Hourly Hire / As Directed') }}</option>
                                        <option value="full_day">{{ translate('Full Day Package Only') }}</option>
                                        <option value="both">{{ translate('Both (Hourly & Full Day)') }}</option>
                                    </select>
                                </div>

                                {{-- Hourly Rate --}}
                                <div class="col-md-4" id="wrap_hourly_rate">
                                    <label class="form-label required-field fw-medium">{{ translate('Hourly Rate') }} ({{ currency_symbol() }}/hr)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="hourly_rate" id="chauffeur_hourly_input" class="form-control fw-bold text-primary" placeholder="45.00" step="0.01" value="45.00">
                                    </div>
                                </div>

                                {{-- Minimum Booking Hours --}}
                                <div class="col-md-4" id="wrap_min_hours">
                                    <label class="form-label required-field fw-medium">{{ translate('Minimum Booking Hours') }}</label>
                                    <select name="min_booking_hours" class="form-select">
                                        <option value="1">1 {{ translate('Hour') }}</option>
                                        <option value="2">2 {{ translate('Hours') }}</option>
                                        <option value="3" selected>3 {{ translate('Hours (Recommended)') }}</option>
                                        <option value="4">4 {{ translate('Hours') }}</option>
                                        <option value="5">5 {{ translate('Hours') }}</option>
                                        <option value="8">8 {{ translate('Hours (Full Day)') }}</option>
                                    </select>
                                    <span class="fz-11 text-muted">{{ translate('Guarantees minimum earnings per booking.') }}</span>
                                </div>

                                {{-- Full Day Rate --}}
                                <div class="col-md-4" id="wrap_daily_rate" style="display: none;">
                                    <label class="form-label fw-medium">{{ translate('Full Day (8-Hour) Package Rate') }} ({{ currency_symbol() }})</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="daily_rate" class="form-control" placeholder="320.00" step="0.01">
                                    </div>
                                </div>

                                {{-- Available Hours --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-medium">{{ translate('Chauffeur Service Hours') }}</label>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <input type="time" name="available_hours_start" class="form-control" value="07:00">
                                        </div>
                                        <div class="col-6">
                                            <input type="time" name="available_hours_end" class="form-control" value="23:00">
                                        </div>
                                    </div>
                                </div>

                                {{-- Preferred Areas --}}
                                <div class="col-md-8">
                                    <label class="form-label fw-medium">{{ translate('Service Coverage Areas / Airports') }}</label>
                                    <input type="text" name="preferred_areas" class="form-control" 
                                           placeholder="{{ translate('e.g. Greater London, M25, Heathrow, Gatwick, City Airport, Birmingham...') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Section 4: VIP Inclusions & Amenities --}}
                        <div class="mb-4">
                            <h4 class="form-section-title">
                                <span class="material-icons text-primary">check_circle</span>
                                {{ translate('VIP Inclusions & Amenities (What customer receives)') }}
                            </h4>
                            <div class="row g-3">
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="wifi" class="form-check-input mt-0" checked>
                                        <span>{{ translate('Free Onboard Wi-Fi') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="water" class="form-check-input mt-0" checked>
                                        <span>{{ translate('Complimentary Bottled Water') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="chargers" class="form-check-input mt-0" checked>
                                        <span>{{ translate('Phone Charging (Lightning & Type-C)') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="meet_and_greet" class="form-check-input mt-0" checked>
                                        <span>{{ translate('Airport Meet & Greet (Name Board)') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="child_seat" class="form-check-input mt-0">
                                        <span>{{ translate('Child / Booster Seat on Request') }}</span>
                                    </label>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <label class="amenity-chip">
                                        <input type="checkbox" name="amenities[]" value="suited_chauffeur" class="form-check-input mt-0" checked>
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
                                    <label class="form-label required-field fw-medium">{{ translate('Car Images (Exterior & Luxury Cabin)') }}</label>
                                    <div class="upload-box" onclick="document.getElementById('chauffeur_images').click()">
                                        <span class="material-icons text-primary fs-36 mb-2">add_photo_alternate</span>
                                        <p class="mb-1 fw-medium text-dark">{{ translate('Click to upload vehicle photos') }}</p>
                                        <span class="fz-12 text-muted">{{ translate('JPG, PNG up to 10MB each. Multiple files allowed.') }}</span>
                                    </div>
                                    <input type="file" name="images[]" id="chauffeur_images" multiple class="d-none" accept="image/*">
                                    <div id="chauffeur_images_preview" class="image-preview-container"></div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Private Hire / Chauffeur License (PCO/PHV)') }}</label>
                                            <input type="file" name="driving_license" class="form-control" accept="image/*,application/pdf">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Vehicle Registration (V5C)') }}</label>
                                            <input type="file" name="vehicle_registration" class="form-control" accept="image/*,application/pdf">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('Hire & Reward / Commercial Chauffeur Insurance') }}</label>
                                            <input type="file" name="insurance_documents" class="form-control" accept="image/*,application/pdf">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-medium">{{ translate('MOT Certificate') }}</label>
                                            <input type="file" name="mot_certificate" class="form-control" accept="image/*,application/pdf">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-3 pt-3 border-top">
                            <a href="{{ route('provider.car.index') }}" class="btn btn--secondary px-4">{{ translate('Cancel') }}</a>
                            <button type="submit" class="btn btn--primary px-5 fw-bold">{{ translate('Submit & Publish Chauffeur Service') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // Chauffeur tier selector
        $('.tier-card').on('click', function() {
            $('.tier-card').removeClass('selected');
            $(this).addClass('selected');
            $(this).find('input[type="radio"]').prop('checked', true);
        });

        // Brand and Dynamic Models AJAX
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

        // Chauffeur service mode changer
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

        // Multi-image preview
        document.getElementById('chauffeur_images').addEventListener('change', function(e) {
            const preview = document.getElementById('chauffeur_images_preview');
            preview.innerHTML = '';
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = document.createElement('div');
                    item.className = 'image-preview-item';
                    item.innerHTML = `<img src="${e.target.result}">`;
                    preview.appendChild(item);
                }
                reader.readAsDataURL(files[i]);
            }
        });
    </script>
@endpush
