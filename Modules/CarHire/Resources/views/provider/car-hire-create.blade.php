@extends('providermanagement::layouts.master')

@section('title', translate('Add Car Hire Service'))

@push('css_or_js')
    <style>
        :root {
            --primary: var(--c1, #0461A5);
            --secondary: #6c757d;
            --success: #28a745;
            --border-light: #f1f1f1;
        }

        .step-container {
            display: none;
            animation: fadeIn 0.4s ease-in-out;
        }

        .step-container.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .wizard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            position: relative;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .wizard-header::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 10%;
            right: 10%;
            height: 2px;
            background: #eee;
            z-index: 1;
        }

        .step-indicator {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .step-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #eee;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #999;
            transition: 0.3s;
        }

        .step-indicator.active .step-circle {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
            box-shadow: 0 0 0 4px rgba(4, 97, 165, 0.2);
        }

        .step-indicator.done .step-circle {
            border-color: var(--success);
            background: var(--success);
            color: #fff;
        }

        .step-label {
            font-size: 12px;
            font-weight: 600;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .step-indicator.active .step-label {
            color: var(--primary);
        }

        .form-card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border-radius: 15px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title span {
            color: var(--primary);
        }

        .upload-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .image-upload-wrapper {
            position: relative;
        }

        .image-upload-box {
            border: 2px dashed #d9d9d9;
            border-radius: 12px;
            background: #fafafa;
            height: 150px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            overflow: hidden;
            transition: 0.3s;
        }

        .image-upload-box:hover {
            border-color: var(--primary);
            background: #fff;
        }

        .image-upload-box .placeholder {
            text-align: center;
            color: #888;
        }

        .image-upload-box .placeholder i {
            font-size: 2.2rem;
            margin-bottom: 6px;
            display: block;
        }

        .image-upload-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .upload-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-top: 8px;
            text-align: center;
        }

        .remove-img-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            color: #ff4d4d;
            border: none;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 5;
        }

        @media (max-width: 576px) {
            .upload-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title text-center">{{ translate('Register Car for Hire (Self-Drive)') }}</h2>
                <p class="text-center text-muted">
                    {{ translate('List your vehicle with clear daily rates, fuel policy, and mileage rules for seamless customer bookings.') }}
                </p>
            </div>

            <!-- Wizard Header -->
            <div class="wizard-header">
                <div class="step-indicator active" id="ind-1" onclick="goToStep(1)">
                    <div class="step-circle">1</div>
                    <div class="step-label">{{ translate('Vehicle') }}</div>
                </div>
                <div class="step-indicator" id="ind-2" onclick="goToStep(2)">
                    <div class="step-circle">2</div>
                    <div class="step-label">{{ translate('Pricing & Rules') }}</div>
                </div>
                <div class="step-indicator" id="ind-3" onclick="goToStep(3)">
                    <div class="step-circle">3</div>
                    <div class="step-label">{{ translate('Terms & Docs') }}</div>
                </div>
            </div>

            <div class="card form-card max-w-800 mx-auto">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('provider.car.store') }}" method="POST" enctype="multipart/form-data" id="car-hire-form">
                        @csrf
                        <input type="hidden" name="service_category" value="car_hire">
                        <input type="hidden" name="category_id" value="{{ $categories->firstWhere('name', 'Car Hire')?->id ?? ($categories->first()->id ?? '') }}">

                        <!-- Step 1: Vehicle Details -->
                        <div class="step-container active" id="step-1">
                            <h4 class="section-title">
                                <span class="material-icons">directions_car</span>
                                {{ translate('Vehicle Information') }}
                            </h4>
                            <div class="row g-3">
                                {{-- Brand --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Car Brand / Make') }}</label>
                                    <select name="brand" id="car_brand_select" class="form-select" required>
                                        <option value="">{{ translate('-- Select Brand --') }}</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->name }}" data-id="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                        <option value="other">{{ translate('+ Other / Custom Brand') }}</option>
                                    </select>
                                    <input type="text" name="custom_brand" id="custom_brand_input" class="form-control mt-2" 
                                           placeholder="{{ translate('Type brand name...') }}" style="display: none;">
                                </div>

                                {{-- Model --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Car Model') }}</label>
                                    <select name="model_select" id="car_model_select" class="form-select">
                                        <option value="">{{ translate('-- Select Model --') }}</option>
                                        <option value="other">{{ translate('+ Enter Custom Model') }}</option>
                                    </select>
                                    <input type="text" name="model" id="car_model_input" class="form-control mt-2" 
                                           placeholder="{{ translate('e.g. 3 Series, C-Class, A4, Golf...') }}" required>
                                </div>

                                {{-- Registration Number --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Registration Number (Plate)') }}</label>
                                    <input type="text" name="registration_number" class="form-control text-uppercase" 
                                           placeholder="{{ translate('e.g. AB21 CDE') }}" required>
                                </div>

                                {{-- Manufacture Year --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Manufacture Year') }}</label>
                                    <input type="number" name="manufacture_year" class="form-control" 
                                           min="2000" max="{{ date('Y') + 1 }}" value="{{ date('Y') - 2 }}" placeholder="2023">
                                </div>

                                {{-- Vehicle Type / Body Style --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Vehicle Category / Type') }}</label>
                                    <select name="car_type_id" class="form-select" required>
                                        <option value="" disabled selected>{{ translate('-- Select Type --') }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Transmission --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Transmission') }}</label>
                                    <select name="transmission_type" class="form-select" required>
                                        <option value="Automatic">{{ translate('Automatic') }}</option>
                                        <option value="Manual">{{ translate('Manual') }}</option>
                                    </select>
                                </div>

                                {{-- Fuel Type --}}
                                <div class="col-sm-6">
                                    <label class="form-label required-field fw-medium">{{ translate('Fuel Type') }}</label>
                                    <select name="fuel_type" class="form-select" required>
                                        @foreach($fuelTypes as $ft)
                                            <option value="{{ $ft->name }}">{{ $ft->name }}</option>
                                        @endforeach
                                        <option value="Hybrid">{{ translate('Hybrid') }}</option>
                                        <option value="Petrol">{{ translate('Petrol') }}</option>
                                    </select>
                                </div>

                                {{-- Seating Capacity --}}
                                <div class="col-sm-6">
                                    <label class="form-label fw-medium">{{ translate('Seating Capacity') }}</label>
                                    <input type="number" name="seating_capacity" class="form-control" value="5" min="2" max="15">
                                </div>

                                {{-- Air Conditioning --}}
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="air_conditioning" value="1" id="air_con" checked>
                                        <label class="form-check-label fw-medium" for="air_con">
                                            {{ translate('Air Conditioning (A/C) Equipped') }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Photos --}}
                            <div class="mt-4 mb-3">
                                <h4 class="section-title">
                                    <span class="material-icons">photo_camera</span>
                                    {{ translate('Vehicle Photos') }}
                                </h4>
                                <p class="small text-muted mb-3">
                                    {{ translate('Provide clear exterior and interior photos to help customers choose your car.') }}
                                </p>

                                <div class="upload-grid">
                                    @foreach (['front_view', 'rear_view', 'interior', 'dashboard'] as $view)
                                        <div class="image-upload-wrapper">
                                            <button type="button" class="remove-img-btn" onclick="clearImg('{{ $view }}')">
                                                <span class="material-icons">close</span>
                                            </button>
                                            <div class="image-upload-box" id="box_{{ $view }}" onclick="document.getElementById('input_{{ $view }}').click()">
                                                <div class="placeholder" id="placeholder_{{ $view }}">
                                                    <i class="material-icons">add_a_photo</i>
                                                    <span>{{ translate('Upload') }}</span>
                                                </div>
                                                <img src="" id="preview_{{ $view }}">
                                            </div>
                                            <span class="upload-label">{{ translate(str_replace('_', ' ', $view)) }}</span>
                                            <input type="file" name="car_images[{{ $view }}]" id="input_{{ $view }}" class="d-none" accept="image/*" onchange="previewFile(this, '{{ $view }}')">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-3">
                                <button type="button" class="btn btn--primary px-5 h-45" onclick="goToStep(2)">{{ translate('Continue to Pricing & Rules') }}</button>
                            </div>
                        </div>

                        <!-- Step 2: Pricing & Rental Rules -->
                        <div class="step-container" id="step-2">
                            <h4 class="section-title">
                                <span class="material-icons">payments</span>
                                {{ translate('Rental Rates & Deposit') }}
                            </h4>
                            <div class="row g-3">
                                {{-- Daily Rate (Primary) --}}
                                <div class="col-md-6">
                                    <label class="form-label required-field fw-medium">
                                        {{ translate('Daily Rental Rate') }} ({{ currency_symbol() }}/day) *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="daily_rate" class="form-control fw-bold fs-16 text-primary" placeholder="65.00" step="0.01" min="1" required>
                                    </div>
                                    <span class="fz-11 text-muted">{{ translate('Standard 24-hour rental rate.') }}</span>
                                </div>

                                {{-- Hourly Rate (Secondary / Extra) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">
                                        {{ translate('Hourly Rate (Optional / Extra hours)') }} ({{ currency_symbol() }}/hr)
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="hourly_rate" class="form-control" placeholder="10.00" step="0.01" min="0">
                                    </div>
                                    <span class="fz-11 text-muted">{{ translate('Leave blank or 0 if daily only.') }}</span>
                                </div>

                                {{-- Security Deposit --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Refundable Security Deposit') }} ({{ currency_symbol() }})</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="security_deposit" class="form-control" placeholder="250.00" step="0.01" min="0">
                                    </div>
                                </div>

                                {{-- Minimum Driver Age --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Minimum Driver Age') }}</label>
                                    <select name="min_driver_age" class="form-select">
                                        <option value="21">21+ {{ translate('years old') }}</option>
                                        <option value="23">23+ {{ translate('years old') }}</option>
                                        <option value="25" selected>25+ {{ translate('years old (Standard)') }}</option>
                                        <option value="30">30+ {{ translate('years old (Prestige)') }}</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-4">
                                    <h4 class="section-title">
                                        <span class="material-icons">tune</span>
                                        {{ translate('Mileage & Fuel Policy') }}
                                    </h4>
                                </div>

                                {{-- Mileage Limit --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Mileage Allowance') }}</label>
                                    <select name="mileage_limit" class="form-select">
                                        <option value="Unlimited" selected>{{ translate('Unlimited Miles') }}</option>
                                        <option value="100 miles/day">100 {{ translate('miles / day') }}</option>
                                        <option value="150 miles/day">150 {{ translate('miles / day') }}</option>
                                        <option value="200 miles/day">200 {{ translate('miles / day') }}</option>
                                        <option value="250 miles/day">250 {{ translate('miles / day') }}</option>
                                    </select>
                                </div>

                                {{-- Extra Mileage Charge --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Extra Mileage Charge') }} ({{ currency_symbol() }}/mile)</label>
                                    <input type="number" name="extra_mileage_charge" class="form-control" placeholder="0.25" step="0.01" min="0">
                                    <span class="fz-11 text-muted">{{ translate('Charged per mile if daily limit exceeded.') }}</span>
                                </div>

                                {{-- Fuel Policy --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Fuel Policy') }}</label>
                                    <select name="fuel_policy" class="form-select">
                                        <option value="Full to Full" selected>{{ translate('Full to Full (Return with full tank)') }}</option>
                                        <option value="Same to Same">{{ translate('Same to Same (Return at same fuel level)') }}</option>
                                    </select>
                                </div>

                                {{-- Service Options (Pickup / Delivery) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Available For') }}</label>
                                    <select name="available_for" id="available_for_select" class="form-select">
                                        <option value="Both" selected>{{ translate('Both (Garage Pickup & Doorstep Delivery)') }}</option>
                                        <option value="Pickup">{{ translate('Garage Pickup Only') }}</option>
                                        <option value="Delivery">{{ translate('Doorstep Delivery Only') }}</option>
                                    </select>
                                </div>

                                {{-- Delivery Fee --}}
                                <div class="col-md-6" id="delivery_fee_wrapper">
                                    <label class="form-label fw-medium">{{ translate('Doorstep Delivery Fee') }} ({{ currency_symbol() }})</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                        <input type="number" name="delivery_fee" class="form-control" placeholder="20.00" step="0.01" min="0">
                                    </div>
                                    <span class="fz-11 text-muted">{{ translate('Flat delivery fee charged if customer requests home delivery.') }}</span>
                                </div>

                                <div class="col-12 mt-4">
                                    <h4 class="section-title">
                                        <span class="material-icons">location_on</span>
                                        {{ translate('Collection Location & Hours') }}
                                    </h4>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Postcode') }}</label>
                                    <input type="text" name="postcode" class="form-control" placeholder="{{ translate('e.g. TW6 1AP') }}">
                                </div>

                                <div class="col-md-6">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label fw-medium">{{ translate('Open From') }}</label>
                                            <input type="time" name="available_hours_start" class="form-control" value="09:00">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-medium">{{ translate('Open Till') }}</label>
                                            <input type="time" name="available_hours_end" class="form-control" value="18:00">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-medium">{{ translate('Garage Collection Address') }}</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="{{ translate('Address where customer will pick up vehicle...') }}"></textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5 pt-3">
                                <button type="button" class="btn btn-outline-secondary px-4 h-45" onclick="goToStep(1)">{{ translate('Back') }}</button>
                                <button type="button" class="btn btn--primary px-5 h-45" onclick="goToStep(3)">{{ translate('Next: Policies & Docs') }}</button>
                            </div>
                        </div>

                        <!-- Step 3: Terms & Documents -->
                        <div class="step-container" id="step-3">
                            <h4 class="section-title">
                                <span class="material-icons">gavel</span>
                                {{ translate('Rental Terms & Verification Documents') }}
                            </h4>

                            <div class="form-group mb-4">
                                <label class="form-label fw-medium">{{ translate('Rental Terms & Conditions') }}</label>
                                <textarea name="terms_conditions" class="form-control" rows="4" 
                                          placeholder="{{ translate('State any specific rules: e.g. No smoking inside, clean return required, international license accepted...') }}"></textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Driving License (Owner / Operator ID)') }}</label>
                                    <input type="file" name="driving_license" class="form-control" accept="image/*,application/pdf">
                                    <small class="text-muted">{{ translate('ID proof for operator.') }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Vehicle Registration (V5C / Logbook)') }}</label>
                                    <input type="file" name="vehicle_registration" class="form-control" accept="image/*,application/pdf">
                                    <small class="text-muted">{{ translate('Proof of vehicle ownership / operation rights.') }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('Commercial Hire Insurance Certificate') }}</label>
                                    <input type="file" name="insurance_documents" class="form-control" accept="image/*,application/pdf">
                                    <small class="text-muted">{{ translate('Valid commercial self-drive hire insurance.') }}</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">{{ translate('MOT / Roadworthiness Certificate') }}</label>
                                    <input type="file" name="mot_certificate" class="form-control" accept="image/*,application/pdf">
                                    <small class="text-muted">{{ translate('Current valid MOT test document.') }}</small>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-5 pt-3">
                                <button type="button" class="btn btn-outline-secondary px-4 h-45" onclick="goToStep(2)">{{ translate('Back') }}</button>
                                <button type="submit" class="btn btn--primary px-5 h-45 fw-bold">{{ translate('Submit & Publish Vehicle') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        function goToStep(step) {
            document.querySelectorAll('.step-container').forEach(c => c.classList.remove('active'));
            document.getElementById('step-' + step).classList.add('active');

            document.querySelectorAll('.step-indicator').forEach((ind, i) => {
                ind.classList.remove('active', 'done');
                if (i + 1 < step) ind.classList.add('done');
                if (i + 1 === step) ind.classList.add('active');
            });
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        // Handle brand and dynamic models
        $('#car_brand_select').on('change', function() {
            let brandVal = $(this).val();
            let selectedOpt = $(this).find(':selected');
            let brandId = selectedOpt.data('id');

            if (brandVal === 'other') {
                $('#custom_brand_input').show().prop('required', true);
                $('#car_model_select').hide();
                $('#car_model_input').show().val('').prop('required', true);
                return;
            } else {
                $('#custom_brand_input').hide().prop('required', false);
            }

            if (brandId) {
                $.ajax({
                    url: "{{ url('provider/car/ajax/models-by-brand') }}/" + brandId,
                    type: "GET",
                    dataType: "json",
                    success: function(models) {
                        let $modelSelect = $('#car_model_select');
                        $modelSelect.empty();
                        $modelSelect.append('<option value="">{{ translate('-- Select Model --') }}</option>');

                        if (models && models.length > 0) {
                            models.forEach(function(m) {
                                $modelSelect.append('<option value="' + m.name + '">' + m.name + '</option>');
                            });
                            $modelSelect.append('<option value="other">{{ translate('+ Enter Custom Model') }}</option>');
                            $modelSelect.show();
                            $('#car_model_input').hide().val('');
                        } else {
                            $modelSelect.hide();
                            $('#car_model_input').show().prop('required', true);
                        }
                    },
                    error: function() {
                        $('#car_model_select').hide();
                        $('#car_model_input').show().prop('required', true);
                    }
                });
            } else {
                $('#car_model_select').hide();
                $('#car_model_input').show().prop('required', true);
            }
        });

        $('#car_model_select').on('change', function() {
            if ($(this).val() === 'other') {
                $('#car_model_input').show().val('').focus().prop('required', true);
            } else {
                $('#car_model_input').hide().val($(this).val()).prop('required', true);
            }
        });

        // Available for toggle delivery fee
        $('#available_for_select').on('change', function() {
            if ($(this).val() === 'Pickup') {
                $('#delivery_fee_wrapper').hide();
            } else {
                $('#delivery_fee_wrapper').show();
            }
        });

        // Image previews
        function previewFile(input, view) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview_' + view).attr('src', e.target.result).show();
                    $('#placeholder_' + view).hide();
                    $('#box_' + view).siblings('.remove-img-btn').show();
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function clearImg(view) {
            $('#input_' + view).val('');
            $('#preview_' + view).attr('src', '').hide();
            $('#placeholder_' + view).show();
            $('#box_' + view).siblings('.remove-img-btn').hide();
        }
    </script>
@endpush
