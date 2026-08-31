@extends('components.admin.content-layout')

@section('card-content')

<div class="card shadow-sm">
   <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
         <h4 class="mb-0">MSME Bulk Registration (Udyam)</h4>
         <a href="{{ url('storage/app/download_format/MSME-Bulk-Registration-Udyam.xlsm') }}"
            class="btn btn-outline-primary btn-sm">
         <i class="fa fa-download"></i> Download Sample Format
         </a>
      </div>
      <div class="alert alert-info">
         <b>Note:</b> Bulk upload allows a maximum of <b>500 MSE entries</b> per upload.
         Please ensure the file does not exceed this limit.
      </div>
      <form id="bulk-upload" enctype="multipart/form-data" method="POST">
         @csrf
         <div class="mb-3">
            <label class="form-label"><b>Select Excel File</b></label>
            <input type="file"
               name="file"
               id="file"
               accept=".xlsm"
               class="form-control">
            <small class="text-muted">
            Only <b>.xlsm</b> files are allowed. Please download the sample format before uploading.
            </small>
         </div>
         <div id="csv-errors"></div>
         <!-- Declaration -->
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
</div>

@endsection


@section('js')
<script>

    $(document).on('change', '#file', function(e) {
        let fileName = e.target.files[0]?.name;
        if (fileName) {
            let ext = fileName.split('.').pop().toLowerCase();
            if (ext !== 'xlsm') {
                toastr.error("Only .xlsm files are allowed!");
                $('#file').val('');
            }

        }

    });



$(document).on('submit', '#bulk-upload', function(e) {
    e.preventDefault();
    $("#csv-errors").html('');
    var form = document.getElementById('bulk-upload');
    var formData = new FormData(form);
    var fileInput = document.getElementById('file');
    if (!fileInput.files.length) {
        toastr.error("Please select a file to upload.");
        return;
    }


    $.ajax({
        url: "{{ route('msme-bulk-import-udyam') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,

        beforeSend: function() {

            $("#ajax-loader").show();

        },

        success: function(res) {

            if (res.status != 'api_not_working') {
                let html = `
				<div class="card mt-3 shadow-sm">
				<div class="card-header py-2 bg-primary text-white">
				Import Summary
				</div>
				<div class="card-body py-2">
				<div class="row text-center g-2">
				<div class="col-md-4">
				<div class="border rounded py-2">
				<div class="text-success fw-bold">Success</div>
				<h5 class="mb-0">${res.success}</h5>
				</div>
				</div>


				<div class="col-md-4">
				<div class="border rounded py-2">
				<div class="text-warning fw-bold">Draft</div>
				<h5 class="mb-0">${res.draft}</h5>
				</div>
				</div>


				<div class="col-md-4">
				<div class="border rounded py-2">
				<div class="text-danger fw-bold">Failed</div>
				<h5 class="mb-0">${res.failed}</h5>
				</div>

				</div>
				</div>


				<div class="text-center mt-2">
				<a href="{{ url('msme-bulk-export') }}" class="btn btn-danger btn-sm">
				<i class="fa fa-download"></i> Download Failed Records
				</a>

				</div>
				</div>
				</div>
				`;


                $("#csv-errors").html(html);
                $('#file').val('');
                toastr.success(res.message);

            } else {
				$("#csv-errors").html(res.message);
				  $('#file').val('');
                toastr.error(res.message);
            }

        },

        complete: function() {
            $("#ajax-loader").hide();

        }

    });

});

</script>

@endsection