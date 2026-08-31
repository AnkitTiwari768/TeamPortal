@extends('components.admin.content-layout')

@section('action-header')

  @php 
        $addUserUrl = url('districts/create');

  @endphp

 @if (acl(config('permissions.district-create')))
    <div class="btn-group drop-btn">
        <button 
          type="button" 
          class="btn btn-danger" 
          autocomplete="off"
          onclick="window.location = '{{$addUserUrl}}'"> 
            <img src="{{asset('assets/ffo-admin/img/add.svg')}}" /> 
            {{ __('Add Districts') }}
        </button> 
    </div> 
  @endif

@endsection

@section('card-content')
 

    <div class="card-body border-bottom d-flex justify-content-between">
      <form id="search_form" autocomplete="off">
      <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
        <div class="col-md-3 mb-2">
          <label class="required">{{ __('message.state_name') }}</label>
          {{ Form::select('state_id', state_list(), $row['state_id'] ?? null, ['class' => 'form-select filter_btn', 'id' => 'state_id']) }}  
        </div> 
		    <div class="col-md-3 mb-2">
          <label class="required district_label">{{ __('Districts Name') }}</label>
          {{ Form::select('district_id', district_list(), $row['district_id'] ?? null, ['class' => 'form-select filter_btn', 'id' => 'district_id']) }}  
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
							<th>{{ __('message.state_name') }}</th>
							<th>{{ __('message.location_name') }}</th>
							<th>{{ __('message.code') }}</th>
							<th>{{ __('message.status') }}</th>
              @if (acl(config('permissions.district-update')) )
							  <th class="actions">{{ __('message.action') }}</th>
              @endif
						</tr>
					</thead>
				</table>
			</div> 
</div>
@section('js')
<script>
var selectedRows = {};
dataTableInit({
  id: "#dataTable",
  showExcelExport: true,
  showCustomExportOption: true,
  order: { column: 1, direction: "asc" },
  url: "{{ url('districts/datalist') }}",
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
      "render": function (data, type, full, meta) {
        return serialNumber("#dataTable", meta.row);
      }
    },
    { "data": "state_name", "orderable": true },
    { "data": "name", "orderable": true },
    { "data": "code", "orderable": true },
    {
      "orderable": true,
      "render": function (data, type, row) {
        return createActivationLabel(row.status);
      }
    },
    @if (acl(config('permissions.district-update')))
    {
      "orderable": false,
      "render": function (data, type, row) {
        var pedit = '';
        @if (acl(config('permissions.district-update')))
          pedit = buttonEdit("{{ url('districts') }}", row.id);
        @endif
        return createActionButtons([pedit]);
      }
    }
    @endif
  ],
  filters: ["country_id", "state_id", "district_id"]
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
        state_name: data.state_name,
        name: data.name,
        code: data.code,
        status: data.status
      });
    }
  });


  const headers = ["State Name", "Name", "Code", "Status"];
const csv = [
  headers.join(','),
  ...rows.map(r => `"${r.state_name}","${r.name}","${r.code}","${stripHtml(createActivationLabel(r.status))}"`)
].join('\n');


  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'city_list.csv';
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
