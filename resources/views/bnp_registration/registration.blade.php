
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
        <title>BNP Registration</title>
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" />
        <link href="{{asset('assets/css/styles.css')}}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver='.time())}}"> 
        <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver='.time())}}">
		 <style>
			
</style> 
</head>
    <body id="mainbody" class="registration-page">
		<div id="cover-spin" style="display:none"></div>
        <div class="container-fluid">
            <div class="row"> 

				<div class="col-lg-3 p-0"> 
					<div class="left-img-wrap h-100 px-4 py-3">
					<div class="img-section-left sticky-top"> 
						<div class="card-header logo-box mb-5 mt-0">
                            <img src="{{asset('assets/img-new/mti-logo.svg')}}" class="logo">
                        </div>

						<div class="timeline">
							<ul class="unstyled p-0 m-0 ">
								<li class="d-flex gap-3 mb-3 done "> <div class="icon">1</div> Basic Details </li>
								<li class="d-flex gap-3 mb-3"> <div class="icon">2</div> Bank Details </li>
							</ul>
						</div>
						</div>
					</div>
				</div>

                <div class="col-lg-9 right-form-content">
					<div class="inner-header pb-3 pt-4 ">
						<div class="row">
							<div class="col-lg-6">
								<h2 class="fw-bolder fs-4">BNP Registration</h2>
							</div>
							<div class="col-lg-6 text-end">
								<button class="btn btn-stroked-theme"> <i class="fa fa-long-arrow-left me-2" aria-hidden="true"></i> Go to home</button>
							</div>
						</div>
					</div>
                    <div class="signup-wrapper card mt-2">
                        <div class="inner-login-wrapper pb-4">
                            <div class="row">                                
                               
                                <!-- <hr/> -->
                                <form method="post" id="formId" class="mt-2 pt-3">
									<div class="row">
										<div class="col-lg-12">
											<p>Fill all required<span style="color: #db0000ff;">*</span> fields and complete the registration process.</p>
										</div>
									</div>
                                    <div class="row basic-detail"> 
                                        <div class="col-lg-12 mb-3">
                                            <h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/bbasic.svg')}}" alt=""> Basic details</h2>                                      
                                        </div>

                                        <div class="col-lg-4 mb-3">
                                            <div class="mb-0">
												<label class="form-label required">Organization ID (as on ONDC)</label>
                                                <input type="text" class="input-text numeric" name="organization_id" maxlength="20" placeholder="Organization" id="organization_id"
												@if(isset($id) && !empty($row['organization_id'])) value="{{$row['organization_id']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organization_id_error"></span>
                                        </div>

                                        <div class="col-lg-4 mb-3">
                                            <div class="mb-0">
												<label class="form-label required">Organization Name</label>
                                                <input type="text" class="input-text txtnumerichypenapercend" name="organization_name" maxlength="100" id="organization_name" placeholder="Legal Name"
												@if(isset($id) && !empty($row['organization_name'])) value="{{$row['organization_name']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organization_name_error"></span>
                                        </div>

										
										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Name of Authorized Person</label>
                                                <input type="text" class="input-text txtOnly" name="authorized_person_name" maxlength="50" id="authorized_person_name" placeholder="Name of Authorized Person"
												@if(isset($id) && !empty($row['bnp_name'])) value="{{$row['bnp_name']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="authorized_person_name_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Email ID</label>
                                                <input type="text" class="input-text" name="email" maxlength="80" id="email" placeholder="Offical Email ID"
												@if(isset($id) && !empty($row['email'])) value="{{$row['email']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Mobile No</label>
                                                <input type="text" class="input-text numeric" name="mobile" maxlength="10" id="mobile" placeholder="Mobile" 
												@if(isset($id) && !empty($row['mobile'])) value="{{$row['mobile']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
                                        </div>


									</div>

								

							<div class="row bank-detail" >

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/bank.svg')}}" alt=""> Bank details </h2>                                      
										</div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Bank Name</label>
                                                <input type="text" class="input-text alphaNumericSpace" name="bank_name" maxlength="80" id="bank_name" placeholder="Bank Name" 
												@if(isset($id) && !empty($row['bank_name'])) value="{{$row['bank_name']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="bank_name_error"></span>
                                        </div>
										
										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">IFSC CODE</label>
                                                <input type="text" class="input-text alphaNumericSpace" name="ifsc_code" maxlength="20" id="ifsc_code" placeholder="IFSC CODE" 
												@if(isset($id) && !empty($row['ifsc_code'])) value="{{$row['ifsc_code']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ifsc_code_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">A/C No</label>
                                                <input type="text" class="input-text numeric" name="account_no" maxlength="30" id="account_no" placeholder="A/C No" 
												@if(isset($id) && !empty($row['account_no'])) value="{{$row['account_no']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="account_no_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">PAN</label>
                                                <input type="text" class="input-text alphaNumericSpace" name="pan" maxlength="10" id="pan" placeholder="PAN" 
												@if(isset($id) && !empty($row['pan'])) value="{{$row['pan']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">GST Number</label>
                                                <input type="text" class="input-text alphaNumericSpace" name="gst_number" maxlength="20" id="gst_number" placeholder="GST Number" 
												@if(isset($id) && !empty($row['gst_number'])) value="{{$row['gst_number']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gst_number_error"></span>
                                        </div>

										
										

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/check.svg')}}" alt=""> Declaration </h2>                                      
										</div>

										<div class="col-lg-12 mb-3">
                                            <div class="d-flex flex-column">
                                            <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="agreecheck" id="agreecheck" required>
                                            <label class="form-label label-2 ms-2">I/ We hereby declare that the details furnished above are true and correct to the best of my/ our knowledge and belief. If the above information is found to be incorrect / misleading/ false, appropriate action as per the laws may be taken against me/ us. I/ We hereby authorize NSIC/ ONDC to share the relevant details for the purpose of scheme administration and support, in compliance with applicable laws. I / We hereby declare that I / We have read the Privacy Policy, Terms and Conditions, Disclaimer and Data Sharing Policy, Operating guidelines of TEAM Initiative, SOPs of TEAM Initiative and abide by it. NSIC reserves the right to change/ amend the SOPs with the approval of the Ministry of MSME as per the policy & procedural requirement as and when warranted and without giving any notice. </label>
                                            </div>
                                            </div>
											<span class="text-danger form-error" id="agreecheck_error"></span>
                                        </div>  

										<div class="col-lg-7">                     
											<div class="action-bottom align-items-start d-flex flex-column">
											<button id="signup-button" type="submit" class="btn btn-primary btn-themed mt-3">Signup</button>
											</div>
										</div>

							</div>

                                    
                                </form>
                                   
                            </div>
                        </div>
                              
                    </div>
                </div>
            </div>
        </div>

		@include('components.admin.popup.sms-email')
		@include('components.admin.file-upload')
		


		<script type="text/javascript">var BASE_URL = "{{url('/')}}"; </script>
		<script src="{{ asset('assets/js/jquery-3.7.0.min.js?ver='.time())}}"></script>
		<script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js?ver='.time())}}"></script>   
		<script src="{{ asset('assets/js/script.js?ver='.time())}}"></script>   
		<script type="text/javascript" src="{{ url('toastr/toastr.min.js?ver='.time())}}"></script>
  
		
		@include('components.admin.bootstrap-dialog')

		<script src="{{ asset('assets/ffo-admin/js/bootstrap.min.js')}}"></script> 
		<script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
		<script src="{{ asset('assets/js/jquery-ui.js?ver='.time())}}"></script>
		<script src="{{ asset('assets/js/common.js?ver='.time())}}"></script>
		<script src="{{ asset('assets/js/validations.js?ver='.time())}}"></script>
        <script src="{{ asset('assets/js/verification.js?ver='.time())}}"></script>
		<link href="{{ asset('assets/css/bootstrap.min.css?ver='.time())}}" rel="stylesheet"> 
		<link rel="stylesheet" href="{{url('toastr/toastr.min.css?ver='.time())}}">
		<script type="text/javascript" src="{{ asset('assets/js/jquery.bootstrap-duallistbox.min.js')}}"></script> 
		<script>  
		
			if (window !== window.top) {  
			document.getElementById('mainbody').innerHTML = "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
			document.getElementById("mainbody").style.backgroundColor = "#ccc";  
			}
	    </script>
	   <script>

		$(document).ready(function() {

			$(document).on('focus input change', 'input, textarea, select', function () {
				let fieldId = $(this).attr('id');
				if (fieldId) {
					$("#" + fieldId + "_error").text(""); // Clear related error span
				}
			});
		});

		$("#agreecheck").on("change", function () {
			if ($(this).is(":checked")) {
				$(this).val("1");
			} else {
				// To make sure an unchecked checkbox still submits, add a hidden field
				$(this).val("");
			}
		});

		 $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

		$("#formId").on("submit", function (event) {
			event.preventDefault();
			var formData=$("#formId").serializeArray();
		
			var method = 'POST';
			@isset($id)
				var url = "{{ url('/update-bnp-registration/'. $id) }}";	
			@else 
				var url = "{{ url('/create-bnp-registration') }}";
			@endisset
			
			$("#cover-spin").show();
			$.ajax({
				url: url,
				type: method,
				data: formData,
				success: function (data) { 
					if (data.status) {
						$("#cover-spin").hide();
						toastr.success('Your account has been created successfully.');
						
						setTimeout(function(){
							redirect("{{ url('login') }}")},
							2000);
						
					}
					else if (!data.status && data.errors.length == 0) {
						failure(data);
						$("#cover-spin").hide(); 
						$("#signup-button").attr("class", "btn btn-primary btn-themed mt-3");
						$("#signup-button").html('Signup');
						return;
					}

					else if (!data.status && data.errors) { 
						applyValidationErrors(data);
						$("#cover-spin").hide();    
						$("#signup-button").attr("class", "btn btn-primary btn-themed mt-3");
						$("#signup-button").html('Signup');
						return;
					}
					else {
						failure(data);
						enableSubmit();
					}
				},
					
			});
		
		});		
	
	</script>
        
    </body>
</html>
