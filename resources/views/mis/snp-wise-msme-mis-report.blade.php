@extends('components.admin.content-layout')
@section('card-content')
<div class="card-body">
    <form id="search_form" autocomplete="off">
        <div class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="text"
                    class="form-control"
                    name="from_date"
                    id="from_date"
                    placeholder="YYYY-MM-DD">
            </div>

            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="text"
                    class="form-control"
                    name="to_date"
                    id="to_date"
                    placeholder="YYYY-MM-DD">
            </div>

            <div class="col-md-2">
                <button type="button"
                    class="btn btn-primary w-100"
                    id="filter_btn">
                    <i class="fa fa-search"></i> Search
                </button>
            </div>

            <div class="col-md-2">
                <button type="button"
                    class="btn btn-secondary w-100"
                    id="reset_btn1">
                    <i class="fa fa-refresh"></i> Reset
                </button>
            </div>

        </div>
    </form>
</div>

<div class="card-body table-loading-container">
<!-- Modern Table Loader Overlay -->
<div id="table-loader">
   <div class="text-center">
      <div class="modern-spinner mx-auto"></div>
      <div class="loading-text">Loading SNP WISE MSME records...</div>
   </div>
</div>
<div class="card-body">
   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover"
         id="dataTable"
         width="100%"
         cellspacing="0">
         <thead>
            <tr>
               <th>S.No.</th>
               <th>SNP ID</th>
               <th>SNP Organisation Name</th>
               <th>Team ID</th>
               <th>Mobile</th>
               <th>Email</th>
               <th>Udyam No</th>
               <th>Enterprise Name</th>
               <th>Entrepreneur Name</th>
               <th>Enterprise Type</th>
               <th>Major Activity</th>
               <th>Product Category</th>
               <th>State</th>
               <th>Gender</th>
               <th>Registration Date</th>
              
            </tr>
         </thead>
         <tbody></tbody>
      </table>
   </div>
</div>
@endsection
@section('js')
@include('mis.common-css')
<style>
    #dataTable_wrapper div.dt-buttons {
        margin-left: 0;
    }
</style>
<script>

let table = null;

$(document).ready(function () {

    $('#from_date, #to_date').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    loadData();

    $('#filter_btn').on('click', function () {

        if (table) {
            table.ajax.reload();
        }
    });

    $('#reset_btn1').on('click', function () {

        $('#from_date').val('');
        $('#to_date').val('');

        if (table) {
            table.ajax.reload();
        }
    });

});

function loadData()
{
    table = $('#dataTable').DataTable({

        processing: true,
        serverSide: true, // Server computes only the requested page, so increasing page length fetches instead of re-rendering an already-loaded set
        destroy: true,
        responsive: false,
        scrollX: true,
        autoWidth: false,
        deferRender: true,
        searchDelay: 800, // Debounce search input (800ms) to avoid triggering an AJAX call on every keystroke

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
        ],

        ajax: {

            url: "{{ url('snp-wise-msme-mis-report/datalist') }}",

            type: "GET",

            data: function (d) {

                d.from_date = $('#from_date').val();
                d.to_date = $('#to_date').val();
            },

            dataSrc: function (json) {
                return json.data || [];
            },

            error: function () {
                $('#table-loader').css('display', 'none');
            }
        },

        columns: [

            {
                data: 'sn',
                orderable: false,
                searchable: false,
                className: "text-center"
            },

            {
                data: 'snp_id',
                render: function (data) {
                    return data
                        ? `<span class="badge bg-primary">${data}</span>`
                        : `<span class="badge bg-secondary">-</span>`;
                }
            },

            {
                data: 'organization_name',
                defaultContent: '-'
            },

            {
                data: 'msme_id',
                render: function (data) {
                    return data
                        ? `<span class="badge bg-success">${data}</span>`
                        : `<span class="badge bg-secondary">-</span>`;
                }
            },

            {
                data: 'mobile',
                defaultContent: '-'
            },

            {
                data: 'email',
                defaultContent: '-'
            },

            {
                data: 'udyam_no',
                defaultContent: '-'
            },

            {
                data: 'enterprise_name',
                defaultContent: '-'
            },

            {
                data: 'entrepreneur_name',
                defaultContent: '-'
            },

            {
                data: 'enterprise_type',
                defaultContent: '-'
            },

            {
                data: 'major_activity',
                defaultContent: '-'
            },

            {
                data: 'product_category',
                defaultContent: '-'
            },

            {
                data: 'state_name',
                defaultContent: '-'
            },

            {
                data: 'gender',
                defaultContent: '-'
            },

            {
                data: 'created_at',
                defaultContent: '-'
            }

        ]
    });

    new $.fn.dataTable.Buttons(table, {
        buttons: [
            {
                extend: 'excel',
                text: 'Excel',
                className: 'buttons-excel'
            },
            {
                extend: 'pdf',
                text: 'PDF',
                className: 'buttons-pdf'
            }
        ]
    });

    table.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
}

// Show/hide custom loader based on DataTable processing status
$('#dataTable').on('processing.dt', function(e, settings, processing) {
    if (processing) {
        $('#table-loader').css('display', 'flex');
    } else {
        $('#table-loader').css('display', 'none');
    }
});

</script>
@endsection