@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid">     
	<div class="row">
		<div class="col-md-12">
			<div class="card mb-4">
				<div class="card-header d-flex justify-content-between mb-3">
					<h6 class="box-heading heading-1">
					  @if(!empty($row->id))
							 {{ __('message.edit_state') }}
						@else
							 {{ __('message.add_state') }}
					  @endif

					</h6>
					
					@include('components.admin.buttons.back-button')
				</div>
				<div class="card-body">
					<form id="formId"> 
						<div class="row">
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.country_name') }}</label>
									{{ Form::select('country_id', country_list(), $row['country_id'] ?? null, ['class' => 'form-control', 'id' => 'country_id']) }}  
									<span class="text-danger form-error" id="country_id_error"></span>
								</div>
							</div> 
							<div class="col-4 mb-3">
								<div class="form-group">
									<label class="required">{{ __('message.state_name') }}</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" maxlength="{{ config('constant.MAXLENGTH') }}" placeholder="Title"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif>
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<div class="col-4 mb-3">
								<div class="form-group">
									<label class="required">{{ __('message.code') }}</label>
									<input type="text" class="form-control" name="code" id="code" maxlength="{{ config('constant.CODELENGTH') }}" placeholder="{{ __('message.code') }}"
										@if (isset($row) && isset($row['code']))
											value="{{ $row['code'] }}"
										@endif>
									<span class="text-danger form-error" id="code_error"></span>
								</div>
							</div>

							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list(), $row['status'] ?? null, ['class' => 'form-control', 'id' => 'status']) }}   
									<span class="text-danger form-error" id="status_error"></span>
								</div> 
							</div>
						</div>

							<div class="mb-3">
								@include('components.admin.buttons.submit-button')
						    	@include('components.admin.buttons.cancel-button')
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
            var name = $("#name").val(); 
			var country_id = $("#country_id").val(); 
            var code = $("#code").val(); 
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/states/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/states') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name, 
                    code: code, 
					country_id : country_id,
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('states') }}");
        });
    </script>
@endsection
@endsection

