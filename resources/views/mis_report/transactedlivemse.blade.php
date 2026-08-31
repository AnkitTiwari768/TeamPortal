@extends('components.admin.content-layout')

@section('card-content')
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

	<div class="border-bottom card-body d-flex justify-content-between">
		<form id="search_form" autocomplete="off">
			<div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
				
				<div class="select-box">
					<label class="form-label">Category</label>
					{!! Form::select('msme_classification', msmeClassification(), '', ['class' => 'form-select filter_btn', 'id' => 'msme_classification']) !!}
				</div>
				<div class="select-box">
					<label class="form-label">Transaction Type</label>
					{!! Form::select('ondc_transaction_type_id', array('' => 'Select') + static_common_list($lists?->ondc_types), '', ['class' => 'form-select select2 filter_btn', 'id' => 'ondc_transaction_type_id']) !!}
				</div>
				<div class="select-box">
					<label class="form-label">Major Activity</label>
					{!! Form::select('major_activity', majorActivity(), '', ['class' => 'form-select select2 filter_btn', 'id' => 'major_activity']) !!}
				</div>
				<div class="select-box">
					<label class="form-label">Product Category</label>
					{!! Form::select('product_category_id[]', remove_select_dynamic_common_list($lists?->sub_domains), '', ['class' => 'form-select select2 filter_btn', 'id' => 'product_category_id', 'multiple' => 'multiple']) !!}
				</div>
				<div class="select-box">
					<label class="form-label">State</label>
					{!! Form::select('state_id[]', remove_select_dynamic_common_list($lists?->state_id), '', ['class' => 'form-select select2 filter_btn', 'id' => 'state_id', 'multiple' => 'multiple']) !!}
				</div>

				<a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
						src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
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
						<th>SNP ID</th>
						<th>SNP NAme</th>
						<th>MSE Team ID</th>
            			<th>Name of MSE</th>
						<th>Category of MSE</th>
						<th>Transaction Type</th>
						<th>Major Category</th>
						<th>Product Category</th>
						<th>State</th>
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
			// dataTableInit({
			// 	id: "#dataTable",
			// 	showExcelExport: true,
			// 	order: {
			// 		column: 1,
			// 		direction: "asc"
			// 	},
			
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
				url: "{{ url('msme-onboarding-ondc-report/datalist') }}",
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
         			{
						"orderable": true,
						"render": function (data, type, row) {
							return row.snp_id;
						}
					},
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.snp_name;
						}
					},
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.team_id;
						}
					},
						
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.entrepreneur_name;
						}
					},		
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.msme_classification;
						}
					},
          			{
						"orderable": true,
						"render": function (data, type, row) {
							return row.transaction_type;
						}
					},
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.major_activity || "N/A";
						}
					},
					{
						"orderable": true,
						"render": function (data, type, row) {
							if (Array.isArray(row.product_categories) && row.product_categories.length > 0) {
								return row.product_categories.join(', ');
							}
							return "N/A";
						}
					},		
					{
						"orderable": true,
						"render": function (data, type, row) {
							return row.state_name;
						}
					},
          
					
					{
						"orderable": false,
						"render": function (data, type, row) {
							var pview = '';
							pview = buttonView("{{ url('mis-reports-msme-details/') }}", row.id);
							var actions = createActionButtons([pview]);
							return actions;
						}
					}
				],
				filters: ["from_date", "to_date", "ondc_transaction_type_id", "product_category_id", "state_id", "gender", "msme_classification", "major_activity"]
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

  // Collect selected rows
  table.rows().every(function () {
    const data = this.data();
    if (selectedRows[data.id]) {
      rows.push({
        snp_id: data.snp_id,
        snp_name: data.snp_name,
		team_id: data.team_id,
        entrepreneur_name: data.entrepreneur_name,
        msme_classification: data.msme_classification,
        transaction_type: data.transaction_type,
        major_activity: data.major_activity,
        product_categories: data.product_categories,
		state_name: data.state_name
      });
    }
  });

  // ✅ Define CSV headers (must match the fields above)
  const headers = [
    "SNP ID",
    "SNP Name",
    "MSE Team ID",
    "Name of MSE",
    "Category of MSE",
    "Transaction Type",
    "Major Category",
    "Product Categories",
    "State"
  ];

  // ✅ Build CSV content
  const csv = [
    headers.join(','), // header row
    ...rows.map(r =>
      [
        r.snp_id,
        r.snp_name,
        r.team_id,
        r.entrepreneur_name,
        r.msme_classification,
        r.transaction_type,
        r.major_activity,
        r.product_categories,
        r.state_name
      ]
        .map(v => `"${String(v || '').replace(/"/g, '""')}"`) // safely escape quotes
        .join(',')
    )
  ].join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'MSEs_Onboarded_on_ONDC.csv';
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

			$('#product_category_id').select2({
				placeholder: "Select",
				allowClear: true
			});
			$('#state_id').select2({
				placeholder: "Select",
				allowClear: true
			});
			$('#gender').select2({
				placeholder: "Select",
				allowClear: true
			});
			$('#msme_classification').select2({
				placeholder: "Select",
				allowClear: true
			});
			$('#major_activity').select2({
				placeholder: "Select",
				allowClear: true
			});
			$('#ondc_transaction_type_id').select2({
				placeholder: "Select",
				allowClear: true
			});
			// Select/Deselect all checkboxes
			$('#select-all').change(function() {
				var isChecked = $(this).prop('checked');
				$('.row-checkbox').prop('checked', isChecked);
			});

			// Handle the download action
			$('#download-btn').click(function() {
				var selectedIds = [];
				$('.row-checkbox:checked').each(function() {
					selectedIds.push($(this).data('id'));
				});

				if (selectedIds.length > 0) {
					$.ajax({
						url: '/download-records', // Replace with your download URL
						method: 'POST',
						data: { ids: selectedIds },
						success: function(response) {
							console.log(response);
						},
						error: function(error) {
							console.error('Download failed:', error);
						}
					});
				} else {
					alert('Please select at least one record.');
				}
			});
		</script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
	@endsection
@endsection
