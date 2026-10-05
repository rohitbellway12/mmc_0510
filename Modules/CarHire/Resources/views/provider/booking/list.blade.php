@extends('providermanagement::layouts.master')

@section('title', translate('Car_Hire_Bookings'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('Car_Hire_Bookings') }}</h2>
                        <div class="d-flex justify-content-end">
                            <span class="opacity-75">{{ translate('Total_Bookings') }}:</span>
                            <span class="title-color">{{ $bookings->total() }}</span>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{ url()->current() }}" class="search-form search-form_style-two"
                                    method="GET">
                                    <div class="input-group search-form__input_group">
                                        <span class="search-form__icon">
                                            <span class="material-icons">search</span>
                                        </span>
                                        <input type="search" class="theme-input-style search-form__input"
                                            value="{{ request('search') }}" name="search"
                                            placeholder="{{ translate('search_by_booking_id') }}">
                                    </div>
                                    <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                                </form>

                                <div class="d-flex flex-wrap align-items-center gap-3">
                                    <select name="booking_status" class="form-select theme-input-style"
                                        onchange="window.location.href='{{ url()->current() }}?booking_status='+this.value">
                                        <option value="all" {{ $bookingStatus == 'all' ? 'selected' : '' }}>
                                            {{ translate('All') }}</option>
                                        <option value="pending" {{ $bookingStatus == 'pending' ? 'selected' : '' }}>
                                            {{ translate('Pending') }}</option>
                                        <option value="accepted" {{ $bookingStatus == 'accepted' ? 'selected' : '' }}>
                                            {{ translate('Accepted') }}</option>
                                        <option value="ongoing" {{ $bookingStatus == 'ongoing' ? 'selected' : '' }}>
                                            {{ translate('Ongoing') }}</option>
                                        <option value="completed" {{ $bookingStatus == 'completed' ? 'selected' : '' }}>
                                            {{ translate('Completed') }}</option>
                                        <option value="canceled" {{ $bookingStatus == 'canceled' ? 'selected' : '' }}>
                                            {{ translate('Canceled') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="example" class="table align-middle">
                                    <thead class="text-nowrap">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('Booking_ID') }}</th>
                                            <th>{{ translate('Car_Info') }}</th>
                                            <th>{{ translate('Customer_Info') }}</th>
                                            <th>{{ translate('Total_Amount') }}</th>
                                            <th>{{ translate('Payment_Status') }}</th>
                                            <th>{{ translate('Schedule_Date') }}</th>
                                            <th>{{ translate('Booking_Date') }}</th>
                                            <th>{{ translate('Status') }}</th>
                                            <th>{{ translate('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bookings as $key => $booking)
                                            <tr>
                                                <td>{{ $key + $bookings->firstItem() }}</td>
                                                <td>
                                                    <a href="{{ route('provider.car.booking.details', [$booking->id]) }}">
                                                        {{ $booking->id }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($booking->car)
                                                        {{ $booking->car->car_brand }} {{ $booking->car->car_model }}<br>
                                                        <span
                                                            class="fz-12 text-muted">{{ $booking->car->registration_number }}</span>
                                                    @else
                                                        {{ translate('N/A') }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($booking->user)
                                                        {{ $booking->user->first_name }}
                                                        {{ $booking->user->last_name }}<br>
                                                        {{ $booking->user->phone }}
                                                    @else
                                                        {{ translate('N/A') }}
                                                    @endif
                                                </td>
                                                <td>{{ with_currency_symbol($booking->total_amount) }}</td>
                                                <td>
                                                    <span
                                                        class="badge badge badge-{{ $booking->is_paid ? 'success' : ($booking->payment_status == 'partially_paid' ? 'warning' : 'danger') }}">
                                                        {{ $booking->is_paid ? translate('Paid') : ($booking->payment_status == 'partially_paid' ? translate('Partially Paid') : translate('Unpaid')) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ date('d-M-Y', strtotime($booking->start_date)) }}
                                                    {{ $booking->pickup_time }}
                                                </td>
                                                <td>{{ $booking->created_at->format('d-M-Y') }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ ucfirst($booking->booking_status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="table-actions gap-2">
                                                        <a href="{{ route('provider.car.booking.details', [$booking->id]) }}"
                                                            type="button" class="action-btn btn--light-primary"
                                                            style="--size: 30px">
                                                            <span class="material-icons">visibility</span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="10">{{ translate('No_bookings_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {!! $bookings->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
