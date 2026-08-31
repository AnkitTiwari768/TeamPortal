@extends('components.admin.content-layout')

@section('card-content') 
		 



		<div class="border-bottom card-body d-flex justify-content-between">
			<form id="search_form" autocomplete="off">
				<div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
				   <div class="select-box">
					<label class="form-label">From date</label>  
					<input type="text" class="form-control filter_btn" name="from_date" id="from_date" placeholder="From Date">
				  </div>
				  <div class="select-box"> 
					<label class="form-label">To date</label>
					<input type="text" class="form-control filter_btn" name="to_date " id="to_date" placeholder="To Date">
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
							<th>TEAMID</th>
							<th>Udyam</th>
							<th>Mobile</th>
							<th>Email</th>
							<th>State</th>
							<th>Name of enterprise</th>
							<th>Enterprise Type </th>
							<th>Date </th>
							<!--<th>Days left </th>-->
							<th class="actions">{{ __('message.action') }}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>  
<div id="tableLoader">
    <div class="loader-box">
        <div class="spinner-border text-primary" role="status"></div>
      
    </div>
</div>
@section('js');

 <script>
        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        // 🔵 SHOW LOADER ON PAGE LOAD
        $("#tableLoader").show();

        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('validated-mse/datalist') }}",
            columns: [{
                    "orderable": false,
                    "className": "noExport",
                    "render": function(data, type, row) {
                        var checked = selectedRows[row.id] ? 'checked' : '';
                        return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' +
                            checked + '>';
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
                        return row.team_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.udyam_no;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.mobile;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.email;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.state_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.enterprise_name;
                    }
                },

                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.msme_classification;
                    }
                },


                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                },
                 /*{
                  "orderable": true,
                  "render": function(data, type, row) {
                      return row.days_left;
                  }
                },*/

               { 
                    "orderable": false,
                    "render": function (data, type, row) {
                    var pview='';
                    pview=buttonView("{{ url('msme-details/') }}", row.id); 
                    var actions=createActionButtons([pview]);
                    
                    var pinprgress='';
                    @if(acl('Update-Bppid-Validate-Mse'))
                    pinprgress=buttonInprogress("{{ url('msme-inprogress/') }}", row.id); 
                    @endif
                    
                    var actions=createActionButtons([pview,pinprgress]);
                    return actions;
                    return actions;
                    }
                },

            ],
            filters: ["from_date", "to_date"]
        });


        // 🔵 LOADER EVENTS (IMPORTANT)
        $('#dataTable').on('preXhr.dt', function () {
            $("#tableLoader").show();
        });

        $('#dataTable').on('xhr.dt', function () {
            $("#tableLoader").hide();
        });

        $('#dataTable').on('draw.dt', function () {
            $("#tableLoader").hide();
        });


        $(document).on("change", ".row-checkbox", function() {
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
        });
        $('#dataTable').on('draw.dt', function() {
            $(".row-checkbox").each(function() {
                var id = $(this).val();
                $(this).prop("checked", selectedRows[id] ? true : false);
            });
            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });
    </script>
@endsection
@endsection
<style>
    #tableLoader{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loader-box{
    text-align: center;
    background: #fff;
    padding: 20px 25px;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
}
</style>
