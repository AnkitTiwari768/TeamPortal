@extends('components.admin.content-layout')

@section('card-content') 
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 


		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>NP ID</th>
							<th>Name</th>
							<th>State Name</th>
							<th>Domain Name</th>
							<th>Organization Name</th>
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
    url: "{{ url('get-relevant-snp') }}",
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
            return row.snp_id;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.snp_name;
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
        /*"render": function(data, type, row) {
            return row.domain_names;
        }*/
       "render": function (data, type, row) {
          let text = row.domain_names || '';
          if (text.length <= 50) return text;

          return `
            <span class="short">${text.slice(0,30)}...</span>
            <span class="full d-none d-block">${text}</span>
            <a href="#" class="toggle d-block mt-1" style="text-decoration:none;">Read More</a>
          `;
        }
      },
	   
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.organization_name;
        }
      },
	 
	  
	  
	  { 
        "orderable": false,
			"render": function (data, type, row) {
				var pview='';
				pview=buttonView("{{ url('relevant-snp-view/') }}", row.id); 
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
<script>
  document.addEventListener('click', e => {
    if (e.target.classList.contains('toggle')) {
      e.preventDefault();
      let td = e.target.parentElement;
      td.querySelector('.short').classList.toggle('d-none');
      td.querySelector('.full').classList.toggle('d-none');
      e.target.textContent =
        e.target.textContent === 'Read More' ? 'Read Less' : 'Read More';
    }
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
@endsection

