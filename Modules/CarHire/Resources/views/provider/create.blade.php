@extends('providermanagement::layouts.master')

@section('title', translate('Add New Vehicle'))

@push('css_or_js')
    <style>
        .selection-card {
            transition: 0.3s;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 2.5rem;
            text-align: center;
            text-decoration: none !important;
            display: block;
            height: 100%;
        }

        .selection-card:hover {
            border-color: var(--c1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transform: translateY(-3px);
        }

        .selection-icon {
            font-size: 3rem;
            color: var(--c1);
            margin-bottom: 1.5rem;
        }

        .selection-card h3 {
            color: #333;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .selection-card p {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 0;
            line-height: 1.5;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title text-center">{{ translate('Choose Your Service Type') }}</h2>
                <p class="text-center text-muted">
                    {{ translate('Select the type of service you want to register your vehicle for.') }}</p>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-sm-6 col-md-5 col-lg-4">
                    <a href="{{ route('provider.car.create-car-hire') }}" class="selection-card bg-white">
                        <div class="selection-icon">
                            <span class="material-icons" style="font-size: inherit;">directions_car</span>
                        </div>
                        <h3>{{ translate('Car Hire') }}</h3>
                        <p>{{ translate('Add vehicles for self-drive rental services with hourly rates and security deposits.') }}
                        </p>
                    </a>
                </div>

                <div class="col-sm-6 col-md-5 col-lg-4">
                    <a href="{{ route('provider.car.create-chauffeur') }}" class="selection-card bg-white">
                        <div class="selection-icon">
                            <span class="material-icons" style="font-size: inherit;">person_pin</span>
                        </div>
                        <h3>{{ translate('Chauffeur Service') }}</h3>
                        <p>{{ translate('Add vehicles with professional drivers for full-day or hourly bookings.') }}</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
