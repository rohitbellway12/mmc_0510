<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ translate('New Quotation / Estimate Received') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .mail-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            padding: 25px;
            text-align: center;
        }
        .header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .content {
            padding: 30px 25px;
            color: #333333;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .details-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .details-row:last-child {
            margin-bottom: 0;
            border-top: 1px dashed #ced4da;
            padding-top: 10px;
            font-weight: bold;
            font-size: 16px;
            color: #007bff;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0 15px;
        }
        .btn-accept {
            display: inline-block;
            background-color: #28a745;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 28px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3);
        }
        .footer {
            background-color: #f1f3f5;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="mail-container">
        <div class="header">
            <h2>{{ translate('New Quotation Received') }}</h2>
        </div>
        <div class="content">
            <div class="greeting">
                {{ translate('Hello') }} {{ $estimate->customer_name }},
            </div>
            <p>
                {{ translate('You have received a new quotation/estimate from') }} 
                <strong>{{ $estimate->provider?->company_name ?? translate('Service Provider') }}</strong>.
            </p>

            <div class="details-box">
                <div class="details-row">
                    <span>{{ translate('Quotation ID') }}:</span>
                    <span>#{{ $estimate->readable_id ?? $estimate->id }}</span>
                </div>
                <div class="details-row">
                    <span>{{ translate('Service / Vehicle') }}:</span>
                    <span>{{ $estimate->car_model ?? $estimate->service?->name ?? translate('Custom Service') }}</span>
                </div>
                @if($estimate->service_schedule)
                <div class="details-row">
                    <span>{{ translate('Service Schedule') }}:</span>
                    <span>{{ $estimate->service_schedule }}</span>
                </div>
                @endif
                @if($estimate->notes)
                <div class="details-row">
                    <span>{{ translate('Notes') }}:</span>
                    <span>{{ $estimate->notes }}</span>
                </div>
                @endif
                <div class="details-row">
                    <span>{{ translate('Total Amount') }}:</span>
                    <span>{{ with_currency_symbol($estimate->total_amount) }}</span>
                </div>
            </div>

            <p style="text-align: center; font-size: 14px; color: #495057;">
                {{ translate('Click the button below to view details and confirm/accept your quotation.') }}
            </p>

            <div class="btn-container">
                <a href="{{ $estimate->web_url }}" class="btn-accept" target="_blank">
                    {{ translate('View & Accept Quotation') }}
                </a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ business_config('business_name', 'business_information')->live_values ?? config('app.name') }}. {{ translate('All rights reserved.') }}
        </div>
    </div>
</body>
</html>
