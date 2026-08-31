@extends('components.front.layout')
@section('page-content')	
<main class="login-form-page">
        <div class="container-fluid">
            <div class="row">

            	<div class="col-lg-6">
                    <div class="left-image-wrapper">
                       <img src="{{asset('assets/img-new/banner-login.jpg')}}" class="img-fluid">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="login-wrapper card">
                        <div class="inner-login-wrapper">
                           <div class="row">
                               <div class="card-header text-center py-4 border-0">
                                   <img src="{{asset('assets/img-new/logo-ffo-new.png')}}" class="logo">
                               </div>
                               <div class="card-text" >
                                   <h2>Verify OTP</h2>
                                   	@if ($errors->any())
										<div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
									@endif
									@if (Session::has('status'))
										<div class="alert alert-success"><ul>
											<li> {{Session::get('status')}} </li>
										</ul></div>
									@endif
                                   <form id="formId" method="POST" action="{{ route('verify-login-otp') }}">
								   @csrf
											<div class="mb-3"> 
												<div class="form-group col-md-12">									
												<input type="hidden" name="username" id="username" value="{{ session('username') }}">
												</div>
											</div>
                                           <div class="mb-3">
                                             <label class="form-label">Enter OTP</label>
                                             <input type="password" class="form-control" name="otp" id="otp" placeholder="{{__('message.otp_meta')}}" maxlength="6" >
                                             
                                           </div>
										  <span class="text-danger form-error" id="otp_error"></span>
                                          
                                           @if(config('settings.enable_captcha'))
										   <div class="mb-3">
                                            <label class="form-label">Enter Captcha Code</label>
                                            <div class="d-flex gap-3 captcha">
                                                <span class="captcha-img">{!! captcha_img() !!}</span>
                                                <a class="c-reload reload" id="reload">
												<i class="fa fa-refresh" aria-hidden="true"></i>
												<!--<img src="{{asset('assets/img-new/reload.svg')}}">--></a>
                                                <input class="form-control" name="captcha" id="captcha" placeholder="Enter captcha code">
                                            </div>
                                          </div>
										  @endif
											<div>
												<span id="otp_timer"></span>
												<a href="javascript:void(0);" id="resend-otp" style="display: none;">Resend OTP</a>
											</div>
                                           <div class="action-bottom d-flex mt-4">
                                                 <button type="submit" class="btn btn-primary"  id="login-button" >{{__('message.verify_otp')}}  </button>
                                           </div>
											
                                   </form>
                               </div>
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
            	$(".captcha-img").html('loading...');
            },
            success: function (data) {
                $(".captcha-img").html(data.captcha);
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


