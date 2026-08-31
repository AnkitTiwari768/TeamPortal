@extends('components.admin.content-layout')

@section('card-content') 
		 

<div>
   <div class="accordion" id="whoCanApplyAccordion">
    <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
       <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
        <img class="img-fluid accordian-img"  src="{{ asset('assets/ffo-admin/img/eligibility-.svg') }}"> Claim Eligibility Criteria 
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#whoCanApplyAccordion">
      <div class="accordion-body">
        <div class="row">

          <!-- Left Column -->
          <div class="col-md-6">
            <div class="custom-card-apply eligibility-card-1">
              <h5><span> <i class="bi bi-check-circle"></i> </span> Eligibility criteria for a SNP to process claims for MSEs</h5>
              <ol>
                <li>MSE must be either a Micro or Small enterprise.</li>
                <li>The Major activity for MSE should be either “Manufacturing” or “Services”.</li>
                <li>The MSE should be live on ONDC Network.</li>
                <li>The claim for catalogue creation for the MSE must have been claimed, approved and completed.</li>                
              </ol>
            </div>
          </div>

          <!-- Right Column -->
          <div class="col-md-6">
            <div class="custom-card-apply eligibility-card-2">
              <h5> <span> <i class="bi bi-file-earmark-text"></i> </span> Documents Required</h5>
              <ol>
                <li>Transaction Logs (PDF).</li>
                <li>CA Certificate. <a style="color:#fff" href="{{ url('storage/app/download_format/Annexure-Statutory-Auditor-Certificate.docx') }}">Download option for downloading the predefined format.</a></li>
                <li>Expense Details with Supporting Documents (Invoice, Date).</li>
                <li>Self-Declaration to be checked. </li>                
              </ol>
            </div>
          </div>

           <!--Left Column -->
          <div class="col-md-6 pt-3">
            <div class="custom-card-apply eligibility-card-3">
              <h5> <span> <i class="bi bi-file-earmark-text"></i> </span> For Accounts Management</h5>
              <ol>

                <li>The incentives structure for the SNPs for ‘Account Management Support’ is as following- 
                  <ol type="a">
                     <li>For B2C MSEs: 5% of net sales* on the network - (*net sales - to be calculated as invoice value minus taxes and logistics costs).  </li>
                      <li>For B2B MSEs: Rs. 250 per transaction on the ONDC network </li>
                      <li> The incentive for Accounts Management will be capped up to Rs. 5,000 per MSE </li>   
                  </ol>
                </li>

              </ol>
            </div>
          </div>


             <!-- Right Column -->
          <div class="col-md-6 pt-3">
            <div class="custom-card-apply eligibility-card-4">
              <h5> <span> <i class="bi bi-file-earmark-text"></i> </span>For Logistics and Transportation </h5>
              <ol>
                
                <li>For B2C orders: Rs. 50 per order for up to 10 orders per MSE </li>
                <li>For B2B orders: Rs. 200 per order for up to 10 orders per MSE </li>
                <li>The incentive for Logistics & Transportation will be capped up to Rs. 500 per MSE for B2C MSE.  </li>
                <li>The incentive for Logistics & Transportation will be capped up to Rs. 2000 per MSE for B2B MSE.  </li>                
                

              </ol>
            </div>
          </div>


        </div>
      </div>
    </div>

  </div>
</div>
</div>
		<div class="align-items-end border-bottom card-body d-flex justify-content-between">
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
			
			@if (hasRole('snp'))
            <div class="card-header d-flex">
                <div class="action-header ms-auto">
                    <div class="btn-group drop-btn">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#myCSVModal">
                            <img src="{{ asset('assets/img-new/add.svg') }}">
                            Add Bulk Claim
                        </button>
                    </div>
                </div>
            </div>
        @endif
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
							<th>Date of Registration</th>
							<th class="actions">{{ __('message.action') }}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div> 	



<!-- Modal CSV -->
    <div class="modal fade" id="myCSVModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Bulk Claim</h5>
                    </div>

                    <div class="text-center flex-grow-1">
                        
                        
                            <a href="{{ url('storage/app/download_format/account_bulk_import.zip') }}"
                                class="btn btn-sm btn-outline-primary">Download Account Sample Format</a>
                        
                            <a href="{{ url('storage/app/download_format/logistic_bulk_import.zip') }}"
                                class="btn btn-sm btn-outline-primary">Download Logistic Sample Format
							</a>
                        
                    </div>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="bulk-upload">
                    @csrf
                    <div class="modal-body">
                        <p>
                            <select class="form-select" name="claim_type_id" id="claim_type_id">
                                <option value="">Select Claim Type</option>
                                @foreach ($claim_types ?? [] as $claim_type_id => $claim_type_name)
                                    <option value="{{ $claim_type_id }}">
                                        {{ $claim_type_name }}
                                    </option>
                                @endforeach
                            </select>
                        </p>
                        <p>Select File : <input type="file" name="file" id="file" accept=".zip"></p>
                        <span class="text-primary">Please ensure the file is in the correct format by downloading the sample format.</span>

                        <div id="csv-errors" style="color: red; font-family: Arial; padding: 10px;"></div>


                    </div>
                    <div class="modal-footer">
                        <button type="sbumit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>		
    
    

@section('js');
<script> 


   $('#myCSVModal').on('hidden.bs.modal', function () {
            $('#csv-errors').html(''); // clear the error messages
            $('#file').val('');
        });
		
		$(document).on('change', '#bulk-upload', function(e) {
			e.preventDefault();
			let fileName = e.target.files[0]?.name; // get selected file name
			if (fileName) {
				let ext = fileName.split('.').pop().toLowerCase();
				if (ext !== 'zip') {
					alert("Only .zip files are allowed!");
					 $('#file').val('');
				}
			}
		});

        $(document).on('submit', '#bulk-upload', function(e) {
            e.preventDefault();
			
            document.getElementById('csv-errors').innerHTML = '';
            var claimTypeId = document.getElementById('claim_type_id').value;

            var form = document.getElementById('bulk-upload');
            var formData = new FormData(form);
            formData.append('claim_type_id', claimTypeId);
            if (claimTypeId == '') {
                toastr.error("Please select claim type.");
                return;
            }

            var fileInput = document.getElementById('file');
            if (!fileInput.files.length) {
                toastr.error("Please select a file to upload.");
                return;
            }

            $.ajax({
                url: "{{ url('claims/bulk-import') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
				beforeSend: function() {
					$("#ajax-loader").show();
				},
                success: function(res) {
                    toastr.success(res.message);
                    $("#csv-errors").html('');
					
                    setTimeout(function() {
                        window.location.href = "{{ url('claims') }}";
                    }, 2000);
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        toastr.error(xhr.responseJSON.message);
                        displayCsvErrors(xhr.responseJSON);
                    } else {
                        toastr.error("Something went wrong.");
                        document.getElementById('csv-errors').innerHTML = '';
                    }
                },
				complete: function() {
					$("#ajax-loader").hide();
				}
            });
        });

        // Function to format and display errors
        function displayCsvErrors(data) {
            let errorHtml = `<strong>${data.message}</strong><ul>`;

            for (let cell in data.errors) {
                data.errors[cell].forEach(msg => {
                    errorHtml += `<li><strong>Cell ${cell}</strong>: ${msg}</li>`;
                });
            }

            errorHtml += `</ul>`;
            document.getElementById('csv-errors').innerHTML = errorHtml;
        }


  // dataTableInit({
  //   id: "#dataTable",
	// showExcelExport: true,
  //   order: {
  //     column: 1,
  //     direction: "asc"
  //   },
  //   url: "{{ url('transacted-live/datalist') }}",
  //   columns: [
  //     {
  //       "orderable": false,
  //       "render": function(data, type, full, meta) {
  //         return serialNumber("#dataTable", meta.row);
  //       }
  //     },
  //     {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.team_id;
  //       }
  //     },
  //     {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.udyam_no;
  //       }
  //     },
	   
  //     {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.mobile;
  //       }
  //     },
	//   {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.email;
  //       }
  //     },
	//   {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.entrepreneur_name;
  //       }
  //     },
	//   {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.enterprise_name;
  //       }
  //     },
	  
	//    {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.msme_classification;
  //       }
  //     },

	  
	//   {
  //       "orderable": true,
  //       "render": function(data, type, row) {
  //           return row.created_at;
  //       }
  //     },
	  
	//   { 
  //       "orderable": false,
	// 		"render": function (data, type, row) {
	// 			let actions = '';

	// 			@if(hasRole('snp'))
	// 				actions = `
	// 				<div class="dropdown">
	// 					<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
	// 						<i class="bi bi-three-dots-vertical"></i>
	// 					</button>
	// 					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
	// 						<li>
	// 							<a class="dropdown-item" href="{{ url('accounts-and-management') }}/${row.id}">
	// 								Claim For Accounts & Management
	// 							</a>
	// 						</li>
	// 						<li>
	// 							<a class="dropdown-item" href="{{ url('transport-and-logistic-creation') }}/${row.id}">
	// 								Claim For Logistics & Transport
	// 							</a>
	// 						</li>
	// 					</ul>
	// 				</div>`;
	// 			@endif

	// 			@if(hasRole('bnp'))
	// 				actions = `
	// 				<div class="dropdown">
	// 					<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
	// 						<i class="bi bi-three-dots-vertical"></i>
	// 					</button>
	// 					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
	// 						<li>
	// 							<a class="dropdown-item" href="{{ url('demand-generation') }}/${row.id}">
	// 								Claim For Demand Generation
	// 							</a>
	// 						</li>
	// 					</ul>
	// 				</div>`;
	// 			@endif

  //       @if(hasRole('iip'))
	// 				actions = `
	// 				<div class="dropdown">
	// 					<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
	// 						<i class="bi bi-three-dots-vertical"></i>
	// 					</button>
	// 					<ul class="dropdown-menu dropdown-menu-end shadow-sm">
	// 						<li>
	// 							<a class="dropdown-item" href="{{ url('packaging-claim') }}/${row.id}">
	// 								Claim For Packaging
	// 							</a>
	// 						</li>
	// 					</ul>
	// 				</div>`;
	// 			@endif

	// 			return actions;
				
	// 			 /* @if(hasRole('snp'))
	// 					var accounts_claim = '<a href="{{ url('accounts-and-management') }}/' + row.id + '">Claim For Accounts & Management</a>';
	// 					var transport_claim = '<a href="{{ url('transport-and-logistic-creation') }}/' + row.id + '">Claim For Logistics & Transport</a>';
	// 					var actions = createActionButtons([accounts_claim, transport_claim]);
	// 			  @endif
				  
				  
	// 			  @if(hasRole('bnp'))
	// 					var demand_genration = '<a href="{{ url('demand-generation') }}/' + row.id + '">Claim For Demand Generation</a>';
	// 					var actions = createActionButtons([demand_genration]);
	// 			  @endif
                
  //               return actions;*/
	// 		}
  //       }
		
  //   ],
	// filters: ["from_date","to_date"]
  // });
  

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
       url: "{{ url('transacted-live/datalist') }}",
    columns: [
       {
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
        "orderable": false,
			"render": function (data, type, row) {
				let actions = '';

				@if(hasRole('snp'))
					actions = `
					<div class="dropdown">
						<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-three-dots-vertical"></i>
						</button>
						<ul class="dropdown-menu dropdown-menu-end shadow-sm">
							<li>
								<a class="dropdown-item" href="{{ url('accounts-and-management') }}/${row.id}">
									Claim For Accounts & Management
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="{{ url('transport-and-logistic-creation') }}/${row.id}">
									Claim For Logistics & Transport
								</a>
							</li>
						</ul>
					</div>`;
				@endif

				@if(hasRole('bnp'))
					actions = `
					<div class="dropdown">
						<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-three-dots-vertical"></i>
						</button>
						<ul class="dropdown-menu dropdown-menu-end shadow-sm">
							<li>
								<a class="dropdown-item" href="{{ url('demand-generation') }}/${row.id}">
									Claim For Demand Generation
								</a>
							</li>
						</ul>
					</div>`;
				@endif

        @if(hasRole('iip'))
					actions = `
					<div class="dropdown">
						<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							<i class="bi bi-three-dots-vertical"></i>
						</button>
						<ul class="dropdown-menu dropdown-menu-end shadow-sm">
							<li>
								<a class="dropdown-item" href="{{ url('packaging-claim') }}/${row.id}">
									Claim For Packaging
								</a>
							</li>
						</ul>
					</div>`;
				@endif

				return actions;
				
				 /* @if(hasRole('snp'))
						var accounts_claim = '<a href="{{ url('accounts-and-management') }}/' + row.id + '">Claim For Accounts & Management</a>';
						var transport_claim = '<a href="{{ url('transport-and-logistic-creation') }}/' + row.id + '">Claim For Logistics & Transport</a>';
						var actions = createActionButtons([accounts_claim, transport_claim]);
				  @endif
				  
				  
				  @if(hasRole('bnp'))
						var demand_genration = '<a href="{{ url('demand-generation') }}/' + row.id + '">Claim For Demand Generation</a>';
						var actions = createActionButtons([demand_genration]);
				  @endif
                
                return actions;*/
			}
    }
		
    ],
	filters: ["from_date","to_date"]
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

