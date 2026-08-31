@extends('components.admin.content-layout')

@section('card-content') 
		 
		<div class="card-body">
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
		
		@if (hasRole('snp'))
            <div class="card-header d-flex">
                <div class="action-header ms-auto">
                    <div class="btn-group drop-btn">
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#myPdfModal">
                            <img src="{{ asset('assets/img-new/add.svg') }}">
                            Add Bulk Catalogue
                        </button>
                    </div>
                </div>
            </div>
        @endif

		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
					<thead>
						<tr>
							<th>{{ __('message.sn') }}</th>
							<th>TEAMID</th>
							<th>Udyam</th>
							<th>Mobile</th>
							<th>Email</th>
							<th>Name of entrepreneur</th>
							<th>Name of enterprise</th>
							<th>Enterprise Type </th>
							<th>Date </th>
							<th class="actions">{{ __('message.action') }}</th>
						</tr>
					</thead>
				</table>
			</div>
		</div> 
		
	<!-- Modal for bulk catalogue upload -->
    <div class="modal fade" id="myPdfModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Bulk Catalogue</h5>
                    </div>
					
					<a href="{{ url('storage/app/download_format/catalogue.zip') }}" class="btn btn-sm btn-outline-primary">Download Sample Pdf Format</a>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="catalogue-bulk-upload">
                    @csrf
                    <div class="modal-body">
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
		
    
    <!-- Modal for single catalogue upload -->
    <div class="modal fade" id="myCatalogueModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-0">Add Catalogue</h5>
                    </div>

                    <div class="flex-grow-1 text-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="catalogue-form">
                    @csrf
                    <input type="hidden" name="msme_id" id="msme_id">
                    <div class="modal-body">
                      <div class="col-md-4">
                        <div class="input-box">
                            <label class="form-label">Upload Document</label><a class="tooltip-ins"  href="#" data-toggle="tooltip" title="File must be of pdf and less than 10mb"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
                            
                            <input type="file" class="form-control" id="upload"/>
                            <div class="upload_file_link">
                                
                            </div>
                            <span class="text-danger form-error" id="catalogue_error"></span>
                            <input type="hidden" name="upload_document" id="upload_document" /> 
                            <div class="progress-upload progress-bg" style="display:none;">
                                <div id="loader-upload" style=""></div>
                                <div class="progress-bar"></div>
                            </div> 
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@section('js');
<script> 
//for bulk catalogue upload pdf
	$('#myPdfModal').on('hidden.bs.modal', function () {
		$('#csv-errors').html('');
		$('#file').val('');
	});
	
	$(document).on('change', '#catalogue-bulk-upload', function(e) {
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



	$(document).on('submit', '#catalogue-bulk-upload', function(e) {
		e.preventDefault();
		
		document.getElementById('csv-errors').innerHTML = '';

		var form = document.getElementById('catalogue-bulk-upload');
		var formData = new FormData(form);
		
		var fileInput = document.getElementById('file');
		if (!fileInput.files.length) {
			toastr.error("Please select a file to upload.");
			return;
		}

		$.ajax({
			url: "{{ url('catalogue-bulk-import') }}",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			beforeSend: function() {
				$("#ajax-loader").show();
			},
			success: function(res) {
				toastr.success(res.message);
				 $("#csv-errors").html(res.message);
				setTimeout(function() {
					window.location.href = "{{ url('ready-for-catalogue-creation') }}";
				}, 5000);
			},
			error: function(xhr) {
				console.log(xhr.responseJSON.message);
				if (xhr.responseJSON && xhr.responseJSON.message) {
					toastr.error(xhr.responseJSON.message);
					$("#csv-errors").html(xhr.responseJSON.message);
				} else {
					toastr.error("Something went wrong.");
				}
			},
			complete: function() {
				$("#ajax-loader").hide();
			}
		});
	});
	
//for single catalogue upload pdf
	  $('#myCatalogueModal').on('show.bs.modal', function (event) {
		  var button = $(event.relatedTarget); // Button/link that triggered modal
		  var msme_id = button.attr('msme-id'); // Or button.data('id')
		  $('#msme_id').val(msme_id);
	  });

	$("#upload").on("change", function() {
		fileUploadWithLoader({
		  url: "{{url('upload-catalogue')}}",
		  selector: "#upload",
		  fileFieldName: 'file',
		  hiddenInputSelector: '#upload_document',
		  progressElementSelector: '.progress-upload',
		  loaderElementSelector: '#loader-upload',
		  loadingContent: 'uploading...',
		  successMessage: 'Successfully uploaded.',
		  errorMessage: 'Invalid file type',
		  showFileName:'.upload_file_link'
		});
	});

	function deleteFile(documentId, type) {
		if (confirm('Do you really want to delete?')) {
			$.ajax({
				url: "{{ url('/delete-catalogue') }}",
				method: "POST",
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				data: { id: documentId, document_type: type },
				success: function (response) {
          console.log(response);
					if (response == true) {
						toastr.success('Document has been deleted.');
						$("." + type + "_file_link").html('');
						$("#" + type + "_document").val('');
						$("#" + type).val('');
					} else {
						toastr.error('Something went wrong.');
					}
				}
			});
		}
	}

  $(document).on('submit', '#catalogue-form', function(e) {
    e.preventDefault();
    var msme_id = document.getElementById('msme_id').value;
    var catalogue = document.getElementById('upload_document').value;

    
    if (catalogue == '') {
        toastr.error("Please upload document.");
        return;
    }

   $('#catalogue_error').text('');

    $.ajax({
        url: "{{ url('save-catalogue') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            msme_id: msme_id,
            catalogue: catalogue
        },
        success: function(res) { 
            toastr.success('Catalogue Updated Successfully.');
            setTimeout(function() {
                window.location.href = "{{ url('ready-for-catalogue-creation') }}";
            }, 2000);
        },
        error: function(xhr) {
            if (xhr.status === 422) { 
                // Laravel validation error
                let errors = xhr.responseJSON.errors;
                if (errors.catalogue) {
                    $('#catalogue_error').text(errors.catalogue[0]); // show error under field
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error("Something went wrong.");
            }
        }
    });
  });

  dataTableInit({
    id: "#dataTable",
	showExcelExport: true,
    order: {
      column: 1,
      direction: "asc"
    },
    url: "{{ url('ready-for-catalogue-creation/datalist') }}",
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
        "orderable": false,
			"render": function (data, type, row) {
			var pview = '';
      
      var catealog_creation = '<a href="javascript:void(0)" msme-id="' + row.id + '" data-bs-toggle="modal" data-bs-target="#myCatalogueModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-upload"></i></a>';

      var actions = createActionButtons([pview, catealog_creation]);
      return actions;
			  }
        }
		
    ],
	filters: ["from_date","to_date"]
  });
  

</script>
@endsection
@endsection

