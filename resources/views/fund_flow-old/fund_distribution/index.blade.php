@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex align-items-center py-3" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>{{ __('fund_flow.fund_distribution_list') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.dashboard') }}</a></li>
                                    <li class="breadcrumb-item active" style="color: #0f172a; font-weight: 500;">{{ __('fund_flow.fund_distribution_list') }}</li>
                                </ol>
                            </nav>
                        </div>
                        @if (acl('fund-distribution-create'))
                            <div class="action-header ms-auto">
                                <div class="btn-group drop-btn">
                                    <a href="{{ url('fund-distributions/create') }}">
                                        <button type="button" class="btn btn-primary">
                                            <img src="{{ asset('assets/img-new/add.svg') }}">
                                            {{ __('fund_flow.add_fund_distribution') }}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-body pt-1">
                        <!-- Advanced Filter Panel -->
                        <div class="border-bottom pb-3 mb-3 bg-light p-3 rounded mt-3">
                            <form id="search_form" autocomplete="off" class="w-100">
                                <div class="row g-3 align-items-end filter-bar">
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
                                        <label class="form-label fw-bold">{{ __('fund_flow.major_component') }}</label>
                                        <select name="major_component_id" id="major_component_id" class="form-select filter_btn">
                                            <option value="">Select Major Component</option>
                                            @foreach ($components as $key => $value)
                                                <option value="{{ $key }}">{{ $value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-sm-6">
                                        <label class="form-label fw-bold">{{ __('fund_flow.sub_component') }}</label>
                                        <select name="sub_component_id" id="sub_component_id" class="form-select filter_btn">
                                            <option value="">Select Sub Component</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2" id="reset_btn_row" style="display:none;">
                                    <div class="col-12">
                                        <a href="javascript:void(0)" class="clear-action btn btn-default btn-sm" id="reset_btn">
                                            <i class="fa fa-times"></i> Reset Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Table -->
                        <div class="tab-content view-application-tab-content">
                            <div class="tab-pane fade show active">
                                <div class="table-responsive">
                                    <table id="dataTable" class="table table-themed table-striped table-hover align-middle border" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">{{ __('fund_flow.s_no') }}</th>
                                                <th>{{ __('fund_flow.financial_year') }}</th>
                                                <th>{{ __('fund_flow.major_component') }}</th>
                                                <th>{{ __('fund_flow.sub_component') }}</th>
                                                <th class="text-end">{{ __('Total Allocated (₹)') }}</th>
                                                <th class="text-end">{{ __('Total Distributed (₹)') }}</th>
                                                <th class="text-end">{{ __('Remaining (₹)') }}</th>
                                                <th class="text-center actions" style="width: 100px;">{{ __('fund_flow.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-dark"></tbody>
                                    </table>
                                </div>
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
                column: 1,
                direction: "desc"
            },
            url: "{{ url('fund-distributions/api/summary-list') }}",
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
                        return row.major_component_name ?? '-';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sub_component_name ? row.sub_component_name : '<span class="badge bg-light text-dark text-muted">None</span>';
                    }
                },
                {
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function(data, type, row) {
                        return row.total_allocated ? Number(row.total_allocated).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
                    }
                },
                {
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function(data, type, row) {
                        return row.total_distributed ? Number(row.total_distributed).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
                    }
                },
                {
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function(data, type, row) {
                        let val = Number(row.remaining || 0);
                        let color = val < 0 ? 'text-danger' : 'text-success';
                        return `<span class="${color}">${val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`;
                    }
                },
                {
                    "orderable": false,
                    "className": "text-center",
                    "render": function(data, type, row) {
                        let targetSub = row.sub_component_id ? row.sub_component_id : 'NULL';
                        let drillUrl = `{{ url('fund-distributions/drill') }}/${row.financial_year}/${row.major_component_id}/${targetSub}`;
                        return `<a href="${drillUrl}" class="btn btn-sm btn-info text-white" title="View Analysis"><i class="fa fa-eye"></i></a>`;
                    }
                }
            ],
            filters: ["financial_year", "major_component_id", "sub_component_id"]
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
            if (majorId) {
                $('#reset_btn_row').show();
            }
        });

        $('#financial_year').on('change', function() {
            if ($(this).val()) {
                $('#reset_btn_row').show();
            }
        });

        $('#sub_component_id').on('change', function() {
            if ($(this).val()) {
                $('#reset_btn_row').show();
            }
        });

        // Reset cascading elements on form reset
        $('#reset_btn').on('click', function() {
            $('#financial_year').val('');
            $('#major_component_id').val('');
            $('#sub_component_id').html('<option value="">Select Sub Component</option>');
            $('#reset_btn_row').hide();
            $(tableId).DataTable().draw();
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
