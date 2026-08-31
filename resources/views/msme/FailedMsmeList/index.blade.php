@extends('components.admin.content-layout')

@section('card-content')

    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off" class="w-100">
            <div class="filter-bar d-flex flex-wrap px-3 py-2 gap-3 align-items-center">
                <div class="select-box" style="min-width: 160px;">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control filter_btn" name="from_date" id="from_date"
                        placeholder="From Date">
                </div>
                <div class="select-box" style="min-width: 160px;">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control filter_btn" name="to_date " id="to_date"
                        placeholder="To Date">
                </div>

                <div class="select-box" style="min-width: 180px;">
                    <label class="form-label">SNP</label>
                    <select name="snp_id" id="snp_id" class="form-select select2 filter_btn">
                        <option value="">Select</option>
                        @foreach ($snpList as $snp)
                            <option value="{{ $snp['id'] }}">{{ $snp['text'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="select-box" style="min-width: 180px;">
                    <label class="form-label">IA</label>
                    <select name="ia_id" id="ia_id" class="form-select select2 filter_btn">
                        <option value="">Select</option>
                        @foreach ($iaList as $ia)
                            <option value="{{ $ia['id'] }}">{{ $ia['text'] }}</option>
                        @endforeach
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
                        <th>{{ __('message.sn') }}</th>
                        <th>Udyam Number</th>
                        <th>Mobile</th>
                        <th>Product Category</th>
                        <th>Current State Business</th>
                        <th>Ondc Transaction</th>
                        <th>Status</th>
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
         * historical dates unselectable. This list is historical by nature, so the
         * limit is cleared here rather than in the shared layout (which every other
         * list depends on).
         */
        $("#to_date").datepicker("option", "minDate", null);

        $('#snp_id, #ia_id').select2({
            placeholder: "Select",
            allowClear: true
        });

        // Bound before dataTableInit() so the initial list request shows the loader.
        bindDataTableLoader("#dataTable");

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 7,
                direction: "desc"
            },
            url: "{{ url('msme/failed-msme/datalist') }}",
            columns: [{
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
                        if (row.status === 'Failed') {
                            return '<span class="badge bg-danger">Failed</span>';
                        }
                        return '<span class="badge bg-secondary">' + (row.status || '-') + '</span>';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                }
            ],
            filters: ["from_date", "to_date", "snp_id", "ia_id"]
        });
    </script>
@endsection
@endsection
