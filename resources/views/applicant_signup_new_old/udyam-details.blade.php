<div class="row">
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">State</label>
			<input type="text" class="form-control" name="state_id" maxlength="80" placeholder="State" id="state_id" value="{{$udetails['BasicDetail']['State']}}" disabled>
			<span class="text-danger form-error" id="state_id_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">District</label>
			<input type="text" class="form-control" name="district_id" maxlength="80" placeholder="District" id="district_id" value="{{$udetails['BasicDetail']['District']}}" disabled>
			<span class="text-danger form-error" id="district_id_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">Type Of Organisation</label>
			<input type="text" class="form-control" name="organisation_type" maxlength="80" placeholder="Type of organization" id="organisation_type" value="{{$udetails['BasicDetail']['OrganisationType']}}" disabled>
			<span class="text-danger form-error" id="financial_year_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">{{ __('Gender') }}</label>
			<input type="text" class="form-control" name="gender" maxlength="80" placeholder="Name of enterprise" id="gender" value="{{$udetails['BasicDetail']['Gender']}}" disabled>
			<span class="text-danger form-error" id="gender_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">{{ __('Social Category') }}</label>
			<input type="text" class="form-control" name="social_category" maxlength="80" placeholder="Social category" id="social_category" value="{{$udetails['BasicDetail']['SocialCategory']}}" disabled>
			<span class="text-danger form-error" id="social_category_error"></span>
		</div>
	</div>
	
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">{{ __('Major Activity of Unit') }}</label>
			<input type="text" class="form-control alphaNumeric" name="major_activity" id="major_activity" maxlength="50" value="{{$udetails['BasicDetail']['MajorActivity']}}" disabled>
			<span class="text-danger form-error" id="major_activity_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">{{ __('MSME Classification/Type of Enterprise') }}</label>
			<input type="text" class="form-control" name="msme_classification" maxlength="80" placeholder="MSME Classification" id="msme_classification" value="{{$udetails['BasicDetail']['EnterpriseType']}}" disabled>
			<span class="text-danger form-error" id="sanction_order_date_error"></span>
		</div>
	</div>
	<div class="col-md-4">
		<div class="mb-3">
			<label class="required form-label">{{ __('Classification Year') }}</label>
			<input type="text" class="form-control" name="" placeholder="Classification Year" id="" value="" disabled>
			<span class="text-danger form-error" id=""></span>
		</div>
	</div>
	<div class="col-lg-4 mb-3" >
		<div class="mb-3">
			<label class="form-label required">GSTIN  </label>
			<input type="text" class="form-control" name="gstin_no" placeholder="Enter Gst Number" id="gstin_no" value="">
			
		</div>
		<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_no_error"></span>
	</div>
	<div class="col-lg-4 mb-3" >
		<div class="mb-3">
			<label class="form-label required">PAN Number   </label>
			<input type="text" class="form-control" name="pan_no" placeholder="Enter PAN Number" id="pan_no" value="">
			
		</div>
		<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
	</div>

	<div class="form-action mt-3 mb-3">
		@include('components.admin.buttons.submit-button')
		@include('components.admin.buttons.cancel-button')
	</div>
</div>