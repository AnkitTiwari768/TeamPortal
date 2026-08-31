@extends('components.admin.layout')
@section('page-content')
<div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex border-bottom card-body d-flex justify-content-between mb-3">
					<div class="heading">
						<h1>{{ __('Workflow Transition List') }}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">{{ __('Workflow Transition List') }}</a></li>
							</ol>
						</nav>
					</div>
					@if (acl('workflow-transition-create') || true)
					<div class="action-header ms-auto">
						<div class="btn-group drop-btn">
							<a href="{{ url('workflow-transitions/create') }}">
							<button type="button" class="btn btn-danger"><img src="{{ asset('assets/img-new/add.svg') }}">
								{{ __('Add Workflow Transition') }}</button></a>
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
									  <th>{{ __('From State') }}</th> 
									  <th>{{ __('To State') }}</th>
									  <th>{{ __('Action') }}</th>
                                      <th>{{ __('Auto Execute') }}</th>
                                      <th>{{ __('Show Roles') }}</th>
									@if (acl('workflow-transition-create') || acl('major-component-create') || true)
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
    url: "{{ url('workflow-transitions/datalist') }}",
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
            return row.from_state_label;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.to_state_label;
        }
      },
      
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.action;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.auto_execute ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>';
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.shown_roles;
        }
      },
      /*{
        "orderable": false,
        "render": function(data, type, row) {
            return row.allowed_roles_names;
        }
      },*/
	    @if (acl('workflow-transition-create') || acl('major-component-create') || true)
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';
          var pdelete='';
          @if (acl('workflow-transition-create') || acl('major-component-create') || true) 
            pedit=buttonEdit("{{ url('workflow-transitions') }}", row.id); 
            pdelete=`<a href="javascript:void(0)" onclick="deleteRecord('{{ url('workflow-transitions-delete') }}/${row.id}')" class="btn btn-sm btn-danger ms-1"><i class="fa fa-trash"></i></a>`;
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
    if (selectedRows[data.id] || ids.length === 0) { 
      rows.push({       
        workflow_type_name: data.workflow_type_name,
        from_state_label: data.from_state_label,
        to_state_label: data.to_state_label,
        action: data.action,
        auto_execute: data.auto_execute ? 'Yes' : 'No',
        allowed_roles_names: data.allowed_roles_names
      });
    }
  });

  const headers = [ "Workflow Type", "From State", "To State", "Action", "Auto Execute", "Allowed Roles"];
  const csv = [
    headers.join(','),
    ...rows.map(r => `"${r.workflow_type_name}","${r.from_state_label}","${r.to_state_label}","${r.action}","${r.auto_execute}","${r.allowed_roles_names}"`)
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'workflow_transitions.csv';
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
