@extends('components.admin.content-layout')
@section('card-content')
<div class="card-body table-loading-container">
<!-- Modern Table Loader Overlay -->
<div id="table-loader">
   <div class="text-center">
      <div class="modern-spinner mx-auto"></div>
      <div class="loading-text">Loading Tier records...</div>
   </div>
</div>

   <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover"
         id="dataTable"
         width="100%"
         cellspacing="0">
         <thead>
            <tr>
               <th>S.No</th>
                <th>Tire</th>
               <th>Registered MSEs</th>
               <th>Open MSEs</th>
               <th>Direct MSEs</th>
               <th>Onboarded MSEs</th>
                <th>Women Owned MSEs</th>
               <th>SC</th>
               <th>ST</th>
               <th>OBC</th>
               <th>General</th>
             
            </tr>
         </thead>
         <tbody></tbody>
      </table>
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

                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],

               ajax: {   
                   url: "{{ url('tier-wise-mis-report/datalist') }}",   
                   type: "GET",   
                   data: {
                       tier: tier
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
                       data: 'tier',
                       defaultContent: ''
                   },  
                   {
                       data: 'total_msme',
                       defaultContent: '0'
                   },
   
                  
   
                   {
                       data: 'open_msme',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'direct_selection',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'onboarded_msme',
                       defaultContent: '0'
                   },
                    {
                       data: 'total_women',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'sc_count',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'st_count',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'obc_count',
                       defaultContent: '0'
                   },
   
                   {
                       data: 'general_count',
                       defaultContent: '0'
                   }
   
               ], 
               
   
           });

           new $.fn.dataTable.Buttons(table, {
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