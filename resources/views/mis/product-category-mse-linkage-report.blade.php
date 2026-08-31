@extends('components.admin.content-layout')
@section('card-content')
@include('mis.common-css')

<style>
    .pcmlr-loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 99999;
    }

    .pcmlr-loader-overlay.active {
        display: flex;
    }

    .pcmlr-loader-container {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        text-align: center;
        min-width: 200px;
    }

    .pcmlr-loader-spinner {
        border: 6px solid #f3f3f3;
        border-radius: 50%;
        border-top: 6px solid #3498db;
        border-right: 6px solid #e74c3c;
        border-bottom: 6px solid #2ecc71;
        border-left: 6px solid #f39c12;
        width: 50px;
        height: 50px;
        margin: 0 auto 15px auto;
        animation: pcmlr-spin 1s linear infinite;
    }

    @keyframes pcmlr-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .pcmlr-loader-text {
        color: #333;
        font-size: 16px;
        font-weight: 500;
        margin: 0;
    }

    .pcmlr-loader-subtext {
        color: #666;
        font-size: 13px;
        margin-top: 5px;
    }

    .pcmlr-loading-disabled {
        pointer-events: none;
        opacity: 0.6;
    }
</style>

<div class="pcmlr-loader-overlay" id="pcmlrGlobalLoader">
    <div class="pcmlr-loader-container">
        <div class="pcmlr-loader-spinner"></div>
        <p class="pcmlr-loader-text" id="pcmlrLoaderText">Loading...</p>
        <p class="pcmlr-loader-subtext" id="pcmlrLoaderSubtext">Please wait while we process your request</p>
    </div>
</div>

<div class="card-body">
    <form id="pcmlr_filter_form" autocomplete="off">
        <div class="filter-bar d-flex align-items-left flex-column gap-3 py-2">
            <div class="row">
                <div class="col-lg-3 col-2 mb-3">
                    <div class="select-box">
                        <label class="form-label">From date</label>
                        <input type="text" class="form-control custom-filter pcmlr_filter_btn" name="from_date" id="from_date" placeholder="From Date" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-3 col-2 mb-3">
                    <div class="select-box">
                        <label class="form-label">To date</label>
                        <input type="text" class="form-control custom-filter pcmlr_filter_btn" name="to_date" id="to_date" placeholder="To Date" autocomplete="off">
                    </div>
                </div>
                <div class="col-lg-4 col-2 mb-3">
                    <div class="select-box">
                        <label class="form-label">Product Category</label>
                        {!! Form::select('product_category_id[]', $productCategories->pluck('name', 'id'), null, ['class' => 'form-select select2 pcmlr_filter_btn', 'id' => 'product_category_id', 'multiple' => 'multiple']) !!}
                    </div>
                </div>
                <div class="col-lg-2 col-2 mb-3 d-flex align-items-center">
                    <button type="button" class="btn reset-btn" id="pcmlr_reset_btn">Reset</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="card-body">
    <div class="d-flex justify-content-end mb-3">
        <button type="button" class="btn btn-success" id="pcmlr_export_excel">
            <i class="fa-solid fa-file-excel me-2"></i> Download Excel
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover" id="pcmlrDataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Product Category</th>
                    <th>Onboarded MSE</th>
                    <th>Linked to SNP</th>
                    <th>Linked to BNP</th>
                    <th>Linked to LSP</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {

    var pcmlrFilterTimeout = null;
    var pcmlrIsLoading = false;

    function pcmlrShowLoader(text, subtext) {
        if (!pcmlrIsLoading) {
            pcmlrIsLoading = true;
            $('#pcmlrLoaderText').text(text || 'Loading...');
            $('#pcmlrLoaderSubtext').text(subtext || 'Please wait while we process your request');
            $('#pcmlrGlobalLoader').addClass('active');
            $('body').addClass('pcmlr-loading-disabled');
        }
    }

    function pcmlrHideLoader() {
        pcmlrIsLoading = false;
        $('#pcmlrGlobalLoader').removeClass('active');
        $('body').removeClass('pcmlr-loading-disabled');
    }

    pcmlrShowLoader('Loading Page...', 'Please wait while the report loads');

    $('#product_category_id').select2({
        placeholder: 'All Categories',
        allowClear: true,
        width: '100%'
    }).on('change', reloadTable);

    $('#from_date').datepicker({
        dateFormat: 'dd-mm-yy',
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function (selected) {
            $('#to_date').datepicker('option', 'minDate', selected);
            reloadTable();
        }
    });

    $('#to_date').datepicker({
        dateFormat: 'dd-mm-yy',
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function (selected) {
            $('#from_date').datepicker('option', 'maxDate', selected);
            reloadTable();
        }
    });

    var pcmlrTable = dataTableInit({
        id: '#pcmlrDataTable',
        url: "{{ url('product-category-mse-linkage-report/datalist') }}",
        order: { column: 1, direction: 'asc' },
        filters: ['from_date', 'to_date', 'product_category_id'],
        columns: [
            {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'product_category', defaultContent: '-' },
            { data: 'onboarded_mse', defaultContent: '0' },
            { data: 'linked_snp', defaultContent: '0' },
            { data: 'linked_bnp', defaultContent: '0' },
            { data: 'linked_lsp', defaultContent: '0' }
        ]
    });

    // "processing.dt" fires true the instant an ajax request starts (initial
    // load, filter change, sorting, pagination, page-length change) - shows
    // the loader as early as possible. It also fires false on ajax error
    // (kept here as a safety net so the loader can't get stuck if a request
    // fails before ever reaching a draw).
    $('#pcmlrDataTable').on('processing.dt', function (e, settings, processing) {
        if (processing) {
            pcmlrShowLoader('Loading Data...', 'Please wait while we fetch the report');
        } else {
            pcmlrHideLoader();
        }
    });

    // "draw.dt" fires only after DataTables has finished inserting the new
    // rows into the DOM - i.e. once the data is actually rendered - so this
    // is the authoritative "hide the loader" signal on the success path.
    $('#pcmlrDataTable').on('draw.dt', function () {
        pcmlrHideLoader();
    });

    function reloadTable() {
        clearTimeout(pcmlrFilterTimeout);
        pcmlrShowLoader('Applying Filters...', 'Please wait while we filter the data');

        pcmlrFilterTimeout = setTimeout(function () {
            pcmlrTable.ajax.reload();
        }, 500);
    }

    $('#pcmlr_reset_btn').on('click', function () {
        pcmlrShowLoader('Clearing Filters...', 'Please wait while we reset all filters');

        $('#from_date').val('');
        $('#to_date').val('');
        $('#from_date').datepicker('option', 'maxDate', 0);
        $('#to_date').datepicker('option', 'minDate', null);
        $('#product_category_id').val(null).trigger('change');
    });

    $(document).on('change', '.dataTables_length select', function () {
        pcmlrShowLoader('Loading Data...', 'Please wait while we update the page');
    });

    $(document).on('click', '#pcmlrDataTable_wrapper .paginate_button', function () {
        if (!$(this).hasClass('disabled')) {
            pcmlrShowLoader('Loading Page...', 'Please wait while we load the next page');
        }
    });

    $('#pcmlr_export_excel').on('click', function () {
        pcmlrShowLoader('Preparing Excel...', 'Please wait while we generate the file');

        var params = [];
        var fromDate = $('#from_date').val();
        var toDate = $('#to_date').val();
        var categories = $('#product_category_id').val() || [];

        if (fromDate) {
            params.push('filters[from_date]=' + encodeURIComponent(fromDate));
        }
        if (toDate) {
            params.push('filters[to_date]=' + encodeURIComponent(toDate));
        }
        $.each(categories, function (i, id) {
            params.push('filters[product_category_id][]=' + encodeURIComponent(id));
        });

        var url = "{{ url('product-category-mse-linkage-report/export-excel') }}";
        window.location.href = url + (params.length ? ('?' + params.join('&')) : '');

        setTimeout(pcmlrHideLoader, 3000);
    });

    // Safety net so the overlay never gets stuck on if a draw/processing
    // event is missed (e.g. a network error firing before "processing.dt").
    setTimeout(function () {
        if ($('#pcmlrGlobalLoader').hasClass('active')) {
            pcmlrHideLoader();
        }
    }, 8000);
});
</script>
@endsection
