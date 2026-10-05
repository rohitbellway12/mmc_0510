@extends('providermanagement::layouts.master')

@section('title', translate('My_Subscriptions'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-3">
                        <h2 class="page-title">{{ translate('My_Subscriptions') }}</h2>
                    </div>

                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'all' ? 'active' : '' }}"
                                    href="{{ url()->current() }}?status=all">{{ translate('All') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'subscribed' ? 'active' : '' }}"
                                    href="{{ url()->current() }}?status=subscribed">{{ translate('Subscribed_Sub_categories') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status == 'unsubscribed' ? 'active' : '' }}"
                                    href="{{ url()->current() }}?status=unsubscribed">{{ translate('Unsubscribed_Sub_categories') }}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{ translate('Total_Sub_Categories') }}:</span>
                            <span class="title-color">{{ $subscribedSubCategories->total() }}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="example" class="table align-middle">
                                            <thead>
                                                <tr>
                                                    <th>{{ translate('SL') }}</th>
                                                    <th>{{ translate('Service_Name') }}</th>
                                                    <th>{{ translate('Category') }}</th>
                                                    <th>{{ translate('Price') }}</th>
                                                    <th class="text-center">{{ translate('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($subscribedSubCategories as $key => $subscribedService)
                                                    <tr>
                                                        <td>{{ $subscribedSubCategories->firstitem() + $key }}</td>
                                                        <td>
                                                            <div class="media gap-2 align-items-center">
                                                                @if (isset($subscribedService->service))
                                                                    <img width="40" class="rounded"
                                                                        src="{{ $subscribedService->service->thumbnail_full_path }}"
                                                                        alt="{{ translate('image') }}">
                                                                    <div class="media-body">
                                                                        <h6 class="">
                                                                            {{ Str::limit($subscribedService->service->name, 30) }}
                                                                        </h6>
                                                                    </div>
                                                                @else
                                                                    <span>{{ translate('Unavailable') }}</span>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::limit($subscribedService->category['name'] ?? translate('Unavailable'), 30) }}
                                                        </td>
                                                        <td>
                                                            @if (isset($subscribedService->service) && $subscribedService->service->variations->isNotEmpty())
                                                                {{ with_currency_symbol($subscribedService->service->variations->first()->price) }}
                                                            @else
                                                                {{ translate('N/A') }}
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <form action="javascript:void(0)" method="post"
                                                                class="hide-div" id="form-{{ $subscribedService->id }}">
                                                                @csrf
                                                                @method('put')
                                                                <input name="service_id"
                                                                    value="{{ $subscribedService->service_id }}">
                                                            </form>
                                                            @if ($subscribedService->is_subscribed == 1)
                                                                <button type="button" class="btn btn--danger subscribe-btn"
                                                                    id="button-{{ $subscribedService->id }}"
                                                                    data-subcategory="{{ $subscribedService->id }}">
                                                                    {{ translate('unsubscribe') }}
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                    class="btn btn--primary subscribe-btn"
                                                                    id="button-{{ $subscribedService->id }}"
                                                                    data-subcategory="{{ $subscribedService->id }}">
                                                                    {{ translate('subscribe') }}
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subscribedSubCategories->links() !!}
                                    </div>
                                </div>
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

        $('.subscribe-btn').on('click', function() {
            let id = $(this).data('subcategory');
            update_subscription(id)
        });

        function update_subscription(id) {

            var form = $('#form-' + id)[0];
            var formData = new FormData(form);

            Swal.fire({
                title: "{{ translate('are_you_sure') }}?",
                text: "{{ translate('want_to_update_subscription') }}",
                type: 'warning',
                showCloseButton: true,
                showCancelButton: true,
                cancelButtonColor: 'var(--bs-secondary)',
                confirmButtonColor: 'var(--bs-primary)',
                cancelButtonText: '{{ translate('cancel') }}',
                confirmButtonText: '{{ translate('yes') }}',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    send_request(formData, id);
                }
            })
        }

        function update_view(id) {
            const subscribe_button = $('#button-' + id);
            if (subscribe_button.hasClass('btn--danger')) {
                subscribe_button.removeClass('btn--danger').addClass('btn--primary').text('{{ translate('subscribe') }}');
            } else {
                subscribe_button.removeClass('btn--primary').addClass('btn--danger').text(
                '{{ translate('unsubscribe') }}');
            }
            subscribe_button.blur();
        }


        function send_request(formData, id) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('provider.service.update-subscription') }}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                beforeSend: function() {
                    $('.preloader').show()
                },
                success: function(response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('successfully data fetched');
                        update_view(id)

                    } else if (response.response_code === 'default_204') {
                        toastr.warning('{{ translate('this_category_is_not_available_in_your_zone') }}')

                    } else {
                        toastr.error('{{ translate('your_subscription_package_category_limit_has_ended') }}');
                    }
                    location.reload();
                },
                error: function(response) {
                    toastr.error('{{ translate('your_subscription_package_category_limit_has_ended') }}')
                },
                complete: function() {
                    $('.preloader').hide()
                }
            });
            return is_success;
        }
    </script>
@endpush
