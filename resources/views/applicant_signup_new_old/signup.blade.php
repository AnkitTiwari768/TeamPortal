
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
		<meta name="csrf-token" content="{{ csrf_token() }}" />
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
							<!--<h6 class="decor-text">MSE TEAM Initiative</h6> -->
							<h2 class="card-title">MSE Registration</h2>
						</div>
						
						<!-- <hr/> -->
						<form method="post" id="formId" class="mt-2">
							<div class="row">
			
								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label required">Name of entrepreneur</label>
										<input type="text" class="input-text txtOnly" name="entrepreneur_name" placeholder="Name of entrepreneur"  maxlength="100" id="entrepreneur_name" required>
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="entrepreneur_name_error"></span>
								</div>

								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label required">Name of enterprise </label>
										<input type="text" class="input-text alphaNumericSpace" name="enterprise_name" maxlength="100" placeholder="Name of enterprise" id="enterprise_name" required>
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="enterprise_name_error"></span>
								</div>
	
								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label">Udyam Number </label>
										<input type="text" class="input-text udyamNumber" name="udyam_no" maxlength="19" placeholder="Enter udyam number" id="udyam_no">
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="udyam_no_error"></span>
								</div>
	
								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label required">Mobile </label>
										<input type="text" class="input-text numeric" name="mobile" maxlength="10" placeholder="Enter Mobile" id="mobile" required>
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
								</div>

								
								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label required">Email </label>
										<input type="email" class="input-text" name="email" maxlength="100" placeholder="Email" id="email" required>
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
								</div>

								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label for="product_category_id" class="form-label required">Category of Business </label>
										{!! Form::select('product_category_id[]',remove_select_dynamic_common_list($lists?->sub_domains),'', ['class' => 'form-select select2', 'id' => 'product_category_id','multiple' => 'multiple','required'=>'required']) !!}
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="product_category_id_error"></span>
								</div>

								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label class="form-label required">Product Details </label>
										<input type="text" class="input-text txtnumericMix" name="product_details" maxlength="200" placeholder="Atta, Shirts, Earphones, etc." id="product_details" required>
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="product_details_error"></span>
								</div>
								<div class="col-lg-4 mb-3">
									<div class="mb-0">
									<label class="form-label required">State</label>
									{{ Form::select('state_id', ['' => 'Select State'] + remove_select_dynamic_common_list($lists?->states), '', ['class' => 'form-select', 'id' => 'state_ids','required'=>'required']) }}
										
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="state_id_error"></span>
								</div>

								<div class="col-lg-4 mb-3">
									<div class="mb-0">
										<label for="ondc_transaction_type_id" class="form-label required" >Type of transaction? (B2B/B2C)</label>
										{!! Form::select('ondc_transaction_type_id', array(''=>'Select')+static_common_list($lists?->ondc_types),'', ['class' => 'form-select select_value', 'id' => 'ondc_transaction_type_id','required'=>'required']) !!}
									</div>
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ondc_transaction_type_id_error"></span>
								</div>
								
								<div class="col-lg-4 mb-3">
									<div class="mb-0 floating-label-input">
										{!! Form::select('select_snp', array(''=>'Select SNP')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'select_snp','required'=>'required']) !!}
										
										<label for="select_snp" class="form-label required" style="display:none">Do you wish to select an SNP?</label>
									</div>
									
									<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="select_snp_error"></span>
								</div>
								
								<div id="sndDetailsContainer"></div>
								
								<div class="col-lg-12 mb-3">
									<div class="d-flex align-items-start">
										<input type="checkbox" class="mt-1 me-2" checked="checked" onclick="return false;">
										<label for="agree" class="form-check-label">
										I confirm that the information provided by me is my own and I consent to be registered under the MSME TEAM Initiative Scheme.
										</label>
									</div>
									<span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
								</div>

								<div class="col-lg-12 mb-3">
									<button id="signup-button" type="submit" class="btn btn-primary mt-3">Register </button>
								</div>
								

							</div>
  
                     	</form>
					</div>
				</div>
        	</div>
		</div>
		

		<script type="text/javascript">var BASE_URL = "{{url('/')}}"; </script>
		<script src="{{ asset('assets/js/jquery-3.7.0.min.js')}}"></script>
		<script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js')}}"></script>   
		<script src="{{ asset('assets/js/script.js')}}"></script>   
		<script type="text/javascript" src="{{ url('toastr/toastr.min.js')}}"></script>
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
    
    </script>
	   
		<script>
		
			 $(document).on('keydown', '.form-control, .form-select', function() { 
				$(this).closest('div').siblings('span').html('');;
			});

			$(document).ready(function() {
				$('#product_category_id').select2({
					placeholder: "Select Category",
					allowClear: true
				});

			});
			
			$('#select_snp').on('change', function () {
				var sub_domain=$("#product_category_id").val();
				var select_snp=$("#select_snp").val();
				var state_id=$("#state_ids").val();
				var ondc_transaction_type_id=$("#ondc_transaction_type_id").val();
				if(select_snp==1){
					$.ajax({
						url: '{{ route("snp-select-details") }}',
						type: 'GET',
						data:{state_id:state_id,ondc_transaction_type_id:ondc_transaction_type_id,sub_domain:sub_domain},
						success: function (response) {
							$("#cover-spin").hide();
							if(select_snp==1){
								$('#sndDetailsContainer').html(response).show();
							}else{
								$('#sndDetailsContainer').html("").hide();
							}
						},
						error: function () {
							$("#cover-spin").hide();
							alert('Failed to load content.');
						}
					});
				}else{
					
					$('#sndDetailsContainer').html("");
					
				}
			});

		
       

        

		$("#formId").on("submit", function (event) {
			event.preventDefault();
            
		    var formData=$("#formId").serializeArray();
	
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
                            toastr.success('Your account has been created successfully. Now relevant SNP will connect with you for furthur process.');
						    
							setTimeout(function(){
                                redirect("{{ url('signup') }}")},
                                2000);
                            
                        }
						 else if (!res.status && res.errors.length == 0) {
							failure(res);
							$("#cover-spin").hide(); 
							$("#signup-button").attr("class", "btn btn-primary mt-3");
							$("#signup-button").html('Register');
							return;
						}

						else if (!res.status && res.errors) { 
							applyValidationErrors(res);
							$("#cover-spin").hide();    
							$("#signup-button").attr("class", "btn btn-primary mt-3");
							$("#signup-button").html('Register');
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
						$("#signup-button").attr("class", "btn btn-primary mt-3");
						$("#signup-button").html('Register');
						return;
					}	
                    
                });
			
        });

    </script>
        
    </body>
</html>
