@extends('components.admin.content-layout')

@section('action-header')

  @php 
        $addUserUrl = url('sub-components/create');

  @endphp

 @if (acl('sub-component-create'))
    <div class="btn-group drop-btn">
        <button 
          type="button" 
          class="btn btn-danger" 
          autocomplete="off"
          onclick="window.location = '{{$addUserUrl}}'"> 
            <img src="{{asset('assets/ffo-admin/img/add.svg')}}" /> 
            {{ __('Add Sub Component') }}
        </button> 
    </div> 
  @endif

@endsection

@section('card-content')
 

    <div class="card-body border-bottom d-flex justify-content-between">
      <form id="search_form" autocomplete="off">
      <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
        <div class="col-md-3 mb-2">
          <label>{{ __('Major Component') }}</label>
          {!! Form::select('major_component_id',dynamic_common_list($details?->majorcomponents),'', ['class' => 'form-select filter_btn', 'id' => 'major_component_id']) !!}
        </div> 
        <div class="col-md-3 mb-2">
          <label class="required">{{ __('Component') }}</label>
          {!! Form::select('component_id',dynamic_common_list($details?->components),'', ['class' => 'form-select filter_btn', 'id' => 'component_id']) !!}  
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
              <th><input type="checkbox" id="selectAll"></th>
							<th>{{ __('message.sn') }}</th>
							<th>{{ __('Major Component') }}</th>
							<th>{{ __('Component') }}</th>
							<th>{{ __('Sub Component') }}</th>
							<th>{{ __('message.status') }}</th>
              @if (acl('sub-component-create') )
							  <th class="actions">{{ __('message.action') }}</th>
              @endif
						</tr>
					</thead>
				</table>
			</div> 
</div>

@section('js');
<script> 
  // dataTableInit({
  //   id: "#dataTable",
  //   showExcelExport: true,
  //   order: {
  //     column: 1,
  //     direction: "asc"
  //   },
      
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
    url: "{{ url('sub-components/datalist') }}",
    columns: [
       {
      "orderable": false,
      "className": "noExport",
      "render": function (data, type, row) {
        var checked = selectedRows[row.id] ? 'checked' : '';
        return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
      }
    },
      {
        "orderable": false,
        "render": function(data, type, full, meta) {
          return serialNumber("#dataTable", meta.row);
        }
      },
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.major_component_name;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.component_name;
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
          return createActivationLabel(row.status);
      }
    },
	  @if (acl('sub-component-create') )
	  	{ 
			"orderable": false,
			"render": function (data, type, row) {
				var pedit='';
				@if (acl('sub-component-create')) 
					pedit=buttonEdit("{{ url('sub-components') }}", row.id); 
				@endif
				
				var actions=createActionButtons([pedit]);

				return actions;
			}
     
        }
		@endif
    ],
    filters: ["major_component_id","component_id"] 
  });

  $(document).ready(function () {
		$('#major_component_id').on('change', function () {
			var majorComponentID = $(this).val();  // get selected value
			getComponent(majorComponentID);
		});
	});
	
  

  
// ✅ Select/Deselect all
$(document).on('change', '#selectAll', function () {
  const checked = $(this).is(':checked');
  $('#dataTable tbody .row-checkbox').prop('checked', checked).trigger('change');
});

// ✅ Track selections
$(document).on('change', '.row-checkbox', function () {
  const id = $(this).val();
  if (this.checked) selectedRows[id] = true;
  else delete selectedRows[id];
  $('#selectAll').prop('checked', $('.row-checkbox').length === $('.row-checkbox:checked').length);
});

// ✅ Maintain selections after table redraw
$('#dataTable').on('draw.dt', function () {
  $('#dataTable .row-checkbox').each(function () {
    const id = $(this).val();
    $(this).prop('checked', !!selectedRows[id]);
  });
});

// ✅ Excel export button logic
$(document).on('click', '.buttons-excel', function (e) {
  e.preventDefault();
  const ids = Object.keys(selectedRows);
  exportSelectedRowsToCSV(ids);
});

// ✅ Export only selected rows + redirect safely
function exportSelectedRowsToCSV(ids) {
  const table = $('#dataTable').DataTable();
  const rows = [];

  table.rows().every(function () {
    const data = this.data();
    if (selectedRows[data.id]) {
      rows.push({
        major_component_name: data.major_component_name,
        component_name: data.component_name,
        name: data.name,
        status: data.status
      });
    }
  });


  const headers = ["Major Component Name", "Component Name","Name", "Status"];
const csv = [
  headers.join(','),
  ...rows.map(r => `"${r.major_component_name}","${r.component_name}","${r.name}","${stripHtml(createActivationLabel(r.status))}"`)
].join('\n');


  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'sub_component_list.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.location.reload();

 function stripHtml(html) {
  const div = document.createElement("div");
  div.innerHTML = html;
  return div.textContent || div.innerText || "";
}
}
</script>
@endsection
@endsection

