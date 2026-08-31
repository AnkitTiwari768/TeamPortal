@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body table-loading-container">
<!-- Modern Table Loader Overlay -->
<div id="table-loader">
   <div class="text-center">
      <div class="modern-spinner mx-auto"></div>
      <div class="loading-text">Loading SNP records...</div>
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
               <th>S.No.</th>
               <th>SNP ID</th>
               <th>SNP Organisation Name</th>
               <th>Open MSEs</th>
               <th>Direct MSEs</th>
               <th>Onboarded MSEs</th>
            </tr>
         </thead>
         <tbody></tbody>
         <tfoot>
            <tr>
               <th colspan="3" class="text-end">Total</th>
               <th class="text-center" id="totalOpenMsme">0</th>
               <th class="text-center" id="totalSelectedMsme">0</th>
               <th class="text-center" id="totalOnboardedMsme">0</th>
            </tr>
         </tfoot>
      </table>
   </div>
</div>
@endsection
@section('js')
@include('mis.common-css')
<style>
    #dataTable_wrapper div.dt-buttons {
        margin-left: 0;
    }
</style>
<script>
   $(document).ready(function () {   
       loadData();   
       function loadData(tier = '')
       {
           table = $('#dataTable').DataTable({   
               processing: true,   
               serverSide: false,   
               destroy: true,   
               responsive: false,
               scrollX: true,
               autoWidth: false,
               pageLength: 10,   
               searchDelay: 800, // Debounce search input (800ms) to avoid triggering on every keystroke
               lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
               ajax: {   
                   url: "{{ url('snp-wise-mis-report/datalist') }}",   
                   type: "GET",   
                   data: {
                       tier: tier
                   },
                   dataSrc: function(json) {
                       var totals = (json.data && json.data.totals) || {};
                       $('#totalOpenMsme').text(totals.total_open_msme || 0);
                       $('#totalSelectedMsme').text(totals.total_selected_msme || 0);
                       $('#totalOnboardedMsme').text(totals.total_onboarded_msme || 0);
                       return (json.data && json.data.rows) || [];
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
                        data: 'snp_id',
                        render: function (data) {
                            return data
                                ? `<span class="badge bg-primary">${data}</span>`
                                : `<span class="badge bg-secondary">-</span>`;
                        }
                    },                
                   {
                       data: 'organization_name',
                       defaultContent: '-'
                   },  
                   {
                       data: 'open_msme',
                       defaultContent: '0'
                   },
                   {
                       data: 'selected_msme',
                       defaultContent: '0'
                   },
                   {
                       data: 'onboarded_msme',
                       defaultContent: '0'
                   },
               ]
           });

           new $.fn.dataTable.Buttons(table, {
               buttons: [
                   {
                       extend: 'excel',
                       text: 'Excel',
                       className: 'buttons-excel'
                   },
                   {
                       extend: 'pdf',
                       text: 'PDF',
                       className: 'buttons-pdf'
                   }
               ]
           });

           table.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
       }
   });
   
   // Show/hide custom loader based on DataTable processing status
   $('#dataTable').on('processing.dt', function(e, settings, processing) {
       if (processing) {
           $('#table-loader').css('display', 'flex');
       } else {
           $('#table-loader').css('display', 'none');
       }
   });
</script>
@endsection