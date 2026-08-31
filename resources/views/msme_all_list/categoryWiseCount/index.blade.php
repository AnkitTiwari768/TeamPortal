@extends('components.admin.content-layout')

@section('card-content')

@include('msme_all_list.categoryWiseCount.loader_css')

<div class="card-body">
    <form id="category_wise_count_search_form" autocomplete="off">
        <div class="category-wise-count-filter-card">
            <div class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6 col-12">
                    <div class="select-box category-wise-count-select-box">
                        <label class="form-label"><i class="fas fa-tags"></i> Category</label>
                        {!! Form::select('category_id[]', remove_select_dynamic_common_list($lists?->categories), '', ['class' => 'form-select select2 filter_btn', 'id' => 'category_id', 'multiple' => 'multiple']) !!}
                    </div>
                </div>
                <div class="col-12 d-flex align-items-center justify-content-end">
                    <a href="javascript:void(0)" class="clear-action" id="category_wise_count_reset_btn" style="display: none;">
                        <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}" alt="clear">
                        Clear all
                    </a>
                    <span class="filter-loader" id="categoryWiseCountFilterLoader">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="categoryWiseCountDataTable" width="100%" cellspacing="0">
        <thead>
            <tr>
                <th>{{ __('message.sn') }}</th>
                <th>Category</th>
                <th>Mapped MSME Count</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows will be populated by DataTable -->
        </tbody>
    </table>
</div>

@endsection

@section('js')

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    window.csrfToken = "{{ csrf_token() }}";
    var categoryWiseCountFilterTimeout = null;
    var categoryWiseCountIsLoading = false;

    // ============================================
    // LOADER FUNCTIONS
    // ============================================

    function categoryWiseCountShowLoader(text = 'Loading...', subtext = 'Please wait while we process your request') {
        if (!categoryWiseCountIsLoading) {
            categoryWiseCountIsLoading = true;
            $('#categoryWiseCountGlobalLoader .loader-text').text(text);
            $('#categoryWiseCountGlobalLoader .loader-subtext').text(subtext);
            $('#categoryWiseCountGlobalLoader').addClass('active');
            $('body').addClass('loading-disabled');
        }
    }

    function categoryWiseCountHideLoader() {
        categoryWiseCountIsLoading = false;
        $('#categoryWiseCountGlobalLoader').removeClass('active');
        $('body').removeClass('loading-disabled');
    }

    function categoryWiseCountShowFilterLoader() {
        $('#categoryWiseCountFilterLoader').addClass('active');
    }

    function categoryWiseCountHideFilterLoader() {
        $('#categoryWiseCountFilterLoader').removeClass('active');
    }

    // ============================================
    // SERIAL NUMBER FUNCTION
    // ============================================

    function categoryWiseCountSerialNumber(tableId, rowIndex) {
        var table = $(tableId).DataTable();
        var pageInfo = table.page.info();
        return pageInfo.start + rowIndex + 1;
    }

    // ============================================
    // SELECT2 INITIALIZATION
    // ============================================

    function categoryWiseCountInitSelect2(element, reloadOnChange = true) {
        if (!element.length) return;

        element.select2({
            placeholder: "Select",
            allowClear: true,
            closeOnSelect: false,
            width: '100%'
        }).on('change', function(e) {
            if (reloadOnChange) {
                categoryWiseCountHandleFilterChange();
            }
        });
    }

    // ============================================
    // CURRENT FILTER VALUES
    // ============================================

    function categoryWiseCountGetFilters() {
        return {
            category_id: $('#category_id').val()
        };
    }

    // ============================================
    // FILTER HANDLING
    // ============================================

    function categoryWiseCountHandleFilterChange() {
        clearTimeout(categoryWiseCountFilterTimeout);
        categoryWiseCountShowFilterLoader();
        categoryWiseCountShowLoader('Applying Filters...', 'Please wait while we filter the data');

        categoryWiseCountFilterTimeout = setTimeout(function() {
            try {
                if (categoryWiseCountTable) {
                    categoryWiseCountTable.ajax.reload(function() {
                        categoryWiseCountHideFilterLoader();
                        categoryWiseCountHideLoader();
                        $('#category_wise_count_reset_btn').show();
                    });
                }
            } catch (error) {
                categoryWiseCountHideFilterLoader();
                categoryWiseCountHideLoader();
                console.error('Filter Error:', error);
            }
        }, 500);
    }

    // ============================================
    // DATATABLE INITIALIZATION
    // ============================================

    $.fn.dataTable.ext.classes.sProcessing = 'dataTables_processing';

    var categoryWiseCountTable = dataTableInit({
        id: "#categoryWiseCountDataTable",
        showExcelExport: false,
        showCustomExportOption: false,
        order: {
            column: 1,
            direction: "asc"
        },
        url: "{{ url('category-wise-count/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function (data, type, full, meta) {
                    return categoryWiseCountSerialNumber("#categoryWiseCountDataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    return row.category || "N/A";
                }
            },
            {
                "orderable": true,
                "render": function (data, type, row) {
                    if (type === 'sort' || type === 'filter') {
                        return row.mapped_msme_count || 0;
                    }
                    return '<span class="category-wise-count-badge">' + (row.mapped_msme_count || 0) + '</span>';
                }
            }
        ],
        filters: ["category_id"],

        preDrawCallback: function(settings) {
            categoryWiseCountShowLoader('Loading Data...', 'Please wait while we fetch the data');
            return true;
        },

        drawCallback: function(settings) {
            categoryWiseCountHideLoader();
            categoryWiseCountHideFilterLoader();
            categoryWiseCountCheckFilterValues();

            $('#categoryWiseCountDataTable_wrapper .dataTables_empty').text('No records found');
        },

        initComplete: function(settings, json) {
            categoryWiseCountHideLoader();
            categoryWiseCountHideFilterLoader();
            categoryWiseCountCheckFilterValues();

            $('.dataTables_length select').on('change', function() {
                categoryWiseCountShowLoader('Loading Data...', 'Please wait while we update the page');
            });
        },

        error: function(settings, techNote, message) {
            categoryWiseCountHideLoader();
            categoryWiseCountHideFilterLoader();
            console.error('DataTable Error:', message);
        }
    });

    // ============================================
    // AJAX ERROR HANDLING
    // ============================================
    $(document).on('error.dt', '#categoryWiseCountDataTable', function(e, settings, techNote, message) {
        categoryWiseCountHideLoader();
        categoryWiseCountHideFilterLoader();
        console.error('Category Wise Count AJAX Error:', message);
        $('#categoryWiseCountDataTable_wrapper .dataTables_empty')
            .text('Something went wrong while loading data. Please try again.');
    });

    function categoryWiseCountCheckFilterValues() {
        let hasValue = false;
        $('#category_wise_count_search_form .filter_btn').each(function() {
            let val = $(this).val();
            if (val && val.length > 0 && !(Array.isArray(val) && val.length === 0)) {
                hasValue = true;
                return false;
            }
        });

        if (hasValue) {
            $('#category_wise_count_reset_btn').show();
        } else {
            $('#category_wise_count_reset_btn').hide();
        }
    }

    $(document).ready(function() {
        categoryWiseCountShowLoader('Loading Page...', 'Please wait while the page loads');

        categoryWiseCountInitSelect2($('#category_id'), true);

        $(document).on('draw.dt', '#categoryWiseCountDataTable', function() {
            categoryWiseCountHideLoader();
            categoryWiseCountHideFilterLoader();
        });

        $(document).on('change', '.dataTables_length select', function() {
            categoryWiseCountShowLoader('Loading Data...', 'Please wait while we update the page');
        });

        setTimeout(function() {
            if ($('#categoryWiseCountGlobalLoader').hasClass('active')) {
                categoryWiseCountHideLoader();
            }
        }, 5000);
    });

    $("#category_wise_count_reset_btn").on("click", function() {
        categoryWiseCountShowLoader('Clearing Filters...', 'Please wait while we reset all filters');
        categoryWiseCountShowFilterLoader();

        try {
            $("#category_id").val('').trigger('change');

            if (categoryWiseCountTable) {
                categoryWiseCountTable.ajax.reload(function() {
                    categoryWiseCountHideFilterLoader();
                    categoryWiseCountHideLoader();
                    $('#category_wise_count_reset_btn').hide();
                });
            }
        } catch (error) {
            categoryWiseCountHideFilterLoader();
            categoryWiseCountHideLoader();
            console.error('Clear Error:', error);
        }
    });

    $(document).on('keyup', '#categoryWiseCountDataTable_filter input', function(e) {
        clearTimeout(categoryWiseCountFilterTimeout);
        categoryWiseCountShowFilterLoader();
        categoryWiseCountShowLoader('Searching...', 'Please wait while we search the data');

        categoryWiseCountFilterTimeout = setTimeout(function() {
            try {
                if (categoryWiseCountTable) {
                    categoryWiseCountTable.ajax.reload(function() {
                        categoryWiseCountHideFilterLoader();
                        categoryWiseCountHideLoader();
                    });
                }
            } catch (error) {
                categoryWiseCountHideFilterLoader();
                categoryWiseCountHideLoader();
                console.error('Search Error:', error);
            }
        }, 500);
    });

    $(document).on('click', '.paginate_button', function(e) {
        if (!$(this).hasClass('disabled')) {
            categoryWiseCountShowLoader('Loading Page...', 'Please wait while we load the next page');
        }
    });

    let categoryWiseCountResizeTimer;
    $(window).on('resize', function() {
        clearTimeout(categoryWiseCountResizeTimer);
        categoryWiseCountResizeTimer = setTimeout(function() {
            if (categoryWiseCountTable) {
                categoryWiseCountTable.columns.adjust();
            }
        }, 250);
    });
</script>
@endsection
