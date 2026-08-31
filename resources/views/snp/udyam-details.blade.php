<div class="signup-wrapper-msme mt-0">
	<div class="container">
		<div class="inner-login-wrapper mt-0 signup-form-msme card">
			<div class="card pb-5 pt-3 px-4">
	
				<form id="formId" class="mt-2">
					<div class="row">
	
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Name of entrepreneur</label>
								<input type="text" class="form-control txtOnly" name="entrepreneur_name" placeholder="Name of entrepreneur"  maxlength="100" id="entrepreneur_name" value="{{$udetails['BasicDetail']['EnterpriseName']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="entrepreneur_name_error"></span>
						</div>

						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Name of enterprise </label>
								<input type="text" class="form-control alphaNumericSpace" name="enterprise_name" maxlength="100" placeholder="Name of enterprise" id="enterprise_name" value="{{$udetails['BasicDetail']['EntrepreneurName']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="enterprise_name_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Type of organization </label>
								<input type="text" class="form-control" name="organisation_type" maxlength="80" placeholder="Type of organization" id="organisation_type" value="{{$udetails['BasicDetail']['OrganisationType']}}" disabled>
								<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="organisation_type_error"></span>
							</div>
						</div>

						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Email </label>
								<input type="email" class="form-control" name="email" maxlength="100" placeholder="Email" id="email" value="{{$udetails['BasicDetail']['EmailId']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="email_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
							    <label class="form-label required">Address </label>
								<input type="text" class="form-control" name="address" maxlength="80" placeholder="Address" id="address" value="{{$udetails['BasicDetail']['CommunicationAddress']}}" disabled>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="address_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
							    <label class="form-label required">State </label>
								<input type="text" class="form-control" name="state_id" maxlength="80" placeholder="State" id="state_id" value="{{$udetails['BasicDetail']['State']}}" disabled>
							</div>
							
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="state_id_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">District </label>
								<input type="text" class="form-control" name="district_id" maxlength="80" placeholder="District" id="district_id" value="{{$udetails['BasicDetail']['District']}}" disabled>
								
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="district_id_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">MSME Classification  </label>
								<input type="text" class="form-control" name="msme_classification" maxlength="80" placeholder="MSME Classification" id="msme_classification" value="{{$udetails['BasicDetail']['EnterpriseType']}}" disabled>
							</div>
							
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="msme_classification_error">
								
							</span>
							<span class="text-danger">
								@if(strtolower($udetails['BasicDetail']['EnterpriseType']) === 'medium')
									Medium-scale MSMEs are not eligible for registration on the TEAMS Portal.
								@endif
							</span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Major activity of unit </label>
								<input type="text" class="form-control" name="major_activity" maxlength="80" placeholder="Major activity of unit" id="major_activity" value="{{$udetails['BasicDetail']['MajorActivity']}}" disabled>
							</div>
								
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="major_activity_error">
							</span>
							
							<span class="text-danger">
								@if(strtolower($udetails['BasicDetail']['MajorActivity']) === 'trading')
									Registration on the TEAMS Portal is currently restricted for MSMEs engaged in Trading activities.
								@endif
							</span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label for="current_state_business_id" class="form-label required">What is the current state of your business?</label>
								{!! Form::select('current_state_business_id', array(''=>'Select')+static_common_list($lists?->current_state_business),'', ['class' => 'form-select', 'id' => 'current_state_business_id']) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="current_state_business_id_error"></span>
						</div>
						
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label for="attending_ondc_awareness_workshop" class="form-label required">Are you interested in attending ONDC awareness</label>
								{!! Form::select('attending_ondc_awareness_workshop', array(''=>'Select')+static_common_list($lists?->yesno),'', ['class' => 'form-select', 'id' => 'attending_ondc_awareness_workshop']) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="attending_ondc_awareness_workshop_error"></span>
						</div>
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label">Turnover (previous FY) </label>
								<input type="text" class="form-control numeric2decimal" name="turnover" placeholder="Turnover (previous FY)" id="turnover" maxlength="15">
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="turnover_error"></span>
						</div>
						
						<!--<div class="col-lg-4 mb-3">
							<div class="mb-0 floating-label-input">
								 {!! Form::select('gstin', array(''=>'Select GSTIN')+static_common_list($lists?->yesno),'', ['class' => 'form-select select_value', 'id' => 'gstin']) !!} 
								 
								 <label for="gstin" class="form-label required" style="display:none" >Select GSTIN</label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_error"></span>
						</div>
			
						<div class="col-lg-4 mb-3" id="div_gstin_no">
							<div class="mb-0 floating-label-input">
								<input type="text" class="form-control" name="gstin_no" placeholder="Enter Gst Number" id="gstin_no" value="">
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
								<input type="text" class="form-control" name="pan_no" placeholder="Enter Pan Number" id="pan_no" value="">
								<label class="form-label required">Enter Pan Number </label>
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
						</div>-->
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label">Enter GST Number </label>
								<input type="text" class="form-control" name="gstin_no" placeholder="Enter GST Number" id="gstin_no" value="">
								
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="gstin_no_error"></span>
						</div>
						
						
						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label class="form-label required">Enter PAN Number </label>
								<input type="text" class="form-control" name="pan_no" placeholder="Enter PAN Number" id="pan_no" value="">
								
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="pan_no_error"></span>
						</div>
						

						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label for="product_category_id" class="form-label required">Select Product Category </label>
								{!! Form::select('product_category_id[]',remove_select_dynamic_common_list($lists?->sub_domains),json_decode($detail->product_category_id, true), ['class' => 'form-select select2 form-control', 'id' => 'product_category_id','multiple' => 'multiple']) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="product_category_id_error"></span>
						</div>


						<div class="col-lg-4 mb-3">
							<div class="mb-0">
								<label for="ondc_transaction_type_id" class="form-label required" >Type of transaction (B2B/B2C)?</label>
								{!! Form::select('ondc_transaction_type_id', array(''=>'Select')+static_common_list($lists?->ondc_types),$detail->ondc_transaction_type_id, ['class' => 'form-select', 'id' => 'ondc_transaction_type_id']) !!}
							</div>
							<span class="text-danger form-error font-12 font-12 font-12 mb-3" id="ondc_transaction_type_id_error"></span>
						</div>
						
						<div class="col-lg-12 mb-3">
							<div class="d-flex align-items-start">
								<input type="checkbox" class="mt-1 me-2" checked="checked" onclick="return false;">
								<label for="agree" class="form-check-label">
								I confirm that the information provided by me is my own and I consent to be registered under the MSME TEAM Initiative Scheme.
								</label>
							</div>
							<span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
						</div>
						
						@if(strtolower($udetails['BasicDetail']['MajorActivity']) === 'trading' || strtolower($udetails['BasicDetail']['EnterpriseType']) === 'medium')
							
						@else
							<div class="col-lg-12 mb-3">
								<button id="signup-button" type="submit" class="btn btn-primary mt-3">Submit</button>
							</div>
						@endif
						
						

					</div>

				</form>
			</div>
		</div>
	</div>
</div>

<script>

$(".form-control, .form-select, .input-text").on("focus", function () {
	let fieldId = $(this).attr('id');
	$('#' + fieldId + '_error').text('');
});

$(document).on("select2:open", ".select2", function () {
	let fieldId = $(this).attr('id');
	if (fieldId) {
		$('#' + fieldId + '_error').text('');
	}
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

	$('#product_category_id').select2({
		placeholder: "Select Product Category",
		allowClear: true
	});
	
	
	
	/*$("#formId").on("submit", function (event) {
		
		event.preventDefault();
		var formData=$("#formId").serializeArray();   
		formData.push({name: 'udyam_no', value: $("#udyam_no").val()});
        formData.push({name: 'mobile', value: $("#mobile").val()});
		var method = 'POST';
		var url = "{{ url('/applicant-update/'. $detail->id) }}";

		var requestData = {
			url: url,
			method: method,
			body: formData
		};

		sendRequest(requestData, "{{ url('mse-to-be-validated') }}");
	});*/

    

	
	
	$("#formId").on("submit", function (event) {
			event.preventDefault();
            
		    var formData=$("#formId").serializeArray();
			formData.push({name: 'udyam_no', value: $("#udyam_no").val()});
            formData.push({name: 'mobile', value: $("#mobile").val()});
            			
            var method = 'POST';
            var url = "{{ url('/applicant-update/'. $detail->id) }}";
            $("#cover-spin").show();
			 $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    success: function (res) { 						
                        if (res.status) {
                            $("#cover-spin").hide();
                            toastr.success('MSME Registration Successfully Completed');
						    
							setTimeout(function(){
                                redirect("{{ url('mse-to-be-validated') }}")},
                                2000);
                            
                        }
						 else if (!res.status && res.errors.length == 0) {
							failure(res);
							$("#cover-spin").hide(); 
							$("#signup-button").attr("class", "btn btn-primary mt-3");
							$("#signup-button").html('Submit');
							return;
						}

						else if (!res.status && res.errors) { 
							applyValidationErrors(res);
							$("#cover-spin").hide();    
							$("#signup-button").attr("class", "btn btn-primary mt-3");
							$("#signup-button").html('Submit');
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
						$("#signup-button").html('Submit');
						return;
					}	
                    
                });
			
        });
</script>