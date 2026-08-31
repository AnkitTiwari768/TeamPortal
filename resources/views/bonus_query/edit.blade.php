@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between">
					<h6 class="m-0 font-weight-bold text-primary"> New Legal officer</h6>
					<a href="javascript:void(0);" onclick = "javascript:history.back(-1);" class="btn btn-sm btn-primary float-right"><i class="fa fa-angle-double-left"></i> Back</a>
				</div>
				<div class="card-body">
					<form id="formId">
				
					<div class="row">
						<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.app_id') }}</label>
									<input type="text" class="form-control txtOnly" name="national_permission_application_id" id="national_permission_application_id" readonly
									
										@if (isset($row) && isset($row['national_permission_application_id']))
											value="{{ $row['national_permission_application_id'] }}"
										@endif>
									<span class="text-danger form-error" id="national_permission_application_id_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.app_type') }}</label>
									<input type="text" class="form-control" name="application_type" id="application_type" readonly 
										@if (isset($row) && isset($row['application_type']))
											value="{{ $row['application_type'] }}"
										@endif>
									<span class="text-danger form-error" id="application_type_error"></span>
								</div>
							</div>
						
						</div>
						<div class="row">
						<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.title_name') }}</label>
									<input type="text" class="form-control txtOnly" name="title_name" id="title_name" readonly
									
										@if (isset($row) && isset($row['title_name']))
											value="{{ $row['title_name'] }}"
										@endif>
									<span class="text-danger form-error" id="title_name_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.legalOfficer_name') }}</label>
									<input type="text" class="form-control" name="legal_officer_name" id="legal_officer_name"  
										@if (isset($row) && isset($row['legal_officer_name']))
											value="{{ $row['legal_officer_name'] }}"
										@endif>
									<span class="text-danger form-error" id="legal_officer_name_error"></span>
								</div>
							</div>
						
						</div>
						<div class="row">
						<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.legalOfficer_email') }}</label>
									<input type="email" class="form-control" name="legal_officer_email" id="legal_officer_email" 
									
										@if (isset($row) && isset($row['legal_officer_email']))
											value="{{ $row['legal_officer_email'] }}"
										@endif>
									<span class="text-danger form-error" id="legal_officer_email_error"></span>
								</div>
							</div>
							
							<div class="col-4 mb-3">
								<div class="form-group ">
									<label class="required">{{ __('message.legalOfficer_contact_number') }}</label>
									<input type="text" class="form-control numeric" maxlength="10" name="legal_officer_contact_number" id="legal_officer_contact_number"  
										@if (isset($row) && isset($row['legal_officer_contact_number']))
											value="{{ $row['legal_officer_contact_number'] }}"
										@endif>
									<span class="text-danger form-error" id="legal_officer_contact_number_error"></span>
								</div>
							</div>
						
						</div>
						<div class="row">
						<div class="col-4 mb-3">
								<!--<div class="form-group ">
									<label class="required">{{ __('message.legalOfficer_designation') }}</label>
									{{ Form::select('designation_id', designation_list(), $row['designation_id'] ?? null, ['class' => 'form-control', 'id' => 'designation_id','disabled' => isset($isProfile)]) }}
									<span class="text-danger form-error" id="designation_id_error"></span>
								</div>-->
							</div>
						<div class="col-4 mb-3">
							<div class="form-group ">
							<br />
								<input type="hidden" id="application_id" name="application_id" 
								@if (isset($row) && isset($row['application_id']))
										value="{{ $row['application_id'] }}"
									@endif />
								<button type="submit" id="submit-btn" class="btn btn-success">Update</button>                  
								<button type="reset" id="cancel-btn" class="btn btn-warning"> Reset</button>  
							</div>
						</div>							
						</div>				
					</form>
				</div>
			</div>
		</div>
	</div>
</div>


@section('js');
<script>
        $("#formId").on("submit", function (event) {
            event.preventDefault();
            var national_permission_application_id = $("#national_permission_application_id").val();  
            var application_type = $("#application_type").val();
            var title_name = $("#title_name").val(); 
            var legal_officer_name = $("#legal_officer_name").val(); 
            var legal_officer_email = $("#legal_officer_email").val(); 
            var application_id = $("#application_id").val();			
            var legal_officer_contact_number = $("#legal_officer_contact_number").val(); 
            var designation_id = $("#designation_id").val(); 
            
			
			var csrfToken = "{{ csrf_token() }}";
			
			var method = 'POST';
			var url = "{{ url('/legalofficer/') }}/"+application_id+'/edit';
            

            var requestData = {
                url: url,
                method: method,
                body: {
                    national_permission_application_id: national_permission_application_id, 
                    application_type: application_type,
                    title_name: title_name,
                    legal_officer_name: legal_officer_name,
                    legal_officer_email: legal_officer_email,
                    application_id: application_id,
                    legal_officer_contact_number: legal_officer_contact_number,
                    designation_id: designation_id,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('/legalofficers') }}");
        });
    </script>
@endsection

@endsection

