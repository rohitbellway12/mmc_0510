<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Verification OTP') }}</title>
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
            max-width: 560px;
            margin: 32px auto;
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
            letter-spacing: 0.3px;
        }
        .content {
            padding: 32px 28px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            background-color: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 6px 14px;
            border-radius: 9999px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px;
        }
        .desc {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.6;
            margin: 0 0 24px;
        }
        .otp-wrapper {
            background: #f8fafc;
            border: 2px dashed #93c5fd;
            border-radius: 12px;
            padding: 20px;
            margin: 0 auto 24px;
            max-width: 320px;
        }
        .otp-code {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #1e3a8a;
            font-family: "Courier New", Courier, monospace;
            display: block;
            margin-left: 10px; /* balances out letter-spacing */
        }
        .notice-card {
            background-color: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 8px;
            padding: 14px 16px;
            text-align: left;
            margin-bottom: 24px;
            font-size: 13px;
            color: #854d0e;
            line-height: 1.5;
        }
        .notice-card strong {
            color: #713f12;
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
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ business_config('business_name', 'business_information')?->live_values ?? 'MMC Automotive' }}</h1>
            <p>{{ translate('Authentication & Verification') }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <span class="badge">{{ translate('Security Verification') }}</span>
            <h2 class="title">{{ translate('Your One-Time Password (OTP)') }}</h2>
            <p class="desc">
                {{ translate('Use the following 4-digit code to verify your account and complete your sign in or registration.') }}
            </p>

            <!-- OTP Box -->
            <div class="otp-wrapper">
                <span class="otp-code">{{ $otp }}</span>
            </div>

            <!-- Notice -->
            <div class="notice-card">
                <div>⏱️ <strong>{{ translate('Expiry') }}:</strong> {{ translate('This OTP is valid for 3 minutes only.') }}</div>
                <div style="margin-top: 6px;">🔒 <strong>{{ translate('Security Note') }}:</strong> {{ translate('Please do NOT share this code with anyone. MMC representatives will never ask for your OTP.') }}</div>
            </div>

            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                {{ translate('If you did not request this OTP, please ignore this email or contact support if you suspect unauthorized activity.') }}
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 6px;">&copy; {{ date('Y') }} {{ business_config('business_name', 'business_information')?->live_values ?? 'MMC Automotive' }}. {{ translate('All rights reserved.') }}</p>
            <p style="margin: 0;">
                {{ translate('Need help?') }} <a href="mailto:{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}">{{ business_config('business_email', 'business_information')?->live_values ?? 'support@mmc.com' }}</a>
            </p>
        </div>
    </div>
</body>
</html>
