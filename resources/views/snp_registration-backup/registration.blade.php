
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
								<li class="d-flex gap-3 mb-3"> <div class="icon">3</div> Value Proposition for MSEs</li>
								<li class="d-flex gap-3 mb-3"> <div class="icon">4</div> Commercial Model </li>
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
                                <form method="post" id="formId" class="mt-2 pt-3 formId">
									<div class="row">
										<div class="col-lg-12">
											<p>Fill all required<span style="color: #db0000ff;">*</span> fields and complete the registration process.</p>
										</div>
									</div>
									<div class="row role-detail">
										<div class="col-lg-12 mb-3">
                                            <h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/bbasic.svg')}}" alt=""> Role</h2>                                      
                                        </div>
										<div class="col-lg-12 mb-3">
											<div class="mb-0">
												<label class="form-label required">Role</label>
												<select name="roles[]" class="form-select" id="roles" multiple>
													<option value="">Select</option>
													@foreach($roleList as $r)
														<option value="{{ $r->id }}">{{ $r->name }}</option>
													@endforeach
												</select>
											</div>
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
												<label class="form-label required">Official/Primary Email ID</label>
                                                <input type="text" class="input-text alphanumeric" name="email" maxlength="100" id="email" placeholder="Official/Primary Email ID"
												@if(isset($id) && !empty($row['email'])) value="{{$row['email']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
                                        </div>
										<div class="col-lg-4 mb-3">
                                            <div class="mb-0 ">
												<label class="form-label required">BPPId.ProviderID</label>
                                                <input type="text" class="input-text alphanumeric" name="bppid_providerid" maxlength="100" id="bppid_providerid" placeholder="BPPId.ProviderID"
												@if(isset($id) && !empty($row['bppid_providerid'])) value="{{$row['bppid_providerid']}}" @endif>
                                                
                                            </div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="bppid_providerid_error"></span>
                                        </div>
									</div>
									<div class="row contact_detail">
										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> Contact details</h2>                                      
										</div>
<!-- txtnumerichypenapercend -->
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Official/Primary Email ID</label>
												<input type="text" class="input-text alphanumeric" name="contact_details[email]" maxlength="100" id="contact_details[email]" placeholder="Official/Primary Email ID"
												@if(isset($id) && !empty($row['contact_details[email]'])) value="{{$row['contact_details[email]']}}" @endif>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>

										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Primary Contact No</label>
												<input type="text" class="input-text numeric" name="contact_details[primary_contact_no]" maxlength="15" id="contact_details[primary_contact_no]" placeholder="Primary No"
												@if(isset($id) && !empty($row['contact_details[primary_contact_no]'])) value="{{$row['contact_details[primary_contact_no]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label">WhatsApp No/Channel</label>
												<input type="text" class="input-text numeric" name="contact_details[whatsapp_no]" maxlength="15" id="contact_details[whatsapp_no]" placeholder="WhatsApp No/Channel"
												@if(isset($id) && !empty($row['contact_details[whatsapp_no]'])) value="{{$row['contact_details[whatsapp_no]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Website</label>
												<input type="text" class="input-text alphanumeric" name="contact_details[website]" maxlength="15" id="contact_details[website]" placeholder="contact_details[website]"
												@if(isset($id) && !empty($row['contact_details[website]'])) value="{{$row['contact_details[website]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label">App Store Link(s)</label>
												<input type="text" class="input-text alphanumeric" name="contact_details[app_store_links]" maxlength="15" id="contact_details[app_store_links]" placeholder="App Store Link(s)"
												@if(isset($id) && !empty($row['contact_details[app_store_links]'])) value="{{$row['contact_details[app_store_links]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label">Social Media Handles</label>
												<input type="text" class="input-text alphanumeric" name="contact_details[social_media_handles]" maxlength="15" id="contact_details[social_media_handles]" placeholder="Social Media Handles"
												@if(isset($id) && !empty($row['contact_details[social_media_handles]'])) value="{{$row['contact_details[social_media_handles]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="contact_details_error"></span>
										</div>
									</div>
									<div class="row authorised_person">
										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> Authorised Person(s)</h2>                                      
										</div>

										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Name of Authorised Person(s)</label>
												<input type="text" class="input-text alphanumeric" name="authorized_person_details[name]" maxlength="80" id="authorized_person_details[name]" placeholder="Name of Authorised Person(s)"
												@if(isset($id) && !empty($row['authorized_person_details[name]'])) value="{{$row['authorized_person_details[name]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="authorized_person_details_error"></span>
										</div>

										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label">Role/Designation</label>
												<select name="authorized_person_details[designation]" class="form-select" id="authorized_person_details[designation]">
													<option value="">Select</option>
													@foreach($roleList as $r)
														<option value="{{ $r->id }}">{{ $r->name }}</option>
													@endforeach
												</select>
												<!-- <input type="text" class="input-text" name="role_designation" maxlength="80" id="role_designation" placeholder="Role/Designation"
												
												@if(isset($id) && !empty($row['role_designation'])) value="{{$row['role_designation_name']}}" @endif > -->
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="role_designation_name_error"></span>
										</div>
										
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label">Phone/WhatsApp Number</label>
												<input type="text" class="input-text numeric" name="authorized_person_details[phone]" maxlength="15" id="authorized_person_details[phone]" placeholder="Phone/WhatsApp Number"
												@if(isset($id) && !empty($row['authorized_person_details[phone]'])) value="{{$row['authorized_person_details[phone]']}}" @endif >
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="authorized_person_details_error"></span>
										</div>

										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Email Address</label>
												<input type="text" class="input-text" name="authorized_person_details[email]" maxlength="80" id="authorized_person_details[email]" placeholder="Email Address"
												@if(isset($id) && !empty($row['authorized_person_details[email]'])) value="{{$row['authorized_person_details[email]']}}" @endif >
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="authorized_person_details_error"></span>
										</div>

										<div class="col-lg-4 mb-3">
										
											<div class="input-box">
												<label class="form-label">Authorized Certificate</label>
												
												<input type="file" class="form-control upload-pdf" id="authorized_person_details[0][certificate]" accept="application/pdf"/>

												<small>1. Image size should be less than 5mb.<br/>
													2. Only files with extension jpg, jpeg, png ,pdf are allowed</small>
												<div class="authorized_certificate_file_link">
													
												</div>
												<span class="text-danger form-error" id="authorized_person_details_error"></span>

												<input type="hidden" name="authorized_person_details[0][certificate]" id="authorized_person_details_0_certificate">

												<input type="hidden" name="authorized_person_details[0][certificate_id]" id="authorized_person_details_0_certificate_id">

												<input type="hidden" name="" id="" 
												@if(isset($id) && !empty($row['authorized_person_details[0][certificate]'])) value="{{ $row['authorized_person_details[0][certificate]']}}" @endif /> 
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
													<label class="form-label required">GST Number</label>
													<input type="text" class="input-text" name="gst_number" maxlength="80" id="gst_number" placeholder="GST Number"
													@if(isset($id) && !empty($row['gst_number'])) value="{{$row['gst_number']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gst_number_error"></span>
											</div>
											<div class="col-lg-4 mb-3">
												<div class="mb-0 ">
													<label class="form-label required">PAN</label>
													<input type="text" class="input-text" name="pan_no" maxlength="80" id="pan_no" placeholder="PAN"
													@if(isset($id) && !empty($row['pan_no'])) value="{{$row['pan_no']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
											</div>
											<div class="col-lg-4 mb-3">
												<div class="mb-0 ">
													<label class="form-label">CIN</label>
													<input type="text" class="input-text" name="cin_no" maxlength="80" id="cin_no" placeholder="CIN"
													@if(isset($id) && !empty($row['cin_no'])) value="{{$row['cin_no']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="cin_no_error"></span>
											</div>

											<div class="col-lg-4 mb-3">
												<div class="mb-0 ">
													<label class="form-label">Startup ID/DPIIT Recognition</label>
													<input type="text" class="input-text" name="startup_id_no" maxlength="80" id="startup_id_no" placeholder="Startup ID/DPIIT Recognition"
													@if(isset($id) && !empty($row['startup_id_no'])) value="{{$row['startup_id_no']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="startup_id_no_error"></span>
											</div>

											<div class="col-lg-4 mb-3">
												<div class="mb-0 ">
													<label class="form-label">FSSAI Number</label>
													<input type="text" class="input-text" name="fssai_number" maxlength="80" id="fssai_number" placeholder="FSSAI Number"
													@if(isset($id) && !empty($row['fssai_number'])) value="{{$row['fssai_number']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="fssai_number_error"></span>
											</div>

											<div class="col-lg-4 mb-3">
												<div class="mb-0 ">
													<label class="form-label">IEC Number</label>
													<input type="text" class="input-text" name="iec_number" maxlength="80" id="iec_number" placeholder="IEC Number"
													@if(isset($id) && !empty($row['iec_number'])) value="{{$row['iec_number']}}" @endif >
												</div>
												<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="iec_number_error"></span>
											</div>
									</div>

									<div class="row bank-detail" >

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> Bank details </h2>                                      
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

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> Role Section (defined below) </h2>                                      
										</div>

										<div id="roleDetailsContainer"></div>
									</div>
									<div class="row value_proposition">
										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"><img src="{{asset('assets/img/bbasic.svg')}}" alt=""> Value Proposition for MSEs</h2>                                      
										</div>

										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Short Description</label>
												<input type="text" class="input-text alphaNumericSpace" name="short_decription" maxlength="10" id="short_decription" placeholder="Short Description" 
												@if(isset($id) && !empty($row['short_decription'])) value="{{$row['short_decription']}}" @endif>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="short_decription_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">What additional service do you offer MSMEs beyond connecting them to ONDC</label>
												<input type="text" class="input-text alphaNumericSpace" name="additional_service" maxlength="10" id="additional_service" placeholder="Enter" 
												@if(isset($id) && !empty($row['additional_service'])) value="{{$row['additional_service']}}" @endif>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="additional_service_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Language Supported</label>
												<select name="language_supported" class="form-select" id="language_supported">
													<option value="">Select</option>
													@foreach($lang as $lang)
														<option value="{{ $lang->id }}">{{ $lang->name }}</option>
													@endforeach
												</select>
												<!-- <input type="text" class="input-text alphaNumericSpace" name="language_supported" maxlength="10" id="language_supported" placeholder="Language Supported" 
												@if(isset($id) && !empty($row['language_supported'])) value="{{$row['language_supported']}}" @endif> -->
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="language_supported_error"></span>
										</div>

										
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Team Scheme landing page</label>
												<input type="text" class="input-text alphaNumericSpace" name="team_scheme_landing_page" maxlength="10" id="team_scheme_landing_page" placeholder="Team Scheme landing page" 
												@if(isset($id) && !empty($row['team_scheme_landing_page'])) value="{{$row['team_scheme_landing_page']}}" @endif>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="team_scheme_landing_page_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Short Video Pitch</label>
												<input type="text" class="input-text alphaNumericSpace" name="short_video_pitch" maxlength="10" id="short_video_pitch" placeholder="Short Video Pitch" 
												@if(isset($id) && !empty($row['short_video_pitch'])) value="{{$row['short_video_pitch']}}" @endif>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="short_video_pitch_error"></span>
										</div>
										<div class="col-lg-12 mb-3">
											<div class="col-lg-4 mb-3">
												<div class="input-box">
													<label class="form-label required">Flyer / Presentation</label>

													<input type="file" class="form-control" id="flyer_presentation_file" accept=".jpg,.jpeg,.png,.pdf" />

													<small>
														1. File size should be less than 1.5MB.<br>
														2. Allowed formats:PDF.
													</small>

													<div class="flyer_presentation_file_link"></div>

													<span class="text-danger form-error" id="flyer_presentation_error"></span>

													<input type="hidden" 
														name="flyer_presentation" 
														id="flyer_presentation"
														@if(isset($id) && !empty($row['flyer_presentation'])) 
																value="{{ $row['flyer_presentation'] }}"
														@endif/>

													<div class="progress-flyer_presentation progress-bg" style="display:none;">
														<div id="loader-flyer_presentation"></div>
														<div class="progress-bar"></div>
													</div>

													@if(isset($id) && !empty($row['flyer_presentation']))
														<a href="{{ url('storage/app/uploads/flyer/' . $row['flyer_presentation']) }}"
														download="{{ $row['flyer_presentation_original_name'] }}">
															{{ $row['flyer_presentation_original_name'] }}
														</a>
													@endif
												</div>
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="flyer_presentation_error"></span>
										</div>
									</div>
									<div class="row commercial-detail" >

										<div class="col-lg-12 mb-3">
											<h2 class="form-title-heading font-20"> <img src="{{asset('assets/img/commerce.svg')}}" alt=""> Commercial Model </h2>                                      
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
												<label class="form-label required">Do you Charge a free</label>
												<select class="form-select" name="charge_free" id="charge_free">
													<option>Select</option>
													<option value="1">Yes</option>
													<option value="2">No</option>
												</select>
												
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="charge_free_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Flat fee/subscription</label>
													<select class="form-select" name="flat_fee_subscription" id="flat_fee_subscription">
														<option>Select</option>
														<option value="1">Flat fee</option>
														<option value="2">Subscription</option>
													</select>
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="flat_fee_error"></span>
										</div>
										<div class="col-lg-4 mb-3" id="flat_fee_div" style="display:none;">
											<div class="mb-0 ">
													<label class="form-label required">INR Amont</label>
														<input type="text" class="input-text alphaNumericSpace" name="flat_fee_amount" maxlength="10" id="flat_fee_amount" placeholder="INR Amount" 
													@if(isset($id) && !empty($row['flat_fee'])) value="{{$row['flat_fee']}}" @endif>
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="flat_fee_error"></span>
										</div>
										<div class="col-lg-4 mb-3" id="subscription_div" style="display:none;">
											<div class="mb-0 ">
												<label class="form-label required">Select Subscription</label>
												<select class="form-select" name="subscription" id="subscription">
													<option>Select</option>
													<option value="1">Annual</option>
													<option value="2">Monthly</option>
													<option value="2">Onetime</option>
												</select>
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="flat_fee_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Commission Per Transaction</label>
													<select class="form-select" name="commision_transaction" id="commision_transaction">
														<option>Select</option>
														<option value="1">Website</option>
														<option value="2">Landing Page</option>
														<option value="2">Document</option>
													</select>
													<!-- <input type="text" class="input-text alphaNumericSpace" name="commision_transaction" maxlength="10" id="commision_transaction" placeholder="Enter INR or Percentage" 
													@if(isset($id) && !empty($row['commision_transaction'])) value="{{$row['commision_transaction']}}" @endif> -->
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="commision_transaction_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Other Fees</label>
													<input type="text" class="input-text alphaNumericSpace" name="other_fees" maxlength="10" id="other_fees" placeholder="Other Fees" 
													@if(isset($id) && !empty($row['other_fees'])) value="{{$row['other_fees']}}" @endif>
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="other_fees_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Special Offers/Discounts/Add-ons?</label>
													<input type="text" class="input-text alphaNumericSpace" name="special_offer" maxlength="10" id="special_offer" placeholder="Special Offers/Discounts/Add-ons?" 
													@if(isset($id) && !empty($row['special_offer'])) value="{{$row['special_offer']}}" @endif>
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="special_offer_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Special Offers for Team scheme</label>
													<input type="text" class="input-text alphaNumericSpace" name="special_offer_team_scheme" maxlength="10" id="special_offer_team_scheme" placeholder="Special Offers for Team scheme" 
													@if(isset($id) && !empty($row['special_offer_team_scheme'])) value="{{$row['special_offer_team_scheme']}}" @endif>
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="special_offer_team_scheme_error"></span>
										</div>
										<div class="col-lg-4 mb-3">
											<div class="mb-0 ">
													<label class="form-label required">Link to Commercial Model</label>
													{{ Form::select('commercial', array(''=>'Select')+static_common_list($lists?->snp_commercial),ucfirst(Str::singular($row['commercial_model'] ?? '')), ['class' => 'form-select', 'id' => 'commercial']) }}
													
													
													<!-- @if(isset($id) && !empty($row['link_to_commercial_model'])) value="{{$row['link_to_commercial_model']}}" @endif> -->
													
											</div>
											<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="link_to_commercial_model_error"></span>
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
											<div class="action-bottom flex-row justify-content-center">
												<input type="hidden" name="form_status" id="form_status" value="">
												<button id="cancel-button" type="button" class="btn btn-secondary btn-themed mt-3">Cancel</button>
												<button id="signup-button" type="submit" class="btn btn-primary btn-themed mt-3">Submit</button>
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
		<script src="{{ asset('assets/js/mustache.min.js?ver='.time())}}"></script>
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
			var ONDC_DOMAIN_LIST = @json($domain_type);
			var ROLE_LIST = @json($roleList);
		</script>
		<script id="role_add_more_template" type="x-tmpl-mustache">
			<div class="role_row card p-3 mb-3" id="role_row_@{{randomId}}" style="border:1px solid #ddd; border-radius:8px;">

				<div class="row">

					<div class="col-md-4">
						<label class="form-label required">Domain / Product Type</label>
						<select name="roles[@{{randomId}}][ondc_domain_mapping]" 
								class="form-select ondc_category"
								data-row-id="@{{randomId}}">
							<option value="">Select</option>
						</select>
					</div>

					<div class="col-md-4">
						<label>ONDC Domain Mapping</label>
						<input type="text" 
							name="roles[@{{randomId}}][ondc_domain_mapping]" 
							class="form-control ondc_mapping_input" readonly
							value="@{{response.ondc_domain_mapping}}">
					</div>

					<div class="col-md-4">
						<label class="form-label required">Transaction Type</label>
						<select name="roles[@{{randomId}}][transaction_type]" class="form-select">
							@foreach(static_common_list($lists?->ondc_types) as $key => $val)
								<option value="{{ $key }}"
									@{{#response.txn_{{ $key }}}}selected@{{/response.txn_{{ $key }}}}>
									{{ $val }}
								</option>
							@endforeach

						</select>
					</div>

					<div class="col-md-4 mt-3">
						<label>Role</label>
						<select name="roles[@{{randomId}}][role]"
								class="form-select ondc_role_select"
								data-row-id="@{{randomId}}">
							<option value="">Select</option>
						</select>
					</div>

					<div class="col-md-4 mt-3">
						<label>Status</label>
						<select name="roles[@{{randomId}}][status]" class="form-select">
							<option value="">Select</option>
							@foreach(static_common_list($lists?->production_status) as $key => $val)
								<option value="{{ $key }}"
									@{{#response.production_status{{ $key }}}}selected@{{/response.production_status{{ $key }}}}>
									{{ $val }}
								</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-4 mt-3">
						<label>Serviceability</label>
						<select name="roles[@{{randomId}}][serviceability][]" 
								class="form-select">
							@foreach(state_list() as $state)
								<option value="{{ $state }}"
									@{{#response.serv_{{ $state }}}}selected@{{/response.serv_{{ $state }}}}>
									{{ $state }}
								</option>
							@endforeach

						</select>
					</div>

					<div class="col-md-12 mt-3 d-flex justify-content-end">
						<a class="add_more_role btn btn-outline-success btn-sm me-2" 
						href="javascript:void(0)">Add</a>

						<a class="remove_role_row btn btn-outline-danger btn-sm delete_role_button"
							data-id="@{{randomId}}"
							href="javascript:void(0)"
							style="display:none;">Remove</a>
					</div>

				</div>

			</div>
		</script>

		<script>
			var role_json   = @json($roles ?? []); 

			function addRoleComponent(response = {}) {

				var randomId = response.random_id ?? response.id ?? Date.now();

				var source = $("#role_add_more_template").html();
				if (!source) {
					console.error("Role template not found");
					return;
				}

				Mustache.parse(source);

				var rendered = Mustache.render(source, {
					randomId:   randomId,
					response:   response
				});

				$("#roleDetailsContainer").append(rendered);
				populateDomainDropdown(randomId, response);
				populateRoleDropdown(randomId, response);
				updateRoleButtons();
			}

			if (role_json && role_json.length > 0) {
				$.each(role_json, function (i, res) {
					addRoleComponent(res);
				});
			} else {
				addRoleComponent();
			}

			$(document).on("click", ".add_more_role", function () {
				addRoleComponent();
			});

			$(document).on("click", ".remove_role_row", function () {
				$(this).closest(".role_row").remove();
				updateRoleButtons();
			});

			function populateDomainDropdown(rowId, response) {
				let select = $(`#role_row_${rowId} .ondc_category`);
				select.empty().append(`<option value="">Select</option>`);

				ONDC_DOMAIN_LIST.forEach(item => {

					let selected = (response.ondc_domain_mapping == item.ondc_domain_id) ? "selected" : "";

					select.append(`
						<option value="${item.id}" ${selected}>
							${item.product_domain_type}
						</option>
					`);
				});
			}
			
			function populateRoleDropdown(rowId, response = {}) {

				let select = $(`#role_row_${rowId} .ondc_role_select`);

				if (select.length === 0) {
					console.error("Role dropdown not found for row:", rowId);
					return;
				}

				select.html(`<option value="">Select</option>`);

				ROLE_LIST.forEach(role => {
					let selected = (response.role == role.id) ? "selected" : "";
					select.append(`
						<option value="${role.id}" ${selected}>${role.name}</option>
					`);
				});
			}


			function updateRoleButtons() {

				let rows = $("#roleDetailsContainer .role_row");

				rows.find(".add_more_role").hide();
				rows.find(".delete_role_button").show();

				let firstRow = rows.first();

				firstRow.find(".add_more_role").show();
				firstRow.find(".delete_role_button").hide();
			}


			$(document).ready(function() {
				$('#roles').select2({
					placeholder: "Select Roles",
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


			$('.upload-pdf').on('change', function() {
				const file = this.files[0];
				if (!file) return;

				let index = $(this).data("index");

				let hidden_file = "#authorized_person_details_" + index + "_certificate";
				let hidden_id   = "#authorized_person_details_" + index + "_certificate_id";

				const formData = new FormData();
				formData.append('_token', '{{ csrf_token() }}');
				formData.append('document_category_id', 'AUTHORIZED_CERTIFICATE'); 
				formData.append('file', file);

				var document_category_id = $(this).attr('document_category_id');
				

				$.ajax({
					url: '{{ url('upload-document-public') }}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if (response.status) {
							toastr.success(response.message);
							$(hidden_file).val(response.data.file_name);
                			$(hidden_id).val(response.data.file_id);
						}
					},
					error: function(xhr, status, error) {
						toastr.error('File upload failed.');
					}
				});
        	});

			$("#flyer_presentation_file").on("change", function() {
				fileUploadWithLoader({
					url: "{{url('upload-flyer-presentation-certificate')}}",
					selector: "#flyer_presentation_file",
					fileFieldName: 'file',
					hiddenInputSelector: '#flyer_presentation_file_document',
					progressElementSelector: '.progress-flyer_presentation_file',
					loaderElementSelector: '#loader-flyer_presentation_file',
					loadingContent: 'uploading...',
					successMessage: 'Successfully uploaded.',
					errorMessage: 'Invalid file type',
					showFileName:'.flyer_presentation_file_file_link'
				});
			});
			
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
			$("#cancel-button").on("click", function (e) {
				console.log("Cancel clicked");
				$("#formId")[0].reset();
			});

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
					var url = "{{ url('/register-network-provider') }}";
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
							if ($("#form_status").val() == "1") {
								setTimeout(function(){
									redirect("{{ url('login') }}")},
									2000);
							}
							
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

			$(document).on("change", ".ondc_category", function () {

					let selectedId = $(this).val();
					let row = $(this).closest('.role_row');
					let mappingInput = row.find(".ondc_mapping_input");

					var url = "{{ url('/get-ondc-domain-id') }}";

					if (selectedId === "") {
						mappingInput.val("");
						return;
					}

					$.ajax({
						url: url,
						type: "POST",
						data: {
							id: selectedId,
							_token: $('meta[name="csrf-token"]').attr("content")
						},
						success: function (res) {
							//console.log(res);

							if (res.status && res.data.length > 0) {
								mappingInput.val(res.data[0].ondc_domain_id);
							} else {
								mappingInput.val("");
							}
						}
					});

			});
			
			$("#flat_fee_subscription").trigger("change");
			$(document).on("change", "#flat_fee_subscription", function () {
				let val = $(this).val();

				if (val == "1") {
					$("#flat_fee_div").show();
					$("#subscription_div").hide();
				} 
				else if (val == "2") {
					$("#subscription_div").show();
					$("#flat_fee_div").hide();
				} 
				else {
					$("#flat_fee_div").hide();
					$("#subscription_div").hide();
				}
			});

		</script>
        
    </body>
</html>
