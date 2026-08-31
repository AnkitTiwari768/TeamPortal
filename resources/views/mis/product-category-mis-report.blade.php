@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body table-loading-container">
<!-- Modern Table Loader Overlay -->
<div id="table-loader">
   <div class="text-center">
      <div class="modern-spinner mx-auto"></div>
      <div class="loading-text">Loading Product Category records...</div>
   </div>
</div>

<div class="card-body">
   <form id="pcmr_filter_form" autocomplete="off">
      <div class="row">
         <div class="col-lg-3 col-md-4 mb-3">
            <div class="select-box">
               <label class="form-label">SNP Approved</label>
               <select class="form-select custom-filter pcmr_filter_btn" id="snp_approved_filter">
                  <option value="">All</option>
               </select>
            </div>
         </div>
      </div>
   </form>
</div>

<div class="row pcmr-summary-cards mt-2 mb-4" id="pcmr_summary_cards">
   <div class="col-lg-4 col-md-6 mb-3">
      <div class="pcmr-stat-card pcmr-stat-card--open">
         <div class="pcmr-stat-card__icon">
            <i class="fas fa-store"></i>
         </div>
         <div class="pcmr-stat-card__body">
            <p class="pcmr-stat-card__label">Open MSME Count</p>
            <h3 class="pcmr-stat-card__value"><span id="pcmr_open_msme_count">0</span></h3>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-md-6 mb-3">
      <div class="pcmr-stat-card pcmr-stat-card--direct">
         <div class="pcmr-stat-card__icon">
            <i class="fas fa-hand-pointer"></i>
         </div>
         <div class="pcmr-stat-card__body">
            <p class="pcmr-stat-card__label">Direct Selection MSME</p>
            <h3 class="pcmr-stat-card__value"><span id="pcmr_direct_selection_msme_count">0</span></h3>
         </div>
      </div>
   </div>
   <div class="col-lg-4 col-md-6 mb-3">
      <div class="pcmr-stat-card pcmr-stat-card--onboard">
         <div class="pcmr-stat-card__icon">
            <i class="fas fa-check-circle"></i>
         </div>
         <div class="pcmr-stat-card__body">
            <p class="pcmr-stat-card__label">Onboard MSME</p>
            <h3 class="pcmr-stat-card__value"><span id="pcmr_onboarded_msme_count">0</span></h3>
         </div>
      </div>
   </div>
</div>

<div class="card-body">
   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover"
         id="dataTable"
         width="100%"
         cellspacing="0">
         <thead>
            <tr>
               <th>S.No</th>
               <th>Product Category</th>
               <th>Open MSEs</th>
               <th>Direct MSEs</th>
               <th>Onboarded MSEs</th>
            </tr>
         </thead>
         <tbody></tbody>
      </table>
   </div>
</div>
</div>
@endsection
@section('js')
@include('mis.common-css')
<style>
   .pcmr-stat-card {
      display: flex;
      align-items: center;
      gap: 16px;
      background: #fff;
      border-radius: 12px;
      padding: 18px 20px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      height: 100%;
   }
   .pcmr-stat-card__icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 52px;
      height: 52px;
      min-width: 52px;
      border-radius: 50%;
      font-size: 20px;
      color: #fff;
   }
   .pcmr-stat-card--open .pcmr-stat-card__icon { background: #0d6efd; }
   .pcmr-stat-card--direct .pcmr-stat-card__icon { background: #fd7e14; }
   .pcmr-stat-card--onboard .pcmr-stat-card__icon { background: #198754; }
   .pcmr-stat-card__label {
      margin: 0;
      font-size: 14px;
      color: #6c757d;
      font-weight: 500;
   }
   .pcmr-stat-card__value {
      margin: 4px 0 0;
      font-size: 26px;
      font-weight: 700;
      color: #212529;
   }
</style>
<script>
   $(document).ready(function () {
       var snpOptionsLoaded = false;

       function populateSnpApprovedOptions(snpOptions) {
           if (snpOptionsLoaded || !Array.isArray(snpOptions)) {
               return;
           }
           snpOptionsLoaded = true;

           var $select = $('#snp_approved_filter');
           $.each(snpOptions, function (i, snp) {
               $select.append($('<option>', {
                   value: snp.id,
                   text: snp.label
               }));
           });
       }

       function updateSummaryCards(summary) {
           summary = summary || {};
           $('#pcmr_open_msme_count').text(summary.open_msme || 0);
           $('#pcmr_direct_selection_msme_count').text(summary.direct_selection_msme || 0);
           $('#pcmr_onboarded_msme_count').text(summary.onboarded_msme || 0);
       }

       loadData();

       function loadData()
       {

           $('#dataTable').DataTable({
               processing: true,
               serverSide: false,
               destroy: true,
               responsive: true,
               pageLength: 10,
               lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
               ajax: {
                   url: "{{ url('product-category-mis-report/datalist') }}",
                   type: "GET",
                   data: function (d) {
                       d.snp_id = $('#snp_approved_filter').val();
                   },
                   dataSrc: function (json) {
                       var payload = json.data || {};
                       populateSnpApprovedOptions(payload.snp_options);
                       updateSummaryCards(payload.summary);
                       return payload.rows || [];
                   }
                  },
                  columns: [
                   {
                    data: null,
                    title: "#",
                    orderable: false,
                    searchable: false,
                    className: "text-center",
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                   },

                   {
                       data: 'product_category',
                       defaultContent: '-'
                   },

                   {
                       data: 'open_msme',
                       defaultContent: '0'
                   },

                   {
                       data: 'direct_selection_msme',
                       defaultContent: '0'
                   },

                   {
                       data: 'onboarded_msme',
                       defaultContent: '0'
                   },



               ],


               dom: '<"d-flex justify-content-between align-items-center mb-3"lBf>rtip',

               buttons: [

                   {
                       extend: 'excel',

                       text: '<i class="fa-solid fa-file-excel me-2"></i> Excel',

                       className: 'buttons-excel'
                   },

                   {
                       extend: 'pdf',

                       text: '<i class="fa-solid fa-file-pdf me-2"></i> PDF',

                       className: 'buttons-pdf'
                   }

               ]

           });

       }

       $('#snp_approved_filter').on('change', function () {
           $('#dataTable').DataTable().ajax.reload();
       });

       // Show/hide custom loader based on DataTable processing status
       $('#dataTable').on('processing.dt', function(e, settings, processing) {
           if (processing) {
               $('#table-loader').css('display', 'flex');
           } else {
               $('#table-loader').css('display', 'none');
           }
       });
   });
</script>
@endsection
