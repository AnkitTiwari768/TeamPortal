@extends('components.admin.content-layout')
@section('card-content')

<style>
    .dt-buttons {
        float: none !important;
        display: inline-flex !important;
        align-items: center;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        gap: 5px;
        margin-bottom: 0 !important;
        width: max-content !important;
    }
    .dt-buttons button, .dt-buttons a {
        display: inline-block !important;
        white-space: nowrap !important;
    }
    .dt-buttons .d-none {
        display: none !important;
    }
    .dataTables_length {
        float: none !important;
        display: inline-block !important;
        margin-right: 10px;
        margin-bottom: 0 !important;
    }
    .dataTables_length label {
        display: inline-flex !important;
        align-items: center;
        gap: 5px;
        margin-bottom: 0 !important;
        white-space: nowrap !important;
    }
    #dataTable_wrapper .col-md-6:first-child {
        display: flex !important;
        align-items: center !important;
        flex-wrap: nowrap !important;
        justify-content: flex-start !important;
        overflow: visible !important;
    }
    #customFilter {
        flex-shrink: 0 !important;
        position: relative;
        z-index: 10;
    }
    #customFilter button {
        cursor: pointer !important;
    }
</style>

<div class="card-body">

    <div id="customFilter" class="d-none align-items-center gap-2">
        <label class="mb-0 text-nowrap"><b>Type:</b></label>
        <select id="bulkFilter" class="form-select form-select-sm" style="width:120px;">
            <option value="">All</option>
            <option value="0">Individual</option>
            <option value="1">Bulk</option>
        </select>

        <button id="filterBtn" class="btn btn-sm btn-primary">Filter</button>
        <button id="resetBtn" class="btn btn-sm btn-secondary">Reset</button>
    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dataTable" width="100%">
            <thead>
                <tr>
                    <th>{{ __('message.sn') }}</th>
                    <th>{{ __('message.owner_name') }}</th>
                    <th>{{ __('message.store_name') }}</th>
                    <th>{{ __('message.email') }}</th>
                    <th>{{ __('message.mobile') }}</th>
                    <th>{{ __('message.type_of_business_pmv_users') }}</th>
                    <th>Type</th>
                    <th>Is Bulk</th>
                    <th class="actions">{{ __('message.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>

</div>

@section('js')
<script>
$(document).ready(function() {

  let table = dataTableInit({
    id: "#dataTable",
    showExcelExport: true,
    order: {
      column: 1,
      direction: "desc"
    },
    url: "{{ url('pmv-users/datalist') }}",
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
            return row.owner_name;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.store_name;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.email;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.mobile;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.category_name ? row.category_name : row.type_of_business;
        }
      },

      {
        "render": function(data, type, row) {
          if (row.type == 1) {
            return '<span class="badge bg-primary">SNP</span>';
          } else if (row.type == 2) {
            return '<span class="badge bg-info text-dark">IA</span>';
          } else if (row.type == 3) {
            return '<span class="badge bg-secondary">Individual</span>';
          } else {
            return '-';
          }
        }
      },

      {
        "render": function(data, type, row) {
          if (row.is_bulk == 1) {
            return '<span class="badge bg-success">Bulk</span>';
          } else {
            return '<span class="badge bg-warning text-dark">Individual</span>';
          }
        }
      },

      { 
        "orderable": false,
        "render": function (data, type, row) {
          var pview = buttonView("{{ url('view-pm-user') }}", row.id); 
          return createActionButtons([pview]);
        }
      }
    ]
  });

  // ✅ Fix alignment: Force all elements onto a single line
  setTimeout(function() {
    let $container = $('#dataTable_wrapper .col-md-6:eq(0)');
    if ($container.length) {
        $container.addClass('d-flex align-items-center flex-nowrap gap-4');
        $('#customFilter').insertAfter('.dt-buttons').removeClass('d-none').addClass('d-flex').show();
    }
  }, 500);

   // ✅ FORCE PARAM INJECTION
    $('#dataTable').on('preXhr.dt', function (e, settings, data) {
        data.is_bulk = $('#bulkFilter').val();
    });

  // ✅ FILTER BUTTON
  $(document).on('click', '#filterBtn', function() {
      oTable.ajax.reload();
  });

  // ✅ RESET BUTTON
  $(document).on('click', '#resetBtn', function() {
      $('#bulkFilter').val('');
      oTable.ajax.reload();
  });

});
</script>
@endsection
@endsection