@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex border-bottom card-body d-flex justify-content-between mb-3">
					<div class="heading">
						<h1>{{ __('Workflow State List') }}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">{{ __('Workflow State List') }}</a></li>
							</ol>
						</nav>
					</div>
					@if (acl('workflow-state-create') || true)
					<div class="action-header ms-auto">
						<div class="btn-group drop-btn">
							<a href="{{ url('workflow-states/create') }}">
							<button type="button" class="btn btn-danger"><img src="{{ asset('assets/img-new/add.svg') }}">
								{{ __('Add Workflow State') }}</button></a>
						</div>
					</div>
					@endif
				</div>
				<div class="card-body pt-1">
					<div class="tab-content view-application-tab-content" id="nav-tabContent">
						<div class="tab-pane fade show active" id="national-p" role="tabpanel" aria-labelledby="nav-home-tab">
							<table id="dataTable" class="table datatable table-striped " style="width:100%"> 
								<thead>
									<tr>
									  <th>{{ __('message.sn') }}</th> 
									  <th>{{ __('Workflow Type') }}</th> 
									  <th>{{ __('State Key') }}</th> 
									  <th>{{ __('State Value') }}</th>
									  <th>{{ __('Label') }}</th>
									  <th>{{ __('Shown Roles') }}</th>
									  <th>{{ __('Initial') }}</th>
									  <th>{{ __('Final') }}</th>
									@if (acl('workflow-state-create') || acl('major-component-create') || true)
										<th class="actions">{{ __('message.action') }}</th>
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
</div>
@section('js')
<script> 
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
    url: "{{ url('workflow-states/datalist') }}",
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
            return row.workflow_type_name;
        }
      }, 
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.state_key;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.state_value;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.label;
        }
      },
      {
        "orderable": false,
        "render": function(data, type, row) {
            if (!row.shown_roles || row.shown_roles.length === 0) return '-';
            var roles = typeof row.shown_roles === 'string' ? JSON.parse(row.shown_roles) : row.shown_roles;
            var html = '';
            roles.forEach(function(r) {
                html += `<span class="badge bg-info me-1">${r.role} (${r.tab})</span>`;
            });
            return html;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.is_initial ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.is_final ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
        }
      },
	    @if (acl('workflow-state-create') || acl('major-component-create') || true)
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';
          var pdelete='';
          @if (acl('workflow-state-create') || acl('major-component-create') || true) 
            pedit=buttonEdit("{{ url('workflow-states') }}", row.id); 
            pdelete=`<a href="javascript:void(0)" onclick="deleteRecord('{{ url('workflow-states-delete') }}/${row.id}')" class="btn btn-sm btn-danger ms-1"><i class="fa fa-trash"></i></a>`;
          @endif
          
          var actions=createActionButtons([pedit]) + pdelete;

          return actions;
        }
      }
      @endif
    ]
  });

$(document).on('click', '.buttons-excel', function (e) {
  e.preventDefault();
  const ids = Object.keys(selectedRows);
  exportSelectedRowsToCSV(ids);
});

function exportSelectedRowsToCSV(ids) {
  const table = $('#dataTable').DataTable();
  const rows = [];

  table.rows().every(function () {
    const data = this.data();
    if (selectedRows[data.id] || ids.length === 0) { // If no selection export all? Usually datatables has selection logic
      rows.push({       
        workflow_type_name: data.workflow_type_name,
        state_key: data.state_key,
        state_value: data.state_value,
        label: data.label,
        shown_roles: data.shown_roles && data.shown_roles.length > 0 ? (typeof data.shown_roles === 'string' ? JSON.parse(data.shown_roles) : data.shown_roles).map(r => r.role + ' (' + r.tab + ')').join('; ') : '-',
        is_initial: data.is_initial ? 'Yes' : 'No',
        is_final: data.is_final ? 'Yes' : 'No'
      });
    }
  });

  const headers = [ "Workflow Type", "State Key", "State Value", "Label", "Shown Roles", "Is Initial", "Is Final"];
  const csv = [
    headers.join(','),
    ...rows.map(r => `"${r.workflow_type_name}","${r.state_key}","${r.state_value}","${r.label}","${r.shown_roles}","${r.is_initial}","${r.is_final}"`)
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'workflow_states.csv';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

function deleteRecord(url) {
    if(confirm('Are you sure you want to delete this record?')) {
        $.ajax({
            url: url,
            type: 'DELETE',
            data: { _token: csrfToken },
            success: function(response) {
                if(response.status === 'success'){
                    alert(response.message);
                    $('#dataTable').DataTable().ajax.reload();
                } else {
                    alert('Error deleting record');
                }
            }
        });
    }
}
</script>
@endsection
@endsection
