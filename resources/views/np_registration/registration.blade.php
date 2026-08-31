<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="timer_time" content="{{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <title>SNP Registration</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver=' . time()) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php Session::forget(['mobile', 'email']);
    Session::forget(['mobile_value', 'email_value']); ?>

    <style>
        .hide-field {
            display: none;
        }

        .signup-wrapper form .form-control, .signup-wrapper form .form-select {
    
            color: #2b2a2a!important;
        }

        .select2.select2-container{
            width:100% !important;
        }
    </style>

</head>


@php
    // dd($row);
    $timelineDetails = [
        ['step' => 1, 'title' => 'Basic Details', 'status' => 'done'],
        ['step' => 2, 'title' => 'Configuration Details', 'status' => 'pending'],
        ['step' => 3, 'title' => 'Value Proposition for MSEs', 'status' => 'pending'],
        ['step' => 4, 'title' => 'Commercial Model', 'status' => 'pending'],
    ];

@endphp

<body id="mainbody" class="registration-page">
    <div id="cover-spin" style="display:none"></div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 p-0">
                <div class="left-img-wrap h-100 px-4 py-3">
                    <div class="img-section-left sticky-top">
                        <div class="card-header logo-box mb-5 mt-0">
                            <img src="{{ asset('assets/img-new/mti-logo.svg') }}" class="logo">
                        </div>

                        @isset($timelineDetails)
                            <div class="timeline">
                                <ul class="unstyled p-0 m-0 ">
                                    @foreach ($timelineDetails as $timeline)
                                        <li class="d-flex gap-3 mb-3 {{ $timeline['status'] }} ">
                                            <div class="icon">{{ $timeline['step'] }}</div> {{ $timeline['title'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endisset
                    </div>
                </div>
            </div>

            <div class="col-lg-9 right-form-content">
                <div class="inner-header pb-3 pt-4 ">
                    <div class="row">
                        <div class="col-lg-6">
                            <h2 class="fw-bolder fs-4">Network Participant Registration</h2>
                        </div>
                        <div class="col-lg-6 text-end">
                            <button class="btn btn-stroked-theme"> <i class="fa fa-long-arrow-left me-2"
                                    aria-hidden="true"></i> Go to home</button>
                        </div>
                    </div>
                </div>
                <div class="signup-wrapper card mt-2">
                    <div class="inner-login-wrapper pb-4">
                        @include('np_registration.form')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')

    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/script.js?ver=' . time()) }}"></script>
    <script type="text/javascript" src="{{ url('toastr/toastr.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/crypto-js.min.js?ver=' . time()) }}"
        integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    @include('components.admin.bootstrap-dialog')
    <script src="{{ asset('assets/ffo-admin/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/common.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/validations.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/mustache.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/verification.js?ver=' . time()) }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css?ver=' . time()) }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('toastr/toastr.min.css?ver=' . time()) }}">
    <script type="text/javascript" src="{{ asset('assets/js/jquery.bootstrap-duallistbox.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

  

    @include('np_registration.form_script')
</body>



</html>
