@extends('components.admin.content-layout')
@section('action-header')
<style>
	input[readonly],
textarea[readonly],
select[readonly] {
    background-color: #e9ecef !important; /* light grey (Bootstrap disabled color) */
    cursor: not-allowed;
    pointer-events: none; /* stops focus / caret */
    opacity: 1; /* keep text readable */
}
</style>
    <div class="btn-group drop-btn">

        @include('components.admin.buttons.back-button')

    </div>
@endsection
@section('card-content')
    <div class="card-body pt-1">

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between">
                <h5 class="m-0 box-heading heading-1">
                    @if (!empty($row['id']))
                        Update {{$title}}
                    @else
                        Add {{$title}}
                    @endif

                </h5>
            </div>
		
            <div class="card card-body">
                <form id="formId">
					<input type="hidden" class="form-control" id="claim_type_id" name="claim_type_id" value="{{$claimSlug}}">
					@if(isset($claimId))
					<input type="hidden" class="form-control" id="id" name="id" value="{{$claimId}}">
					@endif
					<div class="row g-3">
						
                        <div class="form-group col-md-4">
							<label class="form-label required" for="campaign_period">Financial Year</label>
							{!! Form::select('campaign_period',financial_year(),$claimDetails['campaign_period'] ?? null, ['class' => 'form-select', 'id' => 'campaign_period', 'required' => 'required']) !!}
							<span class="text-danger form-error" id="campaign_period_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="campaign_duration">Campaign Duration</label>
                            {!! Form::select('campaign_duration',quaterly(),$claimDetails['campaign_duration'] ?? null, ['class' => 'form-select', 'id' => 'campaign_duration', 'required' => 'required']) !!}
							<span class="text-danger form-error" id="campaign_duration_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="msme_name">MSME Name</label>
							<input type="text" class="form-control" id="msme_name" name="msme_name" placeholder="Enter MSME Name" readonly required
							@if(isset($claimId) && !empty($claimDetails['msme_name'])) value="{{$claimDetails['msme_name']}}" @else value="{{$msmeDetails->entrepreneur_name}} @endif">
							<span class="text-danger form-error" id="msme_name_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="msme_udyam_number">MSME Udyam Number</label>
							<input type="text" class="form-control" id="msme_udyam_number" name="msme_udyam_number" placeholder="Enter MSME Udyam Number" readonly  required
							@if(isset($claimId) && !empty($claimDetails['msme_udyam_number'])) value="{{$claimDetails['msme_udyam_number']}}" @else  value="{{$msmeDetails->udyam_no}} @endif">
							<span class="text-danger form-error" id="msme_udyam_number_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="team_registration_id">MSME TEAM Registration ID</label>
							<input type="text" class="form-control" id="team_registration_id" name="team_registration_id" placeholder="Enter TEAM Registration ID" readonly required
							 @if(isset($claimId) && !empty($claimDetails['team_registration_id'])) value="{{$claimDetails['team_registration_id']}}" @else value="{{$msmeDetails->team_id}} @endif" >
							<span class="text-danger form-error" id="team_registration_id_error"></span>
						</div>
						
						<div class="form-group col-md-4">
							<label class="form-label required" for="msme_email">MSME Email ID</label>
							<input type="text" class="form-control" id="msme_email" name="msme_email" placeholder="Enter Email ID" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_email'])) value="{{$claimDetails['msme_email']}}" @else value="{{$msmeDetails->email}} @endif">
							<span class="text-danger form-error" id="msme_email_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="msme_mobile">MSME Mobile Number</label>
							<input type="text" class="form-control" id="msme_mobile" name="msme_mobile" placeholder="Enter Mobile" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_mobile'])) value="{{$claimDetails['msme_mobile']}}" @else value="{{$msmeDetails->mobile}} @endif">
							<span class="text-danger form-error" id="msme_mobile_error"></span>
						</div>

                        <div class="form-group col-md-12">
							<label class="form-label required" for="msme_address">MSME Address</label>
							<input type="text" class="form-control" id="msme_address" name="msme_address" placeholder="Enter Address" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_address'])) value="{{$claimDetails['msme_address']}}" @else value="{{$msmeDetails->address}} @endif">
							<span class="text-danger form-error" id="msme_address_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="msme_state">MSME State</label>
							<input type="text" class="form-control" id="msme_state" name="msme_state" placeholder="Enter State" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_state'])) value="{{$claimDetails['msme_state']}}" @else value="{{$msmeDetails->state}} @endif">
							<span class="text-danger form-error" id="msme_state_error"></span>
						</div>

                        <div class="form-group col-md-4">
							<label class="form-label required" for="msme_district">MSME District</label>
							<input type="text" class="form-control" id="msme_district" name="msme_district" placeholder="Enter District" readonly required
							 @if(isset($claimId) && !empty($claimDetails['msme_district'])) value="{{$claimDetails['msme_district']}}" @else value="{{$msmeDetails->district}} @endif">
							<span class="text-danger form-error" id="msme_district_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="design_type">Design Type</label>
                            {!! Form::select('design_type',designType(),$claimDetails['design_type'] ?? null, ['class' => 'form-select', 'id' => 'design_type', 'required' => 'required']) !!}
							<span class="text-danger form-error" id="design_type_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="date_of_design_request">Date of Design Request</label>
							<input type="date" class="form-control" id="date_of_design_request" name="date_of_design_request" required max="{{ date('Y-m-d') }}"
							@if(isset($claimId) && !empty($claimDetails['date_of_design_request'])) value="{{$claimDetails['date_of_design_request']}}" @endif>
							<span class="text-danger form-error" id="date_of_design_request_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="date_of_design_delivery">Date of Design Delivery</label>
							<input type="date" class="form-control" id="date_of_design_delivery" name="date_of_design_delivery" required max="{{ date('Y-m-d') }}"
							@if(isset($claimId) && !empty($claimDetails['date_of_design_delivery'])) value="{{$claimDetails['date_of_design_delivery']}}" @endif>
							<span class="text-danger form-error" id="date_of_design_delivery_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="design_cost">Design Cost</label>
							<input type="text" class="form-control integer" id="design_cost" name="design_cost" placeholder="Enter Design Cost" maxlength="6" required
							@if(isset($claimId) && !empty($claimDetails['design_cost'])) value="{{$claimDetails['design_cost']}}" @endif>
							<span class="text-danger form-error" id="design_cost_error"></span>
						</div>
					
						<div class="form-group col-md-4">
							<label class="form-label required" for="subsidy_amount">Subsidy Amount</label>
							
							<input type="text" class="form-control" id="subsidy_amount" name="subsidy_amount" placeholder="Amount" maxlength="40" required readonly
							@if(isset($claimId) && !empty($claimDetails['subsidy_amount'])) value="{{$claimDetails['subsidy_amount']}}" @endif>
							<span class="text-danger form-error" id="subsidy_amount_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required d-block" for="single_use_plastic">Single-Use Plastic Used</label>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="single_use_plastic" id="single_use_plastic_yes" value="1">
								<label class="form-check-label" for="single_use_plastic_yes">Yes</label>
							</div>

							<div class="form-check form-check-inline">
								<input class="form-check-input" type="radio" name="single_use_plastic" id="single_use_plastic_no" value="0">
								<label class="form-check-label" for="single_use_plastic_no">No</label>
							</div>

							<span class="text-danger form-error" id="single_use_plastic_error"></span>
						</div>
						
						<div class="form-group col-md-6">
							<label class="form-label" for="description">Description of Design Work</label>
							
							<input type="text" class="form-control" id="description" name="description" placeholder="Enter Description of Design Work" 
							@if(isset($claimId) && !empty($claimDetails['description'])) value="{{$claimDetails['description']}}" @endif>
							<span class="text-danger form-error" id="description_error"></span>
						</div>
						
						<div class="form-group col-md-6">
							<label class="form-label" for="packaging_remarks">Remarks</label>
							
							<input type="text" class="form-control" id="packaging_remarks" name="packaging_remarks" placeholder="Enter Remarks if any" 
							@if(isset($claimId) && !empty($claimDetails['packaging_remarks'])) value="{{$claimDetails['packaging_remarks']}}" @endif>
							<span class="text-danger form-error" id="packaging_remarks_error"></span>
						</div>
						
						
						@foreach ($claimDocuments as $key => $claimDocument)
							<div class="form-group col-md-4">
								<label class="form-label required">{{ $claimDocument->document_category_name }}</label><a class="tooltip-ins"  href="#" data-toggle="tooltip" title="{{ $claimDocument->informations }}"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
								<input type="file" name="{{ $claimDocument->document_category_slug }}"
									   id="{{ $claimDocument->document_category_slug }}"
									   document_category_id="{{ $claimDocument->document_category_id }}"
									   class="form-control upload-pdf" accept="application/pdf" @empty($claimId) required @endempty>
									   
									  

								<input type="hidden" name="claim_documents[]"
									   id="claim_documents_{{ $claimDocument->document_category_id }}" @if(isset($claimId) && !empty($claimDocument->file_upload_id)) value="{{$claimDocument->document_category_id}}|{{$claimDocument->file_upload_id}}" @endif />
								<span class="text-danger form-error" id="{{ $claimDocument->document_category_slug }}_error"></span>
								@isset($claimDocument->file_system_name)
								<a href="{{ url('storage/app/uploads/claim-documents/'.$claimDocument->file_system_name) }}" target="_blank" >
                                    {{$claimDocument->file_name}}
                                </a>
								@endisset

							</div>
							
							
						@endforeach
						
						
						

						
							<div class="form-group col-md-12 form-check mt-4">
								<input class="form-check-input" type="checkbox" name="eligibility" id="eligibility" required
								@if(isset($claimId)){{ $claimDetails['declaration_authorization'] == 1 ? 'checked' : '' }}@endif>
								<input type="hidden" name="declaration_authorization" id="declaration_authorization" value="1">
								<b>Declaration:</b>
								<label class="form-check-label" for="eligibility">
									I/ We hereby declare that the details furnished above are true and correct to the best of my/ 
									our knowledge and belief. If the above information is found to be incorrect / misleading/ 
									false, appropriate action as per the laws may be taken against me/ us. I/ We hereby authorize
									NSIC/ ONDC to share the relevant details for the purpose of scheme administration and 
									support, in compliance with applicable laws. I / We hereby declare that I / We have read the 
									Privacy Policy, Terms and Conditions, Disclaimer and Data Sharing Policy, Operating 
									guidelines of TEAM Initiative, SOPs of TEAM Initiative and abide by it. NSIC reserves the 
									right to change/ amend the SOPs with the approval of the Ministry of MSME as per the 
									policy & procedural requirement as and when warranted and without giving any notice.”
								</label>
							</div>
						
						<div class="form-group col-md-12 mt-4">
							<button type="submit" class="btn btn-primary">Submit your Claim</button>
						</div>
					</div>
				</form>

            </div>
        </div>

    </div>

@section('js')

    <script>

		document.addEventListener('DOMContentLoaded', function() {
			//calculate subsidy amount
			const designCostInput = document.getElementById('design_cost');
			const subsidyAmountInput = document.getElementById('subsidy_amount');

			designCostInput.addEventListener('input', function() {
				let designCost = parseFloat(designCostInput.value) || 0;
				let subsidy = designCost * 0.2;

				// Cap the subsidy at 2000
				if (subsidy > 2000) {
					subsidy = 2000;
				}

				// Display result rounded to 2 decimal places
				subsidyAmountInput.value = subsidy.toFixed(2);
			});
			//single use plastic concept
			const yesRadio = document.getElementById('single_use_plastic_yes');
			const noRadio = document.getElementById('single_use_plastic_no');
			const submitBtn = document.querySelector('button[type="submit"]');

			function toggleSubmitButton() {
				if (yesRadio.checked) {
					submitBtn.disabled = true;
					submitBtn.classList.add('btn-secondary');
					submitBtn.classList.remove('btn-primary');
					document.getElementById('single_use_plastic_error').innerText = 'Claims using single-use plastic are not allowed.';
				} else {
					submitBtn.disabled = false;
					submitBtn.classList.add('btn-primary');
					submitBtn.classList.remove('btn-secondary');
					document.getElementById('single_use_plastic_error').innerText = '';
					
				}
			}

			yesRadio.addEventListener('change', toggleSubmitButton);
			noRadio.addEventListener('change', toggleSubmitButton);
		});


        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/claim-update/' . $id) }}";
            @else
                var method = 'POST';
                var url = "{{ url('/claims') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: formData,
            };

            sendRequest(requestData, "{{ url('claims') }}");
        });




        $('.upload-pdf').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            var document_category_id = $(this).attr('document_category_id');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            //formData.append('application_id', $('#application_id').val());
            formData.append('document_category_id', document_category_id);
            formData.append('file', file);

            $.ajax({
                url: '{{ url('upload-claim-documents') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#claim_documents_' + document_category_id).val(response.data.claim_document);
                        //$('#upload_file').val('');
                    }
                },
                error: function(xhr, status, error) {
                    //console.error('Upload Error:', error);
                    toastr.error('File upload failed.');
                }
            });
        });
    </script>
@endsection
@endsection
