@extends('components.front.auth-layout-v2')
@section('auth-form')
	<div class="d-flex align-items-center mb-4">
        <img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid" style="max-width: 250px;">

    </div>
	<div id="forgot-udyam-box">
		<h3>Forgot Udyam Registeration  Number We've  Got You !</h3>	
		<form id="formId" class="text-start" method="post">
			@csrf
			<div class="form-group mb-3 col-md-12 emailOption">
						<label class="required mb-1"> Enter Registered {{__('message.mobile') }} Number</label>
						<input type="text" class="form-control" name="mobile_number" id="mobile_number" maxlength="{{ config('constant.MOBILE_LENGTH') }}" placeholder="{{__('message.mobile')}}" required>
			</div>
			<div class="form-group mb-3 col-md-12 emailOption">
				<label class="required mb-1"> Enter Registered {{__('message.email')}}</label>
				<input type="email" class="form-control" name="email" id="email" maxlength="{{ config('constant.EMAIL_LENGTH') }}" placeholder="{{__('message.email')}}" required>
			</div>
			
			@if(config('settings.enable_captcha'))
			<div class="mb-3">
			<label class="form-label required">Enter Captcha Code</label>
			<div class="d-flex gap-3 captcha">
				<span class="captcha-img">{!! captcha_img() !!}</span>
				<a class="c-reload reload" id="reload">
				<i class="fa fa-refresh" aria-hidden="true"></i>
				<!--<img src="{{asset('assets/img-new/reload.svg')}}">--></a>
				<input class="form-control" name="captcha" id="captcha" placeholder="Enter captcha code">
			</div>
			</div>
			@endif

			<button type="submit" class="btn btn-primary-custom w-100 text-white" id="forgot-udyam-button">Proceed</button>
			<div class="mb-3 links">
				<a href="{{url('/')}}" class="float-end"><?php echo __('message.front_back');?></a>  
			</div>						
		</form>
	</div>
	


	<div id="verify-otp-box" style="display: none;">
		<div class="otp-screen">

			<div class="d-flex align-items-center mb-4">
				<img src="{{ asset('assets/img/msme-logo.png') }}" loading="lazy" class="img-fluid"
					style="max-width: 250px;">
			</div>

			<h3 id="otp-heading">Forgot Udyam Registration Number? We've Got You!</h3>

			<h4>Verify OTP</h4>

			<p>Enter the OTP received on the registered mobile number:
				<strong id="otp-mobile">XXXXXXXXXX</strong>
			</p>

			<!-- OTP input boxes -->
			<div class="otp-inputs">
				<input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
                <input type="text" maxlength="1" class="otp-box" inputmode="numeric">
			</div>

			<div class="resend-text">
				<p class="resend-message">
					You can resend OTP in
					<span id="otp_timer" class="otp-timer text-primary text-bold">02:00</span>
				</p>

				<a class="resend-otp m-3 pb-2" id="resend-otp" style="cursor:pointer; display:none;">
					Resend OTP
				</a>

				<input type="hidden" id="res_username">
				<input type="hidden" id="res_mobile">

				<button class="verify-btn" id="verifyOtpButton">Verify</button>
			</div>

		</div>
	</div>
@endsection
@section('js')
 <script> 

function cryptoJS(secret) {
	if (secret.length > 0) {
		var salt = CryptoJS.enc.Hex.parse("{{ $crypto_salt }}");
		var iv = CryptoJS.enc.Hex.parse("{{ $crypto_iv }}");
		var key = CryptoJS.PBKDF2(
			"{{ $crypto_key }}",
			salt, {
				hasher: CryptoJS.algo.SHA512,
				keySize: {{ $crypto_key_size }},
				iterations: {{ $crypto_iterations }}
			}
		);
		var encrypted = CryptoJS.AES.encrypt(secret, key, { iv: iv });
		return {
			ciphertext: CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
			salt: CryptoJS.enc.Hex.stringify(salt),
			iv: CryptoJS.enc.Hex.stringify(iv)
		};
	}
}

$('#reload').click(function() {
    $.ajax({
        type: "GET",
        url: "{{ url('refresh_captcha') }}",
        beforeSend: function() {
            $(".captcha-img").html("loading...");
        },
        success: function(data) {
            $(".captcha-img").html(data.captcha);
        }
    });
});

function authenticate({ username, mobile_number, captcha, route, submitElement }) {
    // let encryptedPassword = "";

    // if (password) {
    //     encryptedPassword = cryptoJS(password);
    //     $(passwordElement).val(encryptedPassword.ciphertext);
    // }

    $.ajax({
        url: route,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            username: username,
            mobile_number: mobile_number,
            captcha: captcha,
        },
        success: function(data) {
            if (data.status) {
                window.location.href = data.url;
            } else {
                $('#reload').click();
                $('#form')[0].reset();

                if (data.errors) {
                    $.each(data.errors, function(i, error) {
                        toastr.error(error);
                    });
                }
            }
        },
        error: function(xhr) {
            $('#reload').click();
            $('#form')[0].reset();

            toastr.error(xhr.responseJSON.message);
        }
    });
}

function sendOtp({ username, mobile_number, route, submitElement, captcha }) {

    $("#cover-spin").show();

    $.ajax({
        url: route,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            username: username,
			mobile_number: mobile_number,
            captcha: captcha
        },
        success: function(data) {

            $("#cover-spin").hide();

            if (data.status) {

                startTimer();
                timerInterval();
				$('#reload').click();
                $("#otp_div").show();
                $("#div_send_otp").hide();
                $("#div_verify_otp").show();

                toastr.success(data.message);

                $("#otp-mobile").text(maskMobileNumber(data.data.mobile));
                $("#res_username").val(data.data.username);
                $("#res_mobile").val(data.data.mobile);

                $("#forgot-udyam-box").hide();
                $("#verify-otp-box").show();

            } else {
                toastr.error(data.message);
            }
        },
        error: function(xhr) {
            $("#cover-spin").hide();
            // toastr.error(xhr.responseJSON.message);
			if (xhr.responseJSON && xhr.responseJSON.errors) {
				$.each(xhr.responseJSON.errors, function(key, messages) {
					toastr.error(messages[0]);
				});
			} else {
				toastr.error(xhr.responseJSON.message);
			}
        }
    });
}

function verifyOtp({username,otp,mobile_number,route,submitElement,}) {
	$("#cover-spin").show();
	let encryptedOtp = '';
	if (otp) {
		encryptedOtp = cryptoJS(otp);
	}
	$.ajax({
		url: route,
		type: 'POST',
		data: {
			"username": mobile_number,
			"mobile_number": mobile_number,
			"email": username,
			otp: encryptedOtp.ciphertext,
			"_token": $('meta[name="csrf-token"]').attr('content'),
		},
		success: function(data) {
			if (data.status) {
				$("#cover-spin").hide();
				$(submitElement).html('Verify OTP');
				$(submitElement).attr("class", "btn btn-primary-custom w-100 text-white");
				toastr.success(data.message);
				setTimeout(() => location.href = "{{ url('/login') }}", 500);

			} else {
				$(submitElement).html('Verify OTP');
				$(submitElement).attr("class", "btn btn-primary-custom w-100 text-white");
				toastr.error(data.message);
			}
			$("#cover-spin").hide();
		},
		error: function(xhr, status, error) {
			$("#cover-spin").hide();
			$(submitElement).html('Verify OTP');
			$(submitElement).attr("class", "btn btn-primary-custom w-100 text-white");
			toastr.error(xhr.responseJSON.message);
		}
	});
}

$("#forgot-udyam-button").on("click", function(e) {
    e.preventDefault();

    $("#forgot-udyam-button2").addClass("disabled").html("Processing...");

    sendOtp({
        username: $("#email").val(),
        mobile_number: $("#mobile_number").val(),
        captcha: $("#captcha").val(),
        route: "{{ url('forgot-udyam-send-otp') }}",
        submitElement: "#forgot-udyam-button",
    });
});

$("#verifyOtpButton").on("click", function(e) {
	e.preventDefault();
	$("#verifyOtpButton").attr("class", "btn btn-primary-custom w-100 text-white disabled");
	$("#verifyOtpButton").html("Processing...");

	const inputs = document.querySelectorAll('.otp-box');
	const otp = Array.from(inputs)
		.map(input => input.value)
		.join('');

	verifyOtp({
		username: $("#res_username").val(),
		mobile_number: $("#mobile_number").val(),
		otp: otp,
		route: "{{ url('forgot-udyam-verify-otp') }}",
		submitElement: "#verifyOtpButton",
	});
});

$("#resend-otp").click(function() {
	$.ajax({
		url: "{{ url('forgot-udyam-send-otp?resend=true') }}",
		type: 'POST',
		data: {
			"username": $("#res_username").val(),
			"mobile_number": $("#mobile_number").val(),
			"_token": $('meta[name="csrf-token"]').attr('content'),
		},
		success: function(data) {
			if (data.status) {
				startTimer();
				timerInterval();
				$("#cover-spin").hide();
				toastr.success(data.message);
				$("#otp-mobile").text(maskMobileNumber(data.data.mobile));
			} else {
				toastr.error(data.message);
			}
			$("#cover-spin").hide();
		},
		error: function(xhr, status, error) {
			$("#cover-spin").hide();
			toastr.error(xhr.responseJSON.message);
		}
	});
});
 </script>   
@endsection

