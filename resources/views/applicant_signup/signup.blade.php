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
    <title>Signup</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver=' . time()) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver=' . time()) }}">
    <?php Session::forget(['mobile', 'email']);
    Session::forget(['mobile_value', 'email_value']); ?>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        h3.page-head {}

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
            background-image: url(assets/img-new/login-background.png);
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
            font-size: 12px;
            font-weight: lighter;
            padding: 0;
            text-align: center;
            padding-bottom: 0px;
            margin-bottom: 9px !important;
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
            min-height: 525px;
            padding: 8px 10px 0px 14px;
        }

        .login-page .login-form-page {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            margin: 0 auto;
            width: 75%;
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
            background: url(assets/img-new/image-login.png) repeat;
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

        .otp-screen h3,
        .right-side h3,
        h3.page-head {
            font-size: 22px !important;
            font-weight: 600;
            color: #1e2a78;
        }

        .login-form-page .form-control,
        .login-form-page .form-select {
            background-color: #ffffff;
            border: 1px solid #cdcdcd !important;
            padding: 10px;
            border-radius: 4px;
            font-size: 14px;
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }


        .btn.form-custom-button {
            background-color: #1e2a78;
            border: none;
            padding: 12px;
            font-weight: 600;
        }

        .btn.form-custom-button:hover {
            background-color: #131e62;
            color: #ffff
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

        .small a {
            font-size: 12px;
            color: #1e2a78;
        }

        .small a:hover {
            text-decoration: underline;
        }

        .link-container-bottom {
            border-top: 1px solid rgba(42, 101, 128, 0.3);
            padding-top: 10px;
        }

        .login-page .login-form-page {
            padding: 11px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice{
            background: linear-gradient(135deg, #F3F9FD);
            border: 1px solid #F3F9FD;
            border-radius: 999px; 
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            margin: 6px 6px 0 0;
            padding: 6px 14px 6px 28px;
            position: relative;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            transition: all 0.25s ease;
        }
        .select2-selection__choice__remove {
            border: none !important;
            background: transparent !important;
            padding: 0 5px !important;
            margin: 0 !important;
            top:5px !important;
            left:10px !important;
        }
    </style>
</head>

<body class="new_login_page login-page">
    <div id="cover-spin" style="display:none"></div>

    <div class="container-fluid">
        <div class="row justify-content-center">

            <div class="card login-card login-form-page">
                <div class="row g-0 left-side-img">

                    <div class="col-md-6 left-side d-none d-md-flex ">
                        <div class="login_img_bg_2"></div>
                        <div class="info-glass">
                            <h4>Welcome to MSME TEAM Portal</h4>
                            <p>Please click on the below link to register your business on Team Portal:</p>
                            <a href="{{ url('registration') }}" class="text-white d-block mb-3 text-break">Express your
                                interest in TEAM Portal</a>
                            <p class="mb-0">If you wish to seek help from helpdesk, Please click here,
                                <strong><strong><a href="{{ config('url.contact_us_url')}}" target="_blank" class="text-white fs-7">Helpdesk</a></strong></strong>
                            </p>
                            <!--<p>Please click on the below link to register your business on Udyami Bharat Portal:</p>
                            <a href="https://udyamregistration.gov.in/Government-India/Ministry-MSME-registration.htm" class="text-white d-block mb-3 text-break" target="_blank">https://udyamregistration.gov.in/Government-India/Ministry-MSME-registration.htm</a>
                            <p class="mb-0">If you wish to seek help from helpdesk, Please click here,
                                <strong>abc.com</strong>
                            </p>-->
                        </div>
                    </div>

                    <div class="col-md-6 right-side ">


                        <div class="d-flex align-items-center mb-4">
                            <img src="{{ asset('assets/img-new/msme-logo.png') }}" loading="lazy" class="img-fluid"
                                style="max-width: 250px;">

                        </div>

                        <h3 class="fw-bold fs-5  mb-3 page-head">MSME Registration</h3>



                        <div class="tab-content" id="loginTabContent">



                            <div class="mb-3">
                                <label class="form-label  ">Enter UDYAM Registration Number<span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control udyamNumber" name="udyam_no"
                                    placeholder="UDYAM-XX-00-0000000" maxlength="19" id="udyam_no"
                                    value="">
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="udyam_no_error"></span>
                            </div>



                            <div class="mb-3">
                                <label class="form-label  ">Enter Mobile No.<span class="text-danger">*</span></label>
                                <input type="text" class="form-control integer" name="mobile" minlength="10"
                                    maxlength="10" placeholder="Enter Mobile No." id="mobile" value="">
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="mobile_error"></span>
                            </div>

                            <div class="mb-4">
                                <label class="form-label  ">Enter Captcha Code <span
                                        class="text-danger">*</span></label>

                                <div class="d-flex align-items-center gap-2 captcha">
                                    <span class="captcha-img">{!! captcha_img() !!}</span>
                                    <a class="c-reload reload" id="reload"><i class="fa fa-refresh"
                                            aria-hidden="true"></i></a>
                                    <input class="form-control" name="captcha" id="captcha"
                                        placeholder="Enter captcha code">
                                </div>

                            </div>

                            <button type="button" id="udyamDetails"
                                class="btn form-custom-button w-100 text-white">Validate</button>

                            <div
                                class="d-flex justify-content-between align-items-center mt-3 small link-container-bottom">
                                <a href="{{ url('forgot-udyam-number') }}" class="text-decoration-none fw-medium">
                                    Forget <span class="text-primary">Udyam Registration Number?</span>
                                </a>
                                <a href="{{ url('login') }}" class="text-decoration-none fw-medium">
                                    Already have an account? <span class="text-primary">Sign In</span>
                                </a>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="udyamDetailsContainer"></div>



    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script type="text/javascript" src="{{ url('toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/crypto-js.min.js') }}"
        integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/common.js') }}"></script>
    <script src="{{ asset('assets/js/validations.js') }}"></script>
    <script src="{{ asset('assets/js/verification.js') }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="{{ url('toastr/toastr.min.css') }}">
    <script>
        if (window !== window.top) {
            document.getElementById('mainbody').innerHTML =
                "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
            document.getElementById("mainbody").style.backgroundColor = "#ccc";
        }
    </script>


    <script>
        

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        $('#udyamDetails').on('click', function() {
            var udyam_no = $("#udyam_no").val();
            var mobile = $("#mobile").val();
            var captcha = $("#captcha").val();



            if (udyam_no == '') {
                toastr.error('Please Enter Udyam Registration No.');
                return false;
            }

            if (mobile == '') {
                toastr.error('Please Enter Mobile Number');
                return false;
            }

            if (captcha == '') {
                toastr.error('Please Enter Captcha');
                return false;
            }

            $("#cover-spin").show();


            $.ajax({
                url: "{{ url('udyam-details') }}",
                type: 'POST',
                data: {
                    udyam_no: udyam_no,
                    mobile: mobile,
                    captcha: captcha
                },
                success: function(response) {
                    if (response.status == false) {
                        //alert();
                        //reloadCaptch();
                        $("#captcha").val('');
                        $("#cover-spin").hide();
                        toastr.error(response.msg);
                        $('#udyamDetailsContainer').html('');
                        setTimeout(function() {
                            window.location.reload();
                        }, 2000);
                    } else {
                        //reloadCaptch();
                        $("#captcha").val('');
                        $("#cover-spin").hide();
                        $('#udyamDetailsContainer').html(response);
                    }
                },
                error: function(xhr) {
                    //reloadCaptch();
                    console.log(xhr);
                    $("#captcha").val('');
                    $("#cover-spin").hide();
                    toastr.error('Udyam api is not working.Please try after some times');

                    // setTimeout(function() {
                    //     window.location.reload();
                    // }, 2000);
                }
            });
        });
    </script>

    <script>
        $(document).on('keydown', '.form-control, .form-select', function() {
            $(this).closest('div').siblings('span').html('');;
        });





        $('#reload').click(function() {

            reloadCaptch();
            /*$.ajax({
                type: 'GET',
                url: "{{ url('refresh_captcha') }}",
                beforeSend: function () {
                    $(".captcha-img").html('loading...');
                },
                success: function (data) {
                    $(".captcha-img").html(data.captcha);
                }
            });*/
        });

        function reloadCaptch() {
            $.ajax({
                type: 'GET',
                url: "{{ url('refresh_captcha') }}",
                beforeSend: function() {
                    $(".captcha-img").html('loading...');
                },
                success: function(data) {
                    $(".captcha-img").html(data.captcha);
                }
            });

        }
    </script>
    <div class="modal fade" id="commercialModelModal" tabindex="-1">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">

				<div class="modal-header bg-light">
					<h5 class="modal-title fw-semibold">Commercial Model</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>

				<div class="modal-body">
					<div class="table-responsive">
						<table class="table table-bordered table-striped">
							
							<tbody id="commercialModelBody">
							</tbody>
						</table>
					</div>
				</div>

				<div class="modal-footer bg-light">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						Close
					</button>
				</div>

			</div>
		</div>
	</div>
	<script>
		$(document).on('click', '.commercialModelBtn', function () {

			var model = $(this).data('model');
			var html = '';

			for (var key in model) {

				var label = key.replaceAll('_', ' ');
				label = label.charAt(0).toUpperCase() + label.slice(1);

				var value = model[key];

				if (value !== null && value !== '') {
					value = value.toString().replaceAll('_', ' ');
					value = value.charAt(0).toUpperCase() + value.slice(1);
				} else {
					value = '-';
				}

				html += `
					<tr>
						<td>${label}</td>
						<td>${value}</td>
					</tr>
				`;
			}

			$('#commercialModelBody').html(html);
			$('#commercialModelModal').modal('show');
		});
	</script>
</body>

</html>
