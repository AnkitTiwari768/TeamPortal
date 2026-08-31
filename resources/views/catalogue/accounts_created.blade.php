@extends('components.admin.content-layout')

@section('card-content')

    <div class="align-items-end border-bottom card-body d-flex justify-content-between catalogue-filter-row">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control filter_btn" name="from_date" id="from_date"
                        placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control filter_btn" name="to_date " id="to_date"
                        placeholder="To Date">
                </div>
                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>

        @if (hasRole('snp'))
            <div class="card-header d-flex">
                <div class="action-header ms-auto">
                    <button id="downloadSelected" class="btn btn-primary">
                        <i class="fa fa-download"></i> Download Predefined File
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#myCSVModal">
                            <img src="{{ asset('assets/img-new/add.svg') }}">
                            Add Bulk Claim
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>{{ __('message.sn') }}</th>
                        <th>TEAMID</th>
                        <th>Udyam</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>Name of enterprise</th>
                        <th>Enterprise Type </th>
                        <th>Date of Registration</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <!-- Modal CSV -->
    <div class="modal fade" id="myCSVModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Bulk Claim</h5>
                    </div>
                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="bulk-upload">
                    @csrf
                    <div class="modal-body">
                        <p>
                            <select class="form-select" name="claim_type_id" id="claim_type_id">
                                <option value="">Select Claim Type</option>
                                @foreach ($claim_types ?? [] as $claim_type_id => $claim_type_name)
                                    <option value="{{ $claim_type_id }}" @if (($claimTypeIdValue ?? null) == $claim_type_id) selected @endif>
                                        {{ $claim_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </p>

                        <!-- GST Type Dropdown commented out and hidden -->
                        <!--
                                <p>
                                    <select class="form-select" name="gst_type" id="gst_type">
                                        <option value="">Select GST Type</option>
                                        <option value="1">GST</option>
                                        <option value="2">CGST + SGST</option>
                                    </select>
                                </p>

                                <div id="gst_percentage_wrapper" style="display: none;">
                                    <p>
                                        <input type="number" min="0" max="100" step="any" class="form-control"
                                            name="gst_percentage" id="gst_percentage" placeholder="Enter GST Percentage (%)"
                                            maxlength="3" oninput="validateThreeDigits(this)">
                                    </p>
                                </div>

                                <div id="cgst_sgst_wrapper" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p>
                                                <input type="number" name="cgst_percentage" id="cgst_percentage"
                                                    class="form-control" placeholder="Enter CGST Percentage" min="0"
                                                    max="100" step="any" value="" maxlength="3"
                                                    oninput="validateThreeDigits(this)">
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p>
                                                <input type="number" name="sgst_percentage" id="sgst_percentage"
                                                    class="form-control" placeholder="Enter SGST Percentage" min="0"
                                                    max="100" step="any" value="" maxlength="3"
                                                    oninput="validateThreeDigits(this)">
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                -->
                        <input type="hidden" name="gst_type" id="gst_type" value="1" />
                        <input type="hidden" name="gst_percentage" id="gst_percentage" value="0" />
                        <input type="hidden" name="cgst_percentage" id="cgst_percentage" value="0" />
                        <input type="hidden" name="sgst_percentage" id="sgst_percentage" value="0" />

                        <p>Select File : <input type="file" name="file" id="file" accept=".xlsx"></p>
                        <span class="text-primary">Please ensure the file is in the correct format by downloading the
                            predefined format.</span>

                        <div id="csv-errors" style="color: red; font-family: Arial; padding: 10px;"></div>

                        <div class="form-group col-md-12 form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="declaration" id="declaration"
                                value="1" required />
                            <strong>Declaration:</strong>
                            <label class="form-check-label" for="declaration"><em>
                                    We understand and acknowledge that incentives under this program are intended
                                    exclusively for onboarding and/or facilitating transactions of Micro and Small
                                    Enterprises (MSEs) that are not currently on the Open Network for Digital Commerce
                                    (ONDC) as sellers and have not previously been on ONDC as sellers.
                                    <br />
                                    We shall not claim dual or duplicate incentives for the same set of MSEs or transactions
                                    under multiple ONDC-related programs.
                                    <br />
                                    Specifically, if an MSE has already been onboarded or incentivized through any other
                                    ONDC-related program run by institutions such as SIDBI, SFAC, or any other entity, we
                                    shall not claim incentives under this program for the same MSE or related transactions.
                                    <br />
                                    Similarly, if incentives have been claimed by us or any other party for transactions
                                    involving a specific MSE under a different program, we shall not submit claims for those
                                    transactions under this program.
                                    <br />
                                    We confirm that due diligence has been conducted to ensure that all MSEs for whom we
                                    claim incentives under this program are eligible, and have not been previously onboarded
                                    or incentivized under any other ONDC-related scheme. 6. We understand and accept that
                                    the liability for any duplication or misrepresentation lies solely with us, the SNP. In
                                    the event of any violation, we shall be fully accountable for returning any undue
                                    incentives received and may be subject to penalties or disqualification from further
                                    participation in ONDC programs.</em>
                            </label>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Upload</button>
                        <button type="submit" id="final-submit" class="btn btn-primary" style="display: none;">Save
                            &amp; Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="claimResultModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Claim Submission Summary</h5>
                </div>
                <div class="modal-body">
                    <div id="claim-summary"></div>
                    <div id="claim-errors"></div>
                </div>
                <div class="modal-footer">
                    <button id="download-report" class="btn btn-danger">
                        Download Error PDF
                    </button>
                    <button id="go-to-claims" class="btn btn-success">
                        Go To Claims
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script>
        // GST Type toggle functionality - FIXED
        $(document).ready(function() {
            // Initial state - hide both
            $('#gst_percentage_wrapper').hide();
            $('#cgst_sgst_wrapper').hide();

            // On change event
            $(document).on('change', '#gst_type', function() {
                var gstType = $(this).val();

                // Hide both wrappers first
                $('#gst_percentage_wrapper').hide();
                $('#cgst_sgst_wrapper').hide();

                // Clear previous values
                $('#gst_percentage').val('');
                $('#cgst_percentage').val('');
                $('#sgst_percentage').val('');

                // Show based on selection
                if (gstType === '1') { // GST selected
                    $('#gst_percentage_wrapper').show();
                    $('#cgst_sgst_wrapper').hide();
                } else if (gstType === '2') { // CGST + SGST selected
                    $('#gst_percentage_wrapper').hide();
                    $('#cgst_sgst_wrapper').show();
                } else {
                    // Default - hide both
                    $('#gst_percentage_wrapper').hide();
                    $('#cgst_sgst_wrapper').hide();
                }
            });
        });

        // Validation function for max 3 digits
        function validateThreeDigits(input) {
            var value = input.value;
            if (value.length > 3) {
                input.value = value.slice(0, 3);
            }
            // Allow only numbers and decimal point
            if (!/^\d*\.?\d*$/.test(input.value)) {
                input.value = input.value.replace(/[^0-9.]/g, '');
            }
        }

        $('#myCSVModal').on('hidden.bs.modal', function() {
            $('#csv-errors').html('');
            $('#file').val('');
            $('#gst_percentage').val('');
            $('#cgst_percentage').val('');
            $('#sgst_percentage').val('');
            $('#gst_type').val('');
            $('#gst_percentage_wrapper').hide();
            $('#cgst_sgst_wrapper').hide();

            // Enable all disabled form controls inside the modal
            $('#myCSVModal').find(':input:disabled').prop('disabled', false);
            $("#final-submit").hide();
        });

        $(document).on('change', '#file', function(e) {
            let fileName = e.target.files[0]?.name;
            if (fileName) {
                let ext = fileName.split('.').pop().toLowerCase();
                if (ext !== 'xlsx') {
                    alert("Only .xlsx files are allowed!");
                    $('#file').val('');
                }
            }
        });

        $(document).on('submit', '#bulk-upload', function(e) {
            e.preventDefault();

            document.getElementById('csv-errors').innerHTML = '';
            var claimTypeId = document.getElementById('claim_type_id').value;
            var gstType = document.getElementById('gst_type').value;

            var form = document.getElementById('bulk-upload');
            var formData = new FormData(form);
            formData.append('claim_type_id', claimTypeId);

            if (claimTypeId == '') {
                toastr.error("Please select claim type.");
                return;
            }

            // if (gstType == '') {
            //     toastr.error("Please select GST Type.");
            //     return;
            // }

            // Validate GST fields based on selection
            if (gstType === '1') { // GST
                var gstPercentage = document.getElementById('gst_percentage').value;
                if (gstPercentage === '') {
                    toastr.error("Please enter GST percentage.");
                    return;
                }
                var gstNum = parseFloat(gstPercentage);
                if (isNaN(gstNum) || gstNum < 0 || gstNum > 100) {
                    toastr.error("GST percentage must be between 0 and 100.");
                    return;
                }
                formData.append('gst_percentage', gstPercentage);
            } else if (gstType === '2') { // CGST + SGST
                var cgstPercentage = document.getElementById('cgst_percentage').value;
                var sgstPercentage = document.getElementById('sgst_percentage').value;

                if (cgstPercentage === '') {
                    toastr.error("Please enter CGST percentage.");
                    return;
                }
                if (sgstPercentage === '') {
                    toastr.error("Please enter SGST percentage.");
                    return;
                }

                var cgstNum = parseFloat(cgstPercentage);
                var sgstNum = parseFloat(sgstPercentage);

                if (isNaN(cgstNum) || cgstNum < 0 || cgstNum > 100) {
                    toastr.error("CGST percentage must be between 0 and 100.");
                    return;
                }
                if (isNaN(sgstNum) || sgstNum < 0 || sgstNum > 100) {
                    toastr.error("SGST percentage must be between 0 and 100.");
                    return;
                }

                // Validate total CGST + SGST doesn't exceed 100%
                var totalGst = cgstNum + sgstNum;
                if (totalGst > 100) {
                    toastr.error("CGST + SGST total cannot exceed 100%.");
                    return;
                }

                formData.append('cgst_percentage', cgstPercentage);
                formData.append('sgst_percentage', sgstPercentage);
            }

            // Declaration validation
            if (!$('#declaration').is(':checked')) {
                toastr.error('Please accept the declaration to proceed.');
                return;
            }

            var fileInput = document.getElementById('file');
            if (!fileInput.files.length) {
                toastr.error("Please select a file to upload.");
                return;
            }

            $.ajax({
                url: "{{ url('claims/account-bulk-import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $("#ajax-loader").show();
                },
                success: function(res) {
                    if (res.summary.inserted >= 1) {
                        toastr.success(res.message);
                        $('#bulk-upload input, #bulk-upload select').prop('disabled', true);
                        $('#final-submit').show();
                    }

                    if (res.summary.failed > 0) {
                        toastr.error('Some rows failed validation. Please check errors below.');
                        displayCsvErrors(res);
                    }
                },
                error: function(err) {
                    console.log(err?.responseJSON?.message);
                    toastr.error(err?.responseJSON?.message ||
                        "Server error while processing the file.");
                },
                complete: function() {
                    $("#ajax-loader").hide();
                }
            });
        });

        $(document).on('click', '#final-submit', function(e) {
            e.preventDefault();

            if (!$('#declaration').is(':checked')) {
                toastr.error('Please accept the declaration to proceed.');
                return;
            }

            $.ajax({
                url: "{{ url('/save-account-uploaded-claims') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                beforeSend() {
                    $("#ajax-loader").show();
                },
                success(res) {
                    let summaryHtml = `
                        <p><strong>Total:</strong> ${res.summary.total_rows}</p>
                        <p class="text-success"><strong>Success:</strong> ${res.summary.inserted}</p>
                        <p class="text-danger"><strong>Failed:</strong> ${res.summary.failed}</p>
                    `;

                    let errorHtml = '';

                    if (res.failed_rows.length > 0) {
                        errorHtml += `<table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>MSME</th>
                                    <th>Team</th>
                                    <th>Udyam</th>
                                    <th>Message</th>
                                </tr>
                            </thead><tbody>`;

                        res.failed_rows.forEach(row => {
                            errorHtml += `
                                <tr>
                                    <td>${row.msme_name}</td>
                                    <td>${row.team_id}</td>
                                    <td>${row.udyam_number}</td>
                                    <td>${row.message}</td>
                                </tr>`;
                        });

                        errorHtml += `</tbody></table>`;
                    } else {
                        window.location.href = "{{ url('accounts-claims') }}";
                    }

                    $("#claim-summary").html(summaryHtml);
                    $("#claim-errors").html(errorHtml);
                    $("#download-report").data('token', res.error_token);
                    $("#claimResultModal").modal('show');
                },
                error(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to save uploaded claims.');
                },
                complete() {
                    $("#ajax-loader").hide();
                }
            });
        });

        $(document).on('click', '#download-report', function() {
            let token = $(this).data('token');
            window.open(`{{ url('/claim-error-report') }}/${token}`, '_blank');
        });

        $(document).on('click', '#go-to-claims', function() {
            window.location.href = "{{ url('accounts-claims') }}";
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

            if (response.error_token) {
                html += `
                    <a href="{{ url('/accounts/import-error-report') }}/${response.error_token}"
                       class="btn btn-danger btn-sm mb-3">
                       ⬇ Download Error Report
                    </a>
                `;
            }

            response.failed_rows.forEach(row => {
                const teamId = row.data.team_id ? row.data.team_id : 'TEAM ID not provided';
                html += `
                    <div class="card mb-2 border-danger">
                        <div class="card-header bg-danger text-white">
                            Row ${row.row_number} (${teamId})
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                `;

                Object.keys(row.errors).forEach(field => {
                    row.errors[field].forEach(message => {
                        html += `<li><strong>${field}</strong>: ${message}</li>`;
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

        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('accounts-created/datalist') }}",
            columns: [{
                    "orderable": false,
                    "className": "noExport",
                    "render": function(data, type, row) {
                        var checked = selectedRows[row.id] ? 'checked' : '';
                        return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' +
                            checked + '>';
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.team_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.udyam_no;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.mobile;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.email;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.state_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.enterprise_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.msme_classification;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                }
            ],
            filters: ["from_date", "to_date"]
        });

        $(document).on("change", ".row-checkbox", function() {
            var id = $(this).val();
            selectedRows[id] = $(this).prop("checked");

            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });

        $(document).on("change", "#selectAll", function() {
            var checked = $(this).prop("checked");
            $(".row-checkbox").each(function() {
                $(this).prop("checked", checked);
                selectedRows[$(this).val()] = checked;
            });
        });

        $('#dataTable').on('draw.dt', function() {
            $(".row-checkbox").each(function() {
                var id = $(this).val();
                $(this).prop("checked", selectedRows[id] ? true : false);
            });
            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });

        $('#downloadSelected').on('click', function() {
            const selectedIds = Object.keys(selectedRows)
                .filter(id => selectedRows[id]);

            if (selectedIds.length === 0) {
                alert('Please select at least one row');
                return;
            }

            $.ajax({
                url: '{{ url('export-selected-excel-accounts') }}',
                method: 'POST',
                data: {
                    _token: csrfToken,
                    ids: selectedIds
                },
                xhrFields: {
                    responseType: 'blob'
                },
                beforeSend: function() {
                    $("#ajax-loader").show();
                },
                success: function(blob, status, xhr) {
                    let filename = 'selected_rows.xlsx';
                    const disposition = xhr.getResponseHeader('Content-Disposition');

                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        filename = disposition
                            .split('filename=')[1]
                            .replace(/"/g, '');
                    }

                    const link = document.createElement('a');
                    const url = window.URL.createObjectURL(blob);
                    link.href = url;
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                },
                error: function() {
                    alert('Download failed');
                },
                complete: function() {
                    $("#ajax-loader").hide();
                }
            });
        });
    </script>
@endsection
@endsection
