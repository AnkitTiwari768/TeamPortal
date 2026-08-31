@extends('components.admin.content-layout')

@section('card-content')
   

    <div class="card-body">

	 <div class="alert alert-info d-flex justify-content-between align-items-center">
    
    <div>
        <ul style="margin-bottom: 0;">
            <li>For Successfully uploaded data, Please click on the Draft List</li>
        </ul>
    </div>

    <div class="d-flex gap-2">
		 @if(acl('bulk-view'))
       <a href="{{ route('msme-draft-list') }}" class="btn btn-success">
			<i class="fa fa-list me-1"></i> Draft MSE List
		</a> 
		@endif

           <!-- Predefined File Format Button -->
    <a href="{{ url('storage/app/download_format/MSME-Bulk-Registration-Udyam.xlsm') }}"
       class="btn btn-outline-primary">
       <i class="fa fa-download"></i> Predefined File Format
    </a>
    </div>


</div>
   <div class="alert alert-info">
    <b>Note:</b> 
    <ul style="margin-bottom: 0;">
        <li>Bulk upload allows a maximum of <b>5000 MSE entries</b> per upload. Please ensure the file does not exceed this limit.</li>
        <li>While saving and uploading the file in Excel, please ensure that the file type is selected as <b>Excel Macro-Enabled Workbook (*.xlsm)</b>.</li>
    </ul>
</div>
    <form id="bulk-upload-draft" enctype="multipart/form-data" method="POST">
        @csrf
        <div class="mb-3">
            <input type="file" name="file" id="file" accept=".xlsm" class="form-control">
            <small class="text-primary">
                Please ensure the file is in the correct format by downloading the sample format.
            </small>
        </div>

        <div id="csv-errors" class="text-danger mb-3" style="font-family: Arial; padding: 10px;"></div>

		  <!-- Import Summary Box -->
        <div id="import-summary" class="mb-3"></div>
	
		  <!-- Declaration Section (Screenshot Design) -->
        <div class="mb-3">
            <div class="p-3 rounded"
                 style="background-color:#f1f1f1; border:1px solid #ddd;">

                <div class="form-check d-flex align-items-start">

                    <input class="form-check-input mt-1 me-2"
                           type="checkbox"
                           name="declaration"
                           id="declaration"
                           value="1"
                           required>

                    <label class="form-check-label" for="declaration">

                        <strong>Declaration:</strong>

                        <div style="color:#444;margin-top:4px;line-height:1.6;text-align:justify;">
                          I / We hereby declare that the MSE data being uploaded in bulk on the TEAM Portal for the purpose of registration of MSEs under the TEAM Scheme has been collected with due consent from the respective MSEs and that the details furnished are true and correct to the best of my / our knowledge and belief. I / We confirm that the upload is being carried out in my / our capacity as a Seller Network Participant (SNP) / Industry Association / Other Associate Organization.
							If any information is found to be incorrect / misleading / false, appropriate action as per applicable laws may be taken. I / We hereby confirm that consent has been obtained from all the MSEs whose data is being uploaded in bulk, authorizing NSIC / ONDC to use, verify, and share the relevant details for the purpose of scheme administration and support, in compliance with applicable laws.
							I / We hereby declare that I / We and all the MSEs whose data is being uploaded have read and agree to abide by the Privacy Policy, Terms and Conditions, Disclaimer, Data Sharing Policy, Operating Guidelines and SOPs of the TEAM Initiative. NSIC reserves the right to change / amend the SOPs with the approval of the Ministry of MSME as per the policy & procedural requirement as and when warranted and without giving any notice.
						</div>

                    </label>

                </div>

            </div>

        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success btn-sm px-4">
            <i class="fa fa-upload"></i> Upload File
            </button>
         </div>
    </form>

</div>

@section('js')
 <script>
	$(document).on('change', '#file', function(e) {
		let fileName = this.files[0]?.name;

		if (fileName) {
			let ext = fileName.split('.').pop().toLowerCase();

			if (ext !== 'xlsm') {
				alert("Only .xlsm files are allowed!");
				$('#file').val('');
			}
		}
	});

	$(document).on('submit', '#bulk-upload-draft', function(e) {
		e.preventDefault();
		document.getElementById('csv-errors').innerHTML = '';

		var form = document.getElementById('bulk-upload-draft');
		var formData = new FormData(form);
		

		var fileInput = document.getElementById('file');
		if (!fileInput.files.length) {
			toastr.error("Please select a file to upload.");
			return;
		}

		$.ajax({
			url: "{{ route('msme-bulk-draft-import') }}",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			beforeSend: function() {
				$("#ajax-loader").show();
			},
		success: function(res) {

			if(res.status === 'success') {
				   toastr.success("MSE Bulk Registration successfully!");
       			 // ✅ Reset form (file blank ho jayega)
        		$('#bulk-upload-draft')[0].reset();
				let summaryHtml = `
					<div class="alert alert-success alert-dismissible fade show"
						role="alert"
						id="import-summary-alert">
						<button type="button"
								class="btn-close"
								data-bs-dismiss="alert">
						</button>
						<h5 class="mb-2">Import Summary</h5> 
						<hr>

						<p class="mb-1">
							<strong>Draft Inserted:</strong> ${res.draft_count}
						</p>

						<p class="mb-1">
							<strong>Temp (Duplicate & Error):</strong> ${res.temp_count}
						</p>

					
				`;

				// ✅ Agar failed rows hain to ek hi download button show karein
				if(res.failed_count > 0) {
					summaryHtml += `
						<hr>
						<a href="{{ route('mse.download.failed.all') }}" 
						class="btn btn-danger btn-sm">
						Download Failed Records
						</a>
					`;
				}

				summaryHtml += `</div>`;

				$("#import-summary").html(summaryHtml);

				// ✅ Auto close after 10 seconds
				setTimeout(function() {
					$("#import-summary-alert").alert('close');
				}, 120000);
			}
		},
			// error: function(xhr) {
			// 	if (xhr.responseJSON && xhr.responseJSON.message) {
			// 		toastr.error(xhr.responseJSON.message);
			// 		displayCsvErrors(xhr.responseJSON);
			// 	} else {
			// 		toastr.error("Something went wrong.");
			// 		document.getElementById('csv-errors').innerHTML = '';
			// 	}
			// },
			error: function(xhr) {
			if (xhr.responseJSON && xhr.responseJSON.message) {
				let msg = xhr.responseJSON.message;
				let formattedMsg = msg.replace(/\n/g, "<br>");
				let alertHtml = `
					<div class="alert alert-danger alert-dismissible fade show" role="alert" id="import-error-alert">
						<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
						${formattedMsg}
					</div>`;
				$("#csv-errors").html(alertHtml);
				toastr.error("Import Failed");
				// Auto close after 5 seconds
				setTimeout(function() {
					$("#import-error-alert").alert('close');
				}, 100000);
			} else {
				toastr.error("Something went wrong.");
				$("#csv-errors").html("");
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
				errorHtml += `<li><strong>Row ${cell}</strong>: ${msg}</li>`;
			});
		}

		errorHtml += `</ul>`;
		document.getElementById('csv-errors').innerHTML = errorHtml;
	}


    </script>
@endsection
@endsection
