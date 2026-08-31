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
                        <input type="text" class="form-control filter_btn" name="to_date " id="to_date" placeholder="To Date">
                    </div>
				   <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
			  </div>
			</form>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
              <th><input type="checkbox" id="selectAll"></th>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('Authorized Person Name') }}</th>
              <th>{{ __('BNP Id') }}</th>
							<th>{{ __('Organization Id') }}</th>
							<th>{{ __('Organization Name') }}</th>
							<th>{{ __('Email') }}</th>
							<th>{{ __('Contact No') }}</th>
							<th class="actions">{{ __('message.action') }}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>  

@section('js');
<script> 
  
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
    url: "{{ url('verified-bnp/datalist') }}",
    columns: [
         {
          "orderable": false,
          "className": "noExport",
          "render": function (data, type, row) {
            var checked = selectedRows[row.id] ? 'checked' : '';
            return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
          }
        },
      {
        "orderable": false,
        "render": function(data, type, full, meta) {
          return serialNumber("#dataTable", meta.row);
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.name;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.bnp_id;
        }
      },
	   
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.organization_id;
        }
      },
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.organization_name;
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
        "orderable": false,
			"render": function (data, type, row) {

				var pedit='';
				
					pedit=buttonView("{{ url('bnp-view-detail') }}", row.id); 
				
				var ppermission='';

				var actions=createActionButtons([pedit]);
				return actions;
			}
        }
		
    ],
	filters: ["from_date","to_date"]
  });


  

// ✅ Select/Deselect all
$(document).on('change', '#selectAll', function () {
  const checked = $(this).is(':checked');
  $('#dataTable tbody .row-checkbox').prop('checked', checked).trigger('change');
});

// ✅ Track selections
$(document).on('change', '.row-checkbox', function () {
  const id = $(this).val();
  if (this.checked) selectedRows[id] = true;
  else delete selectedRows[id];
  $('#selectAll').prop('checked', $('.row-checkbox').length === $('.row-checkbox:checked').length);
});

// ✅ Maintain selections after table redraw
$('#dataTable').on('draw.dt', function () {
  $('#dataTable .row-checkbox').each(function () {
    const id = $(this).val();
    $(this).prop('checked', !!selectedRows[id]);
  });
});

// ✅ Excel export button logic
$(document).on('click', '.buttons-excel', function (e) {
  e.preventDefault();
  const ids = Object.keys(selectedRows);
  exportSelectedRowsToCSV(ids);
});function exportSelectedRowsToCSV(ids) {
  const table = $('#dataTable').DataTable();
  const rows = [];

  // Collect selected rows
  table.rows().every(function () {
    const data = this.data();
    if (selectedRows[data.id]) {
      rows.push({
        name: data.name,
        bnp_id: data.bnp_id,
        organization_id: data.organization_id,
        organization_name: data.organization_name,
        email: data.email,
        mobile: data.mobile
      });
    }
  });

  // ✅ CSV headers (match the row fields)
  const headers = [
    "Authorized Person Name",
    "BNP ID",
    "Organization ID",
    "Organization Name",
    "Email",
    "Mobile"
  ];

  // ✅ Build CSV content
  const csv = [
    headers.join(','),
    ...rows.map(r =>
      [
        r.name,
        r.bnp_id,
        r.organization_id,
        r.organization_name,
        r.email,
        r.mobile
      ]
        .map(v => `"${String(v || '').replace(/"/g, '""')}"`) // escape quotes
        .join(',')
    )
  ].join('\n');


  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'Verified_BNP_List.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.location.reload();

 function stripHtml(html) {
  const div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
}
}
  
</script>
@endsection
@endsection

