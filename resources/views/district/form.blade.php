@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">     
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>
						@if(!empty($row->id))
							 {{ __('Edit Districts') }}
						@else
							 {{ __('Add Districts') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Districts') }}
						@else
							 {{ __('Add Districts') }}
						@endif</a></li>
							</ol>
						</nav>	
					</div>
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							@include('components.admin.buttons.back-button')
						</div>
					</div>
					
				</div>
				<div class="card-body pt-1">
					<form id="formId"> 
						<div class="row">
							<input type="hidden" name="country_id" id="country_id" value="a572ef09-d6cf-11ee-8177-00155d022d06">
							<div class="col-4 mb-3"> 
								<div class="form-group">
									<label class="required">{{ __('message.state_name') }}</label>
									{{ Form::select('state_id', state_list(), $row['state_id'] ?? null, ['class' => 'form-select', 'id' => 'state_id']) }}  
									<span class="text-danger form-error" id="state_id_error"></span>
								</div>
							</div> 
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('City Name') }}</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" maxlength="{{ config('constant.MAXLENGTH') }}" placeholder="Title"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif>
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('message.code') }}</label>
									<input type="text" class="form-control" name="code" id="code" 
									maxlength="{{ config('constant.MAXLENGTH') }}" placeholder="{{ __('message.code') }}"
										@if (isset($row) && isset($row['code']))
											value="{{ $row['code'] }}"
										@endif>
									<span class="text-danger form-error" id="code_error"></span>
								</div>
							</div>
							
							
							<div class="col-md-4"> 
								<div class="mb-3">
									<label class="required form-label">{{ __('message.status') }}</label>
									{{ Form::select('status', status_list(), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) }}   
									<span class="text-danger form-error" id="status_error"></span>
								</div> 
							</div>
						</div>
							<div class="form-action mt-3 mb-3">
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
 @isset($id)
		var countryID = $('#country_id').val();
		getState(countryID);
	    setTimeout(function () { 
			$('#state_id option[value={{ $row['state_id'] }}]').prop('selected', 'selected').change();
        }, 1000);
 	@endisset
        $("#formId").on("submit", function (event) {
            event.preventDefault();
           	var country_id = $("#country_id").val(); 
            var state_id = $("#state_id").val(); 
            var name = $("#name").val(); 
            var code = $("#code").val(); 
			var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/districts/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/districts') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    country_id: country_id,
                    state_id: state_id, 
                    name: name, 
                    code: code, 
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('districts') }}");
        });
    </script>
@endsection
@endsection

