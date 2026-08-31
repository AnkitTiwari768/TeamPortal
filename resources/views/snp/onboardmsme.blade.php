@extends('components.admin.content-layout')

@section('card-content') 
		 
		<div class="border-bottom card-body d-flex justify-content-between">
			<form id="search_form" autocomplete="off">
				<div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
				   <div class="select-box">
					<label class="form-label">From date1</label>  
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
							<th>Days left </th>
                          
							<th class="actions">{{ __('message.action') }}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>  

@section('js');
<!-- <script> 

  dataTableInit({
    id: "#dataTable",
	showExcelExport: true,
    order: {
      column: 1,
      direction: "asc"
    },
    url: "{{ url('msme-chossen-me/datalist') }}",
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
            return row.entrepreneur_name;
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

	  	  
	  {
        "orderable": true,
        "render": function(data, type, row) {
            return row.days_left;
        }
      },
	  
	  { 
        "orderable": false,
			"render": function (data, type, row) {
				var pview='';
				pview=buttonView("{{ url('msme-details/') }}", row.id); 
				var actions=createActionButtons([pview]);
				
				var pinprgress='';
				pinprgress=buttonInprogress("{{ url('msme-inprogress/') }}", row.id); 
				
				var actions=createActionButtons([pview,pinprgress]);
				return actions;
				return actions;
			}
        }
		
    ],
	filters: ["from_date","to_date"]
  });
  

</script> -->
 <script>
        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
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
            url: "{{ url('onboard-mse/datalist') }}",
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
                 {
                  "orderable": true,
                  "render": function(data, type, row) {
                      return row.days_left;
                  }
                },
               

               { 
                    "orderable": false,
                    "render": function (data, type, row) {
                    var pview='';
                    pview=buttonView("{{ url('msme-details/') }}", row.id); 
                    var actions=createActionButtons([pview]);
                    
                    var pinprgress='';
                    pinprgress=buttonInprogress("{{ url('msme-inprogress/') }}", row.id); 
                    
                    
                    var actions=createActionButtons([pview,pinprgress]);
                    return actions;
                    return actions;
                    }
                },

            ],
            filters: ["from_date", "to_date"]
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

