@extends('providermanagement::layouts.master')

@section('title',translate('Service_Pricing'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{translate('Service_Pricing_Management')}}</h2>
                    </div>

                    <!-- Page Header -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <h5 class="mb-0">{{translate('Set_Your_Custom_Prices')}}</h5>
                                    <p class="text-muted mb-0">{{translate('Admin_base_prices_are_shown_for_reference')}}</p>
                                </div>
                                <div class="col-lg-6">
                                    <div class="d-flex justify-content-lg-end gap-3 mt-lg-0 mt-3">
                                        <div class="search-form">
                                            <form action="{{url()->current()}}" method="GET">
                                                <div class="input-group search-form__input_group">
                                                    <span class="search-form__icon">
                                                        <span class="material-icons">search</span>
                                                    </span>
                                                    <input type="search" class="theme-input-style search-form__input" 
                                                           value="{{$search}}" name="search"
                                                           placeholder="{{translate('search_by_service_name')}}">
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Services Pricing Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                <form action="{{route('provider.service_pricing.bulk_update')}}" method="POST" id="bulk-price-form">
                                    @csrf
                                    <input type="hidden" name="prices" id="prices-data">
                                </form>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-borderless align-middle">
                                    <thead class="align-middle text-nowrap">
                                        <tr>
                                            <th>{{translate('Service')}}</th>
                                            <th>{{translate('Category')}}</th>
                <th>{{translate('Variation')}}</th>
                                            <th>{{translate('Admin_Base_Price')}}</th>
                                            <th>{{translate('Your_Price')}}</th>
                                            <th class="text-center">{{translate('Status')}}</th>
                                            <th class="text-center">{{translate('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($services as $service)
                                            @foreach($service->variations as $variation)
                                                @php
                                                    $customPriceKey = $service->id . '_' . $variation->id;
                                                    $customPrice = $customPrices->get($customPriceKey);
                                                    $hasCustomPrice = $customPrice !== null;
                                                    $currentPrice = $hasCustomPrice ? $customPrice->price : $variation->price;
                                                    $isActive = $hasCustomPrice ? $customPrice->is_active : 0;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <div class="media gap-3 align-items-center">
                                                            <img class="avatar avatar-lg" 
                                                                 src="{{$service->thumbnail_full_path}}" 
                                                                 alt="{{$service->name}}">
                                                            <div class="media-body">
                                                                <h5 class="mb-1">{{Str::limit($service->name, 30)}}</h5>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{$service->Category->name ?? 'N/A'}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-muted">{{$variation->variant ?? 'Standard'}}</span>
                                                    </td>
                                                    <td>
                                                        <span class="text-primary fw-bold">
                                                            {{currency_symbol()}}{{with_decimal_point($variation->price)}}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="input-group" style="max-width: 200px;">
                                                            <span class="input-group-text">{{currency_symbol()}}</span>
                                                            <input type="number" 
                                                                   class="form-control custom-price-input" 
                                                                   id="price-{{$service->id}}-{{$variation->id}}"
                                                                   data-service-id="{{$service->id}}"
                                                                   data-variation-id="{{$variation->id}}"
                                                                   data-custom-price-id="{{$customPrice->id ?? ''}}"
                                                                   value="{{$currentPrice}}"
                                                                   min="0"
                                                                   step="0.01"
                                                                   placeholder="{{translate('Enter_price')}}">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($hasCustomPrice)
                                                            <label class="switcher">
                                                                <input type="checkbox" 
                                                                       class="switcher_input price-status-toggle" 
                                                                       data-id="{{$customPrice->id}}"
                                                                       {{$isActive == 1 ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @else
                                                            <span class="badge badge-secondary">{{translate('Not_Set')}}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex gap-2 justify-content-center">
                                                            <button type="button" 
                                                                    class="btn btn-sm btn--primary save-price-btn"
                                                                    data-service-id="{{$service->id}}"
                                                                    data-variation-id="{{$variation->id}}">
                                                                <span class="material-icons">save</span>
                                                            </button>
                                                            @if($hasCustomPrice)
                                                                <button type="button" 
                                                                        class="btn btn-sm btn--danger reset-price-btn"
                                                                        data-id="{{$customPrice->id}}"
                                                                        data-service-id="{{$service->id}}"
                                                                        data-variation-id="{{$variation->id}}"
                                                                        data-base-price="{{$variation->price}}">
                                                                    <span class="material-icons">refresh</span>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="py-5">
                                                        <span class="text-muted">{{translate('No_services_found')}}</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end mt-3">
                                {!! $services->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        // Save individual price
        $('.save-price-btn').on('click', function() {
            let serviceId = $(this).data('service-id');
            let variationId = $(this).data('variation-id');
            let price = $('#price-' + serviceId + '-' + variationId).val();

            if (!price || price < 0) {
                toastr.error('{{translate("Please_enter_valid_price")}}');
                return;
            }

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('want_to_set_custom_price_for_this_service')}}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '{{translate('cancel')}}',
                confirmButtonText: '{{translate('yes')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    savePrice(serviceId, variationId, price);
                }
            });
        });

        function savePrice(serviceId, variationId, price) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('provider.service_pricing.store')}}",
                method: 'POST',
                data: {
                    service_id: serviceId,
                    variation_id: variationId,
                    price: price
                },
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    if (response.response_code === 'default_store_200') {
                        toastr.success('{{translate("Price_updated_successfully")}}');
                        location.reload();
                    } else {
                        toastr.error('{{translate("Failed_to_update_price")}}');
                    }
                },
                error: function (xhr) {
                    toastr.error('{{translate("Something_went_wrong")}}');
                },
                complete: function () {
                    $('.preloader').hide();
                }
            });
        }

        // Reset price to admin base price
        $('.reset-price-btn').on('click', function() {
            let id = $(this).data('id');
            let serviceId = $(this).data('service-id');
            let variationId = $(this).data('variation-id');
            let basePrice = $(this).data('base-price');

            Swal.fire({
                title: "{{translate('are_you_sure')}}?",
                text: "{{translate('This_will_remove_your_custom_price_and_revert_to_admin_base_price')}}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-danger)',
                cancelButtonText: '{{translate('cancel')}}',
                confirmButtonText: '{{translate('reset')}}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    resetPrice(id, serviceId, variationId, basePrice);
                }
            });
        });

        function resetPrice(id, serviceId, variationId, basePrice) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('provider.service_pricing.index')}}/" + id,
                method: 'DELETE',
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    if (response.response_code === 'default_delete_200') {
                        toastr.success('{{translate("Price_reset_successfully")}}');
                        location.reload();
                    } else {
                        toastr.error('{{translate("Failed_to_reset_price")}}');
                    }
                },
                error: function (xhr) {
                    toastr.error('{{translate("Something_went_wrong")}}');
                },
                complete: function () {
                    $('.preloader').hide();
                }
            });
        }

        // Toggle price status
        $('.price-status-toggle').on('change', function() {
            let id = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{route('provider.service_pricing.update_status')}}",
                method: 'PUT',
                data: {
                    id: id,
                    status: status
                },
                beforeSend: function () {
                    $('.preloader').show();
                },
                success: function (response) {
                    if (response.response_code === 'default_status_update_200') {
                        toastr.success('{{translate("Status_updated_successfully")}}');
                    } else {
                        toastr.error('{{translate("Failed_to_update_status")}}');
                    }
                },
                error: function (xhr) {
                    toastr.error('{{translate("Something_went_wrong")}}');
                },
                complete: function () {
                    $('.preloader').hide();
                }
            });
        });
    </script>
@endpush
