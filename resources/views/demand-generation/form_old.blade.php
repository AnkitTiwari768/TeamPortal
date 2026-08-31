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
    </style>
@endsection

@section('page-content')
    <div class="content claim-form">
        <div class="card-main">
            <form id="claimForm" enctype="multipart/form-data">

                <!-- Header -->
                <div class="page-header">
                    <h5>Claim for Demand Generation Incentive</h5>
                    <a class="back-btn">
                        <img src="{{ asset('assets/img/double_arrow.svg') }}" class="img-fluid"> Back
                    </a>
                </div>

                <!-- Top fields -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="required form-label">Organisation ID of Buyer NP</label>
                        <input class="form-control" value="{{ $networkProvider->organization_id ?? '' }}" readonly />
                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Buyer NP Configuration</label>
                        <input class="form-control" value="{{ $networkProvider->bppid_providerid ?? '' }}" readonly />
                    </div>
                </div>

                <!-- LOW AOV -->
                <div class="section-title">Low AOV Categories</div>

                <div class="row g-3">
                    <div class="col-md-4">
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
                    </div>
                    <div class="col-md-4">
                        <label class="required form-label">Claim Period</label>
                        <div class="date-field">
                            <input id="lowAovClaimPeriod" name="low_aov_claim_period" class="form-control"
                                placeholder="Select Date Range" readonly />
                            <i class="fa fa-calendar"></i>

                            <!-- Hidden fields for low AOV claim period -->
                            <input type="hidden" name="low_aov_claim_period_start" id="lowAovClaimPeriodStart">
                            <input type="hidden" name="low_aov_claim_period_end" id="lowAovClaimPeriodEnd">
                        </div>
                    </div>
                </div>

                <!-- Order Details Low -->
                <div class="order-header collapsed" data-bs-toggle="collapse" data-bs-target="#lowOrder"
                    onclick="this.classList.toggle('collapsed')" aria-expanded="true">
                    <span>Order Details for each claim txn</span>
                    <i class="fa fa-chevron-up"></i>
                </div>

                <div class="mt-3 collapse show" id="lowOrder">
                    <label class="required form-label">
                        Upload Order Details for Claim Transactions
                    </label>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="file" class="form-control" name="low_aov_excel" accept=".xls,.xlsx,.csv">
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary download-btn">
                                Download the Predefined Format
                            </a>
                        </div>
                    </div>
                </div>

                <!-- HIGH AOV -->
                <div class="section-title">High AOV Categories</div>

                <div class="row g-3">
                    <div class="col-md-4">
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
                        <label class="required form-label">Claim Period</label>
                        <div class="date-field">
                            <input id="highAovClaimPeriod" name="high_aov_claim_period" class="form-control"
                                placeholder="Select Date Range" readonly />
                            <i class="fa fa-calendar"></i>

                            <!-- Hidden fields for high AOV claim period -->
                            <input type="hidden" name="high_aov_claim_period_start" id="highAovClaimPeriodStart">
                            <input type="hidden" name="high_aov_claim_period_end" id="highAovClaimPeriodEnd">
                        </div>
                    </div>
                </div>

                <!-- Order Details High -->
                <div class="order-header mt-2 collapsed" data-bs-toggle="collapse" data-bs-target="#highOrder"
                    onclick="this.classList.toggle('collapsed')" aria-expanded="true">
                    <span>Order Details for each claim txn</span>
                    <i class="fa fa-chevron-up"></i>
                </div>

                <div class="mt-3 collapse show" id="highOrder">
                    <label class="required form-label">
                        Upload Order Details for Claim Transactions
                    </label>

                    <div class="row g-3 align-items-center">
                        <div class="col-md-4">
                            <input type="file" class="form-control" name="high_aov_excel" accept=".xls,.xlsx,.csv">
                        </div>
                        <div class="col-md-4">
                            <a href="#" class="btn btn-outline-primary download-btn">
                                Download the Predefined Format
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Declaration -->
                <div class="declaration mt-4">
                    <input type="checkbox" name="declaration">
                    <div>
                        <strong>Declaration:</strong><br>
                        {{ $declarationContent ?? '' }}
                    </div>
                </div>

                <!-- Submit -->
                <button class="submit-btn mt-3">
                    <i class="fa fa-paper-plane me-1"></i>
                    Submit your Claim
                </button>

            </form>
        </div> <!-- card-main -->
    </div> <!-- content -->
@endsection

@section('js')
    <script>
        $(function() {
            $('#lowAovClaimPeriod').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                maxDate: moment(),
                opens: 'left'
            });

            $('#lowAovClaimPeriod').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $('#lowAovClaimPeriodStart').val(picker.startDate.format('YYYY-MM-DD'));
                $('#lowAovClaimPeriodEnd').val(picker.endDate.format('YYYY-MM-DD'));
            });

            $('#lowAovClaimPeriod').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#lowAovClaimPeriodStart').val('');
                $('#lowAovClaimPeriodEnd').val('');
            });

            // Initialize date range picker for High AOV
            $('#highAovClaimPeriod').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'
                },
                maxDate: moment(),
                opens: 'left'
            });

            // When date range is selected for High AOV
            $('#highAovClaimPeriod').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format(
                    'YYYY-MM-DD'));
                $('#highAovClaimPeriodStart').val(picker.startDate.format('YYYY-MM-DD'));
                $('#highAovClaimPeriodEnd').val(picker.endDate.format('YYYY-MM-DD'));
            });

            // When date range is cleared for High AOV
            $('#highAovClaimPeriod').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                $('#highAovClaimPeriodStart').val('');
                $('#highAovClaimPeriodEnd').val('');
            });

            $('#lowAovCategories').select2({
                placeholder: "Select",
                allowClear: true
            });

            $('#highAovCategories').select2({
                placeholder: "Select",
                allowClear: true
            });
        });
    </script>
    <script>
        $(document).on('click', '.submit-btn', function(e) {
            e.preventDefault();

            $("#cover-spin").show();
            $(".submit-btn").prop('disabled', true).text('Submitting...');

            let form = $('#claimForm')[0];
            let formData = new FormData(form);

            $.ajax({
                url: "{{ route('demand-generation.store') }}",
                type: "POST",
                data: formData,
                processData: false, // IMPORTANT
                contentType: false, // IMPORTANT
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('.submit-btn').prop('disabled', true);
                },
                success: function(response) {
                    $("#cover-spin").hide();
                    $(".submit-btn").prop('disabled', false).text('Submit');

                    if (data.status) {
                        toastr.success(data.message || 'Claim submitted successful!');
                        setTimeout(function() {
                            window.location.href =
                                "{{ url('file-demand-generation-incentive-claim') }}";
                        }, 1000);
                    } else {
                        if (data.errors) {
                            applyValidationErrors(data);
                            toastr.error('Please correct the errors in the form.');

                        } else {
                            toastr.error(data.message ||
                                'Error occurred while submit the claim. Please try again!');
                        }
                    }
                },
                error: function(xhr) {
                    $('.submit-btn').prop('disabled', false);
                    applyValidationErrors(xhr.responseJSON.errors);
                    $("#cover-spin").hide();
                    $(".submit-btn").prop('disabled', false).text('Submit');
                    toastr.error('An error occurred. Please try again.');
                }
            });
        });
    </script>
@endsection
