@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('workshop.executed_workshop_list') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">{{ __('workshop.dashboard') }}</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ __('workshop.executed_workshop_list') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                    @if (acl('executed-workshop-create'))
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ url('create-executed-workshop') }}">
                                    <button type="button" class="btn btn-primary">
                                        <img src="{{ asset('assets/img-new/add.svg') }}">
                                        {{ __('workshop.add_executed_workshop') }}
                                    </button>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Filter Bar -->
                <div class="card-body d-flex justify-content-between">
                    <form id="search_form" autocomplete="off">
                        <div class="filter-bar d-flex py-2 gap-3 align-items-center">
                            <div class="row">
                                <div class="col-lg-3 col-2 mb-3">
                                    <div class="select-box">
                                        <label class="form-label">{{ __('workshop.from_date') }}</label>
                                        <input type="text" class="form-control" name="from_date" id="from_dates"
                                            placeholder="From Date">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-2 mb-3">
                                    <div class="select-box">
                                        <label class="form-label">{{ __('workshop.to_date') }}</label>
                                        <input type="text" class="form-control" name="to_date" id="to_dates"
                                            placeholder="To Date">
                                    </div>
                                </div>
                                <div class="col-lg-3 col-2 mb-3">
                                    <div class="select-box">
                                        <label class="form-label">{{ __('workshop.status') }}</label>
                                        <select id="status" name="status" class="form-select form-select-sm" style="width: 150px; height: 38px;">
                                            <option value="">{{ __('workshop.all') }}</option>
                                            <option value="Completed">{{ __('workshop.completed') }}</option>
                                            <option value="Cancelled">{{ __('workshop.cancelled') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-3">
                                    <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
                                        <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body pt-1">
                    <div class="tab-content view-application-tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="national-p" role="tabpanel">
                            <table id="dataTable" class="table datatable table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('workshop.s_no') }}</th>
                                        <th>{{ __('workshop.event_title') }}</th>
                                        <th>{{ __('workshop.organiser') }}</th>
                                        <th>{{ __('workshop.event_for') }}</th>
                                        <th>{{ __('workshop.venue_address') }}</th>
                                        <th>{{ __('workshop.from_date') }}</th>
                                        <th>{{ __('workshop.to_date') }}</th>
                                        <th>{{ __('message.status') }}</th>
                                        <th class="actions">{{ __('workshop.action') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- STATUS CHANGE MODAL WITH CONDITIONAL FIELDS -->
<!-- ========================================================= -->
<div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">Change Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changeStatusForm" novalidate>
                @csrf
                <input type="hidden" id="modal-workshop-id" name="workshop_id" />
                <div class="modal-body">
                    <!-- Status Dropdown -->
                    <div class="mb-3">
                        <label for="modal-status" class="form-label required">Status</label>
                        <select class="form-select" id="modal-status" name="status" required>
                            <option value="">Select Status</option>
                             <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                        <span class="text-danger" id="modal-status-error"></span>
                    </div>

                    <!-- ===== CONDITIONAL FIELDS (shown only when status = "Completed") ===== -->
                    <div id="completed-fields-wrapper" style="display: none;">



                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">Number of Participants</label>
                                    <input type="number" class="form-control" name="no_of_participants"
                                        id="modal-no-of-participants" placeholder="Number of Participants">
                                    <span class="text-danger" id="modal-no_of_participants-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">Expense Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="expense_amount"
                                        id="modal-expense-amount" placeholder="Expense Amount">
                                    <span class="text-danger" id="modal-expense_amount-error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">5% NSIC Fee</label>
                                    <input type="number" step="0.01" class="form-control" name="nsic_fee"
                                        id="modal-nsic-fee" placeholder="5% NSIC Fee" readonly>
                                    <span class="text-danger" id="modal-nsic_fee-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">TDS Applicable</label>
                                    <select name="tds_applicable" id="modal-tds-applicable" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                    <span class="text-danger" id="modal-tds_applicable-error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row" id="modal-tds-percentage-wrapper" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">TDS %</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control"
                                        name="tds_percentage" id="modal-tds-percentage"
                                        placeholder="TDS %">
                                    <span class="text-danger" id="modal-tds_percentage-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Net Amount (Auto Calculated)</label>
                                    <input type="number" step="0.01" class="form-control" name="net_amount"
                                        id="modal-net-amount" placeholder="Net Amount" readonly>
                                    <span class="text-danger" id="modal-net_amount-error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">Sanction Order Number</label>
                                    <input type="text" class="form-control" name="sanction_order_number"
                                        id="modal-sanction-order-number"
                                        placeholder="Sanction Order Number">
                                    <span class="text-danger" id="modal-sanction_order_number-error"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="required form-label">Sanction Order Date</label>
                                    <input type="text" class="form-control to_date" name="sanction_order_date"
                                        id="modal-sanction-order-date" placeholder="DD-MM-YYYY">
                                    <span class="text-danger" id="modal-sanction_order_date-error"></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Supporting Document</label>
                                    <input type="hidden" name="supporting_document" id="modal-supporting-document-hidden">
                                    <input type="file" class="form-control" id="modal-supporting-document-file"
                                        accept=".pdf,.xls,.xlsx,.csv">
                                    <span class="text-danger" id="modal-supporting_document-error"></span>
                                    <small class="form-text text-muted">Note: Accept only pdf or excel files</small>
                                    <div id="modal-supporting-document-loader" class="d-none mt-1">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Uploading...</span>
                                        </div>
                                        <span class="ms-1">Uploading...</span>
                                    </div>
                                    <div id="modal-supporting-document-preview" class="mt-2" style="display: none;">
                                        <a href="#" id="modal-supporting-document-link" target="_blank">
                                            View Current Document
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" name="remarks" id="modal-remarks" rows="3"
                                        placeholder="Enter remarks"></textarea>
                                    <span class="text-danger" id="modal-remarks-error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ===== END CONDITIONAL FIELDS ===== -->

                    <!-- Simple Remark field (visible when status is not "Completed") -->
                    <div class="mb-3" id="simple-remark-wrapper">
                        <label for="modal-remark" class="form-label">Remark</label>
                        <textarea class="form-control" id="modal-remark" name="remark" rows="2"
                            placeholder="Enter remarks (if any)"></textarea>
                        <span class="text-danger" id="modal-remark-error"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-status">
                        Change Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script>
    var selectedRows = {};
    var tableId = "#dataTable";

    $('#ajax-loader').show();
    $(tableId).on('preXhr.dt', function() { $('#ajax-loader').show(); });
    $(tableId).on('xhr.dt draw.dt', function() { $('#ajax-loader').hide(); });

    dataTableInit({
        id: "#dataTable",
        showExcelExport: true,
        order: { column: 1, direction: "desc" },
        url: "{{ url('executed-workshop-list') }}",
        columns: [
            { "orderable": false, "render": function(data, type, full, meta) {
                return serialNumber("#dataTable", meta.row);
            }},
            { "orderable": true, "render": function(data, type, row) { return row.event_title; }},
            { "orderable": true, "render": function(data, type, row) { return row.organizer_name ?? '-'; }},
            { "orderable": true, "render": function(data, type, row) {
                if (!row.event_for) return '-';
                var eventForText = row.event_for.charAt(0).toUpperCase() + row.event_for.slice(1);
                return '<div style="white-space: normal; word-break: break-word; min-width: 150px;">' + eventForText + '</div>';
            }},
            { "orderable": true, "render": function(data, type, row) { return row.venue_address; }},
            { "orderable": true, "render": function(data, type, row) { return row.start_date; }},
            { "orderable": true, "render": function(data, type, row) { return row.end_date; }},
            { "orderable": true, "render": function(data, type, row) { return row.status ? statusBedge(row.status) : '-'; }},
            { "orderable": false, "render": function(data, type, row) {
                var viewBtn = '';
                @if (acl('executed-workshop-view'))
                    viewBtn = buttonView("{{ url('view-executed-workshop') }}", row.id);
                @endif

                var pedit = '';
                @if (acl('executed-workshop-edit'))
                    if (row.status !== 'Completed') {
                        pedit = buttonEdit("{{ url('edit-executed-workshop') }}", row.id);
                    }
                @endif

                var statusBtn = '';
                @if (acl('executed-workshop-status'))
                    if (row.status !== 'Completed') {
                        statusBtn = `<a href="javascript:void(0)" class="btn btn-sm btn-info btn-circle m-1 btn-change-status" data-id="${row.id}" data-status="${row.status ?? ''}" data-remark="${row.remark ?? ''}" title="Change Status"><i class="fa fa-refresh" style="color:white;"></i></a>`;
                    }
                @endif

                return createActionButtons([pedit, viewBtn, statusBtn]);
            }}
        ],
        filters: ["from_dates", "to_dates", "status"]
    });

    function getTable() { return $(tableId).DataTable(); }
    function reloadTable() { getTable().ajax.reload(); }

    $(function() {
        $("#from_dates, #to_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            onSelect: function(selected) {
                var target = this.id === "from_dates" ? "#to_dates" : "#from_dates";
                $(target).datepicker("option", this.id === "from_dates" ? "minDate" : "maxDate", selected);
                reloadTable();
                showClearButton();
            }
        });

        $('#status').on('change', function() { reloadTable(); showClearButton(); });

        $('#reset_btn').on('click', function() {
            $('#from_dates, #to_dates, #status').val('');
            $("#from_dates, #to_dates").datepicker("option", { minDate: null, maxDate: null });
            $(this).hide();
            reloadTable();
        });

        function showClearButton() {
            var hasFilter = false;
            $('#from_dates, #to_dates, #status').each(function() {
                if ($(this).val() !== '') { hasFilter = true; return false; }
            });
            hasFilter ? $('#reset_btn').show() : $('#reset_btn').hide();
        }
        showClearButton();
    });

    $(tableId).on('init.dt', function() {
        var oTable = getTable();
        $('.dataTables_filter input').off().on('keyup input', debounce(function() {
            oTable.search(this.value).draw();
        }, 500));
    });

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() { func.apply(context, args); }, wait);
        };
    }

    // ===== MODAL LOGIC =====
    var modalWorkshopId = null, modalOriginalStatus = null, modalCurrentStatus = null;

    // Helper: Manage required attribute for tds_percentage
    function manageTdsRequired(show) {
        var $tdsInput = $('#modal-tds-percentage');
        if (show) {
            $tdsInput.prop('required', true);
        } else {
            $tdsInput.prop('required', false);
            $tdsInput.val('');
        }
        // Also manage net_amount (optional)
        var $netInput = $('#modal-net-amount');
        if (!show) {
            $netInput.val('');
        }
    }

    $(document).on('click', '.btn-change-status', function() {
        var id = $(this).data('id'), status = $(this).data('status') || '', remark = $(this).data('remark') || '';
        modalWorkshopId = id;
        modalOriginalStatus = status;
        modalCurrentStatus = status;

        $('#changeStatusForm')[0].reset();
        // Clear all error messages
        $('#modal-status-error, #modal-remark-error, .text-danger').text('');
        $('#modal-workshop-id').val(id);
        $('#modal-status').val(status);
        $('#modal-remark').val(remark);

        // A Completed workshop can no longer be moved to Cancelled.
        $('#modal-status option[value="Cancelled"]').prop('disabled', status === 'Completed');

        $('#completed-fields-wrapper').hide();
        $('#simple-remark-wrapper').show();

        if (status === 'Completed') {
            fetchWorkshopData(id);
        } else {
            resetConditionalFields();
            $('#modal-supporting-document-preview').hide();
            manageTdsRequired(false);
        }

        $('#statusModal').modal('show');
    });

    function fetchWorkshopData(id) {
        $.ajax({
            url: BASE_URL + '/get-executed-workshop-data/' + id,
            type: 'GET',
            beforeSend: function() { $('#ajax-loader').show(); },
            success: function(response) {
                $('#ajax-loader').hide();
                if (response.status && response.data) {
                    populateCompletedFields(response.data);
                    $('#completed-fields-wrapper').show();
                    $('#simple-remark-wrapper').hide();
                    $('#modal-status').val('Completed');
                    modalCurrentStatus = 'Completed';
                    // Manage TDS required state
                    toggleModalTdsFields();
                } else {
                    toastr.warning('No completion data found.');
                }
            },
            error: function() {
                $('#ajax-loader').hide();
                toastr.error('Failed to fetch workshop data.');
            }
        });
    }

    function populateCompletedFields(data) {
        $('#modal-no-of-participants').val(data.no_of_participants || '');
        $('#modal-expense-amount').val(data.expense_amount || '');
        $('#modal-nsic-fee').val(data.nsic_fee || '0.00');
        $('#modal-tds-applicable').val(data.tds_applicable || '');
        $('#modal-tds-percentage').val(data.tds_percentage || '');
        $('#modal-net-amount').val(data.net_amount || '');
        $('#modal-sanction-order-number').val(data.sanction_order_number || '');
        $('#modal-sanction-order-date').val(data.sanction_order_date || '');
        $('#modal-remarks').val(data.remarks || '');

        if (data.supporting_document) {
            $('#modal-supporting-document-hidden').val(data.supporting_document);
            var filePath = data.supporting_document_url || '';
            if (filePath) {
                $('#modal-supporting-document-link').attr('href', filePath);
                $('#modal-supporting-document-preview').show();
            } else {
                $('#modal-supporting-document-preview').hide();
            }
        } else {
            $('#modal-supporting-document-hidden').val('');
            $('#modal-supporting-document-preview').hide();
        }
        toggleModalTdsFields();
        calculateModalNetAmount();
    }

    function resetConditionalFields() {
        $('#modal-no-of-participants, #modal-expense-amount, #modal-tds-percentage, #modal-net-amount, #modal-sanction-order-number, #modal-sanction-order-date, #modal-remarks').val('');
        $('#modal-nsic-fee').val('0.00');
        $('#modal-tds-applicable').val('');
        $('#modal-supporting-document-hidden').val('');
        $('#modal-supporting-document-file').val('');
        $('#modal-supporting-document-preview').hide();
        $('#modal-tds-percentage-wrapper').hide();
        $('#modal-net-amount').val('');
        manageTdsRequired(false);
    }

    $('#modal-status').on('change', function() {
        var selectedStatus = $(this).val();
        modalCurrentStatus = selectedStatus;
        if (selectedStatus === 'Completed') {
            $('#completed-fields-wrapper').slideDown(300);
            $('#simple-remark-wrapper').hide();
            if (modalOriginalStatus !== 'Completed') {
                resetConditionalFields();
                fetchWorkshopData(modalWorkshopId);
            } else {
                fetchWorkshopData(modalWorkshopId);
            }
        } else {
            $('#completed-fields-wrapper').slideUp(300);
            $('#simple-remark-wrapper').show();
            resetConditionalFields();
            $('#modal-supporting-document-preview').hide();
            manageTdsRequired(false);
        }
    });

    $('#modal-tds-applicable').on('change', function() {
        toggleModalTdsFields();
        calculateModalNetAmount();
    });

    function toggleModalTdsFields() {
        var tdsVal = $('#modal-tds-applicable').val();
        if (tdsVal === 'Yes') {
            $('#modal-tds-percentage-wrapper').slideDown(300);
            manageTdsRequired(true);
        } else {
            $('#modal-tds-percentage-wrapper').slideUp(300);
            manageTdsRequired(false);
        }
        calculateModalNetAmount();
    }

    function calculateModalNsicFee() {
        var expense = parseFloat($('#modal-expense-amount').val()) || 0;
        $('#modal-nsic-fee').val((expense * 0.05).toFixed(2));
        calculateModalNetAmount();
    }
    $('#modal-expense-amount').on('input change', calculateModalNsicFee);

    function calculateModalNetAmount() {
        var expense = parseFloat($('#modal-expense-amount').val()) || 0;
        var tdsVal = $('#modal-tds-applicable').val();
        var netAmount = expense;
        if (tdsVal === 'Yes') {
            var tdsPercent = parseFloat($('#modal-tds-percentage').val()) || 0;
            netAmount -= (expense * tdsPercent / 100);
        }
        $('#modal-net-amount').val(tdsVal !== '' ? netAmount.toFixed(2) : '');
    }
    $('#modal-tds-percentage').on('input', calculateModalNetAmount);

    // Supporting document upload
    $('#modal-supporting-document-file').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        var formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');

        $('#modal-supporting-document-loader').removeClass('d-none');
        $.ajax({
            url: "{{ url('upload-executed-workshop-document') }}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#modal-supporting-document-loader').addClass('d-none');
                if (response.status && response.data) {
                    $('#modal-supporting-document-hidden').val(response.data.id);
                    var filePath = response.data.preview_url || '';
                    if (filePath) {
                        $('#modal-supporting-document-link').attr('href', filePath);
                        $('#modal-supporting-document-preview').show();
                    }
                    toastr.success('Document uploaded successfully.');
                } else {
                    toastr.error('Upload failed.');
                }
            },
            error: function(xhr) {
                $('#modal-supporting-document-loader').addClass('d-none');
                toastr.error(xhr.responseJSON?.message || 'Upload failed.');
            }
        });
    });

    // Submit status change – using POST
    $('#changeStatusForm').on('submit', function(e) {
        e.preventDefault();
        // Clear previous errors
        $('.text-danger').text('');

        var status = $('#modal-status').val();
        if (!status) {
            $('#modal-status-error').text('Please select a status.');
            return;
        }
        $('#modal-status-error').text('');

        var id = $('#modal-workshop-id').val();
        var formData = new FormData(this);

        // If status is "Completed", ensure required fields are filled (client-side)
        if (status === 'Completed') {
            var participants = $('#modal-no-of-participants').val();
            var expense = $('#modal-expense-amount').val();
            var sanctionNo = $('#modal-sanction-order-number').val();
            var sanctionDate = $('#modal-sanction-order-date').val();

            if (!participants) {
                $('#modal-no_of_participants-error').text('Number of participants is required.');
                return;
            } else {
                $('#modal-no_of_participants-error').text('');
            }
            if (!expense || parseFloat(expense) <= 0) {
                $('#modal-expense_amount-error').text('Expense amount is required and must be greater than 0.');
                return;
            } else {
                $('#modal-expense_amount-error').text('');
            }
            if (!sanctionNo) {
                $('#modal-sanction_order_number-error').text('Sanction order number is required.');
                return;
            } else {
                $('#modal-sanction_order_number-error').text('');
            }
            if (!sanctionDate) {
                $('#modal-sanction_order_date-error').text('Sanction order date is required.');
                return;
            } else {
                $('#modal-sanction_order_date-error').text('');
            }

            var tdsApplicable = $('#modal-tds-applicable').val();
            if (tdsApplicable === 'Yes') {
                var tdsPercent = $('#modal-tds-percentage').val();
                if (!tdsPercent || parseFloat(tdsPercent) <= 0) {
                    $('#modal-tds_percentage-error').text('TDS percentage is required when TDS is applicable.');
                    return;
                } else {
                    $('#modal-tds_percentage-error').text('');
                }
            }
        }

        var url = BASE_URL + '/update-executed-workshop/' + id;

        $('#btn-submit-status').prop('disabled', true);
        $('#ajax-loader').show();

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#ajax-loader').hide();
                $('#btn-submit-status').prop('disabled', false);
                if (response.status) {
                    toastr.success(response.message || 'Status updated successfully.');
                    $('#statusModal').modal('hide');
                    reloadTable();
                } else {
                    toastr.error(response.message || 'Something went wrong.');
                }
            },
            error: function(xhr) {
                $('#ajax-loader').hide();
                $('#btn-submit-status').prop('disabled', false);
                // Clear previous errors
                $('.text-danger').text('');

                if (xhr.status === 422) {
                    var errors = xhr.responseJSON?.errors || {};
                    $.each(errors, function(key, messages) {
                        // Map field names to modal error IDs
                        var modalKey = key.replace(/_/g, '-'); // e.g., no_of_participants -> no-of-participants
                        var errorField = $('#modal-' + modalKey + '-error');
                        if (errorField.length) {
                            errorField.text(messages[0]);
                        } else {
                            // Fallback: show as toast
                            toastr.error(messages[0]);
                        }
                    });
                } else {
                    toastr.error(xhr.responseJSON?.message || 'Failed to update status.');
                }
            }
        });
    });

    // Datepicker for modal sanction date
    $(document).on('focus', '#modal-sanction-order-date', function() {
        $(this).datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0
        });
    });

    $('#statusModal').on('shown.bs.modal', function() {
        $('#modal-sanction-order-date').datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0
        });
    });

    $('#statusModal').on('hidden.bs.modal', function() {
        modalWorkshopId = null;
        modalOriginalStatus = null;
        modalCurrentStatus = null;
        $('#completed-fields-wrapper').hide();
        $('#simple-remark-wrapper').show();
        resetConditionalFields();
        $('.text-danger').text('');
        $('#modal-supporting-document-preview').hide();
        $('#modal-supporting-document-file').val('');
        $('#btn-submit-status').prop('disabled', false);
        manageTdsRequired(false);
    });
</script>
@endsection

@endsection
