@extends('components.front.layout')

@push('css')
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .captcha .c-reload {
            border: 0px;
            display: block;
            color: #00508d;
        }

        /* .left-side-img{    min-height: 655px;
                                                        max-height: 655px;} */
        .new_login_page .login-form-page {
            max-width: 850px;
            min-width: 850px;
            border-radius: 10px;
            background: linear-gradient(124deg, #FFFFFF, #FFFFFF, #cbedff);
        }

        .new_login_page {
            background-image: url(./assets/img/login-background.png);
            background-repeat: no-repeat;
            background-size: cover;
        }

        .login-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .left-side {
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

       
        .info-glass h4 {
            font-size: 16px;
            font-weight: 600;
            padding-bottom: 0px;
            text-align: center;
        }

        .info-glass p {
            font-size: 11px !important;
            font-weight: 200;
            padding-bottom: 7px;
            text-align: center;
        }
        

       .info-glass a {
            font-size: 10px;
            font-weight: 400;
            padding: 0;
            text-align: center;
            padding-bottom: 0px;
            margin-bottom: 9px !important;
        }
       .form-control {
                background-color: #ffffff;
                border: 1px solid #cdcdcd !important;
                padding: 10px;
                border-radius: 4px;
                font-size: 14px;
                border-top-right-radius: 8px !important;
                border-bottom-right-radius: 8px !important;
            }
        /* Tab Styling */
        .nav-tabs {
            border-bottom: 2px solid #eee;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 600;
            padding: 5px 20px;
            font-size: 14px;
        }

        .nav-tabs .nav-link.active {
            color: #1e2a78;
            border-bottom: 3px solid #1e2a78;
            background: none;
        }

        .btn-primary-custom {
            background-color: #1e2a78;
            border: none;
            padding: 12px;
            font-weight: 600;
        }

        .btn-primary-custom:hover {
            background-color: #151d54;
        }

        .captcha-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 5px 15px;
            border-radius: 5px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-style: italic;
            letter-spacing: 3px;
            font-size: 1.2rem;
            color: #ff8c00;
            /* Orange color from image */
        }

        .login-page {
            width: 100%;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            position: relative;
        }

       .login-card .right-side {
            min-height: 545px;
            padding: 8px 10px 0px 14px;
        }

        .login-page .login-form-page {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            margin: 0 auto;
            width: 75%;
            padding: 16px;
            background-color: #ffffff7a;
            border: 0;
            border-radius: 16px;
            box-shadow: 0 3px 32px #3a69c336;
            backdrop-filter: blur(3px);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .login_img_bg_2 {
            background: url(./assets/img/image-login.png) repeat;
            background-size: cover;
            border-radius: 10px;
            height: 100%;
            min-height: 545px;
        }

        .card.login-card.login-form-page {
            max-width: 950px;
            min-width: 950px;
            border-radius: 10px;
            background: linear-gradient(124deg, #FFFFFF, #FFFFFF, #cbedff);
        }

        .right-side p.small {
            font-size: 12px;
        }

        .login-form-page form label.form-label {
            font-size: 14px;
            font-weight: 500;
            color: #151515;
        }

        .right-side .tab-content {
            min-height: 310px;
        }

        .login-form-page form .form-control,
        .login-form-page form form .form-select {
            background-color: #ffffff;
            border: 1px solid #cdcdcd !important;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }

        .login-form-page h3 {
            font-size: 22px !important;
            font-weight: 600;
            color: #1e2a78!important;
        }

        /* =========================
                           OTP SCREEN
                        ========================= */

        .otp-screen {
            padding: 30px 0px 30px 6px;
        }

        .otp-screen h3 {
            font-size: 22px;
            font-weight: 600;
            color: #1e2a78;
            margin-bottom: 10px;
        }

        .otp-screen h4 {
            font-size: 22px;
            font-weight: 500;
            color: #1e2a78;
            margin-bottom: 10px;
            padding-top: 22px;
        }

        .otp-screen p {
            font-size: 12px;
            color: #555;
            margin-bottom: 28px;
        }

        .otp-inputs {
            display: flex;
            gap: 12px;
            margin-bottom: 18px;
        }

        .otp-inputs input {
            width: 44px;
            height: 48px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            border: 1px solid #cfcfcf;
            border-radius: 6px;
        }

        .otp-inputs input:focus {
            outline: none;
            border-color: #1e2a78;
            box-shadow: 0 0 0 2px rgba(30, 42, 120, 0.1);
        }

        .resend-text {
            font-size: 12px;
            color: #666;
            margin-bottom: 28px;
        }

        .resend-text a {
            color: #1e2a78;
            font-weight: 500;
            text-decoration: underline;
            cursor: pointer;
        }

        .verify-btn {
            background: #1e2a78;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            width: 100%;
        }

        .verify-btn:hover {
            background: #151d54;
        }

        .right-side {
            min-height: 525px;
        }

        .login-page .login-form-page {
            padding: 11px !important;
        }


        .otp-inputs {
            display: flex;
            gap: 18px;
            margin: 28px 0 18px;
        }

        .otp-inputs input {
            width: 42px;
            height: 40px;
            border: none;
            border-bottom: 2px solid #cfcfcf;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            background: transparent;
        }

        .otp-inputs input:focus {
            outline: none;
            border-bottom-color: #2c3fd6;
            /* blue focus underline */
        }
    </style>
@endpush

@section('page-content')
    <div class="card login-card login-form-page">
        <div class="row g-0 left-side-img">

            <div class="col-md-6 left-side d-none d-md-flex ">
                <div class="login_img_bg_2"></div>
                <div class="info-glass">
                    <h4>Welcome to MSME TEAM Portal</h4>
                    <p>Please click on the below link to register your business on Udyami Bharat Portal:</p>
                    <a href="#"
                        class="text-white d-block mb-3 text-break">https://udyamregistration.gov.in/Government-India/Ministry-MSME-registration.htm</a>
                    <p class="mb-0">If you wish to seek help from helpdesk Please click here,
                        <strong><a href="{{ config('url.contact_us_url')}}" target="_blank" class="text-white fs-7">Helpdesk</a></strong>
                    </p>
                </div>
            </div>

            <div class="col-md-6 right-side ">
                @yield('auth-form')
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function reloadCaptcha({
            captchaImageElement
        }) {
            $.ajax({
                type: 'GET',
                url: "{{ url('refresh_captcha') }}",
                beforeSend: function() {
                    $(captchaImageElement).html('loading...');
                },
                success: function(data) {
                    $(captchaImageElement).html(data.captcha);
                }
            });
        }



        var interval;

        function timerInterval() {
            var counter = 0;
            interval = setInterval(function() {
             if (counter > {{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}) {
                //if (counter > 10) {
                    stopTimer();
                    $("#otp_timer").hide();
                    $(".resend-message").hide();
                    $("#resend-otp").show();
                }
                counter++;
            }, 1000);
        }
    </script>
@endpush
