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
    <title>MSE Registration</title>
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

        .disabled {
            background-color: #dddd !important;
            cursor: no-drop;
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
                        <h2 class="card-title">MSE Registration</h2>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <p><span style="color: #db0000ff;">*</span> Fields are mandatory to fill
                                in the form.</p>
                        </div>
                    </div>
                    <form method="post" id="formId" class="mt-2">
                        <div class="row">
                            <input type="hidden" name="udyam_no" value="{{ $udyamNo }}" id="udyam_no">
                            <input type="hidden" name="mobile" value="{{ $mobile }}" id="mobile">
                            <input type="hidden" name="token" value="{{ $token }}" id="token">
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Name of
                                        Entrepreneur</label>
                                    <input type="text"
                                        class="input-text txtOnly {{ $udetails['BasicDetail']['EnterpriseName'] ? 'disabled' : '' }}"
                                        name="entrepreneur_name" placeholder="Name of Entrepreneur" maxlength="100"
                                        id="entrepreneur_name" value="{{ $udetails['BasicDetail']['EnterpriseName'] }}"
                                        {{ !empty($udetails['BasicDetail']['EnterpriseName']) ? 'readonly' : '' }}>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="entrepreneur_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Name of Enterprise
                                    </label>
                                    <input type="text"
                                        class="input-text alphaNumericSpace {{ $udetails['BasicDetail']['EntrepreneurName'] ? 'disabled' : '' }}"
                                        name="enterprise_name" maxlength="100" placeholder="Name of Enterprise"
                                        id="enterprise_name" value="{{ $udetails['BasicDetail']['EntrepreneurName'] }}"
                                        {{ !empty($udetails['BasicDetail']['EntrepreneurName']) ? 'readonly' : '' }}>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="enterprise_name_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Type of Organization</label>
                                    <input type="text"
                                        class="input-text {{ $udetails['BasicDetail']['OrganisationType'] ? 'disabled' : '' }}"
                                        name="organisation_type" maxlength="80" placeholder="Type of Organization"
                                        id="organisation_type"
                                        value="{{ $udetails['BasicDetail']['OrganisationType'] }}"
                                        {{ !empty($udetails['BasicDetail']['OrganisationType']) ? 'readonly' : '' }}>


                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="organisation_type_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Email </label>
                                    <input type="email"
                                        class="input-text {{ $udetails['BasicDetail']['EmailId'] ? 'disabled' : '' }}"
                                        name="email" maxlength="100" placeholder="Email" id="email"
                                        value="{{ $udetails['BasicDetail']['EmailId'] }}"
                                        {{ !empty($udetails['BasicDetail']['EmailId']) ? 'readonly' : '' }}>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="email_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Address </label>
                                    <input type="text"
                                        class="input-text {{ $udetails['BasicDetail']['CommunicationAddress'] ? 'disabled' : '' }}"
                                        name="address" maxlength="80" placeholder="Address" id="address"
                                        value="{{ $udetails['BasicDetail']['CommunicationAddress'] }}"
                                        {{ !empty($udetails['BasicDetail']['CommunicationAddress']) ? 'readonly' : '' }}>


                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="address_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">State </label>
                                    @if (!empty($udetails['BasicDetail']['State']))
                                        <input type="text" class="input-text disabled"
                                            value="{{ $udetails['BasicDetail']['State'] }}" readonly>

                                        <input type="hidden" name="state_id"
                                            value="{{ $udetails['BasicDetail']['LG_ST_Code'] ?? '' }}">
                                    @else
                                        {{ Form::select('state_id', ['' => 'Select State'] + remove_select_dynamic_common_list($lists?->states), '', [
                                            'class' => 'form-select',
                                            'id' => 'state_id',
                                            'required',
                                        ]) }}
                                    @endif
                                </div>

                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="state_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">District </label>
                                    <input type="text"
                                        class="input-text {{ $udetails['BasicDetail']['District'] ? 'disabled' : '' }}"
                                        name="district_id" maxlength="80" placeholder="District" id="district_id"
                                        value="{{ $udetails['BasicDetail']['District'] }}"
                                        {{ !empty($udetails['BasicDetail']['District']) ? 'readonly' : '' }}>

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="district_id_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">{{ __('message.mse') }}
                                        Classification
                                    </label>
                                    <input type="text"
                                        class="input-text {{ $udetails['BasicDetail']['EnterpriseType'] ? 'disabled' : '' }}"
                                        name="msme_classification" maxlength="80" placeholder="MSE Classification"
                                        id="msme_classification"
                                        value="{{ $udetails['BasicDetail']['EnterpriseType'] }}"
                                        {{ !empty($udetails['BasicDetail']['EnterpriseType']) ? 'readonly' : '' }}>

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="msme_classification_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Major Activity of Unit</label>
                                    <input type="text"
                                        class="input-text {{ $udetails['BasicDetail']['MajorActivity'] ? 'disabled' : '' }}"
                                        name="major_activity" maxlength="80" placeholder="Major Activity of Unit"
                                        id="major_activity" value="{{ $udetails['BasicDetail']['MajorActivity'] }}"
                                        {{ !empty($udetails['BasicDetail']['MajorActivity']) ? 'readonly' : '' }}>

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="major_activity_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select(
                                        'current_state_business_id',
                                        ['' => 'Select'] + static_common_list($lists?->current_state_business),
                                        '',
                                        ['class' => 'form-select select_value', 'id' => 'current_state_business_id'],
                                    ) !!}
                                    <label for="current_state_business_id" class="form-label required"
                                        style="display:none">What
                                        is the current state of your business?</label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="current_state_business_id_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select(
                                        'attending_ondc_awareness_workshop',
                                        ['' => 'Select'] + static_common_list($lists?->yesno),
                                        '',
                                        ['class' => 'form-select select_value', 'id' => 'attending_ondc_awareness_workshop'],
                                    ) !!}

                                    <label for="attending_ondc_awareness_workshop" class="form-label"
                                        style="display:none">Are
                                        you interested in attending ONDC awareness</label>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="attending_ondc_awareness_workshop_error"></span>
                            </div>

                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label">Turnover (Previous FY) <a class="tooltip-ins"
                                            href="#" data-toggle="tooltip" title="in INR ₹"><i
                                                class="fa fa-question-circle" aria-hidden="true"></i></a></label>
                                    <input type="text" class="input-text numeric2decimal" name="turnover"
                                        placeholder="Turnover (Previous FY)" id="turnover" maxlength="15"
                                        value="">

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="turnover_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label">Enter GST Number </label>
                                    <input type="text" class="input-text" name="gstin_no"
                                        placeholder="Enter GST Number" id="gstin_no" value="">

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="gstin_no_error"></span>
                            </div>
                            <?php /*
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Product Details </label>
                                    <input type="text" class="input-text" name="product_details" maxlength="200"
                                        placeholder="Aata, Shirts, Earphones, etc." id="product_details" required1>
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="product_details_error"></span>
                            </div>
                            */
                            ?>
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label class="form-label required">Enter PAN Number
                                    </label>
                                    <input type="text" class="input-text" name="pan_no"
                                        placeholder="Enter PAN Number" id="pan_no" value="">

                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="pan_no_error"></span>
                            </div>
                            <div class="col-lg-8 mb-3">
                                <div class="mb-0">
                                    <label for="product_category_id" class="form-label required">Select Product
                                        Category </label>
                                    {!! Form::select('product_category_id[]', remove_select_dynamic_common_list($lists?->sub_domains), '', [
                                        'class' => 'form-select select2',
                                        'id' => 'product_category_id',
                                        'multiple' => 'multiple',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="product_category_id_error"></span>
                            </div>
                            <div class="col-lg-4 mb-3">
                                <div class="mb-0">
                                    <label for="ondc_transaction_type_id" class="form-label required">Type of
                                        Transaction (B2B/B2C)?</label>
                                    {!! Form::select('ondc_transaction_type_id', ['' => 'Select'] + static_common_list($lists?->ondc_types), '', [
                                        'class' => 'form-select',
                                        'id' => 'ondc_transaction_type_id',
                                    ]) !!}
                                </div>
                                <span class="text-danger form-error font-12 font-12 font-12 mb-3"
                                    id="ondc_transaction_type_id_error"></span>
                            </div>


                            <div class="col-lg-4 mb-3">
                                <div class="mb-0 floating-label-input">
                                    {!! Form::select('select_snp', ['' => 'Select SNP'] + static_common_list($lists?->yesno), '', [
                                        'class' => 'form-select',
                                        'id' => 'select_snp',
                                    ]) !!}

                                    <label for="select_snp" class="form-label required" style="display:none">Do
                                        you wish to select an SNP?</label>
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
                                        I/We hereby declare that the details furnished above are
                                        true and correct to
                                        the best of my/our knowledge and belief. In the event
                                        that any information
                                        is found to be incorrect, misleading, or false,
                                        appropriate action may be
                                        taken against me/us as per the applicable laws.

                                        I/We hereby authorize NSIC / ONDC to share relevant
                                        details, including
                                        transactions carried out on the ONDC Network, with NSIC
                                        for the purpose of
                                        scheme administration and support, in compliance with
                                        applicable laws.

                                        I/We further declare that I/we are not onboarded with
                                        any SNP as on the date
                                        of operationalization of the initiative and wish to
                                        avail the subsidy and
                                        benefits under the TEAM Initiative.

                                        I/We also declare that I/we have not availed similar
                                        assistance under any
                                        other Central or State Government schemes/programmes.

                                        I/We confirm that I/we have read and understood the
                                        Privacy Policy, Terms
                                        and Conditions, Disclaimer, Data Sharing Policy,
                                        Operating Guidelines, and
                                        SOPs of the TEAM Initiative and agree to abide by the
                                        same.

                                        NSIC reserves the right to amend or modify the SOPs of
                                        the TEAM Initiative,
                                        with the approval of the Ministry of MSME, as per policy
                                        and procedural
                                        requirements, as and when warranted, without prior
                                        notice.
                                    </label>
                                </div>
                                <span class="text-danger form-error font-12 mb-3" id="agree_error"></span>
                            </div>

                            <div class="col-lg-12 mb-3">
                                <button id="signup-button" type="submit"
                                    class="btn btn-primary mt-3">Submit</button>
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
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#product_category_id').select2({
                placeholder: "Select Category",
                allowClear: true
            });

        });

        $('#select_snp').on('change', function() {

            var select_snp = $("#select_snp").val();

            //var state_code = "{{ $udetails['BasicDetail']['LG_ST_Code'] }}";
            // var state_code = $("#state_id").val();

            var api_state_code = "{{ data_get($udetails, 'BasicDetail.LG_ST_Code') }}";

            var state_code = api_state_code ? api_state_code : $("#state_id").val();
            var ondc_transaction_type_id = $("#ondc_transaction_type_id").val();
            var sub_domain = $("#product_category_id").val();

            console.log({
                state_code: state_code,
                transaction: ondc_transaction_type_id,
                sub_domain: sub_domain
            });

            if (select_snp == 1) {

                if (state_code === '') {
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

                if (sub_domain.length === 0) {
                    alert('Please select Product Category');
                    $("#product_category_id").focus();
                    $("#select_snp").val('');
                    return false;
                }
            }

            if (select_snp == 1) {
                $.ajax({
                    url: '{{ route('snp-select-details') }}',
                    type: 'GET',
                    data: {
                        state_code: state_code,
                        ondc_transaction_type_id: ondc_transaction_type_id,
                        sub_domain: sub_domain
                    },
                    success: function(response) {
                        //console.log(response);
                        $("#cover-spin").hide();
                        if (select_snp == 1) {
                            $('#sndDetailsContainer').html(response).show();
                            if (response.includes('No SNP data found')) {
                                $('#select_snp').val('').trigger('change');
                                $('#sndDetailsContainer').html(`
                                    <div class="alert alert-warning">
                                        No SNP data found for selected category.
                                    </div>
                                `).show();
                            }
                        } else {
                            $('.snp_id').prop('checked', false);
                            $('#sndDetailsContainer').html("").hide();
                        }
                    },
                    error: function() {
                        $("#cover-spin").hide();
                        alert('Failed to load content.');
                    }
                });
            } else {

                $('#sndDetailsContainer').html("");

            }
        });



        $(document).on('keydown', '.form-control, .form-select', function() {
            $(this).closest('div').siblings('span').html('');
        });

        $("#formId").on("submit", function(event) {
            //$(".form-error font-12 font-12 font-12").html('');
            event.preventDefault();

            var formData = $("#formId").serializeArray();
            formData.push({
                name: 'udyam_no',
                value: $("#udyam_no").val()
            });
            formData.push({
                name: 'mobile',
                value: $("#mobile").val()
            });
            formData.push({
                name: 'token',
                value: $("#token").val()
            });

            var method = 'POST';
            var url = "{{ route('ubp.register.save') }}";
            $("#cover-spin").show();
            $.ajax({
                url: url,
                type: method,
                data: formData,
                // success: function(res) {
                //     if (res.status) {
                //         /*$("#cover-spin").hide();
                //                                 toastr.success('Your account has been created successfully.');

                //     							setTimeout(function(){
                //                                     redirect("{{ url('login') }}")},
                //                                     2000);*/

                //         $("#cover-spin").hide();

                //         if (!res || !res.status) {
                //             toastr.error(res?.message || "Something went wrong");
                //             return;
                //         }

                //         if (res.registration_type === 1) {
                //             toastr.success(res.message);
                //             setTimeout(function() {
                //                 if (res.redirect) {
                //                     window.location.href = res.redirect;
                //                 }
                //             }, 2000);
                //         } else if (res.registration_type === 2 || res.registration_type === 3) {
                //             //alert(res.message);
                //             if (res.redirect) {
                //                 window.location.href = res.redirect;
                //             }
                //         } else {
                //             toastr.success(res.message);
                //             setTimeout(function() {
                //                 if (res.redirect) {
                //                     window.location.href = res.redirect;
                //                 }
                //             }, 2000);
                //             //toastr.error("Invalid response type");
                //         }

                //     } else if (!res.status && res.errors.length == 0) {
                //         failure(res);
                //         $("#cover-spin").hide();
                //         $("#signup-button").attr("class", "btn btn-primary mt-3");
                //         $("#signup-button").html('Submit');
                //         return;
                //     } else if (!res.status && res.errors) {
                //         applyValidationErrors(res);
                //         $("#cover-spin").hide();
                //         $("#signup-button").attr("class", "btn btn-primary mt-3");
                //         $("#signup-button").html('Submit');
                //         return;
                //     } else {
                //         failure(res);
                //         enableSubmit();
                //     }
                // },

                // error: function(xhr) {
                //     applyValidationErrors(xhr.responseJSON);
                //     $("#cover-spin").hide();
                //     $("#signup-button").attr("class", "btn btn-primary mt-3");
                //     $("#signup-button").html('Submit');
                //     return;
                // }

                success: function(res) {

                    $("#cover-spin").hide();
                    $("#signup-button")
                        .attr("class", "btn btn-primary mt-3")
                        .html("Submit");

                    console.log(res);

                    if (!res || !res.status) {

                        if (res && res.errors) {
                            applyValidationErrors(res);
                        } else {
                            toastr.error(res?.message || "Something went wrong");
                        }

                        return;
                    }

                    const registrationType = Number(res.registration_type);

                    // Success toast
                    if (res.message) {
                        toastr.success(res.message);
                    }

                    // Redirect handling
                    if (res.redirect) {
                        if (registrationType === 1 || registrationType === 0) {
                            setTimeout(function() {
                                window.location.href = res.redirect;
                            }, 2000);
                        } else {
                            window.location.href = res.redirect;
                        }
                    }
                },
                error: function(xhr) {

                    $("#cover-spin").hide();
                    $("#signup-button")
                        .attr("class", "btn btn-primary mt-3")
                        .html("Submit");

                    console.log(xhr);

                    if (xhr.responseJSON) {
                        applyValidationErrors(xhr.responseJSON);
                    } else {
                        toastr.error("Server error");
                    }
                }

            });

        });

        $('#type_of_business_id').on('change', function() {
            const selectedText = $('#type_of_business_id option:selected').text();
            //alert(selectedText);

            if (selectedText == 'Others') {
                $('#other_div').show();
                $('#other').attr('required', true);
            } else {
                $('#other_div').hide();
                $('#other').val('').removeAttr('required');
            }
        })
    </script>
</body>

</html>
