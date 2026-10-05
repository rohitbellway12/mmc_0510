<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ translate('Provider Login') }}</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="robots" content="nofollow, noindex ">
    @php($favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png'))
    <link rel="shortcut icon" href="{{ $favIcon }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet" />

    <link href="{{ asset('public/assets/provider-module') }}/css/material-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('public/assets/provider-module') }}/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('public/assets/provider-module') }}/css/toastr.css">

    <style>
        :root {
            --primary: #FAD293;
            --primary-dark: #D4A76A;
            --bg-dark: #0A0A0A;
            --card-bg: rgba(20, 20, 20, 0.7);
            --gold-gradient: linear-gradient(135deg, #FAD293 0%, #D4A76A 100%);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-dark);
            height: 100vh;
            margin: 0;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .login-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.8)), url('{{ asset('public/assets/admin-module/img/automotive-login-bg.png') }}');
            background-size: cover;
            background-position: center;
            z-index: -1;
            transform: scale(1.05);
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(250, 210, 147, 0.2);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo-container {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            max-width: 140px;
            filter: drop-shadow(0 0 10px rgba(250, 210, 147, 0.3));
        }

        .form-title {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }

        .form-group-premium {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-premium {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px !important;
            padding: 1.2rem 1.2rem 1.2rem 3.5rem !important;
            color: #ffffff !important;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .input-premium:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 15px rgba(250, 210, 147, 0.2) !important;
            outline: none;
        }

        .input-premium::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.4rem;
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-premium {
            background: var(--gold-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem;
            color: #000;
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            margin-top: 2rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(250, 210, 147, 0.3);
            filter: brightness(1.1);
        }

        .form-footer {
            margin-top: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.85rem;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .togglePassword {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.4);
            font-size: 1.2rem;
        }

        .software-version {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
        }

        .toast {
            background-color: var(--card-bg) !important;
            border: 1px solid var(--primary) !important;
            color: white !important;
        }
    </style>
</head>

<body>
    <div class="login-bg"></div>

    <div class="glass-card">
        @php($logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/', defaultPath: 'public/assets/admin-module/img/placeholder.png'))

        <div class="login-logo-container">
            <img src="{{ $logo }}" class="login-logo" alt="MMC">
        </div>

        <h1 class="form-title">{{ translate('Provider Sign In') }}</h1>
        <p class="form-subtitle">{{ translate('Premium Automotive Partner Terminal') }}</p>

        <form action="{{ route('provider.auth.login') }}" method="POST" id="login-form">
            @csrf

            <div class="form-group-premium">
                <span class="material-icons input-icon">email</span>
                <input type="email" name="email_or_phone" class="form-control input-premium"
                    value="{{ request()->cookie('provider_remember_email') }}"
                    placeholder="{{ translate('Email or Phone') }}" required id="email">
            </div>

            <div class="form-group-premium">
                <span class="material-icons input-icon">lock</span>
                <input type="password" name="password" class="form-control input-premium"
                    value="{{ request()->cookie('provider_remember_password') }}"
                    placeholder="{{ translate('Password') }}" required id="password">
                <span class="material-icons togglePassword" id="togglePassword">visibility_off</span>
            </div>

            <div class="d-flex justify-content-between align-items-center px-1">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" value="1"
                        {{ request()->cookie('provider_remember_checked') ? 'checked' : '' }} id="rememberMeCheckbox">
                    <label class="form-check-label"
                        for="rememberMeCheckbox">{{ translate('Keep me signed in') }}</label>
                </div>
                <a href="{{ route('provider.auth.reset-password.index') }}" class="form-check-label"
                    style="color: var(--primary)">{{ translate('Forgot Password?') }}</a>
            </div>

            @php($recaptcha = business_config('recaptcha', 'third_party'))
            @if (isset($recaptcha) && $recaptcha->is_active)
                <div class="recaptcha d-flex justify-content-center mt-4">
                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
                </div>
            @endif

            <button type="submit" class="btn btn-premium" id="signInBtn">{{ translate('Login to Terminal') }}</button>

            @if (business_config('provider_self_registration', 'provider_config')->live_values ?? 0)
                <div class="form-footer">
                    {{ translate('New Partner?') }}
                    <a href="{{ route('provider.auth.sign-up') }}">{{ translate('Register Now') }}</a>
                </div>
            @endif

            <div class="form-footer mt-2">
                {{ translate('System Administrator?') }}
                <a href="{{ route('admin.auth.login') }}">{{ translate('Switch to Admin') }}</a>
            </div>
        </form>
    </div>

    <div class="software-version">
        {{ translate('Version') }} {{ env('SOFTWARE_VERSION') }}
    </div>

    <script src="{{ asset('public/assets/provider-module') }}/js/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('public/assets/provider-module') }}/js/toastr.js"></script>
    {!! Toastr::message() !!}

    <script>
        "use strict";

        $('#togglePassword').on('click', function() {
            const passwordField = $('#password');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            $(this).text(type === 'password' ? 'visibility_off' : 'visibility');
        });

        @if (isset($recaptcha) && $recaptcha->is_active)
            $.getScript("https://www.google.com/recaptcha/api.js?render={{ $recaptcha->live_values['site_key'] }}",
                function() {
                    $('#signInBtn').click(function(e) {
                        e.preventDefault();
                        grecaptcha.ready(function() {
                            grecaptcha.execute('{{ $recaptcha->live_values['site_key'] }}', {
                                action: 'submit'
                            }).then(function(token) {
                                document.getElementById('g-recaptcha-response').value = token;
                                $('#login-form').submit();
                            });
                        });
                    });
                });
        @endif

        @if (env('APP_ENV') == 'demo')
            console.log("Demo Mode: provider@provider.com / 12345678");
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}', 'Error', {
                    CloseButton: true,
                    ProgressBar: true
                });
            @endforeach
        @endif
    </script>
</body>

</html>
