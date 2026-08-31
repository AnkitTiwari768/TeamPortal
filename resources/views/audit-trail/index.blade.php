@extends('components.admin.content-layout')
@section('card-content')
 

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('message.module_name') }}</th>
                        <th>{{ __('message.activity_type') }}</th>
                        <th>{{ __('message.full_name') }}</th>  
                        <th>{{ __('message.email') }}</th>   
                        <th>{{ __('Activity Date & Time') }}</th> 
                        <th>{{ __('IP Address') }}</th> 
                    </tr>
                </thead>
            </table>
            </div> 
</div>

@section('js');
<script> 
 $(document).ready(function() {
  dataTableInit({
    id: "#dataTable",
    order: {
      column: 0,
      direction: "DESC"
    },
    url: "{{ url('audit-trail/datalist') }}",
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
            return row.module_name;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.activity_type;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.full_name;
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
            return row.created_at;
        }
      },

      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.ip_address;
        }
      }
    ]
  });
  });
  
  
</script>
@endsection
@endsection

