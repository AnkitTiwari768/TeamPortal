@extends('components.admin.content-layout')
@section('card-content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
   .select2-container{
   width:100% !important;
   }
   .select2-container .select2-selection--single{
   height:48px !important;
   border:1px solid #ced4da !important;
   border-radius:6px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__rendered{
   line-height:46px !important;
   padding-left:12px !important;
   }
   .select2-container--default .select2-selection--single .select2-selection__arrow{
   height:46px !important;
   }

   /* ============================================
      BPPID LIST - summary cards
   ============================================ */
   .bppid-list-stat-card{
   display:flex;
   align-items:center;
   gap:16px;
   width:100%;
   height:100%;
   padding:20px;
   border:none;
   border-radius:14px;
   color:#fff;
   box-shadow:0 6px 16px rgba(0,0,0,0.12);
   transition:transform .2s ease, box-shadow .2s ease;
   }
   .bppid-list-stat-card:hover{
   transform:translateY(-3px);
   box-shadow:0 10px 22px rgba(0,0,0,0.18);
   }
   .bppid-list-stat-card__icon{
   display:flex;
   align-items:center;
   justify-content:center;
   flex-shrink:0;
   width:56px;
   height:56px;
   border-radius:50%;
   background:rgba(255,255,255,0.25);
   font-size:22px;
   }
   .bppid-list-stat-card__body{ min-width:0; }
   .bppid-list-stat-card__label{
   margin:0 0 4px 0;
   font-size:14px;
   font-weight:600;
   letter-spacing:.2px;
   opacity:.92;
   text-transform:uppercase;
   }
   .bppid-list-stat-card__value{
   margin:0;
   font-size:28px;
   font-weight:700;
   line-height:1;
   }
   .bppid-list-stat-card--total{ background:linear-gradient(135deg,#4e73f8 0%,#2541b2 100%); }
   .bppid-list-stat-card--mapped{ background:linear-gradient(135deg,#16c79a 0%,#0e8f6f 100%); }
   .bppid-list-stat-card--unmapped{ background:linear-gradient(135deg,#ff9a44 0%,#d9682a 100%); }
   .bppid-list-stat-card--duplicate{ background:linear-gradient(135deg,#ff5f6d 0%,#c0392b 100%); }
   .bppid-list-stat-card--already-updated{ background:linear-gradient(135deg,#b06ab3 0%,#7b3f9e 100%); }

   /* Export buttons */
   .bppid-list-export-actions{ display:flex; gap:10px; flex-wrap:wrap; }
   .bppid-list-export-btn{
   display:inline-flex;
   align-items:center;
   gap:8px;
   padding:8px 16px;
   border:none;
   border-radius:8px;
   color:#fff;
   font-weight:600;
   font-size:14px;
   cursor:pointer;
   transition:opacity .2s ease, transform .2s ease;
   }
   .bppid-list-export-btn:hover{ opacity:.9; transform:translateY(-1px); color:#fff; }
   .bppid-list-export-btn--pdf{ background:linear-gradient(135deg,#ff5f6d 0%,#c0392b 100%); }
   .bppid-list-export-btn--excel{ background:linear-gradient(135deg,#1d976c 0%,#0f6f4e 100%); }
</style>
<div class="card-body">
   <form id="bpp-id-update" enctype="multipart/form-data">
      @csrf
      <div class="row">
         <!-- Dropdown -->
         <div class="col-md-6 mb-3">
            <div class="col-md-6 mb-3">
               <label for="type" class="form-label fw-bold d-block mb-2">
               Select SNP <span class="text-danger">*</span>
               </label>
               <select name="snp_id" id="snp_id" class="form-control select2" required>
                  <option value="">Select SNP id</option>
                  @foreach($snps as $snp)
                  <option value="{{ $snp->snp_id }}">
                     {{ $snp->snp_name }} ({{ $snp->snp_id }})
                  </option>
                  @endforeach
               </select>
            </div>
         </div>
         <!-- File Upload -->
         <div class="col-md-6 mb-3">
            <label class="form-label fw-bold">
            Upload Excel File
            </label>
            <input
               type="file"
               name="file"
               id="file"
               accept=".xls,.xlsx,.xlsm"
               class="form-control"
               required
               >
            <small class="text-primary">
            Excel format:
            <b>udyam_no</b>,
            <b>bpp_id</b>
            </small>
         </div>
      </div>
      <!-- Declaration -->
      <div class="mb-3">
         <div class="p-3 rounded bg-light border">
            <div class="form-check">
               <input
                  class="form-check-input"
                  type="checkbox"
                  id="declaration"
                  required
                  >
               <label class="form-check-label" for="declaration">
               <strong>Declaration :</strong>
               I hereby declare that the information provided above is true and correct.
               </label>
            </div>
         </div>
      </div>
      <!-- Submit Button -->
      <div class="text-end">
         <button
            type="submit"
            class="btn btn-success btn-sm px-4"
            >
         <i class="fa fa-upload"></i>
         Upload File
         </button>
      </div>
   </form>

   <!-- Tracks the most recent upload's cached report (see BppIdUploadReport).
        Empty = the summary cards/table below show the persisted BPPID list;
        once an upload succeeds, its value scopes both to that upload only. -->
   <input type="hidden" id="bppid_report_key_filter" value="">

   <!-- BPP ID Update - Summary Cards (Total / Mapped / Unmapped / Duplicate / Already Updated) -->
   <div class="row bppid-list-cards mt-4 mb-2" id="bppid_list_cards_container">
      <div class="col-lg col-md-4 col-6 mb-3">
         <div class="bppid-list-stat-card bppid-list-stat-card--total">
            <div class="bppid-list-stat-card__icon"><i class="fa fa-list"></i></div>
            <div class="bppid-list-stat-card__body">
               <p class="bppid-list-stat-card__label">Total</p>
               <h3 class="bppid-list-stat-card__value"><span id="bppid_list_total_count">{{ $summary['total'] ?? 0 }}</span></h3>
            </div>
         </div>
      </div>
      <div class="col-lg col-md-4 col-6 mb-3">
         <div class="bppid-list-stat-card bppid-list-stat-card--mapped">
            <div class="bppid-list-stat-card__icon"><i class="fa fa-check-circle"></i></div>
            <div class="bppid-list-stat-card__body">
               <p class="bppid-list-stat-card__label">Mapped</p>
               <h3 class="bppid-list-stat-card__value"><span id="bppid_list_mapped_count">{{ $summary['mapped'] ?? 0 }}</span></h3>
            </div>
         </div>
      </div>
      <div class="col-lg col-md-4 col-6 mb-3">
         <div class="bppid-list-stat-card bppid-list-stat-card--unmapped">
            <div class="bppid-list-stat-card__icon"><i class="fa fa-times-circle"></i></div>
            <div class="bppid-list-stat-card__body">
               <p class="bppid-list-stat-card__label">Unmapped</p>
               <h3 class="bppid-list-stat-card__value"><span id="bppid_list_unmapped_count">{{ $summary['unmapped'] ?? 0 }}</span></h3>
            </div>
         </div>
      </div>
      <div class="col-lg col-md-4 col-6 mb-3">
         <div class="bppid-list-stat-card bppid-list-stat-card--duplicate">
            <div class="bppid-list-stat-card__icon"><i class="fa fa-clone"></i></div>
            <div class="bppid-list-stat-card__body">
               <p class="bppid-list-stat-card__label">Duplicate</p>
               <h3 class="bppid-list-stat-card__value"><span id="bppid_list_duplicate_count">{{ $summary['duplicate'] ?? 0 }}</span></h3>
            </div>
         </div>
      </div>
      <div class="col-lg col-md-4 col-6 mb-3">
         <div class="bppid-list-stat-card bppid-list-stat-card--already-updated">
            <div class="bppid-list-stat-card__icon"><i class="fa fa-history"></i></div>
            <div class="bppid-list-stat-card__body">
               <p class="bppid-list-stat-card__label">Already Updated</p>
               <h3 class="bppid-list-stat-card__value"><span id="bppid_list_already_updated_count">{{ $summary['already_updated'] ?? 0 }}</span></h3>
            </div>
         </div>
      </div>
   </div>

   <!-- BPP ID Update - Export Actions -->
   <div class="d-flex flex-column align-items-end mb-3 bppid-list-export-actions">
      <div class="d-flex gap-2">
         <button type="button" class="bppid-list-export-btn bppid-list-export-btn--excel" id="bppid_list_export_excel">
            <i class="fa fa-file-excel-o"></i> Download Excel
         </button>
         <button type="button" class="bppid-list-export-btn bppid-list-export-btn--pdf" id="bppid_list_export_pdf">
            <i class="fa fa-file-pdf-o"></i> Download PDF
         </button>
      </div>
      <small class="text-muted mt-1">Excel contains all records. PDF is limited to the first 1000 records due to file size.</small>
   </div>

   <!-- BPP ID Update - DataTable -->
   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="bppIdUpdateDataTable" width="100%" cellspacing="0">
         <thead>
            <tr>
               <th>S.No.</th>
               <th>Old BPPID</th>
               <th>New BPPID</th>
               <th>Updated At</th>
               <th>Udyam No.</th>
            </tr>
         </thead>
         <tbody>
            <!-- Dynamic rows will be populated by DataTable -->
         </tbody>
      </table>
   </div>
</div>
<!-- Processing Modal -->
<div class="modal fade"
   id="processingModal"
   tabindex="-1"
   data-bs-backdrop="static"
   data-bs-keyboard="false">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-body text-center p-5">
            <h5 class="mb-4">
               Processing...
            </h5>
            <div class="loader"></div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('js')
<!-- jQuery is already loaded globally by the admin layout (header.blade.php),
     along with DataTables and jQuery UI's datepicker (layout.blade.php,
     before @yield('js') runs). Re-declaring jQuery here would overwrite
     window.$ with a plugin-less instance and break both of those. -->
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!--
    BPP ID Update - upload form + Summary Cards/DataTable/Excel/PDF downloads
    for the resulting list. A single script so the upload's success handler
    can drive the same cards/table shown below it: on success it stores the
    upload's report_key in the hidden #bppid_report_key_filter field, which
    scopes both the cards (summary-cards?report_key=...) and the DataTable
    (via its "filters" option) to that upload's complete data - including
    unmapped rows that were never saved to the database. Clearing/reloading
    the page resets the field, so the default view is always the persisted
    BPPID list.
-->
<script>
   $(document).ready(function () {

       // Select2 Initialize
       $('.select2').select2({
           placeholder: 'Search & Select',
           width: '100%'
       });

       // Toastr Config
       toastr.options = {
           closeButton: true,
           progressBar: true,
           positionClass: "toast-top-right",
           timeOut: 3000,
           extendedTimeOut: 1000,
           preventDuplicates: true
       };

       function bppidUpdateCards(summary) {
           summary = summary || {};
           $('#bppid_list_total_count').text(summary.total || 0);
           $('#bppid_list_mapped_count').text(summary.mapped || 0);
           $('#bppid_list_unmapped_count').text(summary.unmapped || 0);
           $('#bppid_list_duplicate_count').text(summary.duplicate || 0);
           $('#bppid_list_already_updated_count').text(summary.already_updated || 0);
       }

       var bppidListTable = dataTableInit({
           id: "#bppIdUpdateDataTable",
           order: {
               column: 3,
               direction: "desc"
           },
           url: "{{ url('bppid-update/datalist') }}",
           filters: ["bppid_report_key_filter"],
           columns: [
               {
                   "orderable": false,
                   "render": function (data, type, full, meta) {
                       var table = $('#bppIdUpdateDataTable').DataTable();
                       var pageInfo = table.page.info();
                       return pageInfo.start + meta.row + 1;
                   }
               },
               {
                   "orderable": false,
                   "render": function (data, type, row) {
                       return row.old_bpp_id || "-";
                   }
               },
               {
                   "orderable": true,
                   "render": function (data, type, row) {
                       return row.new_bpp_id || "-";
                   }
               },
               {
                   "orderable": true,
                   "render": function (data, type, row) {
                       return row.updated_at || "-";
                   }
               },
               {
                   "orderable": true,
                   "render": function (data, type, row) {
                       return row.udyam_no || "-";
                   }
               }
           ],
           error: function (settings, techNote, message) {
               console.error('BPPID DataTable Error:', message);
           }
       });

       // Excel/PDF downloads: while a report_key is active (right after an
       // upload) they hit the upload-scoped "all" download so the file
       // contains exactly the just-uploaded batch; otherwise they hit the
       // persisted-list export. Both always contain the complete dataset,
       // not just the visible/paginated rows.
       $('#bppid_list_export_excel').on('click', function () {
           var reportKey = $('#bppid_report_key_filter').val();
           window.location.href = reportKey
               ? "{{ url('bppid-update/download') }}/" + reportKey + "/all/xlsx"
               : "{{ url('bppid-update/export-excel') }}";
       });

       $('#bppid_list_export_pdf').on('click', function () {
           var reportKey = $('#bppid_report_key_filter').val();
           window.location.href = reportKey
               ? "{{ url('bppid-update/download') }}/" + reportKey + "/all/pdf"
               : "{{ url('bppid-update/export-pdf') }}";
       });

       // Form Submit
       $('#bpp-id-update').on('submit', function (e) {

           e.preventDefault();

           let formData = new FormData(this);

           $('#processingModal').modal('show');

           $.ajax({
               url: "{{ url('bulk-update-bpp-id') }}",
               type: "POST",
               data: formData,
               processData: false,
               contentType: false,

               success: function (response) {

                   $('#processingModal').modal('hide');

                   if (response.status) {

                       toastr.success(
                           response.message || 'Bulk BPP ID Update Successful'
                       );

                       var data = response.data || {};

                       if (data.report_key) {
                           $('#bppid_report_key_filter').val(data.report_key);
                           bppidUpdateCards(data.summary || {});

                           if (bppidListTable) {
                               bppidListTable.ajax.reload(null, true);
                           }
                       }

                   } else {

                       toastr.error(
                           response.message || 'Something went wrong'
                       );

                   }

               },

               error: function (xhr) {

                   $('#processingModal').modal('hide');

                   let message = 'Something went wrong';

                   if (
                       xhr.responseJSON &&
                       xhr.responseJSON.message
                   ) {
                       message = xhr.responseJSON.message;
                   }

                   toastr.error(message);

               }
           });

       });

   });
</script>
@endsection
