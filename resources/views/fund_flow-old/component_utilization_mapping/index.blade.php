@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('fund_flow.component_utilization_mapping') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}">{{ __('fund_flow.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a href="#">{{ __('fund_flow.fund_flow_management') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('fund_flow.component_utilization_mapping') }}</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ url('component-utilization-mapping/create') }}">
                                    <button type="button" class="btn btn-primary">
                                        <img src="{{ asset('assets/img-new/add.svg') }}">
                                        {{ __('fund_flow.add_component_utilization_mapping') }}
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
                                        <label class="form-label fw-bold">{{ __('fund_flow.duration') }}</label>
                                        <select name="duration_id" id="duration_id" class="form-select filter_btn">
                                            <option value="">Select Duration</option>
                                            @if (!empty($duration))
                                                @foreach ($duration as $key => $value)
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6" id="sub_duration_filter_wrapper" style="display:none;">
                                        <label class="form-label fw-bold">{{ __('fund_flow.sub_duration') }}</label>
                                        <select name="sub_duration_id" id="sub_duration_id" class="form-select filter_btn">
                                            <option value="">Select Sub Duration</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.target_major_component') }}</label>
                                        <select name="target_major_component_id" id="target_major_component_id" class="form-select filter_btn">
                                            <option value="">Select Target Major Component</option>
                                            @foreach ($majorComponents as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.target_sub_component') }}</label>
                                        <select name="target_sub_component_id" id="target_sub_component_id" class="form-select filter_btn">
                                            <option value="">Select Target Sub Component</option>
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
                                            <th>{{ __('fund_flow.duration') }}</th>
                                            <th>{{ __('fund_flow.sub_duration') }}</th>
                                            <th>{{ __('fund_flow.target_major_component') }}</th>
                                            <th>{{ __('fund_flow.target_sub_component') }}</th>
                                            <th>{{ __('fund_flow.total_max_utilization') }}</th>
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
    @include('scripts/attribute_dependency_dropdown')
    <script>
        var tableId = "#dataTable";

        $('#ajax-loader').show();

        $(tableId).on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });

        $(tableId).on('xhr.dt draw.dt', function() {
            $('#ajax-loader').hide();
        });

        $(document).on('change', '#duration_id', function() {
            var duration = $(this).val();
            var durationText = $(this).find('option:selected').text().trim();

            $('#sub_duration_filter_wrapper').hide();
            $('#sub_duration_id').html('<option value="">Select Sub Duration</option>');

            if (!duration || durationText === 'Yearly') {
                return;
            }

            $('#sub_duration_filter_wrapper').show();
            loadAttributeDropdownOptions(duration.toLowerCase(), "#sub_duration_id", true);
        });

        $(document).on('change', '#target_major_component_id', function() {
            var majorId = $(this).val();
            var $targetSub = $('#target_sub_component_id');

            $targetSub.html('<option value="">Select Target Sub Component</option>');

            if (!majorId) {
                return;
            }

            loadAttributeDropdownOptions(majorId, "#target_sub_component_id", true);
        });

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 8,
                direction: "desc"
            },
            url: "{{ url('component-utilization-mapping/datalist') }}",
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
                        return row.duration_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sub_duration_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.target_major_component_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.target_sub_component_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_max_utilization_amount ? Number(row.total_max_utilization_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
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
                        var viewBtn = buttonView("{{ url('component-utilization-mapping') }}", row.id);
                        var editBtn = buttonEdit("{{ url('component-utilization-mapping') }}", row.id);
                        return createActionButtons([editBtn, viewBtn]);
                    }
                }
            ],
            filters: ["financial_year", "duration_id", "sub_duration_id", "target_major_component_id", "target_sub_component_id"]
        });
    </script>
@endsection
@endsection
