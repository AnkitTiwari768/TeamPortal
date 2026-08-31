<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta http-equiv="X-Frame-Options" content="deny">
    <meta http-equiv="Content-Security-Policy" content="frame-ancestors 'none';">
    <meta name="description" content="" />
    <meta name="author" content="" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="icon" />
    <link href="{{ asset('assets/favicon.ico') }}" type="image/x-icon" rel="shortcut icon" />
    <title>PM Vishwakarma Registration</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.css?ver=' . time()) }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/response.css?ver=' . time()) }}">
    <?php Session::forget(['mobile', 'email']);
    Session::forget(['mobile_value', 'email_value']); ?>
    <style>
        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }

        #formId .col-lg-4.mb-4 {
            margin-bottom: 2.5rem !important;
        }

        .verify_icon {
            width: 20px;
        }

        label.animated {
            position: relative;
            font-size: 15px;
            color: #7d2ae8;
            cursor: pointer;
            display: flex;
            align-items: center;

        }

        label.animated input {
            position: absolute;
            opacity: 0;
        }

        .input-check {
            position: relative;
            display: inline-block;
            top: 5px;
            width: 30px;
            height: 30px;
            border: 2px solid #ccc;
            border-radius: 4px;
            margin-right: 5px;
            transition: .5s;
            transform: scale(0.7);
        }

        label.animated input:checked~.input-check {
            background: #0f6492;
            border-color: #0f6492;
            animation: animate .7s ease;
            transform: scale(0.7);
        }

        @keyframes animate {
            0% {
                transform: scale(1);
            }

            40% {
                transform: scale(1.3, .7);
            }

            55% {
                transform: scale(1);
            }

            70% {
                transform: scale(1.2, .8);
            }

            80% {
                transform: scale(1);
            }

            90% {
                transform: scale(1.1, .9);
            }

            100% {
                transform: scale(1);
            }
        }

        .input-check::before {
            content: '';
            position: absolute;
            top: 9px;
            left: 6px;
            width: 15px;
            height: 6px;
            border-bottom: 3px solid #fff;
            border-left: 3px solid #fff;
            transform: scale(0) rotate(-45deg);
            transition: .5s;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background: linear-gradient(135deg, #F3F9FD);
            border: 1px solid #F3F9FD;
            border-radius: 999px;
            /* pill shape */
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            margin: 6px 6px 0 0;
            padding: 6px 14px 6px 28px;
            position: relative;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            font-weight: 500;
            color: #374151;
            /* box-shadow: 0 2px 6px rgba(79, 70, 229, 0.12); */
            transition: all 0.25s ease;
        }

        .select2-selection__choice__remove {
            border: none !important;
            background: transparent !important;
            padding: 0 5px !important;
            margin: 0 !important;
            top: 5px !important;
            left: 10px !important;
        }

        label.animated input:checked~.input-check::before {
            transform: scale(1) rotate(-45deg);
        }
    </style>
</head>

<body id="mainbody" class="">
    <div id="cover-spin" style="display:none"></div>
    <div class="signup-wrapper-msme mt-0">
        <div class="container">
            <div class="inner-login-wrapper mt-4 signup-form-msme card">
                <div class="card-body pb-5 pt-3 px-4">
                    <div class="logo text-center"> <img src="{{ asset('assets/img-new/msme-logo.png') }}"> </div>
                    <div class="header mb-2 mt-3 text-center">
                        <!--<h6 class="decor-text">MSE TEAM Initiative</h6> -->
                        <h2 class="card-title">PM Vishwakarma Registration</h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <p><span style="color: #db0000ff;">*</span> Fields are mandatory to fill
                                in the form.</p>
                        </div>
                    </div>
                    <!-- <hr/> -->
                    <form method="post" id="formId" class="mt-2">
                        <div class="row">

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Owner Name</label>
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter owner’s full name.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text txtOnly" name="owner_name"
                                        placeholder="Owner Name" maxlength="100" id="owner_name" required>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="owner_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label">Store Name 
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter shop/business name.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </label>
                                    <input type="text" class="input-text txtOnly" name="store_name" maxlength="100"
                                        placeholder="Store Name" id="store_name" required>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="store_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Mobile </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter active mobile number.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    
                                    <input type="text" class="input-text numeric" name="mobile" maxlength="10"
                                        placeholder="Mobile" id="mobile" required>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="mobile_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Email </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter valid email ID.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="email" class="input-text" name="email" maxlength="100"
                                        placeholder="Email" id="email" required>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="email_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                        <label class="form-label">PAN
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter PAN number (if available).">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                    <input type="text" class="input-text" name="pan_no" placeholder="PAN Number"
                                        id="pan_no" value="">

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="pan_no_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Pin Code </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter area PIN code.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text numeric" name="pin_code" maxlength="6"
                                        placeholder="Pin Code" id="pin_code" value="">
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="pin_code_error"></span>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Address </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter complete address.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text" name="address" placeholder="Address"
                                        id="address" value="">
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="address_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Type of Business</label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select business type.">
                                                <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <select class="form-select" name="type_of_business" id="type_of_business" required>
                                        <option value="">Select Type of Business</option>
                                      @foreach ($categories as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>               

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="state_id_error"></span>
                            </div>

                            <!-- <div class="col-lg-4 mb-3" style="display:none;" id="other_div">
                                <div class="mb-0">
                                    <label class="form-label required">If Other ,Please Mention Your Business
                                        Type</label>
                                    <input type="text" class="input-text" name="other" maxlength=""
                                        placeholder="other" id="other" required>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="other_error"></span>
                            </div> -->

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label">Remark
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter additional comments (if any).">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </label>
                                    <input type="text" class="input-text" name="remark" maxlength=""
                                        placeholder="Remark" id="remark">
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="remark_error"></span>
                            </div>

                            @foreach ($documents as $key => $claimDocument_type)
                                <div class="form-group col-md-4">
                                    <label
                                        class="form-label {{ $claimDocument_type->document_category_name != 'Cancelled Cheque' ? 'required' : '' }}">{{ $claimDocument_type->document_category_name }}
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip"
                                            title="{{ $claimDocument_type->informations }}"><i
                                                class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                    <input type="file" name="{{ $claimDocument_type->document_category_slug }}"
                                        id="{{ $claimDocument_type->document_category_slug }}"
                                        document_category_id="{{ $claimDocument_type->document_category_slug }}"
                                        class="form-control upload-pdf" accept="application/pdf required">
                                    <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                        id="cancelled-cheque_error"></span>
                                </div>
                            @endforeach
                            <input type="hidden" name="cancelled-cheque" id="cancelled-cheque">
                            <input type="hidden" name="cancelled-cheque-id" id="cancelled-cheque-id">

                            <input type="hidden" name="vishwakarma-form-copy" id="vishwakarma-form-copy">
                            <input type="hidden" name="vishwakarma-form-copy-id" id="vishwakarma-form-copy-id">

                            <?php /*
									<div class="col-lg-12 mb-3">
										<div class="d-flex align-items-start">
											<input type="checkbox" class="mt-1 me-2" checked="checked" onclick="return false;">
											<label for="agree" class="form-check-label">
											I/We hereby declare that the details furnished above are true and correct to the best of my/our knowledge and belief. In case any of the above information is found to be incorrect, misleading, or false, appropriate action may be taken against me/us as per applicable laws.

											I/We hereby authorize NSIC / ONDC to share relevant details, including transactions carried out on the ONDC Network, with NSIC for the purpose of scheme administration and support, in compliance with applicable laws.

											Accordingly, I/We declare that I/We am/are not onboarded with any SNP as on the date of operationalization of the initiative and wish to avail the subsidy and benefits under the TEAM Initiative.

											I/We further declare that I/We have not availed similar assistance under any Central or State Government schemes/programmes.

											I/We confirm that I/We have read, understood, and agree to abide by the Privacy Policy, Terms and Conditions, Disclaimer, Data Sharing Policy, Operating Guidelines, and SOPs of the TEAM Initiative.

											NSIC reserves the right to change or amend the SOPs of the TEAM Initiative, with the approval of the Ministry of MSME, as per policy and procedural requirements, as and when warranted, without prior notice.
											</label>
										</div>
										<span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
									</div>
								*/
                            ?>
                            <div class="col-lg-12 mb-3">
                                <button id="signup-button" type="submit" class="btn btn-primary mt-3">Register
                                </button>
                            </div>


                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        var BASE_URL = "{{ url('/') }}";
    </script>
    <script src="{{ asset('assets/js/jquery-3.7.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-migrate-3.4.0.min.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script type="text/javascript" src="{{ url('toastr/toastr.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery-ui.js') }}"></script>
    <script src="{{ asset('assets/js/common.js') }}"></script>
    <script src="{{ asset('assets/js/validations.js') }}"></script>
    <script src="{{ asset('assets/js/verification.js') }}"></script>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link rel="stylesheet" href="{{ url('toastr/toastr.min.css') }}">
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

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on('keydown', '.form-control, .form-select', function() {
            $(this).closest('div').siblings('span').html('');
        });

        $("#formId").on("submit", function(event) {
            event.preventDefault();
            var formData = $("#formId").serializeArray();
            var method = 'POST';
            var url = "{{ url('/create-pm-users') }}";
            $("#cover-spin").show();
            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(res) {
                    if (res.status) {
                        $("#cover-spin").hide();
                        toastr.success('Register Successfull.');

                        setTimeout(function() {
                                //redirect("{{ url('pm-registration') }}")
                                redirect("{{ url('https://team.msme.gov.in/') }}")
                            },
                            2000);

                    } else if (!res.status && res.errors.length == 0) {
                        failure(res);
                        $("#cover-spin").hide();
                        $("#signup-button").attr("class", "btn btn-primary mt-3");
                        $("#signup-button").html('Register');
                        return;
                    } else if (!res.status && res.errors) {
                        applyValidationErrors(res);
                        $("#cover-spin").hide();
                        $("#signup-button").attr("class", "btn btn-primary mt-3");
                        $("#signup-button").html('Register');
                        return;
                    } else {
                        failure(res);
                        enableSubmit();
                    }
                },

                error: function(xhr) {
                    applyValidationErrors(xhr.responseJSON);
                    $("#cover-spin").hide();
                    $("#signup-button").attr("class", "btn btn-primary mt-3");
                    $("#signup-button").html('Register');
                    return;
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

                        /*$('#cancelled-cheque').val(response.data.file_name);
                                            $('#cancelled-cheque-id').val(response.data.file_id);

                    						$('#vishwakarma-form-copy').val(response.data.file_name);
                                            $('#vishwakarma-form-copy-id').val(response.data.file_id);*/

                        let field = document_category_id.replaceAll('_', '-');

                        // file name
                        $('#' + field).val(response.data.file_name);

                        // file id
                        $('#' + field + '-id').val(response.data.file_id);
                    }
                },
                error: function(xhr, status, error) {
                    $('input[type="file"]').val('');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {

                        let errors = xhr.responseJSON.errors;
                        let message = '';

                        Object.keys(errors).forEach(function(key) {
                            errors[key].forEach(function(err) {
                                message += err + '<br>';
                            });
                        });

                        toastr.error(message);

                    } else if (xhr.responseJSON && xhr.responseJSON.message) {

                        toastr.error(xhr.responseJSON.message);

                    } else {

                        toastr.error('File upload failed.');
                    }
                    //toastr.error('File upload failed.');
                }
            });
        });

        // $('#type_of_business_id').on('change', function() {
        //     const selectedText = $('#type_of_business_id option:selected').text();
        //     //alert(selectedText);

        //     if (selectedText == 'Others') {
        //         $('#other_div').show();
        //         $('#other').attr('required', true);
        //     } else {
        //         $('#other_div').hide();
        //         $('#other').val('').removeAttr('required');
        //     }
        // });
    </script>
</body>

</html>
