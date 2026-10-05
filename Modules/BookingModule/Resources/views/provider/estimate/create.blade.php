@extends('providermanagement::layouts.master')

@section('title', translate('Create_Quotation_&_Estimate'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-4">
                <div>
                    <h2 class="page-title mb-1">{{ translate('Create_Quotation_/_Booking_Estimate') }}</h2>
                    <p class="text-muted fz-14 mb-0">{{ translate('Initiate a booking quotation or car rental invite on behalf of a customer.') }}</p>
                </div>
                <div>
                    <a href="{{ route('provider.estimate.index') }}" class="btn btn--secondary d-flex align-items-center gap-2">
                        <span class="material-icons">arrow_back</span>
                        {{ translate('Back_to_List') }}
                    </a>
                </div>
            </div>

            <form action="{{ route('provider.estimate.store') }}" method="POST" enctype="multipart/form-data" id="estimate_form">
                @csrf

                {{-- Top Service Mode Selector (Garage vs Car Hire/Chauffeur) --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <label class="form-label fw-bold fz-14 text-dark mb-3 d-flex align-items-center gap-2">
                            <span class="material-icons text-primary">category</span>
                            {{ translate('Select Quotation Category / Service Type') }}
                        </label>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="service-type-card p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer w-100 position-relative h-100 active-type" id="card_car_hire">
                                    <input type="radio" name="module_type" value="car_hire" class="form-check-input mt-0 position-absolute" style="top: 16px; right: 16px;" checked>
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                                        <span class="material-icons fs-28">directions_car</span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">{{ translate('Car Hire & Chauffeur Services') }}</h5>
                                        <p class="fz-12 text-muted mb-0">{{ translate('Book a vehicle from your fleet for self-drive, doorstep delivery, or chauffeur ride.') }}</p>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="service-type-card p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer w-100 position-relative h-100" id="card_general">
                                    <input type="radio" name="module_type" value="general" class="form-check-input mt-0 position-absolute" style="top: 16px; right: 16px;">
                                    <div class="rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center flex-shrink-0" style="width: 52px; height: 52px;">
                                        <span class="material-icons fs-28">build</span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark">{{ translate('Automotive & Garage Repair Services') }}</h5>
                                        <p class="fz-12 text-muted mb-0">{{ translate('Quotes for vehicle repairs, bodywork, tyres, MOT, diagnostics, and maintenance.') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Left Column: Customer Info --}}
                    <div class="col-lg-5">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">person</span>
                                    {{ translate('Customer_Information') }}
                                </h4>
                            </div>
                            <div class="card-body p-4">
                                {{-- Quick Existing Customer Select --}}
                                <div class="mb-3">
                                    <label class="form-label fz-13 text-muted">{{ translate('Quick_Select_Existing_Customer (Optional)') }}</label>
                                    <select class="form-select" id="existing_customer_select">
                                        <option value="">{{ translate('-- Type or Select Existing Customer --') }}</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}"
                                                    data-name="{{ $c->first_name }} {{ $c->last_name }}"
                                                    data-phone="{{ $c->phone }}"
                                                    data-email="{{ $c->email }}">
                                                {{ $c->first_name }} {{ $c->last_name }} ({{ $c->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <span class="fz-11 text-muted">{{ translate('Or type customer details below directly for new customer.') }}</span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Customer_Name') }}</label>
                                        <input type="text" name="customer_name" id="customer_name" class="form-control" 
                                               placeholder="{{ translate('e.g. John Doe') }}" value="{{ old('customer_name') }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Customer_Phone') }}</label>
                                        <input type="text" name="customer_phone" id="customer_phone" class="form-control" 
                                               placeholder="{{ translate('e.g. +44 7123 456789') }}" value="{{ old('customer_phone') }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Customer_Email') }}</label>
                                        <input type="email" name="customer_email" id="customer_email" class="form-control" 
                                               placeholder="{{ translate('e.g. customer@example.com') }}" value="{{ old('customer_email') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Customer_Address / Location') }}</label>
                                        <textarea name="customer_address" id="customer_address" class="form-control" rows="2" 
                                                  placeholder="{{ translate('Customer home or business address...') }}">{{ old('customer_address') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- General Customer Vehicle Info (Only shown for Garage / Automotive Repair) --}}
                        <div class="card border-0 shadow-sm" id="section_customer_vehicle" style="display: none;">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">directions_car</span>
                                    {{ translate('Customer_Vehicle_Details') }} <span class="fz-12 text-muted fw-normal">({{ translate('Optional') }})</span>
                                </h4>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-medium">{{ translate('Car_Model / Make') }}</label>
                                        <input type="text" name="car_model" class="form-control" 
                                               placeholder="{{ translate('e.g. BMW 320d 2021') }}" value="{{ old('car_model') }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label fw-medium">{{ translate('Registration_Number') }}</label>
                                        <input type="text" name="car_registration_number" class="form-control text-uppercase" 
                                               placeholder="{{ translate('e.g. AB12 CDE') }}" value="{{ old('car_registration_number') }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Damage / Problem Description') }}</label>
                                        <textarea name="damage_description" class="form-control" rows="2" 
                                                  placeholder="{{ translate('Detail any work needed, body damage, or symptoms...') }}">{{ old('damage_description') }}</textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Vehicle_Photos / Inspection Images') }}</label>
                                        <input type="file" name="car_images[]" class="form-control" accept="image/*" multiple>
                                        <span class="fz-11 text-muted">{{ translate('JPG, PNG, WebP up to 10MB (You can select multiple photos)') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Service / Car Configuration & Pricing --}}
                    <div class="col-lg-7">
                        {{-- SECTION A: Car Hire & Chauffeur Configuration --}}
                        <div id="section_car_hire" class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">directions_car</span>
                                    {{ translate('Select Fleet Car & Rental Details') }}
                                </h4>
                                <a href="{{ route('provider.car.index') }}" target="_blank" class="fz-12 text-primary text-decoration-none">
                                    <span class="material-icons fz-14 align-middle">open_in_new</span>
                                    {{ translate('Manage Fleet Cars') }}
                                </a>
                            </div>
                            <div class="card-body p-4">
                                @if($cars->isEmpty())
                                    <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded-3 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-icons text-warning">warning</span>
                                            <div class="fz-13">
                                                <strong>{{ translate('No Fleet Cars Found!') }}</strong><br>
                                                {{ translate('You have not added any cars to your fleet yet. Please add cars in Car Management first.') }}
                                            </div>
                                        </div>
                                        <a href="{{ route('provider.car.create') }}" class="btn btn-sm btn-warning text-dark fw-semibold text-nowrap">
                                            {{ translate('Add Fleet Car') }}
                                        </a>
                                    </div>
                                @endif

                                <div class="row g-3">
                                    {{-- Car Select --}}
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Select_Vehicle_From_Your_Fleet') }}</label>
                                        <select class="form-select" name="car_id" id="car_id">
                                            <option value="">{{ translate('-- Choose a Fleet Car --') }}</option>
                                            @foreach($cars as $car)
                                                @php
                                                    $daily = floatval($car->daily_rate ?? $car->daily_rent ?? 0);
                                                    $hourly = floatval($car->hourly_rate ?? 0);
                                                    $delFee = floatval($car->delivery_fee ?? 0);
                                                    $minH = intval($car->min_booking_hours ?? 1);
                                                @endphp
                                                <option value="{{ $car->id }}"
                                                        data-brand="{{ $car->brand }}"
                                                        data-model="{{ $car->model }}"
                                                        data-year="{{ $car->year }}"
                                                        data-reg="{{ $car->registration_number }}"
                                                        data-pricing="{{ $car->pricing_type ?? 'daily' }}"
                                                        data-daily="{{ $daily }}"
                                                        data-hourly="{{ $hourly }}"
                                                        data-delivery-fee="{{ $delFee }}"
                                                        data-min-hours="{{ $minH }}"
                                                        data-service-cat="{{ $car->service_category }}">
                                                    @if($car->service_category === 'chauffeur')
                                                        [Chauffeur] {{ $car->brand }} {{ $car->model }} - {{ with_currency_symbol($hourly) }}/hr (Min {{ $minH }}h)
                                                    @else
                                                        [Car Hire] {{ $car->brand }} {{ $car->model }} - {{ with_currency_symbol($daily) }}/day 
                                                    @endif
                                                    [{{ $car->registration_number ?? 'No Reg' }}]
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Pickup Type --}}
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Pickup / Service Type') }}</label>
                                        <select class="form-select" name="pickup_type" id="pickup_type">
                                            <option value="self">{{ translate('Self-Drive (Customer Pick up from garage)') }}</option>
                                            <option value="delivery">{{ translate('Doorstep Delivery (Deliver car to customer address)') }}</option>
                                            <option value="chauffeur">{{ translate('Chauffeur Service (With Professional Driver)') }}</option>
                                        </select>
                                    </div>

                                    {{-- Rental Period --}}
                                    <div class="col-sm-6">
                                        <label class="form-label required-field fw-medium">{{ translate('Start_Date') }}</label>
                                        <input type="date" name="start_date" id="start_date" class="form-control" 
                                               value="{{ date('Y-m-d') }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label required-field fw-medium">{{ translate('Pickup_Time') }}</label>
                                        <input type="text" name="pickup_time" id="pickup_time" class="form-control" 
                                               value="10:00 AM" placeholder="10:00 AM">
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label required-field fw-medium">{{ translate('End_Date') }}</label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" 
                                               value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label required-field fw-medium">{{ translate('Drop_Time') }}</label>
                                        <input type="text" name="drop_time" id="drop_time" class="form-control" 
                                               value="10:00 AM" placeholder="10:00 AM">
                                    </div>

                                    {{-- Chauffeur Specific Fields --}}
                                    <div class="col-12 chauffeur-field" style="display: none;">
                                        <label class="form-label required-field fw-medium">{{ translate('Pickup_Location / Address') }}</label>
                                        <input type="text" name="pickup_location" id="pickup_location" class="form-control" 
                                               placeholder="{{ translate('e.g. Hotel Grand, London or Airport Terminal 2') }}">
                                    </div>
                                    <div class="col-12 chauffeur-field" style="display: none;">
                                        <label class="form-label required-field fw-medium">{{ translate('Drop_Location / Destination') }}</label>
                                        <input type="text" name="drop_location" id="drop_location" class="form-control" 
                                               placeholder="{{ translate('e.g. Heathrow Airport or Downtown Convention Center') }}">
                                    </div>

                                    {{-- Delivery Specific Field --}}
                                    <div class="col-12 delivery-field" style="display: none;">
                                        <label class="form-label required-field fw-medium">{{ translate('Delivery_Address for Vehicle Drop-off') }}</label>
                                        <input type="text" name="delivery_address" id="delivery_address" class="form-control" 
                                               placeholder="{{ translate('e.g. 123 Main St, London') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION B: Automotive & Garage Repair Configuration --}}
                        <div id="section_garage_service" class="card border-0 shadow-sm mb-4" style="display: none;">
                            <div class="card-header bg-transparent border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">build_circle</span>
                                    {{ translate('Service_&_Pricing_Details') }}
                                </h4>
                                <a href="{{ route('provider.service.available') }}" target="_blank" class="fz-12 text-primary text-decoration-none">
                                    <span class="material-icons fz-14 align-middle">open_in_new</span>
                                    {{ translate('Manage Subscribed Services & Rates') }}
                                </a>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    @if($services->isEmpty())
                                        <div class="col-12">
                                            <div class="alert alert-warning d-flex align-items-center justify-content-between p-3 rounded-3 mb-0">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="material-icons text-warning">warning</span>
                                                    <div class="fz-13">
                                                        <strong>{{ translate('No Subscribed Services Found!') }}</strong><br>
                                                        {{ translate('You have not subscribed to any services yet. Please subscribe to services in Available Services first.') }}
                                                    </div>
                                                </div>
                                                <a href="{{ route('provider.service.available') }}" class="btn btn-sm btn-warning text-dark fw-semibold text-nowrap">
                                                    {{ translate('Available Services') }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Category Filter --}}
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Category') }}</label>
                                        <select class="form-select" id="category_filter">
                                            <option value="all">{{ translate('-- All Categories --') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Service Selector --}}
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Select_Service') }}</label>
                                        <select class="form-select" name="service_id" id="service_id">
                                            <option value="">{{ translate('-- Select a Subscribed Service --') }}</option>
                                            @foreach($services as $svc)
                                                @php
                                                    $isQuotation = (bool)($svc->is_quotation_based ?? false);
                                                    $price = $svc->effective_price ?? ($svc->variations->first()?->price ?? 0);
                                                    $isCustom = $svc->configured_price !== null;
                                                @endphp
                                                <option value="{{ $svc->id }}"
                                                        data-category="{{ $svc->category_id }}"
                                                        data-quotation="{{ $isQuotation ? '1' : '0' }}"
                                                        data-price="{{ $price }}"
                                                        data-custom="{{ $isCustom ? '1' : '0' }}"
                                                        {{ old('service_id') == $svc->id ? 'selected' : '' }}>
                                                    {{ $svc->name }} 
                                                    @if($isQuotation)
                                                        [{{ translate('Quotation Based') }}]
                                                    @elseif($isCustom)
                                                        ({{ with_currency_symbol($price) }} - {{ translate('Your Rate') }})
                                                    @else
                                                        ({{ with_currency_symbol($price) }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Dynamic Category Questions Container --}}
                                    <div class="col-12 d-none" id="category_questions_wrapper">
                                        <div class="p-3 rounded-3 border bg-light shadow-xs">
                                            <label class="form-label fw-bold fz-14 text-dark mb-2 d-flex align-items-center gap-2">
                                                <span class="material-icons text-primary fs-18">quiz</span>
                                                {{ translate('Category Specific Questions') }}
                                            </label>
                                            <div id="category_questions_container" class="row g-3">
                                                {{-- Dynamic questions loaded via JS --}}
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Schedule Date & Time --}}
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium">{{ translate('Service_Schedule_Date_&_Time') }}</label>
                                        <input type="datetime-local" name="service_schedule" id="service_schedule" class="form-control" 
                                               value="{{ old('service_schedule', now()->addDay()->format('Y-m-d\TH:i')) }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Pricing & Notes (Shared) --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">payments</span>
                                    {{ translate('Quotation Pricing & Notes') }}
                                </h4>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    {{-- Info Badge --}}
                                    <div class="col-12">
                                        <div id="service_info_badge" class="alert alert-info py-2 px-3 d-flex align-items-center gap-2 mb-0" style="display: none !important;">
                                            <span class="material-icons fs-18">info</span>
                                            <span id="service_info_text" class="fz-13"></span>
                                        </div>
                                    </div>

                                    {{-- Quoted Price --}}
                                    <div class="col-12">
                                        <label class="form-label required-field fw-medium" id="price_label">
                                            {{ translate('Quotation_Price / Service Amount') }} ({{ currency_symbol() }})
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">{{ currency_symbol() }}</span>
                                            <input type="number" step="0.01" min="0" name="price" id="service_price" class="form-control fw-bold fs-18 text-primary" 
                                                   placeholder="0.00" value="{{ old('price') }}" required>
                                        </div>
                                        <span class="fz-11 text-muted" id="price_help">
                                            {{ translate('Rate is automatically estimated based on car rates / service catalog. You can customize the quote as agreed.') }}
                                        </span>
                                    </div>

                                    {{-- Notes / Terms --}}
                                    <div class="col-12">
                                        <label class="form-label fw-medium">{{ translate('Quotation_Notes_/_Terms') }}</label>
                                        <textarea name="notes" class="form-control" rows="3" 
                                                  placeholder="{{ translate('e.g. Fuel policy, deposit terms, insurance coverage, or 7-day quote validity.') }}">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="card border-0 shadow-sm bg-light">
                            <div class="card-body p-4 text-end">
                                <button type="reset" class="btn btn--secondary me-2">{{ translate('Reset') }}</button>
                                <button type="submit" class="btn btn--primary px-4 py-2 fw-semibold">
                                    <span class="material-icons align-middle me-1">share</span>
                                    {{ translate('Generate_Quotation_&_Link') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <style>
        .service-type-card {
            border: 2px solid #E2E8F0 !important;
            transition: all 0.2s ease;
            background: #fff;
        }
        .service-type-card.active-type {
            border-color: #0461A5 !important;
            background: #F0F7FF !important;
        }
        .cursor-pointer {
            cursor: pointer;
        }
        .single-pill-btn {
            border: 1.5px solid #CBD5E1;
            background: #FFFFFF;
            color: #334155;
            font-weight: 500;
            padding: 7px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            font-size: 13px;
            margin-bottom: 0;
        }
        .single-pill-btn:hover {
            border-color: #0461A5;
            background: #F0F7FF;
            color: #0461A5;
            transform: translateY(-1px);
        }
        .single-pill-btn.active {
            border-color: #0461A5 !important;
            background: #0461A5 !important;
            color: #FFFFFF !important;
            font-weight: 600;
            box-shadow: 0 3px 8px rgba(4, 97, 165, 0.28) !important;
            transform: translateY(-1px);
        }
        .single-pill-btn.active .pill-check-icon {
            display: inline-block !important;
        }
        .single-pill-number {
            min-width: 48px;
            height: 38px;
            padding: 0 12px;
            font-size: 14px;
            font-weight: 600;
        }
        .visually-hidden-input {
            position: absolute !important;
            width: 1px !important;
            height: 1px !important;
            padding: 0 !important;
            margin: -1px !important;
            overflow: hidden !important;
            clip: rect(0, 0, 0, 0) !important;
            white-space: nowrap !important;
            border: 0 !important;
            opacity: 0 !important;
        }
    </style>

    <script>
        $(document).ready(function() {
            // Toggle between Car Hire / Chauffeur vs Garage Service
            function updateModuleView() {
                let moduleType = $('input[name="module_type"]:checked').val();

                $('.service-type-card').removeClass('active-type');
                if (moduleType === 'car_hire') {
                    $('#card_car_hire').addClass('active-type');
                    $('#section_car_hire').show();
                    $('#section_garage_service').hide();
                    $('#section_customer_vehicle').hide();
                    $('#car_id').prop('required', true);
                    $('#service_id').prop('required', false);
                    $('#service_schedule').prop('required', false);
                    updateCarPickupFields();
                    calculateCarRate();
                } else {
                    $('#card_general').addClass('active-type');
                    $('#section_car_hire').hide();
                    $('#section_garage_service').show();
                    $('#section_customer_vehicle').show();
                    $('#car_id').prop('required', false);
                    $('#service_id').prop('required', true);
                    $('#service_schedule').prop('required', true);
                    handleServiceChange();
                }
            }

            $('input[name="module_type"]').on('change', updateModuleView);

            // Pickup Type changer for Car Hire / Chauffeur
            function updateCarPickupFields() {
                let pickupType = $('#pickup_type').val();
                if (pickupType === 'chauffeur') {
                    $('.chauffeur-field').show();
                    $('.delivery-field').hide();
                    $('#pickup_location, #drop_location').prop('required', true);
                    $('#delivery_address').prop('required', false);
                } else if (pickupType === 'delivery') {
                    $('.chauffeur-field').hide();
                    $('.delivery-field').show();
                    $('#pickup_location, #drop_location').prop('required', false);
                    $('#delivery_address').prop('required', true);
                } else {
                    $('.chauffeur-field').hide();
                    $('.delivery-field').hide();
                    $('#pickup_location, #drop_location, #delivery_address').prop('required', false);
                }
            }

            $('#pickup_type').on('change', function() {
                updateCarPickupFields();
                calculateCarRate();
            });

            // Car auto-calculation
            function calculateCarRate() {
                if ($('input[name="module_type"]:checked').val() !== 'car_hire') return;

                let opt = $('#car_id').find(':selected');
                if (!opt.val()) {
                    $('#service_info_badge').hide();
                    return;
                }

                let serviceCat = opt.data('service-cat') || 'car_hire';
                let pickupType = $('#pickup_type').val();

                // Auto-switch pickup type if car is specifically chauffeur
                if (serviceCat === 'chauffeur' && pickupType !== 'chauffeur') {
                    $('#pickup_type').val('chauffeur');
                    updateCarPickupFields();
                    pickupType = 'chauffeur';
                }

                let daily = parseFloat(opt.data('daily')) || 0;
                let hourly = parseFloat(opt.data('hourly')) || 0;
                let deliveryFee = parseFloat(opt.data('delivery-fee')) || 0;
                let minHours = parseInt(opt.data('min-hours')) || 1;

                let sDate = $('#start_date').val();
                let eDate = $('#end_date').val();

                if (sDate && eDate) {
                    let d1 = new Date(sDate);
                    let d2 = new Date(eDate);
                    let diffTime = Math.abs(d2 - d1);
                    let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    if (diffDays === 0) diffDays = 1;

                    let total = 0;
                    let infoMsg = '';

                    if (pickupType === 'chauffeur') {
                        // Chauffeur: Bill by hours or daily
                        let billedHours = minHours;
                        total = billedHours * hourly;
                        if (diffDays > 1 && daily > 0) {
                            total = diffDays * daily;
                        }
                        infoMsg = "<strong>{{ translate('Chauffeur Rate') }}:</strong> " + opt.data('brand') + " " + opt.data('model') + " ({{ currency_symbol() }}" + hourly + "/hr, Min " + minHours + "h) - Total: <strong>{{ currency_symbol() }}" + total.toFixed(2) + "</strong>";
                    } else {
                        // Car Hire: Daily rate
                        total = diffDays * daily;
                        if (pickupType === 'delivery' && deliveryFee > 0) {
                            total += deliveryFee;
                            infoMsg = "<strong>{{ translate('Car Hire') }}:</strong> " + opt.data('brand') + " " + opt.data('model') + " ({{ currency_symbol() }}" + daily + "/day × " + diffDays + " days + {{ currency_symbol() }}" + deliveryFee + " Delivery) - Total: <strong>{{ currency_symbol() }}" + total.toFixed(2) + "</strong>";
                        } else {
                            infoMsg = "<strong>{{ translate('Car Hire') }}:</strong> " + opt.data('brand') + " " + opt.data('model') + " ({{ currency_symbol() }}" + daily + "/day × " + diffDays + " days) - Total: <strong>{{ currency_symbol() }}" + total.toFixed(2) + "</strong>";
                        }
                    }

                    if (total > 0) {
                        $('#service_price').val(total.toFixed(2));
                    }

                    $('#service_info_badge').show().removeClass('alert-warning').addClass('alert-info');
                    $('#service_info_text').html(infoMsg);
                }
            }

            $('#car_id, #start_date, #end_date').on('change', calculateCarRate);

            // Existing customer autocomplete fill
            $('#existing_customer_select').on('change', function() {
                let opt = $(this).find(':selected');
                if (opt.val()) {
                    $('#customer_name').val(opt.data('name'));
                    $('#customer_phone').val(opt.data('phone'));
                    $('#customer_email').val(opt.data('email') || '');
                }
            });

            // Filter services by category
            $('#category_filter').on('change', function() {
                let catId = $(this).val();
                let $serviceSelect = $('#service_id');

                $serviceSelect.find('option').each(function() {
                    let optVal = $(this).val();
                    if (!optVal) return;
                    let optCat = $(this).data('category');
                    if (catId === 'all' || optCat == catId) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });

                let currentSelected = $serviceSelect.find(':selected');
                if (currentSelected.val() && catId !== 'all' && currentSelected.data('category') != catId) {
                    $serviceSelect.val('');
                    handleServiceChange();
                }
            });

            // Garage Service change handler
            function handleServiceChange() {
                if ($('input[name="module_type"]:checked').val() !== 'general') return;

                let opt = $('#service_id').find(':selected');
                let isQuotation = opt.data('quotation') == '1';
                let price = opt.data('price') || 0;
                let isCustom = opt.data('custom') == '1';

                if (!opt.val()) {
                    $('#service_info_badge').hide();
                    loadCategoryQuestions();
                    return;
                }

                $('#service_info_badge').show();
                if (isQuotation) {
                    $('#service_info_badge').removeClass('alert-info').addClass('alert-warning');
                    $('#service_info_text').html("<strong>{{ translate('Quotation-Based Service') }}:</strong> {{ translate('This service does not have a fixed rate. Enter the agreed or estimated quotation amount.') }}");
                    $('#price_label').text("{{ translate('Quotation Amount') }} ({{ currency_symbol() }}) *");
                    if (!$('#service_price').val() || $('#service_price').val() == '0' || $('#service_price').val() == '0.00') {
                        $('#service_price').val('');
                    }
                    $('#service_price').attr('placeholder', '{{ translate('Enter custom quote e.g. 150.00') }}');
                } else {
                    $('#service_info_badge').removeClass('alert-warning').addClass('alert-info');
                    if (isCustom) {
                        $('#service_info_text').html("<strong>{{ translate('Your Configured Price') }}:</strong> {{ translate('Your custom rate configured in Available Services has been auto-filled. You can adjust if needed.') }}");
                    } else {
                        $('#service_info_text').html("<strong>{{ translate('Fixed Price Service') }}:</strong> {{ translate('Standard catalog price auto-filled. You can adjust if a discount or custom agreement applies.') }}");
                    }
                    $('#price_label').text("{{ translate('Service Amount') }} ({{ currency_symbol() }}) *");
                    $('#service_price').val(parseFloat(price).toFixed(2));
                }
                loadCategoryQuestions();
            }

            // Dynamic Category Questions Loader
            function loadCategoryQuestions() {
                let opt = $('#service_id').find(':selected');
                let catId = $('#category_filter').val();
                if ((!catId || catId === 'all') && opt.val()) {
                    catId = opt.data('category');
                }

                if (!catId || catId === 'all') {
                    $('#category_questions_wrapper').addClass('d-none');
                    $('#category_questions_container').html('');
                    return;
                }

                let providerId = "{{ $provider->id }}";
                $.ajax({
                    url: "{{ url('/api/v1/customer/booking/provider/questions') }}",
                    type: "GET",
                    data: {
                        category_id: catId,
                        provider_id: providerId
                    },
                    success: function(res) {
                        let questions = res.content || [];
                        if (questions.length > 0) {
                            let html = '';
                            questions.forEach(function(q) {
                                html += `<div class="col-md-6">
                                    <label class="form-label fw-semibold fz-13 text-dark mb-1">
                                        ${q.question_text} ${q.is_required ? '<span class="text-danger">*</span>' : ''}
                                    </label>`;

                                if (q.question_type === 'select' && q.options) {
                                    let opts = q.options.split(',').map(o => o.trim()).filter(o => o.length > 0);
                                    let qTextLower = q.question_text.toLowerCase();
                                    let isMulti = qTextLower.includes('select all') || qTextLower.includes('apply') || qTextLower.includes('multiple');

                                    // Render Number of Wheels, Wheel Finish, and other short options as clickable pills/buttons
                                    let isNumberWheels = (qTextLower.includes('wheel') || qTextLower.includes('tyre')) && 
                                                         (qTextLower.includes('number') || qTextLower.includes('how many') || opts.every(o => !isNaN(parseInt(o))));
                                    let isWheelFinish = qTextLower.includes('finish') && !qTextLower.includes('colour') && !qTextLower.includes('color');
                                    let isPillSelect = isNumberWheels || isWheelFinish || (opts.length <= 6 && !qTextLower.includes('size') && !qTextLower.includes('colour') && !qTextLower.includes('color'));

                                    if (isMulti) {
                                        html += `<div class="d-flex flex-wrap gap-2 pt-1">`;
                                        opts.forEach(function(o) {
                                            html += `<label class="btn btn-sm btn-outline-warning text-dark border-secondary-subtle rounded-3 chip-btn px-3 py-2 cursor-pointer shadow-xs mb-0">
                                                <input type="checkbox" name="answers[${q.id}][]" value="${o}" class="d-none chip-checkbox">
                                                <span class="chip-text">${o}</span>
                                                <span class="material-icons fs-14 align-middle ms-1 check-icon d-none">check_circle</span>
                                            </label>`;
                                        });
                                        html += `</div>`;
                                    } else if (isPillSelect) {
                                        let isNumericOnly = opts.every(o => /^\d+(\s*wheels?|\s*tyres?)?$/i.test(o.trim()));
                                        html += `<div class="d-flex flex-wrap gap-2 pt-1 single-pill-group" data-required="${q.is_required ? '1' : '0'}">`;
                                        opts.forEach(function(o) {
                                            html += `<label class="single-pill-btn ${isNumericOnly ? 'single-pill-number' : ''}">
                                                <input type="radio" name="answers[${q.id}]" value="${o}" class="visually-hidden-input single-pill-radio">
                                                <span class="pill-text">${o}</span>
                                                ${!isNumericOnly ? '<span class="material-icons fs-14 align-middle ms-1 pill-check-icon d-none">check_circle</span>' : ''}
                                            </label>`;
                                        });
                                        html += `</div>`;
                                    } else {
                                        html += `<select name="answers[${q.id}]" class="form-select" ${q.is_required ? 'required' : ''}>
                                            <option value="">-- {{ translate('Select Option') }} --</option>`;
                                        opts.forEach(function(o) {
                                            html += `<option value="${o}">${o}</option>`;
                                        });
                                        html += `</select>`;
                                    }
                                } else if (q.question_type === 'yes_no') {
                                    let yesNoOpts = ['Yes', 'No', 'Not sure'];
                                    html += `<div class="d-flex flex-wrap gap-2 pt-1 single-pill-group" data-required="${q.is_required ? '1' : '0'}">`;
                                    yesNoOpts.forEach(function(o) {
                                        html += `<label class="single-pill-btn">
                                            <input type="radio" name="answers[${q.id}]" value="${o}" class="visually-hidden-input single-pill-radio">
                                            <span class="pill-text">${o}</span>
                                            <span class="material-icons fs-14 align-middle ms-1 pill-check-icon d-none">check_circle</span>
                                        </label>`;
                                    });
                                    html += `</div>`;
                                } else {
                                    html += `<input type="text" name="answers[${q.id}]" class="form-control" placeholder="{{ translate('Enter details') }}" ${q.is_required ? 'required' : ''}>`;
                                }
                                html += `</div>`;
                            });
                            $('#category_questions_container').html(html);
                            $('#category_questions_wrapper').removeClass('d-none');
                        } else {
                            $('#category_questions_wrapper').addClass('d-none');
                            $('#category_questions_container').html('');
                        }
                    },
                    error: function() {
                        $('#category_questions_wrapper').addClass('d-none');
                        $('#category_questions_container').html('');
                    }
                });
            }

            // Single-pill radio toggle handler
            $(document).on('change', '.single-pill-radio', function() {
                let name = $(this).attr('name');
                $(`input[name="${name}"]`).closest('.single-pill-btn').removeClass('active');
                if ($(this).is(':checked')) {
                    $(this).closest('.single-pill-btn').addClass('active');
                    $(this).closest('.single-pill-group').removeClass('p-2 rounded border border-danger bg-danger-subtle');
                }
            });

            // Form validation for required single pill groups
            $('#estimate_form').on('submit', function(e) {
                let moduleType = $('input[name="module_type"]:checked').val();
                if (moduleType === 'general') {
                    let missing = false;
                    $('.single-pill-group[data-required="1"]').each(function() {
                        let checked = $(this).find('input[type="radio"]:checked').val();
                        if (!checked) {
                            missing = true;
                            $(this).addClass('p-2 rounded border border-danger bg-danger-subtle');
                            let qLabel = $(this).closest('.col-md-6').find('label.form-label').text().replace('*', '').trim();
                            if (typeof toastr !== 'undefined') {
                                toastr.error('{{ translate("Please select an option for") }}: ' + qLabel);
                            }
                        } else {
                            $(this).removeClass('p-2 rounded border border-danger bg-danger-subtle');
                        }
                    });
                    if (missing) {
                        e.preventDefault();
                        let firstErr = $('.single-pill-group.border-danger').first();
                        if (firstErr.length) {
                            $('html, body').animate({
                                scrollTop: firstErr.offset().top - 120
                            }, 300);
                        }
                        return false;
                    }
                }
            });

            // Form reset handler
            $('#estimate_form').on('reset', function() {
                setTimeout(function() {
                    $('.single-pill-btn').removeClass('active');
                    $('.single-pill-group').removeClass('p-2 rounded border border-danger bg-danger-subtle');
                    $('.chip-btn').removeClass('btn-warning text-dark fw-bold border-warning')
                                  .addClass('btn-outline-warning border-secondary-subtle');
                    $('.chip-btn .check-icon').addClass('d-none');
                }, 50);
            });

            // Chip checkbox toggle handler for multi-select questions
            $(document).on('change', '.chip-checkbox', function() {
                let btn = $(this).closest('.chip-btn');
                if ($(this).is(':checked')) {
                    btn.addClass('btn-warning text-dark fw-bold border-warning')
                       .removeClass('btn-outline-warning border-secondary-subtle');
                    btn.find('.check-icon').removeClass('d-none');
                } else {
                    btn.removeClass('btn-warning text-dark fw-bold border-warning')
                       .addClass('btn-outline-warning border-secondary-subtle');
                    btn.find('.check-icon').addClass('d-none');
                }
            });

            $('#service_id, #category_filter').on('change', handleServiceChange);

            // Initial trigger
            updateModuleView();
        });
    </script>
@endpush
