
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
        <title>SNP Registration</title>
        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" />
        <link href="{{asset('assets/css/styles.css')}}" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver='.time())}}"> 
        <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver='.time())}}">
		<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
		 <?php Session::forget(['mobile', 'email']);Session::forget(['mobile_value', 'email_value']);?>
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
								<li class="d-flex gap-3 mb-3"> <div class="icon">2</div> Configuration Details </li>
								<li class="d-flex gap-3 mb-3"> <div class="icon">3</div> Bank Details </li>
								<li class="d-flex gap-3 mb-3"> <div class="icon">4</div> Commercial Details </li>
							</ul>
						</div>
						</div>
					</div>
				</div>

                <div class="col-lg-9 right-form-content">
					<div class="inner-header pb-3 pt-4 ">
						<div class="row">
							<div class="col-lg-6">
								<h2 class="fw-bolder fs-4">SNP Registration</h2>
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
												<label class="form-label required">Organization/Legal Name</label>
                                                <input type="text" class="input-text txtnumerichypenapercend" name="organization_name" maxlength="100" id="organization_name" placeholder="Legal Name"
												@if(isset($id) && !empty($row['organization_name'])) value="{{$row['organization_name']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organization_name_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">App Name / Brand name </label>
                                                <input type="text" class="input-text txtnumerichypenapercend" name="brand_name" maxlength="100" id="brand_name" placeholder="App Name / Brand name"
												@if(isset($id) && !empty($row['brand_name'])) value="{{$row['brand_name']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="brand_name_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Name of Authorized Person</label>
                                                <input type="text" class="input-text txtOnly" name="authorized_person_name" maxlength="50" id="authorized_person_name" placeholder="Name of Authorized Person"
												@if(isset($id) && !empty($row['snp_name'])) value="{{$row['snp_name']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="authorized_person_name_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Designation</label>
                                                <input type="text" class="input-text txtOnly" name="designation" maxlength="50" id="designation" placeholder="Designation" 
												@if(isset($id) && !empty($row['designation'])) value="{{$row['designation']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="designation_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Offical Email ID</label>
                                                <input type="text" class="input-text" name="email" maxlength="80" id="email" placeholder="Offical Email ID"
												@if(isset($id) && !empty($row['email'])) value="{{$row['email']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label">Alternate Email ID</label>
                                                <input type="text" class="input-text" name="alternate_email" maxlength="80" id="alternate_email" placeholder="Alternate Email ID"
												@if(isset($id) && !empty($row['alternate_email'])) value="{{$row['alternate_email']}}" @endif >
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="alternate_email_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Mobile No</label>
                                                <input type="text" class="input-text numeric" name="mobile" maxlength="10" id="mobile" placeholder="Mobile" 
												@if(isset($id) && !empty($row['mobile'])) value="{{$row['mobile']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label">Contact No (Land line)</label>
                                                <input type="text" class="input-text numeric" name="contanct_no" maxlength="15" id="contanct_no" placeholder="Contact No (Land line)"
												@if(isset($id) && !empty($row['alternate_mobile'])) value="{{$row['alternate_mobile']}}" @endif >
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contanct_no_error"></span>
                                        </div>

										<div class="col-lg-12 mb-3">
                                        
											<div class="input-box">
												<label class="form-label required">Authorized Certificate</label>
												
												<input type="file" class="form-control" id="authorized_certificate" accept="application/pdf"/>

												<small>1. Image size should be less than 5mb.<br/>
													2. Only files with extension jpg, jpeg, png ,pdf are allowed</small>
												<div class="authorized_certificate_file_link">
													
												</div>
												<span class="text-danger form-error" id="authorized_certificate_document_error"></span>
												<input type="hidden" name="authorized_certificate_document" id="authorized_certificate_document" 
												@if(isset($id) && !empty($row['authorized_certificate_document'])) value="{{ $row['commercial_model_document']}}" @endif /> 
												<div class="progress-authorized_certificate progress-bg" style="display:none;">
													<div id="loader-authorized_certificate" style=""></div>
													<div class="progress-bar"></div>
												</div>
												@if(isset($id) && !empty($row['authorized_certificate_document']))
												<a href="{{ url('storage/app/uploads/authorized_certificate/'.$row['authorized_certificate_document']) }}" download="{{ $row['authorized_certificate_document_original_name'] }}" >
													{{$row['authorized_certificate_document_original_name']}}
												</a>
												@endif 
											</div>
										</div>
									</div>

								<div class="row config-detail" >
										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/config.svg')}}" alt=""> Configuration details </h2>                                      
										</div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
											<label class="form-label required">Domain</label>
											{!! Form::select('domain[]', static_common_list($lists?->domain), json_decode($row['domain'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'domain', 'multiple' => 'multiple', 'onchange' => 'getSubdomains();']) !!}
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="domain_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
                                                <label class="form-label required">Sub-domain</label>
												{{ Form::select('sub_domain[]', remove_select_dynamic_common_list($lists?->subdomains), json_decode($row['sub_domain'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'sub_domain', 'multiple' => 'multiple']) }}
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="sub_domain_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
                                                <label class="form-label required">Transaction type</label>
												{{ Form::select('transaction_type[]', static_common_list($lists?->ondc_types), json_decode($row['transaction_type'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'transaction_type', 'multiple' => 'multiple']) }}
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="transaction_type_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0">
											<label class="form-label required">What are the States and UTs you cover ?</label>
											{{ Form::select('state_id[]', remove_select_dynamic_common_list($lists?->states), json_decode($row['state_id'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'state_id', 'multiple' => 'multiple']) }}
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="state_id_error"></span>
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

											<div class="input-box">
												<label class="form-label required">Cancelled cheque</label>
												
												<input type="file" class="form-control" id="cancelled_cheque" accept="application/pdf, image/png, image/jpeg"/>

												<small>1. Image size should be less than 200kb.<br/>
													2. Only files with extension jpg, jpeg, png ,pdf are allowed</small>
												<div class="cancelled_cheque_file_link">
													
												</div>
												<span class="text-danger form-error" id="cancelled_cheque_document_error"></span>
												<input type="hidden" name="cancelled_cheque_document" id="cancelled_cheque_document" 
												@if(isset($id) && !empty($row['cancelled_cheque_document'])) value="{{ $row['cancelled_cheque_document']}}" @endif/> 
												<div class="progress-cancelled_cheque progress-bg" style="display:none;">
													<div id="loader-cancelled_cheque" style=""></div>
													<div class="progress-bar"></div>
												</div>  
												@if(isset($id) && !empty($row['cancelled_cheque_document']))
												<a href="{{ url('storage/app/uploads/cancelled_cheque/'.$row['cancelled_cheque_document']) }}" download="{{ $row['cancelled_cheque_document_original_name'] }}" >
													{{$row['cancelled_cheque_document_original_name']}}
												</a>
												@endif
											</div>
                                        </div>
							</div>

							<div class="row commercial-detail" >

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/commerce.svg')}}" alt=""> Commercial details </h2>                                      
										</div>

										
										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
											<label class="form-label required">Commercial Model</label>
											{{ Form::select('commercial', array(''=>'Select')+static_common_list($lists?->snp_commercial),ucfirst(Str::singular($row['commercial_model'] ?? '')), ['class' => 'form-select', 'id' => 'commercial']) }}                                               
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="commercial_model_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
											<div class="input-box">
												<label class="form-label required">Commercial Model Documents</label>
												
												<input type="file" class="form-control" id="commercial_model" accept="application/pdf"/>

												<small>1. Image size should be less than 5mb.<br/>
													2. Only files with extension jpg, jpeg, png ,pdf are allowed</small>
												<div class="commercial_model_file_link">
													
												</div>
												<span class="text-danger form-error" id="commercial_model_document_error"></span>
												<input type="hidden" name="commercial_model_document" id="commercial_model_document" 
												@if(isset($id) && !empty($row['commercial_model_document'])) value="{{ $row['commercial_model_document']}}" @endif/> 
												<div class="progress-commercial_model progress-bg" style="display:none;">
													<div id="loader-commercial_model" style=""></div>
													<div class="progress-bar"></div>
												</div> 
												@if(isset($id) && !empty($row['commercial_model_document']))
												<a href="{{ url('storage/app/uploads/commercial_model/'.$row['commercial_model_document']) }}" download="{{ $row['commercial_model_document_original_name'] }}" >
													{{$row['commercial_model_document_original_name']}}
												</a>
												@endif 
											</div>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">No of sellers on boarded by the SNP on ONDC </label>
                                                <input type="text" class="input-text numeric" name="live_seller" maxlength="10" id="live_seller" placeholder="No of live sellers on boarded by the SNP on ONDC to date" 
												@if(isset($id) && !empty($row['live_seller'])) value="{{$row['live_seller']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="live_seller_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Date of going live on ONDC for the SNP</label>
                                                <input type="date" class="input-text" name="date_of_going_live_on_ondc" maxlength="20" id="date_of_going_live_on_ondc" placeholder="Date of going live on ONDC for the SNP" max="{{ date('Y-m-d') }}"
												@if(isset($id) && !empty($row['date_of_going_live_on_ondc'])) value="{{$row['date_of_going_live_on_ondc']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="date_of_going_live_on_ondc_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">No of transactions done till date by the SNP on ONDC</label>
                                                <input type="text" class="input-text numeric" name="no_of_transactions_done" maxlength="20" id="no_of_transactions_done" placeholder="No of transactions done till date by the SNP on ONDC" @if(isset($id) && !empty($row['no_of_transactions_done'])) value="{{$row['no_of_transactions_done']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="no_of_transactions_done_error"></span>
                                        </div>

										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">Short description of the SNP</label>
												<textarea class="input-text" name="short_description" id="short_description" placeholder="Short description of the SNP" maxlength="1000">@if(isset($id) && !empty($row['short_description'])) {{$row['short_description']}} @endif
												</textarea>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="short_description_error"></span>
                                        </div>

										

										<div class="col-lg-4 mb-3">
											<div class="input-box">
												<label class="form-label required">Upload Description</label>
												
												<input type="file" class="form-control" id="description" accept="application/pdf"/>

												<small>1. Upload Pdf only.<br/>
													2. Size should be less than 5mb.<br/>
													3. Only files with extension,pdf are allowed</small>
												<div class="description_file_link">
													
												</div>
												<span class="text-danger form-error" id="description_document_error"></span>
												<input type="hidden" name="description_document" id="description_document" 
												@if(isset($id) && !empty($row['description_document'])) value="{{ $row['description_document']}}" @endif/> 
												<div class="progress-description progress-bg" style="display:none;">
													<div id="loader-description" style=""></div>
													<div class="progress-bar"></div>
												</div>
												@if(isset($id) && !empty($row['description_document']))
												<a href="{{ url('storage/app/uploads/description/'.$row['description_document']) }}" download="{{ $row['description_document_original_name'] }}" >
													{{$row['description_document_original_name']}}
												</a>
												@endif  
											</div>
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
		<script src="{{ asset('assets/js/crypto-js.min.js?ver='.time())}}" integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>   
		
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
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
		<script>  
		
			if (window !== window.top) {  
			document.getElementById('mainbody').innerHTML = "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
			document.getElementById("mainbody").style.backgroundColor = "#ccc";  
			}
	    </script>
	   <script>

		$(document).ready(function() {
			$('#transaction_type').select2({
				placeholder: "Select ONDC Type(s)",
				allowClear: true
			});

			$('#domain').select2({
				placeholder: "Select domain",
				allowClear: true
			});

			$('#sub_domain').select2({
				placeholder: "Select subdomain",
				allowClear: true
			});

			$('#state_id').select2({
				placeholder: "Select States",
				allowClear: true
			});

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
				$(this).val("");
			}
		});

		function getSubdomains(){
			let selectedDomains = $('#domain').val(); 
			$("#sub_domain").html(''); 
			$.ajax({
				url: `{{ url('/getsubdomains') }}`,
				method: 'POST',
				data: {
					domain_ids: selectedDomains,
					_token: '{{ csrf_token() }}'
				},
				success: function(response) {
					if(response.data && response.data.length > 0)
					{
						var title_html = "<option value=''>Select</option>";
						$.each(response.data, function( index, value ) {
							title_html += "<option value="+value.id+">"+value.name+"</option>";
						}); 
               			$("#sub_domain").html(title_html); 
					}
				},
				error: function(xhr) {
					console.error('Error loading subdomains:', xhr);
				}
			});
		}

		$("#authorized_certificate").on("change", function() {
			fileUploadWithLoader({
				url: "{{url('upload-authorized_certificate')}}",
				selector: "#authorized_certificate",
				fileFieldName: 'file',
				hiddenInputSelector: '#authorized_certificate_document',
				progressElementSelector: '.progress-authorized_certificate',
				loaderElementSelector: '#loader-authorized_certificate',
				loadingContent: 'uploading...',
				successMessage: 'Successfully uploaded.',
				errorMessage: 'Invalid file type',
				showFileName:'.authorized_certificate_file_link'
			});
		});
		
		$("#cancelled_cheque").on("change", function() {
			fileUploadWithLoader({
				url: "{{url('upload-cancelled_cheque')}}",
				selector: "#cancelled_cheque",
				fileFieldName: 'file',
				hiddenInputSelector: '#cancelled_cheque_document',
				progressElementSelector: '.progress-cancelled_cheque',
				loaderElementSelector: '#loader-cancelled_cheque',
				loadingContent: 'uploading...',
				successMessage: 'Successfully uploaded.',
				errorMessage: 'Invalid file type',
				showFileName:'.cancelled_cheque_file_link'
			});
		});
		
		$("#commercial_model").on("change", function() {
			fileUploadWithLoader({
				url: "{{url('upload-commercial_model')}}",
				selector: "#commercial_model",
				fileFieldName: 'file',
				hiddenInputSelector: '#commercial_model_document',
				progressElementSelector: '.progress-commercial_model',
				loaderElementSelector: '#loader-commercial_model',
				loadingContent: 'uploading...',
				successMessage: 'Successfully uploaded.',
				errorMessage: 'Invalid file type',
				showFileName:'.commercial_model_file_link'
			});
		});
		
		$("#description").on("change", function() {
			fileUploadWithLoader({
				url: "{{url('upload-description')}}",
				selector: "#description",
				fileFieldName: 'file',
				hiddenInputSelector: '#description_document',
				progressElementSelector: '.progress-description',
				loaderElementSelector: '#loader-description',
				loadingContent: 'uploading...',
				successMessage: 'Successfully uploaded.',
				errorMessage: 'Invalid file type',
				showFileName:'.description_file_link'
			});
		});
		function deleteFile(documentId, type) {
			if (confirm('Do you really want to delete?')) {
				$.ajax({
					url: "{{ url('/snp-delete-documents') }}",
					method: "POST",
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					data: { id: documentId, document_type: type },
					success: function (response) {
						if (response == true) {
							toastr.success('Document has been deleted.');
							$("." + type + "_file_link").html('');
							$("#" + type + "_document").val('');
							$("#" + type).val('');
						} else {
							toastr.error('Something went wrong.');
						}
					}
				});
			}
		}
		
	</script>
  
	<script>
		 $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

		$("#formId").on("submit", function (event) {
			event.preventDefault();
			var formData=$("#formId").serializeArray();
		
			var method = 'POST';
			@isset($id)
				var url = "{{ url('/update-snp-registration/'. $id) }}";	
			@else 
				var url = "{{ url('/create-snp-registration') }}";
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
