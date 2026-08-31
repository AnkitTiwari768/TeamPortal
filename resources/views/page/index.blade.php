@extends('components.admin.layout')

@section('page-content')

<!-- <div class="container-fluid">
 
  <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('message.page_list') }}
          <span class="float-right">
          @if (acl('page-create'))
            <a href="{{ url('pages/create') }}" class="btn btn-info btn-sm">
                <i class="fas fa-plus"></i> {{ __('message.add_page') }}
            </a>
            @endif
          </span>
        </h6>
    </div>
    <div class="card-body"> -->
        <div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">
                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('page.page_list') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item"><a href="#">{{ __('page.page_list') }}</a></li>
                            </ol>
                        </nav>
                    </div>
                    @if (acl('page-edit'))
                    <div class="action-header ms-auto">
                        <!-- split button -->
                        <div class="btn-group drop-btn">
                            <a href="{{ url('pages/create') }}">
                            <button type="button" class="btn btn-danger"> 
                                <img src="{{asset('assets/img-new/add.svg')}}">{{ __('page.add_page') }}
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
						<th>{{ __('menu.menu_title_en') }}</th>
						<th>{{ __('menu.menu_title_mr') }}</th>
                        <th>{{ __('page.title_en') }}</th>
						<th>{{ __('page.title_mr') }}</th>
                        <th>{{ __('menu.is_active') }}</th>
                        @if (acl('page-edit'))
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
    url: "{{ url('pages/datalist') }}",
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
            return row.menu_title_en;
        }
      },
	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.menu_title_mr;
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
            return createActivationLabel(row.is_active);
        }
      },
	  
      @if (acl('page-edit'))
      {
        "orderable": false,
        "render": function (data, type, row) {
            var action = '';
            @if (acl('page-edit'))
              action += buttonEdit("{{ url('pages') }}", row.id);
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
           