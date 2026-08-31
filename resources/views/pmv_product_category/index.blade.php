@extends('components.admin.layout')
@section('page-content')
 <div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>Pmv Product Category List</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">Pmv Product Category List</a></li>
							</ol>
						</nav>
					</div>
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							<a href="{{ url('pmv-category-create') }}">
							<button type="button" class="btn btn-danger"> <img src="{{asset('assets/img-new/add.svg')}}">
								Add Pmv Product Category</button></a>
						</div>
					</div>
				</div>
				<div class="card-body pt-1">
					
					<!-- tab content -->
					<div class="tab-content view-application-tab-content" id="nav-tabContent">
						<div class="tab-pane fade show active" id="national-p" role="tabpanel"
							aria-labelledby="nav-home-tab">


							<!-- page table design start -->
							<table id="dataTable" class="table datatable table-striped" width="100%"> 
								<thead>
									<tr>
									<th>S.No</th>
									<th>Category Name</th>
									<th>Sub Domain Name</th>  
									<th>Status</th>
									<th class="actions">Action</th>
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
  dataTableInit({
    id: "#dataTable",    
    showExcelExport: false,
    order: {
      column: 1,
      direction: "desc"
    },
    url: "{{ url('pmvCategory/datalist') }}",
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
            return row.subDomain_name;
        }
      }, 
      {
        "orderable": true,
        "render": function(data, type, row) {
            return createActivationLabel(row.status);
        }
      },

	  	{ 
        "orderable": false,
        "render": function (data, type, row) {
          var pedit='';         
          var pedit=buttonEdit("{{ url('pmv-categories') }}", row.id);          
          var actions=createActionButtons([pedit]); 
		  return actions;
        }
      }

    ]
  });
  
</script>
@endsection



@endsection