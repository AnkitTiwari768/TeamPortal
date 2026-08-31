@extends('components.admin.layout')
@section('page-content')

 <main>
                <div class="container-fluid px-4 py-4">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                            <div class="card container-main-card">
                                <div class="card-header d-flex">
                                    <div class="heading">
                                        <h1>{{ __('message.application_query_listing') }}</h1>
                                        
                                    </div>
                                    <div class="action-header ms-auto">
                                        <a href="javascript:void(0);" onclick = "javascript:history.back(-1);" class="btn btn-sm btn-primary"><img src="{{ asset('assets/ffo-admin/img/arrow-back-w.svg') }}"> Back</a>
										<a href="{{url('/add-application-queries')}}/{{$id}}" class="btn btn-sm btn-primary"> <i class="fa fa-plus" aria-hidden="true"></i> Add Query</a>
                                    </div>
                                   
                                </div>
                                <div class="card-body pt-1 position-relative">    
                                    

                                        <!-- page table design start -->
                                        <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0"> 
										<thead>
											<tr>
												<th>{{ __('message.sn') }}</th> 
												<th>{{ __('message.application_from') }}</th>
												<th>{{ __('message.query_date') }}</th>							
												<!--<th>{{ __('message.application_number') }}</th> --> 
												<th >{{ __('message.subject') }}</th>
												{{-- @if (acl(config('permissions.national-application-update'))) --}}
													<th class="actions">{{ __('message.action') }}</th>
												{{-- @endif --}}
												
											</tr>
										</thead>
									</table>
                                        <!-- page table design end -->


                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>

           

@section('js');
<script> /*
$.ajax({
	type: 'GET',
	url: "{{ url('/get-application-queries/') }}/{{$id}}",
	async:false,
	success: function (response) {
		if(response.data.length==0){
			window.location.href="{{ url('/no-application-queries') }}/{{$id}}";
		}
	}
});*/
  dataTableInit({
    id: "#dataTable",
    showExcelExport: true,
    order: {
      column: 1,
      direction: "asc"
    },
    url: "{{ url('/application-queries/') }}/{{$id}}",
    async:false,
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
            return row.from_name;
        }
      }, 
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.date;
        }
      }, /*
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.application_number;
        }
      },*/  
       {
        "orderable": true,
        "render": function(data, type, row) {
            return row.subject;
        }
      },
	    {{-- @if (acl(config('permissions.national-application-update'))) --}}
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
         var pedit='';
		pedit = `<a href="{{ url('view-application-queries/')}}/${row.applicant_query_id}"><img src="{{ asset('assets/ffo-admin/img/view.svg') }}"></a>`;          
          
          var actions=createActionButtons([pedit]);

          return actions;
        }
      }
      {{-- @endif --}}
       
	   
	  
    ]
  });
  
</script>
@endsection
@endsection

