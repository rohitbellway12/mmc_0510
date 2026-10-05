@extends('providermanagement::layouts.master')

@section('title', translate('Edit Vehicle'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin-module/plugins/select2/select2.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4285F4;
            --secondary-color: #f8f9fa;
            --accent-color: #34A853;
            --success-color: #34A853;
            --warning-color: #FBBC05;
            --danger-color: #EA4335;
            --light-color: #f8f9fa;
            --dark-color: #202124;
            --border-color: #dadce0;
            --border-radius: 8px;
            --transition: all 0.2s ease;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #202124;
        }

        .main-content {
            padding: 1.5rem 0;
        }

        .page-header {
            background-color: white;
            color: #202124;
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid var(--primary-color);
        }

        .page-title {
            font-weight: 500;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #5f6368;
            font-size: 1rem;
        }

        .card {
            border: 1px solid var(--border-color) !important;
            border-radius: var(--border-radius) !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
            background-color: white !important;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: var(--secondary-color);
            color: #202124;
            font-weight: 500;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }

        .card-body {
            padding: 1.25rem;
        }

        .form-label {
            color: #202124;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
            padding: 0.625rem 0.75rem !important;
            background-color: #fff !important;
            color: #202124 !important;
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.2) !important;
            outline: none;
        }

        .service-type-selector {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .service-type-card {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 1rem;
            cursor: pointer;
            transition: var(--transition);
            background-color: white;
        }

        .service-type-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .service-type-card.active {
            border-color: var(--primary-color);
            background-color: rgba(66, 133, 244, 0.05);
        }

        .service-type-title {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 0.25rem;
            color: #202124;
        }

        .service-type-desc {
            color: #5f6368;
            font-size: 0.875rem;
        }

        .pricing-options {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .pricing-option {
            flex: 1;
            position: relative;
        }

        .pricing-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .pricing-option label {
            display: block;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .pricing-option input[type="radio"]:checked+label {
            border-color: var(--primary-color);
            background-color: rgba(66, 133, 244, 0.05);
            color: var(--primary-color);
            font-weight: 500;
        }

        .rate-input-group {
            display: flex;
            gap: 1rem;
        }

        .rate-input-group .form-group {
            flex: 1;
        }

        .input-group-text {
            background-color: var(--secondary-color) !important;
            border: 1px solid var(--border-color) !important;
            color: #5f6368 !important;
        }

        .feature-input-container {
            display: flex;
            margin-bottom: 1rem;
        }

        .feature-input {
            flex: 1;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .add-feature-btn {
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-left: none !important;
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            cursor: pointer;
        }

        .add-feature-btn:hover {
            background-color: #3367d6;
        }

        .features-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .feature-tag {
            display: inline-flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            background-color: var(--secondary-color);
            color: #202124;
            border-radius: 16px;
            font-size: 0.875rem;
            border: 1px solid var(--border-color);
        }

        .feature-tag .remove-feature {
            margin-left: 0.5rem;
            cursor: pointer;
            color: #5f6368;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            transition: var(--transition);
        }

        .feature-tag .remove-feature:hover {
            background-color: var(--danger-color);
            color: white;
        }

        .image-upload-container {
            border: 1px dashed var(--border-color);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            background-color: var(--secondary-color);
            transition: var(--transition);
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .image-upload-container:hover {
            border-color: var(--primary-color);
            background-color: rgba(66, 133, 244, 0.05);
        }

        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .image-preview-box {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .image-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-preview-box .remove-image {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.75rem;
        }

        .document-upload-box {
            border: 1px dashed var(--border-color);
            border-radius: var(--border-radius);
            padding: 1rem;
            text-align: center;
            background-color: var(--secondary-color);
            transition: var(--transition);
            cursor: pointer;
            margin-bottom: 1rem;
        }

        .document-upload-box:hover {
            border-color: var(--primary-color);
            background-color: rgba(66, 133, 244, 0.05);
        }

        .document-upload-box.uploaded {
            border-color: var(--success-color);
            background-color: rgba(52, 168, 83, 0.05);
        }

        .document-upload-box.uploaded .upload-icon {
            color: var(--success-color);
        }

        .upload-icon {
            font-size: 1.5rem;
            color: #5f6368;
            margin-bottom: 0.5rem;
        }

        .document-name {
            margin-top: 0.5rem;
            font-weight: 500;
            color: var(--success-color);
            font-size: 0.875rem;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            border: none;
            padding: 0.75rem;
            border-radius: var(--border-radius);
            font-size: 0.875rem;
            transition: var(--transition);
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #3367d6;
        }

        .form-section {
            margin-bottom: 1.5rem;
        }

        .form-section-title {
            font-weight: 500;
            font-size: 1rem;
            margin-bottom: 0.75rem;
            color: #202124;
            display: flex;
            align-items: center;
        }

        .form-section-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }

        .time-input-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .time-separator {
            color: #5f6368;
            font-weight: 500;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {

            .service-type-selector,
            .pricing-options,
            .rate-input-group {
                flex-direction: column;
            }

            .page-header {
                padding: 1rem;
            }

            .page-title {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="page-title">{{ translate('Edit Vehicle') }}</h1>
                <p class="page-subtitle">
                    {{ translate('Update your car hire or chauffeur service information') }}</p>
            </div>

            <form action="{{ route('provider.car.update', [$car->id]) }}" method="POST" enctype="multipart/form-data"
                id="car-form">
                @csrf
                @method('PUT')
                <div class="row g-4">
                    <div class="col-lg-7">
                        <!-- Service Information -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-car me-2"></i> {{ translate('Service Information') }}
                            </div>
                            <div class="card-body">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-concierge-bell"></i> {{ translate('Service Type') }}
                                    </div>
                                    <div class="service-type-selector">
                                        <div class="service-type-card {{ $car->service_category == 'car_hire' ? 'active' : '' }}"
                                            id="car-hire-card">
                                            <input type="radio" name="service_category" id="car_hire" value="car_hire"
                                                {{ $car->service_category == 'car_hire' ? 'checked' : '' }}>
                                            <label for="car_hire" class="w-100">
                                                <div class="service-type-title">
                                                    <i class="fas fa-key me-2"></i> {{ translate('Car Hire') }}
                                                </div>
                                                <div class="service-type-desc">
                                                    {{ translate('Self-drive car rental service') }}</div>
                                            </label>
                                        </div>
                                        <div class="service-type-card {{ $car->service_category == 'chauffeur' ? 'active' : '' }}"
                                            id="chauffeur-card">
                                            <input type="radio" name="service_category" id="chauffeur" value="chauffeur"
                                                {{ $car->service_category == 'chauffeur' ? 'checked' : '' }}>
                                            <label for="chauffeur" class="w-100">
                                                <div class="service-type-title">
                                                    <i class="fas fa-user-tie me-2"></i>
                                                    {{ translate('Chauffeur Service') }}
                                                </div>
                                                <div class="service-type-desc">
                                                    {{ translate('Professional driver with car') }}</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">{{ translate('Category') }}</label>
                                        <select class="form-select" id="category_id" name="category_id" required>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ $car->category_id == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="car_type_id" class="form-label">{{ translate('Vehicle Type') }}</label>
                                        <select class="form-select" id="car_type_id" name="car_type_id" required>
                                            @foreach ($types as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ $car->car_type_id == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="brand" class="form-label">{{ translate('Car Brand/Model') }}</label>
                                        <input type="text" class="form-control" id="brand" name="brand"
                                            value="{{ $car->brand }}" placeholder="{{ translate('Eg: Audi A4') }}"
                                            required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="registration_number"
                                            class="form-label">{{ translate('Registration Number') }}</label>
                                        <input type="text" class="form-control" id="registration_number"
                                            name="registration_number" value="{{ $car->registration_number }}"
                                            placeholder="{{ translate('Eg: ABC XYZ') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Air Conditioning') }}</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="air_conditioning"
                                                id="ac_yes" value="1"
                                                {{ $car->air_conditioning ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ac_yes">{{ translate('Yes') }}</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="air_conditioning"
                                                id="ac_no" value="0"
                                                {{ !$car->air_conditioning ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ac_no">{{ translate('No') }}</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Information -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-pound-sign me-2"></i> {{ translate('Pricing Information') }}
                            </div>
                            <div class="card-body">
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="fas fa-tags"></i> {{ translate('Pricing Options') }}
                                    </div>
                                    <div class="pricing-options">
                                        <div class="pricing-option">
                                            <input type="radio" name="pricing_type" id="pricing_both" value="both"
                                                {{ $car->pricing_type == 'both' ? 'checked' : '' }}>
                                            <label for="pricing_both">{{ translate('Both Hourly & Daily') }}</label>
                                        </div>
                                        <div class="pricing-option">
                                            <input type="radio" name="pricing_type" id="pricing_hourly" value="hourly"
                                                {{ $car->pricing_type == 'hourly' ? 'checked' : '' }}>
                                            <label for="pricing_hourly">{{ translate('Hourly Only') }}</label>
                                        </div>
                                        <div class="pricing-option">
                                            <input type="radio" name="pricing_type" id="pricing_daily" value="daily"
                                                {{ $car->pricing_type == 'daily' ? 'checked' : '' }}>
                                            <label for="pricing_daily">{{ translate('Daily Only') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="rate-input-group">
                                    <div class="form-group" id="hourly_rate_group">
                                        <label for="hourly_rate"
                                            class="form-label">{{ translate('Hourly Rate') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" class="form-control" id="hourly_rate"
                                                name="hourly_rate" value="{{ $car->hourly_rate }}"
                                                placeholder="{{ translate('Enter Hourly Rate') }}" min="0"
                                                step="0.01">
                                        </div>
                                    </div>
                                    <div class="form-group" id="daily_rate_group">
                                        <label for="daily_rate" class="form-label">{{ translate('Daily Rate') }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">£</span>
                                            <input type="number" class="form-control" id="daily_rate" name="daily_rate"
                                                value="{{ $car->daily_rate }}"
                                                placeholder="{{ translate('Enter Daily Rate') }}" min="0"
                                                step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Availability & Features -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-calendar-alt me-2"></i> {{ translate('Availability & Features') }}
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Available Hours') }}</label>
                                    <div class="time-input-group">
                                        <input type="time" class="form-control" name="available_hours_start"
                                            value="{{ $car->available_hours_start }}"
                                            placeholder="{{ translate('Start Time') }}">
                                        <span class="time-separator">{{ translate('to') }}</span>
                                        <input type="time" class="form-control" name="available_hours_end"
                                            value="{{ $car->available_hours_end }}"
                                            placeholder="{{ translate('End Time') }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="preferred_areas"
                                        class="form-label">{{ translate('Preferred Areas') }}</label>
                                    <input type="text" class="form-control" id="preferred_areas"
                                        name="preferred_areas" value="{{ $car->preferred_areas }}"
                                        placeholder="{{ translate('Enter City Or Region') }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Vehicle Features') }}</label>
                                    <div class="feature-input-container">
                                        <input type="text" class="form-control feature-input" id="feature_input"
                                            placeholder="{{ translate('Type a feature and press Enter') }}">
                                        <button type="button" class="btn btn-primary add-feature-btn"
                                            id="add_feature_btn">
                                            {{ translate('Add') }}
                                        </button>
                                    </div>
                                    <div class="features-container" id="features_container">
                                        <!-- Features will be added here dynamically -->
                                    </div>
                                    <input type="hidden" name="features" id="features_hidden" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <!-- Images & Documents -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-images me-2"></i> {{ translate('Images & Documents') }}
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Vehicle Images') }}</label>
                                    <div class="image-upload-container"
                                        onclick="document.getElementById('car_images').click()">
                                        <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                        <p class="mb-0">{{ translate('Click to upload images') }}</p>
                                        <small class="text-muted">{{ translate('JPG, PNG (Max 5MB each)') }}</small>
                                    </div>
                                    <input type="file" id="car_images" class="d-none" name="images[]" multiple
                                        accept="image/*">
                                    <div class="image-preview-container" id="image_preview_container">
                                        @if ($car->images)
                                            @foreach ($car->images as $img)
                                                <div class="image-preview-box">
                                                    <img src="{{ asset('storage/app/public/car/' . $img) }}"
                                                        alt="car">
                                                    <button type="button" class="remove-image"
                                                        onclick="$(this).parent().remove()">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <input type="hidden" name="old_images[]"
                                                        value="{{ $img }}">
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Driving License') }}</label>
                                    <div class="document-upload-box {{ $car->driving_license ? 'uploaded' : '' }}"
                                        id="driving_license_box"
                                        onclick="document.getElementById('driving_license').click()">
                                        <i
                                            class="fas {{ $car->driving_license ? 'fa-check-circle' : 'fa-file-upload' }} upload-icon"></i>
                                        <p class="mb-0">{{ translate('Upload Driving License') }}</p>
                                        <small class="text-muted">{{ translate('PDF, JPG, PNG (Max 5MB)') }}</small>
                                        <div class="document-name" id="driving_license_name">
                                            {{ $car->driving_license ?? '' }}</div>
                                    </div>
                                    <input type="file" id="driving_license" class="d-none" name="driving_license"
                                        accept="image/*,application/pdf">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Vehicle Registration Certificate') }}</label>
                                    <div class="document-upload-box {{ $car->vehicle_registration ? 'uploaded' : '' }}"
                                        id="vehicle_registration_box"
                                        onclick="document.getElementById('vehicle_registration').click()">
                                        <i
                                            class="fas {{ $car->vehicle_registration ? 'fa-check-circle' : 'fa-file-upload' }} upload-icon"></i>
                                        <p class="mb-0">{{ translate('Upload Registration Certificate') }}</p>
                                        <small class="text-muted">{{ translate('PDF, JPG, PNG (Max 5MB)') }}</small>
                                        <div class="document-name" id="vehicle_registration_name">
                                            {{ $car->vehicle_registration ?? '' }}</div>
                                    </div>
                                    <input type="file" id="vehicle_registration" class="d-none"
                                        name="vehicle_registration" accept="image/*,application/pdf">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Insurance Documents') }}</label>
                                    <div class="document-upload-box {{ $car->insurance_documents ? 'uploaded' : '' }}"
                                        id="insurance_documents_box"
                                        onclick="document.getElementById('insurance_documents').click()">
                                        <i
                                            class="fas {{ $car->insurance_documents ? 'fa-check-circle' : 'fa-file-upload' }} upload-icon"></i>
                                        <p class="mb-0">{{ translate('Upload Insurance Documents') }}</p>
                                        <small class="text-muted">{{ translate('PDF, JPG, PNG (Max 5MB)') }}</small>
                                        <div class="document-name" id="insurance_documents_name">
                                            {{ $car->insurance_documents ?? '' }}</div>
                                    </div>
                                    <input type="file" id="insurance_documents" class="d-none"
                                        name="insurance_documents" accept="image/*,application/pdf">
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">{{ translate('MOT Certificate') }} <small
                                            class="text-muted">({{ translate('If Car Is 3+ Years Old') }})</small></label>
                                    <div class="document-upload-box {{ $car->mot_certificate ? 'uploaded' : '' }}"
                                        id="mot_certificate_box"
                                        onclick="document.getElementById('mot_certificate').click()">
                                        <i
                                            class="fas {{ $car->mot_certificate ? 'fa-check-circle' : 'fa-file-upload' }} upload-icon"></i>
                                        <p class="mb-0">{{ translate('Upload MOT Certificate') }}</p>
                                        <small class="text-muted">{{ translate('PDF, JPG, PNG (Max 5MB)') }}</small>
                                        <div class="document-name" id="mot_certificate_name">
                                            {{ $car->mot_certificate ?? '' }}</div>
                                    </div>
                                    <input type="file" id="mot_certificate" class="d-none" name="mot_certificate"
                                        accept="image/*,application/pdf">
                                </div>

                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-save me-2"></i> {{ translate('Update Vehicle') }}
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize service type cards
            initServiceTypeCards();

            // Initialize pricing options
            initPricingOptions();

            // Initialize features
            initFeatures();

            // Initialize image uploads
            initImageUploads();

            // Initialize document uploads
            initDocumentUploads();
        });

        function initServiceTypeCards() {
            const serviceTypeCards = document.querySelectorAll('.service-type-card');

            serviceTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove active class from all cards
                    serviceTypeCards.forEach(c => c.classList.remove('active'));

                    // Add active class to clicked card
                    this.classList.add('active');

                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });
        }

        function initPricingOptions() {
            const pricingRadios = document.querySelectorAll('input[name="pricing_type"]');
            const hourlyRateGroup = document.getElementById('hourly_rate_group');
            const dailyRateGroup = document.getElementById('daily_rate_group');

            function updatePricingFields() {
                const selectedPricing = document.querySelector('input[name="pricing_type"]:checked').value;

                if (selectedPricing === 'hourly') {
                    hourlyRateGroup.style.display = 'block';
                    dailyRateGroup.style.display = 'none';
                    document.getElementById('hourly_rate').setAttribute('required', 'required');
                    document.getElementById('daily_rate').removeAttribute('required');
                } else if (selectedPricing === 'daily') {
                    hourlyRateGroup.style.display = 'none';
                    dailyRateGroup.style.display = 'block';
                    document.getElementById('hourly_rate').removeAttribute('required');
                    document.getElementById('daily_rate').setAttribute('required', 'required');
                } else { // both
                    hourlyRateGroup.style.display = 'block';
                    dailyRateGroup.style.display = 'block';
                    document.getElementById('hourly_rate').removeAttribute('required');
                    document.getElementById('daily_rate').removeAttribute('required');
                }
            }

            pricingRadios.forEach(radio => {
                radio.addEventListener('change', updatePricingFields);
            });

            // Initialize with default selection
            updatePricingFields();
        }

        function initFeatures() {
            const features = @json($car->features ?? []);
            const featureInput = document.getElementById('feature_input');
            const addFeatureBtn = document.getElementById('add_feature_btn');
            const featuresContainer = document.getElementById('features_container');
            const featuresHidden = document.getElementById('features_hidden');

            // Initialize existing features
            updateFeaturesDisplay();

            function addFeature() {
                const feature = featureInput.value.trim();
                if (feature && !features.includes(feature)) {
                    features.push(feature);
                    updateFeaturesDisplay();
                    featureInput.value = '';
                    featureInput.focus();
                }
            }

            function removeFeature(index) {
                features.splice(index, 1);
                updateFeaturesDisplay();
            }

            function updateFeaturesDisplay() {
                featuresContainer.innerHTML = '';
                features.forEach((feature, index) => {
                    const featureTag = document.createElement('div');
                    featureTag.className = 'feature-tag';
                    featureTag.innerHTML = `
                        ${feature}
                        <span class="remove-feature" onclick="removeFeature(${index})">
                            <i class="fas fa-times"></i>
                        </span>
                    `;
                    featuresContainer.appendChild(featureTag);
                });
                featuresHidden.value = JSON.stringify(features);
            }

            addFeatureBtn.addEventListener('click', addFeature);

            featureInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addFeature();
                }
            });

            // Make removeFeature globally accessible
            window.removeFeature = removeFeature;
        }

        function initImageUploads() {
            const carImagesInput = document.getElementById('car_images');
            const imagePreviewContainer = document.getElementById('image_preview_container');

            carImagesInput.addEventListener('change', function() {
                if (this.files) {
                    for (let i = 0; i < this.files.length; i++) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imageBox = document.createElement('div');
                            imageBox.className = 'image-preview-box';
                            imageBox.innerHTML = `
                                <img src="${e.target.result}" alt="preview">
                                <button type="button" class="remove-image">
                                    <i class="fas fa-times"></i>
                                </button>
                            `;
                            imagePreviewContainer.appendChild(imageBox);

                            // Add remove functionality
                            imageBox.querySelector('.remove-image').addEventListener('click', function() {
                                imageBox.remove();
                            });
                        }
                        reader.readAsDataURL(this.files[i]);
                    }
                }
            });
        }

        function initDocumentUploads() {
            const documentInputs = [{
                    input: 'driving_license',
                    box: 'driving_license_box',
                    name: 'driving_license_name'
                },
                {
                    input: 'vehicle_registration',
                    box: 'vehicle_registration_box',
                    name: 'vehicle_registration_name'
                },
                {
                    input: 'insurance_documents',
                    box: 'insurance_documents_box',
                    name: 'insurance_documents_name'
                },
                {
                    input: 'mot_certificate',
                    box: 'mot_certificate_box',
                    name: 'mot_certificate_name'
                }
            ];

            documentInputs.forEach(doc => {
                const input = document.getElementById(doc.input);
                const box = document.getElementById(doc.box);
                const nameDisplay = document.getElementById(doc.name);

                input.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        // Update UI to show file is uploaded
                        box.classList.add('uploaded');
                        nameDisplay.textContent = this.files[0].name;

                        // Update icon
                        const icon = box.querySelector('.upload-icon');
                        icon.className = 'fas fa-check-circle upload-icon';
                    }
                });
            });
        }
    </script>
@endpush
