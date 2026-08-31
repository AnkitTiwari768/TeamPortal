@extends('components.front.layout')
@section('page-content')


<main class="login-form-page">
	<div class="container-fluid p-0">       
		<div class="row">

	<div class="col-lg-7 col-md-6 col-sm-12 pe-0">
		<div class="left-img"> </div>
	</div>
	<div class="col-lg-5 col-md-6 col-sm-12 ps-0">
		<div class="login-right-wrapper pt-5">
			<div class="logo-top d-flex justify-content-between">
		            <div><img src="{{ asset('assets/img/logo.png')}}"></div>
                	<div><img src="{{ asset('assets/img/g20img.png')}}"></div>
		    </div>

	<div class="card login-card">
		<div class="card-header py-3 d-flex flex-row justify-content-between flex-column">
			<h6>{{__('message.login_form')}} </h6>sdfsd
		</div>sd
		@if ($errors->any())
			<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
		@endif
		@if(Session::get('flash_error'))
		<div class="alert alert-danger">{{ Session::get('flash_error') }}</div>
		@endif
		@if (Session::get('status'))
			<div class="alert alert-success"><ul>
				<li> {{Session::get('status')}} </li>
			</ul></div>
		@endif
		<div class="card-body">
			<form id="formId" method="POST" action="{{ route('verify-login-otp') }}">
			@csrf
				<div class="mb-3"> 
					<div class="form-group col-md-12">									
						<input type="hidden" name="username" id="username" value="{{ session('username') }}">
					</div>
				</div>
				
				
				<div class="mb-3"> 
					<div class="form-group col-md-12">
						<label class="required">{{__('message.otp')}}</label>
						<input type="password" class="form-control" name="otp" id="otp" placeholder="{{__('message.otp_meta')}}" maxlength="6">
						<span class="text-danger form-error" id="otp_error"></span>
					</div>
				</div> 
				@if(config('settings.enable_captcha'))
				<div class="row mb-3">
					<div class="form-group col-md-4">
						<div class="captcha">{!! captcha_img() !!}</div>
					</div>
					<div class="form-group col-md-2"> 
						<button type="button" class="btn btn-danger" class="reload" id="reload">&#x21bb;</button>
					</div>
					<div class="form-group col-md-6"> 
							<input id="captcha" type="text" class="form-control" placeholder="Enter Captcha" name="captcha">
							<span class="text-danger form-error" id="captcha_error"></span>
					</div>
				</div>
				@endif
				<div>
					<span id="otp_timer"></span>
					<a href="javascript:void(0);" id="resend-otp" style="display: none;">Resend OTP</a>
				</div>
					<div class="mb-3">
					<button type="button" id="login-button" class="btn btn-success" id="login-button">{{__('message.verify_otp')}} <i class="fa fa-sign-in" aria-hidden="true"></i></button>  
				</div>	
				
				<div class="mb-3 links">
					<a href="{{url('signup')}}">{{__('message.signup')}}</a>
					<a href="{{url('forget-password')}}" class="float-end">{{__('message.forgot_password')}}</a>
				</div>								
			</form>
		</div>
	</div>

	
		</div>
	</div>
	</div>
	</div>
</main>
	
@section('js')
 <script>
 $("#login-button").on("click", function(e) {
		e.preventDefault();
		var username = $("#username").val();
		var password = $("#otp").val();
		if (username && password) {
			var crypto = cryptoJS(password);
			if (crypto && crypto.ciphertext) {
				$("#otp").val(crypto.ciphertext);
				//toastr.success("Login Successfully!");
				$("#formId").submit();
			}
		} else {
			toastr.error("Please enter the required fields");
			return;
		}
	});

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
			var encrypted = CryptoJS.AES.encrypt(secret, key, {iv: iv});
			var encryptedData = {
				ciphertext : CryptoJS.enc.Base64.stringify(encrypted.ciphertext),
				salt : CryptoJS.enc.Hex.stringify(salt),
				iv : CryptoJS.enc.Hex.stringify(iv)    
			};  
			return encryptedData;
		}
	}

	$('#reload').click(function () {
        $.ajax({
            type: 'GET',
            url: "{{ url('refresh_captcha')}}",
            beforeSend: function () {
            	$(".captcha").html('loading...');
            },
            success: function (data) {
                $(".captcha").html(data.captcha);
            }
        });
    });

	$("#resend-otp").click(function(e) {
		e.preventDefault();
		$.ajax({
			type: "POST",
			url: "{{ url('resend-otp-login') }}",
			data: {
				username: $("#username").val(),
				_token: "{{ csrf_token() }}",
			},
			success: function(response) {
				if (response.status) {
					location.reload();
				}
			}
		});
	});

	
	startTimer();

	let counter = 0;
	setInterval(function() {
		if (counter > {{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}) {
			$("#otp_timer").hide();
			$("#resend-otp").show();
		}
		counter++;
	},1000);
	
 </script>   
@endsection
@endsection
