@extends('components.admin.content-layout')

@section('card-content')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


	<div class="card-body">
<form id="search_form" class="mb-2" autocomplete="off">
			<div class=" filter-bar d-flex align-items-left flex-column gap-3 py-2 mb-0">

					<div class="row">
						<div class="col-lg-3 col-2 mb-3">
							<div class="select-box from-date-box">
							<label class="form-label">From date</label>
							<input type="text" class="form-control filter_btn" name="from_date" id="from_dates"
								placeholder="From Date">
						</div>
</div>
	<div class="col-lg-3 col-2 mb-3">
			<div class="select-box to-date-box">
							<label class="form-label">To date</label>
							<input type="text" class="form-control filter_btn" name="to_date " id="to_dates" placeholder="To Date">
						</div>
</div>
	<div class="col-lg-3 col-2 mb-3">
		<div class="select-box">
							<label class="form-label">Status</label>
							{!! Form::select('msme_status', $statusOptions, '', ['class' => 'form-select  filter_btn', 'id' => 'msme_status']) !!}
						</div>
</div>
	<div class="col-lg-3 col-2 mb-3">
		<div class="select-box">
							<label class="form-label">SNP Name</label>
							{!! Form::select('snp_id',  ['' => 'Select'] + $snpName, '', ['class' => 'form-select select2 filter_btn', 'id' => 'snp_id']) !!}
						</div>
</div>
	<div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-0">
		<a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
								src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a> 
</div>
					</div>
						
					
						
						

						

						
			</div>
		</form>
		<div class="table-responsive">
			<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
				<thead>
					<tr>
						<!-- <th><input type="checkbox" id="selectAll"></th> -->
						<th>{{ __('message.sn') }}</th>
						<th>Team ID</th>
						<th>Udyam Number</th>
						<th>Mobile</th>
						<th>Email</th>
						<th>Name of Enterpreneur</th>
						<th>Name of enterprise</th>
						<th>Enterprise Type</th>
						<th>Major Category</th>
						<th>Product Category</th>
						<th>Gender</th>
						<th>State</th>
						<th>Registration Date</th>
						<th>Type of Transaction</th>
						
						<th class="actions">{{ __('message.action') }}</th>
					</tr>
				</thead>
				<tbody>
					<!-- Dynamic rows will be populated by DataTable -->
				</tbody>
			</table>
		</div>
	</div>

	<style>
		form#search_form {
			overflow: auto;
		}
	</style>

	@section('js');
		<script>
			window.csrfToken = "{{ csrf_token() }}";
			var selectedRows = {};
			dataTableInit({
				id: "#dataTable",
				showExcelExport: false,
				showCustomExportOption: true,
				order: {
					column: 1,
					direction: "asc"
				},
				url: "{{ url('get-snp-wise-mse-list') }}",
				columns: [
					  /*{
						"orderable": false,
						"className": "noExport",
						"render": function (data, type, row) {
							var checked = selectedRows[row.id] ? 'checked' : '';
							return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
						}
					},*/
					{
						"orderable": false,
						"render": function (data, type, full, meta) {
							return serialNumber("#dataTable", meta.row);
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.team_id;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.udyam_no;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.mobile;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.email;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.entrepreneur_name;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.enterprise_name;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.msme_classification;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.major_activity || "N/A";
						}
					},
					{
						orderable: false,
						render: function (data, type, row) {

							let text = '';

							if (Array.isArray(row.product_categories)) {
								text = row.product_categories.join(', ');
							} else {
								text = row.product_categories || '';
							}

							if (type === 'sort' || type === 'filter') {
								return text;
							}

							if (text.length <= 20) {
								return text || 'N/A';
							}

							return `
								<span class="short-text">${text.substring(0, 20)}...</span>
								<span class="full-text d-none">${text}</span>
								<a href="#" class="toggle-text d-block mt-1" style="text-decoration:none;">
									Read More
								</a>
							`;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.gender 
							? row.gender.charAt(0).toUpperCase() + row.gender.slice(1) 
							: 'N/A';
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.state_name;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.created_at;
						}
					},
					{
						"orderable": false,
						"render": function (data, type, row) {
							return row.transaction_type;
						}
					},
					
					{
						"orderable": false,
						"render": function (data, type, row) {
							var pview = '';
							pview = buttonView("{{ url('snp-wise-mse-detail/') }}", row.id);
							var actions = createActionButtons([pview]);
							return actions;
						}
					}
				],
				filters: ["from_dates", "to_dates","msme_status","snp_id"]
			});


			
			$('#snp_id').select2({
				placeholder: "Select",
				allowClear: true
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

			$(".clear-action").on("click", function () {

				$("#from_dates").val('');
				$("#to_dates").val('');
				$("#mapping_option").val('');
				$("#product_category_id").val('');
				$("#state_id").val('');
				$("#gender").val('');
				$("#msme_classification").val('');
				$("#major_activity").val('');
				$("#ondc_transaction_type_id").val('');

				$("#from_dates").datepicker("option", "maxDate", 0);
				$("#to_dates").datepicker("option", "minDate", null);

				oTable.ajax.reload();

				$(".clear-action").hide();
			});

			$(document).on('click', '.toggle-text', function (e) {

				e.preventDefault();

				let td = $(this).closest('td');

				td.find('.short-text').toggleClass('d-none');
				td.find('.full-text').toggleClass('d-none');

				if ($(this).hasClass('expanded')) {
					$(this).text('Read More').removeClass('expanded');
				} else {
					$(this).text('Read Less').addClass('expanded');
				}
			});
		</script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	@endsection
@endsection