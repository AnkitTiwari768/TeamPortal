<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="timer_time" content="{{ \App\Http\Services\VerificationService::CODE_EXPIRATION_TIME }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <title>Assisted Registration</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver=' . time()) }}">
    <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver=' . time()) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <?php Session::forget(['mobile', 'email']);
    Session::forget(['mobile_value', 'email_value']); ?>
    <style>

    </style>
</head>

<body id="mainbody" class="registration-page">
    <div id="cover-spin" style="display:none"></div>
    <div class="container-fluid">
        <div class="row">

            <div class="col-lg-3 p-0">
                <div class="left-img-wrap h-100 px-4 py-3">
                    <div class="img-section-left sticky-top">
                        <div class="card-header logo-box mb-5 mt-0">
                            <img src="{{ asset('assets/img-new/mti-logo.svg') }}" class="logo">
                        </div>

                        <div class="timeline">
                            <ul class="unstyled p-0 m-0 ">
                                <li class="d-flex gap-3 mb-3 done ">
                                    <div class="icon">1</div> Basic Details
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-9 right-form-content">
                <div class="inner-header pb-3 pt-4 ">
                    <div class="row">
                        <div class="col-lg-6">
                            <h2 class="fw-bolder fs-4">Assisted Registrations</h2>
                        </div>
                        <div class="col-lg-6 text-end">
                            <a href="{{ url('') }}" class="btn btn-stroked-theme"> <i
                                    class="fa fa-long-arrow-left me-2" aria-hidden="true"></i> Go to home</a>
                        </div>
                    </div>
                </div>
                <div class="signup-wrapper card mt-2">
                    <div class="inner-login-wrapper pb-4">
                        <div class="row">
                            <form method="post" id="formId" class="mt-2 pt-3 formId">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p><span style="color: #db0000ff;">*</span> Fields are mandatory to fill
                                            in the form.</p>
                                    </div>
                                </div>
                                <div class="row basic-detail">
                                    <div class="col-lg-12 mb-3">
                                        <h2 class="form-title-heading font-20"> <img
                                                src="{{ asset('assets/img/bbasic.svg') }}" alt=""> Basic
                                            details</h2>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Name of Organization</label>
                                                <a data-bs-toggle="tooltip" title="Enter the official registered name of your organization as per legal documents"><i
                                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text txtnumerichypenapercend"
                                                name="organization_name" maxlength="100"
                                                placeholder="Name of Organization" id="organization_name"
                                                @if (isset($id) && !empty($row['organization_name'])) value="{{ $row['organization_name'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="organization_name_error"></span>
                                    </div>
                                   
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Entity Type</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip"
                                                    title="Select the type of business entity"><i
                                                        class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <select name="entity_type" id="entity_type" class="form-select text-black">
                                                <option value="">Select</option>
                                                @foreach ($entity_type as $key => $val)
                                                    <option value="{{ $val->id }}" class="text-black">
                                                        {{ $val->attribute_value }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="entity_type_error"></span>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Email of Entity</label>
                                                <!-- <a class="tooltip-ins" href="#" data-bs-toggle="tooltip"
                                                    title=""><i
                                                        class="fa fa-question-circle" aria-hidden="true"></i></a> -->
                                            </div>
                                            <input type="text" class="input-text alphanumeric" name="entity_email"
                                                maxlength="100" id="entity_email" placeholder="Email of Entity"
                                                @if (isset($id) && !empty($row['entity_email'])) value="{{ $row['entity_email'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="entity_email_error"></span>
                                    </div>
                                </div>
                                <div class="row contact_detail">
                                    <!-- txtnumerichypenapercend -->
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <label class="form-label">Number of Members/Beneficiaries <a
                                                    class="tooltip-ins" href="#" data-bs-toggle="tooltip"
                                                    title="Number of members/beneficiaries associated with your association."><i
                                                        class="fa fa-question-circle"
                                                        aria-hidden="true"></i></a></label>
                                            <input type="text" class="input-text integer" name="number_of_members"
                                                maxlength="10" id="number_of_members"
                                                placeholder="Number of Members/Beneficiaries"
                                                @if (isset($id) && !empty($row['number_of_members'])) value="{{ $row['number_of_members'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="number_of_members_error"></span>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                                <label class="form-label">Registration No
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter the registration number of the organization .">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                            <input type="text" class="input-text alphaNumeric" maxlength="100"
                                                name="registration_number" id="registration_number"
                                                placeholder="Registration No"
                                                @if (isset($id) && !empty($row['registration_number'])) value="{{ $row['registration_number'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="registration_number_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <label class="form-label">Website
                                             <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Provide the official website URL of the organization (if available).">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>   
                                            </label>
                                            <input type="text" class="input-text alphanumeric" name="website"
                                                id="website" placeholder="Website"
                                                @if (isset($id) && !empty($row['website'])) value="{{ $row['website'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="website_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                            <label class="form-label required">Contact No</label>
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter the primary contact number of the organization for communication.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text numeric" name="contact_number"
                                                minlength="10" maxlength="10" id="contact_number"
                                                placeholder="Contact No"
                                                @if (isset($id) && !empty($row['contact_number'])) value="{{ $row['contact_number'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="contact_number_error"></span>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                                <label class="form-label">PAN/TAN
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Provide the Permanent Account Number (PAN) or Tax Deduction Account Number (TAN) of the organization.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                            <input type="text" class="input-text alphanumeric" name="pan_number"
                                                minlength="10" maxlength="10" id="pan_number" placeholder="PAN"
                                                @if (isset($id) && !empty($row['pan_number'])) value="{{ $row['pan_number'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="pan_number_error"></span>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">State</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select the state where the organization is registered or operates.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            {{ Form::select('state_id', ['' => 'Select'] + remove_select_dynamic_common_list($lists?->states), json_decode($row['state_id'] ?? '[]', true), ['class' => 'form-select text-dark', 'id' => 'state_id']) }}

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="state_id_error"></span>
                                    </div>

                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">District</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select the district corresponding to the selected state.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            {{ Form::select('district_id', ['' => 'Select'], json_decode($row['district_id'] ?? '[]', true), ['class' => 'form-select text-dark', 'id' => 'district_id']) }}
                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="district_id_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Complete Address</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter the full registered address of the organization including street, locality, city, state, and PIN code.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text alphanumeric"
                                                name="complete_address" id="complete_address"
                                                placeholder="Complete Address"
                                                @if (isset($id) && !empty($row['complete_address'])) value="{{ $row['complete_address'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="complete_address_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Contact Person Name</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Provide the name of the authorized person representing the organization.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text txtOnly"
                                                name="contact_person_name" maxlength="100" id="contact_person_name"
                                                placeholder="Contact Person Name"
                                                @if (isset($id) && !empty($row['contact_person_name'])) value="{{ $row['contact_person_name'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="contact_person_name_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Designation of Contact Person</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter the official designation of the contact person.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text txtOnly"
                                                name="contact_person_designation" maxlength="100"
                                                id="contact_person_designation"
                                                placeholder="Designation of Contact Person"
                                                @if (isset($id) && !empty($row['contact_person_designation'])) value="{{ $row['contact_person_designation'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="contact_person_designation_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Contact Person Phone No</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter the mobile number of the contact person for communication.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text integer"
                                                name="contact_person_phone" minlength="10" maxlength="10"
                                                id="contact_person_phone" placeholder="Contact Person Phone No"
                                                @if (isset($id) && !empty($row['contact_person_phone'])) value="{{ $row['contact_person_phone'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="contact_person_phone_error"></span>
                                    </div>
                                    <div class="col-lg-4 mb-3">
                                        <div class="mb-0 ">
                                            <div class="d-flex gap-3">
                                                <label class="form-label required">Contact Person Email</label>
                                                <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Provide the email ID of the contact person.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                            </div>
                                            <input type="text" class="input-text alphanumeric"
                                                name="contact_person_email" id="contact_person_email"
                                                placeholder="Contact Person Email"
                                                @if (isset($id) && !empty($row['contact_person_email'])) value="{{ $row['contact_person_email'] }}" @endif>

                                        </div>
                                        <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                            id="contact_person_email_error"></span>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-12 mb-3">
                                            <div class="input-box">
                                                @foreach ($documents as $key => $claimDocument_type)
                                                    <div class="form-group col-md-4">
                                                        <label
                                                            class="form-label required">{{ $claimDocument_type->document_category_name }}
                                                            <a class="tooltip-ins" href="#"
                                                                data-bs-toggle="tooltip"
                                                                title="{{ $claimDocument_type->informations }}"><i
                                                                    class="fa fa-question-circle"
                                                                    aria-hidden="true"></i></a></label>
                                                        <input type="file"
                                                            name="{{ $claimDocument_type->document_category_slug }}"
                                                            id="{{ $claimDocument_type->document_category_slug }}"
                                                            document_category_id="{{ $claimDocument_type->document_category_slug }}"
                                                            class="form-control upload-pdf"
                                                            accept="application/pdf required">
                                                        <span
                                                            class="text-danger form-error font-12 font-12 font-12 mb-3"
                                                            id="authorization_document_error"></span>
                                                    </div>
                                                @endforeach
                                                <input type="hidden" name="authorization_document"
                                                    id="authorization_document">
                                                <input type="hidden" name="authorization_document_id"
                                                    id="authorization_document_id">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="action-bottom flex-row justify-content-center">
                                            <button id="cancel-button" type="reset"
                                                class="btn btn-secondary btn-themed mt-3">Cancel</button>
                                            <button id="signup-button" type="submit"
                                                class="btn btn-primary btn-themed mt-3">Submit</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @include('components.admin.popup.sms-email')
    @include('components.admin.file-upload')



    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js?ver=' . time()) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/script.js?ver=' . time()) }}"></script>
    <script type="text/javascript" src="{{ url('toastr/toastr.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/crypto-js.min.js?ver=' . time()) }}"
        integrity="sha512-E8QSvWZ0eCLGk4km3hxSsNmGWbLtSCSUcewDQPQWZF6pEU8GlT8a5fF32wOl1i8ftdMhssTrF/OhyGWwonTcXA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    @include('components.admin.bootstrap-dialog')

    <!-- <script src="{{ asset('assets/ffo-admin/js/bootstrap.min.js') }}"></script> -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/common.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/validations.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/mustache.min.js?ver=' . time()) }}"></script>
    <script src="{{ asset('assets/js/verification.js?ver=' . time()) }}"></script>
    <!-- <link href="{{ asset('assets/css/bootstrap.min.css?ver=' . time()) }}" rel="stylesheet"> -->
    <link rel="stylesheet" href="{{ url('toastr/toastr.min.css?ver=' . time()) }}">
    <script type="text/javascript" src="{{ asset('assets/js/jquery.bootstrap-duallistbox.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        if (window !== window.top) {
            document.getElementById('mainbody').innerHTML =
                "<div id='frame' style='position:absolute;top:50%;left:50%;transform:translate(-50%, -50%)'><h1>Security Alert:</h1> <h2>This website cannot be displayed within an iframe for security reasons. For your safety, we do not allow our content to be framed by external websites. Please visit our website directly by typing the URL in your browser's address bar.</h2></div>";
            document.getElementById("mainbody").style.backgroundColor = "#ccc";
        }
    </script>


    <script>
       

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        $(document).ready(function() {
            // $('#roles').select2({
            // 	placeholder: "Select Roles",
            // 	allowClear: true
            // });

            $(document).on('focus input change', 'input, textarea, select', function() {
                let fieldId = $(this).attr('id');
                if (fieldId) {
                    $("#" + fieldId + "_error").text(""); // Clear related error span
                }
            });
        });

        $('.upload-pdf').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            var document_category_id = $(this).attr('document_category_id');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('document_category_id', document_category_id);
            formData.append('file', file);

            $.ajax({
                url: "{{ url('upload-document-public') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success(response.message);
                        $('#authorization_document').val(response.data.file_name);
                        $('#authorization_document_id').val(response.data.file_id);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('File upload failed.');
                }
            });
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();

            var method = 'POST';
            // @isset($id)
            // 	var url = "{{ url('/update-snp-registration/' . $id) }}";	
            // @else 
            var url = "{{ url('/ia-registration') }}";
            // @endisset

            var requestData = {
                url: url,
                method: method,
                body: formData,
            };

            sendRequest(requestData, "{{ url('https://team.msme.gov.in/') }}");

            //sendRequest(requestData, "{{ url('ia-registration') }}");
        });
    </script>

</body>

</html>
