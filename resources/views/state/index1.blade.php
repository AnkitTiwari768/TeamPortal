@extends('components.admin.layout')
@section('page-content')
 <div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>{{ __('message.state_list') }}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">{{ __('message.state_list') }}</a></li>
							</ol>
						</nav>
					</div>
					@if (acl(config('permissions.state-create')))
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							<a href="{{ url('states/create') }}">
							<button type="button" class="btn btn-danger"><img src="{{ asset('assets/img-new/add.svg') }}">
								{{ __('message.add_state') }}</button></a>
						</div>
					</div>
					@endif
				</div>
				<div class="card-body pt-1">
					
					<!-- tab content -->
					<div class="tab-content view-application-tab-content" id="nav-tabContent">
						<div class="tab-pane fade show active" id="national-p" role="tabpanel"
							aria-labelledby="nav-home-tab">
							 <div class="filter-bar mt-4 mb-4 d-flex px-3 py-2 align-items-center">
                                                <p class="me-4 mb-0"> <img src="{{asset('assets/img-new/filter-ico.svg')}}"> Filter By </p>

                                                <!-- dropdown buttons -->
                                                <div class="dropdown me-2">
                                                     {{ Form::select('country_id', country_list(), $row['country_id'] ?? null, ['class' => 'form-control btn', 'id' => 'country_id']) }}
                                                </div>

                                                <!-- dropdown buttons -->
                                                <div class="dropdown me-2">
                                                     {{ Form::select('state_id', state_list(), $row['state_id'] ?? null, ['class' => 'form-control btn', 'id' => 'state_id']) }}
                                                </div>



                                                <a href="" class="ms-auto clear-action"> <img
                                                        src="{{asset('assets/img-new/close-blue.svg')}}">Clear all</a>

                                            </div>

							<!-- page table design start -->
							<table id="dataTable" class="table datatable table-striped " style="width:100%"> 
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
							<!-- page table design end -->

						</div>
					
					</div>
					<!-- tab ends -->



				</div>
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