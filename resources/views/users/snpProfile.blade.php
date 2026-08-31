@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">SNP Details</h6>
					
				</div>
				<div class="card-body">
					<form id="formId">
					<div class="row g-3">
						@php
							$basic_details = [
								'organization_id' => 'Organization Id',
								'organization_name' => 'Organization Name',
								'brand_name' => 'App Name / Brand name',
								'snp_name' => 'Authorized Person Name',
								'designation' => 'Designation',
								'email' => 'Email',
								'alternate_email' => 'Alternate Email ID',
								'mobile' => 'Mobile No',
								'alternate_mobile' => 'Contact No (Land line)',
							];

							$bank_details = [
								'bank_name' => 'Bank Name',
								'ifsc_code' => 'IFSC Code',
								'account_no' => 'Account No',
								'pan' => 'PAN',
								'gst_number' => 'GST Number',
							];

							$commercial_details = [
								'commercial_model' => 'Commercial Model',
								'live_seller' => 'No of live sellers on boarded',
								'date_of_going_live_on_ondc' => 'Date of going live on ONDC for the SNP',
								'no_of_transactions_done' => 'No of transactions done till date',
								'short_description' => 'Short Description',
							];
							
						@endphp

							<h4>Basic Details</h4>
							<div class="row g-3">
							@foreach ($basic_details as $key => $label)
								@php
									$editable_fields = ['email', 'alternate_email', 'mobile', 'alternate_mobile'];
									$value = $row[$key] ?? null;
									$isEditable = in_array($key, $editable_fields);
								@endphp

								@if (!empty($value))
									<div class="form-group col-md-4">
										<label class="form-label">{{ $label }}</label>
										<input type="text" class="form-control" name="{{ $key }}" id="{{ $key }}"
											value="{{ $value }}" @unless($isEditable) disabled @endunless>
											
									</div>
								@endif
							@endforeach
							
							@if (!empty($row['authorizedcertificate']))
								<div class="form-group col-md-4">
									<label class="form-label">Authorized Certificate</label>
									<a href="{{ url($row['authorizedcertificate']) }}" download="{{ $row['authorized_certificate_document_original_name'] }}" >
										View Authorized Certificate
									</a>
								</div>
							@endif
							</div>
							<h4>Configuration Details</h4>
							<div class="row g-3">							
								<div class="form-group col-md-4">
									<label class="form-label">{{ $label }}</label>
									<label class="form-label">Domain</label>
									{!! Form::select('domain[]', static_common_list($details?->domain), json_decode($row['domain'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'domain', 'multiple' => 'multiple', 'onchange' => 'getSubdomains();']) !!}
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">Sub-domain</label>
									{{ Form::select('sub_domain[]', remove_select_dynamic_common_list($details?->subdomains), json_decode($row['sub_domain'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'sub_domain', 'multiple' => 'multiple']) }}									
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">Transaction type</label>
									{{ Form::select('transaction_type[]', static_common_list($details?->ondc_types), json_decode($row['transaction_type'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'transaction_type', 'multiple' => 'multiple']) }}									
								</div>
								<div class="form-group col-md-4">
									<label class="form-label">What are the States and UTs you cover ?</label>
									{{ Form::select('state_id[]', remove_select_dynamic_common_list($details?->states), json_decode($row['state_id'] ?? '[]', true), ['class' => 'form-select select2', 'id' => 'state_id', 'multiple' => 'multiple']) }}								
								</div>								
							</div>
							<h4>Bank Details</h4>
							<div class="row g-3">
							@foreach ($bank_details as $key => $label)
								@php
									$value = $row[$key] ?? null;
								@endphp

								@if (!empty($value))
									<div class="form-group col-md-4">
									<label class="form-label">{{ $label }}</label>
										<input type="text" class="form-control" name="{{ $key }}" id="{{ $key }}"
											value="{{ $value }}">
									</div>
								@endif
							@endforeach
							@if (!empty($row['cancelled_cheque']))
								<div class="form-group col-md-4">
									<label class="form-label">Cancelled Cheque</label>
									<a href="{{ url($row['cancelled_cheque']) }}" download="{{ $row['cancelled_cheque_document_original_name'] }}" >
										View Cancelled Cheque
									</a>
									<div class="input-box">
										<label class="form-label required">Upload New</label>
										
										<input type="file" class="form-control" id="cancelled_cheque"/>

										<small>1. Image size should be less than 50kb.<br/>
											2. Only files with extension jpg, jpeg, png ,pdf are allowed</small>
										<div class="cancelled_cheque_file_link">
											
										</div>
										<span class="text-danger form-error" id="cancelled_cheque_document_error"></span>
										<input type="hidden" name="cancelled_cheque_document" id="cancelled_cheque_document" value=""/> 
										<div class="progress-cancelled_cheque progress-bg" style="display:none;">
											<div id="loader-cancelled_cheque" style=""></div>
											<div class="progress-bar"></div>
										</div>  
									</div>
								</div>
							@endif
							</div>
							<h4>Commercial Details</h4>
							<div class="row g-3">
							@foreach ($commercial_details as $key => $label)
								@php
									$editable_fields = ['live_seller', 'no_of_transactions_done'];
									$value = $row[$key] ?? null;
									$isEditable = in_array($key, $editable_fields);
								@endphp

								@if (!empty($value))
									<div class="form-group col-md-4">
										<label class="form-label">{{ $label }}</label>
										<input type="text" class="form-control" name="{{ $key }}" id="{{ $key }}"
											value="{{ $value }}" @unless($isEditable) disabled @endunless>
									</div>
								@endif
							@endforeach
							@if (!empty($row['commercial_model_path']))
								<div class="form-group col-md-4">
									<label class="form-label">Commercial Model Document</label>
									<a href="{{ url($row['commercial_model_path']) }}" download="{{ $row['commercial_model_document_original_name'] }}" >
										View Commercial Model
									</a>
								</div>
							@endif
							@if (!empty($row['description']))
								<div class="form-group col-md-4">
									<label class="form-label">Description</label>
									<a href="{{ url($row['description']) }}" download="{{ $row['description_document_original_name'] }}" >
										View Description
									</a>
								</div>
							@endif
					</div>
					<div class="form-action mt-3 mb-3">
								@include('components.admin.buttons.submit-button')								
								<a href="javascript:void(0);" class="btn btn-warning wave-effect has-ripple" onclick = "javascript:history.back(-1);">Cancel</a>
							</div>	
					</form>
				</div>
					
				</div>
			</div>
		</div>
	</div>
</div>
@section('js');

	<script>
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

		$("#formId").on("submit", function (event) {
			event.preventDefault(); 			
			
			var formData=$("#formId").serializeArray();
			
			//To set formdata in case of update profile
			@if (isset($isSnp))
				formData.push({name: 'isSnp', value: true});
				
			@endif
			
			var method = 'POST';

			@isset($isSnp)
				var url = "{{ url('profile/update/'. $id) }}"; 
			@else 
				var url = "{{ url('/users/update/'. $id) }}";
			@endisset
			 
			var requestData = {
				url: url,
				method: method,
				body:formData,   
			};
			
			//sendRequest(requestData, redirectTo);
			@if(isset($isSnp)) 
			sendRequest(requestData, "{{ url('profile') }}");
			@else 
			sendRequest(requestData, "{{ url('users') }}");
			@endif 
		});
	</script>

@endsection
@endsection

