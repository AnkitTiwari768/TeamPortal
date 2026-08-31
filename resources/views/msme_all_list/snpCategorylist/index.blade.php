@extends('components.admin.content-layout')

@section('card-content')

@include('msme_all_list.snpCategorylist.loader_css')

<div class="card-body">
    <form id="snp_category_list_search_form" autocomplete="off">
        <div class="snp-category-list-filter-card">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="select-box snp-category-list-select-box">
                        <label class="form-label"><i class="fas fa-hashtag"></i> NP Team ID</label>
                        <input type="text" class="form-control filter_btn" id="np_team_id" name="np_team_id" placeholder="Search NP Team ID" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="select-box snp-category-list-select-box">
                        <label class="form-label"><i class="fas fa-user-tag"></i> Role</label>
                        {!! Form::select('role', array('' => 'All') + $lists->roles, '', ['class' => 'form-select select2 filter_btn', 'id' => 'role']) !!}
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="select-box snp-category-list-select-box">
                        <label class="form-label"><i class="fas fa-tags"></i> Category</label>
                        {!! Form::select('category', array('' => 'All') + $lists->categories, '', ['class' => 'form-select select2 filter_btn', 'id' => 'category']) !!}
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="select-box snp-category-list-select-box">
                        <label class="form-label"><i class="fas fa-exchange-alt"></i> Transaction Type</label>
                        {!! Form::select('transaction_type', array('' => 'All') + $lists->transaction_types, '', ['class' => 'form-select select2 filter_btn', 'id' => 'transaction_type']) !!}
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center justify-content-end">
                    <a href="javascript:void(0)" class="clear-action" id="snp_category_list_reset_btn" style="display: none;">
                        <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}" alt="clear">
                        Clear all
                    </a>
                    <span class="filter-loader" id="snpCategoryListFilterLoader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-3 snp-category-list-export-actions">
    <button type="button" class="snp-category-list-export-btn snp-category-list-export-btn--excel" id="snp_category_list_export_excel">
        <i class="fas fa-file-excel"></i> Download Excel
    </button>
    <button type="button" class="snp-category-list-export-btn snp-category-list-export-btn--pdf" id="snp_category_list_export_pdf">
        <i class="fas fa-file-pdf"></i> Download PDF
    </button>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="snpCategoryListDataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>{{ __('message.sn') }}</th>
                <th>NP Team ID</th>
                <th>Organization Name</th>
                <th>Role</th>
                <th>Category</th>
                <th>Open MSME Count</th>
                <th>Transaction Type</th>
                <th>ONDC Domain Mapping</th>
                <th>Serviceability</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows will be populated by DataTable -->
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" style="text-align: right;">Total</th>
                <th id="snpCategoryListOpenMsmeTotal">0</th>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
</div>

<div class="modal fade" id="snpCategoryListDetailsModal" tabindex="-1" aria-labelledby="snpCategoryListDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content snp-category-list-modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="snpCategoryListDetailsModalLabel">
                    <i class="fas fa-store"></i> SNP Category Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="snp-category-list-detail-grid">
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-hashtag"></i> NP Team ID</span>
                        <span class="snp-category-list-detail-value" id="scld_np_team_id">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-building"></i> Organization Name</span>
                        <span class="snp-category-list-detail-value" id="scld_organization_name">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-user-tag"></i> Role</span>
                        <span class="snp-category-list-detail-value" id="scld_role_name">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-tags"></i> Category</span>
                        <span class="snp-category-list-detail-value" id="scld_category">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-store"></i> Open MSME Count</span>
                        <span class="snp-category-list-detail-value" id="scld_open_msme_count">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-exchange-alt"></i> Transaction Type</span>
                        <span class="snp-category-list-detail-value" id="scld_transaction_type">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-project-diagram"></i> ONDC Domain Mapping</span>
                        <span class="snp-category-list-detail-value" id="scld_ondc_domain_mapping">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-map-marker-alt"></i> Serviceability</span>
                        <span class="snp-category-list-detail-value" id="scld_serviceability">-</span>
                    </div>
                    <div class="snp-category-list-detail-item">
                        <span class="snp-category-list-detail-label"><i class="fas fa-circle-check"></i> Status</span>
                        <span class="snp-category-list-detail-value" id="scld_status_name">-</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.csrfToken = "{{ csrf_token() }}";
    var snpCategoryListFilterTimeout = null;
    var snpCategoryListIsLoading = false;

    // ============================================
    // LOADER FUNCTIONS
    // ============================================

    function snpCategoryListShowLoader(text = 'Loading...', subtext = 'Please wait while we process your request') {
        if (!snpCategoryListIsLoading) {
            snpCategoryListIsLoading = true;
            $('#snpCategoryListGlobalLoader .loader-text').text(text);
            $('#snpCategoryListGlobalLoader .loader-subtext').text(subtext);
            $('#snpCategoryListGlobalLoader').addClass('active');
            $('body').addClass('loading-disabled');
        }
    }

    function snpCategoryListHideLoader() {
        snpCategoryListIsLoading = false;
        $('#snpCategoryListGlobalLoader').removeClass('active');
        $('body').removeClass('loading-disabled');
    }

    function snpCategoryListShowFilterLoader() {
        $('#snpCategoryListFilterLoader').addClass('active');
    }

    function snpCategoryListHideFilterLoader() {
        $('#snpCategoryListFilterLoader').removeClass('active');
    }

    // ============================================
    // SERIAL NUMBER FUNCTION
    // ============================================

    function snpCategoryListSerialNumber(tableId, rowIndex) {
        var table = $(tableId).DataTable();
        var pageInfo = table.page.info();
        return pageInfo.start + rowIndex + 1;
    }

    // ============================================
    // ROLE BADGE RENDERING
    // ============================================

    function snpCategoryListRoleBadge(roleCode, roleName) {
        if (!roleCode) {
            return roleName || 'N/A';
        }

        var cssClass = 'snp-category-list-role-badge--' + roleCode.toLowerCase();
        return '<span class="snp-category-list-role-badge ' + cssClass + '">' + roleCode + '</span>';
    }

    // ============================================
    // VIEW DETAILS MODAL
    // ============================================

    var snpCategoryListDetailsModal = null;

    function snpCategoryListShowDetailsModal(row) {
        if (!row) return;

        $('#scld_np_team_id').text(row.np_team_id || 'N/A');
        $('#scld_organization_name').text(row.organization_name || 'N/A');
        $('#scld_role_name').html(snpCategoryListRoleBadge(row.role_code, row.role_name) || 'N/A');
        $('#scld_category').text(row.category || 'N/A');
        $('#scld_open_msme_count').text(row.open_msme_count || 0);
        $('#scld_transaction_type').text(row.transaction_type || 'N/A');
        $('#scld_ondc_domain_mapping').text(row.ondc_domain_mapping || 'N/A');
        $('#scld_serviceability').text(row.serviceability || 'N/A');
        $('#scld_status_name').text(row.status_name || 'N/A');

        if (!snpCategoryListDetailsModal) {
            snpCategoryListDetailsModal = new bootstrap.Modal(document.getElementById('snpCategoryListDetailsModal'));
        }
        snpCategoryListDetailsModal.show();
    }

    // ============================================
    // OPEN MSME COUNT - FILTERED TOTAL (FOOTER)
    // ============================================
    // The table is server-side paginated, so the sum shown here comes from
    // the server (sum of open_msme_count across all rows matching the
    // current filters/search - not just the current page) via the raw ajax
    // response's `open_msme_total`, captured before dataTableInit's dataSrc
    // callback strips it down to just the row array.
    $(document).on('xhr.dt', '#snpCategoryListDataTable', function(e, settings, json) {
        var total = (json && json.data && typeof json.data.open_msme_total !== 'undefined')
            ? json.data.open_msme_total
            : 0;
        $('#snpCategoryListOpenMsmeTotal').text(total);
    });

    $(document).on('click', '#snpCategoryListDataTable .snp-category-list-view-btn', function() {
        var tr = $(this).closest('tr');
        var rowData = snpCategoryListTable.row(tr).data();
        snpCategoryListShowDetailsModal(rowData);
    });

    // ============================================
    // SELECT2 INITIALIZATION
    // ============================================

    function snpCategoryListInitSelect2(element, reloadOnChange = true) {
        if (!element.length) return;

        element.select2({
            placeholder: "Select",
            allowClear: true,
            closeOnSelect: true,
            width: '100%'
        }).on('change', function(e) {
            if (reloadOnChange) {
                snpCategoryListHandleFilterChange();
            }
        });
    }

    // ============================================
    // CURRENT FILTER VALUES
    // ============================================

    function snpCategoryListGetFilters() {
        return {
            np_team_id: $('#np_team_id').val(),
            role: $('#role').val(),
            category: $('#category').val(),
            transaction_type: $('#transaction_type').val()
        };
    }

    // ============================================
    // EXPORT (Excel / PDF) - reuses the same filters as the on-screen list
    // ============================================

    function snpCategoryListBuildExportUrl(baseUrl) {
        var filters = snpCategoryListGetFilters();
        var params = [];

        $.each(filters, function(key, val) {
            if (val === null || val === undefined || val === '') {
                return;
            }
            params.push('filters[' + key + ']=' + encodeURIComponent(val));
        });

        return baseUrl + (params.length ? ('?' + params.join('&')) : '');
    }

    $('#snp_category_list_export_excel').on('click', function() {
        window.location.href = snpCategoryListBuildExportUrl("{{ url('snp-category-list/export-excel') }}");
    });

    $('#snp_category_list_export_pdf').on('click', function() {
        window.location.href = snpCategoryListBuildExportUrl("{{ url('snp-category-list/export-pdf') }}");
    });

    // ============================================
    // FILTER HANDLING
    // ============================================

    function snpCategoryListHandleFilterChange() {
        clearTimeout(snpCategoryListFilterTimeout);
        snpCategoryListShowFilterLoader();
        snpCategoryListShowLoader('Applying Filters...', 'Please wait while we filter the data');

        snpCategoryListFilterTimeout = setTimeout(function() {
            try {
                if (snpCategoryListTable) {
                    snpCategoryListTable.ajax.reload(function() {
                        snpCategoryListHideFilterLoader();
                        snpCategoryListHideLoader();
                        $('#snp_category_list_reset_btn').show();
                    });
                }
            } catch (error) {
                snpCategoryListHideFilterLoader();
                snpCategoryListHideLoader();
                console.error('Filter Error:', error);
            }
        }, 500);
    }

    // ============================================
    // DATATABLE INITIALIZATION
    // ============================================

    $.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing';

    var snpCategoryListTable = dataTableInit({
        id: "#snpCategoryListDataTable",
        showExcelExport: false,
        showCustomExportOption: false,
        order: {
            column: 1,
            direction: "asc"
        },
        url: "{{ url('snp-category-list/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return snpCategoryListSerialNumber("#snpCategoryListDataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.np_team_id || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.organization_name || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    if (type === 'sort' || type === 'filter') {
                        return row.role_name || '';
                    }
                    return snpCategoryListRoleBadge(row.role_code, row.role_name);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.category || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.open_msme_count || 0;
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.transaction_type || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.ondc_domain_mapping || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.serviceability || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return row.status_name || "N/A";
                }
            },
            {
                "orderable": false,
                "render": function (data, type, row) {
                    return '<button type="button" class="snp-category-list-view-btn" title="View Details">' +
                        '<i class="fas fa-eye"></i>' +
                        '</button>';
                }
            }
        ],
        filters: ["np_team_id", "role", "category", "transaction_type"],

        preDrawCallback: function(settings) {
            snpCategoryListShowLoader('Loading Data...', 'Please wait while we fetch the data');
            return true;
        },

        drawCallback: function(settings) {
            snpCategoryListHideLoader();
            snpCategoryListHideFilterLoader();
            snpCategoryListCheckFilterValues();

            // dataTableInit() (assets/js/script.js) falls back to DataTables'
            // own default empty-state markup - relabel it here instead of
            // patching the shared helper.
            $('#snpCategoryListDataTable_wrapper .dataTables_empty').text('No records found');
        },

        initComplete: function(settings, json) {
            snpCategoryListHideLoader();
            snpCategoryListHideFilterLoader();
            snpCategoryListCheckFilterValues();

            $('.dataTables_length select').on('change', function() {
                snpCategoryListShowLoader('Loading Data...', 'Please wait while we update the page');
            });
        },

        error: function(settings, techNote, message) {
            snpCategoryListHideLoader();
            snpCategoryListHideFilterLoader();
            console.error('DataTable Error:', message);
        }
    });

    // ============================================
    // AJAX ERROR HANDLING
    // ============================================
    // DataTables dispatches this native event whenever the datalist ajax
    // call fails (network error, 500, timeout, etc.) - the "error" key
    // passed into dataTableInit() above is not wired by the shared helper,
    // so this is the reliable hook for surfacing failures to the user.
    $(document).on('error.dt', '#snpCategoryListDataTable', function(e, settings, techNote, message) {
        snpCategoryListHideLoader();
        snpCategoryListHideFilterLoader();
        console.error('SNP Category List AJAX Error:', message);
        $('#snpCategoryListDataTable_wrapper .dataTables_empty')
            .text('Something went wrong while loading data. Please try again.');
    });

    function snpCategoryListCheckFilterValues() {
        let hasValue = false;
        $('#snp_category_list_search_form .filter_btn').each(function() {
            let val = $(this).val();
            if (val && val.length > 0 && !(Array.isArray(val) && val.length === 0)) {
                hasValue = true;
                return false;
            }
        });

        if (hasValue) {
            $('#snp_category_list_reset_btn').show();
        } else {
            $('#snp_category_list_reset_btn').hide();
        }
    }

    $(document).ready(function() {
        snpCategoryListShowLoader('Loading Page...', 'Please wait while the page loads');

        snpCategoryListInitSelect2($('#role'), true);
        snpCategoryListInitSelect2($('#category'), true);
        snpCategoryListInitSelect2($('#transaction_type'), true);

        $('#np_team_id').on('keyup', function() {
            snpCategoryListHandleFilterChange();
        });

        $(document).on('draw.dt', '#snpCategoryListDataTable', function() {
            snpCategoryListHideLoader();
            snpCategoryListHideFilterLoader();
        });

        $(document).on('change', '.dataTables_length select', function() {
            snpCategoryListShowLoader('Loading Data...', 'Please wait while we update the page');
        });

        setTimeout(function() {
            if ($('#snpCategoryListGlobalLoader').hasClass('active')) {
                snpCategoryListHideLoader();
            }
        }, 5000);
    });

    $("#snp_category_list_reset_btn").on("click", function() {
        snpCategoryListShowLoader('Clearing Filters...', 'Please wait while we reset all filters');
        snpCategoryListShowFilterLoader();

        try {
            $("#np_team_id").val('');
            $("#role").val('').trigger('change');
            $("#category").val('').trigger('change');
            $("#transaction_type").val('').trigger('change');

            if (snpCategoryListTable) {
                snpCategoryListTable.ajax.reload(function() {
                    snpCategoryListHideFilterLoader();
                    snpCategoryListHideLoader();
                    $('#snp_category_list_reset_btn').hide();
                });
            }
        } catch (error) {
            snpCategoryListHideFilterLoader();
            snpCategoryListHideLoader();
            console.error('Clear Error:', error);
        }
    });

    $(document).on('keyup', '#snpCategoryListDataTable_filter input', function(e) {
        clearTimeout(snpCategoryListFilterTimeout);
        snpCategoryListShowFilterLoader();
        snpCategoryListShowLoader('Searching...', 'Please wait while we search the data');

        snpCategoryListFilterTimeout = setTimeout(function() {
            try {
                if (snpCategoryListTable) {
                    snpCategoryListTable.ajax.reload(function() {
                        snpCategoryListHideFilterLoader();
                        snpCategoryListHideLoader();
                    });
                }
            } catch (error) {
                snpCategoryListHideFilterLoader();
                snpCategoryListHideLoader();
                console.error('Search Error:', error);
            }
        }, 500);
    });

    $(document).on('click', '.paginate_button', function(e) {
        if (!$(this).hasClass('disabled')) {
            snpCategoryListShowLoader('Loading Page...', 'Please wait while we load the next page');
        }
    });

    let snpCategoryListResizeTimer;
    $(window).on('resize', function() {
        clearTimeout(snpCategoryListResizeTimer);
        snpCategoryListResizeTimer = setTimeout(function() {
            if (snpCategoryListTable) {
                snpCategoryListTable.columns.adjust();
            }
        }, 250);
    });
</script>
@endsection
