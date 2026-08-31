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
							<!-- <ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="#">Home</a></li>
								<li class="breadcrumb-item"><a href="#">Event List</a></li>
							</ol> -->
						</nav>
					</div>
					
				</div>


			<div class="card-body w-100">
				<form id="search_form" autocomplete="off">
					<div class="filter-bar row py-2 align-items-center">
						<div class="col-lg-3 mb-3">
<div class="select-box">
							<label class="form-label">From date</label>
							<input type="text" class="form-control" name="from_date" id="from_dates"
								placeholder="From Date">
						</div>
						</div>

						<div class="col-lg-3 mb-3">
						
						<div class="select-box">
							<label class="form-label">To date</label>
							<input type="text" class="form-control" name="to_date " id="to_dates"
								placeholder="To Date">
						</div>
</div>
<div class="col-lg-3 mb-3">
						<div class="select-box">
							<label class="form-label" style="display: block;">Target Audience</label>
							 {{ Form::select('event_for',['' => 'Select'] + event_for(), $row['event_for'] ?? null, ['class'=>'form-select filter_btn','id'=>'event_for']) }}
						</div>
</div>
<div class="col-lg-3 mb-3">

						<div class="select-box">
							<label class="form-label" style="display: block;">State</label>
							 {{ Form::select('state_list',['' => 'Select'] + state_list(), $row['state_list'] ?? null, ['class'=>'form-select filter_btn','id'=>'state_list']) }}
						</div>
</div>
<div class="col-lg-3 mb-3">
						<div class="select-box">
							<label class="form-label" style="display: block;">Status</label>
							 {{ Form::select('eventStatus',['' => 'Select'] + $eventStatus, $row['eventStatus'] ?? null, ['class'=>'form-select filter_btn','id'=>'eventStatus']) }}
						</div>
</div>
<div class="col-lg-3 mb-3">

						<a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
								src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
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
										<!-- <th><input type="checkbox" id="selectAll"></th> -->
										<th>S.No</th>
										<th>Event Title</th>
										<th>Event For</th>
										<th>Organiser</th>
										<th class="text-nowrap">Start Date</th>
										<th class="text-nowrap">End Date</th>
										<th>State</th>
										<th>District</th>
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
		url: "{{ url('event-list') }}",
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
					return row.event_title;
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.organiser_name ?? '-';
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.event_for ? row.event_for.charAt(0).toUpperCase() + row.event_for.slice(1) : '-';
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.start_date;
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.end_date;
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.state_name;
				}
			},
			{
				"orderable": true,
				"render": function (data, type, row) {
					return row.district_name;
				}
			},
			/*{
				"orderable": true,
				"render": function (data, type, row) {
					return moment(row.created_at).format("DD-MM-YYYY");
				}
			},*/
			{
				"orderable": false,
				"render": function (data, type, row) {
					var viewBtn = buttonView("{{ url('view-workshop') }}", row.id);
					var actions = createActionButtons([viewBtn]);
					return actions;
				}
			}
		],
		createdRow: true,
        filters: ["from_dates","to_dates","event_for","state_list",'eventStatus']
	});

		/*$(document).on("change", ".row-checkbox", function() {
            var id = $(this).val();
            selectedRows[id] = $(this).prop("checked");

            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });


        $(document).on("change", "#selectAll", function() {
            var checked = $(this).prop("checked");
            $(".row-checkbox").each(function() {
                $(this).prop("checked", checked);
                selectedRows[$(this).val()] = checked;
            });
        });*/


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


        $(".clear-action").on("click", function () {

            $("#from_dates").val('');
            $("#to_dates").val('');

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });
  
</script>
@endsection



@endsection