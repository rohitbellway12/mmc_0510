<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $statusLabel ?? 'Booking Update' }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #1f2937;
            -webkit-text-size-adjust: 100%;
        }
        .email-container {
            max-width: 600px;
            margin: 24px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 8px 0 4px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 28px 24px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 16px;
        }
        .status-accepted {
            background-color: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .status-ongoing {
            background-color: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }
        .status-completed {
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        .status-canceled {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .status-default {
            background-color: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }
        .greeting {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .info-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 14px;
            border-bottom: 1px dashed #e5e7eb;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #6b7280;
            font-weight: 500;
        }
        .info-value {
            color: #111827;
            font-weight: 600;
            text-align: right;
        }
        .table-responsive {
            margin-bottom: 24px;
        }
        table.service-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table.service-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 600;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        table.service-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #1f2937;
        }
        .total-box {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
            text-align: right;
            margin-bottom: 24px;
        }
        .total-box .grand-total {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
        }
        .footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .footer a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>
<body>
    @php
        $badgeClass = match($status) {
            'accepted' => 'status-accepted',
            'ongoing' => 'status-ongoing',
            'completed' => 'status-completed',
            'canceled' => 'status-canceled',
            default => 'status-default',
        };

        $statusMessage = match($status) {
            'accepted' => 'Great news! Your booking has been accepted. Our service partner is preparing for your appointment.',
            'ongoing' => 'Your service partner has started the work. Your service is now actively in progress.',
            'completed' => 'Your booking is completed! Thank you for choosing our services. We hope you had a great experience.',
            'canceled' => 'Your booking has been canceled. If you have any questions or this was unexpected, please contact our support.',
            default => 'The status of your booking has been updated.',
        };

        $customerName = ($booking->customer?->first_name ?? '') . ' ' . ($booking->customer?->last_name ?? '');
        if (trim($customerName) == '') {
            $customerName = 'Valued Customer';
        }
    @endphp

    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ business_config('business_name', 'business_information')?->live_values ?? 'MMC Automotive' }}</h1>
            <p>Booking Status Notification</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div style="text-align: center;">
                <span class="status-badge {{ $badgeClass }}">
                    Status: {{ $statusLabel }}
                </span>
            </div>

            <div class="greeting">
                <p>Hello <strong>{{ $customerName }}</strong>,</p>
                <p>{{ $statusMessage }}</p>
            </div>

            <!-- Booking Quick Summary -->
            <div class="info-card">
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="color: #6b7280; font-size: 14px; padding: 4px 0;">Booking ID:</td>
                        <td style="font-size: 14px; font-weight: 700; text-align: right; color: #1e3a8a;">#{{ $booking->readable_id ?? $booking->id }}</td>
                    </tr>
                    <tr>
                        <td style="color: #6b7280; font-size: 14px; padding: 4px 0;">Service Schedule:</td>
                        <td style="font-size: 14px; font-weight: 600; text-align: right; color: #111827;">
                            {{ $booking->service_schedule ? \Carbon\Carbon::parse($booking->service_schedule)->format('d M Y, h:i A') : 'Flexible Schedule' }}
                        </td>
                    </tr>
                    @if(!empty($booking->provider))
                    <tr>
                        <td style="color: #6b7280; font-size: 14px; padding: 4px 0;">Service Provider:</td>
                        <td style="font-size: 14px; font-weight: 600; text-align: right; color: #111827;">{{ $booking->provider->company_name ?? 'Provider Assigned' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td style="color: #6b7280; font-size: 14px; padding: 4px 0;">Payment Method:</td>
                        <td style="font-size: 14px; font-weight: 600; text-align: right; color: #111827; text-transform: capitalize;">
                            {{ str_replace('_', ' ', $booking->payment_method ?? 'Not specified') }} ({{ $booking->is_paid ? 'Paid' : 'Unpaid' }})
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Service Details List -->
            @if(!empty($booking->detail) && $booking->detail->count() > 0)
            <div class="table-responsive">
                <table class="service-table">
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->detail as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->service_name ?? $item->service?->name ?? 'Service' }}</strong>
                                @if(!empty($item->variant_key))
                                    <br><small style="color: #6b7280;">Variant: {{ $item->variant_key }}</small>
                                @endif
                            </td>
                            <td style="text-align: center;">{{ $item->quantity ?? 1 }}</td>
                            <td style="text-align: right; font-weight: 600;">{{ with_currency_symbol($item->total_cost ?? 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Total Amount -->
            <div class="total-box">
                <div style="font-size: 14px; color: #6b7280; margin-bottom: 4px;">Total Amount:</div>
                <div class="grand-total">{{ with_currency_symbol($booking->total_booking_amount ?? 0) }}</div>
            </div>

            <p style="font-size: 13px; color: #6b7280; line-height: 1.5; text-align: center;">
                You can view real-time updates and track your booking details anytime inside the mobile app.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} {{ business_config('business_name', 'business_information')?->live_values ?? 'MMC Automotive' }}. All rights reserved.</p>
            <p style="margin: 0;">Have queries? Reach us at <a href="mailto:{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}">{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}</a></p>
        </div>
    </div>
</body>
</html>
