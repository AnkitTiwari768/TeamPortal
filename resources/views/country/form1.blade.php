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
							 {{ __('message.edit_country') }}
						@else
							 {{ __('message.add_country') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('message.edit_country') }}
						@else
							 {{ __('message.add_country') }}
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
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('message.country_name') }}</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" placeholder="{{ __('message.country_name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('message.country_code') }}</label>
									<input type="text" class="form-control" name="country_code" id="country_code" 
									maxlength="{{ config('constant.MAXLENGTH') }}" placeholder="{{ __('message.country_code') }}"
										@if (isset($row) && isset($row['country_code']))
											value="{{ $row['country_code'] }}"
										@endif>
									<span class="text-danger form-error" id="country_code_error"></span>
								</div>
							</div>
							
							<div class="col-md-4">
								<div class="mb-3 ">
									<label class="required form-label">{{ __('message.iso2_code') }}</label>
									<input type="text" class="form-control" name="iso2_code" id="iso2_code" maxlength="{{ config('constant.CODELENGTH') }}" placeholder="{{ __('message.iso2_code') }}"
										@if (isset($row) && isset($row['iso2_code']))
											value="{{ $row['iso2_code'] }}"
										@endif>
									<span class="text-danger form-error" id="iso2_code_error"></span>
								</div>
							</div>

							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('message.iso3_code') }}</label>
									<input type="text" class="form-control" name="iso3_code" maxlength="{{ config('constant.CODELENGTH') }}" id="iso3_code" placeholder="{{ __('message.iso3_code') }}"
										@if (isset($row) && isset($row['iso3_code']))
											value="{{ $row['iso3_code'] }}"
										@endif>
									<span class="text-danger form-error" id="iso3_code_error"></span>
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
        $("#formId").on("submit", function (event) {
            event.preventDefault();
            var name = $("#name").val(); 
            var status = $("#status").val();
			var country_code = $("#country_code").val(); 
            var iso2_code = $("#iso2_code").val(); 
            var iso3_code = $("#iso3_code").val(); 
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/countries/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/countries') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name, 
                    status: status,
					country_code:country_code,
                    iso2_code: iso2_code,
                    iso3_code: iso3_code,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('countries') }}");
        });
    </script>
@endsection
@endsection

