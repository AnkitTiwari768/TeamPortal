@extends('components.admin.content-layout')

@section('action-header')

  @php 
        $addUserUrl = url('states/create');

  @endphp

  @if (acl(config('permissions.state-create')))
    <div class="btn-group drop-btn">
        <button 
          type="button" 
          class="btn btn-danger" 
          autocomplete="off"
          onclick="window.location = '{{$addUserUrl}}'"> 
            <img src="{{asset('assets/ffo-admin/img/add.svg')}}" /> 
            {{ __('message.add_state') }}
        </button> 
    </div> 
  @endif

@endsection

@section('card-content') 

    <div class="card-body">
      <form id="search_form" autocomplete="off">
      <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
        <div class="select-box">
        <label>{{ __('message.country_name') }}</label>
        {{ Form::select('country_id', country_list(), $row['country_id'] ?? null, ['class' => 'form-select filter_btn', 'id' => 'country_id']) }}
       
	   </div> 
		    <div class="select-box">
        <label>{{ __('message.state_name') }}</label>
        {{ Form::select('state_id', state_list(), $row['state_id'] ?? null, ['class' => 'form-select filter_btn', 'id' => 'state_id']) }}
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
							<th>{{ __('message.country_name') }}</th>
							<th>{{ __('message.state_name') }}</th>
							<th>{{ __('message.code') }}</th>
							<th>{{ __('message.status') }}</th>
              @if (acl(config('permissions.state-update')) )
							 <th class="actions">{{ __('message.action') }}</th>
              @endif
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
    url: "{{ url('states/datalist') }}",
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
            return row.country_name;
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
            return row.code;
        }
    },
    {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.status);
        }
    },
    @if (acl(config('permissions.state-update')) )
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';
          @if (acl(config('permissions.state-update'))) 
            pedit=buttonEdit("{{ url('states') }}", row.id); 
          @endif
          
          var actions=createActionButtons([pedit]);

          return actions;
        }
      }
    @endif
      
    ],
	filters: ["country_id","state_id"] 
  });
  
</script>
@endsection
@endsection

