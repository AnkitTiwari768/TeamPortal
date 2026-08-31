@extends('components.admin.content-layout')

@section('action-header')
    <!-- @if (acl('fund-distribution-create'))
            <div class="btn-group drop-btn">
                <a href="{{ route('dist.create') }}" class="btn btn-danger">
                    <img src="{{ asset('assets/ffo-admin/img/add.svg') }}" alt="+" />
                    {{ __('Create New Distribution') }}
                </a>
            </div>
        @endif -->
@endsection

@section('card-content')
    <!-- Advanced Master Filter Panel -->
    <div class="border-bottom card-body d-flex justify-content-between bg-light">
        <form id="search_form" autocomplete="off" class="w-100">
            <div class="row g-3 py-2 px-3 align-items-end filter-bar">
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Financial Year') }}</label>
                    <select name="financial_year" id="financial_year" class="form-select filter_btn">

                        @foreach(financial_year() as $key => $val)
                            <option value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Major Component') }}</label>
                    <select name="major_component_id" id="major_component_id" class="form-select filter_btn">
                        <option value="">Select Major Component</option>
                        @foreach($majorComponents as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">{{ __('Sub Component') }}</label>
                    <select name="sub_component_id" id="sub_component_id" class="form-select filter_btn">
                        <option value="">Select Sub Component</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-center">
                    <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display:none;">
                        <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">{{ __('Reset Filters') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-striped border align-middle" id="dataTable" width="100%">
                <thead class="table-light text-uppercase text-secondary" style="font-size: 0.85rem;">
                    <tr>
                        <th>S.NO.</th>
                        <th>{{ __('Financial Year') }}</th>
                        <th>{{ __('Major Component') }}</th>
                        <th>{{ __('Sub Component') }}</th>
                        <th class="text-end">{{ __('Total Allocated (₹)') }}</th>
                        <th class="text-end">{{ __('Total Distributed (₹)') }}</th>
                        <th class="text-end">{{ __('Remaining (₹)') }}</th>
                        <th class="text-center actions" style="width: 100px;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-dark"></tbody>
            </table>
        </div>
    </div>
@endsection



@section('js')
    ;
    <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
                <script src="{{ asset('assets/js/allocation/distribution.js') }}"></script>
                 <script>
        // Pass backend-aware paths to script initialization engine
        initDistributionListing({
            subComponentUrl: "{{ url('web/api/allocation/sub-components') }}",
            summaryListUrl: "{{ url('web/fund-distributions/api/summary-list') }}",
            drillDownBaseUrl: "{{ url('web/fund-distributions/drill') }}"
        });
    </script>   
    <script>
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
            url: "{{ url('web/fund-distributions/api/summary-list') }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.financial_year;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.major_component_name;
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.sub_component_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_allocated;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.total_distributed;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.remaining;
                    }
                },
                {
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        let targetSub = row.sub_component_id ? row.sub_component_id : 'NULL';
                        let drillUrl = `{{url('web/fund-distributions/drill')}}/${row.financial_year}/${row.major_component_id}/${targetSub}`;

                        return `<a href="${drillUrl}" class="btn btn-sm btn-info" title="View Analysis"><i class="fa fa-eye"></i></a>`;
                    }
                }
            ],
            filters: ["financial_year", "major_component_id", "sub_component_id"]
        });



        function getStatusLabel(status) {
            switch (status) {
                case 1:
                    return `<span class="badge bg-primary">Pending</span>`;
                    break;
                case 2:
                    return `<span class="badge bg-success">Approved</span>`;
                    break;
                case 3:
                    return `<span class="badge bg-danger">Rejected</span>`;
                case 4:
                    return `<span class="badge bg-warning">Reverted</span>`;
                    break;
                    default:
                        return `<span class="badge bg-secondary">N/A</span>`;
                    }
                }
                </script>
                
@endsection











<!-- @section('js')
    <script>
        // Pass backend-aware paths to script initialization engine
        initDistributionListing({
            subComponentUrl: "{{ url('web/api/allocation/sub-components') }}",
            summaryListUrl: "{{ url('web/fund-distributions/api/summary-list') }}",
            drillDownBaseUrl: "{{ url('web/fund-distributions/drill') }}"
        });
    </script>
@endsection -->
