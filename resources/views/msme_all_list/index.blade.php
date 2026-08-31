@extends('components.admin.content-layout')

@section('card-content')

@include('msme_all_list.loader_css')

<div class="card-body">
    <form id="msme_all_list_search_form" autocomplete="off">
        <div class="filter-bar d-flex align-items-left flex-column gap-3 py-2">
            <div class="first-filter-row d-flex gap-3">
                <div class="row">
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box from-date-box">
                            <label class="form-label">From date</label>
                            <input type="text" class="form-control filter_btn" name="from_date" id="from_dates"
                                placeholder="From Date">
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box to-date-box">
                            <label class="form-label">To date</label>
                            <input type="text" class="form-control filter_btn" name="to_date" id="to_dates"
                                placeholder="To Date">
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Transaction Type</label>
                            {!! Form::select('ondc_transaction_type_id', array('' => 'Select') + static_common_list($lists?->ondc_types), '', ['class' => 'form-select select2 filter_btn', 'id' => 'ondc_transaction_type_id']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Product Category</label>
                            {!! Form::select('product_category_id[]', remove_select_dynamic_common_list($lists?->sub_domains), '', ['class' => 'form-select select2 filter_btn', 'id' => 'product_category_id', 'multiple' => 'multiple']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">State</label>
                            {!! Form::select('state_id[]', remove_select_dynamic_common_list($lists?->state_id), '', ['class' => 'form-select select2 filter_btn', 'id' => 'state_id', 'multiple' => 'multiple']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Status</label>
                            {!! Form::select('msme_status', msmeStatusTypeOptions(), '', ['class' => 'form-select select2 filter_btn', 'id' => 'msme_status']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">SNP Approved</label>
                            {!! Form::select('snp_approved_id', array('' => 'All') + ($lists?->snp_approved ?? []), '', ['class' => 'form-select select2 filter_btn', 'id' => 'snp_approved_id']) !!}
                        </div>
                    </div>
                    <div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-3">
                        <a href="javascript:void(0)" class="clear-action" id="msme_all_list_reset_btn" style="display: none;">
                            <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}" alt="clear">
                            Clear all
                        </a>
                        <span class="filter-loader" id="msmeAllListFilterLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-3 msme-all-list-export-actions">
    <button type="button" class="msme-all-list-export-btn msme-all-list-export-btn--excel" id="msme_all_list_export_excel">
        <i class="fas fa-file-excel"></i> Download Excel
    </button>
    <button type="button" class="msme-all-list-export-btn msme-all-list-export-btn--pdf" id="msme_all_list_export_pdf">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</div>

@include('msme_all_list.summary_cards')

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="msmeAllListDataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>{{ __('message.sn') }}</th>
                <th>Team ID</th>
                <th>Udyam Number</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Name of Enterpreneur</th>
                <th>Name of enterprise</th>
                <th>Enterprise Type</th>
                <th>State</th>
                <th>Registration Date</th>
                <th>Type of Transaction</th>
                <th>Product Category</th>
                <th>Incorporation Date</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows will be populated by DataTable -->
        </tbody>
    </table>
</div>

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.csrfToken = "{{ csrf_token() }}";
    var msmeAllListFilterTimeout = null;
    var msmeAllListIsLoading = false;

    // ============================================
    // LOADER FUNCTIONS
    // ============================================

    function msmeAllListShowLoader(text = 'Loading...', subtext = 'Please wait while we process your request') {
        if (!msmeAllListIsLoading) {
            msmeAllListIsLoading = true;
            $('#msmeAllListGlobalLoader .loader-text').text(text);
            $('#msmeAllListGlobalLoader .loader-subtext').text(subtext);
            $('#msmeAllListGlobalLoader').addClass('active');
            $('body').addClass('loading-disabled');
        }
    }

    function msmeAllListHideLoader() {
        msmeAllListIsLoading = false;
        $('#msmeAllListGlobalLoader').removeClass('active');
        $('body').removeClass('loading-disabled');
    }

    function msmeAllListShowFilterLoader() {
        $('#msmeAllListFilterLoader').addClass('active');
    }

    function msmeAllListHideFilterLoader() {
        $('#msmeAllListFilterLoader').removeClass('active');
    }

    // ============================================
    // SERIAL NUMBER FUNCTION
    // ============================================

    function msmeAllListSerialNumber(tableId, rowIndex) {
        var table = $(tableId).DataTable();
        var pageInfo = table.page.info();
        return pageInfo.start + rowIndex + 1;
    }

    // ============================================
    // SELECT2 INITIALIZATION
    // ============================================

    function msmeAllListInitSelect2(element, reloadOnChange = true) {
        if (!element.length) return;

        element.select2({
            placeholder: "Select",
            allowClear: true,
            closeOnSelect: true,
            width: '100%'
        }).on('change', function(e) {
            if (reloadOnChange) {
                msmeAllListHandleFilterChange();
            }
        });
    }

    // ============================================
    // CURRENT FILTER VALUES (shared by datatable + summary cards)
    // ============================================

    function msmeAllListGetFilters() {
        return {
            from_dates: $('#from_dates').val(),
            to_dates: $('#to_dates').val(),
            ondc_transaction_type_id: $('#ondc_transaction_type_id').val(),
            product_category_id: $('#product_category_id').val(),
            state_id: $('#state_id').val(),
            msme_status: $('#msme_status').val(),
            snp_approved_id: $('#snp_approved_id').val()
        };
    }

    // ============================================
    // EXPORT (Excel / PDF) - reuses the same six filters as the list & cards
    // ============================================

    function msmeAllListBuildExportUrl(baseUrl) {
        var filters = msmeAllListGetFilters();
        var params = [];

        $.each(filters, function(key, val) {
            if (val === null || val === undefined || val === '') {
                return;
            }
            if (Array.isArray(val)) {
                $.each(val, function(i, v) {
                    params.push('filters[' + key + '][]=' + encodeURIComponent(v));
                });
            } else {
                params.push('filters[' + key + ']=' + encodeURIComponent(val));
            }
        });

        return baseUrl + (params.length ? ('?' + params.join('&')) : '');
    }

    $('#msme_all_list_export_excel').on('click', function() {
        window.location.href = msmeAllListBuildExportUrl("{{ url('msme-all-list/export-excel') }}");
    });

    $('#msme_all_list_export_pdf').on('click', function() {
        window.location.href = msmeAllListBuildExportUrl("{{ url('msme-all-list/export-pdf') }}");
    });

    // ============================================
    // SUMMARY CARDS
    // ============================================

    function msmeAllListLoadSummaryCards() {
        $.ajax({
            url: "{{ url('msme-all-list/summary-cards') }}",
            type: 'GET',
            data: { filters: msmeAllListGetFilters() },
            success: function(response) {
                var data = (response && response.data) ? response.data : {};

                $('#msme_all_list_total_count').text(data.total_msme || 0);
                $('#msme_all_list_open_msme_count').text(data.open_msme || 0);
                $('#msme_all_list_direct_selection_msme_count').text(data.direct_selection_msme || 0);
                $('#msme_all_list_onboarded_msme_count').text(data.onboarded_msme || 0);
            },
            error: function(jqxhr, techNote, message) {
                console.error('Summary Cards Error:', message);
            }
        });
    }

    // ============================================
    // FILTER HANDLING
    // ============================================

    function msmeAllListHandleFilterChange() {
        clearTimeout(msmeAllListFilterTimeout);
        msmeAllListShowFilterLoader();
        msmeAllListShowLoader('Applying Filters...', 'Please wait while we filter the data');

        msmeAllListFilterTimeout = setTimeout(function() {
            try {
                if (msmeAllListTable) {
                    msmeAllListTable.ajax.reload(function() {
                        msmeAllListHideFilterLoader();
                        msmeAllListHideLoader();
                        $('#msme_all_list_reset_btn').show();
                    });
                }
                msmeAllListLoadSummaryCards();
            } catch (error) {
                msmeAllListHideFilterLoader();
                msmeAllListHideLoader();
                console.error('Filter Error:', error);
            }
        }, 500);
    }

    // ============================================
    // DATATABLE INITIALIZATION
    // ============================================

    $.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing';

    var msmeAllListTable = dataTableInit({
        id: "#msmeAllListDataTable",
        showExcelExport: false,
        showCustomExportOption: true,
        order: {
            column: 1,
            direction: "asc"
        },
        url: "{{ url('msme-all-list/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return msmeAllListSerialNumber("#msmeAllListDataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.team_id || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.udyam_no || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.mobile || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.email || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.entrepreneur_name || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.enterprise_name || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.organisation_type || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.state_name || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.created_at || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.transaction_type || "N/A";
                }
            },
            {
                orderable: false,
                render: function (data, type, row) {
                    let text = '';
                    if (Array.isArray(row.product_categories)) {
                        text = row.product_categories.join(', ');
                    } else {
                        text = row.product_categories || '';
                    }

                    if (type === 'sort' || type === 'filter') {
                        return text;
                    }

                    if (text.length <= 20) {
                        return text || 'N/A';
                    }

                    return `
                        <span class="short-text">${text.substring(0, 20)}...</span>
                        <span class="full-text d-none">${text}</span>
                        <a href="#" class="toggle-text d-block mt-1">Read More</a>
                    `;
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.incorporation_date || "N/A";
                }
            }
        ],
        filters: ["from_dates", "to_dates", "ondc_transaction_type_id", "product_category_id", "state_id", "msme_status", "snp_approved_id"],

        preDrawCallback: function(settings) {
            msmeAllListShowLoader('Loading Data...', 'Please wait while we fetch the data');
            return true;
        },

        drawCallback: function(settings) {
            msmeAllListHideLoader();
            msmeAllListHideFilterLoader();
            msmeAllListCheckFilterValues();
        },

        initComplete: function(settings, json) {
            msmeAllListHideLoader();
            msmeAllListHideFilterLoader();
            msmeAllListCheckFilterValues();

            $('.dataTables_length select').on('change', function() {
                msmeAllListShowLoader('Loading Data...', 'Please wait while we update the page');
            });
        },

        error: function(settings, techNote, message) {
            msmeAllListHideLoader();
            msmeAllListHideFilterLoader();
            console.error('DataTable Error:', message);
        }
    });

    // ============================================
    // NOTE: the "filters" array above lists DOM element ids only — dataTableInit()
    // (assets/js/script.js) reads each one's current value on every ajax request,
    // so the datalist and summary-card counts always reflect the same six filters
    // (From Date, To Date, Transaction Type, Product Category, State, Status).
    // ============================================

    function msmeAllListCheckFilterValues() {
        let hasValue = false;
        $('#msme_all_list_search_form .filter_btn').each(function() {
            let val = $(this).val();
            if (val && val.length > 0 && !(Array.isArray(val) && val.length === 0)) {
                hasValue = true;
                return false;
            }
        });

        if (hasValue) {
            $('#msme_all_list_reset_btn').show();
        } else {
            $('#msme_all_list_reset_btn').hide();
        }
    }

    $(document).ready(function() {
        msmeAllListShowLoader('Loading Page...', 'Please wait while the page loads');

        $("#from_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#to_dates").datepicker("option", "minDate", selected);
                msmeAllListHandleFilterChange();
            }
        });

        $("#to_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#from_dates").datepicker("option", "maxDate", selected);
                msmeAllListHandleFilterChange();
            }
        });

        msmeAllListInitSelect2($('#ondc_transaction_type_id'), true);
        msmeAllListInitSelect2($('#product_category_id'), true);
        msmeAllListInitSelect2($('#state_id'), true);
        msmeAllListInitSelect2($('#msme_status'), true);
        msmeAllListInitSelect2($('#snp_approved_id'), true);

        $(document).on('draw.dt', '#msmeAllListDataTable', function() {
            msmeAllListHideLoader();
            msmeAllListHideFilterLoader();
        });

        $(document).on('change', '.dataTables_length select', function() {
            msmeAllListShowLoader('Loading Data...', 'Please wait while we update the page');
        });

        setTimeout(function() {
            if ($('#msmeAllListGlobalLoader').hasClass('active')) {
                msmeAllListHideLoader();
            }
        }, 5000);
    });

    $("#msme_all_list_reset_btn").on("click", function() {
        msmeAllListShowLoader('Clearing Filters...', 'Please wait while we reset all filters');
        msmeAllListShowFilterLoader();

        try {
            $("#from_dates").val('');
            $("#to_dates").val('');
            $("#ondc_transaction_type_id").val('').trigger('change');
            $("#product_category_id").val('').trigger('change');
            $("#state_id").val('').trigger('change');
            $("#msme_status").val('').trigger('change');
            $("#snp_approved_id").val('').trigger('change');

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            if (msmeAllListTable) {
                msmeAllListTable.ajax.reload(function() {
                    msmeAllListHideFilterLoader();
                    msmeAllListHideLoader();
                    $('#msme_all_list_reset_btn').hide();
                });
            }
            msmeAllListLoadSummaryCards();
        } catch (error) {
            msmeAllListHideFilterLoader();
            msmeAllListHideLoader();
            console.error('Clear Error:', error);
        }
    });

    $(document).on('keyup', '#msmeAllListDataTable_filter input', function(e) {
        clearTimeout(msmeAllListFilterTimeout);
        msmeAllListShowFilterLoader();
        msmeAllListShowLoader('Searching...', 'Please wait while we search the data');

        msmeAllListFilterTimeout = setTimeout(function() {
            try {
                if (msmeAllListTable) {
                    msmeAllListTable.ajax.reload(function() {
                        msmeAllListHideFilterLoader();
                        msmeAllListHideLoader();
                    });
                }
            } catch (error) {
                msmeAllListHideFilterLoader();
                msmeAllListHideLoader();
                console.error('Search Error:', error);
            }
        }, 500);
    });

    $(document).on('click', '.toggle-text', function(e) {
        e.preventDefault();
        let td = $(this).closest('td');
        td.find('.short-text').toggleClass('d-none');
        td.find('.full-text').toggleClass('d-none');
        if ($(this).hasClass('expanded')) {
            $(this).text('Read More').removeClass('expanded');
        } else {
            $(this).text('Read Less').addClass('expanded');
        }
    });

    $(document).on('click', '.paginate_button', function(e) {
        if (!$(this).hasClass('disabled')) {
            msmeAllListShowLoader('Loading Page...', 'Please wait while we load the next page');
        }
    });

    let msmeAllListResizeTimer;
    $(window).on('resize', function() {
        clearTimeout(msmeAllListResizeTimer);
        msmeAllListResizeTimer = setTimeout(function() {
            if (msmeAllListTable) {
                msmeAllListTable.columns.adjust();
            }
        }, 250);
    });
</script>
@endsection
