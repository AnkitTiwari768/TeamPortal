@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('fund_flow.fund_allocation_list') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ url('dashboard') }}">{{ __('fund_flow.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="#">{{ __('fund_flow.fund_allocation_list') }}</a></li>
                                </ol>
                            </nav>
                        </div>
                        @if (acl('fund-allocation-create'))
                            <div class="action-header ms-auto">
                                <!-- split button -->
                                <div class="btn-group drop-btn">
                                    <a href="{{ url('fund-allocation/create') }}">
                                        <button type="button" class="btn btn-primary">
                                            <img src="{{ asset('assets/img-new/add.svg') }}">
                                            {{ __('fund_flow.add_fund_allocation') }}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-body pt-1">
                        <!-- Advanced Filter Panel -->
                        <div class="border-bottom pb-3 mb-3 bg-light p-3 rounded">
                            <form id="search_form" autocomplete="off" class="w-100">
                                <!-- Row 1: Period filters -->
                                <div class="row g-3 align-items-end filter-bar mb-3">
                                    <div class="col-md-4 col-sm-12">
                                        <label class="form-label fw-bold">{{ __('fund_flow.financial_year') }}</label>
                                        <select name="financial_year" id="financial_year" class="form-select filter_btn">
                                            <option value="">Select Financial Year</option>
                                            @foreach ($financialYears as $key => $val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.duration') }}</label>
                                        <select name="duration_id" id="duration_id" class="form-select filter_btn">
                                            <option value="">Select Duration</option>
                                            @foreach ($duration as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.sub_duration') }}</label>
                                        <select name="sub_duration_id" id="sub_duration_id" class="form-select filter_btn">
                                            <option value="">Select Sub Duration</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- Row 2: Component filters & Actions -->
                                <div class="row g-3 align-items-end filter-bar">
                                    <div class="col-md-6 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.major_component') }}</label>
                                        <select name="major_component_id" id="major_component_id"
                                            class="form-select filter_btn">
                                            <option value="">Select Major Component</option>
                                            @foreach ($components as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.sub_component') }}</label>
                                        <select name="sub_component_id" id="sub_component_id"
                                            class="form-select filter_btn">
                                            <option value="">Select Sub Component</option>
                                        </select>
                                    </div>
                                    <div class="col-12 mt-2 d-flex align-items-center">
                                        <a href="javascript:void(0)" class="clear-action btn btn-default btn-sm"
                                            id="reset_btn" style="display:none;">
                                            <i class="fa fa-times"></i> Reset Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- tab content -->
                        <div class="tab-content view-application-tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="national-p" role="tabpanel"
                                aria-labelledby="nav-home-tab">

                                <!-- table start -->
                                <table id="dataTable" class="table datatable table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th>{{ __('fund_flow.s_no') }}</th>
                                            <th>{{ __('fund_flow.financial_year') }}</th>
                                            <th>{{ __('fund_flow.duration') }}</th>
                                            <th>{{ __('fund_flow.sub_duration') }}</th>
                                            <th>{{ __('fund_flow.sanction_order_number') }}</th>
                                            <th>{{ __('fund_flow.sanction_order_date') }}</th>
                                            <th>{{ __('Total Amount Allocated') }}</th>
                                            <th>{{ __('Total Allocated Funds') }}</th>
                                            <th>{{ __('Total Available Amount') }}</th>
                                            <th>{{ __('fund_flow.created_date') }}</th>
                                            <th class="actions">{{ __('fund_flow.action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                                <!-- table end -->
                            </div>
                        </div>
                        <!-- tab ends -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@section('js')
    ;
    <script>
        var selectedRows = {};
        var tableId = "#dataTable";

        // Show loader immediately on page load
        $('#ajax-loader').show();

        // Bind events before initialization to catch the initial AJAX request
        $(tableId).on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });

        $(tableId).on('xhr.dt draw.dt', function() {
            $('#ajax-loader').hide();
        });

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 8,
                direction: "desc"
            },
            url: "{{ url('fund-allocation/datalist') }}",
            columns: [{
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
                        return row.duration ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sub_duration ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sanction_order_no ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sanction_order_date ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_fresh_amount ? Number(row.total_fresh_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_allocated_amount ? Number(row.total_allocated_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_available_amount ? Number(row.total_available_amount).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
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
                       
                            del = buttonDelete("{{ url('fund-allocation-delete') }}", row.id);
                        

                        var viewBtn = '';
                        @if (acl('fund-allocation-view'))
                            var viewBtn = buttonView("{{ url('fund-allocation') }}", row.id);
                        @endif

                        var actions = createActionButtons([viewBtn, del]);
                        return actions;
                    }
                }
            ],
            filters: ["financial_year", "major_component_id", "sub_component_id", "duration_id", "sub_duration_id"]
        });

        // Cascading Dropdown Helper
        function loadCascadeDropdown(attrCode, parentId, selectId, placeholder) {
            var $sel = $('#' + selectId);
            $sel.html('<option value="">Loading...</option>');

            if (!parentId) {
                $sel.html('<option value="">' + placeholder + '</option>');
                return;
            }

            $.ajax({
                url: "{{ url('fund-allocation/attribute-values') }}/" + encodeURIComponent(attrCode),
                method: 'GET',
                data: {
                    parent_id: parentId
                },
                headers: {
                    'Accept': 'application/json'
                },
                success: function(result) {
                    var items = Array.isArray(result) ? result : (result.data || []);
                    var html = '<option value="">' + placeholder + '</option>';
                    $.each(items, function(i, item) {
                        var label = item.name || item.attribute_value || '';
                        html += '<option value="' + item.id + '">' + label + '</option>';
                    });
                    $sel.html(html);
                },
                error: function() {
                    $sel.html('<option value="">' + placeholder + '</option>');
                }
            });
        }

        // Cascade Events
        $('#major_component_id').on('change', function() {
            var majorId = $(this).val();
            loadCascadeDropdown('major-components', majorId, 'sub_component_id', 'Select Sub Component');
        });

        $('#duration_id').on('change', function() {
            var durationId = $(this).val();
            loadCascadeDropdown('duration', durationId, 'sub_duration_id', 'Select Sub Duration');
        });

        // Reset cascading elements on form reset
        $('#reset_btn').on('click', function() {
            $('#sub_component_id').html('<option value="">Select Sub Component</option>');
            $('#sub_duration_id').html('<option value="">Select Sub Duration</option>');
        });

        // Search Debouncing
        function debounce(func, wait) {
            var timeout;
            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    func.apply(context, args);
                }, wait);
            };
        }

        $(tableId).on('init.dt', function() {
            var oTable = $(tableId).DataTable();
            $('.dataTables_filter input')
                .off()
                .on('keyup input', debounce(function() {
                    oTable.search(this.value).draw();
                }, 500));
        });
    </script>
@endsection

@endsection
