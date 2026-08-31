@extends('components.admin.layout')
@section('page-content')
 <div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex border-bottom card-body d-flex justify-content-between mb-3">
					<div class="heading">
						<h1>{{ __('Workflow Type List') }}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">{{ __('Workflow Type List') }}</a></li>
							</ol>
						</nav>
					</div>
					@if (acl('workflow-type-create') || acl('major-component-create'))
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							<a href="{{ url('workflow-types/create') }}">
							<button type="button" class="btn btn-danger"><img src="{{ asset('assets/img-new/add.svg') }}">
								{{ __('Add Workflow Type') }}</button></a>
						</div>
					</div>
					@endif
				</div>
				<div class="card-body pt-1">
					
					<!-- tab content -->
					<div class="tab-content view-application-tab-content" id="nav-tabContent">
						<div class="tab-pane fade show active" id="national-p" role="tabpanel"
							aria-labelledby="nav-home-tab">


							<!-- page table design start -->
							<table id="dataTable" class="table datatable table-striped " style="width:100%"> 
								<thead>
									<tr>
									  <th>{{ __('message.sn') }}</th> 
									  <th>{{ __('Workflow Type') }}</th> 
									  <th>{{ __('Slug') }}</th> 
									  <th>{{ __('message.status') }}</th>
									@if (acl('workflow-type-create') || acl('major-component-create'))
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
    url: "{{ url('workflow-types/datalist') }}",
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
            return row.name;
        }
      }, 
      {
        "orderable": true,
        "render": function(data, type, row) {
            return row.slug;
        }
      },
      {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.status);
        }
      },
	    @if (acl('workflow-type-create') || acl('major-component-create'))
	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';
          @if (acl('workflow-type-create') || acl('major-component-create')) 
            pedit=buttonEdit("{{ url('workflow-types') }}", row.id); 
          @endif
          
          var actions=createActionButtons([pedit]);

          return actions;
        }
      }
      @endif
    ]
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
        name: data.name,
        slug: data.slug,
        status: data.status
      });
    }
  });


  const headers = [ "Workflow Type", "Slug", "Status"];
const csv = [
  headers.join(','),
  ...rows.map(r => `"${r.name}","${r.slug}","${stripHtml(createActivationLabel(r.status))}"`)
].join('\n');


  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'workflow_type.csv';
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
