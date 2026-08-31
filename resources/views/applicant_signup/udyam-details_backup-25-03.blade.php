<form id="formId">
    <div class="signup-wrapper-msme mt-0">
        <div class="container">
            <div class="inner-login-wrapper mt-0 signup-form-msme card">
                <div class="card-body pb-5 pt-3 px-4">
                    <div class="logo text-center"> <img src="{{ asset('assets/img-new/msme-logo.png') }}"> </div>
                    <div class="header mb-2 mt-3 text-center">
                        <!--<h6 class="decor-text">MSE TEAM Initiative</h6> -->
                        <h2 class="card-title">{{ __('message.mse') }} Registration</h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <p><span style="color: #db0000ff;">*</span> Fields are mandatory to fill
                                in the form.</p>
                        </div>
                    </div>
                    <!-- <hr/> -->
                    <form method="post" id="formId" class="mt-2">
                        <div class="row">

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Name of Entrepreneur</label>
                                    <input type="text" class="input-text txtOnly" name="entrepreneur_name"
                                        placeholder="Name of Entrepreneur" maxlength="100" id="entrepreneur_name"
                                        value="{{ $udetails['BasicDetail']['EnterpriseName'] }}" disabled>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="entrepreneur_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Name of Enterprise </label>
                                    <input type="text" class="input-text alphaNumericSpace" name="enterprise_name"
                                        maxlength="100" placeholder="Name of Enterprise" id="enterprise_name"
                                        value="{{ $udetails['BasicDetail']['EntrepreneurName'] }}" disabled>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="enterprise_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="organisation_type" maxlength="80"
                                        placeholder="Type of Organization" id="organisation_type"
                                        value="{{ $udetails['BasicDetail']['OrganisationType'] }}" disabled>
                                    <label class="form-label required">Type of Organization </label>

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="organisation_type_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Email </label>
                                    <input type="email" class="input-text" name="email" maxlength="100"
                                        placeholder="Email" id="email"
                                        value="{{ $udetails['BasicDetail']['EmailId'] }}" disabled>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="email_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="address" maxlength="80"
                                        placeholder="Address" id="address"
                                        value="{{ $udetails['BasicDetail']['CommunicationAddress'] }}" disabled>
                                    <label class="form-label required">Address </label>

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="address_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="state_id" maxlength="80"
                                        placeholder="State" id="state_id"
                                        value="{{ $udetails['BasicDetail']['State'] }}" disabled>
                                    <label class="form-label required">State </label>
                                </div>

                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="state_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="district_id" maxlength="80"
                                        placeholder="District" id="district_id"
                                        value="{{ $udetails['BasicDetail']['District'] }}" disabled>
                                    <label class="form-label required">District </label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="district_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="msme_classification"
                                        maxlength="80" placeholder="MSE Classification" id="msme_classification"
                                        value="{{ $udetails['BasicDetail']['EnterpriseType'] }}" disabled>
                                    <label class="form-label required">{{ __('message.mse') }} Classification </label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="msme_classification_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="major_activity" maxlength="80"
                                        placeholder="Major Activity of Unit" id="major_activity"
                                        value="{{ $udetails['BasicDetail']['MajorActivity'] }}" disabled>
                                    <label class="form-label required">Major Activity of Unit </label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="major_activity_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select(
                                        'current_state_business_id',
                                        ['' => 'Select'] + static_common_list($lists?->current_state_business),
                                        '',
                                        ['class' => 'form-select select_value', 'id' => 'current_state_business_id'],
                                    ) !!}
                                    <label for="current_state_business_id" class="form-label required"
                                        style="display:none">What is the current state of your business?</label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="current_state_business_id_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select(
                                        'attending_ondc_awareness_workshop',
                                        ['' => 'Select'] + static_common_list($lists?->yesno),
                                        '',
                                        ['class' => 'form-select select_value', 'id' => 'attending_ondc_awareness_workshop'],
                                    ) !!}

                                    <label for="attending_ondc_awareness_workshop" class="form-label required"
                                        style="display:none">Are you interested in attending ONDC awareness</label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="attending_ondc_awareness_workshop_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text numeric2decimal" name="turnover"
                                        placeholder="Turnover (Previous FY)" id="turnover" maxlength="15"
                                        value="">
                                    <label class="form-label">Turnover (Previous FY) <a class="tooltip-ins"
                                            href="#" data-toggle="tooltip" title="in INR ₹"><i
                                                class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="turnover_error"></span>
                            </div>

                            <!--<div class="col-lg-4 mb-3">
       <div class="mb-0 floating-label-input">
        {!! Form::select('gstin', ['' => 'Select GSTIN'] + static_common_list($lists?->yesno), '', [
            'class' => 'form-select select_value',
            'id' => 'gstin',
        ]) !!}
        
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
        {!! Form::select('pan', ['' => 'Select PAN'] + static_common_list($lists?->yesno), '', [
            'class' => 'form-select select_value',
            'id' => 'pan',
        ]) !!}
        
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
      </div>-->

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="gstin_no"
                                        placeholder="Enter GST Number" id="gstin_no" value="">
                                    <label class="form-label">Enter GST Number </label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="gstin_no_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    <input type="text" class="input-text" name="pan_no"
                                        placeholder="Enter PAN Number" id="pan_no" value="">
                                    <label class="form-label required">Enter PAN Number </label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="pan_no_error"></span>
                            </div>





                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label for="ondc_transaction_type_id" class="form-label required">Type of
                                        Transaction (B2B/B2C)?</label>
                                    {!! Form::select('ondc_transaction_type_id', ['' => 'Select'] + static_common_list($lists?->ondc_types), '', [
                                        'class' => 'form-select',
                                        'id' => 'ondc_transaction_type_id',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="ondc_transaction_type_id_error"></span>
                            </div>
                            <div class="col-lg-8 mb-3">
                                <div class="mb-0">
                                    <label for="product_category_id" class="form-label required">Select Product
                                        Category </label>
                                    {!! Form::select('product_category_id[]', remove_select_dynamic_common_list($lists?->sub_domains), '', [
                                        'class' => 'form-select select2',
                                        'id' => 'product_category_id',
                                        'multiple' => 'multiple',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="product_category_id_error"></span>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select('select_snp', ['' => 'Select SNP'] + static_common_list($lists?->yesno), '', [
                                        'class' => 'form-select',
                                        'id' => 'select_snp',
                                    ]) !!}

                                    <label for="select_snp" class="form-label required" style="display:none">Do you
                                        wish to select an SNP?</label>
                                </div>

                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="select_snp_error"></span>
                            </div>

                            <div id="sndDetailsContainer"></div>

                            <div class="col-lg-12 mb-3">
                                <div class="d-flex align-items-start">
                                    <input type="checkbox" class="mt-1 me-2" checked="checked"
                                        onclick="return false;">
                                    <label for="agree" class="form-check-label">
                                        I/We hereby declare that the details furnished above are true and correct to the
                                        best of my/our knowledge and belief. In the event that any information is found
                                        to be incorrect, misleading, or false, appropriate action may be taken against
                                        me/us as per the applicable laws.

                                        I/We hereby authorize NSIC / ONDC to share relevant details, including
                                        transactions carried out on the ONDC Network, with NSIC for the purpose of
                                        scheme administration and support, in compliance with applicable laws.

                                        I/We further declare that I/we are not onboarded with any SNP as on the date of
                                        operationalization of the initiative and wish to avail the subsidy and benefits
                                        under the TEAM Initiative.

                                        I/We also declare that I/we have not availed similar assistance under any other
                                        Central or State Government schemes/programmes.

                                        I/We confirm that I/we have read and understood the Privacy Policy, Terms and
                                        Conditions, Disclaimer, Data Sharing Policy, Operating Guidelines, and SOPs of
                                        the TEAM Initiative and agree to abide by the same.

                                        NSIC reserves the right to amend or modify the SOPs of the TEAM Initiative, with
                                        the approval of the Ministry of MSME, as per policy and procedural requirements,
                                        as and when warranted, without prior notice.
                                    </label>
                                </div>
                                <span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <button id="signup-button" type="submit"
                                    class="btn btn-primary mt-3">Submit</button>
                            </div>


                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(".form-control, .form-select, .input-text").on("focus", function() {
        let fieldId = $(this).attr('id');

        if (fieldId == 'state_ids') {
            $('#state_id' + '_error').text('');
        }
        $('#' + fieldId + '_error').text('');
    });


    $(document).on("select2:open", ".select2", function() {
        let fieldId = $(this).attr('id');
        if (fieldId) {
            $('#' + fieldId + '_error').text('');
        }
    });

    $('.select_value').on('change', function() {

        var valueId = $(this).val().trim();

        // get the select ID
        var selectId = $(this).attr('id');

        // find the matching label using for="..."
        var label = $('label[for="' + selectId + '"]');

        if (valueId == 1 || valueId == 0) {
            label.show();
        } else {
            label.hide();
        }
    });


    $(".numeric2decimal").on("input", function() {
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

    /*$('#div_gstin_no').hide();
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
    });*/


    $('#select_snp').on('change', function() {

        var select_snp = $("#select_snp").val();
        var state_code = "{{ $udetails['BasicDetail']['LG_ST_Code'] }}";
        var ondc_transaction_type_id = $("#ondc_transaction_type_id").val();
        var sub_domain = $("#product_category_id").val();

        /*if(select_snp ==1 && (ondc_transaction_type_id =='' || sub_domain.length==0)){*/

        if (select_snp == 1) {

            /* alert('Please select type of transaction? (B2B/B2C) & product category');
            $("#select_snp").val('');
            return false; */

            if (state_id === '') {
                alert('Please select State');
                $("#state_ids").focus();
                $("#select_snp").val('');
                return false;
            }

            if (ondc_transaction_type_id === '') {
                alert('Please select Type of Transaction (B2B / B2C)');
                $("#ondc_transaction_type_id").focus();
                $("#select_snp").val('');
                return false;
            }

            if (sub_domain.length === 0) {
                alert('Please select Product Category');
                $("#product_category_id").focus();
                $("#select_snp").val('');
                return false;
            }
        }

        if (select_snp == 1) {
            $.ajax({
                //url: '{{ route('snp-details') }}',
                url: '{{ route('snp-select-details') }}',
                type: 'GET',
                data: {
                    state_code: state_code,
                    ondc_transaction_type_id: ondc_transaction_type_id,
                    sub_domain: sub_domain
                },
                success: function(response) {
                    $("#cover-spin").hide();
                    if (select_snp == 1) {
                        $('#sndDetailsContainer').html(response).show();
                    } else {
                        $('.snp_id').prop('checked', false);
                        $('#sndDetailsContainer').html("").hide();
                    }
                },
                error: function() {
                    $("#cover-spin").hide();
                    alert('Failed to load content.');
                }
            });
        } else {

            $('#sndDetailsContainer').html("");

        }
    });


    /*$('#product_category_id').on('change', function () {
		
			var sub_domain=$("#product_category_id").val();
			alert(sub_domain);
			var selectId = $(this).attr('id');
			if(selectId){
				var label = $('label[for="' + selectId + '"]');
				label.show();
			}
			
			//if(sub_domain.length ==0){
				//alert('Please select product category');
				//$("#select_snp").val('');
				//return false;
			//}
			
			var select_snp=$("#select_snp").val();
			var state_code="{{ $udetails['BasicDetail']['LG_ST_Code'] }}";
			var ondc_transaction_type_id=$("#ondc_transaction_type_id").val();
			$.ajax({
				url: '{{ route('snp-details') }}',
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
			
    });*/


    /*$('#select_snp').on('change', function () {
    	var select_snp=$(this).val();
    	if(select_snp==1){
    		$('#sndDetailsContainer').show();
    	}else{
    		$('#sndDetailsContainer').hide();
    		 $('.snp_id').prop('checked', false);
    	}
    });*/



    $("#formId").on("submit", function(event) {
        //$(".form-error font-12 font-12 font-12").html('');
        event.preventDefault();

        var formData = $("#formId").serializeArray();
        formData.push({
            name: 'udyam_no',
            value: $("#udyam_no").val()
        });
        formData.push({
            name: 'mobile',
            value: $("#mobile").val()
        });

        var method = 'POST';
        var url = "{{ url('/applicant') }}";
        $("#cover-spin").show();
        $.ajax({
            url: url,
            type: method,
            data: formData,
            success: function(res) {
                if (res.status) {
                    $("#cover-spin").hide();
                    toastr.success('Your account has been created successfully.');

                    setTimeout(function() {
                            redirect("{{ url('login') }}")
                        },
                        2000);

                } else if (!res.status && res.errors.length == 0) {
                    failure(res);
                    $("#cover-spin").hide();
                    $("#signup-button").attr("class", "btn btn-primary mt-3");
                    $("#signup-button").html('Submit');
                    return;
                } else if (!res.status && res.errors) {
                    applyValidationErrors(res);
                    $("#cover-spin").hide();
                    $("#signup-button").attr("class", "btn btn-primary mt-3");
                    $("#signup-button").html('Submit');
                    return;
                } else {
                    failure(res);
                    enableSubmit();
                }
            },

            error: function(xhr) {
                applyValidationErrors(xhr.responseJSON);
                $("#cover-spin").hide();
                $("#signup-button").attr("class", "btn btn-primary mt-3");
                $("#signup-button").html('Submit');
                return;
            }

        });

    });
</script>
