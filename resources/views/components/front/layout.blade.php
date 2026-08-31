<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="timer_time" content="{{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}" />
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <title>{{ $title }}</title>

    @php

        $styleheets = [
            'assets/css/bootstrap.min.css',
            'assets/css/datatables.min.css',
            'assets/css/jquery-ui.css',
            'assets/css/font-awesome.css',
            'assets/css/front_custom.css',
            'toastr/toastr.min.css',
            'assets/css/select2.min.css',
            'assets/css/styles.css',
            'assets/css/response.css',
        ];

    @endphp


    @if ($styleheets)

        @foreach ($styleheets as $styleheet)
            <link rel="stylesheet" href="{{ asset($styleheet . '?ver=' . time()) }}" />
        @endforeach

    @endif

    @stack('css')

    {{-- Shared responsive stylesheet (loaded last so it can override page-specific inline styles) --}}
    <link rel="stylesheet" href="{{ asset('assets/ffo-admin/css/responsive.css?ver=' . time()) }}" />

    <style>
        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }

        /* @media(max-width:768px){
  .login-page .login-form-page{
    width: 92%;
    padding: 14px;
    border-radius: 8px;
    height: calc(100vh - 50px);
    margin-top: 25px;
}


.left-image-wrapper{
}
 .login-page .login-wrapper{
    min-height: auto;
  }
  .login-page .login-wrapper .inner-login-wrapper{
    width: 100%;
    position: initial;
    transform: none;
    padding: 22px 0;
  }
.login-page .login-wrapper.card .card-text{
    width: 100%;
    padding: 0;
}
  .login-form-page .ps-0{
    padding: 0px!important;
}
  .login-page .login-form-page .left-image-wrapper {
    height: 100%;
    background: url(../img-new/login-bg-23.jpg);
    background-size: cover;
    background-position-y: center;
    border-radius: 20px;
    height: 228px;
    overflow: hidden;
}
  .action-bottom{
    margin-top:0px !important;
  }
.login-page .login-wrapper.card .btn-primary{
    margin-top: 8px;
    min-height: 48px;
}
  .font-signup{
    display: block;
    text-decoration: underline;
    font-weight: 500;
}
  .login-form-container{
    margin-top: 20px;
  }

  
} */
    </style>

    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
</head>

<body class="login-page new_login_page" id="mainbody">
    <div id="cover-spin"></div>
    <!-- @include('components.front.header') -->

    <!--<div id="wrapper" class="login-wrapper">
 <div id="content-wrapper" class="d-flex flex-column">
        <div id="content" class="" style="min-height:512px;">-->
    @yield('page-content')
    <!--</div>
 </div>
</div>-->
    @include('components.front.footer')

    @yield('js')

    @stack('js')

    <script>
        if (window !== window.top) {
            document.getElementById('mainbody').innerHTML =
                "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
            document.getElementById("mainbody").style.backgroundColor = "#ccc";
        }


        setTimeout(function() {
            $("#cover-spin").hide();
        }, 300);

        function maskMobileNumber(mobile) {
            if (!mobile) return '';

            const str = mobile.toString();

            if (str.length < 6) {
                return '*'.repeat(str.length);
            }

            const visibleStart = 2;
            const visibleEnd = 2;

            const maskedLength = str.length - (visibleStart + visibleEnd);
            const maskedPart = '*'.repeat(maskedLength);

            return (
                str.slice(0, visibleStart) +
                maskedPart +
                str.slice(-visibleEnd)
            );
        }

        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.otp-box');

            inputs.forEach((input, index) => {

                // Allow only digits & move forward
                input.addEventListener('input', (e) => {
                    input.value = input.value.replace(/\D/g, '');

                    if (input.value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                });

                // Handle backspace
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace') {
                        if (input.value === '' && index > 0) {
                            inputs[index - 1].focus();
                        }
                    }
                });

                // Handle paste
                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const paste = e.clipboardData.getData('text').replace(/\D/g, '');

                    paste.split('').forEach((char, i) => {
                        if (inputs[i]) {
                            inputs[i].value = char;
                        }
                    });

                    const lastIndex = Math.min(paste.length, inputs.length) - 1;
                    if (lastIndex >= 0) {
                        inputs[lastIndex].focus();
                    }
                });
            });
        });
    </script>


<!-- password changed successfully -->
<div class="modal fade" id="exampleModal" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img src="{{asset('assets/img/tick-blue.svg')}}" alt="" style="width: 32px;">
       <h5 class="fw-medium mt-3">Password Changed Successfully</h5>
	   <p>Your password has been changed successfully.</p>
       <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Close"> Cancel </button>
      </div>
    </div>
  </div>
</div>


</body>

</html>
