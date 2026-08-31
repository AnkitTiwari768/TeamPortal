@extends('components.admin.content-layout')

@section('card-content') 
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
		 
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
				  
				 	<div class="select-box"> 
						<label class="form-label">Transaction Type</label>
						{!! Form::select('ondc_transaction_type_id', array(''=>'Select')+static_common_list($lists?->ondc_types),'', ['class' => 'form-select filter_btn', 'id' => 'ondc_transaction_type_id']) !!}
					
				    </div>
					
					<div class="select-box"> 
						<label class="form-label">Category</label>
						{!! Form::select('product_category_id[]',remove_select_dynamic_common_list($lists?->sub_domains),'', ['class' => 'form-select select2 filter_btn', 'id' => 'product_category_id','multiple' => 'multiple']) !!}
					
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
							<th>Udyam</th>
							<th>Mobile</th>
							<th>Email</th>
							<th>Name of entrepreneur</th>
							<th>Name of enterprise</th>
							<th>Type of organization </th>
							<th>Enterprise Type </th>
							<th>Social Category </th>
							<th>State </th>
							<th>DateTime </th>
							<th>Days left </th>
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
    url: "{{ url('msme/datalist') }}",
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
            return row.udyam_no;
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
            return row.email;
        }
      },
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.entrepreneur_name;
        }
      },
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.enterprise_name;
        }
      },
	  
	   {
        "orderable": true,
        "render": function(data, type, row) {
            return row.organisation_type;
        }
      },
	  
	   {
        "orderable": true,
        "render": function(data, type, row) {
            return row.msme_classification;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.social_category;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.state_name;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.created_at;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.days_left;
        }
      },
	  
	  { 
        "orderable": false,
			"render": function (data, type, row) {
				var pview='';
				pview=buttonView("{{ url('msme-details/') }}", row.id); 
				var actions=createActionButtons([pview]);
				return actions;
			}
        }
		
    ],
	filters: ["from_date","to_date","ondc_transaction_type_id","product_category_id"]
  });
  
  
   $('#product_category_id').select2({
		placeholder: "Select",
		allowClear: true
	});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
@endsection

