@extends('components.admin.content-layout')

@section('card-content')

  <div class="border-bottom card-body d-flex justify-content-between">
    <form id="search_form" autocomplete="off">
      <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
        <div class="select-box">
          <label class="form-label">From date</label>
          <input type="text" class="form-control filter_btn" name="from_date" id="from_date" placeholder="From Date">
        </div>

        <div class="select-box">
          <label class="form-label">To date</label>
          <input type="text" class="form-control filter_btn" name="to_date" id="to_date" placeholder="To Date">
        </div>
        <div class="select-box">
          <label class="form-label" style="display: block;">Status</label>
          {!! Form::select('review_status', status_filter(), '', ['class' => 'form-select select2 filter_btn', 'id' => 'review_status']) !!}
        </div>
        <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
          <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all
        </a>
      </div>
    </form>
  </div>

  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
        <thead>
          <tr>
            <!-- <th><input type="checkbox" id="selectAll"></th> -->
            <th>{{ __('message.sn') }}</th>
            <th>{{ __('Authorized Person Name') }}</th>
            <th>{{ __('SNP Id') }}</th>
            <th>{{ __('Organization Id') }}</th>
            <th>{{ __('Organization Name') }}</th>
            <th>{{ __('Email') }}</th>
            <th>{{ __('Contact No') }}</th>
            <th>Status</th>
            <th class="actions">{{ __('message.action') }}</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

@endsection

@section('js')
  <script>
    // dataTableInit({
    //   id: "#dataTable",
    //   showExcelExport: true,
    //   order: {
    //     column: 1,
    //     direction: "asc"
    //   },
     
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "desc"
            },
      url: "{{ url('mis-snp-registration-reports/datalist') }}", // ✅ Make sure this route returns getSnpList($status = 1)
      columns: [
        //  {
        //   "orderable": false,
        //   "className": "noExport",
        //   "render": function (data, type, row) {
        //     var checked = selectedRows[row.id] ? 'checked' : '';
        //     return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
        //   }
        // },
        {
          orderable: false,
          render: function (data, type, full, meta) {
            return serialNumber("#dataTable", meta.row);
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
           // console.log(row, 'rows data')
            return row.name ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            return row.snp_id ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            return row.organization_id ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            return row.organization_name ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            return row.email ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            return row.mobile ?? '-';
          }
        },
        {
          orderable: true,
          render: function (data, type, row) {
            let status = row.review_status ?? '';
            let label = '';

            if (status == 3) {
              label = `<span class="badge bg-success">Approved</span>`;
            } else if (status == 4) {
              label = `<span class="badge bg-danger">Rejected</span>`;
            } else if (status == 5) {
              label = `<span class="badge bg-warning text-dark">Reverted</span>`;
            } else {
              label = `<span class="badge bg-secondary">Pending</span>`;
            }

            return label;
          }
        },
        {
          orderable: false,
          render: function (data, type, row) {
            // let viewBtn = buttonView("{{ url('/mis-snp-registration-reports/snp-view-detail') }}", row.id);
            let viewBtn = buttonView("{{ url('mis-snp-registration-reports/snp-view-detail') }}", row.id);
            return createActionButtons([viewBtn]);
          }
        }
      ],
      filters: ["from_date", "to_date", "review_status"]
    });


$(document).on('change', '#selectAll', function () {
    const checked = $(this).is(':checked');
    $('#dataTable tbody .row-checkbox').prop('checked', checked).trigger('change');
});

var selectedRows = {};

$(document).on('change', '.row-checkbox', function () {
    const id = $(this).val();
    this.checked ? selectedRows[id] = true : delete selectedRows[id];
});

$('#dataTable').on('draw.dt', function () {
    $('.row-checkbox').each(function () {
        $(this).prop('checked', !!selectedRows[$(this).val()]);
    });
});

$(document).on('click', '.buttons-excel', function (e) {
    e.preventDefault();

    if (Object.keys(selectedRows).length === 0) {
        toastr.warning('Please select at least one row');
        return;
    }

    exportSelectedRowsToCSV();
});

function exportSelectedRowsToCSV() {

    const table = $('#dataTable').DataTable();
    const rows = [];

    table.rows({ search: 'applied' }).data().each(function (data) {

        if (selectedRows[data.id]) {

            let status = 'Pending';
            if (data.review_status == 3) status = 'Approved';
            if (data.review_status == 4) status = 'Rejected';
            if (data.review_status == 5) status = 'Reverted';

            rows.push([
                data.name,
                data.snp_id,
                data.organization_id,
                data.organization_name,
                data.email,
                data.mobile,
                status
            ]);
        }
    });

    const csv = [
        ["Authorized Person Name","SNP ID","Organization ID","Organization Name","Email","Mobile","Status"],
        ...rows
    ].map(e => e.map(v => `"${v ?? ''}"`).join(',')).join('\n');

    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'SNP_Registration_List.csv';
    link.click();
}


$('#review_status').select2({
  placeholder: "Select",
  allowClear: true
});
  </script>
@endsection