
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
		<link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon"/>
        <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon"/> 
        <title>Signup</title>
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" />
        <link href="{{asset('assets/css/styles.css')}}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver='.time())}}"> 
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
        <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver='.time())}}"> 
		 <?php Session::forget(['mobile', 'email']);Session::forget(['mobile_value', 'email_value']);?>
		 <style>
			#cover-spin {
				position:fixed;
				width:100%;
				left:0;right:0;top:0;bottom:0;
				background-color: rgba(255,255,255,0.7);
				z-index:9999;
			}
			
			@-webkit-keyframes spin {
				from {-webkit-transform:rotate(0deg);}
				to {-webkit-transform:rotate(360deg);}
			}
			
			@keyframes spin {
				from {transform:rotate(0deg);}
				to {transform:rotate(360deg);}
			}
			
			#cover-spin::after {
				content:'';
				display:block;
				position:absolute;
				left:48%;top:40%;
				width:40px;height:40px;
				border-style:solid;
				border-color:black;
				border-top-color:transparent;
				border-width: 4px;
				border-radius:50%;
				-webkit-animation: spin .8s linear infinite;
				animation: spin .8s linear infinite;
			}
            #formId .col-lg-4.mb-4{margin-bottom: 2.5rem !important;}
            .verify_icon{width:20px;}
            label.animated {
    position: relative;
    font-size: 15px;
    color: #7d2ae8;
    cursor: pointer;
    display: flex;
    align-items: center;
    
}

label.animated input {
    position: absolute;
    opacity: 0;
}

.input-check {
    position: relative;
    display: inline-block;
    top: 5px;
    width: 30px;
    height: 30px;
    border: 2px solid #ccc;
    border-radius: 4px;
    margin-right: 5px;
    transition: .5s;
    transform: scale(0.7);
}

label.animated input:checked~.input-check {
    background: #0f6492;
    border-color: #0f6492;
    animation: animate .7s ease;
    transform: scale(0.7);
}

@keyframes animate {
    0% {
        transform: scale(1);
    }

    40% {
        transform: scale(1.3, .7);
    }

    55% {
        transform: scale(1);
    }

    70% {
        transform: scale(1.2, .8);
    }

    80% {
        transform: scale(1);
    }

    90% {
        transform: scale(1.1, .9);
    }

    100% {
        transform: scale(1);
    }
}

.input-check::before {
    content: '';
    position: absolute;
    top: 9px;
    left: 6px;
    width: 15px;
    height: 6px;
    border-bottom: 3px solid #fff;
    border-left: 3px solid #fff;
    transform: scale(0) rotate(-45deg);
    transition: .5s;
}

label.animated input:checked~.input-check::before {
    transform: scale(1) rotate(-45deg);
}
		</style> 
    </head>
    <body id="mainbody" class="">
		<div id="cover-spin" style="display:none"></div>
        <div class="signup-wrapper-msme mt-0">
            <div class="container">
                        <div class="inner-login-wrapper mt-4 signup-form-msme card">
                               <div class="card-body pb-5 pt-3 px-4">
                                <div class="logo text-center"> <img src="{{asset('assets/img-new/msme-logo.png')}}"> </div>
                                    <div class="header mb-2 mt-3 text-center">
                                        <h6 class="decor-text">MSME TEAM Initiative</h6>
                                        <h2 class="card-title">MSME Registration</h2>
                                        <p>Proceed by filling your URN No. and Registered Mobile number.</p>
                                    </div>
                                    
                                   <!-- <hr/> -->
                                   <form method="post" id="formId" class="mt-2">
                                    <div class="row justify-content-center">
                                        <div class="col-lg-7">
										<div class="row">
                                            
                                            <div class="col-lg-6 mb-3">
                                            <div class="mb-0 floating-label-input">
                                                <input type="text" class="input-text udyamNumber" name="udyam_no" placeholder="Udyam Registration No.(URN))"  maxlength="80" id="udyam_no" value="">
                                                <label class="form-label required">Udyam Registration No.(URN))</label>
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="udyam_no_error"></span>
                                        </div>

                                        <div class="col-lg-6 mb-3">
                                            <div class="mb-0 floating-label-input">
                                                <input type="text" class="input-text integer" name="mobile" minlength="10" maxlength="10" placeholder="Mobile Number as in Udyam Registration" id="mobile" value="">
                                                <label class="form-label required">Mobile Number as in Udyam Registration</label>
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
                                        </div>

                                        <div class="col-lg-12 mb-3">
                                            <div class="mb-0 floating-label-input">
                                                <button type="button" id="udyamDetails" class="input-text btn btn-primary btn-themed">Validate </button>
                                            </div>
                                        </div>
                                        </div>
								</div>
                                        
										
										<div id="udyamDetailsContainer" class="px-5"></div>
                                    </div>
                                   </form>
                               </div>
                        </div>
            </div>
        </div>
        <!-- after validate udyam details will come here  -->
        <div class="udyam-details-wrapper">

        </div>           
        
		@include('components.admin.popup.sms-email')
		

		<script type="text/javascript">var BASE_URL = "{{url('/')}}"; </script>
		<script src="{{ asset('assets/js/jquery-3.7.0.min.js')}}"></script>
		<script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js')}}"></script>   
		<script src="{{ asset('assets/js/script.js')}}"></script>   
		<script type="text/javascript" src="{{ url('toastr/toastr.min.js')}}"></script>
		<script src="{{ asset('assets/js/crypto-js.min.js')}}" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>    
		<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
		<script src="{{ asset('assets/js/jquery-ui.js')}}"></script>
		<script src="{{ asset('assets/js/common.js')}}"></script>
		<script src="{{ asset('assets/js/validations.js')}}"></script>
        <script src="{{ asset('assets/js/verification.js')}}"></script>
		<link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet"> 
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

		<link rel="stylesheet" href="{{url('toastr/toastr.min.css')}}">
		<script>  
		
		if (window !== window.top) {  
		  document.getElementById('mainbody').innerHTML = "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
		  document.getElementById("mainbody").style.backgroundColor = "#ccc";  
		}
	   </script>
	   
	   
	   <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $('#udyamDetails').on('click', function () {
			var udyam_no=$("#udyam_no").val();
			var mobile=$("#mobile").val();
            if(udyam_no == '' || mobile == ''){
                toastr.error('Please enter Udyam Registration No. and Mobile Number');
                return false;
            }
          
			$("#cover-spin").show();
            $.ajax({
                url: '{{ route("udyam-details") }}',
                type: 'POST',
				data:{udyam_no:udyam_no,mobile:mobile},
                success: function (response) {
					console.log(response);
					if(response.status==false){
						$("#cover-spin").hide();
						toastr.error(response.msg);
						$('#udyamDetailsContainer').html('');
					}else{
						$("#cover-spin").hide();
						$('#udyamDetailsContainer').html(response);
					}
                },
                error: function () {
					$("#cover-spin").hide();
					toastr.error('Udyam api is not working.Please try after some times');
                }
            });
        });
    </script>
	   
		<script>
		
        $(document).on('keydown', '.form-control, .form-select', function() { 
            $(this).closest('div').siblings('span').html('');;
        });

        

		$("#formId").on("submit", function (event) {
			//$(".form-error font-12 font-12 font-12").html('');
			event.preventDefault();
            
		    var formData=$("#formId").serializeArray();
            var password=$("#otp").val();  
            formData = formData.filter(function(field) {
                return field.name !== 'otp';
            });

            if(password){
                formData.push({name: 'otp', value: cryptoJS(password).ciphertext});
            }


            formData.push({name: 'username', value: $("#mobile").val()});
            
            
            //console.log(formData);
			
            var method = 'POST';
            var url = "{{ url('/applicant') }}";
            $("#cover-spin").show();
			 $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function (res) { 						
                        if (res.status) {
                            $("#cover-spin").hide();
                            toastr.success('Your account has been created successfully.');
						    
							setTimeout(function(){
                                redirect("{{ url('login') }}")},
                                2000);
                            
                        }
						 else if (!res.status && res.errors.length == 0) {
							failure(res);
							$("#cover-spin").hide(); 
							$("#signup-button").attr("class", "btn btn-primary btn-themed mt-3");
							$("#signup-button").html('Submit Register');
							return;
						}

						else if (!res.status && res.errors) { 
							applyValidationErrors(res);
							$("#cover-spin").hide();    
							$("#signup-button").attr("class", "btn btn-primary btn-themed mt-3");
							$("#signup-button").html('Submit Register');
							return;
						}
						else {
							failure(res);
							enableSubmit();
						}
                    },
					
					error: function (xhr) {
						applyValidationErrors(xhr.responseJSON);
						$("#cover-spin").hide();    
						$("#signup-button").attr("class", "btn btn-primary btn-themed mt-3");
						$("#signup-button").html('Submit Register');
						return;
					}	
                    
                });
			
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

	
    </script>
        
    </body>
</html>
