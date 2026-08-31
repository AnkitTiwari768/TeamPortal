 @extends('components.admin.content-layout')

@section('card-content')

<style>
        #dataTable_batch>tbody>tr>td:nth-child(3) {
            cursor: pointer;
            color: #0d6efd;
            text-decoration: underline;
            font-weight: 500;
        }

        #dataTable_batch>tbody>tr>td:nth-child(3):hover {
            color: #0a58ca;
        }
    </style>
 <div class="card-body">
     @php
         $dataTableComponent =
             '
            <div class="table-responsive">
                <table class="table" id="dataTable_batch" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th></th>
							<th>' .__('message.sn') . '</th>
							<th>Batch No</th>
							<th>SNP ID</th>
							<th>SNP Name</th>							
							<th>Financial Year</th>
							<th>Month</th>
                            <th>Claimed Amount (Rs)</th>
                            <th>Total Claims</th>
                            <th>Submission Date</th>
                            <th class="actions">' .__('message.action') .'</th>
                        </tr>
                    </thead>
                </table>
            </div>
        ';
     @endphp

     <div class="tab-content" id="myTabContent">
         {!! $dataTableComponent !!}
     </div>


 </div>
 <script>
     function createBadgeByStatus(status) {
          return '<span class="badge bg-primary">' + status + '</span>';
     }
 </script>
 @include('claim-form.query_workflow_modal')
 
 

@section('js')
 <script>
     const hasSNPRole = {{ hasRole('snp') || hasRole('lsp') || hasRole('bnp') ? 'true' : 'false' }};
     initBatchTable();
     function initBatchTable() {
         if ($.fn.DataTable.isDataTable('#dataTable_batch')) return;
		 
         var parentTable = dataTableInit({
             id: "#dataTable_batch",
             showExcelExport: false,
             order: {
                 column: 1,
                 direction: "asc"
             },
             url: "{{ url('batch-query-list/' . ($claimSlug ?? 'claim-for-catalog-creation')) }}",
             columns: [
			 
			     {
                     className: "dt-control",
                     orderable: false,
                     data: null,
                     defaultContent: ""
                 },
				 
                 {
                     orderable: false,
                     render: function(data, type, full, meta) {
                         return serialNumber("#dataTable_batch", meta.row);
                     }
                 },
                 {
                     data: "batch_number"
                 },
                 {
                     data: "snp_id"
                 },
                 {
                     data: "snp_name"
                 },
                 {
                     data: "financial_year"
                 },
                 {
                     data: "month_name"
                 },
                 {
                     data: "claim_amount"
                 },
                 {
                     data: "total_claims"
                 },
                 {
                     data: "created_at"
                 },
				 
				  {

					 orderable: false,
					 render: function(data, type, row) {
						 var timelineBtn = viewWTHistory(row.batch_id);
						  let uploadInvoice = "";
						  if(hasSNPRole){
								if(row.is_query==1){
								  uploadInvoice = `
									<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Upload Invoice">
										<button type="button" 
											batch-id="${row.id}" 
											class="btn btn-sm btn-primary view-action-btn" 
											data-bs-toggle="modal" 
											data-bs-target="#workflowModal">
										Upload Invoice
										</button></a>
									`;
								}
							}
						 
							
							return `<div class="text-center d-flex">${uploadInvoice}${timelineBtn}</div>`;
						
					 }
				 },
				 
                
             ],
         });



         $('#dataTable_batch tbody').on('click', 'td.dt-control, td:nth-child(3)', function() {

             var tr = $(this).closest('tr');
             var row = parentTable.row(tr);

             if (!row || !row.data()) return;

             if (row.child.isShown()) {
                 row.child.hide();
                 tr.removeClass('shown');
             } else {
                 row.child(`
                <table id="childTable-${row.data().id}" class="table table-sm table-bordered" style="width:100%">
                    <thead>
                        <tr>
						    <th>{{ __('message.sn') }}</th>
                            <th>Claim No</th>
                            <th>Udyam No</th>
                            <th>Team Id</th>
                            <th>MSME Name</th>
                            <th>Claimed Amount (Rs)</th>
                            <th>Status</th>
							<th>Action</th>
                        </tr>
                    </thead>
                </table>
            `).show();
                 tr.addClass('shown');

                 $(`#childTable-${row.data().id}`).DataTable({
                     processing: true,
                     serverSide: true,
                     searching: false,
                     info: false,
                     lengthChange: false,
                     paging: false,
                     ajax: "{{ url('batch-query-detail') }}/" + row.data().id,
                     
                     columns: [
					 
						{
                             orderable: false,
                             render: function(data, type, full, meta) {
                                 return serialNumber("#dataTable_batch", meta.row);
                             }
                         },

                         {
                             data: "application_number",
                             render: function(data, type, row) {
                                 if (!row.id) return data;

                                 return `
								<a href="{{ url('claim-show') }}/${row.id}" 
								class="text-primary text-decoration-underline" 
								style="cursor:pointer;">
									${data}
								</a>
							`;
                             }
                         },
                         {
                             data: "udyam_no"
                         },
                         {
                             data: "team_id"
                         },
                         {
                             data: "msme_name"
                         },
                         {
                             data: "amount",
                             render: function(data) {
                                 return parseFloat(data).toLocaleString('en-IN', {
                                     minimumFractionDigits: 2
                                 });
                             }
                         },
						 
						{
                             render: function(data, type, row) {
                                 return createBadgeByStatus(row.status);

                             }
                         },
						 
						 {

                             orderable: false,
                             render: function(data, type, row) {
                                  let viewBtn = "";
								  viewBtn = `<a href="{{ url('claim-show') }}/${row.id}" class=" btn-info border btn btn-info btn-sm me-1 px-2 text-white" title="View"><i class="bi bi-eye"></i></a>`;
									
									return `<div class="text-center d-flex">${viewBtn}</div>`;
								 
							 }
						 },
                         
                     ],


                 });
             }
         });
		 
		 
         return parentTable;
     }
 
 </script>
@endsection
@endsection