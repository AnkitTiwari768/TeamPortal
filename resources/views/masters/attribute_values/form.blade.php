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
							{{ __('Edit :name', ['name' => $dynamicSlug ?? null]) }}
						 @else
							{{ __('Add :name', ['name' => $dynamicSlug ?? null]) }}
						 @endif
						</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li> 
								<li class="breadcrumb-item">
									<a href="{{ url('masters/' . $dynamicSlug) }}">
										{{ ucwords(str_replace('_', ' ', $dynamicSlug)) }}
									</a>
								</li>
								<li class="breadcrumb-item active">
									  @if(!empty($row->id))
										{{ __('Edit', ['name' => $attribute->name ?? null]) }}
									@else
										{{ __('Add', ['name' => $attribute->name ?? null]) }}
									@endif
								</li>
							</ol>
						</nav>	
					</div>
					<div class="action-header ms-auto">
						<div class="btn-group drop-btn">
							<a href="{{ url()->previous() }}" class="btn btn-secondary">
							 <i class="fa fa-arrow-left"></i> Back
							</a>
						</div>
					</div>
				</div> 
				<div class="card-body pt-1">
					<form id="formId"> 
					<div class="row g-3">
							<div class="col-md-6">
								<label class="required form-label">Attribute</label>
							<select class="form-select" id="attribute_id">

								@if($attribute)
									<option value="{{ $attribute->id }}"
										{{ ($row->attribute ?? '') == $attribute->id ? 'selected' : '' }}>
										{{ $attribute->name }}
									</option>
								@endif
							</select>
							</div>
							<div class="col-md-6">
										<label class="form-label">Parent</label>
										<select class="form-select" id="parent_id">
											<option value="">Select Parent</option>
										</select>
									</div>
							<div class="col-md-4">
								<div class="mb-3">
									<label class="required form-label">Name</label>
									<input type="text" class="form-control" name="attribute_value" id="attribute_value" maxlength="255" 
										@if (isset($row) && isset($row->attribute_value))
											value="{{ $row->attribute_value }}"
										@endif>
									<span class="text-danger form-error" id="attribute_value_error"></span>
								</div>
							</div> 
							<div class="col-md-4">
								<div class="mb-3">
									<label class="form-label">Sort Order</label>
									<input type="number" class="form-control" name="sort_order" id="sort_order" 
										@if (isset($row) && isset($row->sort_order))
											value="{{ $row->sort_order }}"
										@endif>
									<span class="text-danger form-error" id="sort_order_error"></span>
								</div>
							</div> 								
							<div class="col-md-4"> 
								<div class="mb-3">
									<label class="required form-label">Status</label>
									{{ Form::select('status', status_list(), (isset($row->status) ? $row->status : null), ['class' => 'form-select', 'id' => 'status']) }}   
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
@section('js')
<script>
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: "3000",
    extendedTimeOut: "1000",
    preventDuplicates: true
};




/* Submit Form */
$("#formId").on("submit", function (event) {

    event.preventDefault();

    $(".form-error").text("");
    let attribute_id    = $("#attribute_id").val();
    let attribute_value = $("#attribute_value").val();
    let status          = $("#status").val();
	let sort_order      = $("#sort_order").val();
	let parent_id =      $("#parent_id").val();
    let csrfToken       = "{{ csrf_token() }}";
    let dynamicSlug     = "{{ $dynamicSlug }}";

    let entityType = dynamicSlug
        .replace(/[-_]/g, " ")
        .replace(/\b\w/g, function(l){
            return l.toUpperCase();
        });
	if (parent_id === "") {
		parent_id = null; // ✅ convert to NULL
	}
    @isset($id)
        let url = "{{ url('masters/' . $dynamicSlug . '/update/' . $id) }}";
        let successMsg = entityType + " Updated Successfully";
    @else
        let url = "{{ url('masters/' . $dynamicSlug) }}";
        let successMsg = entityType + " Created Successfully";
    @endisset

    $.ajax({
        url: url,
        type: "POST",
        data: {
			attribute_id: attribute_id, 
            parent_id: parent_id,       
            attribute_value: attribute_value,
			sort_order: sort_order,     
            status: status,
            _token: csrfToken
        },
        success: function (res) {
            toastr.success(successMsg);
            setTimeout(function () {
                window.location.href = "{{ url('masters/' . $dynamicSlug) }}";
            }, 2000);
        },

        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, messages) {
                    $("#" + field + "_error").text(messages[0]);
                });
            } else {
                toastr.error("Something went wrong");
            }
        }
    });

});


$(document).ready(function () {
    let attributeId = $("#attribute_id").val();

    // ✅ IMPORTANT FIX HERE
    let selectedParent = "{{ $row->id ?? '' }}";

    if (attributeId) {
        $.get("{{ url('/get-attribute-values') }}/" + attributeId, function (res) {

            let options = '<option value="">Select Parent</option>';

            res.data.forEach(function (parent) {

                let isParentSelected = parent.id == selectedParent ? 'selected' : '';

                options += `<option value="${parent.id}" ${isParentSelected}>
                                ${parent.attribute_value}
                            </option>`;

                if (parent.children && parent.children.length > 0) {
                    parent.children.forEach(function (child) {

                        let isChildSelected = child.id == selectedParent ? 'selected' : '';

                        options += `<option value="${child.id}" ${isChildSelected}>
                                        &nbsp;&nbsp; └─> ${child.attribute_value}
                                    </option>`;
                    });
                }
            });

            $("#parent_id").html(options);
        });
    }
});
</script>
@endsection
@endsection