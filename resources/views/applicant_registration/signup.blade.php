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
    <title>Signup</title>
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
                        <h2 class="card-title">Express Your Interest in TEAM Portal</h2>
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
                                        <label class="form-label required">Name of Entrepreneur</label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter entrepreneur’s full name.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text txtOnly" name="entrepreneur_name"
                                        placeholder="Name of Entrepreneur" maxlength="100" id="entrepreneur_name"
                                        required1>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="entrepreneur_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Name of Enterprise </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter registered business name.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text alphaNumericSpace" name="enterprise_name"
                                        maxlength="100" placeholder="Name of Enterprise" id="enterprise_name" required1>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="enterprise_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label">Udyam Number 
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter valid Udyam registration number if you have.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </label>
                                    <input type="text" class="input-text udyamNumber" name="udyam_no" maxlength="19"
                                        placeholder="UDYAM-XX-00-0000000" id="udyam_no">
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="udyam_no_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Mobile </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter active mobile number.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    <input type="text" class="input-text numeric" name="mobile" maxlength="10"
                                        placeholder="Enter Mobile" id="mobile" required1>
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
                                        placeholder="Email" id="email" required1>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="email_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">Product Details </label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Enter details about products/services offered.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>    
                                    <input type="text" class="input-text" name="product_details" maxlength="200"
                                        placeholder="Atta, Shirts, Earphones, etc." id="product_details" required1>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="product_details_error"></span>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label class="form-label required">State</label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select your business state.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    {{ Form::select('state_id', ['' => 'Select State'] + remove_select_dynamic_common_list($lists?->states), '', ['class' => 'form-select', 'id' => 'state_ids', 'required1' => 'required1']) }}

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="state_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label for="ondc_transaction_type_id" class="form-label required">Type of
                                            transaction? (B2B/B2C)</label>
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select transaction type.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    {!! Form::select('ondc_transaction_type_id', ['' => 'Select'] + static_common_list($lists?->ondc_types), '', [
                                        'class' => 'form-select select_value',
                                        'id' => 'ondc_transaction_type_id',
                                        'required1' => 'required1',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="ondc_transaction_type_id_error"></span>
                            </div>
                            <div class="col-lg-4 mb-3"></div>
                            <div class="col-lg-8 mb-3 ">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                        <label for="product_category_id" class="form-label required">Category of Business</label>
                                        <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select the product category.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    {!! Form::select('product_category_id[]', remove_select_dynamic_common_list($lists?->sub_domains), '', [
                                        'class' => 'form-select select2',
                                        'id' => 'product_category_id',
                                        'multiple' => 'multiple',
                                        'required1' => 'required1',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="product_category_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <div class="d-flex gap-3">
                                            <label for="select_snp" class="form-label required">How do
                                            you wish to Proceed?
                                            </label>
                                            <a class="tooltip-ins" href="#" data-bs-toggle="tooltip" title="Select prefer registration method.">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i></a>
                                    </div>
                                    
                                    <select name="select_snp" id="select_snp" class="form-select" required1>
                                        <option value="">Select</option>
                                        <option value="1">Choose Your SNP for further assistance</option>
                                        <option value="2">Option to self register on Udyam</option>
                                        <option value="3">Seek helpdesk support</option>
                                    </select>

                                        
                                </div>

                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="select_snp_error"></span>
                            </div>
                            <div id="sndDetailsContainer"></div>

                            <div class="col-lg-12 mb-3">
                                <div class="d-flex align-items-start">
                                    <input type="checkbox" class="mt-1 me-2" checked="checked"
                                        onclick="return false;">
                                    <label for="agree" class="form-check-label">
                                        I/We hereby declare that the details furnished above are true and correct to the
                                        best of my/our knowledge and belief. In case any of the above information is
                                        found to be incorrect, misleading, or false, appropriate action may be taken
                                        against me/us as per applicable laws.

                                        I/We hereby authorize NSIC / ONDC to share relevant details, including
                                        transactions carried out on the ONDC Network, with NSIC for the purpose of
                                        scheme administration and support, in compliance with applicable laws.

                                        Accordingly, I/We declare that I/We am/are not onboarded with any SNP as on the
                                        date of operationalization of the initiative and wish to avail the subsidy and
                                        benefits under the TEAM Initiative.

                                        I/We further declare that I/We have not availed similar assistance under any
                                        Central or State Government schemes/programmes.

                                        I/We confirm that I/We have read, understood, and agree to abide by the Privacy
                                        Policy, Terms and Conditions, Disclaimer, Data Sharing Policy, Operating
                                        Guidelines, and SOPs of the TEAM Initiative.

                                        NSIC reserves the right to change or amend the SOPs of the TEAM Initiative, with
                                        the approval of the Ministry of MSME, as per policy and procedural requirements,
                                        as and when warranted, without prior notice.
                                    </label>
                                </div>
                                <span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
                            </div>

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

            // tooltip initialize js script
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            // tooltip script end

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    <script>
        /* $(document).on('keydown', '.form-control, .form-select', function() { 
            				$(this).closest('div').siblings('span').html('');;
            			}); */

        $(".form-control, .form-select, .input-text").on("focus", function() {
            let fieldId = $(this).attr('id');
            if (fieldId == 'state_ids') {
                $('#state_id' + '_error').text('');
            }
            $('#' + fieldId + '_error').text('');
        });

        $(document).on("select2:open", ".select2", function() {
            let fieldId = $(this).attr('id');
            if (fieldId) {
                $('#' + fieldId + '_error').text('');
            }
        });

        $(document).ready(function() {
            $('#product_category_id').select2({
                placeholder: "Select Category",
                allowClear: true
            });

        });

        var snpXhr = null;

        function loadSnpDetails() {
            var sub_domain = $("#product_category_id").val();
            var select_snp = $("#select_snp").val();
            var state_id = $("#state_ids").val();
            var ondc_transaction_type_id = $("#ondc_transaction_type_id").val();

            if (select_snp != 1) {
                return;
            }

            /*if(select_snp ==1 && (state_id =='' || ondc_transaction_type_id =='' || sub_domain.length==0)){
            	alert('Please state,select type of transaction? (B2B/B2C) & product category');
            	$("#select_snp").val('');
            	return false;
            }*/

            if (state_id === '') {
                alert('Please select State');
                $("#state_ids").focus();
                $("#select_snp").val('');
                return false;
            }
            if (ondc_transaction_type_id === '') {
                alert('Please select Type of Transaction (B2B / B2C)');
                $("#ondc_transaction_type_id").focus();
                $("#select_snp").val('');
                return false;
            }

            if (!sub_domain || sub_domain.length === 0) {
                alert('Please select Product Category');
                $("#product_category_id").focus();
                $("#select_snp").val('');
                return false;
            }

            if (snpXhr && snpXhr.readyState !== 4) {
                snpXhr.abort();
            }

            $("#cover-spin").show();

            snpXhr = $.ajax({
                url: '{{ route('snp-select-details') }}',
                type: 'GET',
                data: {
                    state_code: state_id,
                    ondc_transaction_type_id: ondc_transaction_type_id,
                    sub_domain: sub_domain
                },
                success: function(response) {
                    $('#sndDetailsContainer').html(response).show();
                },
                error: function(xhr) {
                    if (xhr.statusText === 'abort') {
                        return;
                    }
                    toastr.error('Failed to load content.');
                },
                complete: function() {
                    $("#cover-spin").hide();
                }
            });
        }

        $('#select_snp').on('change', function() {
            var select_snp = $("#select_snp").val();
            if (select_snp == 1) {
                loadSnpDetails();
            } else if (select_snp == 2) {
                /*alert("By clicking on OK you will be redirected to the Udyam Registration Page, please register and keep the Udyam number handy with you, one of our SNP will connect with you soon.");
                window.location.href="https://udyamregistration.gov.in/Government-India/Ministry-MSME-registration.htm";*/

                //$("#select_snp").val('');
                $('#sndDetailsContainer').html("");

            } else if (select_snp == 3) {
                /*alert("Your data has been shared with the helpdesk, you must receive a call from our executive soon. If you wish to connect, please call on 14475");
                window.location.href=BASE_URL+'/contact-us';*/

                //$("#select_snp").val('');
                $('#sndDetailsContainer').html("");
            }
        });

        $('#state_ids, #ondc_transaction_type_id, #product_category_id').on('change', function() {
            loadSnpDetails();
        });






        $("#formId").on("submit", function(event) {
            event.preventDefault();

            var formData = $("#formId").serializeArray();
            var method = 'POST';
            var url = "{{ url('/applicant-registration') }}";

            $("#cover-spin").show();
            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(res) {
                    if (res.status) {
                        $("#cover-spin").hide();
                        if (res.registration_type == 1) {
                            toastr.success(res.message);
                            setTimeout(function() {
                                    redirect(res.redirect)
                                },
                                2000);
                        } else if (res.registration_type == 2) {
                            alert(res.message);
                            //window.location.href=res.redirect;
                            var newTab = window.open();
                            newTab.location.href = res.redirect;
                        } else if (res.registration_type == 3) {
                            alert(res.message);
                            window.location.href = res.redirect;
                        }


                    } else if (!res.status && res.errors.length == 0) {
                        failure(res);
                        $("#cover-spin").hide();
                        $("#signup-button").attr("class", "btn btn-primary mt-3");
                        $("#signup-button").html('Register');
                        return;
                    } else if (!res.status && res.errors) {
                        if (res.errors.snp_id) {
                            var snpErrorMsg = Array.isArray(res.errors.snp_id) ? res.errors.snp_id[0] : res.errors.snp_id;
                            toastr.error(snpErrorMsg);
                        }
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
                    var res = xhr.responseJSON;
                    if (res && res.errors && res.errors.snp_id) {
                        var snpErrorMsg = Array.isArray(res.errors.snp_id) ? res.errors.snp_id[0] : res.errors.snp_id;
                        toastr.error(snpErrorMsg);
                    }
                    applyValidationErrors(res);
                    $("#cover-spin").hide();
                    $("#signup-button").attr("class", "btn btn-primary mt-3");
                    $("#signup-button").html('Register');
                    return;
                },

                complete: function() {
                    $("#cover-spin").hide();
                }

            });

        });
    </script>
    <div class="modal fade" id="commercialModelModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-semibold">Commercial Model</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped common_table">

                            <tbody id="commercialModelBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>

            </div>
        </div>
    </div>
    <script>
        $(document).on('click', '.commercialModelBtn', function() {

            var model = $(this).data('model');
            var html = '';

            for (var key in model) {

                var label = key.replaceAll('_', ' ');
                label = label.charAt(0).toUpperCase() + label.slice(1);

                var value = model[key];

                if (value !== null && value !== '') {
                    value = value.toString().replaceAll('_', ' ');
                    value = value.charAt(0).toUpperCase() + value.slice(1);
                } else {
                    value = '-';
                }

                html += `
					<tr>
						<td>${label}</td>
						<td>${value}</td>
					</tr>
				`;
            }

            $('#commercialModelBody').html(html);
            $('#commercialModelModal').modal('show');
        });
    </script>
</body>

</html>
