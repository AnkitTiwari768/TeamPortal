@extends('components.admin.layout')
@section('page-content') 

    <div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('menu.menu_list') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ __('menu.menu_list') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                    @if (acl('menu-edit'))
                    <div class="action-header ms-auto">
                        <!-- split button -->
                        <div class="btn-group drop-btn">
                            <a href="{{ url('menus/create') }}">
                            <button type="button" class="btn btn-danger"> 
                                <img src="{{asset('assets/img-new/add.svg')}}">{{ __('menu.add_menu') }}
                            </button></a>
                        </div>
                    </div>
                    @endif
                </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
						<th>{{ __('menu.parent_title') }}</th>
                        <th>{{ __('menu.title_en') }}</th>
						<th>{{ __('menu.title_mr') }}</th>
						<th>{{ __('menu.sort_order') }}</th>
						<!--<th>{{ __('menu.sub_menu') }}</th>-->
						<!--<th>{{ __('menu.template') }}</th>-->
                        <th>{{ __('menu.is_active') }}</th>
                        @if (acl('menu-edit'))
                          <th>{{ __('message.action') }}</th>
                        @endif
                    </tr>
                </thead>
            </table>
        </div>
    </div>
  </div>
</div>
</div>
</div>

@endsection

@section('js')

<script>
$(document).ready(function() {
  dataTableInit({
    id: "#dataTable",
    showExcelExport: false,
    order: {
      column: 0,
      direction: "ASC"
    },
    url: "{{ url('menus/datalist') }}",
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
            return row.title_en;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.title_en;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.title_mr;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.sort_order;
        }
      },
	  
	  /*{
        "orderable": true,
        "render": function(data, type, row) {
			if(row.sub_menu==1){
				return 'Yes';
			}else{
				return 'No';
			}
        }
      },*/
	  
	/*   {
        "orderable": true,
        "render": function(data, type, row) {
			if(row.template==2){
				return 'Dynamic Page';
			}
        }
      }, */
	  
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.is_active);
        }
      },
	  
      @if (acl('menu-edit'))
      {
        "orderable": false,
        "render": function (data, type, row) {
            var action = '';
            @if (acl('menu-edit'))
              action += buttonEdit("{{ url('menus') }}", row.id);
            @endif
            return action;
        }
      }
      @endif
    ]
  });
});
</script>

@endsection
           