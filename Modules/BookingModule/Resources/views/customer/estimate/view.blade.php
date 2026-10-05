<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ translate('Opening MMC App...') }}</title>
    
    <script>
        (function() {
            var token = "{{ $estimate->link_token }}";
            var androidPackage = "{{ $packageName ?? 'com.mmc.customer' }}";
            var playStoreUrl = "{{ $playStoreUrl }}";
            var appStoreUrl = "{{ $appStoreUrl }}";
            var appScheme = "{{ $appScheme ?? 'mmc' }}://estimate/" + token;

            var userAgent = navigator.userAgent || navigator.vendor || window.opera;
            var isAndroid = /android/i.test(userAgent);
            var isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream;

            if (isAndroid) {
                // Android Chrome Intent: Opens app directly if installed;
                // If not installed, Chrome automatically redirects to S.browser_fallback_url (Play Store)!
                var intentUrl = "intent://estimate/" + token + "#Intent;scheme={{ $appScheme ?? 'mmc' }};package=" + androidPackage + ";S.browser_fallback_url=" + encodeURIComponent(playStoreUrl) + ";end;";
                window.location.href = intentUrl;

                // Fallback for browsers that don't auto-redirect intent
                setTimeout(function() {
                    window.location.replace(playStoreUrl);
                }, 1200);
            } else if (isIOS) {
                // iOS Scheme
                window.location.href = appScheme;

                // If app is not installed, browser stays active -> send directly to App Store
                setTimeout(function() {
                    window.location.replace(appStoreUrl);
                }, 1200);
            } else {
                // PC / Desktop or other device: Send directly to Play Store!
                window.location.replace(playStoreUrl);
            }
        })();
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0F172A;
            color: #FFFFFF;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .redirect-box {
            padding: 30px;
            max-width: 400px;
            width: 90%;
        }
        .logo-box {
            width: 76px;
            height: 76px;
            background: #0461A5;
            border-radius: 20px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(4, 97, 165, 0.4);
        }
        .logo-box svg {
            width: 42px;
            height: 42px;
            fill: white;
        }
        h2 {
            font-size: 22px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        p {
            font-size: 14px;
            color: #94A3B8;
            margin: 0 0 24px 0;
            line-height: 1.5;
        }
        .spinner {
            width: 36px;
            height: 36px;
            border: 3px solid rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            border-top-color: #38BDF8;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 24px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .btn-store {
            display: block;
            background: #10B981;
            color: white;
            text-decoration: none;
            padding: 13px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="redirect-box">
        <div class="logo-box">
            <svg viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3zM19 17H5v-4.66l.12-.34h13.77l.11.34V17z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
        </div>
        <h2>{{ translate('Opening MMC App...') }}</h2>
        <p>{{ translate('Taking you directly to your quotation. If the app is not installed, you will be redirected to the store.') }}</p>
        <div class="spinner"></div>
        <a href="{{ $playStoreUrl }}" class="btn-store" id="manual-btn">{{ translate('Open Google Play Store') }}</a>
    </div>

    <script>
        var ua = navigator.userAgent || navigator.vendor || window.opera;
        if (/iPad|iPhone|iPod/.test(ua) && !window.MSStream) {
            var btn = document.getElementById('manual-btn');
            if (btn) {
                btn.href = "{{ $appStoreUrl }}";
                btn.innerText = "{{ translate('Open App Store') }}";
                btn.style.background = "#0284C7";
            }
        }
    </script>
</body>
</html>
