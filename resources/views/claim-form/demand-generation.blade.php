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
							<label class="form-label required" for="snp_id">BNP ID</label>
							<input type="text" class="form-control" id="snp_id" name="snp_id" placeholder="Enter BNP ID" readonly required
								@if(isset($claimId) && !empty($claimDetails['snp_id'])) value="{{$claimDetails['snp_id']}}" @else value="{{auth()->user()->username}}@endif">
							<span class="text-danger form-error" id="snp_id_error"></span>
						</div>
						
						
						<div class="form-group col-md-4">
							<label class="form-label required" for="seller_provider_id">MSME Seller Provider ID</label>
							<input type="text" class="form-control" id="seller_provider_id" name="seller_provider_id" placeholder="Enter Seller Provider ID" readonly required
							 @if(isset($claimId) && !empty($claimDetails['seller_provider_id'])) value="{{$claimDetails['seller_provider_id']}}" @else value="{{$msmeDetails->seller_provider_id}} @endif">
							<span class="text-danger form-error" id="seller_provider_id_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="campaign_period">Campaign Period</label>
							{!! Form::select('campaign_period',campaign_period(),$claimDetails['campaign_period'] ?? null, ['class' => 'form-select', 'id' => 'campaign_period', 'required' => 'required']) !!}
							<span class="text-danger form-error" id="campaign_period_error"></span>
						</div>

						<div class="form-group col-md-4 campaign_duration" style="display:none;">
							<label class="form-label required" for="campaign_duration">Campaign Duration</label>
							<input type="text" class="form-control integer" id="campaign_duration" name="campaign_duration" placeholder="Enter Campaign Duration" maxlength="10" required
							@if(isset($claimId) && !empty($claimDetails['campaign_duration'])) value="{{$claimDetails['campaign_duration']}}" @endif>
							<span class="text-danger form-error" id="campaign_duration_error"></span>
						</div>

						<div class="form-group col-md-4">
							<label class="form-label required" for="number_of_orders">Number of Orders</label>
							<input type="text" class="form-control integer" id="number_of_orders" name="number_of_orders" placeholder="Enter Number Of Orders" maxlength="6" required
							@if(isset($claimId) && !empty($claimDetails['number_of_orders'])) value="{{$claimDetails['number_of_orders']}}" @endif>
							<span class="text-danger form-error" id="number_of_orders_error"></span>
						</div>
					
						<div class="form-group col-md-4">
							<label class="form-label required" for="ondc_order_id1"> ONDC Order ID</label>
							
							<input type="text" class="form-control txtnumerichypenSlashUnederscoreComma" id="ondc_order_id1" name="ondc_order_id1" placeholder="Enter ONDC Order ID 1" maxlength="40" required
							@if(isset($claimId) && !empty($claimOrders[0]->ondc_order_id)) value="{{$claimOrders[0]->ondc_order_id}}" @endif>
							<span class="text-danger form-error" id="ondc_order_id1_error"></span>
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
						<div class="form-group col-md-8"> </div>
						<div class="form-group col-md-4 m-0 p-0 ca-cert-download"><a href="{{ url('storage/app/ca_certificate.pdf') }}" download=""> <i class="bi bi-download"></i>CA Certificate Format</a></div>
						
						

						
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

		$(document).ready(function() {
			$('#campaign_period').on('change', function() {
				let value = $(this).val();

				if (value) {
					// show the duration field
					$('.campaign_duration').show();
				} else {
					// hide if nothing selected
					$('.campaign_duration').hide();
					$('#campaign_duration').val(''); // clear value
				}
			});

			// trigger once on page load (in case edit mode has value)
			$('#campaign_period').trigger('change');
		});



        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();
            @isset($claimId)
                var method = 'POST';
                var url = "{{ url('/claim-update/' . $claimId) }}";
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
