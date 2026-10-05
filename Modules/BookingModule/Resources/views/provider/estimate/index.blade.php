@extends('providermanagement::layouts.master')

@section('title', translate('Quotations_&_Estimates'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap d-flex justify-content-between flex-wrap align-items-center gap-3 mb-4">
                <div>
                    <h2 class="page-title mb-1">{{ translate('Quotations_&_Booking_Estimates') }}</h2>
                    <p class="text-muted fz-14 mb-0">{{ translate('Create and send price quotations or booking invites directly to customers.') }}</p>
                </div>
                <div>
                    <a href="{{ route('provider.estimate.create') }}" class="btn btn--primary d-flex align-items-center gap-2">
                        <span class="material-icons">add_circle</span>
                        {{ translate('Create_New_Quotation') }}
                    </a>
                </div>
            </div>

            {{-- Stat Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <span class="fz-13 text-muted fw-semibold d-block mb-1">{{ translate('Total_Sent') }}</span>
                                <h3 class="mb-0 fw-bold text-dark">{{ $statusCounts['all'] ?? 0 }}</h3>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width: 48px; height: 48px;">
                                <span class="material-icons">send</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <span class="fz-13 text-muted fw-semibold d-block mb-1">{{ translate('Pending_Acceptance') }}</span>
                                <h3 class="mb-0 fw-bold text-warning">{{ $statusCounts['pending'] ?? 0 }}</h3>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-warning-subtle text-warning" style="width: 48px; height: 48px;">
                                <span class="material-icons">hourglass_top</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <span class="fz-13 text-muted fw-semibold d-block mb-1">{{ translate('Accepted_&_Booked') }}</span>
                                <h3 class="mb-0 fw-bold text-success">{{ $statusCounts['accepted'] ?? 0 }}</h3>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-success-subtle text-success" style="width: 48px; height: 48px;">
                                <span class="material-icons">task_alt</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-center justify-content-between p-3">
                            <div>
                                <span class="fz-13 text-muted fw-semibold d-block mb-1">{{ translate('Canceled') }}</span>
                                <h3 class="mb-0 fw-bold text-danger">{{ $statusCounts['canceled'] ?? 0 }}</h3>
                            </div>
                            <div class="rounded-circle d-flex align-items-center justify-content-center bg-danger-subtle text-danger" style="width: 48px; height: 48px;">
                                <span class="material-icons">cancel</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('provider.estimate.index', ['status' => 'all', 'search' => $queryParams['search']]) }}"
                               class="btn btn-sm {{ $queryParams['status'] == 'all' ? 'btn-primary' : 'btn-outline-secondary' }} rounded-pill px-3">
                                {{ translate('All') }} ({{ $statusCounts['all'] }})
                            </a>
                            <a href="{{ route('provider.estimate.index', ['status' => 'pending', 'search' => $queryParams['search']]) }}"
                               class="btn btn-sm {{ $queryParams['status'] == 'pending' ? 'btn-warning text-dark' : 'btn-outline-secondary' }} rounded-pill px-3">
                                {{ translate('Pending') }} ({{ $statusCounts['pending'] }})
                            </a>
                            <a href="{{ route('provider.estimate.index', ['status' => 'accepted', 'search' => $queryParams['search']]) }}"
                               class="btn btn-sm {{ $queryParams['status'] == 'accepted' ? 'btn-success text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
                                {{ translate('Accepted') }} ({{ $statusCounts['accepted'] }})
                            </a>
                            <a href="{{ route('provider.estimate.index', ['status' => 'canceled', 'search' => $queryParams['search']]) }}"
                               class="btn btn-sm {{ $queryParams['status'] == 'canceled' ? 'btn-danger text-white' : 'btn-outline-secondary' }} rounded-pill px-3">
                                {{ translate('Canceled') }} ({{ $statusCounts['canceled'] }})
                            </a>
                        </div>
                        <form action="{{ route('provider.estimate.index') }}" method="GET" class="d-flex gap-2">
                            <input type="hidden" name="status" value="{{ $queryParams['status'] }}">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <input type="text" name="search" class="form-control" placeholder="{{ translate('Search by ID, Customer, Car...') }}" value="{{ $queryParams['search'] }}">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <span class="material-icons fs-16">search</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">{{ translate('ID_&_Date') }}</th>
                                    <th>{{ translate('Customer') }}</th>
                                    <th>{{ translate('Service') }}</th>
                                    <th>{{ translate('Vehicle_Info') }}</th>
                                    <th>{{ translate('Quoted_Price') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th class="text-end pe-4">{{ translate('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estimates as $estimate)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">#{{ $estimate->readable_id }}</div>
                                            <div class="fz-12 text-muted">{{ $estimate->created_at->format('d M Y, h:i A') }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $estimate->customer_name }}</div>
                                            <div class="fz-12 text-muted">
                                                <a href="tel:{{ $estimate->customer_phone }}" class="text-muted text-decoration-none">
                                                    {{ $estimate->customer_phone }}
                                                </a>
                                            </div>
                                            @if($estimate->customer_email)
                                                <div class="fz-11 text-muted">{{ $estimate->customer_email }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $isCarEstimate = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');
                                            @endphp
                                            @if($isCarEstimate)
                                                <div class="fw-medium text-dark">{{ $estimate->car_model }}</div>
                                                <div>
                                                    <span class="badge bg-primary-subtle text-primary fz-11 px-2 py-0 border border-primary-subtle">
                                                        {{ ucfirst(str_replace('_', ' ', $estimate->module_type)) }} - {{ ucfirst($estimate->pickup_type ?? 'Self') }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="fw-medium text-dark">{{ $estimate->service?->name ?? translate('Custom') }}</div>
                                                <div>
                                                    @if($estimate->service_type == 'quotation_based')
                                                        <span class="badge bg-warning-subtle text-warning fz-11 px-2 py-0 border border-warning-subtle">
                                                            {{ translate('Quotation_Based') }}
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info-subtle text-info fz-11 px-2 py-0 border border-info-subtle">
                                                            {{ translate('Fixed_Price') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($estimate->car_model || $estimate->car_registration_number)
                                                <div class="fw-medium fz-13 text-dark">{{ $estimate->car_model ?? '-' }}</div>
                                                @if($estimate->car_registration_number)
                                                    <span class="badge bg-light text-dark border px-2 py-0 fz-11">{{ $estimate->car_registration_number }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted fz-12">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark fs-15">{{ with_currency_symbol($estimate->total_amount) }}</div>
                                            @if($isCarEstimate && $estimate->start_date)
                                                <div class="fz-11 text-muted" title="{{ translate('Rental Duration') }}">
                                                    <span class="material-icons fz-12 align-middle text-primary">calendar_today</span>
                                                    {{ $estimate->start_date->format('d M') }} - {{ $estimate->end_date ? $estimate->end_date->format('d M Y') : '' }}
                                                </div>
                                            @elseif($estimate->service_schedule)
                                                <div class="fz-11 text-muted" title="{{ translate('Scheduled Date') }}">
                                                    <span class="material-icons fz-12 align-middle">calendar_today</span>
                                                    {{ $estimate->service_schedule->format('d M Y, h:i A') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($estimate->status == 'pending')
                                                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fz-12">
                                                    {{ translate('Pending') }}
                                                </span>
                                            @elseif($estimate->status == 'accepted')
                                                <span class="badge bg-success text-white px-3 py-1 rounded-pill fz-12">
                                                    {{ translate('Accepted') }}
                                                </span>
                                                @if($estimate->car_booking_id)
                                                    <div class="mt-1">
                                                        <a href="{{ route('provider.car.booking.details', [$estimate->car_booking_id]) }}" class="fz-11 text-success fw-semibold">
                                                            <span class="material-icons fz-12 align-middle">directions_car</span>
                                                            {{ translate('Car Booking') }}
                                                        </a>
                                                    </div>
                                                @elseif($estimate->booking)
                                                    <div class="mt-1">
                                                        <a href="{{ route('provider.booking.details', [$estimate->booking_id]) }}" class="fz-11 text-primary fw-semibold">
                                                            {{ translate('Booking') }} #{{ $estimate->booking->readable_id }}
                                                        </a>
                                                    </div>
                                                @endif
                                            @elseif($estimate->status == 'canceled')
                                                <span class="badge bg-danger text-white px-3 py-1 rounded-pill fz-12">
                                                    {{ translate('Canceled') }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary text-white px-3 py-1 rounded-pill fz-12">
                                                    {{ ucfirst($estimate->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-1">
                                                {{-- View Details --}}
                                                <a href="{{ route('provider.estimate.details', [$estimate->id]) }}" 
                                                   class="action-btn btn--light-primary" title="{{ translate('View Details') }}" style="--size: 30px">
                                                    <span class="material-icons">visibility</span>
                                                </a>

                                                {{-- Copy Link Button --}}
                                                <button type="button" class="action-btn btn--light-info copy-link-btn" 
                                                        data-link="{{ $estimate->web_url }}" 
                                                        title="{{ translate('Copy Customer Link') }}" style="--size: 30px">
                                                    <span class="material-icons">content_copy</span>
                                                </button>

                                                {{-- WhatsApp Share --}}
                                                @php
                                                    $cleanPhone = preg_replace('/[^0-9]/', '', $estimate->customer_phone);
                                                    $itemTitle = $isCarEstimate ? $estimate->car_model : ($estimate->service?->name ?? 'service');
                                                    $waText = urlencode(translate("Hello {$estimate->customer_name}, here is your quotation/booking estimate for {$itemTitle} from " . (auth()->user()->provider->company_name ?? 'our service') . ": {$estimate->web_url}"));
                                                @endphp
                                                <a href="https://api.whatsapp.com/send?phone={{ $cleanPhone }}&text={{ $waText }}" target="_blank"
                                                   class="action-btn btn--light-success" title="{{ translate('Share on WhatsApp') }}" style="--size: 30px">
                                                    <span class="material-icons">chat</span>
                                                </a>

                                                {{-- Cancel Button if pending --}}
                                                @if($estimate->status == 'pending')
                                                    <a href="{{ route('provider.estimate.cancel', [$estimate->id]) }}" 
                                                       onclick="return confirm('{{ translate('Are you sure you want to cancel this quotation?') }}')"
                                                       class="action-btn btn--light-danger" title="{{ translate('Cancel Quotation') }}" style="--size: 30px">
                                                        <span class="material-icons">close</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light mb-3" style="width: 64px; height: 64px;">
                                                <span class="material-icons text-muted" style="font-size: 32px;">request_quote</span>
                                            </div>
                                            <h5 class="fw-semibold">{{ translate('No quotations found') }}</h5>
                                            <p class="fz-13 mb-3">{{ translate('You have not sent any quotations or booking estimates yet.') }}</p>
                                            <a href="{{ route('provider.estimate.create') }}" class="btn btn-sm btn--primary">
                                                {{ translate('Create First Quotation') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 d-flex justify-content-end border-top">
                        {!! $estimates->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('.copy-link-btn').on('click', function() {
                let link = $(this).data('link');
                navigator.clipboard.writeText(link).then(function() {
                    if (typeof toastr !== 'undefined') {
                        toastr.success("{{ translate('Quotation link copied to clipboard!') }}");
                    } else {
                        alert("{{ translate('Link copied!') }}");
                    }
                });
            });
        });
    </script>
@endpush
