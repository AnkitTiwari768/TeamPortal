@extends('components.admin.layout')

@section('page-content')
<style>
.required::after {
    content: " *";
    color: red;
    font-weight: bold;
}
label {
    font-weight: 600; /* bold */
}
.btn i {
    margin-right: 5px;
}

</style>
<div class="container-fluid px-4 py-4">     
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">

                <!-- HEADER -->
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ $title }}</h1>

                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li> 
                                <li class="breadcrumb-item active">{{ $title }}</li>
                            </ol>
                        </nav>    
                    </div>

                    <div class="ms-auto">
                        <a href="{{ route('notification-template-list') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <!-- BODY -->
                <div class="card-body">

                    <form id="formId">
                        <div class="row">

                            <!-- Stage -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Stage</label>
                                <input type="text" name="stage" id="stage" class="form-control"
                                    value="{{ $row->stage ?? '' }}">
                                <span class="text-danger form-error" id="stage_error"></span>
                            </div>

                            <!-- Trigger Point -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Trigger Point</label>
                                <input type="text" name="trigger_point" id="trigger_point" class="form-control"
                                    value="{{ $row->trigger_point ?? '' }}">
                                <span class="text-danger form-error" id="trigger_point_error"></span>
                            </div>

                            <!-- Template Key -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Template Key</label>
                                <input type="text" name="template_key" id="template_key" class="form-control"
                                    value="{{ $row->template_key ?? '' }}" readonly>
                                <span class="text-danger form-error" id="template_key_error"></span>
                            </div>

                            <!-- Type -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Type</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="">Select</option>
                                    <option value="1" {{ (isset($row) && $row->type==1)?'selected':'' }}>User</option>
                                    <option value="2" {{ (isset($row) && $row->type==2)?'selected':'' }}>Role</option>
                                </select>
                                <span class="text-danger form-error" id="type_error"></span>
                            </div>

                            <!-- Message -->
                            <div class="col-md-12 mb-3">
                                <label class="required">Message</label>
                                <textarea name="message" id="message" class="form-control" rows="4">{{ $row->message ?? '' }}</textarea>
                                <span class="text-danger form-error" id="message_error"></span>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Select</option>
                                    <option value="1" {{ (isset($row) && $row->status==1)?'selected':'' }}>Active</option>
                                    <option value="0" {{ (isset($row) && $row->status==0)?'selected':'' }}>Inactive</option>
                                </select>
                                <span class="text-danger form-error" id="status_error"></span>
                            </div>

                        </div>

                        <!-- BUTTON -->
                      <div class="text-end mt-3">
							<!-- Submit / Update Button -->
							<button type="submit" class="btn btn-primary px-4">
								<i class="fa fa-save me-1"></i> 
								@isset($id)
									Update
								@else
									Submit
								@endisset
							</button>
							<!-- Reset Button -->
							<button type="reset" class="btn btn-warning px-4 ms-2">
								<i class="fa fa-undo me-1"></i> Reset
							</button>

						</div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


@section('js')
<script>

// 🔥 SLUG FUNCTION
function slugify(text) {
    return text.toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

// 🔥 AUTO GENERATE TEMPLATE KEY
$("#trigger_point").on("keyup change", function () {
    let trigger = $(this).val();
    let slug = slugify(trigger);
    $("#template_key").val(slug);
});


// 🔥 FORM SUBMIT
$("#formId").on("submit", function (e) {
    e.preventDefault();

    $(".form-error").text("");

    let formData = {
        stage: $("#stage").val(),
        trigger_point: $("#trigger_point").val(),
        template_key: $("#template_key").val(),
        message: $("#message").val(),
        type: $("#type").val(),
        status: $("#status").val(),
        _token: "{{ csrf_token() }}"
    };

    @isset($id)
        var url = "{{ route('notification-template-update', $id) }}";
    @else
        var url = "{{ route('notification-template-store') }}";
    @endisset

    $.ajax({
        url: url,
        type: "POST",
        data: formData,

        beforeSend: function () {
            $("#loader").show();
        },

        success: function(res){
            $("#loader").hide();

            if(res.status){
                toastr.success(res.message);

                setTimeout(()=>{
                    window.location.href = "{{ route('notification-template-list') }}";
                },1500);
            } else {
                $.each(res.errors,function(k,v){
                    $("#"+k+"_error").text(v[0]);
                });
            }
        },

        error: function(xhr){
            $("#loader").hide();

            if(xhr.status === 422){
                let errors = xhr.responseJSON.errors;
                $.each(errors,function(k,v){
                    $("#"+k+"_error").text(v[0]);
                });
            } else {
                toastr.error("Something went wrong");
            }
        }
    });
});

</script>
@endsection