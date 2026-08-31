@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('fund_flow.carry_forward_mapping') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">{{ __('fund_flow.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a href="#">{{ __('fund_flow.fund_flow_management') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('fund_flow.carry_forward_mapping') }}</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ url('carry-forward-mapping/create') }}">
                                    <button type="button" class="btn btn-primary">
                                        <img src="{{ asset('assets/img-new/add.svg') }}">
                                        {{ __('fund_flow.add_carry_forward') }}
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-1">
                        <!-- Filters -->
                        <div class="border-bottom pb-3 mb-3 bg-light p-3 rounded">
                            <form id="search_form" autocomplete="off" class="w-100">
                                <div class="row g-3 align-items-end filter-bar">
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.financial_year') }}</label>
                                        <select name="financial_year" id="financial_year" class="form-select filter_btn">
                                            <option value="">Select Financial Year</option>
                                            @foreach ($financialYears as $key => $val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.carry_forward_type') }}</label>
                                        <select name="from_duration_id" id="from_duration_id" class="form-select filter_btn">
                                            <option value="">Select Carry Forward Type</option>
                                            @foreach ($duration as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.from_duration') }}</label>
                                        <select name="from_sub_duration_id" id="from_sub_duration_id" class="form-select filter_btn">
                                            <option value="">Select From Duration</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.to_duration') }}</label>
                                        <select name="to_sub_duration_id" id="to_sub_duration_id" class="form-select filter_btn">
                                            <option value="">Select To Duration</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Table -->
                        <div class="tab-content view-application-tab-content">
                            <div class="tab-pane fade show active" role="tabpanel">
                                <table id="dataTable" class="table datatable table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('fund_flow.s_no') }}</th>
                                            <th>{{ __('fund_flow.financial_year') }}</th>
                                            <th>{{ __('fund_flow.from_duration') }}</th>
                                            <th>{{ __('fund_flow.to_duration') }}</th>
                                            <th>{{ __('fund_flow.total_amount') }}</th>
                                            <th>{{ __('fund_flow.created_by') }}</th>
                                            <th>{{ __('fund_flow.created_date') }}</th>
                                            <th class="actions">{{ __('fund_flow.action') }}</th>
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

@section('js')
    <script>
        var selectedRows = {};
        var tableId = "#dataTable";

        $('#ajax-loader').show();

        $(tableId).on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });

        $(tableId).on('xhr.dt draw.dt', function() {
            $('#ajax-loader').hide();
        });

        $(document).on('change', '#from_duration_id', function() {
            var durationId = $(this).val();
            var $fromSub = $('#from_sub_duration_id');
            var $toSub = $('#to_sub_duration_id');

            $fromSub.html('<option value="">Select From Duration</option>');
            $toSub.html('<option value="">Select To Duration</option>');

            if (!durationId) {
                return;
            }

            $.ajax({
                url: "{{ url('fund-allocation/attribute-values') }}/duration",
                method: 'GET',
                data: { parent_id: durationId },
                headers: { 'Accept': 'application/json' },
                success: function(result) {
                    var items = Array.isArray(result) ? result : (result.data || []);
                    var fromOpts = '<option value="">Select From Duration</option>';
                    var toOpts = '<option value="">Select To Duration</option>';
                    $.each(items, function(i, item) {
                        var label = item.name || item.attribute_value || '';
                        fromOpts += '<option value="' + item.id + '">' + label + '</option>';
                        toOpts += '<option value="' + item.id + '">' + label + '</option>';
                    });
                    $fromSub.html(fromOpts);
                    $toSub.html(toOpts);
                }
            });
        });

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 6,
                direction: "desc"
            },
            url: "{{ url('carry-forward-mapping/datalist') }}",
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
                        return row.financial_year ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.from_sub_duration ?? row.from_duration ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.to_sub_duration ?? row.to_duration ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_amount ? Number(row.total_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_by_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at ?? '-';
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        var del = '';
                        var viewBtn = buttonView("{{ url('carry-forward-mapping') }}", row.id);
                        var editBtn = buttonEdit("{{ url('carry-forward-mapping') }}", row.id);
                        return createActionButtons([editBtn, viewBtn, del]);
                    }
                }
            ],
            filters: ["financial_year", "from_duration_id", "from_sub_duration_id", "to_sub_duration_id"]
        });
    </script>
@endsection
@endsection
