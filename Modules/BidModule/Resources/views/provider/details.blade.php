@extends('providermanagement::layouts.master')

@section('title',translate('Request Details'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-wrap mb-3 d-flex align-items-center justify-content-between">
                    <h2 class="page-title">{{translate('Request Details')}}</h2>

                    <div class=""><i class="material-icons ripple-animation" data-bs-toggle="modal"
                                     data-bs-target="#alertModal">info</i></div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none h-100">
                                    <div class="card-body">
                                        <div class="media flex-wrap gap-3">
                                            <img width="140" class="radius-10"
                                                 src="{{onErrorImage(
                                                        $post?->customer?->profile_image,
                                                        asset('storage/app/public/user/profile_image').'/' . $post?->customer?->profile_image,
                                                        asset('public/assets/placeholder.png') ,
                                                        'user/profile_image/')}}"
                                                 alt="{{ translate('profile-image') }}">
                                            <div class="media-body">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="material-icons text-primary">person</span>
                                                    <h4>{{translate('Customer Information')}}</h4>
                                                </div>
                                                <h5 class="text-primary mb-1">{{$post?->customer?->first_name.' '.$post?->customer?->last_name}}</h5>
                                                <p class="text-muted fs-12">
                                                    @if($distance)
                                                        {{$distance . ' ' . translate('away from you')}}
                                                    @endif
                                                </p>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="material-icons">map</span>
                                                    <p>{{Str::limit($post?->service_address?->address??translate('not_available'), 100)}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card bg-primary-light shadow-none h-100">
                                    <div class="card-body d-flex flex-column justify-content-between">
                                        <div>
                                            @php
                                                $allServices = ($post->services && $post->services->count() > 0)
                                                    ? $post->services
                                                    : ($post->service ? collect([$post->service]) : collect([]));
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img width="18"
                                                         src="{{asset('public/assets/provider-module')}}/img/media/more-info.png"
                                                         alt="">
                                                    <h4 class="m-0">{{translate('Service Information')}}</h4>
                                                </div>
                                                @if($allServices->count() > 1)
                                                    <span class="badge bg-primary text-white fs-12 px-2.5 py-1">
                                                        <i class="material-icons fs-14 align-middle">build</i> {{$allServices->count()}} {{translate('Services Requested')}}
                                                    </span>
                                                @endif
                                            </div>

                                            @if($allServices->count() > 0)
                                                <div class="d-flex flex-column gap-2 mb-3">
                                                    @foreach($allServices as $idx => $serv)
                                                        <div class="p-2.5 rounded border d-flex align-items-center justify-content-between gap-3 shadow-xs"
                                                             style="background: rgba(var(--c1-rgb, 4, 97, 68), 0.05);">
                                                            <div class="media align-items-center gap-3">
                                                                <div class="position-relative">
                                                                    <img width="46" height="46" class="rounded object-fit-cover border"
                                                                         src="{{ $serv->thumbnail_full_path ?? onErrorImage(
                                                                                $serv?->subCategory?->image ?? $post?->sub_category?->image,
                                                                                asset('storage/app/public/category').'/' . ($serv?->subCategory?->image ?? $post?->sub_category?->image),
                                                                                asset('public/assets/placeholder.png'),
                                                                                'category/') }}"
                                                                         alt="{{ $serv->name }}">
                                                                    @if($allServices->count() > 1)
                                                                        <span class="badge bg-primary text-white position-absolute top-0 start-0 translate-middle rounded-circle p-1" style="font-size: 10px; min-width: 20px; line-height: 12px; text-align: center;">
                                                                            {{ $idx + 1 }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                                <div class="media-body">
                                                                    <h5 class="mb-1 fw-bold">{{ $serv->name }}</h5>
                                                                    <div class="text-muted fs-12">
                                                                        <span class="badge bg-white text-primary border" style="font-size: 11px;">
                                                                            {{ $serv->subCategory?->name ?? ($serv->category?->name ?? $post?->sub_category?->name ?? translate('Service')) }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="media gap-2 mb-4">
                                                    <img width="30"
                                                         src="{{onErrorImage(
                                                                $post?->sub_category?->image,
                                                                asset('storage/app/public/category').'/' . $post?->sub_category?->image,
                                                                asset('public/assets/placeholder.png') ,
                                                                'category/')}}"
                                                         alt="{{ translate('category') }}">
                                                    <div class="media-body">
                                                        <h5>{{$post?->service?->name ?? translate('Service not available')}}</h5>
                                                        <div class="text-muted fs-12">{{$post?->sub_category?->name}}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="p-3 rounded border d-flex flex-column gap-2 mt-2"
                                             style="background: rgba(var(--c1-rgb, 4, 97, 68), 0.05);">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 fs-13">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <span class="material-icons text-primary fs-16">schedule</span>
                                                    <span>{{translate('Booking Request Time')}}</span>
                                                </div>
                                                <span class="fw-bold">{{$post->created_at->format('d/m/Y h:ia')}}</span>
                                            </div>
                                            <div class="border-top pt-2 d-flex align-items-center justify-content-between flex-wrap gap-2 fs-13">
                                                <div class="d-flex align-items-center gap-2 text-muted">
                                                    <span class="material-icons text-success fs-16">event</span>
                                                    <span>{{translate('Service Scheduled Time')}}</span>
                                                </div>
                                                <span class="fw-bold text-success">{{date('d/m/Y h:ia',strtotime($post->booking_schedule))}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card h-100 shadow-sm border" style="border-top: 4px solid #0055ff !important;">
                                    <div class="card-header d-flex align-items-center justify-content-between bg-primary-light shadow-none">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-icons text-primary">directions_car</span>
                                            <h5 class="text-uppercase m-0 fw-bold">{{translate('Vehicle & Damage Details')}}</h5>
                                        </div>
                                        @if($post->car_registration_number)
                                            <span class="badge bg-primary text-white fs-12 px-2 py-1">{{$post->car_registration_number}}</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <span class="text-muted fs-12 d-block">{{translate('Car Model')}}</span>
                                                <strong class="fs-15 text-dark">{{$post->car_model ?? translate('Not Specified')}}</strong>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="text-muted fs-12 d-block">{{translate('Registration Number')}}</span>
                                                <strong class="fs-15 text-primary">{{$post->car_registration_number ?? translate('Not Specified')}}</strong>
                                            </div>
                                            <div class="col-12">
                                                <span class="text-muted fs-12 d-block">{{translate('Damage / Problem Description')}}</span>
                                                <div class="p-2 rounded bg-light border text-dark fs-13 mt-1">
                                                    {{$post->damage_description ?? ($post->service_description ?? translate('No specific damage description provided.'))}}
                                                </div>
                                            </div>
                                            @if(!empty($post->car_images_full_path))
                                                <div class="col-12">
                                                    <span class="text-muted fs-12 d-block mb-2">{{translate('Damaged Vehicle Photos')}} ({{count($post->car_images_full_path)}})</span>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($post->car_images_full_path as $imgUrl)
                                                            <div class="position-relative d-inline-block">
                                                                <a href="{{$imgUrl}}" target="_blank" title="{{translate('Click to see full image')}}">
                                                                    <img src="{{$imgUrl}}" 
                                                                         class="rounded border shadow-sm" 
                                                                         style="height: 120px; width: 120px; object-fit: cover;"
                                                                         alt="{{translate('Car Damage Photo')}}">
                                                                    <div class="badge bg-dark position-absolute bottom-0 end-0 m-1 opacity-75 text-white" style="font-size: 10px;">
                                                                        <span class="material-icons fs-12 align-middle">zoom_in</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="card h-100">
                                    <div
                                        class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                        <img width="18"
                                             src="{{asset('public/assets/provider-module')}}/img/icons/edit-info.png"
                                             alt="">
                                        <h5 class="text-uppercase">{{translate('Service Description')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        <p>{{$post->service_description}}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="card">
                                    <div
                                        class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                        <img width="18"
                                             src="{{asset('public/assets/provider-module')}}/img/icons/instruction.png"
                                             alt="">
                                        <h5 class="text-uppercase">{{translate('Additional Instruction')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        <ul class="d-flex flex-column gap-3 px-3 instruction-details">
                                            @forelse($post?->addition_instructions as $item)
                                                <li>{{$item->details}}</li>
                                            @empty
                                                <span>{{translate('No_Addition_Instructions')}}</span>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @if(isset($post->question_answers) && count($post->question_answers) > 0)
                            <div class="col-12">
                                <div class="card border border-info-subtle shadow-xs mb-3">
                                    <div class="card-header d-flex align-items-center gap-2 bg-info-light shadow-none" style="background-color: #f0f9ff;">
                                        <span class="material-icons text-info">quiz</span>
                                        <h5 class="text-uppercase mb-0 text-dark fw-bold">{{translate('Customer Answered Questions')}}</h5>
                                    </div>
                                    <div class="card-body pb-4">
                                        <div class="row g-3">
                                            @foreach($post->question_answers as $qa)
                                                <div class="col-md-6 col-lg-3">
                                                    <div class="p-3 rounded-3 border bg-white h-100 shadow-xs">
                                                        <strong class="d-block text-dark mb-2 fs-13">
                                                            <span class="material-icons fs-14 text-primary align-middle me-1">help_outline</span>
                                                            {{ $qa->question?->question_text ?? translate('Question') }}
                                                        </strong>
                                                        <span class="badge bg-primary-light text-primary fs-12 px-2 py-1 border border-primary-subtle rounded-2">
                                                            {{ $qa->answer_value ?? '-' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if(!$post->is_booked && !$post?->bids->contains('provider_id', auth()->user()->provider->id))
                            <div class="col-12">
                                <div class="card shadow-sm border-0" style="background: linear-gradient(135deg, #f0fdf4 0%, #e6f9ed 100%); border: 2px solid #22c55e !important;">
                                    <div class="card-header bg-transparent border-0 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-icons text-success" style="font-size: 28px;">request_quote</span>
                                            <div>
                                                <h4 class="text-success m-0 fw-bold">{{translate('Send Your Quotation to Customer')}}</h4>
                                                <small class="text-muted">{{translate('Enter your price and work terms below to submit your quotation to the customer')}}</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-success text-white px-3 py-2 fs-12">{{translate('Ready For Quotation')}}</span>
                                    </div>
                                    <div class="card-body pt-3">
                                        <form action="{{route('provider.booking.post.update_status', [$post->id])}}" method="GET">
                                            <input type="hidden" name="status" value="accept">
                                            <div class="row g-3 align-items-end">
                                                <div class="col-md-4">
                                                    <label class="form-label fw-bold text-dark">
                                                        {{translate('Your Offered Price')}} ({{currency_symbol()}}) <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text fw-bold fs-16 bg-white border-end-0">{{currency_symbol()}}</span>
                                                        <input type="number" 
                                                               name="offered_price" 
                                                               class="form-control form-control-lg fw-bold text-success border-start-0 fs-18" 
                                                               placeholder="e.g. 1500" 
                                                               step="any" 
                                                               min="{{$post?->service?->min_bidding_price ?? 1}}" 
                                                               required>
                                                    </div>
                                                    <small class="text-muted">{{translate('Base price')}}: {{with_currency_symbol($post?->service?->min_bidding_price ?? 0)}}</small>
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label fw-bold text-dark">
                                                        {{translate('Quotation Note / Work Terms')}}
                                                    </label>
                                                    <input type="text" 
                                                           name="provider_note" 
                                                           class="form-control form-control-lg fs-14" 
                                                           placeholder="{{translate('e.g. Genuine parts, 2 hours completion with warranty')}}">
                                                    <small class="text-muted">{{translate('Mention timeline, parts warranty or any terms')}}</small>
                                                </div>
                                                <div class="col-md-3">
                                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" style="height: 48px;">
                                                        <span class="material-icons">send</span>
                                                        {{translate('Send Quotation')}}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($post?->bids->contains('provider_id', auth()->user()->provider->id))
                            <div class="{{$bid_offers_visibility_for_providers ? 'col-lg-6' : 'col-lg-12'}}">
                                <div class="card h-100 border-success" style="border: 2px solid #22c55e;">
                                    <div
                                        class="card-header d-flex align-items-center justify-content-between bg-success text-white shadow-none">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="material-icons text-white">check_circle</span>
                                            <h5 class="text-uppercase m-0 text-white">{{translate('YOUR SUBMITTED QUOTATION')}}</h5>
                                        </div>
                                        <span class="badge bg-white text-success px-2 py-1 fs-12">{{translate('Quotation Sent')}}</span>
                                    </div>
                                    <div class="card-body pb-4">
                                        <div id="viewQuotationDetails">
                                            <div class="d-flex gap-2 flex-wrap align-items-center fs-12 mb-3">
                                                <span class="text-muted fs-14">{{translate('Price Offered')}}:</span>
                                                <h2 class="text-success mb-0 fw-bold">{{with_currency_symbol($post?->bids->where('provider_id', auth()->user()->provider->id)->first()?->offered_price ?? 0)}}</h2>
                                                <span
                                                    class="text-muted fs-12">({{$post->updated_at->diffForHumans()}})</span>
                                            </div>

                                            <h5 class="text-muted mb-1">{{translate('Your Note')}} :</h5>
                                            <p class="p-2 bg-light rounded text-dark">{{$post?->bids?->where('provider_id', auth()->user()->provider->id)->first()?->provider_note ?? translate('No note provided')}}</p>

                                            <div class="d-flex justify-content-end gap-2 mt-3">
                                                <button type="button" class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1" onclick="$('#editQuotationForm').slideDown(); $('#viewQuotationDetails').slideUp();">
                                                    <span class="material-icons fs-14">edit</span>
                                                    {{translate('Update Price / Resend')}}
                                                </button>
                                                <button type="button" class="btn btn-sm btn--danger d-flex align-items-center gap-1" data-bs-toggle="modal"
                                                        data-bs-target="#withdrawRequestModal--{{$post['id']}}">
                                                    <span class="material-icons fs-14">cancel</span>
                                                    {{translate('Cancel Quotation')}}
                                                </button>
                                            </div>
                                        </div>

                                        <div id="editQuotationForm" style="display: none;">
                                            <form action="{{route('provider.booking.post.update_status', [$post->id])}}" method="GET">
                                                <input type="hidden" name="status" value="accept">
                                                <h6 class="fw-bold mb-3 text-primary d-flex align-items-center gap-1">
                                                    <span class="material-icons fs-16">edit</span>
                                                    {{translate('Update Your Quotation & Resend')}}
                                                </h6>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark fs-12">{{translate('Offered Price')}} ({{currency_symbol()}})</label>
                                                    <input type="number" name="offered_price" class="form-control" value="{{$post?->bids->where('provider_id', auth()->user()->provider->id)->first()?->offered_price ?? ''}}" min="{{$post?->service?->min_bidding_price ?? 1}}" step="any" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold text-dark fs-12">{{translate('Quotation Note / Work Terms')}}</label>
                                                    <input type="text" name="provider_note" class="form-control" value="{{$post?->bids->where('provider_id', auth()->user()->provider->id)->first()?->provider_note ?? ''}}">
                                                </div>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="$('#editQuotationForm').slideUp(); $('#viewQuotationDetails').slideDown();">
                                                        {{translate('Cancel')}}
                                                    </button>
                                                    <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                                        <span class="material-icons fs-14">send</span>
                                                        {{translate('Save & Resend Quotation')}}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @if($bid_offers_visibility_for_providers)
                                <div class="col-lg-6">
                                    <div class="card h-100">
                                        <div
                                            class="card-header d-flex align-items-center gap-2 bg-primary-light shadow-none">
                                            <img width="18"
                                                 src="{{asset('public/assets/provider-module')}}/img/icons/provider.png"
                                                 alt="">
                                            <h5 class="text-uppercase">{{translate('OTHER PROVIDER OFFERING')}}</h5>
                                        </div>
                                        <div class="card-body pb-4">
                                            @foreach($post->bids as $item)
                                                @if($item?->provider?->id != auth()->user()->provider->id)
                                                    <div class="d-flex justify-content-between gap-3 mb-4">
                                                        <div class="media gap-3">
                                                            <div class="avatar avatar-lg">
                                                                <img
                                                                    src="{{onErrorImage(
                                                                   $item?->provider?->logo,
                                                                    asset('storage/app/public/provider/logo').'/' .$item?->provider?->logo,
                                                                    asset('public/assets/placeholder.png') ,
                                                                    'provider/logo/')}}"
                                                                    class="rounded" alt="{{translate('image')}}">
                                                            </div>
                                                            <div class="media-body">
                                                                <h5>{{$item?->provider->company_name}}</h5>
                                                                <div
                                                                    class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                                        <span class="common-list_rating d-flex gap-1">
                                                            <span class="material-icons text-primary fs-12">star</span>
                                                            {{$item?->provider?->avg_rating??0}}
                                                        </span>
                                                                    <span>{{$item?->provider?->rating_count??0}} {{translate('Reviews')}}</span>
                                                                </div>
                                                                <div
                                                                    class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                                                    <span
                                                                        class="text-danger">{{translate('price offered')}}</span>
                                                                    <h4 class="text-primary">{{with_currency_symbol($item->offered_price??0)}}</h4>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <button class="dropdown-item" data-bs-toggle="modal"
                                                                    data-bs-target="#providerInformationModal--{{$item->provider->id}}">
                                                                <img width="24"
                                                                     src="{{asset('public/assets/provider-module')}}/img/icons/chat.png"
                                                                     alt="">
                                                            </button>

                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                            @if($post->bids->count() == 0 || ($post->bids->count() == 1 && $post->bids->contains('provider_id', auth()->user()->provider->id)))
                                                <div class="d-flex justify-content-between gap-3 mb-4">
                                                    <span>{{translate('No other provider offering for the post')}}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if(!$post->is_booked && !$post?->bids->contains('provider_id', auth()->user()->provider->id))
                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <a href="{{route('provider.booking.post.update_status', [$post->id, 'status' => 'ignore'])}}"
                                   class="btn btn-danger">{{translate('Ignore')}}</a>
                                <a class="btn btn--primary" href="#" data-bs-toggle="modal"
                                   data-bs-target="#newBookingModal">{{translate('Place Offer')}}</a>
                            </div>
                        @elseif(!$post->is_booked && $post?->bids->contains('provider_id', auth()->user()->provider->id))
                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button class="btn btn--danger" data-bs-toggle="modal"
                                        data-bs-target="#withdrawRequestModal--{{$post['id']}}">{{translate('Withdraw Offer')}}</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="newBookingModal" tabindex="-1"
         aria-labelledby="newBookingModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form action="{{route('provider.booking.post.update_status', [$post->id])}}"
                      method="GET">
                    <div class="modal-header">
                        <h5 class="modal-title"
                            id="newBookingModalLabel">{{translate('New Booking Request Form')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card border">
                            <div class="card-body">
                                <div class="d-flex gap-4 mb-4">
                                    <div class="media gap-2">
                                        <div class="avatar avatar-lg rounded">
                                            <img
                                                src="{{onErrorImage(
                                               $post?->customer?->profile_image,
                                                asset('storage/app/public/user/profile_image').'/' .$post?->customer?->profile_image,
                                                asset('public/assets/placeholder.png') ,
                                                'user/profile_image/')}}"
                                                alt="{{translate('image')}}">
                                        </div>
                                        <div class="media-body">
                                            <h5 class="text-primary">{{$post?->customer?->first_name.' '.$post?->customer?->last_name}}</h5>
                                            <div class="text-muted fs-12">
                                                @if($distance)
                                                    {{$distance . ' ' . translate('away from you')}}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="media gap-2 border-start ps-4">
                                            <img width="30"
                                                 src="{{onErrorImage(
                                                $post?->sub_category?->image,
                                                asset('storage/app/public/category').'/' .$post?->sub_category?->image,
                                                asset('public/assets/placeholder.png') ,
                                                'category/')}}"
                                                 alt="{{translate('profile image')}}">
                                            <div class="media-body">
                                                <h5>{{$post?->service?->name}}</h5>
                                                <div
                                                    class="text-muted fs-12">{{$post?->sub_category?->name}}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <img width="18"
                                         src="{{asset('public/assets/provider-module')}}/img/media/edit-info.png"
                                         alt="">
                                    <h4>{{translate('Service Requirement')}}</h4>
                                </div>

                                @if($post->service_description)
                                    <p class="fs-12">{{$post->service_description}}</p>
                                @else
                                    <span
                                        class="small">{{translate('Not Available')}}</span>
                                @endif

                            </div>
                        </div>

                        <div class="card border mt-3">
                            <div class="card-body">
                                <div class="mb-30">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" name="offered_price"
                                               placeholder="{{translate('Offer Price')}}"
                                               min="{{$post?->service?->min_bidding_price??0}}" step="any"
                                               id="offer-price" data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="{{translate('Minimum Offer price')}} {{with_currency_symbol($post?->service?->min_bidding_price??0)}}">
                                        <label for="offer-price">{{translate('Offer Price')}}</label>
                                    </div>
                                </div>
                                <div class="form-floating">
                                    <textarea class="form-control" placeholder="{{translate('Add Your Note')}}"
                                              name="provider_note" id="add-your-note"></textarea>
                                    <label for="add-your-note"
                                           class="d-flex align-items-center gap-1">
                                        {{translate('Add Your Note')}}</label>
                                    <input type="hidden" name="status" value="accept">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end border-0 pt-0">
                        <button type="submit"
                                class="btn btn--primary">{{translate('Send Your Offer')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0">

                </div>
                <div class="modal-body pb-sm-5 px-sm-5">
                    <div class="d-flex flex-column align-items-center gap-2 text-center">
                        <img src="{{asset('public/assets/provider-module')}}/img/icons/alert.png" alt="">
                        <h3>{{translate('Alert')}}!</h3>
                        <p class="fw-medium">
                            {{translate('This request is with customized instructions. Please read the customer description and instructions thoroughly and place your pricing according to this')}}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="withdrawRequestModal--{{$post['id']}}" tabindex="-1"
         aria-labelledby="withdrawRequestModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-column gap-2 align-items-center text-center">
                        <img width="75" class="mb-2"
                             src="{{asset('public/assets/provider-module')}}/img/media/withdraw.png"
                             alt="">
                        <h3>{{translate('Cancel this quotation?')}}</h3>
                        <div class="text-muted fs-12">
                            {{translate('Your quotation will be withdrawn from the customer. You will be able to send a fresh quotation anytime.')}}
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center gap-3 border-0 pt-0 pb-4">
                    <button type="button" class="btn btn--secondary"
                            data-bs-dismiss="modal"
                            aria-label="{{translate('Close')}}">{{translate('Keep Offer')}}</button>
                    <a href="{{route('provider.booking.post.withdraw', [$post->id])}}"
                       type="button"
                       class="btn btn--danger">{{translate('Yes, Cancel Quotation')}}</a>
                </div>
            </div>
        </div>
    </div>

    @foreach($post->bids as $item)
        @if($item?->provider?->id != auth()->user()->provider->id)
            <div class="modal fade" id="providerInformationModal--{{$item->provider->id}}" tabindex="-1"
                 aria-labelledby="alertModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header pb-0 border-0">
                            <h3>{{translate('Provider Information')}}</h3>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <div class="modal-body pb-sm-5 px-sm-5">
                            <div class="d-flex justify-content-between gap-3 mb-4">
                                <div class="media gap-3">
                                    <div class="avatar avatar-lg">
                                        <img
                                            src="{{onErrorImage(
                                            $item?->provider?->logo,
                                            asset('storage/app/public/provider/logo').'/' . $item?->provider?->logo,
                                            asset('public/assets/placeholder.png') ,
                                            'provider/logo/')}}"
                                            class="rounded" alt="{{translate('provider logo')}}">
                                    </div>
                                    <div class="media-body">
                                        <div class="d-flex justify-content-between">
                                            <h5>{{$item?->provider->company_name}}</h5>
                                            <div>{{$item->created_at->format('Y-m-d h:ia')}}</div>
                                        </div>
                                        <div class="fs-12 d-flex flex-wrap align-items-center gap-2 mt-1">
                                            <span class="common-list_rating d-flex gap-1">
                                                <span class="material-icons text-primary fs-12">star</span>
                                                {{$item?->provider?->avg_rating??0}}
                                            </span>
                                            <span>{{$item?->provider?->rating_count??0}} {{translate('Reviews')}}</span>
                                        </div>
                                        <div class="d-flex gap-2 flex-wrap align-items-center fs-12 mt-1">
                                            <span class="text-danger">{{translate('price offered')}}</span>
                                            <h4 class="text-primary">{{with_currency_symbol($item->offered_price??0)}}</h4>
                                        </div>

                                        <div>
                                            <span>{{translate('Description')}}:</span>
                                            <p>{{$item->provider_note}}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
