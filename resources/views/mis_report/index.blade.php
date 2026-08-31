@extends('components.admin.content-layout')

@section('card-content')


@include('mis_report.mis_loader_css')

<div class="card-body">
    <form id="search_form" autocomplete="off">
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
                            <input type="text" class="form-control filter_btn" name="to_date" id="to_dates" placeholder="To Date">
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Classification</label>
                            {!! Form::select('msme_classification', msmeClassification(), '', ['class' => 'form-select filter_btn', 'id' => 'msme_classification']) !!}
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
                            <label class="form-label">SNP</label>
                            {!! Form::select('snp_id', array('' => 'Select') + ($lists?->snp_list ?? []), '', ['class' => 'form-select select2 filter_btn', 'id' => 'snp_id']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Major Activity of Business</label>
                            {!! Form::select('major_activity', majorActivity(), '', ['class' => 'form-select select2 filter_btn', 'id' => 'major_activity']) !!}
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
                            <label class="form-label">Gender</label>
                            {!! Form::select('gender', gender(), '', ['class' => 'form-select filter_btn', 'id' => 'gender']) !!}
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
                            <label class="form-label">Mapping Option</label>
                            {!! Form::select('mapping_option', mapping_filter(), '', ['class' => 'form-select select2 filter_btn', 'id' => 'mapping_option']) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Status</label>
                            {!! Form::select(
                                'msme_status',
                                msmeStatusTypeOptions(),
                                '',
                                [
                                    'class' => 'form-select select2 filter_btn',
                                    'id' => 'msme_status'
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">Source Of Registration</label>
                            {!! Form::select(
                                'source_of_registration',
                                [
                                    '' => 'Select',
                                    'Association' => 'Association',
                                    'SNP' => 'SNP',
                                    'Self' => 'Self'
                                ],
                                '',
                                [
                                    'class' => 'form-select select2 filter_btn',
                                    'id' => 'source_of_registration'
                                ]
                            ) !!}
                        </div>
                    </div>
                    <div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-3">
                        <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
                            <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}" alt="clear">
                            Clear all
                        </a>
                        <span class="filter-loader" id="filterLoader">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>{{ __('message.sn') }}</th>
                <th>Source Of Registration</th>
                <th>Team ID</th>
                <th>Udyam Number</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Name of Enterpreneur</th>
                <th>Name of enterprise</th>
                <th>Enterprise Type</th>
                <th>Major Category</th>
                <th>Product Category</th>
                <th>Gender</th>
                <th>State</th>
                <th>Registration Date</th>
                <th>Type of Transaction</th>
                <th>Incorporation Date</th>
                <th>SNP ID</th>
                <th class="actions">{{ __('message.action') }}</th>
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
    var selectedRows = {};
    var filterTimeout = null;
    var isLoading = false;

    // ============================================
    // LOADER FUNCTIONS
    // ============================================

    function showLoader(text = 'Loading...', subtext = 'Please wait while we process your request') {
        if (!isLoading) {
            isLoading = true;
            $('.loader-text').text(text);
            $('.loader-subtext').text(subtext);
            $('#globalLoader').addClass('active');
            $('body').addClass('loading-disabled');
        }
    }

    function hideLoader() {
        isLoading = false;
        $('#globalLoader').removeClass('active');
        $('body').removeClass('loading-disabled');
    }

    function showFilterLoader() {
        $('#filterLoader').addClass('active');
    }

    function hideFilterLoader() {
        $('#filterLoader').removeClass('active');
    }

    // ============================================
    // SERIAL NUMBER FUNCTION
    // ============================================

    function serialNumber(tableId, rowIndex) {
        var table = $(tableId).DataTable();
        var pageInfo = table.page.info();
        return pageInfo.start + rowIndex + 1;
    }

    // ============================================
    // BUTTON FUNCTIONS
    // ============================================

    function buttonView(url, id) {
        return `<a href="${url}/${id}" class="btn btn-sm btn-info" title="View Details">
                    <i class="fas fa-eye"></i>
                </a>`;
    }

    function createActionButtons(buttons) {
        return buttons.join(' ');
    }

    // ============================================
    // SELECT2 INITIALIZATION
    // ============================================

    function initSelect2(element, reloadOnChange = true) {
        if (!element.length) return;
        
        element.select2({
            placeholder: "Select",
            allowClear: true,
            closeOnSelect: true,
            width: '100%'
        }).on('change', function(e) {
            if (reloadOnChange) {
                handleFilterChange();
            }
        });
    }

    // ============================================
    // FILTER HANDLING
    // ============================================

    function handleFilterChange() {
        clearTimeout(filterTimeout);
        showFilterLoader();
        showLoader('Applying Filters...', 'Please wait while we filter the data');
        
        filterTimeout = setTimeout(function() {
            try {
                if (oTable) {
                    oTable.ajax.reload(function() {
                        hideFilterLoader();
                        hideLoader();
                        $('#reset_btn').show();
                    });
                }
            } catch (error) {
                hideFilterLoader();
                hideLoader();
                console.error('Filter Error:', error);
            }
        }, 500);
    }

    // ============================================
    // DATATABLE INITIALIZATION
    // ============================================

    // Completely hide DataTable default processing
    $.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing';

    // Initialize DataTable
    var oTable = dataTableInit({
        id: "#dataTable",
        showExcelExport: false,
        showCustomExportOption: true,
        order: {
            column: 1,
            direction: "asc"
        },
        url: "{{ url('mis-reports-msme/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.source_of_registration || "N/A";
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
                    return row.msme_classification || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.major_activity || "N/A";
                }
            },
            {
                orderable: true,
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
                "orderable": true,
                "render": function (data, type, row) {
                    return row.gender 
                    ? row.gender.charAt(0).toUpperCase() + row.gender.slice(1) 
                    : 'N/A';
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
                "orderable": true,
                "render": function (data, type, row) {
                    return row.transaction_type || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.incorporation_date || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.snp_id || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    var pview = buttonView("{{ url('mis-reports-msme-details/') }}", row.id);
                    return createActionButtons([pview]);
                }
            }
        ],
        filters: ["from_dates", "to_dates", "ondc_transaction_type_id", "product_category_id", "state_id", "gender", "msme_classification", "major_activity", "mapping_option", "msme_status", "source_of_registration", "snp_id"]
    });

    // ============================================
    // LOADER <-> DATATABLE LIFECYCLE
    // ============================================
    // NOTE: dataTableInit() (assets/js/script.js) does not forward
    // preDrawCallback/drawCallback/initComplete/error options to the
    // underlying DataTable, so the loader is wired directly to DataTables'
    // own jQuery events instead. "preXhr.dt" fires immediately before every
    // ajax request the table makes (initial load, sorting, pagination, page
    // length change, search, and our own filter-triggered reloads), so the
    // loader now covers the full fetch, and "draw.dt" fires only once the
    // fetched rows have actually been rendered, so it's hidden no earlier
    // than that.
    $('#dataTable').on('preXhr.dt', function() {
        showLoader('Loading Data...', 'Please wait while we fetch the data');
        showFilterLoader();
    });

    $('#dataTable').on('draw.dt', function() {
        hideLoader();
        hideFilterLoader();
        checkFilterValues();
    });

    $('#dataTable').on('error.dt', function(e, settings, techNote, message) {
        hideLoader();
        hideFilterLoader();
        console.error('DataTable Error:', message);
    });

    // ============================================
    // CHECK FILTER VALUES
    // ============================================

    function checkFilterValues() {
        let hasValue = false;
        $('.filter_btn, #from_dates, #to_dates').each(function() {
            let val = $(this).val();
            if (val && val.length > 0 && !(Array.isArray(val) && val.length === 0)) {
                hasValue = true;
                return false;
            }
        });
        
        if (hasValue) {
            $('#reset_btn').show();
        } else {
            $('#reset_btn').hide();
        }
    }

    // ============================================
    // DOCUMENT READY - INITIALIZE COMPONENTS
    // ============================================

    $(document).ready(function() {
        // Show loader on page load
        showLoader('Loading Page...', 'Please wait while the page loads');

        // Initialize datepickers
        $("#from_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#to_dates").datepicker("option", "minDate", selected);
                handleFilterChange();
            }
        });

        $("#to_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#from_dates").datepicker("option", "maxDate", selected);
                handleFilterChange();
            }
        });

        // Initialize Select2 elements
        initSelect2($('#mapping_option'), true);
        initSelect2($('#product_category_id'), true);
        initSelect2($('#state_id'), true);
        initSelect2($('#gender'), true);
        initSelect2($('#msme_classification'), true);
        initSelect2($('#major_activity'), true);
        initSelect2($('#ondc_transaction_type_id'), true);
        initSelect2($('#source_of_registration'), true);
        initSelect2($('#msme_status'), true);
        initSelect2($('#snp_id'), true);

        // Listen for DataTable draw events
        $(document).on('draw.dt', '#dataTable', function() {
            hideLoader();
            hideFilterLoader();
        });

        // Listen for page length change
        $(document).on('change', '.dataTables_length select', function() {
            showLoader('Loading Data...', 'Please wait while we update the page');
        });

        // Fallback - hide loader after 5 seconds if still visible
        setTimeout(function() {
            if ($('#globalLoader').hasClass('active')) {
                hideLoader();
                console.log('Loader hidden by fallback timeout');
            }
        }, 5000);
    });

    // ============================================
    // CLEAR FILTERS
    // ============================================

    $("#reset_btn").on("click", function() {
        showLoader('Clearing Filters...', 'Please wait while we reset all filters');
        showFilterLoader();
        
        try {
            // Reset all inputs
            $("#from_dates").val('');
            $("#to_dates").val('');
            $("#mapping_option").val('').trigger('change');
            $("#product_category_id").val('').trigger('change');
            $("#state_id").val('').trigger('change');
            $("#gender").val('').trigger('change');
            $("#msme_classification").val('').trigger('change');
            $("#major_activity").val('').trigger('change');
            $("#ondc_transaction_type_id").val('').trigger('change');
            $("#source_of_registration").val('').trigger('change');
            $("#msme_status").val('').trigger('change');
            $("#snp_id").val('').trigger('change');
            
            // Reset datepicker restrictions
            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);
            
            // Reload datatable
            if (oTable) {
                oTable.ajax.reload(function() {
                    hideFilterLoader();
                    hideLoader();
                    $('#reset_btn').hide();
                });
            }
        } catch (error) {
            hideFilterLoader();
            hideLoader();
            console.error('Clear Error:', error);
        }
    });

    // ============================================
    // SEARCH INPUT HANDLING
    // ============================================

    $(document).on('keyup', '#dataTable_filter input', function(e) {
        clearTimeout(filterTimeout);
        showFilterLoader();
        showLoader('Searching...', 'Please wait while we search the data');
        
        filterTimeout = setTimeout(function() {
            try {
                if (oTable) {
                    oTable.ajax.reload(function() {
                        hideFilterLoader();
                        hideLoader();
                    });
                }
            } catch (error) {
                hideFilterLoader();
                hideLoader();
                console.error('Search Error:', error);
            }
        }, 500);
    });

    // ============================================
    // TOGGLE TEXT FOR PRODUCT CATEGORIES
    // ============================================

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

    // ============================================
    // PAGINATION HANDLING
    // ============================================

    $(document).on('click', '.paginate_button', function(e) {
        if (!$(this).hasClass('disabled')) {
            showLoader('Loading Page...', 'Please wait while we load the next page');
        }
    });

    // ============================================
    // AJAX ERROR HANDLING - Only console logs
    // ============================================

    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        hideLoader();
        hideFilterLoader();
        console.error('AJAX Error:', {
            status: jqxhr.status,
            statusText: jqxhr.statusText,
            responseText: jqxhr.responseText,
            thrownError: thrownError
        });
    });

    // ============================================
    // WINDOW RESIZE HANDLING
    // ============================================

    let resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (oTable) {
                oTable.columns.adjust();
            }
        }, 250);
    });

    console.log('MSME List Page Loaded Successfully');
</script>
@endsection