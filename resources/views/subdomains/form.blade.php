

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
							 {{ __('Edit SubDomain') }}
						@else
							 {{ __('Add SubDomain') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit SubDomain') }}
						@else
							 {{ __('Add SubDomain') }}
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
									<label class="required form-label">Domain</label>
									{!! Form::select('domain_id', array(''=>'Select Domain')+static_common_list($lists?->domain), $row['domain_id'] ?? null, ['class' => 'form-select', 'id' => 'domain_id']) !!}
									<span class="text-danger form-error" id="domain_id_error"></span>
								</div> 
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">SubDomain Name</label>
									<input type="text" class="form-control" name="name" id="name" 
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="{{config('constant.MAXLENGTH')}}">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							
							<div class="col-md-4"> 
								<div class="mb-3">
									<label class="required form-label">{{ __('Status') }}</label>
									{!! Form::select('status', array(''=>'Select Status')+static_common_list($lists?->status), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) !!}
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
            var domain_id = $("#domain_id").val(); 
            var name = $("#name").val(); 
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/sub-domains/update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/sub-domains') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
					domain_id:domain_id,
                    name: name, 
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('sub-domains') }}");
        });
    </script>
@endsection
@endsection

