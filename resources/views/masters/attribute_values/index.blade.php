@extends('components.admin.content-layout')
@section('action-header')
@php
        $dynamicSlug = $dynamicSlug ?? ''; 
        $addUserUrl = url('masters/' . $dynamicSlug . '/create');
@endphp


    <div class="btn-group drop-btn">
        <button type="button"
          class="btn btn-danger"
          autocomplete="off"
          onclick="window.location = '{{$addUserUrl}}'">
            <img src="{{asset('assets/ffo-admin/img/add.svg')}}" />
            Add {{ str_replace('_', ' ', ucwords($title)) }}
        </button>
    </div>
  {{-- @endif --}}

@endsection

@section('card-content')
  <div class="card mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('message.sn') }}</th>                       
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                           	<th class="text-end">Action</th>
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
      url: "{{ url('masters/' . $dynamicSlug . '/datalist') }}",
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
              return row.attribute_value;
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
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.sort_order;
        }
      },
      {
        "orderable": false,
        "className": "text-end",
        "render": function(data, type, row) {
        var editBtn = buttonEdit("{{ url('masters/' . $dynamicSlug) }}", row.id);
            var actions = createActionButtons([editBtn]);
            return actions;
        }
      }
      ],
      filters: ["status"]
    });

  </script>
  @endsection
  @endsection

