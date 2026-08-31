
<div class="row">
   <div class="card-text">
		<div class="row">
			<!-- Udyam Details -->
			<div id="udyamDetailsContainer"></div>

			<div class="col-lg-12 mb-3">
				<h2 class="form-title-heading font-20">Udyam Details </h2>                                      
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="entrepreneur_name" placeholder="Name of entrepreneur"  maxlength="80" id="entrepreneur_name" value="{{$udetails['BasicDetail']['EnterpriseName']}}" disabled>
					<label class="form-label required">Name of entrepreneur</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="entrepreneur_name_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="enterprise_name" maxlength="80" placeholder="Name of enterprise" id="enterprise_name" value="{{$udetails['BasicDetail']['EntrepreneurName']}}" disabled>
					<label class="form-label required">Name of enterprise </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="enterprise_name_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="organisation_type" maxlength="80" placeholder="Type of organization" id="organisation_type" value="{{$udetails['BasicDetail']['OrganisationType']}}" disabled>
					<label class="form-label required">Type of organization </label>

				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organisation_type_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="email" maxlength="80" placeholder="Name of enterprise" id="email" value="{{$udetails['BasicDetail']['EmailId']}}" disabled>
					<label class="form-label required">Email </label>

				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
			</div>
	
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="state_id" maxlength="80" placeholder="State" id="state_id" value="{{$udetails['BasicDetail']['State']}}" disabled>
					<label class="form-label required">State </label>
				</div>
				
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="state_id_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="district_id" maxlength="80" placeholder="District" id="district_id" value="{{$udetails['BasicDetail']['District']}}" disabled>
					<label class="form-label required">District </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="district_id_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="address" maxlength="80" placeholder="Address" id="address" value="{{$udetails['BasicDetail']['CommunicationAddress']}}" disabled>
					<label class="form-label required">Address </label>

				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="address_error"></span>
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="pincode" maxlength="80" placeholder="Pincode" id="pincode" value="{{$udetails['BasicDetail']['PINCode']}}" disabled>
					<label class="form-label required">Pincode </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pincode_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="ph" maxlength="80" placeholder="PH" id="ph" value="{{$udetails['BasicDetail']['PH']}}" disabled>
					<label class="form-label required">PH</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ph_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
						<input type="text" class="input-text" name="major_activity" maxlength="80" placeholder="Major activity of unit" id="major_activity" value="{{$udetails['BasicDetail']['MajorActivity']}}" disabled>
						<label class="form-label required">Major activity of unit </label>
					</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="major_activity_error"></span>
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="msme_classification" maxlength="80" placeholder="MSME Classification" id="msme_classification" value="{{$udetails['BasicDetail']['EnterpriseType']}}" disabled>
					<label class="form-label required">MSME Classification  </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="msme_classification_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="gender" maxlength="80" placeholder="Name of enterprise" id="gender" value="{{$udetails['BasicDetail']['Gender']}}" disabled>
					<label class="form-label required">Gender  </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="gender_error"></span>  
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="social_category" maxlength="80" placeholder="Social category" id="social_category" value="{{$udetails['BasicDetail']['SocialCategory']}}" disabled>
					<label class="form-label required">Social category  </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="social_category_error"></span>  
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="total_emp" placeholder="No of persons employed" id="total_emp" value="{{$udetails['BasicDetail']['TotalEmp']}}" disabled>
					<label class="form-label required">No of persons employed </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="total_emp_error"></span>
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="incorporation_date" placeholder="Date of incorporation" id="incorporation_date" value="{{$udetails['BasicDetail']['IncorporationDate']}}" disabled>
					<label class="form-label required">Date of incorporation </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="incorporation_date_error"></span>
			</div>

			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					 {!! Form::select('gstin', array(''=>'Select GSTIN')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'gstin']) !!} 
					 
					 <label for="gstin" class="form-label required" style="display:none" >Select GSTIN</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3" id="div_gstin_no">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="gstin_no" placeholder="Enter Gst Number" id="gstin_no" value="">
					<label class="form-label required">Enter gst Number </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_no_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('pan', array(''=>'Select PAN')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'pan']) !!}
					
					<label for="pan" class="form-label required" style="display:none">Select PAN</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3" id="div_pan_no">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text" name="pan_no" placeholder="Enter Pan Number" id="pan_no" value="">
					<label class="form-label required">Enter Pan Number </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
			</div>

	

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					
					{!! Form::select('specially_abled', ['' => 'Select Specially abled'] + static_common_list($lists?->yesno), '', ['class' => 'form-select select_value', 'id' => 'specially_abled']) !!}
					
					<label for="specially_abled" class="form-label required" style="display:none">Select Specially abled</label>
				</div>
				<span class="text-danger form-error font-12 mb-3" id="specially_abled_error"></span>
			</div>

			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('physical_device_business_transactions', array(''=>'Select business transactions?')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'physical_device_business_transactions']) !!}
					
					<label for="physical_device_business_transactions" class="form-label required" style="display:none">Select business transactions?</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="physical_device_business_transactions_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('printer', array(''=>'Select Printer')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'printer']) !!}
					
					<label for="printer" class="form-label required" style="display:none" >Select Printer</label>
				</div>
				
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="printer_error"></span>
			</div>


			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('catalogue_prodcut_details', array(''=>'Select Catalogue Prodcut Details')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'catalogue_prodcut_details']) !!}
					
					<label for="catalogue_prodcut_details" class="form-label required" style="display:none">Select Catalogue Prodcut Details</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="catalogue_prodcut_details_error"></span>  
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('attending_ondc_awareness_workshop', array(''=>'Select ONDC awareness workshop')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'attending_ondc_awareness_workshop']) !!}
					
					<label for="attending_ondc_awareness_workshop" class="form-label required" style="display:none">Select ONDC awareness workshop</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="attending_ondc_awareness_workshop_error"></span>
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text integer" name="nic_code"  placeholder="National Industrial Classification (NIC) Code" id="nic_code" minlength="2" maxlength="5">
					<label class="form-label required">National Industrial Classification (NIC) Code</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="nic_code_error"></span>
			</div>
			
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text numeric2decimal" name="net_investment_plant_machinery" placeholder="Net investment in plant and machinery" id="net_investment_plant_machinery" maxlength="15">
					<label class="form-label required">Net investment in plant and machinery</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="net_investment_plant_machinery_error"></span>
			</div>
			<div class="align-items-end row">
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text numeric2decimal" name="turnover" placeholder="Turnover (previous FY)" id="turnover" maxlength="15">
					<label class="form-label required">Turnover (previous FY) </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="turnover_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text txtMix" name="dic_attached" placeholder="DIC attached to?" id="dic_attached" maxlength="100">
					<label class="form-label required">DIC attached to? </label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="dic_attached_error"></span>
			</div>
			

			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					<input type="text" class="input-text txtMix" name="products_geography" placeholder="Please provide the ‘google map’ link of your unit (official address of enterprise)" id="products_geography" maxlength="200">
					<label class="form-label required">Please provide the ‘google map’ link of your unit (official address of enterprise)</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="products_geography_error"></span>
			</div>
			</div>
			

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('current_state_business_id', array(''=>'Select current state business')+static_common_list($lists?->current_state_business),'', ['class' => 'form-select select_value', 'id' => 'current_state_business_id']) !!}
					<label for="current_state_business_id" class="form-label required" style="display:none">Select current state</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="current_state_business_id_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('ondc_transaction_type_id', array(''=>'Select ONDC types')+static_common_list($lists?->ondc_types),'', ['class' => 'form-select select_value', 'id' => 'ondc_transaction_type_id']) !!}
					
					<label for="ondc_transaction_type_id" class="form-label required" style="display:none">Select ONDC types</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ondc_transaction_type_id_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input product_category_label">
					{!! Form::select('product_category_id[]',remove_select_dynamic_common_list($lists?->sub_domains),'', ['class' => 'form-select select2', 'id' => 'product_category_id','multiple' => 'multiple']) !!}
					
					<label for="product_category_id" class="form-label required" style="display:none">Select Product Category</label>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="product_category_id_error"></span>
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input">
					{!! Form::select('select_snp', array(''=>'Select SNP')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'select_snp']) !!}
					
					<label for="select_snp" class="form-label required" style="display:none">Select SNP</label>
				</div>
				
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="select_snp_error"></span>
			</div>
			
			
			
			<div id="sndDetailsContainer">
			
			</div>
			
			<div class="col-lg-4 mb-3">
				<div class="mb-0 floating-label-input"></div>
				
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="snp_id_error"></span>
			</div>
			
			<div class="col-lg-12 mb-3">
			  <div class="d-flex align-items-start">
				<input type="checkbox" class="mt-1 me-2" name="nsic_office" id="nsic_office" value="" required>
				<label for="nsic_office" class="form-check-label">
				  “I/ We hereby declare that the details furnished above are true and correct to the best of my/ our knowledge and belief. If the above information is found to be incorrect / misleading/ false, appropriate action as per the laws may be taken against me/ us. I/ We hereby authorize NSIC/ ONDC to share the relevant details, including transactions on the ONDC Network, with NSIC for the purpose of scheme administration and support, in compliance with applicable laws. Accordingly, I/ we hereby declare that I/ we am/are not onboarded by any SNP as on the date of Operationalisation of the initiative. I / We wish to avail the SUBSIDY & BENEFITS of TEAM INITIATIVE. I / We hereby declare that I have not taken similar assistance from any Central / State government schemes / Programmes. I / We hereby declare that I / We have read the Privacy Policy, Terms and Conditions, Disclaimer and Data Sharing Policy, Operating guidelines of TEAM Initiative, SOPs of TEAM Initiative and abide by it. NSIC reserves the right to change/ amend the SOPs with the approval of the Ministry of MSME as per the policy & procedural requirement as and when warranted and without giving any notice.”
				</label>
			  </div>
			  <span class="text-danger form-error font-12 mb-3" id="nsic_office_error"></span>
			</div>
			
			
			<div class="d-flex align-items-start">
				<input type="checkbox" class="mt-1 me-2" name="agree" id="agree" value="" required>
				<label for="agree" class="form-check-label">
				 Unlock growth for your enterprise by joining NSIC eMarketing portal MSMEmart.com! Gain access to exclusive features like Product Showcase, Unlimited Tender alerts, and Trade Leads. Sign up now and expand your business at Ministry of MSME, Govt of India subsidised Rates!
				</label>
			  </div>
			  <span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
			</div>


			<!--<div class="col-lg-4 mb-3">
				<div class="input-group mb-0 floating-label-input">
					<input type="text" class="input-text numeric" name="mobile" placeholder="Enter Mobile" maxlength="10" id="mobile">
					<label class="form-label label_mobile required">Mobile No</label>
					<button class="btn btn-outline-secondary" type="button" id="mobile_btn" onclick="GenerateOTPButton(this)">Verify</button>
				  </div>
				  <span class="text-danger form-error font-12 font-12 font-12 mb-3" id="mobile_error"></span>
			</div>

			<div class="col-lg-4 mb-3">
				<div class="input-group mb-0 floating-label-input">
					<input type="text" class="input-text" name="username" placeholder="Enter Email" id="email" >
					<label class="form-label label_email required">Email</label>
					<button class="btn bg-transparent" type="button" id="email_btn" onclick="GenerateOTPButton(this)" style="color: #adadad; font-weight: 500; font-size: 14px; margin-top: -1px;"><img src="{{asset('assets/img/non_verify.svg')}}" class="verify_icon"> Verify</button>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span> 
				<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="username_error"></span> 
			</div>-->

			<!--<div class="col-lg-4">
				<div class="mb-3">
					<label class="form-label">Enter Password</label>
					<input type="password" class="form-control" name="password" placeholder="Password" id="password">
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="password_error"></span>
			</div>

			<div class="col-lg-4">
				<div class="mb-3">
					<label class="form-label">Confirm Password</label>
					<input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" id="confirm_password">
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="confirm_password_error"></span>
			</div>-->
			
			<?php /*@if(config('settings.enable_captcha'))
			<div class="col-lg-4 mb-3">
				<div class="mb-0">
					<div class="d-flex align-items-center gap-3 captcha floating-label-input">
						<span class="captcha-img">{!! captcha_img() !!}</span>
						<a href="javascript:void(0);" class="c-reload pe-auto" id="reload"> <i class="fa fa-refresh" aria-hidden="true"></i> </a>
						<input class="input-text" id="captcha" name="captcha">
					</div>
				</div>
				<span class="text-danger form-error font-12 font-12 font-12" id="captcha_error"></span>
			</div>
			@endif */?>

					<div class="row g-3">
						<span id="otp_timer"></span>
						<a href="javascript:void(0);"  id="resend-otp" class="text-primary text-decoration-none" style="display: none; font-size: 13px;">
						  <i class="bi bi-arrow-repeat me-1"></i>Resend OTP
						</a>

						  <div class="form-group col-md-12" style="display:none" id="otp_div">
							<label class="form-label required">
							  Enter the 6 digit One Time Password (OTP)  
							  
							</label>
							<input type="password" class="form-control integer" name="otp" id="otp" placeholder="Enter 6 digit OTP" minlength="6" maxlength="6" required>
							<span class="text-danger form-error" id="otp_error"></span>
						  </div>
						  
						<div class="row mt-2">
						  <div class="form-group col-md-2" id="div_send_otp">
							<button type="button" class="btn btn-primary w-100" id="send-otp">Send OTP</button>
						  </div>
						  
						  <!--<div class="form-group col-md-2" style="display:none" id="div_verify_otp">
							<button type="button" class="btn btn-primary w-100" id="verify-otp">Verify OTP</button>
						  </div>-->

						</div>
					</div>
			
			               
				<div style="display: none;" id="div-submit-register">
					<button id="signup-button" type="submit" class="btn btn-primary btn-themed mt-3">Submit Register </button>
				</div>
			

		</div>

	   
   </div>
</div>
  
  
<script>     
	
	$('.select_value').on('change', function () {
		
		var valueId = $(this).val().trim();

		// get the select ID
		var selectId = $(this).attr('id');

		// find the matching label using for="..."
		var label = $('label[for="' + selectId + '"]');

		if (
		valueId == 1 || 
		valueId == 0 || 
		valueId=="36c32a31-5339-11f0-81dc-00155d022d06" || 
		valueId=="44380a2d-5339-11f0-81dc-00155d022d06" || 
		valueId=="4a3110a6-5339-11f0-81dc-00155d022d06" || 
		valueId=="9e7e1e8b-5578-11f0-81dc-00155d022d06" ||
		valueId=="36523ead-533d-11f0-81dc-00155d022d06"
		) {
			label.show();
		} else {
			label.hide();
		}
});


    $('#product_category_id').select2({
		placeholder: "Select Product Category",
		allowClear: true
	});
	
	$('#div_gstin_no').hide();
	$('#gstin').click(function () {
		var gstin=$(this).val();
		if(gstin==1){
			$('#div_gstin_no').show();
		}else{
			$('#div_gstin_no').hide();
			$('#gstin_no').val('');
		}
	});
	
	
	$('#div_pan_no').hide();
	$('#pan').click(function () {
		var pan=$(this).val();
		if(pan==1){
			$('#div_pan_no').show();
		}else{
			$('#div_pan_no').hide();
			$('#pan_no').val('');
		}
	});
	
	
	$('#product_category_id').on('change', function () {
			var sub_domain=$("#product_category_id").val();
			var selectId = $(this).attr('id');
			if(selectId){
				var label = $('label[for="' + selectId + '"]');
				label.show();
			}
			/*if(sub_domain.length ==0){
				alert('Please select product category');
				$("#select_snp").val('');
				return false;
			}*/
			
			var select_snp=$("#select_snp").val();
			
			var state_code="{{$udetails['BasicDetail']['LG_ST_Code']}}";
			var ondc_transaction_type_id=$("#ondc_transaction_type_id").val();
			$.ajax({
				url: '{{ route("snp-details") }}',
				type: 'GET',
				data:{state_code:state_code,ondc_transaction_type_id:ondc_transaction_type_id,sub_domain:sub_domain},
				success: function (response) {
					$("#cover-spin").hide();
					if(select_snp==1){
						$('#sndDetailsContainer').html(response).show();
					}else{
						$('#sndDetailsContainer').html(response).hide();
					}
				},
				error: function () {
					$("#cover-spin").hide();
					alert('Failed to load content.');
				}
			});
			
    });
	
	
	$('#select_snp').on('change', function () {
		var select_snp=$(this).val();
		if(select_snp==1){
			$('#sndDetailsContainer').show();
		}else{
			$('#sndDetailsContainer').hide();
			 $('.snp_id').prop('checked', false);
		}
	});

			
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
	

	var today = new Date();
	$(".datepicker").datepicker({
        dateFormat: "dd-mm-yy",
        changeYear: true,
        changeMonth: true,
        //minDate: 0  
    });
	
	$("#nsic_office").on("change", function () {
		if ($(this).is(":checked")) {
			$(this).val(1);
		} else {

			$(this).val("");
		}
	});
	
	$("#agree").on("change", function () {
		if ($(this).is(":checked")) {
			$(this).val(1);
		} else {

			$(this).val("");
		}
	});


	 $("#send-otp").on("click", function(e) {
		e.preventDefault();

		
		var gstin = $("#gstin").val().trim();
		
		if(gstin==1){
			toastr.error('Please enter gst number');
			return false;
		}
        
		var pan = $("#pan").val().trim();
		
        if(pan==1){
			toastr.error('Please enter pan number');
			return false;
		}
		
		
		
		var specially_abled = $("#specially_abled").val().trim();
        var physical_device_business_transactions = $("#physical_device_business_transactions").val().trim();
        var printer = $("#printer").val().trim();
        var catalogue_prodcut_details = $("#catalogue_prodcut_details").val().trim();
        var attending_ondc_awareness_workshop = $("#attending_ondc_awareness_workshop").val().trim();
		
		var dic_attached = $("#dic_attached").val().trim();
		var nic_code = $("#nic_code").val().trim();

        var products_geography = $("#products_geography").val().trim();
        var current_state_business_id = $("#current_state_business_id").val().trim();
        var ondc_transaction_type_id = $("#ondc_transaction_type_id").val().trim();
		var product_category_id = $("#product_category_id").val();
        var select_snp = $("#select_snp").val().trim();
		
		if(select_snp==1){
			var snp_id = $("[name='snp_id']:checked").val();
			if(snp_id ==undefined){
				 toastr.error('if select snp yes then select any snp');
				 return false;
			}
		}
		
		var nsic_office = $("#nsic_office").val();	 
		var agree = $("#agree").val();
		
		if (
            gstin === "" ||
            pan === "" ||
            specially_abled === "" ||
            physical_device_business_transactions === "" ||
            printer === "" ||
            catalogue_prodcut_details === "" ||
            attending_ondc_awareness_workshop === "" ||
            products_geography === "" ||
            current_state_business_id === "" ||
            ondc_transaction_type_id === "" || 
			product_category_id =="" || dic_attached ==="" || nic_code==="" || select_snp === "" || nsic_office===""  || agree===""
        ) {
            toastr.error('Please fill in all required fields.');
            return false;
        }

		
		
       $("#send-otp").attr("class", "btn btn-primary disabled");
       $("#send-otp").html("Processing...");
       var csrfToken = "{{ csrf_token() }}";
		  
	   var mobile = $("#mobile").val();
	   var email = $("#email").val();

	   if (mobile && email) {
		   $("#cover-spin").show();
		   $.ajax({
			   url: "{{ url('send-otp-msme-registration') }}",
			   type: 'POST',
			   data: { 
				   "username":mobile,
				   "email":email,
				   "_token": csrfToken,
			   },
			  
			   success: function (data) { 
				   if (data.status) {
					   startTimer();
                       timerInterval();
					  $("#otp_div").show();
					  $('#div_send_otp').hide();
					  $('#div_verify_otp').show();
					  $("#div-submit-register").show();
					  $("#cover-spin").hide();    
					   toastr.success(data.message);
				   }else{ 
						$("#send-otp").html('Send OTP');
						$("#send-otp").attr("class", "btn btn-primary");				   
						toastr.error(data.message);
				   }
					$("#cover-spin").hide();  
			   },
			   error: function(xhr, status, error) {
				   
				   $("#cover-spin").hide(); 
				   $("#send-otp").html('Send OTP');
					$("#send-otp").attr("class", "btn btn-primary");
				   toastr.error(xhr.responseJSON.message);
			   }
		   });
	   } 
	   else { 
			$("#cover-spin").hide(); 
			$("#send-otp").html('Send OTP');
			$("#send-otp").attr("class", "btn btn-primary");
			toastr.error("Please enter the required fields");
		   return;
	   }
   }); 

   
     $("#resend-otp").click(function(e) {
       e.preventDefault();
	   var mobile = $("#mobile").val();
	   var email = $("#email").val();

	   if (mobile && email) {
       $.ajax({
           type: "POST",
           url: "{{ url('send-otp-msme-registration') }}",
           data: {
               "username":mobile,
				"email":email,
               _token: $('meta[name="csrf-token"]').attr('content'),
           },
           success: function(data) {
               if (data.status) {
                    startTimer();
                    clearInterval(interval);
                    timerInterval();
					 $("#otp_div").show();
					 $('#div_send_otp').hide();
					 $('#div_verify_otp').show();
					   $("#div-submit-register").show();
					toastr.success(data.message);
                    $("#otp_timer").show();
                    $("#resend-otp").hide();
               }
           }
       });
	}
   });
   
   
   
   var interval;
   function timerInterval(){
       var counter = 0;
       interval = setInterval(function() {  
       if (counter > {{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}) {
	   //if (counter > 2) {
		  stopTimer();
           $("#otp_timer").hide();
           $("#resend-otp").show();
       }
       counter++;
   },1000);
   }
   
   $(".txtMix").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[a-zA-Z.\\-\\() ]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
   
   $(".integer").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("^[0-9]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	$(".numeric").on("keypress keyup blur input",function (e) {
		  var regex = new RegExp("[0-9.]+$");
		  var key = String.fromCharCode(!e.charCode ? e.which : e.charCode);
			if (!regex.test(key)) {
			   e.preventDefault();
			   return false;
			}
		return true;
	});
	
	
	$(".numeric2decimal").on("input", function () {
		var val = $(this).val();
	
		// 1. Remove invalid chars (only digits and . allowed)
		val = val.replace(/[^0-9.]/g, '');
	
		// 2. Allow only one decimal point
		val = val.replace(/(\..*?)\..*/g, '$1');
	
		// 3. Limit to 2 decimal places
		if (val.indexOf('.') >= 0) {
			val = val.substring(0, val.indexOf('.') + 3);
		}
	
		// 4. Limit total length (15 incl. decimals)
		if (val.length > 15) {
			val = val.substring(0, 15);
		}
	
		$(this).val(val);
	});
</script>


