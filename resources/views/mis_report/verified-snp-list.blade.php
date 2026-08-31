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
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('Authorized Person Name') }}</th>
              <th>{{ __('SNP Id') }}</th>
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
  dataTableInit({
    id: "#dataTable",
	showExcelExport: true,
    order: {
      column: 1,
      direction: "asc"
    },
    url: "{{ url('verified-snp/datalist') }}",
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
            return row.snp_id;
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
				
					pedit=buttonView("{{ url('snp-view-detail') }}", row.id); 
				
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

