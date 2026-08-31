<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="timer_time" content="{{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}" />

    <title>{{ $title }}</title>


    <link href="{{ asset('assets/ffo-admin/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/datatables.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-duallistbox.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/custom.css')}}"> --}}
    <link rel="stylesheet" href="{{ asset('toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ripple.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/response.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/timepicker.css') }}">
    <link href="{{ asset('assets/ffo-admin/css/styles.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/ffo-admin/css/responsive.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/slick.css') }}" rel="stylesheet" />

    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/slick.min.js') }}"></script>

    @yield('styles')

    <style>
        div.dataTables_wrapper div.dataTables_length select.form-select {
            min-width: 130px !important;
        }

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
    </style>

    <style type="text/css">
        #loader {
            border: 12px solid #f3f3f3;
            border-radius: 50%;
            border-top: 12px solid #444444;
            width: 70px;
            height: 70px;
            animation: spin 1s linear infinite;

        }

        .center {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            z-index: 999;
        }

        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .permission-card {
            background: linear-gradient(45deg, #fc6c85, #FF9255);
            color: #fff;
        }

        .incentive-card {
            background: linear-gradient(45deg, #10ABEE, #1177AD);
            color: #fff;
        }

        .applications-tabs .card {
            width: calc(50% - 8px);
        }

        .applications-tabs .card h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .applications-tabs .card p {
            font-size: 14px;
        }

        .applications-tabs .card .card-body .status p {
            margin-bottom: 5px;
            font-size: 14px;
        }

        .incentive-card .tabs {}

        .incentive-card .tabs p {
            font-size: 12px;
            font-weight: 500;
            padding: 5px 7px;
            background: #ffffff42;
            border-radius: 4px;
            margin: 0;
        }

        .incentive-card .tabs p.active {
            background-color: #0FA5E9;
            color: #fff;
        }

        .permission-small-cards {
            gap: 12px;
            display: flex;
        }

        .permission-small-cards .card {
            border-left: 3px solid #0D87BE;
            display: inline-flex;
            gap: 10px;
            flex-direction: row;
            padding: 14px 8px;
            align-items: center;
            width: auto;
            border-radius: 9px;
        }

        .permission-small-cards h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 500;
        }

        .permission-small-cards img {
            max-width: 24px;
            width: 24px;
        }

        .permission-small-cards .card1 {
            background: linear-gradient(45deg, #10ABEE, #1177AD);
            color: #fff;
        }

        .permission-small-cards .card2 {
            background: linear-gradient(180deg, #44D3A0, #319E76);
            color: #fff;
        }

        .permission-small-cards .card3 {
            background: linear-gradient(139deg, #ED8000, #E2A811);
            color: #fff;
        }

        .permission-small-cards .card4 {
            background: linear-gradient(139deg, #0B96C1, #09B88E);
            color: #fff;
        }

        .permission-small-cards .card5 {
            background: linear-gradient(139deg, #07A358, #04D33E);
            color: #fff;
        }

        .applications-tabs .card .card-body P.status span,
        .applications-tabs .incentive-card .card-body P.status span,
        .applications-tabs .incentive-card .card-body .btn {
            padding: 4px 10px;
            background-color: #0000002e;
            border-radius: 16px;
            align-items: center;
            display: inline-flex;
            font-size: 12px;
            margin-top: 4px;
        }

        .applications-tabs .card .card-body P.status span::after {
            content: "";
            background: url("{{ asset('assets/img-new/check_f.svg') }}") no-repeat;
            width: 20px;
            height: 20px;
            background-position: 50% 50%;
            display: inline-block;
        }

        .applications-tabs .incentive-card .card-body P.status span::after {
            content: "";
            background: url("{{ asset('assets/img-new/cached.svg') }}") no-repeat;
            width: 20px;
            height: 20px;
            background-position: 50% 50%;
            display: inline-block;
        }

        .applications-tabs .incentive-card .card-body .dwld_btn::after {
            content: "";
            background: url("{{ asset('assets/img-new/download_fff.svg') }}") no-repeat;
            width: 20px;
            height: 20px;
            background-position: 50% 50%;
            display: inline-block;
        }

        .applications-tabs .incentive-card .card-body .renew-btn::after {
            content: "";
            background: url("{{ asset('assets/img-new/cached.svg') }}") no-repeat;
            width: 20px;
            height: 20px;
            background-position: 50% 50%;
            display: inline-block;
        }

        .applications-tabs .incentive-card .card-body .pay_milstone p,
        .applications-tabs .incentive-card .card-body .pay_milstone span {
            font-size: 11px;
            margin: 0;
        }

        .applications-tabs .incentive-card .card-body .pay_milstone a {
            font-size: 11px;
            text-decoration: none;
            color: #fff;
            padding: 2px 5px;
            background-color: #0000002e;
            border-radius: 16px;
            align-items: center;
            display: inline-flex;
            gap: 3px;
        }

        .applications-tabs .incentive-card .card-body .pay_milstone a::after {
            content: "";
            background: url("{{ asset('assets/img-new/rupee-2.svg') }}") no-repeat;
            width: 20px;
            height: 20px;
            background-position: 50% 50%;
            display: inline-block;
        }
    </style>

</head>

<body class="sb-nav-fixed" id="mainbody">
    <div id="cover-spin" style="display:none"></div>
