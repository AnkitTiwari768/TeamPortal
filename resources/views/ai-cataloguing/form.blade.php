@extends('components.admin.layout')

@section('styles')
    <style>
        body {
            background: #f4f6f9;
            font-family: Inter, Arial, sans-serif;
            color: #111827;
        }

        .layout {
            display: flex;
            margin-top: 80px;
        }

        .content {
            flex: 1;
            padding: 24px;
        }

        .card-main {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .06);
            padding: 24px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 15px;
        }

        .page-header h5 {
            margin: 0;
            font-weight: 600;
            color: #1f2937;
        }

        .back-btn {
            background: linear-gradient(46deg, #117AB1 40%, #09577A 100%);
            color: #fff !important;
            border-radius: 9px;
            padding: 7px 22px;
            font-size: 13px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

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
        }

        .form-control,
        .form-select {
            font-size: 14px;
            height: 47px !important;
            border-radius: 8px;
        }

        .form-control[readonly] {
            background-color: #f3f4f6;
        }

        .btn-submit {
            background: #10b981;
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .btn-submit:hover:not(:disabled) {
            background: #059669;
            color: #fff;
        }

        .btn-submit:disabled {
            background: #a7f3d0;
            cursor: not-allowed;
        }

        .btn-import {
            background: #3b82f6;
            color: #fff;
            border: none;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .btn-import:hover:not(:disabled) {
            background: #2563eb;
            color: #fff;
        }

        .btn-import:disabled {
            background: #bfdbfe;
            cursor: not-allowed;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .error-table-wrapper {
            margin-top: 20px;
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 15px;
        }

        .loader-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9999;
        }

        .loader-spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            z-index: 10000;
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
                @csrf
                <div class="page-header">
                    <h5>AI Cataloguing Incentive Claim</h5>
                    <a href="{{ url('ai-cataloguing-claim') }}" class="back-btn">
                        <img src="{{ asset('assets/img/double_arrow.svg') }}" class="img-fluid"> Back
                    </a>
                </div>

                <!-- GST fields commented out and hidden -->
                <!--
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label class="required form-label">GST Type</label>
                        <select id="gst_type" name="gst_type" class="form-select">
                            <option value="">-- Select GST Type --</option>
                            <option value="1">GST (IGST)</option>
                            <option value="2">CGST/SGST</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="gst_percentage_wrapper" style="display:none;">
                        <label class="required form-label">GST Percentage</label>
                        <input type="number" name="gst_percentage" id="gst_percentage" class="form-control"
                            placeholder="Enter GST Percentage" min="0" max="100" step="any" />
                    </div>

                    <div class="col-md-4" id="cgst_wrapper" style="display:none;">
                        <label class="required form-label">CGST Percentage</label>
                        <input type="number" name="cgst_percentage" id="cgst_percentage" class="form-control"
                            placeholder="Enter CGST Percentage" min="0" max="100" step="any" />
                    </div>

                    <div class="col-md-4" id="sgst_wrapper" style="display:none;">
                        <label class="required form-label">SGST Percentage</label>
                        <input type="number" name="sgst_percentage" id="sgst_percentage" class="form-control"
                            placeholder="Enter SGST Percentage" min="0" max="100" step="any" />
                    </div>
                </div>
                -->
                <input type="hidden" name="gst_type" id="gst_type" value="1" />
                <input type="hidden" name="gst_percentage" id="gst_percentage" value="0" />
                <input type="hidden" name="cgst_percentage" id="cgst_percentage" value="0" />
                <input type="hidden" name="sgst_percentage" id="sgst_percentage" value="0" />

                <div class="section-title mt-4 mb-2" style="font-size: 16px; font-weight: 600; color: #374151;">Upload Claim
                    Sheet</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="required form-label">Upload Excel/CSV File</label>
                        <input type="file" class="form-control" name="excel_file" id="excel_file"
                            accept=".xls,.xlsx,.csv" required>
                        <span class="text-danger form-error" style="font-size: 11px;">Expected format columns: Udyam Number,
                            Catalogue ID, Catalogue Finalization Date, Catalogue Completion Status, Digital Catalogue
                            Footprint, Amount Claimed with Financial Reconciliation Report</span>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <a href="{{ route('ai-cataloguing.download-template') }}" class="btn btn-outline-primary"
                            style="height: 47px; display: inline-flex; align-items: center; justify-content: center; width: 100%;">
                            <i class="fa fa-download me-2"></i> Download Predefined Template Format
                        </a>
                    </div>
                </div>

                <!-- Declaration -->
                <div class="mt-4 p-3 border rounded bg-light">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="declaration" id="declaration" value="1"
                            required>
                        <label class="form-check-label" for="declaration"
                            style="font-size: 13px; font-weight: 500; cursor: pointer;">
                            {!! $declarationContent ??
                                'I hereby declare that all the information uploaded is correct and matches ONDC registry.' !!}
                        </label>
                    </div>
                </div>

                <!-- Import / Submit Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <button type="button" id="btn_import" class="btn-import" disabled>
                            <i class="fa fa-upload me-2"></i> Upload
                        </button>
                        <button type="button" id="btn_submit" class="btn-submit" style="display:none;" disabled>
                            <i class="fa fa-check me-2"></i> Submit Claim
                        </button>
                    </div>
                </div>
            </form>

            <!-- Import Summary & Errors -->
            <div id="import_summary_section" style="display:none;" class="summary-card">
                <h6 class="border-bottom pb-2 mb-3" style="font-weight: 600; color: #1e3a8a;">Sheet Processing Summary</h6>
                <div class="row text-center">
                    <div class="col-md-4 border-end">
                        <div style="font-size: 13px; color: #64748b;">Total Processed Rows</div>
                        <div id="summary_total" style="font-size: 24px; font-weight: 700; color: #1e293b;">0</div>
                    </div>
                    <div class="col-md-4 border-end">
                        <div style="font-size: 13px; color: #64748b;">Valid Records (Ready for claim)</div>
                        <div id="summary_valid" style="font-size: 24px; font-weight: 700; color: #10b981;">0</div>
                    </div>
                    <div class="col-md-4">
                        <div style="font-size: 13px; color: #64748b;">Failed/Invalid Records</div>
                        <div id="summary_failed" style="font-size: 24px; font-weight: 700; color: #ef4444;">0</div>
                    </div>
                </div>
            </div>

            <div id="import_errors_section" style="display:none;" class="error-table-wrapper">
                <div class="d-flex justify-between align-items-center mb-3">
                    <h6 style="color: #c53030; font-weight: 600; margin: 0;"><i
                            class="fa fa-exclamation-triangle me-2"></i> Invalid Records List (Showing Top 10)</h6>
                    <a href="javascript:void(0)" id="download_error_report" class="btn btn-sm btn-danger"><i
                            class="fa fa-download me-2"></i> Download Error Report</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" id="error_table"
                        style="font-size: 12px; background: #fff;">
                        <thead>
                            <tr class="table-danger">
                                <th>Row</th>
                                <th>Udyam Number</th>
                                <th>Catalogue ID</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('js')
    <script>
        $(document).ready(function() {
            let errorToken = '';

            function checkClaimFields() {
                let fileSelected = $('#excel_file').val() !== '';
                let gstType = $('#gst_type').val();
                let gstValid = false;
                let isDeclarationChecked = $('#declaration').is(':checked');

                // Clear previous validation classes & errors
                $('#gst_percentage').removeClass('is-invalid');
                $('#cgst_percentage').removeClass('is-invalid');
                $('#sgst_percentage').removeClass('is-invalid');
                $('#gst-feedback, #cgst-feedback, #sgst-feedback').remove();

                if (gstType === '1') {
                    let gstPercentage = $('#gst_percentage').val();
                    if (gstPercentage !== '' && !isNaN(gstPercentage)) {
                        let v = parseFloat(gstPercentage);
                        gstValid = (v >= 0 && v <= 100);
                    }
                    if (gstPercentage !== '' && !gstValid) {
                        $('#gst_percentage').addClass('is-invalid');
                        if ($('#gst-feedback').length === 0) {
                            $('#gst_percentage').after(
                                '<span id="gst-feedback" class="invalid-feedback" style="display:block;"><strong>GST Percentage must be a number between 0 and 100.</strong></span>'
                                );
                        }
                    }
                } else if (gstType === '2') {
                    let cgst = $('#cgst_percentage').val();
                    let sgst = $('#sgst_percentage').val();

                    let cgstValid = cgst !== '' && !isNaN(cgst) && parseFloat(cgst) >= 0 && parseFloat(cgst) <= 100;
                    let sgstValid = sgst !== '' && !isNaN(sgst) && parseFloat(sgst) >= 0 && parseFloat(sgst) <= 100;

                    if (cgst !== '' && !cgstValid) {
                        $('#cgst_percentage').addClass('is-invalid');
                        if ($('#cgst-feedback').length === 0) {
                            $('#cgst_percentage').after(
                                '<span id="cgst-feedback" class="invalid-feedback" style="display:block;"><strong>CGST must be between 0 and 100.</strong></span>'
                                );
                        }
                    }
                    if (sgst !== '' && !sgstValid) {
                        $('#sgst_percentage').addClass('is-invalid');
                        if ($('#sgst-feedback').length === 0) {
                            $('#sgst_percentage').after(
                                '<span id="sgst-feedback" class="invalid-feedback" style="display:block;"><strong>SGST must be between 0 and 100.</strong></span>'
                                );
                        }
                    }
                    gstValid = cgstValid && sgstValid;
                }

                // Import button requires file, gst selection, and declaration checked
                if (fileSelected && gstType && gstValid && isDeclarationChecked) {
                    $('#btn_import').prop('disabled', false);
                } else {
                    $('#btn_import').prop('disabled', true);
                }
            }

            $('#gst_type').on('change', function() {
                let type = $(this).val();

                $('#gst_percentage_wrapper').hide();
                $('#cgst_wrapper').hide();
                $('#sgst_wrapper').hide();

                $('#gst_percentage').val('');
                $('#cgst_percentage').val('');
                $('#sgst_percentage').val('');

                if (type === '1') {
                    $('#gst_percentage_wrapper').show();
                } else if (type === '2') {
                    $('#cgst_wrapper').show();
                    $('#sgst_wrapper').show();
                }
                checkClaimFields();
            });

            // Listeners for enabling import button
            $('#excel_file, #declaration').on('change', checkClaimFields);
            $('#gst_percentage, #cgst_percentage, #sgst_percentage').on('input change', checkClaimFields);

            // Upload & Verify sheet
            $('#btn_import').on('click', function() {
                let formData = new FormData($('#claimForm')[0]);

                $.ajax({
                    url: "{{ route('ai-cataloguing.import') }}",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('#global-loader').show();
                        $('#import_summary_section').hide();
                        $('#import_errors_section').hide();
                        $('#btn_submit').hide().prop('disabled', true);
                    },
                    success: function(res) {
                        $('#global-loader').hide();
                        if (res.status) {
                            toastr.success(res.message);
                            $('#summary_total').text(res.summary.total_rows);
                            $('#summary_valid').text(res.summary.inserted);
                            $('#summary_failed').text(res.summary.failed);
                            $('#import_summary_section').show();

                            if (res.summary.failed > 0) {
                                errorToken = res.error_token;
                                let tbody = $('#error_table tbody');
                                tbody.empty();
                                res.failed_rows.forEach(function(row) {
                                    let errorsHtml = '';
                                    for (let field in row.errors) {
                                        errorsHtml +=
                                            `<div><strong>${field}:</strong> ${row.errors[field].join(', ')}</div>`;
                                    }
                                    tbody.append(`
                                        <tr>
                                            <td>${row.row_number}</td>
                                            <td>${row.data.udyam_number || ''}</td>
                                            <td>${row.data.catalogue_id || ''}</td>
                                            <td class="text-danger">${errorsHtml}</td>
                                        </tr>
                                    `);
                                });
                                $('#import_errors_section').show();
                            }

                            if (res.summary.inserted > 0) {
                                $('#btn_submit').show().prop('disabled', false);
                            }
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        $('#global-loader').hide();
                        let msg = 'Failed to upload sheet.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });

            // Error report download
            $('#download_error_report').on('click', function() {
                if (errorToken) {
                    window.location.href = "{{ url('ai-cataloguing-error-report') }}/" + errorToken;
                }
            });

            // Submit claim
            $('#btn_submit').on('click', function() {
                let formData = $('#claimForm').serialize();

                $.ajax({
                    url: "{{ route('ai-cataloguing.submit') }}",
                    type: "POST",
                    data: formData,
                    beforeSend: function() {
                        $('#global-loader').show();
                    },
                    success: function(res) {
                        $('#global-loader').hide();
                        if (res.status) {
                            toastr.success(res.message || 'Claim submitted successfully!');
                            setTimeout(function() {
                                window.location.href =
                                    "{{ route('ai-cataloguing.index') }}";
                            }, 1500);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(xhr) {
                        $('#global-loader').hide();
                        let msg = 'Failed to submit claim.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        toastr.error(msg);
                    }
                });
            });

            // Run check on page load in case values are pre-filled
            checkClaimFields();
        });
    </script>
@endsection
