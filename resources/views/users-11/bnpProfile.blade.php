@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow mb-4">
			
				<div class="card-header py-3 d-flex flex- align-items-center justify-content-between"> 
					<h6 class="m-0 font-weight-bold text-primary">BNP Details</h6>
					
				</div>
				<div class="card-body">
					<form id="formId">
                        <div class="row g-3">
                            @php
                                $basic_details = [
                                    'organization_id' => 'Organization Id',
                                    'organization_name' => 'Organization Name',
                                    'bnp_name' => 'Authorized Person Name',
                                    'designation' => 'Designation',
                                    'email' => 'Email',
                                    'mobile' => 'Mobile No',
                                ];

                                $bank_details = [
                                    'bank_name' => 'Bank Name',
                                    'ifsc_code' => 'IFSC Code',
                                    'account_no' => 'Account No',
                                    'pan' => 'PAN',
                                    'gst_number' => 'GST Number',
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
                                </div>
                                
                                
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
@section('js');

	<script>

		$("#formId").on("submit", function (event) {
			event.preventDefault(); 			
			
			var formData=$("#formId").serializeArray();
			
			//To set formdata in case of update profile
			@if (isset($isSnp))
				formData.push({name: 'isBnp', value: true});
				
			@endif
			
			var method = 'POST';

			@isset($isBnp)
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

