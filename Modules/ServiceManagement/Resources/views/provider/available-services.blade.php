@extends('providermanagement::layouts.master')

@section('title', translate('Available Services'))

@section('content')
    @push('css_or_js')
        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
                --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                --hover-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }

            .service-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
                gap: 1.5rem;
                padding: 1rem 0;
            }

            .premium-card {
                background: #ffffff;
                border: 1px solid rgba(0, 0, 0, 0.05);
                border-radius: 1.25rem;
                padding: 1.75rem;
                transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                position: relative;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                min-height: 240px;
                box-shadow: var(--card-shadow);
            }

            .premium-card:hover {
                transform: translateY(-8px);
                box-shadow: var(--hover-shadow);
                border-color: rgba(79, 70, 229, 0.2);
            }

            .card-icon-overlay {
                position: absolute;
                top: -20px;
                right: -20px;
                font-size: 8rem;
                color: rgba(79, 70, 229, 0.03);
                z-index: 0;
                pointer-events: none;
            }

            .service-title-wrap {
                z-index: 1;
                margin-bottom: 1.5rem;
            }

            .service-title-link {
                font-size: 1.35rem;
                font-weight: 700;
                color: #1e293b;
                text-decoration: none;
                line-height: 1.3;
                display: block;
                transition: color 0.3s;
            }

            .premium-card:hover .service-title-link {
                color: #4f46e5;
            }

            .service-category-badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                background: rgba(79, 70, 229, 0.08);
                color: #4f46e5;
                font-size: 0.75rem;
                font-weight: 600;
                border-radius: 2rem;
                margin-bottom: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .action-wrapper {
                z-index: 1;
                display: flex;
                flex-wrap: wrap;
                gap: 0.75rem;
                margin-top: auto;
            }

            .btn-premium {
                padding: 0.6rem 1.25rem;
                border-radius: 0.75rem;
                font-weight: 600;
                font-size: 0.9rem;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                border: none;
            }

            .btn-subscribe {
                background: var(--primary-gradient);
                color: white;
            }

            .btn-subscribe:hover {
                background: linear-gradient(135deg, #4338ca 0%, #2563eb 100%);
                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            }

            .btn-unsubscribe {
                background: #fff1f2;
                color: #e11d48;
                border: 1px solid #fecdd3;
            }

            .btn-unsubscribe:hover {
                background: #ffe4e6;
                color: #be123c;
            }

            .btn-manage {
                background: #f8fafc;
                color: #64748b;
                border: 1px solid #e2e8f0;
            }

            .btn-manage:hover {
                background: #f1f5f9;
                color: #1e293b;
                border-color: #cbd5e1;
            }

            .status-indicator {
                position: absolute;
                top: 1.25rem;
                right: 1.25rem;
                width: 10px;
                height: 10px;
                border-radius: 50%;
            }

            .status-subscribed {
                background: #10b981;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
            }

            .status-available {
                background: #94a3b8;
            }

            .config-details-wrap {
                background: #f8fafc;
                border-radius: 0.75rem;
                padding: 0.75rem;
                margin-bottom: 1.25rem;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                border: 1px solid #f1f5f9;
            }

            .config-item {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.85rem;
                color: #475569;
                font-weight: 500;
            }

            .config-item .material-icons {
                font-size: 1rem;
                color: #64748b;
            }

            .type-badge {
                padding: 0.15rem 0.5rem;
                background: #e2e8f0;
                color: #475569;
                border-radius: 0.5rem;
                font-size: 0.75rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .empty-state {
                text-align: center;
                padding: 5rem 2rem;
                background: #f8fafc;
                border-radius: 1.5rem;
                border: 2px dashed #e2e8f0;
            }

            .image-upload-wrapper {
                display: block;
                border: 2px dashed #e2e8f0;
                border-radius: 1rem;
                padding: 1.5rem;
                text-align: center;
                cursor: pointer;
                transition: all 0.3s ease;
                background: #f8fafc;
            }

            .image-upload-wrapper:hover {
                border-color: var(--bs-primary);
                background: #f0f7ff;
            }

            .image-preview-container {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
                margin-top: 1.5rem;
            }

            .preview-item {
                position: relative;
                width: 90px;
                height: 90px;
                border-radius: 0.75rem;
                overflow: hidden;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
                border: 2px solid #f1f5f9;
                transition: all 0.3s ease;
            }

            .preview-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 20px rgba(0, 0, 0, 0.12);
                border-color: var(--bs-primary);
            }

            .preview-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .remove-img {
                position: absolute;
                top: 5px;
                right: 5px;
                background: rgba(239, 68, 68, 0.9);
                color: white;
                border-radius: 50%;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                cursor: pointer;
                border: 2px solid white;
                z-index: 10;
                transition: all 0.2s ease;
            }

            .remove-img:hover {
                background: #ef4444;
                transform: scale(1.1);
            }

            .emergency-card {
                background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 12px;
                padding: 12px 20px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 20px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            }

            .emergency-badge {
                background: rgba(239, 68, 68, 0.15);
                color: #f87171;
                padding: 3px 10px;
                border-radius: 100px;
                font-weight: 700;
                font-size: 9px;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                text-transform: uppercase;
                border: 1px solid rgba(239, 68, 68, 0.1);
            }

            .pulse-red {
                width: 6px;
                height: 6px;
                background: #ef4444;
                border-radius: 50%;
                animation: pulse-red 2s infinite;
            }

            @keyframes pulse-red {
                0% {
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
                }

                70% {
                    box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
                }
            }

            .emergency-settings-grid {
                display: flex;
                align-items: center;
                gap: 12px;
                background: rgba(255, 255, 255, 0.03);
                padding: 6px 12px;
                border-radius: 8px;
                border: 1px solid rgba(255, 255, 255, 0.05);
            }

            .emergency-setting-item {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .emergency-label {
                font-size: 10px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                margin-bottom: 0;
                white-space: nowrap;
            }

            .emergency-input {
                background: rgba(15, 23, 42, 0.8) !important;
                border: 1px solid rgba(255, 255, 255, 0.1) !important;
                color: #f1f5f9 !important;
                border-radius: 4px;
                padding: 2px 6px;
                font-size: 11px;
                max-width: 50px;
                height: 22px;
            }

            /* Mini switch for inner settings (24/7, Weekends) */
            .mini-switch {
                position: relative;
                display: inline-block;
                width: 30px;
                height: 16px;
            }

            .mini-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .mini-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ef4444;
                transition: .3s;
                border-radius: 16px;
            }

            .mini-slider:before {
                position: absolute;
                content: "";
                height: 10px;
                width: 10px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .3s;
                border-radius: 50%;
            }

            input:checked+.mini-slider {
                background-color: #22c55e;
            }

            input:checked+.mini-slider:before {
                transform: translateX(14px);
            }

            .emergency-toggle-wrap {
                background: rgba(15, 23, 42, 0.4);
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                padding: 6px 12px;
                display: flex;
                align-items: center;
                gap: 8px;
                margin-left: auto;
            }

            .emergency-switch {
                position: relative;
                display: inline-block;
                width: 34px;
                height: 18px;
            }

            .emergency-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .emergency-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ef4444;
                transition: .4s;
                border-radius: 18px;
            }

            .emergency-slider:before {
                position: absolute;
                content: "";
                height: 12px;
                width: 12px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked+.emergency-slider {
                background-color: #22c55e;
            }

            input:checked+.emergency-slider:before {
                transform: translateX(16px);
            }
            }
        </style>
    @endpush

    <div class="main-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-wrap mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <h2 class="page-title">{{ translate('Available_Services') }} </h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('provider.service_pricing.index') }}"
                                class="btn btn--primary d-flex align-items-center gap-2">
                                <span class="material-icons fs-18">monetization_on</span> {{ translate('Manage_Pricing') }}
                            </a>
                            <a href="{{ route('provider.time_slots.index') }}"
                                class="btn btn-outline-primary d-flex align-items-center gap-2">
                                <span class="material-icons fs-18">schedule</span> {{ translate('Time_Slots') }}
                            </a>
                        </div>
                    </div>

                    {{-- Ultra-Compact Emergency Mode Bar --}}
                    <div class="emergency-card">
                        <div class="d-flex align-items-center gap-3">
                            <div>
                                <div class="emergency-badge">
                                    <div class="pulse-red"></div>
                                    {{ translate('Emergency') }}
                                </div>
                                <div class="text-white fw-bold" style="font-size: 0.95rem; line-height: 1;">
                                    {{ translate('Live Assistance') }}</div>
                            </div>
                        </div>

                        <div class="emergency-settings-grid flex-grow-1">
                            <div class="emergency-setting-item">
                                <label class="emergency-label">{{ translate('24/7') }}</label>
                                <label class="mini-switch">
                                    <input type="checkbox" id="after_hours_available"
                                        {{ $provider->after_hours_available ? 'checked' : '' }}>
                                    <span class="mini-slider"></span>
                                </label>
                            </div>
                            <div class="emergency-setting-item ps-3" style="border-left: 1px solid rgba(255,255,255,0.1);">
                                <label class="emergency-label">{{ translate('Weekends') }}</label>
                                <label class="mini-switch">
                                    <input type="checkbox" id="weekend_emergency_available"
                                        {{ $provider->weekend_emergency_available ? 'checked' : '' }}>
                                    <span class="mini-slider"></span>
                                </label>
                            </div>
                            <div class="emergency-setting-item ps-3" style="border-left: 1px solid rgba(255,255,255,0.1);">
                                <label class="emergency-label">{{ translate('ETA') }}</label>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="number" class="emergency-input emergency-settings-input"
                                        id="emergency_response_time" placeholder="30" min="1" max="999"
                                        value="{{ $provider->emergency_response_time }}"
                                        style="max-width: 42px; text-align: center;">
                                    <select class="emergency-input" id="emergency_response_unit"
                                        style="max-width: 46px; padding: 2px 4px; height: 22px;">
                                        <option value="min">min</option>
                                        <option value="hr">hr</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div
                            class="emergency-toggle-wrap {{ $provider->is_emergency_active ? 'emergency-toggle-active' : '' }}">
                            <span class="fw-bold {{ $provider->is_emergency_active ? 'text-success' : 'text-danger' }}"
                                id="emergency-status-text"
                                style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                                {{ $provider->is_emergency_active ? translate('ON') : translate('OFF') }}
                            </span>
                            <label class="emergency-switch">
                                <input type="checkbox" id="is_emergency_active"
                                    {{ $provider->is_emergency_active ? 'checked' : '' }}>
                                <span class="emergency-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="card border-0 shadow-none bg-transparent">
                        <div class="card-body p-0">
                            @if (count($categories) > 0)
                                <div class="mb-4">
                                    <ul class="nav nav-pills gap-2 p-1 bg-white rounded-pill shadow-sm"
                                        style="width: fit-content;">
                                        <li class="nav-item">
                                            <a class="nav-link rounded-pill {{ $activeCategory == 'all' ? 'active' : '' }}"
                                                href="{{ url()->current() }}?active_category=all">{{ translate('All_Services') }}</a>
                                        </li>
                                        @foreach ($categories as $category)
                                            <li class="nav-item">
                                                <a class="nav-link rounded-pill {{ $activeCategory == $category->id ? 'active' : '' }}"
                                                    href="{{ url()->current() }}?active_category={{ $category->id }}">
                                                    {{ $category->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                @php
                                    $activeCategoryModel = $categories->firstWhere('id', $activeCategory);
                                    $isActiveQuotationCategory = $activeCategoryModel ? (bool)$activeCategoryModel->is_quotation_based : false;
                                @endphp
                                @if ($isActiveQuotationCategory)
                                    <div class="alert alert-warning border border-warning-subtle d-flex align-items-center gap-3 rounded-4 p-3 mb-4 shadow-xs" style="background-color: #fffbeb; border-color: #fde68a !important;">
                                        <span class="material-icons text-warning" style="font-size: 32px;">request_quote</span>
                                        <div>
                                            <strong class="text-dark d-block fs-14 mb-1">{{ translate('Quotation-Based Category') }}: {{ $activeCategoryModel->name }}</strong>
                                            <span class="text-muted small">
                                                {{ translate('Services in this category do not have fixed upfront prices. Customers submit vehicle details and damage or modification photos first. When new requests arrive, you will receive notifications and can submit your price quotation under') }}
                                                <a href="{{ route('provider.booking.post.list', ['type' => 'all', 'service_type' => 'all']) }}" class="fw-bold text-primary text-decoration-underline ms-1">
                                                    {{ translate('Booking Management > Customized Requests') }}
                                                </a>.
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                @if (count($services) > 0)
                                    <div class="service-grid">
                                        @foreach ($services as $service)
                                            @php
                                                $isSubscribed = in_array($service->id, $subscribedServiceIds);
                                                $serviceName = strtolower($service->name);
                                                $isQuotation = (bool)($service->is_quotation_based ?? false);
                                            @endphp
                                            <div class="premium-card">
                                                <div
                                                    class="status-indicator {{ $isSubscribed ? 'status-subscribed' : 'status-available' }}">
                                                </div>

                                                @php
                                                    $icon = 'settings';
                                                    if (str_contains($serviceName, 'car')) {
                                                        $icon = 'directions_car';
                                                    } elseif (str_contains($serviceName, 'tyre')) {
                                                        $icon = 'adjust';
                                                    } elseif (str_contains($serviceName, 'wash')) {
                                                        $icon = 'local_car_wash';
                                                    } elseif (str_contains($serviceName, 'repair')) {
                                                        $icon = 'build';
                                                    } elseif (str_contains($serviceName, 'recovery')) {
                                                        $icon = 'home_repair_service';
                                                    }
                                                @endphp
                                                <span class="material-icons card-icon-overlay">{{ $icon }}</span>

                                                <div class="service-title-wrap">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                        <span class="service-category-badge mb-0">{{ $service->category->name ?? 'Service' }}</span>
                                                        @if ($isQuotation)
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-11 rounded-pill d-inline-flex align-items-center gap-1" title="{{ translate('Pricing is based on customer quotation & vehicle damage assessment') }}">
                                                                <span class="material-icons" style="font-size: 13px;">request_quote</span> {{ translate('Quotation_Only') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <a href="{{ route('provider.service.detail', [$service->id]) }}"
                                                        class="service-title-link">
                                                        {{ $service->name }}
                                                    </a>
                                                </div>

                                                @if ($isSubscribed && ($subscribedServiceEstimatedTime[$service->id] || !empty($subscribedServiceTypes[$service->id])))
                                                    <div class="config-details-wrap">
                                                        @if ($subscribedServiceEstimatedTime[$service->id])
                                                            <div class="config-item">
                                                                <span class="material-icons">schedule</span>
                                                                <span>{{ $subscribedServiceEstimatedTime[$service->id] }}</span>
                                                            </div>
                                                        @endif
                                                        @if (isset($subscribedServiceTypes[$service->id]) && is_array($subscribedServiceTypes[$service->id]))
                                                            <div class="config-item">
                                                                <span class="material-icons">local_shipping</span>
                                                                <div class="d-flex flex-wrap gap-1">
                                                                    @foreach ($subscribedServiceTypes[$service->id] as $type)
                                                                        <span
                                                                            class="type-badge">{{ translate($type) }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="action-wrapper">
                                                    <form action="javascript:void(0)" method="post" class="hide-div"
                                                        id="form-{{ $service->id }}">
                                                        @csrf
                                                        @method('put')
                                                        <input type="hidden" name="service_id"
                                                            value="{{ $service->id }}">
                                                    </form>

                                                    @if ($isSubscribed)
                                                        <button type="button"
                                                            class="btn-premium btn-unsubscribe update-service-subscription w-100"
                                                            id="button-{{ $service->id }}" data-id="{{ $service->id }}"
                                                            data-category-name="{{ $service->category->name ?? '' }}">
                                                            <span class="material-icons fs-16">remove_circle_outline</span>
                                                            {{ translate('unsubscribe') }}
                                                        </button>

                                                        @php
                                                            $currentEstTime =
                                                                $subscribedServiceEstimatedTime[$service->id] ?? '';
                                                            $currentTypes = $subscribedServiceTypes[$service->id] ?? [];
                                                        @endphp
                                                        @if (!str_contains($serviceName, 'car hire') && !str_contains($serviceName, 'chauffeur'))
                                                            <button type="button"
                                                                class="btn-premium btn-manage w-100 manage-service-details"
                                                                data-id="{{ $service->id }}"
                                                                data-is-quotation="{{ $isQuotation ? '1' : '0' }}"
                                                                data-service-name="{{ $service->name }}"
                                                                data-estimated-time="{{ $currentEstTime }}"
                                                                data-service-types='{{ json_encode($currentTypes) }}'
                                                                data-variations='{{ json_encode($service->variations) }}'
                                                                data-custom-prices='{{ json_encode($customPrices) }}'
                                                                data-service-price="{{ $subscribedServicePrices[$service->id] ?? 0 }}"
                                                                data-images='{{ json_encode($subscribedServiceImages[$service->id] ?? []) }}'>
                                                                <span class="material-icons fs-16">edit_note</span>
                                                                {{ translate('Service_Details') }}
                                                            </button>
                                                        @endif

                                                        @if (str_contains($serviceName, 'car hire') || str_contains($serviceName, 'chauffeur'))
                                                            <a href="{{ route('provider.car.index') }}"
                                                                class="btn-premium btn-manage flex-grow-1">
                                                                <span class="material-icons fs-16">directions_car</span>
                                                                {{ translate('Cars') }}
                                                            </a>
                                                        @endif

                                                        @if (isset($service->category) && str_contains(strtolower($service->category->name), 'tyre'))
                                                            @if (str_contains($serviceName, 'emergency'))
                                                                @php $currentCaps = $subscribedServiceCapabilities[$service->id] ?? []; @endphp
                                                                <button type="button"
                                                                    class="btn-premium btn-manage flex-grow-1 manage-capabilities"
                                                                    data-id="{{ $service->id }}"
                                                                    data-category-name="{{ $service->category->name ?? '' }}"
                                                                    data-capabilities='{{ json_encode($currentCaps) }}'>
                                                                    <span class="material-icons fs-16">bolt</span>
                                                                    {{ translate('Cap.') }}
                                                                </button>
                                                            @endif

                                                            @if (str_contains($serviceName, 'replacement'))
                                                                <a href="{{ route('provider.tyre.index') }}"
                                                                    class="btn-premium btn-manage flex-grow-1">
                                                                    <span class="material-icons fs-16">adjust</span>
                                                                    {{ translate('Tyres') }}
                                                                </a>
                                                            @endif
                                                        @endif
                                                    @else
                                                        <button type="button"
                                                            class="btn-premium btn-subscribe update-service-subscription w-100"
                                                            id="button-{{ $service->id }}"
                                                            data-id="{{ $service->id }}"
                                                            data-category-name="{{ $service->category->name ?? '' }}">
                                                            <span class="material-icons fs-16">add_task</span>
                                                            {{ translate('subscribe') }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <span class="material-icons mb-3 fs-48 text-muted">category</span>
                                        <h4 class="text-muted">{{ translate('No_Services_found_in_this_category') }}</h4>
                                    </div>
                                @endif
                            @else
                                <div class="empty-state">
                                    <span class="material-icons mb-3 fs-48 text-muted">warning</span>
                                    <h4 class="text-muted">{{ translate('No_available_services') }}</h4>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Removed sub-category modals as we now display services directly --}}

    <!-- Capability Selection Modal -->
    <div class="modal fade" id="capabilityModal" tabindex="-1" role="dialog" aria-labelledby="capabilityModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="capabilityModalLabel">{{ translate('Select_Service_Capabilities') }}</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="capabilities-form">
                        @csrf
                        @method('put')
                        <input type="hidden" name="service_id" id="modal_service_id">
                        <p class="mb-3">{{ translate('Please_select_the_services_you_provide') }}:</p>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="capabilities[]"
                                value="Mobile Tyre Service" id="cap_mobile_tyre">
                            <label class="form-check-label" for="cap_mobile_tyre">
                                {{ translate('Mobile_Tyre_Service') }}
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="capabilities[]" value="Recovery Truck"
                                id="cap_recovery_truck">
                            <label class="form-check-label" for="cap_recovery_truck">
                                {{ translate('Recovery_Truck') }}
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ translate('Skip') }}</button>
                    <button type="button" class="btn btn--primary"
                        onclick="save_capabilities()">{{ translate('Save_Capabilities') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Details Modal -->
    <div class="modal fade" id="serviceDetailsModal" tabindex="-1" role="dialog"
        aria-labelledby="serviceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
                <div class="modal-header border-0 pb-0">
                    <h4 class="modal-title fw-bold text-primary" id="serviceDetailsModalLabel">
                        {{ translate('Service_Configuration') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="service-details-form" onsubmit="event.preventDefault(); save_service_details();"
                        enctype="multipart/form-data">
                        @csrf
                        @method('put')
                        <input type="hidden" name="service_id" id="details_service_id">

                        <div class="mb-4">
                            <label class="form-label text-dark fw-bold mb-2">
                                <span class="material-icons fs-18 align-middle me-1">schedule</span>
                                {{ translate('Estimated_Completion_Time') }}
                            </label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg" id="details_hours"
                                            min="0" max="24" placeholder="0"
                                            style="border-radius: 1rem 0 0 1rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text border-start-0"
                                            style="border-radius: 0 1rem 1rem 0; background: #f1f5f9; font-weight: 600;">{{ translate('hrs') }}</span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="input-group">
                                        <input type="number" class="form-control form-control-lg" id="details_minutes"
                                            min="0" max="59" placeholder="0"
                                            style="border-radius: 1rem 0 0 1rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <span class="input-group-text border-start-0"
                                            style="border-radius: 0 1rem 1rem 0; background: #f1f5f9; font-weight: 600;">{{ translate('mins') }}</span>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="estimated_time" id="details_estimated_time">
                            <small
                                class="text-muted mt-2 d-block">{{ translate('Select_how_long_this_service_usually_takes') }}</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label d-block text-dark fw-bold mb-3">
                                <span class="material-icons fs-18 align-middle me-1">category</span>
                                {{ translate('Service_Delivery_Methods') }}
                            </label>
                            <div class="d-flex flex-column gap-3">
                                <div class="premium-checkbox shadow-sm border p-3 rounded-3 d-flex align-items-center gap-3 transition-all"
                                    style="background: #fff;">
                                    <input class="form-check-input flex-shrink-0" type="checkbox" name="service_types[]"
                                        value="mobile" id="type_mobile" style="width: 22px; height: 22px;">
                                    <label class="form-check-label flex-grow-1 cursor-pointer mb-0" for="type_mobile">
                                        <div class="fw-bold text-dark">{{ translate('Mobile_Service') }}</div>
                                        <div class="text-muted small">
                                            {{ translate('You_visit_the_customer_at_their_place') }}</div>
                                    </label>
                                </div>
                                <div class="premium-checkbox shadow-sm border p-3 rounded-3 d-flex align-items-center gap-3 transition-all"
                                    style="background: #fff;">
                                    <input class="form-check-input flex-shrink-0" type="checkbox" name="service_types[]"
                                        value="workshop" id="type_workshop" style="width: 22px; height: 22px;">
                                    <label class="form-check-label flex-grow-1 cursor-pointer mb-0" for="type_workshop">
                                        <div class="fw-bold text-dark">{{ translate('Workshop_Service') }}</div>
                                        <div class="text-muted small">
                                            {{ translate('Customer_brings_vehicle_to_your_location') }}</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-0" id="pricing-section-wrap">
                            <label class="label-premium d-flex align-items-center gap-2 mb-3" id="pricing-section-title">
                                <span class="material-icons fs-18">payments</span>
                                {{ translate('Service_Pricing') }}
                            </label>
                            <div id="pricing-variations-container" class="bg-light p-3 rounded-4 border">
                                <!-- Variations will be loaded here -->
                                <div class="text-center text-muted py-2">
                                    <small>{{ translate('Select_a_service_to_see_pricing_options') }}</small>
                                </div>
                            </div>
                            <p class="text-muted small mt-2 mb-0" id="pricing-section-note">
                                <span class="material-icons fs-12 align-middle">info</span>
                                {{ translate('Prices_are_per_zone_and_will_be_used_during_booking') }}
                            </p>
                        </div>

                        <div class="mt-4">
                            <label class="form-label text-dark fw-bold mb-2">
                                <span class="material-icons fs-18 align-middle me-1">image</span>
                                {{ translate('Service_Images') }}
                            </label>
                            <label class="image-upload-wrapper" for="service_images">
                                <input type="file" name="images[]" id="service_images" multiple accept="image/*"
                                    class="d-none" onchange="previewImages(this)">
                                <span class="material-icons fs-32 text-muted">cloud_upload</span>
                                <p class="mb-0 text-muted small mt-1">{{ translate('Click_to_upload_images') }}</p>
                                <p class="text-muted" style="font-size: 10px;">
                                    {{ translate('Max_file_size_10MB_JPG_PNG') }}
                                </p>
                            </label>
                            <div id="image-preview-container" class="image-preview-container">
                                <!-- Previews will be loaded here -->
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 pb-4 px-4 pt-0">
                    <button type="button" class="btn btn-premium w-100 py-3" onclick="save_service_details()"
                        style="background: var(--bs-primary); color: #fff; border-radius: 1rem;">
                        <span class="material-icons fs-18">save</span>
                        {{ translate('Save_Configuration') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        "use strict";

        $(".provider-service-detail").on('click', function() {
            let route = $(this).data('route')
            location.href = route;
        })

        $(".update-service-subscription").on('click', function() {
            let id = $(this).data('id')
            update_subscription(id)
        })

        $(".manage-capabilities").on('click', function() {
            let id = $(this).data('id');
            let capabilities = $(this).data('capabilities');

            $('#modal_service_id').val(id);

            // clear checkboxes
            $('input[name="capabilities[]"]').prop('checked', false);

            // pre-check if capabilities exist
            if (capabilities && Array.isArray(capabilities)) {
                capabilities.forEach(function(cap) {
                    $('input[name="capabilities[]"][value="' + cap + '"]').prop('checked', true);
                });
            }

            var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('capabilityModal'));
            myModal.show();
        });

        $(".manage-service-details").on('click', function() {
            let id = $(this).data('id');
            let estTime = $(this).data('estimated-time') + ''; // ensure string
            let types = $(this).data('service-types');
            let images = $(this).data('images');

            $('#details_service_id').val(id);
            $('#service_images').val(''); // Clear file input
            $('#image-preview-container').empty(); // Clear previews

            if (images && Array.isArray(images)) {
                images.forEach(function(img) {
                    let html = `
                        <div class="preview-item" data-image="${img}">
                            <img src="{{ asset('storage/app/public/subscribed_service') }}/${img}" alt="Preview" onerror="this.src='{{ asset('public/assets/admin-module/img/placeholder.png') }}'">
                            <span class="remove-img" onclick="markImageForRemoval(this, '${img}')">
                                <span class="material-icons" style="font-size: 14px;">close</span>
                            </span>
                        </div>
                    `;
                    $('#image-preview-container').append(html);
                });
            }

            // Parse "X hrs Y mins" or "X hours Y mins"
            let hours = 0;
            let minutes = 0;

            if (estTime) {
                let hMatch = estTime.match(/(\d+)\s*(h|hr|hour)/i);
                let mMatch = estTime.match(/(\d+)\s*(m|min)/i);
                if (hMatch) hours = hMatch[1];
                if (mMatch) minutes = mMatch[1];
            }

            $('#details_hours').val(hours > 0 ? hours : '');
            $('#details_minutes').val(minutes > 0 ? minutes : '');

            // clear checkboxes
            $('input[name="service_types[]"]').prop('checked', false);

            // pre-check if types exist
            if (types && Array.isArray(types)) {
                types.forEach(function(type) {
                    $('input[name="service_types[]"][value="' + type + '"]').prop('checked', true);
                });
            }

            // pricing variations
            let isQuotation = $(this).data('is-quotation') == '1';
            let variations = $(this).data('variations');
            let customPrices = $(this).data('custom-prices');
            let servicePriceOverride = $(this).data('service-price');
            let container = $('#pricing-variations-container');
            container.empty();

            if (isQuotation) {
                $('#pricing-section-title').html('<span class="material-icons fs-18 text-warning">request_quote</span> {{ translate("Quotation_Pricing_Model") }}');
                $('#pricing-section-note').html('<span class="material-icons fs-12 align-middle text-warning">info</span> {{ translate("No fixed price is required for this service. Pricing is determined per quotation.") }}');
                
                let html = `
                    <div class="p-3 bg-white rounded-3 border border-warning-subtle shadow-xs">
                        <div class="d-flex align-items-start gap-2">
                            <span class="material-icons text-warning fs-22 mt-1">lightbulb</span>
                            <div>
                                <div class="fw-bold text-dark mb-1 fs-13">{{ translate("Dynamic Quotation-Based Service") }}</div>
                                <p class="text-muted small mb-0" style="line-height: 1.5;">
                                    {{ translate("This service does not use fixed pricing because each job depends on vehicle model, condition, and damage assessment. When a customer sends vehicle photos and requirement details, you will receive the request to submit your custom price quote in") }}
                                    <a href="{{ route('provider.booking.post.list', ['type' => 'all', 'service_type' => 'all']) }}" target="_blank" class="fw-bold text-primary text-decoration-underline">
                                        {{ translate("Customized Requests") }}
                                    </a>.
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                container.append(html);
            } else {
                $('#pricing-section-title').html('<span class="material-icons fs-18">payments</span> {{ translate("Service_Pricing") }}');
                $('#pricing-section-note').html('<span class="material-icons fs-12 align-middle">info</span> {{ translate("Prices_are_per_zone_and_will_be_used_during_booking") }}');

                // Logic: If variations exist and it's more than just one "Default" variation
                let hasRealVariations = variations && variations.length > 0 && !(variations.length == 1 && variations[0]
                    .variant_key == 'default');

                if (hasRealVariations) {
                    variations.forEach(function(variation) {
                        let priceKey = id + '_' + variation.id;
                        let priceValue = (customPrices && customPrices[priceKey]) ? customPrices[priceKey]
                            .price : variation.price;

                        let html = `
                            <div class="variation-row mb-3 pb-3 border-bottom last-child-no-border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark small">${variation.variant}</span>
                                    <span class="badge bg-soft-primary text-primary small">Base: {{ currency_symbol() }}${variation.price}</span>
                                </div>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0">{{ currency_symbol() }}</span>
                                    <input type="number" step="0.01" class="form-control border-start-0 ps-0 variation-price-input" 
                                           name="variations[${variation.id}]" 
                                           value="${priceValue}" 
                                           placeholder="0.00"
                                           style="box-shadow: none;">
                                </div>
                            </div>
                        `;
                        container.append(html);
                    });
                } else {
                    // Show single service price input
                    let priceValue = (servicePriceOverride > 0) ? servicePriceOverride : (variations && variations
                        .length > 0 ? variations[0].price : 0);
                    let html = `
                        <div class="variation-row mb-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small">{{ translate('Service_Price') }}</span>
                                ${variations && variations.length > 0 ? `<span class="badge bg-soft-primary text-primary small">Base: {{ currency_symbol() }}${variations[0].price}</span>` : ''}
                            </div>
                            <div class="input-group shadow-sm">
                                <span class="input-group-text bg-white border-end-0">{{ currency_symbol() }}</span>
                                <input type="number" step="0.01" class="form-control border-start-0 ps-0" 
                                       name="service_price" 
                                       value="${priceValue}" 
                                       placeholder="0.00"
                                       style="box-shadow: none;">
                            </div>
                        </div>
                    `;
                    container.append(html);
                }
            }

            var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('serviceDetailsModal'));
            myModal.show();
        });

        function save_service_details() {
            // Combine hours and minutes
            let hours = $('#details_hours').val() || 0;
            let minutes = $('#details_minutes').val() || 0;
            let combinedTime = "";

            if (hours > 0) combinedTime += hours + " hrs ";
            if (minutes > 0) combinedTime += minutes + " mins";

            $('#details_estimated_time').val(combinedTime.trim());

            // Collect variations pricing
            let variationsPricing = [];
            $('.variation-price-input').each(function() {
                let variationId = $(this).attr('name').match(/\[(.*?)\]/)[1];
                let price = $(this).val();
                variationsPricing.push({
                    variation_id: variationId,
                    price: price
                });
            });

            var form = $('#service-details-form')[0];
            var formData = new FormData(form);
            // We don't need to append variations manually if we use name="variations[ID]" in HTML inputs, 
            // but let's ensure it's handled. FormData handles it automatically.

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('provider.service.update-service-details') }}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                beforeSend: function() {
                    $('.preloader').show()
                },
                success: function(response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('{{ translate('Service_details_updated_successfully') }}');
                        location.reload();
                    } else {
                        toastr.error('{{ translate('Failed_to_update_service_details') }}');
                    }
                },
                error: function(response) {
                    toastr.error('{{ translate('Something_went_wrong') }}');
                },
                complete: function() {
                    $('.preloader').hide()
                }
            });
        }

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
                    const categoryName = $('#button-' + id).data('category-name');
                    const isSubscribing = !$('#button-' + id).hasClass('btn--danger');
                    send_request(formData, id, categoryName, isSubscribing)
                }
            })
        }

        function send_request(formData, id, categoryName = '', isSubscribing = false) {
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
                        toastr.success('{{ translate('subscription_updated_successfully') }}');

                        // Toggle button UI on success
                        const subscribe_button = document.querySelector('#button-' + id);
                        if (subscribe_button.classList.contains('btn--danger')) {
                            subscribe_button.classList.remove('btn--danger');
                            subscribe_button.classList.add('btn--primary');
                            $('#button-' + id).text('{{ translate('subscribe') }}')
                        } else {
                            subscribe_button.classList.remove('btn--primary');
                            subscribe_button.classList.add('btn--danger');
                            $('#button-' + id).text('{{ translate('unsubscribe') }}')
                        }

                        console.log('Category Name:', categoryName);
                        console.log('Is Subscribing:', isSubscribing);

                        if (isSubscribing && categoryName.toLowerCase().includes('tyre')) {
                            $('#modal_service_id').val(id);
                            // Clear existing checks for new sub
                            $('input[name="capabilities[]"]').prop('checked', false);
                            var myModal = bootstrap.Modal.getOrCreateInstance(document.getElementById(
                                'capabilityModal'));
                            myModal.show();
                        } else {
                            location.reload();
                        }
                    } else if (response.response_code === 'default_204') {
                        toastr.warning('{{ translate('this_category_is_not_available_in_your_zone') }}');
                        location.reload();
                    } else if (response.response_code === 'default_403') {
                        toastr.error(
                            '{{ translate('your_subscription_package_category_limit_has_ended') }}');
                        // Don't reload - let user see the error
                    } else if (response.response_code === 'default_404') {
                        toastr.error('{{ translate('service_not_found') }}');
                        location.reload();
                    } else {
                        toastr.error('{{ translate('something_went_wrong') }}');
                        location.reload();
                    }
                },
                error: function(response) {
                    toastr.error('{{ translate('something_went_wrong') }}');
                    location.reload();
                },
                complete: function() {
                    $('.preloader').hide()
                }
            });
        }

        function save_capabilities() {
            var form = $('#capabilities-form')[0];
            var formData = new FormData(form);
            var id = $('#modal_service_id').val();

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{ route('provider.service.update-capabilities') }}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'post',
                beforeSend: function() {
                    $('.preloader').show()
                },
                success: function(response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('{{ translate('Capabilities_updated_successfully') }}');
                        location.reload();
                    } else {
                        toastr.error('{{ translate('Failed_to_update_service_details') }}');
                    }
                },
                error: function(response) {
                    toastr.error('{{ translate('Something_went_wrong') }}');
                },
                complete: function() {
                    $('.preloader').hide()
                }
            });
        }

        function markImageForRemoval(btn, imgName) {
            $(btn).parent().remove();
            $('#service-details-form').append(`<input type="hidden" name="remove_images[]" value="${imgName}">`);
        }

        function previewImages(input) {
            let container = $('#image-preview-container');
            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.match('image.*')) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            let html = `
                                <div class="preview-item new-preview">
                                    <img src="${e.target.result}" alt="Preview">
                                    <span class="remove-img" onclick="removeLocalPreview(this)">
                                        <span class="material-icons" style="font-size: 16px;">close</span>
                                    </span>
                                </div>
                            `;
                            container.append(html);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        function removeLocalPreview(btn) {
            $(btn).parent().remove();
        }

        // Emergency Settings Logic
        $('#is_emergency_active, #after_hours_available, #weekend_emergency_available').on('change', function() {
            updateEmergencySettings();
        });

        $('.emergency-settings-input').on('blur', function() {
            updateEmergencySettings();
        });

        function updateEmergencySettings() {
            let isEmergency = $('#is_emergency_active').is(':checked') ? 1 : 0;
            let afterHours = $('#after_hours_available').is(':checked') ? 1 : 0;
            let weekends = $('#weekend_emergency_available').is(':checked') ? 1 : 0;
            let responseTime = $('#emergency_response_time').val();

            // Update UI
            if (isEmergency) {
                $('.emergency-toggle-wrap').addClass('emergency-toggle-active');
                $('#emergency-status-text').text('{{ translate('EMERGENCY_MODE_ON') }}').addClass('text-danger')
                    .removeClass('text-white');
            } else {
                $('.emergency-toggle-wrap').removeClass('emergency-toggle-active');
                $('#emergency-status-text').text('{{ translate('EMERGENCY_MODE_OFF') }}').removeClass('text-danger')
                    .addClass('text-white');
            }

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $.ajax({
                url: "{{ route('provider.service.update-emergency-settings') }}",
                method: 'POST',
                data: {
                    is_emergency_active: isEmergency,
                    after_hours_available: afterHours,
                    weekend_emergency_available: weekends,
                    emergency_response_time: responseTime
                },
                success: function(response) {
                    if (response.response_code === 'default_200') {
                        toastr.success('{{ translate('Emergency_settings_updated') }}');
                    } else {
                        toastr.error('{{ translate('Failed_to_update_emergency_settings') }}');
                    }
                },
                error: function() {
                    toastr.error('{{ translate('Something_went_wrong') }}');
                }
            });
        }
    </script>
@endpush
