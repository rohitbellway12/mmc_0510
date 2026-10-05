<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ translate('Quotation Offer Accepted!') }}</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 20px; }
        .mail-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .header { background-color: #28a745; color: #ffffff; padding: 25px; text-align: center; }
        .header h2 { margin: 0; font-size: 22px; font-weight: 600; }
        .content { padding: 30px 25px; color: #333333; line-height: 1.6; }
        .details-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .details-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
        .details-row:last-child { margin-bottom: 0; border-top: 1px dashed #ced4da; padding-top: 10px; font-weight: bold; font-size: 16px; color: #28a745; }
        .footer { background-color: #f1f3f5; padding: 15px; text-align: center; font-size: 12px; color: #6c757d; }
    </style>
</head>
<body>
    <div class="mail-container">
        <div class="header">
            <h2>{{ translate('Quotation Accepted!') }}</h2>
        </div>
        <div class="content">
            <p>{{ translate('Hello') }} {{ $postBid->provider?->company_name }},</p>
            <p>{{ translate('Great news! A customer has accepted your quotation offer.') }}</p>
            <div class="details-box">
                <div class="details-row">
                    <span>{{ translate('Customer') }}:</span>
                    <span>{{ $postBid->post?->customer?->first_name }} {{ $postBid->post?->customer?->last_name }}</span>
                </div>
                <div class="details-row">
                    <span>{{ translate('Accepted Price') }}:</span>
                    <span>{{ with_currency_symbol($postBid->offered_price) }}</span>
                </div>
            </div>
            <p>{{ translate('Please log in to your provider dashboard to view details and proceed with the service.') }}</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. {{ translate('All rights reserved.') }}
        </div>
    </div>
</body>
</html>
