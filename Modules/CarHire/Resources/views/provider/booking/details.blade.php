@extends('providermanagement::layouts.master')

@section('title', translate('Car_Hire_Booking_Details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-4">
                <h2 class="page-title">{{ translate('Booking_Details') }}</h2>
            </div>

            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <h3 class="c1">{{ translate('Booking') }} # {{ $booking->id }}</h3>
                        <span
                            class="badge badge-{{ $booking->booking_status == 'ongoing'
                                ? 'warning'
                                : ($booking->booking_status == 'completed'
                                    ? 'success'
                                    : ($booking->booking_status == 'canceled'
                                        ? 'danger'
                                        : 'info')) }}">
                            {{ ucwords($booking->booking_status) }}
                        </span>
                    </div>
                    <p class="opacity-75 fz-12">{{ translate('Booking_Placed') }}:
                        {{ $booking->created_at->format('d-M-Y h:i A') }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h3 class="mb-3">{{ translate('Booking_Summary') }}</h3>
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Car_Details') }}</th>
                                            <th>{{ translate('Pricing_Type') }}</th>
                                            <th>{{ translate('Price') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-3">
                                                    @if ($booking->car && $booking->car->car_image)
                                                        <img width="70" class="rounded"
                                                            src="{{ asset('storage/app/public/car/' . $booking->car->car_image) }}"
                                                            alt="">
                                                    @endif
                                                    <div>
                                                        <h5 class="mb-1">{{ $booking->car->car_brand }}
                                                            {{ $booking->car->car_model }}</h5>
                                                        <span
                                                            class="fz-12 text-muted">{{ $booking->car->registration_number }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $booking->car->service_type)) }}</td>
                                            <td>{{ with_currency_symbol($booking->total_amount) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end mt-4">
                                <div class="col-sm-6">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                @if(isset($booking->rent_amount) && $booking->rent_amount > 0)
                                                    <tr>
                                                        <td>{{ translate('Rental Fee') }}</td>
                                                        <td class="text-end">
                                                            {{ with_currency_symbol($booking->rent_amount) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if(isset($booking->delivery_fee) && $booking->delivery_fee > 0)
                                                    <tr>
                                                        <td>{{ translate('Delivery Fee') }}</td>
                                                        <td class="text-end">
                                                            {{ with_currency_symbol($booking->delivery_fee) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                                @if(isset($booking->security_deposit) && $booking->security_deposit > 0)
                                                    <tr>
                                                        <td>
                                                            {{ translate('Refundable Security Deposit') }}
                                                            <span class="badge badge-soft-info ms-1 fz-10">{{ translate('Refundable') }}</span>
                                                        </td>
                                                        <td class="text-end text-primary">
                                                            <strong>{{ with_currency_symbol($booking->security_deposit) }}</strong>
                                                        </td>
                                                    </tr>
                                                @endif
                                                <tr>
                                                    <td><strong>{{ translate('Total_Amount') }}</strong></td>
                                                    <td class="text-end">
                                                        <strong class="text-dark fs-16">{{ with_currency_symbol($booking->total_amount) }}</strong>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>{{ translate('Payment_Method') }}</td>
                                                    <td class="text-end text-capitalize">
                                                        {{ str_replace('_', ' ', $booking->payment_method) }}</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ translate('Payment_Status') }}</td>
                                                    <td class="text-end">
                                                        <span class="text-{{ $booking->is_paid ? 'success' : ($booking->payment_status == 'partially_paid' ? 'warning' : 'danger') }}">
                                                            {{ $booking->is_paid ? translate('Paid') : ($booking->payment_status == 'partially_paid' ? translate('Partially Paid') : translate('Unpaid')) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            @if ($booking->pickup_type == 'chauffeur')
                                <h3 class="mb-3">{{ translate('Ride_Route') }}</h3>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <span class="material-icons text-primary">trip_origin</span>
                                        <strong>{{ translate('Pickup') }}:</strong>
                                        <p class="mb-0">{{ $booking->pickup_location ?? translate('N/A') }}</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="material-icons text-success">place</span>
                                        <strong>{{ translate('Drop') }}:</strong>
                                        <p class="mb-0">{{ $booking->drop_location ?? translate('N/A') }}</p>
                                    </div>
                                </div>
                            @elseif($booking->pickup_type == 'delivery')
                                <h3 class="mb-3">{{ translate('Delivery_Address') }}</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="material-icons text-primary">place</span>
                                    <p class="mb-0">{{ $booking->delivery_address ?? translate('N/A') }}</p>
                                </div>
                            @else
                                <h3 class="mb-3">{{ translate('Pickup_Information') }}</h3>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="material-icons text-primary">info</span>
                                    <p class="mb-0">{{ translate('Self_Pickup_from_Provider_Location') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h3 class="c1 mb-3">{{ translate('Booking_Setup') }}</h3>
                            <form action="{{ route('provider.car.booking.status-update', [$booking->id]) }}"
                                method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">{{ translate('Change_Status') }}</label>
                                    <select name="booking_status" class="form-select"
                                        {{ $booking->booking_status == 'canceled' || $booking->booking_status == 'completed' ? 'disabled' : '' }}>
                                        <option value="pending"
                                            {{ $booking->booking_status == 'pending' ? 'selected' : '' }}>
                                            {{ translate('Pending') }}</option>
                                        <option value="accepted"
                                            {{ $booking->booking_status == 'accepted' ? 'selected' : '' }}>
                                            {{ translate('Accepted') }}</option>
                                        <option value="ongoing"
                                            {{ $booking->booking_status == 'ongoing' ? 'selected' : '' }}>
                                            {{ translate('Ongoing') }}</option>
                                        <option value="completed"
                                            {{ $booking->booking_status == 'completed' ? 'selected' : '' }}>
                                            {{ translate('Completed') }}</option>
                                        <option value="canceled"
                                            {{ $booking->booking_status == 'canceled' ? 'selected' : '' }}>
                                            {{ translate('Canceled') }}</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn--primary w-100"
                                    {{ $booking->booking_status == 'canceled' || $booking->booking_status == 'completed' ? 'disabled' : '' }}>
                                    {{ translate('Update_Status') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h3 class="c1 mb-3">{{ translate('Customer_Information') }}</h3>
                            <div class="media gap-3 align-items-center">
                                <img width="50" height="50" class="rounded-circle"
                                    src="{{ $booking->user->profile_image_full_path }}" alt="">
                                <div class="media-body">
                                    <h5 class="mb-1">{{ $booking->user->first_name }} {{ $booking->user->last_name }}
                                    </h5>
                                    <p class="fz-12 mb-0">{{ $booking->user->phone }}</p>
                                    <p class="fz-12 mb-0">{{ $booking->user->email }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h3 class="c1 mb-3">{{ translate('Schedule_Information') }}</h3>
                            <ul class="list-unstyled">
                                <li class="mb-2"><strong>{{ translate('Start_Date') }}:</strong>
                                    {{ date('d-M-Y', strtotime($booking->start_date)) }}</li>
                                <li class="mb-2"><strong>{{ translate('Pickup_Time') }}:</strong>
                                    {{ $booking->pickup_time }}</li>
                                <li class="mb-2"><strong>{{ translate('End_Date') }}:</strong>
                                    {{ date('d-M-Y', strtotime($booking->end_date)) }}</li>
                                <li class="mb-2"><strong>{{ translate('Drop_Time') }}:</strong>
                                    {{ $booking->drop_time }}</li>
                                <li class="mb-2"><strong>{{ translate('Pickup_Type') }}:</strong>
                                    {{ ucfirst(str_replace('_', ' ', $booking->pickup_type)) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
