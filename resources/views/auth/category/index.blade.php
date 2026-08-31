@extends('components.admin.layout')
@section('page-content')

<div class="container-fluid">
	<div class="card mb-4">
	  <div class="card-header d-flex justify-content-between">
			<h6 class="box-heading heading-1">{{ __('message.category_list') }}</h6>
			@if (acl(config('permissions.category-create')))
				<a href="{{ url('categories/create') }}" class="btn btn-info wave-effect">
				<span class="btn-label"><?php echo __('message.plus'); ?> </span>{{ __('message.add_category') }}</a>
			@endif
      
			
		</div>

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('message.category_name') }}</th>
							<th>{{ __('message.level') }}</th>
							<th>{{ __('message.status') }}</th>
							 @if (acl(config('permissions.category-update')) || acl(config('permissions.category-delete')))
								<th class="actions">{{ __('message.action') }}</th>
							@endif
						</tr>
					</thead>
				</table>
			</div>
		</div>
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
    url: "{{ url('categories/datalist') }}",
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
      }, {
        "orderable": true,
        "render": function(data, type, row) {
            return row.level;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.status);
        }
      },
	  	@if (acl(config('permissions.category-update')) || acl(config('permissions.category-delete')))
	  	{ 
			"orderable": false,
			"render": function (data, type, row) {
				var pedit='';
				@if (acl(config('permissions.category-update'))) 
					pedit=buttonEdit("{{ url('categories') }}", row.id); 
				@endif
				
				var pdelete='';
				@if (acl(config('permissions.category-delete'))) 
					pdelete= (row.is_mapped==0)?buttonDelete("{{ url('categories') }}", row.id):'';
				@endif
				
				var actions=createActionButtons([pedit,pdelete]);

				return actions;
			}
     
        }
		@endif
    ]
  });
  
</script>
@endsection
@endsection

