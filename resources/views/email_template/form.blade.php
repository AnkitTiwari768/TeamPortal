@extends('components.admin.layout')

@section('page-content')
<style>
.required::after {
    content: " *";
    color: red;
    font-weight: bold;
}
label {
    font-weight: 600;
}
.btn i {
    margin-right: 5px;
}
</style>

<div class="container-fluid px-4 py-4">     
    <div class="row">
        <div class="col-lg-12">
            <div class="card">

                <!-- HEADER -->
                <div class="card-header d-flex">
                    <div>
                        <h4>{{ $title }}</h4>
                    </div>

                    <div class="ms-auto">
                        <a href="{{ route('email-template-list') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <!-- BODY -->
                <div class="card-body">

                    <form id="formId">
                        <div class="row">

                            <!-- SUBJECT -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Subject</label>
                                <input type="text" id="subject" class="form-control"
                                    value="{{ $row->subject ?? '' }}">
                                <span class="text-danger" id="subject_error"></span>
                            </div>

                            <!-- TEMPLATE KEY -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Template Key</label>
                                <input type="text" id="template_key" class="form-control"
                                    value="{{ $row->template_key ?? '' }}" readonly>
                                <span class="text-danger" id="template_key_error"></span>
                            </div>

                            <!-- BODY -->
                            <div class="col-md-12 mb-3">
                                <label class="required">Body</label>
                              <textarea id="body" class="form-control">{{ $row->body ?? '' }}</textarea>
                                <span class="text-danger" id="body_error"></span>
                            </div>

                            <!-- VARIABLES -->
                            <div class="col-md-12 mb-3">
                                <label>Variables (Auto JSON)</label>
                                <textarea id="variables" class="form-control" rows="4">
{{ isset($row->variables) ? json_encode($row->variables, JSON_PRETTY_PRINT) : '' }}
                                </textarea>
                                <span class="text-danger" id="variables_error"></span>
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-6 mb-3">
                                <label class="required">Status</label>
                                <select id="is_active" class="form-select">
                                    <option value="">Select</option>
                                    <option value="1" {{ (isset($row) && $row->is_active==1)?'selected':'' }}>Active</option>
                                    <option value="0" {{ (isset($row) && $row->is_active==0)?'selected':'' }}>Inactive</option>
                                </select>
                                <span class="text-danger" id="is_active_error"></span>
                            </div>

                        </div>

                        <!-- BUTTON -->
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                @isset($id) Update @else Submit @endisset
                            </button>

                            <button type="reset" class="btn btn-warning ms-2">
                                Reset
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

// ✅ SLUG
function slugify(text) {
    return text.toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}

$("#subject").on("keyup change", function () {
    $("#template_key").val(slugify($(this).val()));
});


// 🔥 AUTO EXTRACT VARIABLES FROM BODY
function extractVariables(body) {
    let matches = body.match(/\{\{(.*?)\}\}/g);
    if (!matches) return [];

    return matches.map(v => v.replace(/[{}]/g, '').trim());
}

$("#body").on("keyup change", function () {
    let vars = extractVariables($(this).val());

    let obj = {};
    vars.forEach(v => obj[v] = "string");

    $("#variables").val(JSON.stringify(obj, null, 2));
});


// 🔥 FORM SUBMIT
$("#formId").on("submit", function (e) {
    e.preventDefault();

    $(".text-danger").text("");

    let variables = $("#variables").val();

    // ✅ JSON VALIDATION
    if (variables) {
        try {
            JSON.parse(variables);
        } catch (e) {
            toastr.error("Invalid JSON in Variables");
            return;
        }
    }

    let formData = {
        subject: $("#subject").val(),
        template_key: $("#template_key").val(),
        body: $("#body").val(),
        variables: variables,
        is_active: $("#is_active").val(),
        _token: "{{ csrf_token() }}"
    };

    @isset($id)
        var url = "{{ route('email-template-update', $id) }}";
    @else
        var url = "{{ route('email-template-store') }}";
    @endisset

    $.ajax({
        url: url,
        type: "POST",
        data: formData,

        success: function(res){
            if(res.status){
                toastr.success(res.message);
                setTimeout(()=>{
                    window.location.href = "{{ route('email-template-list') }}";
                },1000);
            } else {
                $.each(res.errors,function(k,v){
                    $("#"+k+"_error").text(v[0]);
                });
            }
        },

        error: function(xhr){
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