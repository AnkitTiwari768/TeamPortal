@extends('components.admin.content-layout')

@section('action-header')
    <button type="button" class="btn btn-outline-success me-2" id="batch_timeline_history_export_excel">
        <i class="fa fa-file-excel-o me-1"></i> Download Excel
    </button>
    <button type="button" class="btn btn-outline-danger" id="batch_timeline_history_export_pdf">
        <i class="fa fa-file-pdf-o me-1"></i> Download PDF
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
                    <input type="text" class="form-control filter_btn" name="to_date" id="to_date"
                        placeholder="To Date">
                </div>
                <div class="select-box">
                    <label class="form-label">Status</label>
                    {!! Form::select('status', array('' => 'All') + $statusOptions, '', ['class' => 'form-select filter_btn', 'id' => 'status']) !!}
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
                    <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}" alt="clear">
                    Clear all
                </a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>Organization Name</th>
                        <th>Batch Number</th>
                        <th>Subject</th>
                        <th>Action</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Created By Role</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@section('js')
    @include('scripts/datatable_ajax_loader')

    <script>
        /*
         * The shared layout initialises #to_date with minDate = today, which makes
         * historical dates unselectable. This list is historical, so the limit is
         * cleared here rather than in the shared layout that every other list uses.
         */
        $("#to_date").datepicker("option", "minDate", null);

        // Bound before dataTableInit() so the initial list request shows the loader.
        bindDataTableLoader("#dataTable");

        dataTableInit({
            id: "#dataTable",
            order: {
                column: 6,
                direction: "desc"
            },
            url: "{{ url('batch-timeline-history/datalist') }}",
            columns: [
                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.organization_name || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.batch_number || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.subject || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.action || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.status || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at || "N/A";
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_by_role || "N/A";
                    }
                }
            ],
            filters: ["from_date", "to_date", "status"]
        });

        // Export (Excel / PDF) - reuses the same search & filters as the on-screen list.
        function batchTimelineHistoryBuildExportUrl(baseUrl) {
            var params = [];
            var filters = {
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val(),
                status: $('#status').val()
            };

            $.each(filters, function(key, val) {
                if (val === null || val === undefined || val === '') {
                    return;
                }
                params.push('filters[' + key + ']=' + encodeURIComponent(val));
            });

            var search = $('#dataTable_filter input').val();
            if (search) {
                params.push('search[value]=' + encodeURIComponent(search));
            }

            return baseUrl + (params.length ? ('?' + params.join('&')) : '');
        }

        $('#batch_timeline_history_export_excel').on('click', function() {
            window.location.href = batchTimelineHistoryBuildExportUrl("{{ url('batch-timeline-history/export-excel') }}");
        });

        $('#batch_timeline_history_export_pdf').on('click', function() {
            window.location.href = batchTimelineHistoryBuildExportUrl("{{ url('batch-timeline-history/export-pdf') }}");
        });
    </script>
@endsection
@endsection
