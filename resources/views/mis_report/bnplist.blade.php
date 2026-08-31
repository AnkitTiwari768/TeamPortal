@extends('components.admin.content-layout')

@section('card-content') 
		 
		 <div class="card-body">
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
                     <div class="select-box">
					<label class="form-label">Status</label>
					{!! Form::select('review_status',status_filter()  , '', ['class' => 'form-select  filter_btn', 'id' => 'review_status']) !!}
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
              <!-- <th><input type="checkbox" id="selectAll"></th> -->
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
  // dataTableInit({
  //   id: "#dataTable",
	// showExcelExport: true,
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
    url: "{{ url('mis-bnp-registration-reports/datalist') }}",
    columns: [
        // {
        //   "orderable": false,
        //   "className": "noExport",
        //   "render": function (data, type, row) {
        //     var checked = selectedRows[row.id] ? 'checked' : '';
        //     return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
        //   }
        // },
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
				
					pedit=buttonView("{{ url('/mis-bnp-registration-reports/bnp-view-detail') }}", row.id); 
				
				var ppermission='';

				var actions=createActionButtons([pedit]);
				return actions;
			}
        }
		
    ],
	filters: ["from_date","to_date","review_status"]
  });
  
 // select all
$(document).on('change', '#selectAll', function () {
    $('.row-checkbox').prop('checked', this.checked).trigger('change');
});

// store selected ids
var selectedRows = {};

$(document).on('change', '.row-checkbox', function () {
    this.checked ? selectedRows[this.value] = true : delete selectedRows[this.value];
});

// maintain checkbox after redraw
$('#dataTable').on('draw.dt', function () {
    $('.row-checkbox').each(function () {
        $(this).prop('checked', !!selectedRows[this.value]);
    });
});

// excel export
$(document).on('click', '.buttons-excel', function (e) {
    e.preventDefault();

    if (!Object.keys(selectedRows).length) {
        toastr.warning('Please select at least one row');
        return;
    }

    const table = $('#dataTable').DataTable();
    const rows = [];

    // IMPORTANT: applied search + all visible data
    table.rows({ search: 'applied' }).data().each(function (data) {

        if (selectedRows[data.id]) {
            rows.push([
                data.name,
                data.bnp_id,
                data.organization_id,
                data.organization_name,
                data.email,
                data.mobile
            ]);
        }
    });

    const csv = [
        ["Authorized Person Name","BNP ID","Organization ID","Organization Name","Email","Contact No"],
        ...rows
    ].map(e => e.map(v => `"${v ?? ''}"`).join(',')).join('\n');

    const blob = new Blob([csv], { type: 'text/csv' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'BNP_Registration_List.csv';
    link.click();
});

</script>
@endsection
@endsection

