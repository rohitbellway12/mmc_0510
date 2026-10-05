<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Registration Success') }}</title>
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
            max-width: 580px;
            margin: 28px auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .content {
            padding: 32px 28px;
        }
        .welcome-badge {
            display: inline-block;
            background-color: #ecfdf5;
            color: #059669;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 6px 14px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 10px;
        }
        .subtext {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .cred-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .cred-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 12px;
        }
        .cred-row {
            padding: 8px 0;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            border-bottom: 1px dashed #e2e8f0;
        }
        .cred-row:last-child {
            border-bottom: none;
        }
        .cred-label {
            color: #64748b;
            font-weight: 500;
        }
        .cred-value {
            color: #0f172a;
            font-weight: 600;
            font-family: monospace;
            font-size: 15px;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff !important;
            font-weight: 600;
            font-size: 14px;
            padding: 12px 28px;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.3);
        }
        .tip-card {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.5;
            margin-bottom: 24px;
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
        $businessName = business_config('business_name', 'business_information')?->live_values ?? 'MMC Automotive';
        $customerName = trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? ''));
        if (empty($customerName)) {
            $customerName = 'Valued Customer';
        }
    @endphp

    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $businessName }}</h1>
            <p>{{ translate('Welcome to our Platform') }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <span class="welcome-badge">🎉 {{ translate('Registration Successful') }}</span>
            <h2 class="greeting">{{ translate('Hello') }} {{ $customerName }},</h2>
            <p class="subtext">
                {{ translate('Your account has been created successfully. You can now explore all our services and manage your bookings seamlessly.') }}
            </p>

            <!-- Credentials Box -->
            <div class="cred-card">
                <div class="cred-title">{{ translate('Your Login Credentials') }}</div>
                <table style="width: 100%; border: none;">
                    <tr>
                        <td style="color: #64748b; font-size: 14px; padding: 6px 0;">{{ translate('Email') }}:</td>
                        <td style="font-size: 14px; font-weight: 600; text-align: right; color: #0f172a;">{{ $customer->email }}</td>
                    </tr>
                    @if(!empty($password))
                    <tr>
                        <td style="color: #64748b; font-size: 14px; padding: 6px 0;">{{ translate('Temporary Password') }}:</td>
                        <td style="font-size: 14px; font-weight: 700; text-align: right; color: #1e3a8a; font-family: monospace;">{{ $password }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="tip-card">
                💡 <strong>{{ translate('Security Recommendation') }}:</strong> {{ translate('For your safety, please log in and change your temporary password immediately.') }}
            </div>

            @if(!empty($url))
            <div class="btn-wrapper">
                <a href="{{ $url }}" class="btn">{{ translate('Go to Your Account') }}</a>
            </div>
            @endif

            <p style="font-size: 13px; color: #6b7280; text-align: center; margin: 0;">
                {{ translate('Thank you for choosing') }} {{ $businessName }}!
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} {{ $businessName }}. {{ translate('All rights reserved.') }}</p>
            <p style="margin: 0;">
                {{ translate('Have questions?') }} <a href="mailto:{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}">{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}</a>
            </p>
        </div>
    </div>
</body>
</html>
