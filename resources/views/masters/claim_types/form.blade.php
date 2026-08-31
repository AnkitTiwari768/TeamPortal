
@php
    use App\Web\Category\OndcTypeService;
	use App\Services\StatusService;
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
						{{ $title ?? '' }}
						@else
							{{ $title ?? '' }}
						@endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item"><a href="#">@if(!empty($row->id))
						{{ $title ?? '' }}
						@else
							{{ $title ?? '' }}
						@endif</a></li>
							</ol>
						</nav>	
					</div>
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							<a href="{{ url()->previous() }}" class="btn btn-secondary">
								<i class="fa fa-arrow-left"></i> Back
							</a>
						</div>
					</div>
					
				</div>
				<div class="card-body pt-1">
					<form id="formId"> 
						<div class="row">
						

							<div class="col-md-6">
								<div class="mb-3">
									<label class="required form-label">Name</label>
									<input type="text" class="form-control txtOnly" name="name" id="name" 
										@if (isset($row) && isset($row['name']))
											value="{{ $row['name'] }}"
										@endif maxLength="">
									<span class="text-danger form-error" id="name_error"></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="mb-3">
									<label class="required form-label">Short Name</label>
									<input type="text" class="form-control txtOnly" name="short_name" id="short_name" 
										@if (isset($row) && isset($row['short_name']))
											value="{{ $row['short_name'] }}"
										@endif maxLength="">
									<span class="text-danger form-error" id="short_name_error"></span>
								</div>
								</div>
												
							
							<div class="col-md-6"> 
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
	toastr.options = {
    "closeButton": true,          // shows cross button
    "progressBar": true,         // shows progress bar
    "positionClass": "toast-top-right",
    "timeOut": "3000",
    "extendedTimeOut": "1000",
    "preventDuplicates": true
};


$("#formId").on("submit", function (event) {

    event.preventDefault();

    // Clear previous errors
    $(".form-error").text("");

    let name = $("#name").val();
    let status = $("#status").val();
	let short_name = $("#short_name").val();
    let csrfToken = "{{ csrf_token() }}";

    @isset($id)
        var url = "{{ url('/claimTypes-update/'.$id) }}";
    @else
        var url = "{{ url('/claimTypes-create') }}";
    @endisset

    $.ajax({
        url: url,
        type: "POST",
        data: {
            name: name,
			short_name: short_name,
            status: status,
            _token: csrfToken
        },

        beforeSend: function () {
            $("#loader").show();
        },

        success: function (response) {
            $("#loader").hide();
            if(response.status){
                toastr.success(response.message);
               setTimeout(function(){
					window.location.href = "{{ url('claimTypes') }}";
				},3000);
            } else if(response.errors) {
                // Loop through validation errors and show under fields
                $.each(response.errors, function(field, messages){
                    $("#" + field + "_error").text(messages[0]);
                });
            } else {
                toastr.error(response.message);
            }

        },

        error: function(xhr){
            $("#loader").hide();
            if(xhr.status === 422){ // Laravel validation status code
                let errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages){
                    $("#" + field + "_error").text(messages[0]);
                });
            } else {
                toastr.error("Something went wrong");
            }

        }

    });

});

</script>
@endsection
@endsection

