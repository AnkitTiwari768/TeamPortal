@extends('components.front.auth-layout')
@section('auth-form')


						
						
	<div id="login-form-container" >
		<h2>Reset Password</h2>	 
		<form id="formId" method="POST" class="text-start" action="{{ route('reset-password') }}">
		@csrf
			<input type="hidden" name="token" id="token" value="{{ $token }}">
			
			<div class="mb-3"> 
				<div class="form-group col-md-12">
					<label class="required">{{__('message.email')}}</label>
					<input type="text" class="form-control" name="email" id="email" placeholder="{{__('message.email')}}">
					<span class="text-danger form-error" id="email_error"></span>
				</div>
			</div>				
			
			<div class="mb-3"> 
				<div class="form-group col-md-12">
					<label class="required">{{__('message.password')}}</label>
					<input type="password" class="form-control" name="password" id="password" placeholder="{{__('message.password')}}">
					<span class="text-danger form-error" id="password_error"></span>
				</div>
			</div>
				
			<div class="mb-3"> 
				<div class="form-group col-md-12">
					<label class="required">{{__('message.confirm_password')}}</label>
					<input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="{{__('message.confirm_password')}}">
					<span class="text-danger form-error" id="password_confirmation_error"></span>
				</div>
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
		
			<div class="mb-3">
				<button type="button" id="reset-password-button" class="btn btn-primary">{{__('message.reset_password')}} <i class="fa fa-sign-in" aria-hidden="true"></i></button>
			</div>						
		</form>
	</div>


@section('js')
 <script>
	$("#reset-password-button").on("click", function(e) {
		e.preventDefault();
		var email = $("#email").val();
		var token = $("#token").val();
		var password = $("#password").val();
		var confirmpassword = $("#password_confirmation").val();
		if (email && password && confirmpassword) {
			var crypto = cryptoJS($("#password").val());
			var cryptoCP = cryptoJS($("#password_confirmation").val());
			
			if (crypto && crypto.ciphertext && cryptoCP && cryptoCP.ciphertext) {
				$("#password").val(crypto.ciphertext);
				$("#password_confirmation").val(cryptoCP.ciphertext);
				var password = $("#password").val();
				var confirmpassword = $("#password_confirmation").val();
			}
		}
		if (email && password && confirmpassword) {
				//$("#formId").submit();
				$.ajax({
					url: "{{ url('reset-password') }}",
					type: 'POST',
					data: { 
						"_token": $('meta[name="csrf-token"]').attr('content'),
						"token": token,
						"email": email, 
						"password": password,
						"confirmpassword": confirmpassword, 
						"captcha": $("#captcha").val(),
					},
					success: function (data) { 
						console.log(data);
						if(data.status){ 
							toastr.success(data.message);
							var redirectTo = "{{ url('/login') }}";
							redirect(redirectTo)
						}else{
							$('#reload').click();
							$('#captcha').val('');
							$('#password').val('');
							$('#password_confirmation').val('');
							$('#otp').val('');
							errors=data.errors; 
							if (data.message!='') { 
								$.each(errors, function (index, error) {  
									toastr.error(error); 
								});
							}
							else{
								toastr.error(data.errors);
							}
						}
					},
					error: function(xhr, status, error) { 
						$('#reload').click();
						$('#captcha').val('');
						$('#email_username').val('');
						$('#password').val('');
						$("#login-button").removeClass("disabled").addClass("btn-primary");
						$("#login-button").html('<i class="fa fa-paper-plane-o" aria-hidden="true"></i> Login');
						
						toastr.error(xhr.responseJSON.message);
					}
				});
			
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
 </script>   
@endsection
@endsection
