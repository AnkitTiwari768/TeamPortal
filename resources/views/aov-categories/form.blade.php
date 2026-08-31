
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
							 {{ __('Edit Aov Category') }}
						@else
							 {{ __('Add Aov Category') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Aov Category') }}
						@else
							 {{ __('Add Aov Category') }}
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
									<label class="required form-label">Category Name</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" 
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">Aov type</label>
									<select name="aov_grouping_type" class="form-select" id="aov_grouping_type">
										<option value="">Select</option>

										<option value="High AOV" 
											{{ isset($row) && $row['aov_grouping_type'] == 'High AOV' ? 'selected' : '' }}>
											High AOV
										</option>

										<option value="Low AOV" 
											{{ isset($row) && $row['aov_grouping_type'] == 'Low AOV' ? 'selected' : '' }}>
											Low AOV
										</option>
									</select>
									<span class="text-danger form-error" id="aov_grouping_type_error"></span>
								</div>
							</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="form-label">Minimun Order Value</label>
									<input type="text" class="form-control integer" name="minimum_order_value" id="minimum_order_value" 
										@if (isset($row) && isset($row['minimum_order_value']))
											value="{{ $row['minimum_order_value'] }}"
										@endif maxLength="10">
									<span class="text-danger form-error" id="minimum_order_value_error"></span>
								</div>
							</div>
						<div class="col-md-4">
							<div class="mb-3">

								<label class="required form-label d-flex align-items-center gap-1">
									ONDC Domain Id 
									<a class="tooltip-ins" href="javascript:void(0)"
									data-toggle="tooltip"
									title="ONDC Domain ID must follow the format: Valid ONDC Domain ID formats: ONDC:RET10, ONDC:RET1A, ONDC:LOG12, ONDC:SRV14, FIS10 / FIS12 / FIS13, nic2004:60232">
										<i class="fa fa-question-circle" aria-hidden="true"></i>
									</a>
									<span class="text-danger">*</span>
								</label>

								<select name="ondc_domain_id"
										id="ondc_domain_id"
										class="form-select">
									<option value="">Select ONDC Domain Id</option>

									@foreach($ondcDomains as $domain)
										<option value="{{ $domain->value }}"
											@if(isset($row) && $row->ondc_domain_id == $domain->value) selected @endif>
											{{ $domain->value }}
										</option>
									@endforeach
								</select>

								<span class="text-danger form-error" id="ondc_domain_id_error"></span>

							</div>
						</div>
													
							<div class="col-md-4"> 
								<div class="mb-3">
									<label class="required form-label">{{ __('Status') }}</label>
									{{ Form::select('status',['' => 'Select'] + app(StatusService::class)->list(), $row['status'] ?? null, ['class' => 'form-select', 'id' => 'status']) }}
									<span class="text-danger form-error" id="status_error"></span>
								</div> 
							</div>
						</div>

		
						<div class="form-action mt-3 mb-3 d-flex justify-content-end gap-2">
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
      $("#ondc_domain_id").select2({
          placeholder: "Select ONDC Domain Id",
          allowClear: true
      });
     

        $("#formId").on("submit", function (event) {
            event.preventDefault();
           
            var name = $("#name").val(); 
			var aov_grouping_type = $("#aov_grouping_type").val(); 
			var minimum_order_value = $("#minimum_order_value").val(); 
			var ondc_domain_id = $("#ondc_domain_id").val(); 
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
			
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/aov-categories-update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/aov-categories-create') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name, 
					aov_grouping_type:aov_grouping_type,
					minimum_order_value:minimum_order_value,
					ondc_domain_id:ondc_domain_id,
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('aov-categories') }}");
        });
    </script>
@endsection
@endsection

