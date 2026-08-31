@extends('components.admin.content-layout')

@section('styles')
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
@endsection
<style>
.file-locked {
    pointer-events: none;
    background-color: #f8f9fa;
    opacity: 1;
}
.select-locked {
    pointer-events: none;
    background-color: #f8f9fa;
}
.disabled {
    background-color: #dddd !important;
    cursor: no-drop;
}


</style>
@section('card-content')
<?php
    function editable($field, $editableFields) {
        return in_array($field, $editableFields);
    }
?>
<div class="card-body pt-1">
    <div class="card mb-4">
        <div class="card-body">
            <form method="post" id="formId" class="mt-2">
                @if(isset($id)) <input type="hidden" name="id" value="{{ $id }}"> @endif
					<div class="row">
	
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Name of Entrepreneur</label>
								<input type="text" class="input-text txtOnly disabled" name="entrepreneur_name" placeholder="Name of Entrepreneur"  maxlength="100" id="entrepreneur_name" value="{{$udetails['BasicDetail']['EnterpriseName']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="entrepreneur_name_error"></span>
						</div>

						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Name of Enterprise </label>
								<input type="text" class="input-text alphaNumericSpace disabled" name="enterprise_name" maxlength="100" placeholder="Name of Enterprise" id="enterprise_name" value="{{$udetails['BasicDetail']['EntrepreneurName']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="enterprise_name_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="organisation_type" maxlength="80" placeholder="Type of Organization" id="organisation_type" value="{{$udetails['BasicDetail']['OrganisationType']}}" disabled>
								<label class="form-label required">Type of Organization </label>

							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organisation_type_error"></span>
						</div>

						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Email </label>
								<input type="email" class="input-text disabled" name="email" maxlength="100" placeholder="Email" id="email" value="{{$udetails['BasicDetail']['EmailId']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="address" maxlength="80" placeholder="Address" id="address" value="{{$udetails['BasicDetail']['CommunicationAddress']}}" disabled>
								<label class="form-label required">Address </label>

							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="address_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="state_id" maxlength="80" placeholder="State" id="state_id" value="{{$udetails['BasicDetail']['State']}}" disabled>
								<label class="form-label required">State </label>
							</div>
							
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="state_id_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="district_id" maxlength="80" placeholder="District" id="district_id" value="{{$udetails['BasicDetail']['District']}}" disabled>
								<label class="form-label required">District </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="district_id_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="msme_classification" maxlength="80" placeholder="MSE Classification" id="msme_classification" value="{{$udetails['BasicDetail']['EnterpriseType']}}" disabled>
								<label class="form-label required">{{ __('message.mse') }} Classification  </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="msme_classification_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
									<input type="text" class="input-text disabled" name="major_activity" maxlength="80" placeholder="Major Activity of Unit" id="major_activity" value="{{$udetails['BasicDetail']['MajorActivity']}}" disabled>
									<label class="form-label required">Major Activity of Unit </label>
								</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="major_activity_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								{!! Form::select('current_state_business_id', array(''=>'Select')+static_common_list($lists?->current_state_business),$selected['current_state_business_id'] ?? '', ['class' => 'form-select select_value disabled', 'id' => 'current_state_business_id','disabled' => true]) !!}
								<label for="current_state_business_id" class="form-label required" style="display:none">What is the current state of your business?</label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="current_state_business_id_error"></span>
						</div>
						
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								{!! Form::select('attending_ondc_awareness_workshop', array(''=>'Select')+static_common_list($lists?->yesno),$selected['attending_ondc_awareness_workshop'] ?? '', ['class' => 'form-select select_value disabled', 'id' => 'attending_ondc_awareness_workshop','disabled' => true]) !!}
								
								<label for="attending_ondc_awareness_workshop" class="form-label required" style="display:none">Are you interested in attending ONDC awareness</label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="attending_ondc_awareness_workshop_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text numeric2decimal disabled" name="turnover" placeholder="Turnover (Previous FY)" id="turnover" maxlength="15" value="{{$udetails['BasicDetail']['turnover']}}" disabled>
								<label class="form-label">Turnover (Previous FY) </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="turnover_error"></span>
						</div>
					
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="gstin_no" placeholder="Enter GST Number" id="gstin_no" value="{{$udetails['BasicDetail']['gstin_no']}}" disabled>
								<label class="form-label">Enter GST Number </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_no_error"></span>
						</div>
						
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								<input type="text" class="input-text disabled" name="pan_no" placeholder="Enter PAN Number" id="pan_no" value="{{$udetails['BasicDetail']['pan_no']}}" disabled>
								<label class="form-label required">Enter PAN Number </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
						</div>
						

						


						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label for="ondc_transaction_type_id" class="form-label required" >Type of Transaction (B2B/B2C)?</label>
								{!! Form::select('ondc_transaction_type_id', array(''=>'Select')+static_common_list($lists?->ondc_types),$selected['ondc_transaction_type_id'] ?? '', ['class' => 'form-select disabled', 'id' => 'ondc_transaction_type_id','disabled' => true]) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ondc_transaction_type_id_error"></span>
						</div>
						<div class="col-lg-8 mb-3">
                            @php
                                $selectedProductCategories = [];
                                if (!empty($msmeData->product_category_id)) {
                                    $selectedProductCategories = json_decode($msmeData->product_category_id, true);
                                }
                            @endphp
							<div class="mb-0">
								<label for="product_category_id" class="form-label required">Select Product Category </label>
								{!! Form::select('product_category_id[]',remove_select_dynamic_common_list($lists?->sub_domains),$selectedProductCategories, ['class' => 'form-select select2 disabled', 'id' => 'product_category_id','multiple' => 'multiple','disabled' => true]) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="product_category_id_error"></span>
						</div>
						<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								{!! Form::select('select_snp', array(''=>'Select SNP')+static_common_list($lists?->yesno),$selected['attending_ondc_awareness_workshop'] ?? '', ['class' => 'form-select disabled', 'id' => 'select_snp','disabled' => true]) !!}
								
								<label for="select_snp" class="form-label required" style="display:none">Do you wish to select an SNP?</label>
							</div>
							
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="select_snp_error"></span>
						</div>		
                        <div id="sndDetailsContainer"></div>				
						<!-- <div class="col-lg-12 mb-3">
							<button id="signup-button" type="submit" class="btn btn-primary mt-3">Submit</button>
						</div> -->
						

					</div>

			</form>
        </div>
    </div>
</div>
    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')
@endsection


