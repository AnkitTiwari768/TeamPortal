
@php
    use App\Web\Category\OndcTypeService;
	use App\Services\StatusService
@endphp

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
							 {{ __('Edit Category') }}
						@else
							 {{ __('Add Category') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Category') }}
						@else
							 {{ __('Add Category') }}
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
									<label class="required form-label">Ondc Type</label>
									{{ Form::select('ondc_type_id', app(OndcTypeService::class)->list(), $row['ondc_type_id'] ?? null, ['class' => 'form-select', 'id' => 'ondc_type_id']) }}   
									<span class="text-danger form-error" id="ondc_type_id_error"></span>
								</div> 
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">Category Name</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" 
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							
							<div class="col-md-4"> 
								<div class="mb-3">
									<label class="required form-label">{{ __('Status') }}</label>
									{{ Form::select('status', app(StatusService::class)->list(), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) }}
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
            var ondc_type_id = $("#ondc_type_id").val(); 
            var name = $("#name").val(); 
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/categories/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/categories') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
					ondc_type_id:ondc_type_id,
                    name: name, 
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('categories') }}");
        });
    </script>
@endsection
@endsection

