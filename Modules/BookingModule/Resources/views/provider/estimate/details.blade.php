@extends('providermanagement::layouts.master')

@section('title', translate('Quotation_Details') . ' #' . $estimate->readable_id)

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            {{-- Header --}}
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h2 class="page-title mb-0">{{ translate('Quotation') }} #{{ $estimate->readable_id }}</h2>
                        @if($estimate->status == 'pending')
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fz-12">{{ translate('Pending_Acceptance') }}</span>
                        @elseif($estimate->status == 'accepted')
                            <span class="badge bg-success text-white px-3 py-1 rounded-pill fz-12">{{ translate('Accepted_&_Booked') }}</span>
                        @elseif($estimate->status == 'canceled')
                            <span class="badge bg-danger text-white px-3 py-1 rounded-pill fz-12">{{ translate('Canceled') }}</span>
                        @endif
                    </div>
                    <p class="text-muted fz-14 mb-0">
                        {{ translate('Created_on') }} {{ $estimate->created_at->format('d M Y, h:i A') }}
                        @if($estimate->expired_at)
                            • <span class="text-secondary">{{ translate('Expires_on') }} {{ $estimate->expired_at->format('d M Y') }}</span>
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('provider.estimate.index') }}" class="btn btn--secondary d-flex align-items-center gap-2">
                        <span class="material-icons">arrow_back</span>
                        {{ translate('Back_to_List') }}
                    </a>
                    <a href="{{ route('provider.estimate.create') }}" class="btn btn--primary d-flex align-items-center gap-2">
                        <span class="material-icons">add_circle</span>
                        {{ translate('New_Quotation') }}
                    </a>
                </div>
            </div>

            {{-- Link Sharing Banner --}}
            <div class="card border-0 shadow-sm mb-4 bg-primary-subtle border-start border-4 border-primary">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-7">
                            <h4 class="text-primary fw-bold mb-1 d-flex align-items-center gap-2">
                                <span class="material-icons">share</span>
                                {{ translate('Customer_Shareable_Link') }}
                            </h4>
                            <p class="text-muted fz-13 mb-3">
                                {{ translate('Send this quotation link to the customer via WhatsApp, SMS, or Email. Customer can view quotation details and accept online.') }}
                            </p>
                            <div class="input-group">
                                <input type="text" id="share_link_input" class="form-control bg-white fw-medium" value="{{ $estimate->web_url }}" readonly>
                                <button type="button" class="btn btn-primary d-flex align-items-center gap-1 copy-btn" data-target="#share_link_input">
                                    <span class="material-icons fs-16">content_copy</span>
                                    {{ translate('Copy_Link') }}
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-5 text-lg-end">
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $estimate->customer_phone);
                                $itemTitle = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur') ? $estimate->car_model : ($estimate->service?->name ?? 'service');
                                $waText = urlencode(translate("Hello {$estimate->customer_name}, here is your quotation/booking estimate for {$itemTitle} from " . (auth()->user()->provider->company_name ?? 'our service') . ": {$estimate->web_url}"));
                            @endphp
                            <a href="https://api.whatsapp.com/send?phone={{ $cleanPhone }}&text={{ $waText }}" target="_blank" 
                               class="btn btn-success d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
                                <span class="material-icons">chat</span>
                                {{ translate('Send_via_WhatsApp') }}
                            </a>
                            <a href="{{ $estimate->web_url }}" target="_blank" 
                               class="btn btn-outline-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold">
                                <span class="material-icons">open_in_new</span>
                                {{ translate('Preview_Customer_Page') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if($estimate->status == 'accepted' && ($estimate->booking || $estimate->car_booking_id))
                {{-- Booking Linked Alert --}}
                <div class="alert alert-success d-flex align-items-center justify-content-between p-3 mb-4 rounded-3 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-icons text-success fs-24">check_circle</span>
                        <div>
                            <div class="fw-bold fs-15">{{ translate('Quotation Accepted!') }}</div>
                            <div class="fz-13">
                                {{ translate('This quotation was accepted by the customer and converted to active booking') }}
                                @if($estimate->booking)
                                    <strong>#{{ $estimate->booking->readable_id }}</strong>.
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($estimate->car_booking_id)
                            <a href="{{ route('provider.car.booking.details', [$estimate->car_booking_id]) }}" class="btn btn-sm btn-outline-success fw-semibold">
                                <span class="material-icons fs-16 align-middle">directions_car</span>
                                {{ translate('Car_Hire_Booking') }}
                            </a>
                        @endif
                        @if($estimate->booking_id)
                            <a href="{{ route('provider.booking.details', [$estimate->booking_id]) }}" class="btn btn-sm btn-success fw-semibold">
                                {{ translate('View_Booking_Details') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="row g-4">
                {{-- Left Side: Service & Financials --}}
                <div class="col-lg-8">
                    {{-- Service Details Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                <span class="material-icons text-primary">build</span>
                                {{ translate('Service_&_Quotation_Pricing') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>{{ translate('Service_Name') }}</th>
                                            <th>{{ translate('Category') }}</th>
                                            <th>{{ translate('Type') }}</th>
                                            <th class="text-end">{{ translate('Amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $isCarBooking = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">
                                                    @if($isCarBooking)
                                                        {{ $estimate->car_model }}
                                                    @else
                                                        {{ $estimate->service?->name ?? translate('Custom Service') }}
                                                    @endif
                                                </div>
                                                @if($isCarBooking)
                                                    <div class="fz-12 text-muted">
                                                        {{ translate('Rental Type') }}: <strong>{{ ucfirst($estimate->pickup_type ?? 'Self Drive') }}</strong>
                                                        @if($estimate->car_registration_number)
                                                            • <span class="badge bg-light text-dark border">{{ $estimate->car_registration_number }}</span>
                                                        @endif
                                                    </div>
                                                @elseif($estimate->service?->short_description)
                                                    <div class="fz-12 text-muted">{{ $estimate->service->short_description }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    @if($isCarBooking)
                                                        {{ ucfirst(str_replace('_', ' ', $estimate->module_type)) }}
                                                    @else
                                                        {{ $estimate->category?->name ?? '-' }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>
                                                @if($isCarBooking)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ ucfirst($estimate->pickup_type ?? 'Rental') }}</span>
                                                    @if($estimate->pricing_type)
                                                        <span class="badge {{ $estimate->pricing_type === 'hourly' ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-info-subtle text-info border border-info-subtle' }} ms-1">
                                                            {{ ucfirst($estimate->pricing_type) }} {{ translate('Rate') }}
                                                        </span>
                                                    @endif
                                                @elseif($estimate->service_type == 'quotation_based')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ translate('Quotation Based') }}</span>
                                                @else
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle">{{ translate('Fixed Price') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-bold fs-16 text-dark">
                                                {{ with_currency_symbol($estimate->price) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        @if($isCarBooking && (($estimate->security_deposit ?? 0) > 0 || ($estimate->delivery_fee ?? 0) > 0))
                                            <tr>
                                                <td colspan="3" class="text-end fw-semibold">{{ translate('Base Rental Fee') }}:</td>
                                                <td class="text-end fw-semibold">{{ with_currency_symbol($estimate->rent_amount > 0 ? $estimate->rent_amount : ($estimate->price - ($estimate->delivery_fee ?? 0) - ($estimate->security_deposit ?? 0))) }}</td>
                                            </tr>
                                            @if(($estimate->delivery_fee ?? 0) > 0)
                                                <tr>
                                                    <td colspan="3" class="text-end fw-semibold">{{ translate('Doorstep Delivery Fee') }}:</td>
                                                    <td class="text-end fw-semibold">{{ with_currency_symbol($estimate->delivery_fee) }}</td>
                                                </tr>
                                            @endif
                                            @if(($estimate->security_deposit ?? 0) > 0)
                                                <tr>
                                                    <td colspan="3" class="text-end fw-semibold">
                                                        {{ translate('Refundable Security Deposit') }}
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle ms-1 fz-10">{{ translate('Refundable') }}</span>:
                                                    </td>
                                                    <td class="text-end fw-bold text-info">{{ with_currency_symbol($estimate->security_deposit) }}</td>
                                                </tr>
                                            @endif
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-end fw-semibold">{{ translate('Subtotal') }}:</td>
                                                <td class="text-end fw-semibold">{{ with_currency_symbol($estimate->price) }}</td>
                                            </tr>
                                        @endif
                                        @if($estimate->tax_amount > 0)
                                            <tr>
                                                <td colspan="3" class="text-end fw-semibold">{{ translate('Tax / VAT') }}:</td>
                                                <td class="text-end">{{ with_currency_symbol($estimate->tax_amount) }}</td>
                                            </tr>
                                        @endif
                                        <tr class="border-top">
                                            <td colspan="3" class="text-end fw-bold fs-16 text-dark">{{ translate('Total_Quoted_Amount') }}:</td>
                                            <td class="text-end fw-bold fs-18 text-primary">{{ with_currency_symbol($estimate->total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            @if($estimate->notes)
                                <div class="mt-4 p-3 bg-light rounded-3">
                                    <h6 class="fw-bold fz-13 text-dark mb-1">{{ translate('Provider Notes & Terms') }}:</h6>
                                    <p class="fz-13 text-muted mb-0">{{ $estimate->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Vehicle Information --}}
                    @if($estimate->car_model || $estimate->car_registration_number || $estimate->damage_description || $estimate->car_image)
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-transparent border-bottom py-3">
                                <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                    <span class="material-icons text-primary">directions_car</span>
                                    {{ translate('Vehicle_Information') }}
                                </h4>
                            </div>
                            <div class="card-body p-4">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="fz-12 text-muted d-block">{{ translate('Car Model / Make') }}</label>
                                        <span class="fw-bold text-dark">{{ $estimate->car_model ?? '-' }}</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="fz-12 text-muted d-block">{{ translate('Registration Number') }}</label>
                                        <span class="badge bg-light text-dark border px-2 py-1 fs-14 fw-bold">{{ $estimate->car_registration_number ?? '-' }}</span>
                                    </div>
                                    @if($estimate->damage_description)
                                        <div class="col-12">
                                            <label class="fz-12 text-muted d-block">{{ translate('Damage / Work Description') }}</label>
                                            <p class="fz-13 text-dark bg-light p-3 rounded-2 mb-0">{{ $estimate->damage_description }}</p>
                                        </div>
                                    @endif
                                    @if($estimate->car_images_full_path && count($estimate->car_images_full_path) > 0)
                                        <div class="col-12">
                                            <label class="fz-12 text-muted d-block mb-2">{{ translate('Inspection / Vehicle Photos') }}</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($estimate->car_images_full_path as $imgPath)
                                                    <a href="{{ $imgPath }}" target="_blank" title="{{ translate('Click to enlarge') }}">
                                                        <img src="{{ $imgPath }}" alt="Car Image" 
                                                             class="rounded-3 border shadow-sm img-thumbnail" style="height: 120px; width: 140px; object-fit: cover;">
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Side: Customer & Schedule Summary --}}
                <div class="col-lg-4">
                    {{-- Customer Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                <span class="material-icons text-primary">person</span>
                                {{ translate('Customer_Details') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold fs-18" 
                                     style="width: 50px; height: 50px;">
                                    {{ strtoupper(substr($estimate->customer_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">{{ $estimate->customer_name }}</h5>
                                    @if($estimate->customer)
                                        <span class="badge bg-success-subtle text-success fz-11">{{ translate('Registered Customer') }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border fz-11">{{ translate('Guest / Phone Prospect') }}</span>
                                    @endif
                                </div>
                            </div>

                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2 fz-13">
                                <li class="d-flex align-items-center gap-2">
                                    <span class="material-icons fs-16 text-muted">phone</span>
                                    <a href="tel:{{ $estimate->customer_phone }}" class="text-dark fw-medium text-decoration-none">
                                        {{ $estimate->customer_phone }}
                                    </a>
                                </li>
                                @if($estimate->customer_email)
                                    <li class="d-flex align-items-center gap-2">
                                        <span class="material-icons fs-16 text-muted">email</span>
                                        <a href="mailto:{{ $estimate->customer_email }}" class="text-dark text-decoration-none">
                                            {{ $estimate->customer_email }}
                                        </a>
                                    </li>
                                @endif
                                @if($estimate->customer_address)
                                    <li class="d-flex align-items-start gap-2">
                                        <span class="material-icons fs-16 text-muted mt-1">location_on</span>
                                        <span class="text-dark">{{ $estimate->customer_address }}</span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    {{-- Schedule Card --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h4 class="card-title mb-0 d-flex align-items-center gap-2 fz-16">
                                <span class="material-icons text-primary">schedule</span>
                                {{ $isCarBooking ? translate('Rental Schedule & Details') : translate('Schedule_&_Timing') }}
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            @if($isCarBooking)
                                <div class="mb-3">
                                    <span class="fz-12 text-muted d-block">{{ translate('Rental Start') }}</span>
                                    <div class="fw-bold text-dark fs-14 mt-1">
                                        <span class="material-icons fs-16 align-middle text-primary">event</span>
                                        {{ $estimate->start_date ? $estimate->start_date->format('d M Y') : '' }} ({{ $estimate->pickup_time }})
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <span class="fz-12 text-muted d-block">{{ translate('Rental End / Return') }}</span>
                                    <div class="fw-bold text-dark fs-14 mt-1">
                                        <span class="material-icons fs-16 align-middle text-danger">event_available</span>
                                        {{ $estimate->end_date ? $estimate->end_date->format('d M Y') : '' }} ({{ $estimate->drop_time }})
                                    </div>
                                </div>
                                @if($estimate->pickup_type === 'chauffeur')
                                    <div class="mb-3">
                                        <span class="fz-12 text-muted d-block">{{ translate('Pickup Location') }}</span>
                                        <div class="fz-13 text-dark mt-1">{{ $estimate->pickup_location ?? '-' }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <span class="fz-12 text-muted d-block">{{ translate('Destination') }}</span>
                                        <div class="fz-13 text-dark mt-1">{{ $estimate->drop_location ?? '-' }}</div>
                                    </div>
                                @elseif($estimate->pickup_type === 'delivery')
                                    <div class="mb-3">
                                        <span class="fz-12 text-muted d-block">{{ translate('Delivery Address') }}</span>
                                        <div class="fz-13 text-dark mt-1">{{ $estimate->delivery_address ?? '-' }}</div>
                                    </div>
                                @endif
                            @else
                                <div class="mb-3">
                                    <span class="fz-12 text-muted d-block">{{ translate('Proposed Schedule') }}</span>
                                    <div class="fw-bold text-dark fs-15 mt-1">
                                        <span class="material-icons fs-16 align-middle text-primary">event</span>
                                        {{ $estimate->service_schedule ? $estimate->service_schedule->format('d M Y, h:i A') : translate('Not specified') }}
                                    </div>
                                </div>
                            @endif
                            <div>
                                <span class="fz-12 text-muted d-block">{{ translate('Quotation Validity') }}</span>
                                <div class="fz-13 text-dark mt-1">
                                    <span class="material-icons fs-16 align-middle text-warning">hourglass_bottom</span>
                                    {{ $estimate->expired_at ? $estimate->expired_at->format('d M Y') : translate('7 Days') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Card --}}
                    @if($estimate->status == 'pending')
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-3 text-center">
                                <a href="{{ route('provider.estimate.cancel', [$estimate->id]) }}" 
                                   onclick="return confirm('{{ translate('Are you sure you want to cancel this quotation?') }}')"
                                   class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2">
                                    <span class="material-icons">close</span>
                                    {{ translate('Cancel_This_Quotation') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('.copy-btn').on('click', function() {
                let target = $($(this).data('target'));
                navigator.clipboard.writeText(target.val()).then(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.success("{{ translate('Link copied to clipboard!') }}");
                    } else {
                        alert("{{ translate('Link copied!') }}");
                    }
                });
            });
        });
    </script>
@endpush
