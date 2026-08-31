@extends('components.admin.content-layout')

@section('card-content') 
		 

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
              <!-- <th><input type="checkbox" id="selectAll"></th> -->
							<th>{{ __('message.sn') }}</th>
							<th>TEAMID</th>
							<th>Udyam</th>
							<th>Mobile</th>
							<th>Email</th>
							<th>State</th>
							<th>Name of enterprise</th>
							<th>Enterprise Type </th>
							<th>Date of Registration</th>
							<!-- <th>Eligible for additional 10% bonus incentive</th> -->
							<!-- <th>Status</th> -->
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
    
  <div class="modal fade" id="incentiveBonusModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">

              <div class="modal-header bg-light">
                  <h5 class="modal-title fw-semibold">
                      Claim Bonus
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <form id="bonus-submit">
                  @csrf
                  <div class="modal-body">
                      <div id="bonus-errors" class="mb-3 text-danger"></div>
                      <div class="row g-3">
                            <input type="hidden" name="msme_id" id="msme_id">
                            <input type="hidden" value="{{$claimTypeId}}" name ="claim_type_id" id="claim_type_id">
                          <div class="col-md-4">
                              <label class="form-label fw-semibold">MSME TEAM Registration ID </label>
                              <input type="text" class="form-control" id="team_registration_id" name="team_registration_id" readonly>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label fw-semibold">MSME Udyam Number</label>
                              <input type="text" class="form-control" id="msme_udyam_number" name="msme_udyam_number" readonly>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label fw-semibold">BPP Provider ID</label>
                              <input type="text" class="form-control" id="bpp_id" name="bpp_id" readonly>
                          </div>
                            <div class="col-md-12">
                                <div class="col-md-8">
                                <label class="form-label fw-semibold">Remarks (Optional)</label>
                                <textarea class="form-control" name="remarks" rows="3" placeholder="Enter remarks if any"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                @foreach ($documents as $key => $bonusDocument)
                                    <div class="form-group col-md-4">

                                        <label class="form-label required">
                                            {{ $bonusDocument->name }}
                                        </label>

                                        <a class="tooltip-ins" href="#"
                                        data-toggle="tooltip"
                                        title="{{ $bonusDocument->informations }}">
                                            <i class="fa fa-question-circle" aria-hidden="true"></i>
                                        </a>

                                        <input type="file"
                                            name="{{ $bonusDocument->slug }}"
                                            id="{{ $bonusDocument->slug }}"
                                            document_category_id="{{ $bonusDocument->id }}"
                                            class="form-control upload-document"
                                            accept="application/pdf"
                                            @empty($claimId) required @endempty
                                        >

                                        <input type="hidden"
                                            name="file_upload_ids[]"
                                            id="claim_documents_{{ $bonusDocument->id }}"
                                           
                                        />

                                        <span class="text-danger form-error"
                                            id="{{ $bonusDocument->slug }}_error"></span>

                                        @isset($bonusDocument->file_system_name)
                                            <a href="{{ url('storage/app/uploads/claim-documents/' . $bonusDocument->file_system_name) }}"
                                            target="_blank">
                                                {{ $bonusDocument->file_name }}
                                            </a>
                                        @endisset
                                    </div>
                                @endforeach
                            </div>
                          
                      </div>
                      <div class="row mt-3">
                        <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-themed table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Claim Amount Received</th>
                                                <th>Date of Claim Submitted</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bonusOrderDetailsContainer"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                      </div>

                  <div class="modal-footer bg-light">
                      <button type="button"
                              class="btn btn-outline-secondary"
                              data-bs-dismiss="modal">
                          Cancel
                      </button>

                      <button type="submit" class="btn btn-primary"> Submit Claim </button>
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

    $('#incentiveBonusModal').on('hidden.bs.modal', function () {
      $('#bonus').html('');
      $('#file').val('');
      $('#claim_type_id').val('');
    });

    $('#bonus-submit').on('submit', function (e) {
        e.preventDefault();

        let formData = $(this).serialize();

        $.ajax({
            url: "{{ url('/submit-bonus-claim') }}",
            type: "POST",
            data: formData,
            success: function (res) {
                if (res.status) {
                    toastr.success('Bonus claim submitted successfully');

                    $('#incentiveBonusModal').modal('hide');

                    window.location.reload();

                } else {
                    toastr.error('Unable to submit claim');
                }
            },
            error: function (xhr) {

                if (xhr.responseJSON?.errors) {
                    toastr.error('Validation error');
                } else {
                    toastr.error('Something went wrong');
                }
            }
        });
    });


        $(document).ready(function () {
        $('#incentiveBonusModal').on('show.bs.modal', function (e) {
            $('#team_registration_id').val('');
            $('#msme_udyam_number').val('');
            $('#bpp_id').val('');
            $('#msme_id').val('');
            $('#bonus').html('');

            let button = e.relatedTarget;
            let rowId  = $(button).data('id');
            if (!rowId) return;
            var url = "{{ url('/get-msme-bonus-details') }}/" + rowId;
            $.ajax({
            url: url,
            type: 'GET',
            success: function (res) {
                $('#bonusOrderDetailsContainer').html('');
                let totalAmount = 0;
                if (res.success && Array.isArray(res.data) && res.data.length > 0) {
                let first = res.data[0];
                $('#team_registration_id').val(first.team_id ?? '');
                $('#msme_udyam_number').val(first.udyam_no ?? '');
                $('#bpp_id').val(first.bpp_id ?? '');
                $('#msme_id').val(first.msme_id ?? '');
                res.data.forEach(function (item, index) {
                    let amount = parseFloat(item.amount) || 0;
                    totalAmount += amount;
                    $('#bonusOrderDetailsContainer').append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>₹ ${item.amount ?? 0}</td>
                        <td>${item.created_date ?? '-'}</td>
                    </tr>`);
                });
                $('#bonusOrderDetailsContainer').append(`
                    <tr class="fw-bold bg-light">
                        <td class="text-end">Total</td>
                        <td>₹ ${totalAmount}</td>
                        <td></td>
                    </tr>
                `);
                } 
                else {
                    $('#bonusOrderDetailsContainer').html(`
                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No data found
                            </td>
                        </tr>
                    `);
                }
            },
            error: function () {
                $('#bonusOrderDetailsContainer').html(`
                    <tr>
                        <td colspan="3" class="text-center text-danger">
                            Something went wrong
                        </td>
                    </tr>
                `);
            }
            });
        });
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
            // {
            //   "orderable": false,
            //   "className": "noExport",
            //   "render": function(data, type, row) {
            //       var checked = selectedRows[row.id] ? 'checked' : '';
            //       return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' +
            //           checked + '>';
            //   }
            // },
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
	 <?php /* {
        "orderable": true,
        "render": function(data, type, row) {
          //console.log(row.bonus_amount);
            if (!row.bonus_amount || row.bonus_amount <= 0) 
            {
              return 'NA';
            }
            const isDisabled = row.disable_bonus_button == 1;

            return `
                <button
                    type="button"
                    class="btn btn-sm ${isDisabled ? 'btn-secondary' : 'btn-primary'}"
                    ${isDisabled ? 'disabled' : 'data-bs-toggle="modal" data-bs-target="#incentiveBonusModal"'}
                    data-id="${row.id}">
                    Claim Bonus
                </button>
            `;
        }
      }, 
	  {
        "orderable": true,
        "render": function(data, type, row) {
            if (!row.bonus_amount || row.bonus_amount <= 0) {
                return 'NA';
            }
            return row.bonus_query_status_name ?? 'NA';
            }
      },*/?>
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
                            <?php /*  <li>
                                <a class="dropdown-item" href="{{ url('bonus-queries') }}/${row.id}">
                                    View Bonus Queries
                                </a>
                            </li> */?>
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

        $('.upload-document').on('change', function() {
            const file = this.files[0];
            if (!file) return;
            var document_category_id = $(this).attr('document_category_id');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            //formData.append('application_id', $('#application_id').val());
            formData.append('document_category_id', document_category_id);
            formData.append('file', file);

            $.ajax({
                url: '{{ url('upload-claim-documents') }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        toastr.success('Document upload successfully');
                           const parts = response.data.claim_document.split('|');
                            const uploadId = parts[1];
                        $('#claim_documents_' + document_category_id).val(uploadId);
                        //$('#upload_file').val('');
                    }
                },
                error: function(xhr, status, error) {
                    //console.error('Upload Error:', error);
                    toastr.error('File upload failed.');
                }
            });
        });
</script>
@endsection
@endsection

