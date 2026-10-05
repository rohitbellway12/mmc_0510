@extends('providermanagement::layouts.master')

@section('title', translate('My Tyres'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                <h2 class="page-title">{{ translate('My Tyres') }}</h2>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('provider.tyre.create') }}" class="btn btn--primary">
                        <span class="material-icons">add</span>
                        {{ translate('Add New Tyre') }}
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                        <form action="{{ url()->current() }}" class="search-form search-form_style-two" method="GET">
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
                                    <th>{{ translate('Brand/Model') }}</th>
                                    <th>{{ translate('Type / Spec') }}</th>
                                    <th>{{ translate('Size') }}</th>
                                    <th>{{ translate('Price') }}</th>
                                    <th>{{ translate('Stock') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th>{{ translate('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tyres as $key => $tyre)
                                    <tr>
                                        <td>{{ $tyres->firstitem() + $key }}</td>
                                        <td>
                                            @if (!empty($tyre->images) && count($tyre->images) > 0)
                                                <img width="50" class="rounded"
                                                    src="{{ asset('storage/app/public/tyre/' . $tyre->images[0]) }}"
                                                    alt="">
                                            @else
                                                <img width="50" class="rounded"
                                                    src="{{ asset('public/assets/admin-module/img/placeholder.png') }}"
                                                    alt="">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong class="text-primary">{{ $tyre->brand }}</strong>
                                                <small class="text-muted">{{ $tyre->model ?? 'N/A' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge bg-info text-white text-uppercase" style="width: fit-content;">
                                                    {{ str_replace('_', ' ', $tyre->tyre_type ?? 'Tubeless') }}
                                                </span>
                                                <small class="text-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $tyre->vehicle_type ?? 'Car')) }}
                                                </small>
                                            </div>
                                        </td>
                                        <td><strong>{{ $tyre->size }}</strong></td>
                                        <td>{{ currency_symbol() }}{{ with_decimal_point($tyre->price) }}</td>
                                        <td>{{ $tyre->stock }}</td>
                                        <td>
                                            <label class="switcher mx-auto">
                                                <input type="checkbox" class="switcher_input"
                                                    onclick="route_alert('{{ route('provider.tyre.status-update', [$tyre->id]) }}', '{{ translate('want_to_update_status') }}')"
                                                    {{ $tyre->status ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('provider.tyre.edit', [$tyre->id]) }}"
                                                    class="action-btn btn--light-primary" style="--size: 30px">
                                                    <span class="material-icons">edit</span>
                                                </a>
                                                <form action="{{ route('provider.tyre.delete', [$tyre->id]) }}"
                                                    method="POST" id="delete-{{ $tyre->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn btn--light-danger"
                                                        style="--size: 30px"
                                                        onclick="form_alert('delete-{{ $tyre->id }}', '{{ translate('want_to_delete_this_tyre') }}?')">
                                                        <span class="material-icons">delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">{{ translate('No_tyres_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">
                        {!! $tyres->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
