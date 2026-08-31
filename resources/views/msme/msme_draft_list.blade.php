@extends('components.admin.content-layout')

@section('action-header')
    @if (acl('bulk-create'))
        <a href="{{ route('msme-bulk-draft-registration-udyam') }}" class="btn btn-primary me-2">
            <i class="fa fa-upload me-1"></i> MSME Bulk Upload
        </a>
    @endif
    <!-- <a href="{{ route('failed-msme-list') }}" class="btn btn-secondary me-2">
        <i class="fa fa-list me-1"></i> Failed MSME List
    </a> -->
    <button type="button" id="downloadErrorBtn" class="btn btn-danger">
        <i class="fa fa-download me-1"></i> Download Error
    </button>
@endsection

@section('card-content')

    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
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

                <div class="select-box">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="form-select filter_btn">
                        <option value="">Select</option>
                        <option value="Pending">Pending</option>
                        <option value="Migrated">Migrated</option>
                        <option value="Failed">Failed</option>
                    </select>
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>{{ __('message.sn') }}</th>
                        <th>Udyam Number</th>
                        <th>Mobile</th>
                        <th>Product Category</th>
                        <th>Current State Business</th>
                        <th>Ondc Transaction</th>
                        <th>Status</th>
                        <th>Uploaded By</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@section('js');
    @include('scripts/read_more_toggle')
    @include('scripts/datatable_ajax_loader')

    <script>
        /*
         * The shared layout initialises #to_date with minDate = today, which makes
         * historical dates unselectable. This list is historical, so the limit is
         * cleared here rather than in the shared layout that every other list uses.
         */
        $("#to_date").datepicker("option", "minDate", null);

        var selectedRows = {};

        // Bound before dataTableInit() so the initial list request shows the loader.
        bindDataTableLoader("#dataTable");

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 9,
                direction: "desc"
            },
            url: "{{ url('msme/draftDataList') }}",
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
                        // Sorting and Excel export get the raw value; only the
                        // on-screen cell gets the Read More markup.
                        if (type !== 'display') {
                            return row.product_category_id || '';
                        }
                        return readMoreCell(row.product_category_id);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.current_state_business_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.ondc_transaction_type_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        if (row.status === 'Pending') {
                            return '<span class="badge bg-warning">Pending</span>';
                        } else if (row.status === 'Migrated') {
                            return '<span class="badge bg-success">Migrated</span>';
                        } else if (row.status === 'Failed') {
                            return '<span class="badge bg-danger">Failed</span>';
                        }
                        return '<span class="badge bg-secondary">-</span>';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        if (row.uploaded_by === 'SNP') {
                            return '<span class="badge bg-info">SNP</span>';
                        } else if (row.uploaded_by === 'IA') {
                            return '<span class="badge bg-primary">IA</span>';
                        }
                        return '<span class="badge bg-secondary">' + (row.uploaded_by || '-') + '</span>';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                }
            ],
            filters: ["from_date", "to_date", "status"]
        });

        /* ---------------- row selection (same pattern as ia/registered-msme) ---------------- */
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

        /* ---------------- download error excel ---------------- */
        $('#downloadErrorBtn').on('click', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const originalText = $btn.html();

            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Downloading...');
            $btn.prop('disabled', true);

            $('#ajax-loader').show();

            $.ajax({
                url: "{{ route('msme.download.error') }}",
                type: "GET",
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response, status, xhr) {
                    $('#ajax-loader').hide();
                    const contentType = xhr.getResponseHeader('Content-Type');

                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        const reader = new FileReader();
                        reader.onload = function() {
                            var message = 'No failed records found.';
                            try {
                                const json = JSON.parse(reader.result);
                                message = json.message || message;
                            } catch (e) {
                                // keep default message
                            }
                            toastr.error(message);
                            $btn.html(originalText);
                            $btn.prop('disabled', false);
                        };
                        reader.readAsText(response);
                        return;
                    }

                    let filename = 'msme_failed_records.xlsx';
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition) {
                        const matches = /filename="?([^"]+)"?/i.exec(disposition);
                        if (matches && matches[1]) {
                            filename = matches[1];
                        }
                    }

                    const blob = new Blob([response], {
                        type: contentType || 'application/octet-stream'
                    });

                    const downloadUrl = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = downloadUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(downloadUrl);

                    toastr.success('File downloaded successfully!');

                    $btn.html(originalText);
                    $btn.prop('disabled', false);
                },
                error: function(xhr) {
                    $('#ajax-loader').hide();
                    $btn.html(originalText);
                    $btn.prop('disabled', false);

                    var message = 'No failed records found.';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        message = response.message || message;
                    } catch (e) {
                        // keep default message
                    }
                    toastr.error(message);
                }
            });
        });
    </script>
@endsection
@endsection
