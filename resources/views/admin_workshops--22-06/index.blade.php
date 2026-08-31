@extends('components.admin.layout')
@section('page-content')
 <div class="container-fluid px-4 py-4">
	<div class="row">
		<div class="col-lg-12 col-md-12">
			<div class="card container-main-card">
				<div class="card-header d-flex">
					<div class="heading">
						<h1>{{$title}}</h1>
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{url('dashboard')}}">Dashboard</a></li>
								<li class="breadcrumb-item"><a href="#">{{$title}}</a></li>
							</ol>
						</nav>
					</div>
				
					<div class="action-header ms-auto">
						<!-- split button -->
						<div class="btn-group drop-btn">
							<a href="{{ url('create-admin-workshop') }}">
								<button type="button" class="btn btn-danger">
									<img src="{{asset('assets/img-new/add.svg')}}"> Add Workshop
								</button>
							</a>
						</div>
					</div>
					
				</div>


                <div class="card-body d-flex justify-content-between">
                    <form id="search_form" autocomplete="off">
                        <div class="filter-bar d-flex py-2 gap-3 align-items-center">
                            <div class="row">
                                <div class="col-lg-3 col-2 mb-3">
                                    <div class="select-box">
                                        <label class="form-label">From date</label>
                                        <input type="text" class="form-control" name="from_date" id="from_dates"
                                            placeholder="From Date">
                                    </div>
                                </div>
                            <div class="col-lg-3 col-2 mb-3">
                                <div class="select-box">
                                    <label class="form-label">To date</label>
                                    <input type="text" class="form-control" name="to_date" id="to_dates"
                                        placeholder="To Date">
                                </div>
                            </div>
                
                            <div class="col-lg-3 col-2 mb-3">
                                <div class="select-box">
                                    <label class="form-label">State</label>
                                {!! Form::select('state_id[]', ['' => 'Select'] + remove_select_dynamic_common_list($lists?->state_id), '', ['class' => 'form-select filter_btn', 'id' => 'state_id']) !!}
                                </div>
                            </div>
                            <div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-3">

                            <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                                    src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
                        </div>

                        </div>
                        </div>
                    </form>
                </div>
				<div class="card-body pt-1">
					<!-- tab content -->
					<div class="tab-content view-application-tab-content" id="nav-tabContent">
						<div class="tab-pane fade show active" id="national-p" role="tabpanel" aria-labelledby="nav-home-tab">
							
							<!-- table start -->
							<table id="dataTable" class="table datatable table-striped" width="100%">
								<thead>
									<tr>
										<th>S.No</th>
										<th>Financial Year</th>
										<th>Duration</th>
										<th>Workshop Title</th>		
										<th>State</th>
										<th>Expense Amount</th>
										<th class="actions">Action</th>
									</tr>
								</thead>
							</table>
							<!-- table end -->
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
 var selectedRows = {};
  dataTableInit({
		id: "#dataTable",
		showExcelExport: true,
		order: {
			column: 1,
			direction: "desc"
		},
		url: "{{ url('admin-workshop/datatable') }}",
		columns: [
			{
				"orderable": false,
				"render": function (data, type, full, meta) {
					return serialNumber("#dataTable", meta.row);
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.financial_year ?? '-';
				}
			},
			{
			"orderable": true,
			"render": function (data, type, row) {
				return row.duration_name ?? '-';
			}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.title ?? '-';
				}
			},
			
		
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.state_name ?? '-';
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.total_expense ? parseFloat(row.total_expense).toFixed(2) : '-';
				}
			},
			{
				"orderable": false,
				"render": function (data, type, row) {		

					 var viewBtn = buttonView("{{ url('view-admin-workshop') }}", row.id);					
					var pedit ='';				
						var pedit = buttonEdit("{{ url('edit-admin-workshop') }}", row.id);
				
					var actions = createActionButtons([pedit,viewBtn]);
					return actions;
				}
			}
		],
		createdRow: true,
            filters: ["from_dates","to_dates","state_id"]
	});

	

	 		$("#from_dates").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0,
                onSelect: function(selected) {
                    $("#to_dates").datepicker("option", "minDate", selected);
                    handleDateChange();
                }
            });

            $("#to_dates").datepicker({
                dateFormat: "dd-mm-yy",
                changeYear: true,
                changeMonth: true,
                maxDate: 0,
                onSelect: function(selected) {
                    $("#from_dates").datepicker("option", "maxDate", selected);
                    handleDateChange();
                }
            });

            function handleDateChange() {
                let from = $("#from_dates").val();
                let to = $("#to_dates").val();

                if (from || to) {
                    oTable.ajax.reload();
                    $("#reset_btn").show();
                } else {
                    $("#reset_btn").hide();
                }
            }

			$("#from_dates, #to_dates").datepicker({
				dateFormat: "dd-mm-yy"
			});

			// reload on change
			$("#from_dates, #to_dates, #state_id").on("change", function () {
				oTable.ajax.reload();
			});
        $(".clear-action").on("click", function () {

            $("#from_dates").val('');
            $("#to_dates").val('');
            //$("#state_id").val('');

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });

	function buttonDelete(baseUrl, id) {
    return `
        <a href="javascript:void(0)" 
           onclick="deleteItem('${baseUrl}${id}')" 
           class="btn btn-sm btn-danger" 
           title="Delete">
            <i class="fa fa-trash"></i>
        </a>
    `;
}
function deleteItem(url) {
    if (!confirm('Are you sure you want to delete this workshop?')) return;

    $.ajax({
        url: url,
        type: 'DELETE',
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function (response) {
            toastr.success(response.message);
            $('#dataTable').DataTable().ajax.reload();
        },
        error: function () {
            toastr.error('Delete failed');
        }
    });
}
  
</script>
@endsection
@endsection