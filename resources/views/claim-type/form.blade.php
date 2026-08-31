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
							 {{ __('Edit Claim Type') }}
						@else
							 {{ __('Add Claim Type') }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
							 {{ __('Edit Claim Type') }}
						@else
							 {{ __('Add Claim Type') }}
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
									<label class="required form-label">{{ __('Name') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="name" id="name" placeholder="{{ __('Enter Claim Type Name') }}"
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="255">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('Slug') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="slug" id="slug" placeholder="{{ __('Enter Slug') }}"
										@if (isset($row) && isset($row['slug']))
											value="{{ $row['slug'] }}"
										@endif maxLength="255">
									<span class="text-danger form-error" id="slug_error"></span>
								</div>
							</div>

                            <div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">{{ __('Short Name') }}</label>
									<input type="text" class="form-control alphaNumericSpace" name="short_name" id="short_name" placeholder="{{ __('Enter Short Name') }}"
										@if (isset($row) && isset($row['short_name']))
											value="{{ $row['short_name'] }}"
										@endif maxLength="255">
									<span class="text-danger form-error" id="short_name_error"></span>
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
            var slug = $("#slug").val();
            var short_name = $("#short_name").val();
            var status = $("#status").val();
			var csrfToken = "{{ csrf_token() }}";
            
            @isset($id)
                var method = 'POST';
                var url = "{{ url('/claim-types-update/'. $id) }}";
            @else 
                var method = 'POST';
                var url = "{{ url('/claim-types') }}";
            @endisset

            var requestData = {
                url: url,
                method: method,
                body: {
                    name: name, 
                    slug: slug,
                    short_name: short_name,
                    status: status,
                    _token: csrfToken
                }
            };

            sendRequest(requestData, "{{ url('claim-types') }}");
        });
    </script>
@endsection
@endsection

