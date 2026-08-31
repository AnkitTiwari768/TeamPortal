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
                  
				  <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
			  </div>
			</form>
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('Authorized Person Name') }}</th>
							<th>{{ __('BNP Id') }}</th>
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

@section('js');
<script> 

  // dataTableInit({
  //   id: "#dataTable",
	// showExcelExport: true,
  //   order: {
  //     column: 1,
  //     direction: "asc"
  //   },

   window.customExportUrl = "{{ url('custom-export-by-ids') }}";
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
    url: "{{ url('unverified-bnp/datalist') }}",
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
            orderable: true,
            render: function (data, type, row) {
                let status = row.review_status ?? '';
                let label = '';

                if (status == 3) {
                    label = `<span class="badge bg-success">Approved</span>`;
                } else if (status ==4) {
                    label = `<span class="badge bg-danger">Rejected</span>`;
                }else if (status == 5) {
                    label = `<span class="badge bg-danger">Reverted</span>`;
                }else {
                    label = `<span class="badge bg-secondary">Pending</span>`;
                }

                return label;
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
  
</script>
@endsection
@endsection

