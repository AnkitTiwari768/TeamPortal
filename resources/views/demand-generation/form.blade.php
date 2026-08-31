@extends('components.admin.layout')

@section('styles')
    <style>
        body {
            background: #f4f6f9;
            font-family: Inter, Arial, sans-serif;
            color: #111827;
        }


        /* ---------- TOP BAR  Start---------- */
        .top-nav {
            height: 80px;
            background: #ffffff;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .top-nav img {
            height: 43px;
        }

        .toggle-btn {
            background: #ebebeb;
            border: none;
            width: 38px;
            height: 38px;
            margin-left: 15px;
            border-radius: 4px;
        }

        .welcome-text {
            margin-left: 15px;
            font-weight: 500;
            font-size: 16px;
        }

        /* ---------- TOP NAV CSS END ---------- */

        /* Layout */
        /* ---------- LAYOUT ---------- */
        .layout {
            display: flex;
            margin-top: 80px;
        }


        .layout {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 16px;
        }

        .sidebar a {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            color: #374151;
            text-decoration: none;
            margin-bottom: 6px;
        }

        .sidebar a.active {
            background: #1e40af;
            color: #fff;
        }

        .content {
            flex: 1;
            padding: 24px;
        }

        /* Card */
        .card-main {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
            padding: 24px;
        }

        /* Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .back-btn {
            background: linear-gradient(46deg, #117AB1 40%, #09577A 100%);
            color: #fff;
            border-radius: 9px;
            padding: 7px 22px;
            font-size: 13px;
            align-items: center;
        }

        a.back-btn {
            text-decoration: none;
        }

        /* Form */
        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #151515;
        }

        label.required.form-label:after {
            content: '*';
            color: red;
            font-size: 19px;
            font-weight: 500;
            margin-left: 4px;
            line-height: 6px;
        }


        .form-control,
        .form-select {
            border-radius: 8px;
            font-size: 14px;
        }

        .form-control,
        .form-select {
            font-size: 14px;
            height: 47px !important;
            border-radius: 8px;
        }

        .form-control[readonly] {
            background: #eef1f4;
        }

        /* Section title */
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: #1e40af;
            margin: 22px 0 10px;
        }

        .section-title::before {
            content: "";
            width: 4px;
            height: 18px;
            background: #1e40af;
            border-radius: 2px;
        }

        /* Date */
        .date-field {
            position: relative;
        }

        .date-field i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
        }

        /* Order Details header */
        .order-header {
            background: #f1f7fd;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            margin-top: 10px;
        }

        .order-header span {
            font-weight: 600;
            font-size: 14px;
        }

        .order-header i {
            transition: .3s;
        }

        .order-header.collapsed i {
            transform: rotate(180deg);
        }

        /* Upload */
        .upload-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .claim-form .download-btn a {
            text-decoration: none;
        }

        .download-btn {
            border: 1px solid #214A85;
            color: #214A85;
            border-radius: 8px;
            padding: 014px;
            font-size: 13px;
            text-decoration: none;
        }

        .claim-form .download-btn a {
            color: #214A85;
        }

        /* Declaration */
        .claim-form .declaration {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
            position: relative;
            padding-left: 24px;
        }


        .claim-form .declaration>input {
            position: absolute;
            top: 0;
            left: 0;
            width: 16px;
            height: 16px;
            background-color: #dddd;
        }

        /* Submit */
        .claim-form .submit-btn {
            background: linear-gradient(46deg, #117AB1 40%, #09577A 100%);
            color: #fff;
            border-radius: 10px;
            padding: 10px 35px;
            font-size: 14px;
            margin-top: 16px;
            box-shadow: none;
            border: none;
        }

        /* Footer */
        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 14px;
        }

        /* Responsive */
        @media(max-width:992px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }

        .declaration-content h2 {
            color: #2E6DA4;
            margin-top: 1em;
            font-size: 10px !important;
        }

        .declaration-content p {
            margin-bottom: 1em;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Overlay */
        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            z-index: 9998;
        }

        /* Spinner */
        .loader-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 50px;
            height: 50px;
            border: 5px solid #e5e7eb;
            border-top: 5px solid #1e40af;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: spin 1s linear infinite;
            z-index: 9999;
        }

        @keyframes spin {
            100% {
                transform: translate(-50%, -50%) rotate(360deg);
            }
        }
    </style>
@endsection

@section('page-content')
    <div id="global-loader" style="display:none;">
        <div class="loader-overlay"></div>
        <div class="loader-spinner"></div>
    </div>
    <div class="content claim-form">
        <div class="card-main">



            <form id="claimForm" enctype="multipart/form-data">

                <!-- Header -->
                <div class="page-header">
                    <h5>Claim for Demand Generation Incentive</h5>
                    <a href="{{ url('dashboard') }}" class="back-btn">
                        <img src="{{ asset('assets/img/double_arrow.svg') }}" class="img-fluid"> Back
                    </a>
                </div>

                <!-- Top fields -->
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label class="form-label">Organisation ID of Buyer NP</label>
                        <input class="form-control" value="{{ $networkProvider->organization_id ?? '' }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class=" form-label">Buyer NP Configuration</label>
                        <input class="form-control" value="{{ $networkProvider->bppid_providerid ?? '' }}" readonly />
                    </div>

                    <!-- Type Dropdown commented out and hidden -->
                    <!--
                    <div class="col-md-4">
                        <label class="required form-label">GST Type</label>
                        <select id="gst_type" name="gst_type" class="form-select">
                            <option value="">-- Select GST Type --</option>
                            <option value="1">GST</option>
                            <option value="2">CGST/SGST</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="gst_percentage_wrapper" style="display:none;">
                        <label class="required form-label">GST Percentage</label>
                        <input type="number" name="gst_percentage" id="gst_percentage"
                            class="form-control @error('gst_percentage') is-invalid @enderror"
                            placeholder="Enter GST Percentage"
                            min="0" max="100" step="any"
                            value="{{ old('gst_percentage') }}" />
                        @error('gst_percentage')
                            <span class="invalid-feedback" role="alert" style="display:block;">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="col-md-4" id="cgst_wrapper" style="display:none;">
                        <label class="required form-label">CGST Percentage</label>
                        <input type="number" name="cgst_percentage" id="cgst_percentage"
                            class="form-control"
                            placeholder="Enter CGST Percentage"
                            min="0" max="100" step="any"
                            value="{{ old('cgst_percentage') }}" />
                    </div>

                    <div class="col-md-4" id="sgst_wrapper" style="display:none;">
                        <label class="required form-label">SGST Percentage</label>
                        <input type="number" name="sgst_percentage" id="sgst_percentage"
                            class="form-control"
                            placeholder="Enter SGST Percentage"
                            min="0" max="100" step="any"
                            value="{{ old('sgst_percentage') }}" />
                    </div>
                    -->
                    <input type="hidden" name="gst_type" id="gst_type" value="1" />
                    <input type="hidden" name="gst_percentage" id="gst_percentage" value="0" />
                    <input type="hidden" name="cgst_percentage" id="cgst_percentage" value="0" />
                    <input type="hidden" name="sgst_percentage" id="sgst_percentage" value="0" />
                </div>
                

                <div class="row g-3 mt-3">
                    {{-- <div class="col-md-4">
                        <label class="required form-label">Low AOV Categories</label>
                        <select id="lowAovCategories" name="low_aov_categories[]" class="form-select select2" multiple>
                            <option>Select</option>
                            @isset($productCategories['lowAov'])
                                @foreach ($productCategories['lowAov'] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Number of Unique MSEs</label>
                        <input class="form-control integer" name="low_aov_unique_mse" placeholder="Number of Unique MSEs">
                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Cumulative Transactions</label>
                        <input class="form-control integer" name="low_aov_cumulative_txn"
                            placeholder="Cumulative Transactions">
                    </div> --}}
                    <div class="col-md-4">
                        <label class=" form-label">Claim Period</label>
                        <div class="date-field">
                            <input id="lowAovClaimPeriod" name="low_aov_claim_period" class="form-control"
                                placeholder="Select Date Range" readonly />
                            <i class="fa fa-calendar" id="lowAovClaimPeriodIcon"></i>

                            <!-- Hidden fields for low AOV claim period -->
                            <input type="hidden" name="low_aov_claim_start_date" id="lowAovClaimPeriodStart">
                            <input type="hidden" name="low_aov_claim_end_date" id="lowAovClaimPeriodEnd">
                        </div>
                        <span class="text-danger form-error">Note: All the order creation timestamps must lie within the selected Claim Period Range</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Upload Order Details for Claim Transactions</label>
                        <input type="file" class="form-control" name="low_aov_excel" accept=".xls,.xlsx,.csv">
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('download.csv.template') }}" class="btn btn-outline-primary download-btn mt-3">
                            Download the Predefined Format
                        </a>
                    </div>
                </div>



                <!-- HIGH AOV -->
                {{-- <div class="section-title">High AOV Categories</div> --}}

                {{-- <div class="row g-3">
                    {{-- <div class="col-md-4">
                        <label class="required form-label">High AOV Categories</label>
                        <select id="highAovCategories" name="high_aov_categories[]" class="form-select select2" multiple>
                            <option value="">Select</option>

                            @isset($productCategories['highAov'])
                                @foreach ($productCategories['highAov'] as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            @endisset
                        </select>

                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Number of Unique MSEs</label>
                        <input class="form-control integer" name="high_aov_unique_mse" placeholder="Number of Unique MSEs">
                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Cumulative Transactions</label>
                        <input class="form-control integer" name="high_aov_cumulative_txn"
                            placeholder="Cumulative Transactions">
                    </div>
                    <div class="col-md-4">
                        <label class=" form-label">Claim Period</label>
                        <div class="date-field">
                            <input id="highAovClaimPeriod" name="high_aov_claim_period" class="form-control"
                                placeholder="Select Date Range" readonly />
                            <i class="fa fa-calendar"></i>

                            <!-- Hidden fields for high AOV claim period -->
                            <input type="hidden" name="high_aov_claim_period_start" id="highAovClaimPeriodStart">
                            <input type="hidden" name="high_aov_claim_period_end" id="highAovClaimPeriodEnd">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class=" form-label">Upload Order Details for Claim Transactions</label>
                        <input type="file" class="form-control" name="high_aov_excel" accept=".xls,.xlsx,.csv">
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('download.template') }}" class="btn btn-outline-primary download-btn mt-3">
                            Download the Predefined Format
                        </a>
                    </div>
                </div> --}}

                <!-- Declaration -->

                <div class="declaration mt-4">
                    <input type="checkbox" name="declaration" />
                    <div>
                        <strong>Declaration:</strong><br>
                        {!! $declarationContent ?? '' !!}
                    </div>
                </div>

                <!-- Upload Button -->
                <button type="button" class="submit-btn upload-btn mt-3">
                    <i class="fa fa-upload me-1"></i>
                    Upload
                </button>

                <!-- Final Submit Button (Hidden initially) -->
                <button type="button" class="submit-btn final-submit-btn mt-3" style="display:none;">
                    <i class="fa fa-paper-plane me-1"></i>
                    Submit Your Claim
                </button>

                <button type="button" class="submit-btn reset-btn btn-secondary mt-3" style="display:none;">
                    <i class="fa fa-refresh me-1"></i>
                    Reset
                </button>
            </form>

            <div id="csv-errors" class="mt-3"></div>
        </div> <!-- card-main -->
    </div> <!-- content -->
@endsection

@section('js')
    <script>
           // ── checkClaimFields ─────────────────────────────────────────
    function checkClaimFields() {
        let lowAovPeriod  = $('#lowAovClaimPeriod').val();
        let lowAovFile    = $('input[name="low_aov_excel"]').val();
        let highAovPeriod = $('#highAovClaimPeriod').val();
        let highAovFile   = $('input[name="high_aov_excel"]').val();

        let gstType  = $('#gst_type').val();
        let gstValid = false;

        if (gstType === '1') {
            let gstPercentage = $('#gst_percentage').val();
            if (gstPercentage !== '' && !isNaN(gstPercentage)) {
                let v = parseFloat(gstPercentage);
                gstValid = (v >= 0 && v <= 100);
            }
            if (gstPercentage !== '' && !gstValid) {
                $('#gst_percentage').addClass('is-invalid');
                if ($('#gst-feedback').length === 0)
                    $('#gst_percentage').after('<span id="gst-feedback" class="invalid-feedback" style="display:block;"><strong>GST Percentage must be a number between 0 and 100.</strong></span>');
            } else {
                $('#gst_percentage').removeClass('is-invalid');
                $('#gst-feedback').remove();
            }

        } else if (gstType === '2') {
            let cgst = $('#cgst_percentage').val();
            let sgst = $('#sgst_percentage').val();

            let cgstValid = cgst !== '' && !isNaN(cgst) && parseFloat(cgst) >= 0 && parseFloat(cgst) <= 100;
            let sgstValid = sgst !== '' && !isNaN(sgst) && parseFloat(sgst) >= 0 && parseFloat(sgst) <= 100;

            if (cgst !== '' && !cgstValid) {
                $('#cgst_percentage').addClass('is-invalid');
                if ($('#cgst-feedback').length === 0)
                    $('#cgst_percentage').after('<span id="cgst-feedback" class="invalid-feedback" style="display:block;"><strong>CGST must be between 0 and 100.</strong></span>');
            } else {
                $('#cgst_percentage').removeClass('is-invalid');
                $('#cgst-feedback').remove();
            }

            if (sgst !== '' && !sgstValid) {
                $('#sgst_percentage').addClass('is-invalid');
                if ($('#sgst-feedback').length === 0)
                    $('#sgst_percentage').after('<span id="sgst-feedback" class="invalid-feedback" style="display:block;"><strong>SGST must be between 0 and 100.</strong></span>');
            } else {
                $('#sgst_percentage').removeClass('is-invalid');
                $('#sgst-feedback').remove();
            }

            gstValid = cgstValid && sgstValid;
        }

        if (((lowAovPeriod && lowAovFile) || (highAovPeriod && highAovFile)) && gstValid) {
            $('.submit-btn').prop('disabled', false);
        } else {
            $('.submit-btn').prop('disabled', true);
        }
    }

$(document).ready(function () {

    // Disable submit initially
    $('.submit-btn').prop('disabled', true);

 
    // ── GST Type dropdown toggle ──────────────────────────────────
    $('#gst_type').on('change', function () {
        let type = $(this).val();

        $('#gst_percentage_wrapper').hide();
        $('#cgst_wrapper').hide();
        $('#sgst_wrapper').hide();

        $('#gst_percentage').val('').removeClass('is-invalid');
        $('#cgst_percentage').val('').removeClass('is-invalid');
        $('#sgst_percentage').val('').removeClass('is-invalid');
        $('#gst-feedback, #cgst-feedback, #sgst-feedback').remove();

        if (type === '1') {
            $('#gst_percentage_wrapper').show();
        } else if (type === '2') {
            $('#cgst_wrapper').show();
            $('#sgst_wrapper').show();
        }

        checkClaimFields();
    });

    // ── Field change listeners ────────────────────────────────────
    $('#lowAovClaimPeriod, #highAovClaimPeriod').on('change apply.daterangepicker cancel.daterangepicker', checkClaimFields);
    $('input[name="low_aov_excel"], input[name="high_aov_excel"]').on('change', checkClaimFields);
    $('#gst_percentage, #cgst_percentage, #sgst_percentage').on('input change', checkClaimFields);

    // ── Date Range Pickers ────────────────────────────────────────
    function initDateRangePicker(inputId, startId, endId) {
        $(inputId).daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear',
                format: 'DD-MM-YYYY'
            },
            maxDate: moment(),
            maxSpan: { days: 59 },
            opens: 'left'
        });

        $(inputId).on('apply.daterangepicker', function (ev, picker) {
            $(this).val(
                picker.startDate.format('DD-MM-YYYY') + ' - ' +
                picker.endDate.format('DD-MM-YYYY')
            ).trigger('change');
            $(startId).val(picker.startDate.format('YYYY-MM-DD'));
            $(endId).val(picker.endDate.format('YYYY-MM-DD'));
        });

        $(inputId).on('cancel.daterangepicker', function () {
            $(this).val('').trigger('change');
            $(startId).val('');
            $(endId).val('');
        });
    }

    initDateRangePicker('#lowAovClaimPeriod',  '#lowAovClaimPeriodStart',  '#lowAovClaimPeriodEnd');
    initDateRangePicker('#highAovClaimPeriod', '#highAovClaimPeriodStart', '#highAovClaimPeriodEnd');

    $('.date-field i.fa-calendar, #lowAovClaimPeriodIcon').on('click', function () {
        $(this).siblings('input').click();
    });

    // Select2
    $('#lowAovCategories, #highAovCategories').select2({
        placeholder: "Select",
        allowClear: true
    });

    // Run once on load
    checkClaimFields();

});

        // Type dropdown toggle
        $('#gst_type').on('change', function () {
            let type = $(this).val();

            // Hide all GST-related fields first & clear their values
            $('#gst_percentage_wrapper').hide();
            $('#cgst_wrapper').hide();
            $('#sgst_wrapper').hide();

            $('#gst_percentage').val('').removeClass('is-invalid');
            $('#cgst_percentage').val('').removeClass('is-invalid');
            $('#sgst_percentage').val('').removeClass('is-invalid');
            $('#gst-feedback, #cgst-feedback, #sgst-feedback').remove();

            if (type === '1') {
                $('#gst_percentage_wrapper').show();
            } else if (type === '2') {
                $('#cgst_wrapper').show();
                $('#sgst_wrapper').show();
            }

           checkClaimFields();
        });

        // Also listen to CGST/SGST inputs
        $('#cgst_percentage, #sgst_percentage').on('input change', checkClaimFields);


        $(function() {

            function initDateRangePicker(inputId, startId, endId) {
                $(inputId).daterangepicker({
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'DD-MM-YYYY'
                    },
                    maxDate: moment(),

                    // ✅ Restrict range to max 60 days
                    maxSpan: {
                        days: 60-1
                    },

                    opens: 'left'
                });

                $(inputId).on('apply.daterangepicker', function(ev, picker) {

                    $(this).val(
                        picker.startDate.format('DD-MM-YYYY') + ' - ' +
                        picker.endDate.format('DD-MM-YYYY')
                    ).trigger('change');

                    $(startId).val(picker.startDate.format('YYYY-MM-DD'));
                    $(endId).val(picker.endDate.format('YYYY-MM-DD'));
                });

                $(inputId).on('cancel.daterangepicker', function() {
                    $(this).val('').trigger('change');
                    $(startId).val('');
                    $(endId).val('');
                });
            }

            // ✅ Initialize both pickers
            initDateRangePicker('#lowAovClaimPeriod', '#lowAovClaimPeriodStart', '#lowAovClaimPeriodEnd');
            initDateRangePicker('#highAovClaimPeriod', '#highAovClaimPeriodStart', '#highAovClaimPeriodEnd');

            $('.date-field i.fa-calendar, #lowAovClaimPeriodIcon').on('click', function() {
                $(this).siblings('input').click();
            });

            // Select2
            $('#lowAovCategories, #highAovCategories').select2({
                placeholder: "Select",
                allowClear: true
            });

        });
    </script>
    <script>
        $(document).ajaxStart(function() {
            $('#cover-spin').show();
        });

        $(document).ajaxStop(function() {
            $('#cover-spin').hide();
        });
        $(document).on('click', '.upload-btn', function(e) {
            e.preventDefault();

            let form = $('#claimForm')[0];
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('demand-generation.import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $(".upload-btn")
                        .prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...');
                },
                success: function(response) {

                    $(".upload-btn")
                        .prop('disabled', false)
                        .html('<i class="fa fa-upload me-1"></i> Upload');

                    if (response.status) {
                        toastr.success(response.message);

                        // Always show errors if present
                        if (response.summary.failed > 0) {
                            displayCsvErrors(response);
                        } else {
                            $("#csv-errors").html('');
                        }

                        // ✅ MAIN CONDITION
                        if (response.summary.inserted > 0) {

                            // Hide upload
                            $('.upload-btn').hide();

                            // Show submit + reset
                            $('.final-submit-btn').show();
                            $('.reset-btn').show();

                            // Disable form
                            disableForm();

                        } else {
                            // No valid rows inserted

                            $('.final-submit-btn').hide();
                            $('.reset-btn').hide();
                            $('.upload-btn').show();
                        }

                    } else {
                        toastr.error(response.message);

                        $('.final-submit-btn').hide();
                        $('.reset-btn').hide();
                    }
                },
                error: function(err) {
                    if (err.status === 422) {
                        toastr.error(err.responseJSON.message);
                    } else {
                        toastr.error('An unexpected error occurred. Please try again.');
                    }
                    $(".upload-btn").prop('disabled', false).text('Upload');
                }
            });
        });

        $(document).on('click', '.final-submit-btn', function(e) {
            e.preventDefault();

            $.ajax({
                url: "{{ route('demand-generation.submit') }}", // ✅ create this route
                type: "POST",
                data: {
                    gst_type        : $('#gst_type').val(),         // ✅ added
                    gst_percentage  : $('#gst_percentage').val(),
                    cgst_percentage : $('#cgst_percentage').val(),  // ✅ added
                    sgst_percentage : $('#sgst_percentage').val(),  // ✅ added
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $(".final-submit-btn")
                        .prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin me-1"></i> Submitting...');
                },
                success: function(response) {
                    $(".final-submit-btn")
                        .prop('disabled', false)
                        .html('<i class="fa fa-paper-plane me-1"></i> Submit Your Claim');

                    if (response.status) {
                        toastr.success('Claim submitted successfully');

                        // Optional reset
                        $('.final-submit-btn').hide();
                        $('#claimForm')[0].reset();

                        setTimeout(function() {
                            window.location.href = "{{ url('/demand-generation-claim') }}";
                        }, 1000);

                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    $(".final-submit-btn").prop('disabled', false).text('Submit Your Claim');
                    toastr.error(xhr.responseJSON.message ?? 'Submission failed');
                }
            });
        });

        $(document).on('click', '.reset-btn', function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to reset the uploaded data?')) {
                return;
            }

            $.ajax({
                url: "{{ route('demand-generation.reset') }}", // ✅ create this
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('.reset-btn')
                        .prop('disabled', true)
                        .html('<i class="fa fa-spinner fa-spin me-1"></i> Resetting...');
                },
                success: function(response) {

                    $('.reset-btn')
                        .prop('disabled', false)
                        .html('<i class="fa fa-refresh me-1"></i> Reset');

                    if (response.status) {
                        toastr.success('Reset successful');

                        // ✅ Reset form
                        $('#claimForm')[0].reset();

                        // ✅ Clear CSV errors
                        $("#csv-errors").html('');

                        // ✅ Reset date pickers
                        resetDatePickers();

                        // ✅ Enable form again
                        enableForm();

                        // Hide reset button
                        $('.reset-btn').hide();

                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function() {
                    $('.reset-btn').prop('disabled', false).text('Reset');
                    toastr.error('Reset failed');
                }
            });
        });

        function displayCsvErrors(response) {

            let html = `
        <div class="alert alert-danger">
            <strong>Import Summary</strong><br>
            Total Rows: ${response.summary.total_rows} <br>
            Inserted: ${response.summary.inserted} <br>
            Failed: ${response.summary.failed}
        </div>
    `;

            // ✅ Download full error report
            if (response.error_token) {
                html += `
            <a href="{{ url('demand-generation-error-report') }}/${response.error_token}"
               class="btn btn-danger btn-sm mb-3">
               ⬇ Download Complete Error Report
            </a>
        `;
            }

            function formatFieldName(field) {
                return field
                    .replace(/_/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());
            }

            response.failed_rows.forEach(row => {

                const teamId = row.data.unique_team_registration_id_of_the_mse ??
                    'TEAM ID not provided';

                html += `
            <div class="card mb-3 border-danger">
                <div class="card-header bg-danger text-white">
                    Row ${row.row_number} (${teamId})
                </div>
                <div class="card-body">
                    <ul class="mb-0">
        `;

                Object.keys(row.errors).forEach(field => {
                    row.errors[field].forEach(message => {
                        html += `
                    <li>
                        <strong>${formatFieldName(field)}</strong>: ${message}
                    </li>
                `;
                    });
                });

                html += `
                    </ul>
                </div>
            </div>
        `;
            });

            $("#csv-errors").html(html);
        }

        function disableForm() {
            $('#claimForm')
                .find('input, select, textarea, button')
                .not('.final-submit-btn, .reset-btn') // keep these enabled
                .prop('disabled', true);
        }

        function enableForm() {
            $('#claimForm')
                .find('input, select, textarea, button')
                .prop('disabled', false);

            // Reset button visibility
            $(".upload-btn")
                .prop('disabled', false)
                .html('<i class="fa fa-upload me-1"></i> Upload')
                .show();
            $('.final-submit-btn').hide();
        }

        function resetDatePickers() {

            // LOW AOV
            $('#lowAovClaimPeriod').val('');
            $('#lowAovClaimPeriodStart').val('');
            $('#lowAovClaimPeriodEnd').val('');

            let lowPicker = $('#lowAovClaimPeriod').data('daterangepicker');
            if (lowPicker) {
                lowPicker.setStartDate(moment());
                lowPicker.setEndDate(moment());
            }

            // HIGH AOV
            $('#highAovClaimPeriod').val('');
            $('#highAovClaimPeriodStart').val('');
            $('#highAovClaimPeriodEnd').val('');

            let highPicker = $('#highAovClaimPeriod').data('daterangepicker');
            if (highPicker) {
                highPicker.setStartDate(moment());
                highPicker.setEndDate(moment());
            }
        }
    </script>
@endsection
