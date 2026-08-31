@extends('components.admin.content-layout')
@section('card-content')
<style>
   .loader{
   border:8px solid #f3f3f3;
   border-top:8px solid #3498db;
   border-right:8px solid #f1c40f;
   border-bottom:8px solid #e74c3c;
   border-left:8px solid #2ecc71;
   border-radius:50%;
   width:70px;
   height:70px;
   animation:spin 1s linear infinite;
   margin:auto;
   }
   @keyframes spin{
   0%{transform:rotate(0deg);}
   100%{transform:rotate(360deg);}
   }
</style>
    <div class="card-body">
       <div class="alert alert-info d-flex justify-content-between align-items-center">
    
    <div>
        <ul style="margin-bottom: 0;">
            <li>For Successfully uploaded data, Please click on the PMV List.</li>
        </ul>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('pmvList') }}" class="btn btn-success btn-sm">
            <i class="fa fa-list me-1"></i> PMV List
        </a> 

        <!-- <a href="{{ url('storage/app/download_format/PMV_Bulk_Upload.xlsm') }}"
           class="btn btn-outline-primary btn-sm">
            <i class="fa fa-download"></i> Predefined File Format
        </a> -->

            <a href="{{ route('download.pmv.format') }}"
            class="btn btn-outline-primary btn-sm">
                <i class="fa fa-download"></i> Predefined File Format
            </a>    
        </div>

</div>
    <div class="alert alert-info">
        <b>Note:</b> 
        <ul style="margin-bottom: 0;">
            <li>Bulk upload allows a maximum of <b>5000 PMV entries</b> per upload. Please ensure the file does not exceed this limit.</li>
            <li>While saving and uploading the file in Excel, please ensure that the file type is selected as <b>Excel Macro-Enabled Workbook (*.xlsm)</b>.</li>
        </ul>
    </div>
   <form id="pmv-bulk-upload" enctype="multipart/form-data" method="POST">
      @csrf
      <div class="mb-3">
            <input type="file" name="file" id="file" accept=".xlsm" class="form-control">
            <small class="text-primary">Please ensure the file is in the correct format by downloading the sample format.</small>
      </div>
      <div id="csv-errors" class="text-danger mb-3"></div>
      <div id="import-summary" class="mb-3"></div>
      
      <!-- Declaration Section (Dummy Design) -->
    <div class="mb-3">
        <div class="p-3 rounded"
            style="background-color:#f1f1f1; border:1px solid #ddd;">
            <div class="form-check d-flex align-items-start">
                <input class="form-check-input mt-1 me-2" type="checkbox" name="declaration" id="declaration" value="1" required>
                <label class="form-check-label" for="declaration">
                <strong>Declaration:</strong>
                    <div style="color:#444;margin-top:4px;line-height:1.6;text-align:justify;">
                       I hereby declare that the information provided above is true and correct to the best of my knowledge and belief.
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
<!-- Processing Loader Modal -->
  <div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <h5 class="mb-4">Processing...</h5>
                    <div class="loader"></div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
<script>
    let processingModal; // ✅ global modal instance

    $(document).ready(function () {
        const modalEl = document.getElementById('processingModal');
        processingModal = new bootstrap.Modal(modalEl);
    });

    $(document).on('change', '#file', function() {
        let fileName = this.files[0]?.name;
        if (fileName) {
            let ext = fileName.split('.').pop().toLowerCase();
            if (ext !== 'xlsm') {
                alert("Only .xlsm files allowed");
                $('#file').val('');
            }
        }
    });

    $(document).on('submit', '#pmv-bulk-upload', function(e) {
        e.preventDefault();

        $("#csv-errors").html('');
        var formData = new FormData(this);
        var fileInput = document.getElementById('file');

        if (!fileInput.files.length) {
            toastr.error("Please select file");
            return;
        }

        $.ajax({
            url: "{{ url('pmv-bulk-registation-import') }}",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,

            // ✅ SHOW loader
            beforeSend: function() {
                processingModal.show();
            },

            success: function(res) {
                if (res.status === 'success') {
                    toastr.success("PMV Bulk Registration successfully!");
                    $('#pmv-bulk-upload')[0].reset();

                    let summaryHtml = `
                    <div class="alert alert-success alert-dismissible fade show mb-2 p-3" role="alert" id="import-summary-alert">                
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <h5 class="mb-2">Import Summary</h5>
                        <hr class="my-2">
                        <p><strong>Total Rows:</strong> ${res.total_count}</p>
                        <p><strong>Inserted:</strong> ${res.success_count}</p>
                        <p><strong>Error Rows:</strong> ${res.failed_count}</p>
                    `;

                    if (res.failed_count > 0) {
                        summaryHtml += `
                        <a href="{{ route('pmv.download.failed.all') }}" class="btn btn-danger btn-sm">
                            Download Failed Records
                        </a>`;
                    }

                    summaryHtml += `</div>`;
                    $("#import-summary").html(summaryHtml);
                      // ✅ AUTO CLOSE AFTER 1 MINUTE (25000 ms)
                    setTimeout(function() {
                        $('#import-summary-alert').alert('close');
                    }, 25000);
                }
            },

            error: function(xhr) {
            $("#processingModal").modal('hide');
            if (xhr.responseJSON && xhr.responseJSON.message) {
                let msg = xhr.responseJSON.message.replace(/\n/g, "<br>");
                let alertHtml = `<div class="alert alert-danger alert-dismissible fade show">
                 <button type="button" class="btn-close" data-bs-dismiss="alert"></button> ${msg}</div>`;
                $("#csv-errors").html(alertHtml);
                toastr.error("Import Failed");
            } else {
                toastr.error("Something went wrong");
            }
        },

            complete: function() {
                // ✅ FORCE CLOSE (important)
                processingModal.hide();

                // 🔥 fallback (agar phir bhi stuck ho)
                setTimeout(() => {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('#processingModal').removeClass('show').hide();
                }, 300);
            }
        });
    });
</script>
@endsection