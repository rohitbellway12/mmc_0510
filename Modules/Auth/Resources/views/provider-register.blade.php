<!DOCTYPE html>
<html lang="en">
<head>
    <title>{{translate('Provider_Registration')}}</title>

    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta name="description" content=""/>
    <meta name="keywords" content=""/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php
    $favIcon = getBusinessSettingsImageFullPath(key: 'business_favicon', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png')
    ?>
    <link rel="shortcut icon" href="{{ $favIcon }}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap"
        rel="stylesheet"/>

    <link href="{{asset('public/assets/provider-module')}}/css/material-icons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.css"/>
    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/toastr.css">
    <link rel="stylesheet" href="{{asset('public/assets/admin-module/plugins/swiper/swiper-bundle.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/libs/intl-tel-input/css/intlTelInput.css')}}?v={{ time() }}"/>

    <link rel="stylesheet" href="{{asset('public/assets/provider-module')}}/css/style.css?v={{ time() }}"/>

    <style>
        :root {
            --primary: #FAD293;
            --primary-dark: #D4A76A;
            --bg-dark: #0A0A0A;
            --card-bg: rgba(18, 18, 18, 0.88);
            --card-glass: rgba(22, 22, 22, 0.75);
            --gold-gradient: linear-gradient(135deg, #FAD293 0%, #D4A76A 100%);
            --gold-glow: 0 0 15px rgba(250, 210, 147, 0.25);
            --border-gold: rgba(250, 210, 147, 0.22);
            --input-bg: rgba(255, 255, 255, 0.05);
            --input-border: rgba(255, 255, 255, 0.12);
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            background: var(--bg-dark) !important;
            color: #ffffff !important;
            min-height: 100vh;
        }

        .gold-gradient-text {
            background: var(--gold-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .text-gold {
            color: var(--primary) !important;
        }

        .register-wrap.style__two {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr minmax(auto, 780px);
            background: var(--bg-dark);
        }

        @media only screen and (max-width: 991px) {
            .register-wrap.style__two {
                grid-template-columns: 1fr;
            }
        }

        .register-left {
            position: relative;
            background-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.88)), url('{{ asset('public/assets/admin-module/img/automotive-login-bg.png') }}') !important;
            background-size: cover !important;
            background-position: center !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
        }

        .glass-card-left {
            background: rgba(18, 18, 18, 0.78);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid var(--border-gold);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            max-width: 480px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-right-wrap {
            background: rgba(12, 12, 12, 0.95) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-left: 1px solid var(--border-gold);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #ffffff !important;
        }

        .register-right {
            max-width: 650px;
            width: 100%;
            margin: 0 auto;
            padding: 2.5rem 1.75rem !important;
            color: #ffffff;
        }

        /* Steps navigation styling */
        #register-vertical-steps.wizard.vertical {
            display: grid;
            grid-template-columns: 46px 1fr;
            gap: 1.5rem;
        }

        @media only screen and (max-width: 575px) {
            #register-vertical-steps.wizard.vertical {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
        }

        #register-vertical-steps.wizard .steps {
            padding-top: 1rem;
            margin-bottom: 0;
        }

        #register-vertical-steps.wizard .steps ul {
            display: flex;
            flex-direction: column;
            gap: 0;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        #register-vertical-steps.wizard .steps ul li,
        .wizard .steps ul li {
            position: relative;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
        }

        #register-vertical-steps.wizard .steps ul li a,
        .wizard .steps ul li a {
            text-decoration: none;
            color: inherit;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            padding: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #register-vertical-steps.wizard .steps ul li:not(:first-child) {
            padding-top: 85px !important;
            padding-left: 0 !important;
        }

        @media only screen and (max-width: 575px) {
            #register-vertical-steps.wizard .steps ul {
                flex-direction: row;
                justify-content: space-between;
            }
            #register-vertical-steps.wizard .steps ul li:not(:first-child) {
                padding-top: 0 !important;
            }
        }

        #register-vertical-steps.wizard .steps ul li .number,
        #register-vertical-steps.wizard .steps ul .number,
        .wizard .steps ul .number {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 38px !important;
            min-width: 38px !important;
            max-width: 38px !important;
            height: 38px !important;
            min-height: 38px !important;
            max-height: 38px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.08) !important;
            background-color: rgba(255, 255, 255, 0.08) !important;
            color: rgba(255, 255, 255, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            font-weight: 600 !important;
            font-size: 0.95rem !important;
            transition: all 0.3s ease !important;
            padding: 0 !important;
            margin: 0 auto !important;
        }

        #register-vertical-steps.wizard .steps ul li.current .number,
        .wizard .steps ul .current .number {
            background: var(--gold-gradient) !important;
            background-color: #FAD293 !important;
            color: #000000 !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 18px rgba(250, 210, 147, 0.45) !important;
            font-weight: 700 !important;
        }

        #register-vertical-steps.wizard .steps ul li.done .number,
        .wizard .steps ul .done .number {
            background: rgba(250, 210, 147, 0.18) !important;
            background-color: rgba(250, 210, 147, 0.18) !important;
            color: var(--primary) !important;
            border: 1px solid var(--primary) !important;
        }

        #register-vertical-steps.wizard .steps ul li:not(:first-child)::after {
            background-color: rgba(250, 210, 147, 0.25) !important;
            width: 2px !important;
            left: 18px !important;
        }

        #register-vertical-steps.wizard .steps ul .step-info {
            display: none !important;
        }

        /* Action buttons bar */
        #register-vertical-steps.wizard.vertical .actions {
            background: rgba(12, 12, 12, 0.96) !important;
            border-top: 1px solid var(--border-gold);
            backdrop-filter: blur(15px);
            padding: 1.25rem 0.5rem;
            position: sticky;
            bottom: 0;
            z-index: 99;
            margin-top: 1.5rem;
        }

        #register-vertical-steps.wizard .actions ul {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        #register-vertical-steps.wizard .actions ul a {
            text-decoration: none;
            display: inline-block;
            padding: 0.8rem 2.2rem;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        #register-vertical-steps.wizard .actions ul a[href="#next"],
        #register-vertical-steps.wizard .actions ul a[href="#finish"] {
            background: var(--gold-gradient) !important;
            color: #000000 !important;
            text-transform: uppercase;
            box-shadow: 0 4px 15px rgba(250, 210, 147, 0.3);
            border: none !important;
        }

        #register-vertical-steps.wizard .actions ul a[href="#next"]:hover,
        #register-vertical-steps.wizard .actions ul a[href="#finish"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(250, 210, 147, 0.5);
            filter: brightness(1.1);
        }

        #register-vertical-steps.wizard .actions ul a[href="#previous"] {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        #register-vertical-steps.wizard .actions ul a[href="#previous"]:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        #register-vertical-steps.wizard .actions ul li.disabled {
            display: none !important;
        }

        /* Form elements & inputs */
        .form-control,
        .form-select {
            background-color: var(--input-bg) !important;
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            font-size: 0.95rem;
            height: 54px !important;
            padding: 0.8rem 1.2rem;
            transition: all 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--primary) !important;
            box-shadow: var(--gold-glow) !important;
            color: #ffffff !important;
            outline: none;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35) !important;
        }

        .form-select option {
            background-color: #161616 !important;
            color: #ffffff !important;
        }

        /* Form Floating Labels - Completely eliminate white boxes */
        .form-floating {
            position: relative;
        }

        .form-floating > label,
        .form-floating > label::after,
        .form-floating__icon > label,
        .form-floating__icon > label::after,
        #register-vertical-steps .form-floating > label,
        #register-vertical-steps .form-floating + label,
        #register-vertical-steps.wizard .form-floating > label {
            color: rgba(255, 255, 255, 0.6) !important;
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .form-floating.form-floating__icon label,
        .form-floating.form-floating__icon label::after {
            padding-left: 3.2rem !important;
            background: transparent !important;
            background-color: transparent !important;
        }

        .form-floating.form-floating__icon .form-control {
            padding-left: 3.2rem !important;
        }

        .form-floating.form-floating__icon .material-icons {
            color: var(--primary) !important;
            opacity: 0.9;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            position: absolute;
            z-index: 5;
        }

        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating__icon > input:focus + label {
            color: var(--primary) !important;
            opacity: 1 !important;
            background: #111111 !important;
            background-color: #111111 !important;
            border-radius: 4px;
            padding: 0 6px !important;
            display: inline-block !important;
            width: auto !important;
        }

        .form-floating > .form-control:focus ~ label::after,
        .form-floating > .form-control:not(:placeholder-shown) ~ label::after {
            display: none !important;
            background: transparent !important;
            background-color: transparent !important;
        }

        .togglePassword {
            position: absolute;
            right: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: rgba(255, 255, 255, 0.5) !important;
            font-size: 1.3rem;
            z-index: 5;
            transition: color 0.2s;
        }

        .togglePassword:hover {
            color: var(--primary) !important;
        }

        /* Section titles */
        h3.border-bottom,
        h5.border-bottom {
            border-color: var(--border-gold) !important;
            color: var(--primary) !important;
            font-weight: 600 !important;
            letter-spacing: 0.5px;
        }

        /* Upload box */
        .upload-file {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 2px dashed rgba(250, 210, 147, 0.35) !important;
            border-radius: 16px !important;
            padding: 0.5rem !important;
            transition: all 0.3s ease !important;
            display: inline-block !important;
        }

        .upload-file:hover {
            border-color: var(--primary) !important;
            background: rgba(250, 210, 147, 0.06) !important;
        }

        .upload-file__img img {
            background: transparent !important;
            background-color: transparent !important;
            border-radius: 12px !important;
            filter: invert(0.92) brightness(1.2) contrast(1.1) !important;
        }

        .upload-file__edit {
            background: var(--gold-gradient) !important;
            color: #000000 !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.5) !important;
        }

        /* International Tel Input */
        .iti {
            width: 100%;
        }

        .iti input {
            background: var(--input-bg) !important;
            border: 1px solid var(--input-border) !important;
            color: #ffffff !important;
            border-radius: 12px !important;
            height: 58px;
        }

        .iti input:focus {
            border-color: var(--primary) !important;
            box-shadow: var(--gold-glow) !important;
        }

        .iti__selected-dial-code {
            color: #ffffff !important;
        }

        .iti__country-list {
            background-color: #161616 !important;
            border: 1px solid var(--border-gold) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }

        .iti__country.iti__highlight {
            background-color: rgba(250, 210, 147, 0.15) !important;
        }

        .iti__country-name, .iti__dial-code {
            color: #ffffff !important;
        }

        .iti__flag {
            background-color: transparent !important;
            background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/19.2.15/img/flags.png") !important;
            box-shadow: none !important;
        }

        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .iti__flag {
                background-image: url("https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/19.2.15/img/flags@2x.png") !important;
            }
        }

        /* Map Div */
        #location_map_div {
            border: 1px solid var(--border-gold);
            border-radius: 14px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            height: 280px;
        }

        #pac-input {
            background: rgba(18, 18, 18, 0.9) !important;
            border: 1px solid var(--border-gold) !important;
            color: #ffffff !important;
            border-radius: 10px !important;
            margin-top: 10px;
            padding: 8px 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
        }

        /* Business Plan & Subscription Packages */
        .business-plan-option {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 16px !important;
            color: #ffffff !important;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .business-plan-option:hover {
            border-color: rgba(250, 210, 147, 0.5) !important;
            background: rgba(255, 255, 255, 0.06) !important;
        }

        .business-plan-option.active {
            border-color: var(--primary) !important;
            background: rgba(250, 210, 147, 0.09) !important;
            box-shadow: var(--gold-glow);
        }

        .business-plan-option input[type="radio"],
        .payment-method-option input[type="radio"] {
            accent-color: var(--primary-dark);
            cursor: pointer;
        }

        .price-box {
            background: rgba(22, 22, 22, 0.9) !important;
            border: 1px solid rgba(250, 210, 147, 0.25) !important;
            border-radius: 16px !important;
            color: #ffffff !important;
            transition: all 0.3s ease;
        }

        .price-box.active {
            border-color: var(--primary) !important;
            box-shadow: 0 0 25px rgba(250, 210, 147, 0.35) !important;
            background: rgba(28, 25, 20, 0.95) !important;
        }

        .price-box strong.h3 {
            color: var(--primary);
            font-weight: 700;
        }

        .price-box hr {
            border-color: rgba(250, 210, 147, 0.2);
        }

        .price-box ul li {
            color: rgba(255, 255, 255, 0.8);
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: var(--primary) !important;
        }

        .payment-method-option {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }

        .payment-method-option.active {
            border-color: var(--primary) !important;
            background: rgba(250, 210, 147, 0.08) !important;
        }

        /* Spartan Multi Image Picker */
        .spartan_item_wrapper {
            background: rgba(255, 255, 255, 0.04) !important;
            border: 2px dashed rgba(250, 210, 147, 0.35) !important;
            border-radius: 14px !important;
            overflow: hidden;
        }

        /* Checkbox styling */
        .form-check-input {
            accent-color: var(--primary);
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        /* Toastr */
        .toast {
            background-color: rgba(20, 20, 20, 0.95) !important;
            border: 1px solid var(--primary) !important;
            color: #ffffff !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.8);
        }

        /* Preloader */
        .preloader {
            background-color: var(--bg-dark) !important;
        }
    </style>
</head>

<body>
    <div class="preloader"></div>

    <?php
    $logo = getBusinessSettingsImageFullPath(key: 'business_logo', settingType: 'business_information', path: 'business/',  defaultPath : 'public/assets/admin-module/img/placeholder.png');
    ?>

    <div class="dark-support">
        <div class="register-wrap style__two">
            <div class="register-left d-flex justify-content-center align-items-center bg-center">
                <div class="glass-card-left d-flex flex-column gap-3 align-items-center justify-content-center">
                    <div class="text-center w-100">
                        <img class="login-logo mb-3"
                            src="{{ $logo }}"
                            alt="{{ translate('MMC Logo') }}"
                            style="max-width: 150px; filter: drop-shadow(0 0 15px rgba(250, 210, 147, 0.4));">

                        <h2 class="text-center text-white mt-2 fw-bold" style="font-size: 1.85rem; line-height: 1.3;">
                            Drive Your <br><span class="gold-gradient-text">Business Forward</span>
                        </h2>

                        <p class="text-white-50 mt-3 mb-4" style="font-size: 0.92rem; line-height: 1.5;">
                            {{ translate('Join the MMC Elite Automotive Partner Network & expand your service terminal.') }}
                        </p>

                        <div class="partner-perks text-start d-flex flex-column gap-2 p-3 mb-4" style="background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border-gold); border-radius: 14px;">
                            <div class="d-flex align-items-center gap-2" style="font-size: 0.88rem;">
                                <span class="material-icons text-gold" style="font-size: 18px;">check_circle</span>
                                <span class="text-white">{{ translate('Direct Automotive Service Bookings') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2" style="font-size: 0.88rem;">
                                <span class="material-icons text-gold" style="font-size: 18px;">check_circle</span>
                                <span class="text-white">{{ translate('Verified & Trusted Partner Terminal') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2" style="font-size: 0.88rem;">
                                <span class="material-icons text-gold" style="font-size: 18px;">check_circle</span>
                                <span class="text-white">{{ translate('Real-time Dispatch & Revenue Analytics') }}</span>
                            </div>
                        </div>

                        <div class="pt-2 border-top" style="border-color: rgba(255, 255, 255, 0.1) !important;">
                            <span class="text-white-50" style="font-size: 0.9rem;">{{ translate('Already registered as partner?') }}</span>
                            <a href="{{ route('provider.auth.login') }}" class="text-gold fw-bold ms-1" style="text-decoration: none;">{{ translate('Sign In to Terminal') }} &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="register-right-wrap">
                <div class="register-right mx-auto p-3">
                    <div class="text-center mb-4 d-flex flex-column gap-1 mt-2">
                        <div class="d-inline-flex align-items-center justify-content-center mb-1">
                            <span class="badge" style="background: rgba(250, 210, 147, 0.15); color: #FAD293; border: 1px solid rgba(250, 210, 147, 0.3); border-radius: 20px; font-weight: 600; font-size: 0.75rem; letter-spacing: 1px; padding: 6px 14px; text-transform: uppercase;">
                                {{ translate('Partner Onboarding') }}
                            </span>
                        </div>
                        <h2 class="gold-gradient-text fw-bold mb-1" style="font-size: 1.95rem;">{{ translate('Provider Registration') }}</h2>
                        <p class="text-white-50 mb-0" style="font-size: 0.9rem;">{{ translate('Sign up to connect and provide premium automotive services') }}</p>
                    </div>

                    <form action="{{route('provider.auth.sign-up-submit')}}" method="POST" enctype="multipart/form-data" id="register-vertical-steps">
                        @csrf

                        <h3>{{translate('Step 1')}}</h3>
                        <section>
                            <div class="" id="register-form-p-0">
                                <h3 class="border-bottom mb-4 pb-2">{{translate('Basic Information')}}</h3>
                                <h5 class="border-bottom mb-4 pb-2">{{translate('General_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control"
                                                    value="{{old('company_name')}}" name="company_name"
                                                    placeholder="{{translate('Company_Name')}}">
                                            <label>{{translate('Company_Name')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">apartment</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="email" id="company_email" class="form-control"
                                                    value="{{old('company_email')}}" name="company_email"
                                                    placeholder="{{translate('email')}}">
                                            <label>{{translate('email')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">mail</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="country-picker-1">
                                            <input type="tel"
                                                   class="form-control"
                                                    value="{{old('company_phone')}}"
                                                   name="company_phone"
                                                    id="company_phone"
                                                    placeholder="{{translate('123 456 789')}}" maxlength="255">
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control" name="company_address"
                                                            placeholder="{{translate('Address')}}" value="{{old('company_address')}}">
                                            <label>{{translate('Address')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">location_on</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <h5 class="mb-3">{{ translate('company_logo') }} (1:1) <span class="text-danger">*</span></h5>
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                            <div>
                                                <div class="form-error-wrap upload-file">
                                                    <input type="file" class="upload-file__input"
                                                            name="logo" accept=".jpg,.png,.jpeg,.gif">
                                                    <span class="upload-file__edit">
                                                            <span class="material-icons">edit</span>
                                                        </span>
                                                    <div class="upload-file__img">
                                                        <img
                                                            src="{{asset('public/assets/provider-module')}}/img/media/upload-file.png"
                                                            alt="">
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="opacity-75 max-w220">{{translate('Image_format_-_jpg,_png,_jpeg,_gif_Image_Size_-_maximum_size_2_MB_Image_Ratio_-_1:1')}}</p>
                                        </div>
                                    </div>

                                    <div class="border-bottom d-flex align-items-center justify-content-between gap-2 pb-2 mb-4">
                                        <h5>{{translate('Contact Person Information')}}</h5>
                                        <div class="d-flex gap-2 align-items-center">
                                            <label for="sameAsGI">{{translate('Same as general info')}}</label>
                                            <input type="checkbox" value="" id="sameAsGI">
                                        </div>
                                    </div>

                                    <div class="sameAsGI_div">
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="text" class="form-control"
                                                        value="{{old('contact_person_name')}}"
                                                        name="contact_person_name"
                                                        placeholder="{{translate('Contact_Person_Name')}}">
                                                <label>{{translate('Name')}} <span class="text-danger">*</span></label>
                                                <span class="material-icons">person</span>
                                            </div>
                                        </div>
                                        <div class="mb-30">
                                            <div class="country-picker-2">
                                                <input type="tel"
                                                       class="form-control"
                                                        value="{{old('contact_person_phone')}}"
                                                        id="contact_person_phone"
                                                        name="contact_person_phone"
                                                        placeholder="{{translate('123 456 789')}}">
                                            </div>
                                        </div>
                                        <div class="mb-30">
                                            <div class="form-floating form-floating__icon">
                                                <input type="email" class="form-control"
                                                        value="{{old('contact_person_email')}}"
                                                        name="contact_person_email"
                                                        placeholder="{{translate('Email')}}">
                                                <label>{{translate('Email')}} <span class="text-danger">*</span></label>
                                                <span class="material-icons">mail</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 2')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('Business_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30">
                                        <div class="form-error-wrap">
                                            <select name="zone_id" id="zone_id" class="form-select">
                                                <option value="0" selected
                                                        disabled>{{translate('Select_Zone')}}</option>
                                                @foreach($zones as $zone)
                                                    <option
                                                        value="{{$zone->id}}" {{old('zone_id')==$zone->id?'selected':''}}>{{$zone->name}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div id="location_map_div">
                                            <input id="pac-input" class="form-control w-auto"
                                                    data-toggle="tooltip"
                                                    data-placement="right"
                                                    data-original-title="{{ translate('search_your_location_here') }}"
                                                    type="text"
                                                    placeholder="{{ translate('search_here') }}"/>
                                            <div id="location_map_canvas"
                                                    class="overflow-hidden rounded h-100"></div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="text" id="latitude" name="latitude"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex:') }} 23.8118428"
                                                            value="{{ old('latitude') }}" required
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{translate('Select from map')}}" readonly>
                                                    <label for="latitude">{{ translate('latitude') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-30">
                                                <div class="form-floating">
                                                    <input type="text" step="0.1" name="longitude"
                                                            class="form-control"
                                                            placeholder="{{ translate('Ex:') }} 90.356331"
                                                            id="longitude"
                                                            value="{{ old('longitude') }}" required
                                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                                            title="{{translate('Select from map')}}" readonly>
                                                    <label for="longitude">{{ translate('longitude') }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-30">
                                        <div class="form-error-wrap">
                                            <select name="identity_type" id="identity_type"
                                                    class="form-select">
                                                <option value="0" selected disabled>{{translate('Identity_Type')}}</option>
                                                <option value="passport" {{old('identity_type')=='passport'?'selected':''}}>{{translate('passport')}}</option>
                                                <option value="nid" {{old('identity_type')=='nid'?'selected':''}}>{{translate('nid')}}</option>
                                                <option value="driving_license" {{old('identity_type')=='driving_license'?'selected':''}}>{{translate('driving_license')}}</option>
                                                <option value="trade_license" {{old('identity_type')=='trade_license'?'selected':''}}>{{translate('trade_license')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <input type="text" class="form-control"
                                                    value="{{old('identity_number')}}" name="identity_number"
                                                    placeholder="{{translate('Identity_Number')}}">
                                            <label>{{translate('Identity_Number')}}</label>
                                            <span class="material-icons">badge</span>
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-error-wrap">
                                            <div class="mb-3">{{translate('Identity_Image')}}(2:1) <span class="text-danger">*</span></div>
                                            <div id="multi_image_picker2" class="row"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 3')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('account_Information')}}</h5>
                                <div class="mb-4">
                                    <div class="mb-30" data-bs-toggle="tooltip" data-bs-title="if you want to change
                                    mail you need to go to first stage">
                                        <div class="form-floating form-floating__icon">
                                            <input type="email" id="account_email" class="form-control"
                                                    value="{{old('account_email')}}" name="account_email"
                                                    placeholder="{{translate('Email')}}" readonly>
                                            <label>{{translate('Email')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">mail</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <span class="material-icons togglePassword">visibility_off</span>
                                            <input type="password" class="form-control" value=""
                                                    name="password" id="password"
                                                    placeholder="{{translate('Password')}}">
                                            <label>{{translate('Password')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">lock</span>
                                        </div>
                                    </div>
                                    <div class="mb-30">
                                        <div class="form-floating form-floating__icon">
                                            <span class="material-icons togglePassword">visibility_off</span>
                                            <input type="password" class="form-control" value=""
                                                    name="confirm_password"
                                                    placeholder="{{translate('Confirm_Password')}}">
                                            <label>{{translate('Confirm_Password')}} <span class="text-danger">*</span></label>
                                            <span class="material-icons">lock</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <h3>{{translate('Step 4')}}</h3>
                        <section>
                            <div class="">
                                <h5 class="border-bottom mb-4 pb-2">{{translate('Choose_Business_Plan')}}</h5>
                                <div class="choose_business_plan_wrap">
                                    <div class="d-flex flex-column gap-3">
                                        @if($commission)
                                            <label class="business-plan-option border active rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Commision Base')}}</h4>
                                                    <div>{{translate('You_have_to_give_a_certain_percentage_of_commission_to_admin_for_every_booking_request')}}</div>
                                                </div>

                                                <input value="commision_base" type="radio" name="choose_business_plan" class="position-static w-26" checked>
                                            </label>
                                        @endif
                                        @if($subscription && count($formattedPackages) > 0)
                                            <label class="business-plan-option border rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Subscription Base')}}</h4>
                                                    <div>{{translate('You_have_to_pay_a_certain_amount_in_every_month_/_year_to_admin_as_subscription_fee')}}</div>
                                                </div>

                                                <input value="subscription_base" type="radio" name="choose_business_plan" class="position-static w-26">
                                            </label>
                                        @endif
                                    </div>
                                </div>

                                @if($subscription)
                                    <div class="priceBoxSwiper-wrap">
                                        <h5 class="mt-4 mb-3 text-center">{{translate('Select Plan')}}</h5>
                                        <div class="w-100 mw-440">
                                            <div dir="ltr" class="swiper priceBoxSwiper">
                                                <div class="swiper-wrapper">
                                                    <input type="hidden" name="selected_package_id" id="selected-package-input" value="">

                                                    @foreach($formattedPackages as $index => $package)
                                                        <div class="swiper-slide h-auto">
                                                            <div class="price-box {{ $index == 1 ? 'active' : '' }} d-flex flex-column rounded-3 border h-100 package-option" data-id="{{ $package->id }}">
                                                                <div class="price-box__top d-flex gap-2 align-items-center justify-content-center px-2 py-4 text-center mb-3">
                                                                    <span class="material-symbols-outlined uncheck-icon">radio_button_unchecked</span>
                                                                    <span class="material-icons text-warning check-icon">check_circle</span>
                                                                    <h5 class="line-clamp-1">{{ $package->name }}</h5>
                                                                </div>

                                                                <div class="text-center min-h-62 d-flex flex-column justify-content-center">
                                                                    <strong class="h3">{{ with_currency_symbol($package->price) }}</strong>
                                                                    <div>{{ $package->duration }} {{translate('Days')}}</div>
                                                                </div>

                                                                <div class="px-2">
                                                                    <hr>
                                                                </div>

                                                                <div class="p-3 flex-grow-1 d-flex flex-column">
                                                                    <ul class="d-flex flex-column align-items-center gap-2 p-0 fs-12 mb-30">
                                                                        @foreach($package->feature_list as $feature)
                                                                            <li>{{ $feature }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="swiper-button-next"></div>
                                                <div class="swiper-button-prev"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="free_trial_or_payment_wrap">
                                    <div class="d-flex flex-column gap-3 mt-4">
                                        @if($freeTrialStatus)
                                            <label class="business-plan-option border active rounded p-3 d-flex justify-content-between gap-2">
                                                <div class="d-flex flex-column gap-2">
                                                    <h4>{{translate('Continue with')}} {{ $duration }} {{translate('Days')}} {{translate('Free Trial')}}</h4>
                                                    <div>{{translate('Use the system free for')}} {{ $duration }} {{translate('days')}}. {{translate('After that you
                                                        have to complete payments to continue')}}</div>
                                                </div>

                                                <input value="free_trial" type="radio" name="free_trial_or_payment" class="position-static w-26" checked>
                                            </label>
                                        @endif

                                        @if($digitalPayment)
                                            <label class="business-plan-option payment_methods_list_container border rounded p-3">
                                                <div class="d-flex justify-content-between gap-2">
                                                    <div class="d-flex flex-column gap-2">
                                                        <h4>{{translate('Select Payment Method')}}</h4>
                                                        <div>{{translate('You can use any of our secure payment method & get selected subscription plan features')}}</div>
                                                    </div>

                                                    <input value="payment" type="radio" name="free_trial_or_payment" class="position-static w-26">
                                                </div>
                                                <div class="payment_methods_list d-flex flex-column gap-3">
                                                    <input type="hidden" name="payment_platform" value="web">
                                                    <input type="hidden" name="callback" value="{{route('provider.auth.login')}}">
                                                    @foreach($paymentGateways ?? [] as $gateway)
                                                    <label class="payment-method-option border active rounded p-3 d-flex justify-content-between">
                                                        <div class="d-flex gap-2 align-items-center">
                                                            <img width="70" src="{{onErrorImage(
                                                                    $gateway['gateway_image'],
                                                                    asset('storage/app/public/payment_modules/gateway_image').'/' . $gateway['gateway_image'],
                                                                    asset('public/assets/admin-module/img/placeholder.png') ,
                                                                    'payment_modules/gateway_image/')}}" alt="{{translate('gateway image')}}">
                                                            <div>{{ $gateway['label'] }}</div>
                                                        </div>

                                                        <input value="{{ $gateway['gateway'] }}" type="radio" name="payment_method" class="position-static" {{ $loop->first ? 'checked' : '' }}>
                                                    </label>
                                                    @endforeach
                                                </div>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                        </section>
                    </form>
                </div>
            </div>
        </div>
        <?php
            $addressLat = \Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','address_latitude')->first()?->live_values ?? 23.811842872190;
            $addressLong = \Modules\BusinessSettingsModule\Entities\BusinessSettings::where('key_name','address_longitude')->first()?->live_values ?? 23.811842872190;
        ?>
    </div>

    <script src="{{asset('public/assets/provider-module')}}/js/jquery-3.6.0.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/bootstrap.bundle.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/plugins/perfect-scrollbar/perfect-scrollbar.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/main.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/plugins/swiper/swiper-bundle.min.js"></script>

    <script src="{{asset('public/assets/provider-module')}}/js/sweet_alert.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/toastr.js"></script>
    {!! Toastr::message() !!}

    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-steps/jquery.steps.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="{{asset('public/assets/admin-module')}}/js/helper.js"></script>

    {{--country code --}}
    <span class="system-default-country-code" data-value="us"></span>
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/utils.js') }}"></script>
    <script src="{{ asset('public/assets/libs/intl-tel-input/js/intlTelInout-validation.js') }}"></script>

    <script>
        "use strict";

        document.addEventListener('DOMContentLoaded', function () {
            const packageOptions = document.querySelectorAll('.package-option');
            const selectedPackageInput = document.getElementById('selected-package-input');
            const initialSelectedPackage = document.querySelector('.package-option.active');
            if (initialSelectedPackage) {
                selectedPackageInput.value = initialSelectedPackage.dataset.id;
            }

            packageOptions.forEach(option => {
                option.addEventListener('click', function () {
                    packageOptions.forEach(opt => opt.classList.remove('active'));
                    this.classList.add('active');
                    selectedPackageInput.value = this.dataset.id;
                });
            });
        });

        $(document).ready(function () {
            $("#company_email").on("change keyup paste", function () {
                $('#account_email').val($(this).val());
            });
            $("#company_phone").on("change keyup paste", function () {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$(this).val()}`);
            });
            $('#register-vertical-steps-p-0').find('.iti__flag-container').on("click", function () {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
            });

            setInterval(() => {
                const countryCode = $('#register-vertical-steps-p-0').find('.iti__selected-dial-code').text();
                $('#account_phone').val(`${countryCode} ${$("#company_phone").val()}`);
                $('#account_email').val($('#company_email').val());
            }, 2000);


            $('#sameAsGI').on('change', function() {
                if ($(this).is(':checked')) {
                    $('[name="contact_person_name"]').val($('[name="company_name"]').val());
                    $('[name="contact_person_email"]').val($('#company_email').val());

                    let companyPhone = $('#company_phone').val();
                    let dialCode = $('.country-picker-1 .iti__selected-dial-code').text();
                    let fullPhoneNumber = dialCode + companyPhone.replace(dialCode, '');

                    $('#contact_person_phone').val(fullPhoneNumber);

                    $('[name="contact_person_phone"]').val(companyPhone.replace(dialCode, ''));
                    $('.country-picker-phone-number2').val(fullPhoneNumber);

                    $('.country-picker-2').find('.iti__selected-dial-code').text(dialCode);
                    $('.country-picker-2').find('.iti__selected-flag .iti__flag').attr('class', $('.country-picker-1').find('.iti__flag').attr('class'));

                } else {
                    $('[name="contact_person_name"]').val('');
                    $('[name="contact_person_email"]').val('');
                    $('#contact_person_phone').val('');
                    $('.country-picker-phone-number2').val('');
                }
            });



            let swiper = new Swiper(".priceBoxSwiper", {
                slidesPerView: 1.8,
                spaceBetween: 10,
                centeredSlides: true,
                initialSlide: 1,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
            });
        });

    </script>

    <script>
        (function ($) {
            "use strict";


            let company_phone = undefined;
            let contact_person_phone = undefined;


            let formWizard = $("#register-vertical-steps");

            // Form validation with jQuery
            formWizard.validate({
                errorPlacement: function (error, element) {
                    element.parents('.form-floating, .form-error-wrap').after(error);
                },
            });

            function setValidationRulesAndMessages(rules, messages) {
                formWizard.validate().settings.rules = rules;
                formWizard.validate().settings.messages = messages;
            }

            function handleImageUploadValidation() {
                let uploaded = $("#multi_image_picker2 > .spartan_item_wrapper").length < 2;
                if (uploaded) {
                    var errorMessageElement = formWizard.find(".coba-identity-img-err");

                    if (errorMessageElement.length > 0) {
                        errorMessageElement.text("Please Upload identity Image");
                    } else {
                        formWizard.find("#multi_image_picker2").parents(".form-error-wrap").after(`<span class="text-danger coba-identity-img-err">Please Upload identity Image</span>`);
                    }
                    return false;
                } else {
                    formWizard.find(".coba-identity-img-err").remove();
                }
                return true;
            }

            let firstClick = true;
            formWizard.steps({
                headerTag: "h3",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "vertical",
                autoFocus: true,
                labels: {
                    finish: "Complete",
                    next: "Next",
                    previous: "Previous"
                },
                onStepChanging: function (event, currentIndex, newIndex) {
                    if (newIndex < currentIndex) {
                        return true;
                    }

                    switch (currentIndex) {
                        case 0:
                            setValidationRulesAndMessages({
                                company_name: "required",
                                company_email: {
                                    required: true,
                                    email: true
                                },
                                company_phone: "required",
                                company_address: "required",
                                logo: "required",
                                contact_person_name: "required",
                                contact_person_phone: "required",
                                contact_person_email: "required",
                            }, {
                                company_name: "Please enter your name",
                                company_phone: "Please enter your phone",
                                company_phone_2: "Please enter your phone",
                                company_email: "Please enter a valid email address",
                                company_address: "Please enter your address",
                                logo: "Please upload logo",
                                contact_person_name: "Please enter your name",
                                contact_person_phone: "Please enter your phone",
                                contact_person_email: "Please enter a valid email address",
                            });

                            formWizard.validate().settings.ignore = ":disabled,:hidden";
                            if (!formWizard.valid()) {
                                return false;
                            }

                            let email = $("#company_email").val();
                            let companyPhone = $('#company_phone').val();
                            let dialCode = $('.country-picker-1 .iti__selected-dial-code').text();
                            let phone = dialCode + companyPhone.replace(dialCode, '');

                            let isValid = false;
                            console.log(email, companyPhone, dialCode, phone);

                            $.ajax({
                                url: "{{ route('check-unique-user') }}",
                                type: "POST",
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: {
                                    email: email,
                                    phone: phone,
                                },
                                async: false,
                                success: function (response) {
                                    if (response.success) {
                                        isValid = true;
                                    } else {
                                        if (response.email_exists) {
                                            toastr.error('Email already exists')
                                        }
                                        if (response.phone_exists) {
                                            toastr.error('Phone already exists')
                                        }
                                        isValid = false;
                                    }
                                },
                                error: function () {
                                    isValid = false;
                                }
                            });
                            return isValid;

                        //    break;
                        case 1:
                            setValidationRulesAndMessages({
                                zone_id: "required",
                                latitude: "required",
                                longitude: "required",
                                identity_type: "required",
                                identity_number: "required",
                            }, {
                                zone_id: "Please enter your Zone",
                                latitude: "Please enter latitude",
                                longitude: "Please enter longitude",
                                identity_type: "Please enter identity type",
                                identity_number: "Please enter identity number",
                            });

                            if (!handleImageUploadValidation()) {
                                return false;
                            }
                            break;
                        case 2:
                            setValidationRulesAndMessages({
                                account_email: "required",
                                password: {
                                    required: true,
                                    minlength: 8
                                },
                                confirm_password: {
                                    required: true,
                                    minlength: 8,
                                    equalTo: "#password"
                                },
                            }, {
                                account_email: "Please enter email",
                                password: {
                                    required: "Please provide a password",
                                    minlength: "Your password must be at least 8 characters long"
                                },
                                confirm_password: {
                                    required: "Please provide confirm password",
                                    minlength: "Your password must be at least 8 characters long",
                                    equalTo: "Please enter the same password as above",
                                },
                            });
                            break;
                    }

                    formWizard.validate().settings.ignore = ":disabled,:hidden";

                    return formWizard.valid();
                },
                onFinished: function (event, currentIndex) {
                    event.preventDefault();
                    firstClick = $('body').hasClass('subscription_base');

                    if(firstClick){
                        $(".choose_business_plan_wrap").slideUp();
                        $(".priceBoxSwiper-wrap").slideUp();
                        $(".free_trial_or_payment_wrap").slideDown();
                        $("a[href='#finish']").text('Register');
                        $('body').removeClass('subscription_base');
                    } else {
                        formWizard.submit();
                    }
                }
            });

        })(jQuery);
    </script>


    <script src="{{asset('public/assets/provider-module')}}/js//tags-input.min.js"></script>
    <script src="{{asset('public/assets/provider-module')}}/js/spartan-multi-image-picker.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{business_config('google_map', 'third_party')?->live_values['map_api_key_client']}}&libraries=places&v=3.45.8"></script>
    <script>
        "use strict";

        $("#multi_image_picker2").spartanMultiImagePicker({
            fieldName: 'identity_images[]',
            maxCount: 2,
            allowedExt: 'png|jpg|jpeg',
            rowHeight: 'auto',
            groupClassName: 'col-6',
            dropFileLabel: "{{translate('Drop_here')}}",
            placeholderImage: {
                image: '{{asset('public/assets/admin-module')}}/img/media/banner-upload-file.png',
                width: '100%',
            },

            onRenderedPreview: function (index) {
                toastr.success('{{translate('Image_added')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            // onRemoveRow: function (index) {
            //     console.log(index);
            // },
            onExtensionErr: function (index, file) {
                toastr.error('{{translate('Please_only_input_png_or_jpg_type_file')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('{{translate('File_size_too_big')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }

        });
        @if ($errors->any())
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
        @endif

        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#viewer').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }
        }

        $("#customFileEg1").change(function () {
            readURL(this);
        });




        $(document).ready(function () {
            function initAutocomplete(mapCanvasId) {
                var myLatLng = {
                    lat: {{ $addressLat }},
                    lng: {{ $addressLong }}
                };
                const map = new google.maps.Map(document.getElementById(mapCanvasId), {
                    center: {
                        lat: {{ $addressLat }},
                        lng: {{ $addressLong }}
                    },
                    zoom: 13,
                    mapTypeId: "roadmap",
                });

                var marker = new google.maps.Marker({
                    position: myLatLng,
                    map: map,
                });

                marker.setMap(map);
                var geocoder = geocoder = new google.maps.Geocoder();
                google.maps.event.addListener(map, 'click', function (mapsMouseEvent) {
                    var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                    var coordinates = JSON.parse(coordinates);
                    var latlng = new google.maps.LatLng(coordinates['lat'], coordinates['lng']);
                    marker.setPosition(latlng);
                    map.panTo(latlng);

                    document.getElementById('latitude').value = coordinates['lat'];
                    document.getElementById('longitude').value = coordinates['lng'];


                    geocoder.geocode({
                        'latLng': latlng
                    }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                            if (results[1]) {
                                document.getElementById('address').innerHtml = results[1].formatted_address;
                            }
                        }
                    });
                });

                const input = document.getElementById("pac-input");
                const searchBox = new google.maps.places.SearchBox(input);
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);

                map.addListener("bounds_changed", () => {
                    searchBox.setBounds(map.getBounds());
                });
                let markers = [];

                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();

                    if (places.length == 0) {
                        return;
                    }

                    markers.forEach((marker) => {
                        marker.setMap(null);
                    });
                    markers = [];

                    const bounds = new google.maps.LatLngBounds();
                    places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            return;
                        }
                        var mrkr = new google.maps.Marker({
                            map,
                            title: place.name,
                            position: place.geometry.location,
                        });
                        google.maps.event.addListener(mrkr, "click", function (event) {
                            document.getElementById('latitude').value = this.position.lat();
                            document.getElementById('longitude').value = this.position.lng();
                        });

                        markers.push(mrkr);

                        if (place.geometry.viewport) {
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                    });
                    map.fitBounds(bounds);
                });
            };
            initAutocomplete("location_map_canvas");
        });


        $('.togglePassword').on('click', function () {
            const input = $(this).siblings('input');
            const type = input.attr('type') === 'password' ? 'text' : 'password';
            input.attr('type', type);
            $(this).text(type === 'password' ? 'visibility_off' : 'visibility');
        });

        $('.__right-eye').on('click', function () {
            if ($(this).hasClass('active')) {
                $(this).removeClass('active')
                $(this).find('i').removeClass('tio-invisible')
                $(this).find('i').addClass('tio-hidden-outlined')
                $(this).siblings('input').attr('type', 'password')
            } else {
                $(this).addClass('active')
                $(this).siblings('input').attr('type', 'text')


                $(this).find('i').addClass('tio-invisible')
                $(this).find('i').removeClass('tio-hidden-outlined')
            }
        });
    </script>
</body>
</html>
