@extends('providermanagement::layouts.master')

@section('title', translate('My Cars'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h2 class="page-title">{{ translate('My Cars') }}</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('provider.car.create') }}" class="btn btn--primary">
                        <span class="material-icons">add</span>
                        {{ translate('Add New Car') }}
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <div class="mb-3">
                            <ul class="nav nav-pills gap-2 p-1 bg-white rounded-pill shadow-sm" style="width: fit-content;">
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill {{ $category == 'all' ? 'active' : '' }}"
                                        href="{{ url()->current() }}?category=all&search={{ $search }}">{{ translate('All') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill {{ $category == 'car_hire' ? 'active' : '' }}"
                                        href="{{ url()->current() }}?category=car_hire&search={{ $search }}">{{ translate('Car Hire') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link rounded-pill {{ $category == 'chauffeur' ? 'active' : '' }}"
                                        href="{{ url()->current() }}?category=chauffeur&search={{ $search }}">{{ translate('Chauffeur Service') }}</a>
                                </li>
                            </ul>
                        </div>

                        <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="GET">
                            <input type="hidden" name="category" value="{{ $category }}">
                            <div class="input-group search-form__input_group">
                                <span class="search-form__icon">
                                    <span class="material-icons">search</span>
                                </span>
                                <input type="search" class="theme-input-style search-form__input"
                                    value="{{ $search }}" name="search"
                                    placeholder="{{ translate('search_by_brand_or_model') }}">
                            </div>
                            <button type="submit" class="btn btn--primary">{{ translate('search') }}</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="align-middle text-nowrap">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('Image') }}</th>
                                    <th>{{ translate('Vehicle_Info') }}</th>
                                    <th>{{ translate('Service') }}</th>
                                    <th>{{ translate('Rate') }}</th>
                                    <th>{{ translate('Documents') }}</th>
                                    <th>{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cars as $key => $car)
                                    <tr>
                                        <td>{{ $cars->firstitem() + $key }}</td>
                                        <td>
                                            @if (!empty($car->images) && count($car->images) > 0)
                                                <img width="50"
                                                    src="{{ asset('storage/app/public/car/' . $car->images[0]) }}"
                                                    alt="">
                                            @else
                                                <img width="50"
                                                    src="{{ asset('public/assets/admin-module/img/media/car.png') }}"
                                                    alt="">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">{{ $car->brand ?? '' }} {{ $car->model ?? '' }}</div>
                                            <div class="fz-12 text-muted">
                                                @if($car->registration_number)
                                                    <span class="badge bg-light text-dark border">{{ $car->registration_number }}</span>
                                                @endif
                                                @if($car->manufacture_year || $car->year)
                                                    <span>• {{ $car->manufacture_year ?? $car->year }}</span>
                                                @endif
                                                @if($car->transmission_type || $car->transmission)
                                                    <span>• {{ $car->transmission_type ?? $car->transmission }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($car->service_category == 'car_hire')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle">{{ translate('Car Hire (Self-Drive)') }}</span>
                                            @elseif($car->service_category == 'chauffeur')
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ translate('Chauffeur Service') }}</span>
                                                @if($car->chauffeur_tier)
                                                    <div class="fz-11 text-muted mt-1">{{ ucwords(str_replace('_', ' ', $car->chauffeur_tier)) }}</div>
                                                @endif
                                            @else
                                                {{ $car->category->name ?? '' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($car->service_category == 'car_hire')
                                                <div class="fw-bold text-primary">{{ currency_symbol() }}{{ with_decimal_point($car->daily_rate ?? $car->daily_rent ?? 0) }}/{{ translate('day') }}</div>
                                                @if(($car->hourly_rate ?? 0) > 0)
                                                    <div class="fz-11 text-muted">{{ currency_symbol() }}{{ with_decimal_point($car->hourly_rate) }}/{{ translate('hr') }}</div>
                                                @endif
                                            @elseif($car->service_type == 'full_day' || $car->pricing_type == 'daily')
                                                <div class="fw-bold text-primary">{{ currency_symbol() }}{{ with_decimal_point($car->daily_rate ?? 0) }}/{{ translate('day') }}</div>
                                            @else
                                                <div class="fw-bold text-primary">{{ currency_symbol() }}{{ with_decimal_point($car->hourly_rate ?? 0) }}/{{ translate('hr') }}</div>
                                                @if(($car->min_booking_hours ?? 1) > 1)
                                                    <div class="fz-11 text-muted">{{ translate('Min') }} {{ $car->min_booking_hours }} {{ translate('hrs') }}</div>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @php
                                                    $docs = [
                                                        'DL' => [
                                                            'field' => 'driving_license',
                                                            'title' => 'Driving License',
                                                        ],
                                                        'RC' => [
                                                            'field' => 'vehicle_registration',
                                                            'title' => 'Registration (V5C)',
                                                        ],
                                                        'IN' => [
                                                            'field' => 'insurance_documents',
                                                            'title' => 'Insurance',
                                                        ],
                                                        $car->service_category == 'chauffeur' ? 'MOT' : 'AP' => [
                                                            'field' => 'mot_certificate',
                                                            'title' =>
                                                                $car->service_category == 'chauffeur'
                                                                    ? 'MOT Certificate'
                                                                    : 'Address Proof',
                                                        ],
                                                    ];
                                                @endphp
                                                @foreach ($docs as $code => $info)
                                                    @if ($car->{$info['field']})
                                                        <span class="badge badge-success"
                                                            title="{{ translate($info['title']) }}">{{ $code }}</span>
                                                    @else
                                                        <span class="badge badge-secondary" style="opacity: 0.5"
                                                            title="{{ translate('Missing') }} {{ translate($info['title']) }}">{{ $code }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('provider.car.edit', [$car->id]) }}"
                                                    class="action-btn btn--light-primary" style="--size: 30px">
                                                    <span class="material-icons">edit</span>
                                                </a>
                                                <form action="{{ route('provider.car.destroy', [$car->id]) }}"
                                                    method="POST" id="delete-{{ $car->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn btn--light-danger"
                                                        style="--size: 30px"
                                                        onclick="form_alert('delete-{{ $car->id }}', '{{ translate('want_to_delete_this_car') }}?')">
                                                        <span class="material-icons">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">{{ translate('No_cars_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $cars->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
