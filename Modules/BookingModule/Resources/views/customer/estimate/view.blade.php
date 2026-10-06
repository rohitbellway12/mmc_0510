<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Quotation') }} #{{ $estimate->readable_id }} - {{ config('app.name', 'MMC') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <style>
        :root {
            --primary: #0461A5;
            --primary-dark: #034b80;
            --primary-light: #e0f2fe;
            --success: #10b981;
            --success-dark: #059669;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --gray-700: #334155;
            --gray-500: #64748b;
            --gray-200: #e2e8f0;
            --gray-50: #f8fafc;
            --white: #ffffff;
            --radius-lg: 16px;
            --radius-md: 12px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
            --shadow-lg: 0 10px 25px -3px rgba(0,0,0,0.08), 0 4px 6px -4px rgba(0,0,0,0.04);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            line-height: 1.5;
            padding: 24px 16px 48px;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
        }

        /* Top Brand Bar */
        .brand-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0 4px;
        }
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 18px;
            color: var(--primary);
            text-decoration: none;
        }
        .brand-icon {
            width: 36px;
            height: 36px;
            background: var(--primary);
            color: var(--white);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-pending { background: #fef3c7; color: #b45309; }
        .badge-accepted { background: #d1fae5; color: #047857; }
        .badge-canceled { background: #fee2e2; color: #b91c1c; }

        /* Main Card */
        .card {
            background: var(--white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid rgba(226, 232, 240, 0.8);
            margin-bottom: 20px;
        }

        .card-hero {
            background: linear-gradient(135deg, #0461A5 0%, #034b80 100%);
            color: var(--white);
            padding: 32px 28px 28px;
            position: relative;
        }
        .card-hero h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 6px;
        }
        .card-hero p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
        }

        .price-tag {
            margin-top: 20px;
            display: inline-flex;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
            padding: 10px 18px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .price-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
        }
        .price-amount {
            font-size: 28px;
            font-weight: 800;
            color: #ffffff;
        }

        .card-body {
            padding: 28px;
        }

        /* Section Block */
        .section-block {
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        .section-block:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--gray-500);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .section-title .material-icons {
            font-size: 18px;
            color: var(--primary);
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 540px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 12px;
            color: var(--gray-500);
            margin-bottom: 2px;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
        }

        /* Items / Pricing Table */
        .price-summary {
            background: var(--gray-50);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-top: 8px;
        }
        .price-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            color: var(--gray-700);
        }
        .price-row.total {
            border-top: 1px solid var(--gray-200);
            margin-top: 8px;
            padding-top: 12px;
            font-weight: 800;
            font-size: 18px;
            color: var(--dark);
        }
        .price-row.total .val {
            color: var(--primary);
        }

        /* Provider notes */
        .notes-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: var(--radius-md);
            padding: 14px 16px;
            font-size: 13px;
            color: #92400e;
        }

        /* Accept Button Area */
        .action-area {
            margin-top: 28px;
        }
        .btn-accept {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: linear-gradient(135deg, var(--success) 0%, var(--success-dark) 100%);
            color: var(--white);
            border: none;
            border-radius: var(--radius-md);
            padding: 16px 24px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .btn-accept:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
        }

        .accepted-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #d1fae5;
            color: #065f46;
            padding: 16px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 14px;
        }
        .canceled-banner {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #fee2e2;
            color: #991b1b;
            padding: 16px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 14px;
        }

        /* Vehicle Photos */
        .photo-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .photo-thumb {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        /* Footer */
        .page-footer {
            text-align: center;
            font-size: 12px;
            color: var(--gray-500);
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Top Brand Header --}}
        <div class="brand-header">
            <div class="brand-logo">
                <div class="brand-icon">
                    <span class="material-icons" style="font-size: 20px;">directions_car</span>
                </div>
                <span>{{ config('app.name', 'MMC') }}</span>
            </div>
            <div>
                @if($estimate->status === 'pending')
                    <span class="badge-status badge-pending">{{ translate('Pending Acceptance') }}</span>
                @elseif($estimate->status === 'accepted')
                    <span class="badge-status badge-accepted">{{ translate('Accepted') }}</span>
                @elseif($estimate->status === 'canceled')
                    <span class="badge-status badge-canceled">{{ translate('Canceled') }}</span>
                @endif
            </div>
        </div>

        {{-- Main Quotation Card --}}
        <div class="card">
            @php
                $isCarBooking = ($estimate->module_type === 'car_hire' || $estimate->module_type === 'chauffeur');
                $itemTitle = $isCarBooking ? $estimate->car_model : ($estimate->service?->name ?? translate('Custom Service'));
            @endphp

            <div class="card-hero">
                <p>{{ translate('Quotation from') }} {{ $estimate->provider?->company_name ?? translate('Service Provider') }}</p>
                <h1>{{ $itemTitle }}</h1>
                <p style="font-size: 12px; opacity: 0.75;">{{ translate('Quotation') }} #{{ $estimate->readable_id }} • {{ $estimate->created_at->format('d M Y') }}</p>

                <div class="price-tag">
                    <span class="price-label">{{ translate('Total Quoted Price') }}</span>
                    <span class="price-amount">{{ with_currency_symbol($estimate->total_amount) }}</span>
                </div>
            </div>

            <div class="card-body">
                {{-- Service / Rental Details --}}
                <div class="section-block">
                    <div class="section-title">
                        <span class="material-icons">info</span>
                        {{ $isCarBooking ? translate('Rental & Booking Details') : translate('Service Details') }}
                    </div>
                    <div class="info-grid">
                        @if($isCarBooking)
                            <div class="info-item">
                                <span class="info-label">{{ translate('Vehicle Model') }}</span>
                                <span class="info-value">{{ $estimate->car_model }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ translate('Rental Type') }}</span>
                                <span class="info-value">{{ ucfirst($estimate->pickup_type ?? 'Rental') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ translate('Start Date & Time') }}</span>
                                <span class="info-value">
                                    {{ $estimate->start_date ? $estimate->start_date->format('d M Y') : '-' }} ({{ $estimate->pickup_time }})
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ translate('End Date & Time') }}</span>
                                <span class="info-value">
                                    {{ $estimate->end_date ? $estimate->end_date->format('d M Y') : '-' }} ({{ $estimate->drop_time }})
                                </span>
                            </div>
                            @if($estimate->pickup_type === 'chauffeur')
                                <div class="info-item">
                                    <span class="info-label">{{ translate('Pickup Location') }}</span>
                                    <span class="info-value">{{ $estimate->pickup_location ?? '-' }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">{{ translate('Destination') }}</span>
                                    <span class="info-value">{{ $estimate->drop_location ?? '-' }}</span>
                                </div>
                            @elseif($estimate->pickup_type === 'delivery')
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <span class="info-label">{{ translate('Delivery Address') }}</span>
                                    <span class="info-value">{{ $estimate->delivery_address ?? '-' }}</span>
                                </div>
                            @endif
                        @else
                            <div class="info-item">
                                <span class="info-label">{{ translate('Service') }}</span>
                                <span class="info-value">{{ $estimate->service?->name ?? translate('Custom Service') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ translate('Category') }}</span>
                                <span class="info-value">{{ $estimate->category?->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">{{ translate('Proposed Schedule') }}</span>
                                <span class="info-value">
                                    {{ $estimate->service_schedule ? $estimate->service_schedule->format('d M Y, h:i A') : translate('Flexible') }}
                                </span>
                            </div>
                            @if($estimate->car_model || $estimate->car_registration_number)
                                <div class="info-item">
                                    <span class="info-label">{{ translate('Vehicle') }}</span>
                                    <span class="info-value">
                                        {{ $estimate->car_model }} 
                                        @if($estimate->car_registration_number)
                                            ({{ $estimate->car_registration_number }})
                                        @endif
                                    </span>
                                </div>
                            @endif
                            @if($estimate->damage_description)
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <span class="info-label">{{ translate('Work / Issue Description') }}</span>
                                    <span class="info-value" style="font-weight: 400; color: var(--gray-700);">{{ $estimate->damage_description }}</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    @if($estimate->car_images_full_path && count($estimate->car_images_full_path) > 0)
                        <div style="margin-top: 14px;">
                            <span class="info-label">{{ translate('Inspection / Vehicle Photos') }}</span>
                            <div class="photo-gallery">
                                @foreach($estimate->car_images_full_path as $p)
                                    <a href="{{ $p }}" target="_blank">
                                        <img src="{{ $p }}" alt="Vehicle Photo" class="photo-thumb">
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Price Breakdown --}}
                <div class="section-block">
                    <div class="section-title">
                        <span class="material-icons">receipt</span>
                        {{ translate('Price Breakdown') }}
                    </div>
                    <div class="price-summary">
                        <div class="price-row">
                            <span>{{ translate('Subtotal') }}</span>
                            <span class="fw-semibold">{{ with_currency_symbol($estimate->price) }}</span>
                        </div>
                        @if($estimate->tax_amount > 0)
                            <div class="price-row">
                                <span>{{ translate('Tax / VAT') }}</span>
                                <span>{{ with_currency_symbol($estimate->tax_amount) }}</span>
                            </div>
                        @endif
                        @if($estimate->discount_amount > 0)
                            <div class="price-row" style="color: var(--success);">
                                <span>{{ translate('Discount') }}</span>
                                <span>-{{ with_currency_symbol($estimate->discount_amount) }}</span>
                            </div>
                        @endif
                        <div class="price-row total">
                            <span>{{ translate('Total Amount') }}</span>
                            <span class="val">{{ with_currency_symbol($estimate->total_amount) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                @if($estimate->notes)
                    <div class="section-block">
                        <div class="section-title">
                            <span class="material-icons">note</span>
                            {{ translate('Provider Notes & Instructions') }}
                        </div>
                        <div class="notes-box">
                            {{ $estimate->notes }}
                        </div>
                    </div>
                @endif

                {{-- Customer Info confirmation --}}
                <div class="section-block">
                    <div class="section-title">
                        <span class="material-icons">person</span>
                        {{ translate('Customer Details') }}
                    </div>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">{{ translate('Name') }}</span>
                            <span class="info-value">{{ $estimate->customer_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">{{ translate('Phone') }}</span>
                            <span class="info-value">{{ $estimate->customer_phone }}</span>
                        </div>
                    </div>
                </div>

                {{-- Action Area --}}
                <div class="action-area">
                    @if($estimate->status === 'pending')
                        <form action="{{ route('estimate.accept', [$estimate->link_token]) }}" method="POST" 
                              onsubmit="return confirm('{{ translate('Are you sure you want to accept this quotation and confirm booking?') }}');">
                            @csrf
                            <button type="submit" class="btn-accept">
                                <span class="material-icons">check_circle</span>
                                {{ translate('Accept Quotation & Confirm Booking') }}
                            </button>
                        </form>
                        <p style="text-align: center; font-size: 12px; color: var(--gray-500); margin-top: 10px;">
                            {{ translate('By clicking Accept, this quotation will be converted into an official booking.') }}
                        </p>
                    @elseif($estimate->status === 'accepted')
                        <div class="accepted-banner">
                            <span class="material-icons" style="font-size: 24px;">check_circle</span>
                            <div>
                                <div>{{ translate('Quotation Accepted!') }}</div>
                                <div style="font-size: 12px; font-weight: 400; opacity: 0.9;">
                                    {{ translate('This quotation has been confirmed. The provider has been notified to proceed.') }}
                                </div>
                            </div>
                        </div>
                    @elseif($estimate->status === 'canceled')
                        <div class="canceled-banner">
                            <span class="material-icons" style="font-size: 24px;">cancel</span>
                            <div>
                                <div>{{ translate('Quotation Canceled') }}</div>
                                <div style="font-size: 12px; font-weight: 400; opacity: 0.9;">
                                    {{ translate('This quotation is no longer valid.') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="page-footer">
            <p>{{ translate('Need assistance? Please contact') }} {{ $estimate->provider?->company_phone ?? translate('our support') }}.</p>
            <p style="margin-top: 4px;">&copy; {{ date('Y') }} {{ config('app.name', 'MMC') }}. {{ translate('All rights reserved.') }}</p>
        </div>
    </div>
</body>
</html>
