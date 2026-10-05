@extends('adminmodule::layouts.master')

@section('title',translate('provider_details'))

@section('content')
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title-wrap mb-3">
                <h2 class="page-title">{{translate('Provider_Details')}}</h2>
            </div>

            <div class="mb-3">
                <ul class="nav nav--tabs nav--tabs__style2">
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='overview'?'active':''}}"
                           href="{{url()->current()}}?web_page=overview">{{translate('Overview')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscribed_services'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscribed_services">{{translate('Subscribed_Services')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bookings'?'active':''}}"
                           href="{{url()->current()}}?web_page=bookings">{{translate('Bookings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='serviceman_list'?'active':''}}"
                           href="{{url()->current()}}?web_page=serviceman_list">{{translate('Service_Man_List')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='settings'?'active':''}}"
                           href="{{url()->current()}}?web_page=settings">{{translate('Settings')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='bank_information'?'active':''}}"
                           href="{{url()->current()}}?web_page=bank_information">{{translate('Bank_Information')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='reviews'?'active':''}}"
                           href="{{url()->current()}}?web_page=reviews">{{translate('Reviews')}}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{$webPage=='subscription'?'active':''}}"
                           href="{{url()->current()}}?web_page=subscription&provider_id={{ request()->id }}">{{translate('Business Plan')}}</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="subscribed-tab-pane">
                    <div
                        class="d-flex flex-wrap justify-content-between align-items-center border-bottom mx-lg-4 mb-10 gap-3">
                        <ul class="nav nav--tabs">
                            <li class="nav-item">
                                <a class="nav-link {{$status=='all'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=all">{{translate('All')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='subscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=subscribed">{{translate('Subscribed')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{$status=='unsubscribed'?'active':''}}"
                                   href="{{url()->current()}}?web_page=subscribed_services&status=unsubscribed">{{translate('Unsubscribed')}}</a>
                            </li>
                        </ul>

                        <div class="d-flex gap-2 fw-medium">
                            <span class="opacity-75">{{translate('Total_Services')}}:</span>
                            <span class="title-color">{{$subCategories->total()}}</span>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="all-tab-pane">
                            <div class="card">
                                <div class="card-body">
                                    <div class="data-table-top d-flex flex-wrap gap-10 justify-content-between">
                                        <form
                                            action="{{url()->current()}}?web_page=subscribed_services&status={{$status}}"
                                            class="search-form search-form_style-two"
                                            method="POST">
                                            @csrf
                                            <div class="input-group search-form__input_group">
                                            <span class="search-form__icon">
                                                <span class="material-icons">search</span>
                                            </span>
                                                <input type="search" class="theme-input-style search-form__input"
                                                       value="{{$search??''}}" name="search"
                                                       placeholder="{{translate('search_here')}}">
                                            </div>
                                            <button type="submit" class="btn btn--primary">
                                                {{translate('search')}}
                                            </button>
                                        </form>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="example" class="table align-center align-middle">
                                            <thead>
                                            <tr>
                                                <th>{{translate('SL')}}</th>
                                                <th>{{translate('Service_Name')}}</th>
                                                <th>{{translate('Category')}}</th>
                                                <th>{{translate('Status')}}</th>
                                                <th class="text-center">{{translate('Subscribe_/_Unsubscribe')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($subCategories as $key => $service)
                                                @php
                                                    $subRecord = isset($subscribedMap) ? $subscribedMap->get($service->id) : null;
                                                    $isSubscribed = $subRecord ? ($subRecord->is_subscribed == 1) : false;
                                                    $toggleId = $subRecord ? $subRecord->id : $service->id;
                                                @endphp
                                                <tr>
                                                    <td>{{ $subCategories->firstitem() + $key }}</td>
                                                    <td>
                                                        <div class="media gap-2 align-items-center">
                                                            <img width="40" height="40" class="rounded object-cover"
                                                                 src="{{ $service->thumbnail_full_path }}"
                                                                 alt="{{ translate('image') }}">
                                                            <div class="media-body">
                                                                <h6 class="fw-bold text-title mb-0 fz-14">
                                                                    {{ Str::limit($service->name, 40) }}
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="fw-medium text-title opacity-90">
                                                            {{ $service->category ? $service->category->name : translate('Unavailable') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($isSubscribed)
                                                            <span class="badge bg-soft-success text-success px-2 py-1" style="font-weight: 600;">{{translate('Subscribed')}}</span>
                                                        @else
                                                            <span class="badge bg-soft-danger text-danger px-2 py-1" style="font-weight: 600;">{{translate('Unsubscribed')}}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @can('provider_manage_status')
                                                            <label class="switcher" data-bs-toggle="modal"
                                                                   data-bs-target="#deactivateAlertModal">
                                                                <input class="switcher_input route-alert-reload"
                                                                       data-route="{{route('admin.provider.sub_category.update_subscription',[$toggleId])}}?provider_id={{$provider->id}}"
                                                                       data-message="{{translate('want_to_update_status')}}"
                                                                       type="checkbox" {{$isSubscribed ? 'checked' : ''}}>
                                                                <span class="switcher_control"></span>
                                                            </label>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <span class="text-muted">{{translate('No_services_found')}}</span>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        {!! $subCategories->links() !!}
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
