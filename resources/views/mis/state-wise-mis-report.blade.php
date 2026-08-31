@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body table-loading-container">
<!-- Modern Table Loader Overlay -->
<div id="table-loader">
   <div class="text-center">
      <div class="modern-spinner mx-auto"></div>
      <div class="loading-text">Loading State records...</div>
   </div>
</div>
<div class="card-body">
   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
         <thead>
            <tr>
               <th>S.No.</th>
               <th>State Name</th>
               <th>Open MSEs</th>
               <th>Direct MSEs</th>
               <th>Onboarded MSEs</th>             
            </tr>
         </thead>
         <tbody></tbody>
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
       function loadData(productCategory = '')
       {
   
           table = $('#dataTable').DataTable({   
               processing: true,   
               serverSide: false,   
               destroy: true,   
               responsive: false,
               scrollX: true,
               autoWidth: false,
               searchDelay: 800, // Debounce search input (800ms) to avoid triggering on every keystroke
               pageLength: 10,   
               lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
               ajax: {   
                   url: "{{ url('state-wise-mis-report/datalist') }}",   
                   type: "GET",   
                   data: {
                       productCategory: productCategory
                   },   
                    dataSrc: 'data'
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
                       data: 'state_name',
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