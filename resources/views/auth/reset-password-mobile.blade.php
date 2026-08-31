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
                            <h6 class="m-0 font-weight-bold text-primary"> {{__('message.reset_password')}}</h6>
                        </div> 
                        @if ($errors->any())
                            <div class="alert alert-danger"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                        @endif
                        <div class="alert alert-danger errorMobile" style="display:none;">
							<li> <span id="error"></span></li>
						</div>
                        <div class="card-body">
                            <form id="formId" autocomplete="off"> 
                            @csrf 
                        
                                <div class="form-group mb-3 col-md-12">
                                    <label class="required mb-1"> Enter {{__('message.otp_meta')}}</label>
                                    <input type="text" class="form-control integer" id="userotp" name="userotp" required="required" maxlength="6" autocomplete="off" password" placeholder="{{__('message.otp_meta')}}"> 
                                    OTP will autometically expire after 2 minute:   <span id="otp_timer"></span>
                                </div>
                                <div>
					            <span id="otp_timer"></span>
                                    <a href="javascript:void(0);" id="resend-otp" style="display: none;">Resend OTP</a>
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
                                <div class="mb-3">
                                    <button type="button" id="reset-password-button" class="btn btn-success">{{__('message.verify_otp')}} <i class="fa fa-sign-in" aria-hidden="true"></i></button>  
                                    
                                    <a href="{{url('/')}}" class="btn btn-primary float-end"><?php echo __('message.front_back');?></a>  
                                    
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

    $("#reset-password-button").on("click", function(e) {
        e.preventDefault();
        var userotp = $("#userotp").val();
        var password = $("#password").val();
        var confirmpassword = $("#password_confirmation").val();
        if (userotp && password && confirmpassword) {
            var crypto = cryptoJS(password);
            var cryptoCP = cryptoJS(confirmpassword);
            if (crypto && crypto.ciphertext && cryptoCP && cryptoCP.ciphertext) {
                $("#password").val(crypto.ciphertext);
                $("#password_confirmation").val(cryptoCP.ciphertext);
                var cryptopassword = $("#password").val();
                var cryptoconfirmpassword = $("#password_confirmation").val();
                //alert($("#formId").attr('action'));
                //$("#formId").submit();
                $.ajax('verify_otp_forgotPassword', {
                    type: 'POST',  
                    data: { 
                        "_token": "{{ csrf_token() }}",
                        "userotp":  userotp, 
                        "password":cryptopassword,
                        "password_confirmation":cryptoconfirmpassword,
                        "captcha":  $('#captcha').val().trim(),
                        },  // data to submit
                        success: function (data, status, xhr) { 
                        if(data.status==false){
                            toastr.error(data.message);
                           // $('#error').html('Error: ' + data.message); 
                         $("#reload").trigger("click");
                        }
                        else {                      
                            $('#success').html(data.message);
                            toastr.success("Password changed Successfully.");
                            window.setTimeout(function() {
                                window.location.href = "{{ url('/') }}";
                            }, 2000);
                        }
                    },
                    error: function (response) {
                        console.log(response);
                        toastr.error(response.responseJSON.message);
                         //  $('#error').html('Error: ' + response.responseJSON.errors.mobile[0]);
                    }
                });
            }
        } else {
            toastr.error("Please enter the required fields");
            return false;
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

    $("#resend-otp").click(function(e) {
		e.preventDefault();
        
		$.ajax({
			type: "POST",
			url: "{{ url('resend-otp-forgotPassword') }}",
			data: {
				mobile: "{{ Session::get('mobile') }}",
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
