 <div class="card-body">
     <input type="hidden" id="review_status" value="">
     @php

         $proceedLabel = 'Proceed';

         if ($showRejectLabel) {
             $proceedLabel = 'Send Query';
         }

         if (
            $claimSlug === 'claim-for-catalog-creation' ||
            $claimSlug === 'claim-for-accounts-management' ||
            $claimSlug === 'claim-for-packaging'
        ) {
            $NP_ID = 'SNP ID';
            $NP_NAME = 'SNP Name';
         }
         elseif ($claimSlug === 'claim-for-logistics-and-transportation') {
            $NP_ID = 'LSP ID';
            $NP_NAME = 'LSP Name';
         }
         elseif ($claimSlug === 'claim-for-demand-generation') {
            $NP_ID = 'BNP ID';
            $NP_NAME = 'BNP Name';
         }
         else {
            $NP_ID = 'NP ID';
            $NP_NAME = 'NP Name';
         }

         $dataTableComponent =
             '
            <div class="table-responsive">
                <table class="table" id="dataTable_batch" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>' .
             __('message.sn') .
             '</th>
							<th>Batch No</th>
							<th>'. $NP_ID .'</th>
							<th>'. $NP_NAME .'</th>							
							<th>Financial Year</th>
							<th>Month</th>
                            <th>Claimed Amount (Rs)</th>
                            <th>Total Claims</th>
                            <th>Submission Date</th>
                            <th class="actions">' .
             __('message.action') .
             '</th>
                        </tr>
                    </thead>
                </table>
            </div>
        ';
     @endphp

     <div class="tab-content" id="myTabContent">
         {!! $dataTableComponent !!}
     </div>

     {{-- <div><button type="button" id="create_batch" class="btn btn-primary">
             Create Batch & Forward to CA
         </button></div> --}}

 </div>

 <script>
     function createBadgeByStatus(status) {
         if (status === "Pending" || status === "Sent to ONDC") {
             return '<span class="badge bg-warning">Pending</span>';
         } else {
             return '<span class="badge bg-primary">' + status + '</span>';
         }
     }
 </script>




 <script>
     //$(document).ready(function() {
     function initBatchTable() {
         if ($.fn.DataTable.isDataTable('#dataTable_batch')) return;
         var parentTable = dataTableInit({
             id: "#dataTable_batch",
             showExcelExport: false,
             order: {
                 column: 1,
                 direction: "asc"
             },
             url: "{{ url('batch-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-catalog-creation')) }}",
             columns: [{
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
                         //var viewBtn = buttonView("{{ url('claim-show') }}", row.id);
                         var editBtn = '';
                         @if (hasRole('snp') || hasRole('lsp') || hasRole('bnp'))
                             if (row.status === 'Reverted' || (row.is_bulk && row.status ===
                                     'Draft')) {
                                 editBtn = buttonEdit("{{ url('claim-edit') }}", row.id);
                             }
                         @endif

                         var timelineBtn = viewWTHistory(row.id);

                         let actionProceedButton = '';

                         if (!hasSNPRole && !hasCARole && row.show_proceed_action) {
                             actionProceedButton = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
									<button type="button" 
										batch-id="${row.id}" 
										class="btn btn-sm btn-primary view-action-btn" 
										data-bs-toggle="modal" 
										data-bs-target="#workflowModal" 
										route="{{ url('proceed-batch-workflow') }}"
										action="proceed-batch-workflow">
										{{ $proceedLabel }}
								</button></a>`;

                         }



                         if (hasNSICRole && row.sent_to_nsic_finance) {
                             actionProceedButton = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
								   <button type="button" 
									batch-id="${row.id}" 
									class="btn btn-sm btn-primary view-action-btn" 
									data-bs-toggle="modal" 
									data-bs-target="#workflowModal" 
									route="{{ url('proceed-batch-workflow') }}"
									action="proceed-batch-workflow-sent-to-finance">
									Proceed
							</button></a>`;

                         }



                         var FinanceIcon = "";
                         /*if (hasFinanceRole && row.batchStatus === 'Approved' && row.mark_payment_completed==1) {
                                    FinanceIcon = `<a href="javascript:void(0)" 
						     batch-id="${row.id}" data-bs-toggle="modal" data-bs-target="#myPaymentModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-check"></i></a>
							`;
    						}*/


                         if ((hasFinanceRole && row.mark_payment_completed) || (row.is_query == 2)) {
                             FinanceIcon = `<a href="javascript:void(0)" 
						     batch-id="${row.id}" data-bs-toggle="modal" data-bs-target="#myPaymentModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-check"></i></a>
							`;
                         }

                         var ondc_buttons = '';
                         // if (hasONDCRole) {
                         // 	if (row.batch_button.show_send_to_nsic == true) {
                         // 		ondc_buttons = `
                        // 		<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Forward to NSIC">
                        // 			<button type="button" 
                        // 					batch-id="${row.id}" 
                        // 					class="btn btn-sm btn-primary" 
                        // 					data-bs-toggle="modal" 
                        // 					data-bs-target="#workflowModal" 
                        // 					route="{{ url('forward-to-nsic-by-ondc') }}"
                        // 					action="forward-to-nsic-by-ondc">
                        // 					<!--Forward to NSIC & Revert to SNP-->
                        // 					<img src="${BASE_URL}/assets/img-new/forward.svg">

                        // 			</button></a>
                        // 		`;
                         // 	}
                         // }


                         var snp_buttons = "";
                         if (hasSNPRole && row.show_proceed_action) {

                             snp_buttons = `
							<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Upload Invoice">
								<button type="button" 
										batch-id="${row.id}" 
										class="btn btn-sm btn-primary view-action-btn" 
										data-bs-toggle="modal" 
										data-bs-target="#workflowModal" 
										route="{{ url('proceed-batch-workflow') }}" 
										action="proceed-batch-workflow">
									Upload Invoice
								</button></a>
							`;


                         }

                         if (hasSNPRole && row.sent_to_ca) {
                             snp_buttons = `
							<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
								<button type="button" 
										batch-id="${row.id}" 
										class="btn btn-sm btn-primary view-action-btn" 
										data-bs-toggle="modal" 
										data-bs-target="#workflowModal" 
										route="{{ url('proceed-batch-workflow') }}" 
										action="proceed-batch-workflow-to-ca">
									Proceed
								</button></a>
							`;

                         }

                         // if (hasSNPRole) {

                         // 	if (row.batch_button.show_move_to_draft == true && row.show_move_to_draft_in_rejected==1) {
                         // 		snp_buttons = `
                        // 		<!--<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Move to Drafts">
                        // 		<button type="button" 
                        // 					batch-id="${row.id}" 
                        // 					class="btn btn-sm bg-primary btn-sm btn-circle m-1 view-action-btn" 
                        // 					data-bs-toggle="modal" 
                        // 					data-bs-target="#workflowModal" 
                        // 					route="{{ url('move-to-drafts') }}"
                        // 					action="move-to-drafts">
                        // 					<img src="${BASE_URL}/assets/img-new/draft.svg">
                        // 			</button></a>-->
                        // 		`; 
                         // 	}


                         // 	if (row.batch_button.show_sent_to_ca == true) {
                         // 		snp_buttons = `
                        // 		<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Re-send to CA">
                        // 			<button type="button" 
                        // 					batch-id="${row.id}" 
                        // 					class="btn btn-sm btn-primary" 
                        // 					data-bs-toggle="modal" 
                        // 					data-bs-target="#workflowModal" 
                        // 					route="{{ url('resend-to-ca') }}"
                        // 					action="resend-to-ca">
                        // 					<img src="${BASE_URL}/assets/img-new/resend.svg"> 
                        // 			</button></a>
                        // 		`;
                         // 	}

                         // 	if ((row.is_sent_ondc =='' || row.is_sent_ondc ==null) && row.is_ca_certified==1 && row.show_sent_to_ondc == 1) {
                         // 		snp_buttons = `
                        // 		<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Forward to ONDC">
                        // 			<button type="button" 
                        // 					batch-id="${row.id}" 
                        // 					class="btn btn-sm btn-primary" 
                        // 					data-bs-toggle="modal" 
                        // 					data-bs-target="#workflowModal" 
                        // 					route="{{ url('forward-to-ondc') }}"
                        // 					action="forward-to-ondc">
                        // 					<img src="${BASE_URL}/assets/img-new/forward.svg">

                        // 			</button></a>
                        // 		`;
                         // 	}
                         // }



                         var ca_buttons = "";
                         if (hasCARole && row.show_proceed_action) {

                             ca_buttons = `
							<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Upload Certificate">
								<button type="button" 
										batch-id="${row.id}" 
										class="btn btn-sm btn-primary view-action-btn" 
										data-bs-toggle="modal" 
										data-bs-target="#workflowModal" 
										route="{{ url('proceed-batch-workflow') }}" 
										action="proceed-batch-workflow">
									Upload Certificate
								</button></a>
							`;


                         }

                         var nsic_buttons = '';
                         if (hasNSICRole) {
                             if (row.batch_button != null) {
                                 if (row.batch_button.showForwardToNsicFinance == true) {
                                     nsic_buttons = `
								<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Forward to NSIC Finance">
									<button type="button" 
											batch-id="${row.id}" 
											class="btn btn-sm btn-primary" 
											data-bs-toggle="modal" 
											data-bs-target="#workflowModal" 
											route="{{ url('forward-to-nsic-finance') }}"
											action="forward-to-nsic-finance">
											<img src="${BASE_URL}/assets/img-new/forward.svg">
											
									</button></a>
								`;
                                 }
                             }
                         }


                         var nsic_finance_buttons = '';
                         if (hasFinanceRole) {
                             if (row.batch_button != null) {
                                 if (row.batch_button.showFinalApproval == true && row
                                     .batchStatus != 'Payment_completed') {
                                     nsic_finance_buttons = `
								<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Approve">
									<button type="button" 
											batch-id="${row.id}" 
											class="btn btn-sm btn-primary" 
											data-bs-toggle="modal" 
											data-bs-target="#workflowModal" 
											route="{{ url('batch-final-approval') }}"
											action="batch-final-approval">
											<!--Approve & Revert-->
											<img src="${BASE_URL}/assets/img-new/approve.svg">
									</button></a>
								`;
                                 }
                             }
                         }



                         return createActionButtons([editBtn, timelineBtn, FinanceIcon,
                             ca_buttons, snp_buttons, ondc_buttons, nsic_buttons,
                             nsic_finance_buttons, actionProceedButton
                         ]);
                     }
                 }
             ],
             filters: ['review_status', 'is_bulk']
         });


         const hasSNPRole = {{ hasRole('snp') || hasRole('lsp') || hasRole('bnp') ? 'true' : 'false' }};
         const hasONDCRole = {{ hasRole('ondc-admin') ? 'true' : 'false' }};
         const hasNSICRole = {{ hasRole('nsic') ? 'true' : 'false' }};
         const hasFinanceRole = {{ hasRole('nsic-finance') ? 'true' : 'false' }};
         const hasCARole = {{ hasRole('ca') ? 'true' : 'false' }};


        @if ($claimSlug === 'claim-for-demand-generation')

            @include('claim-form.batch_list_demand_generation')

        @else 
            
            $('#dataTable_batch tbody').on('click', 'td.dt-control, td:nth-child(3)', function() {
             var review_status = $("#review_status").val();

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
                     ajax: "{{ url('batch-detail') }}/" + row.data().id + "/" +
                         review_status,
                     createdRow: function(row, data, dataIndex) {
                         if (hasSNPRole || hasONDCRole || hasNSICRole) {
                             if (data.is_edited == 1) {
                                 $(row).addClass('row-edited');
                             } else {
                                 $(row).removeClass('row-edited');
                             }
                         }
                     },
                     columns: [{
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
                         //{ data: "amount" },
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
                                 var SnpeditBtn = '';
                                 var sendNsicBtn = '';
                                 var snp_buttons = '';

                                 // View Button (outside dropdown)
                                 if (row.id) {
                                     viewBtn = `
										<a href="{{ url('claim-show') }}/${row.id}" class=" btn-info border btn btn-info btn-sm me-1 px-2 text-white" title="View">
											<i class="bi bi-eye"></i>
										</a>
									`;
                                 }


                                 // for snp
                                 if (hasSNPRole) {
                                     <?php /*  if (row.status === 'Reverted' || row.status ===
                                         'Draft') {
                                         SnpeditBtn = buttonEdit(
                                             "{{ url('claim-edit') }}", row.id);
                                     }

                                     if (row.is_edited == 1 && row
                                         .is_revert_to_snp ==
                                         1 &&
                                         row.status === 'Reverted') {
                                         sendNsicBtn = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Send To Nsic">
										<button type="button" batch-id="${row.batch_id}" claim-id="${row.id}" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#workflowModal" route="{{ url('resend-snp-to-nsic') }}" action="resend-to-nsic"><img src="${BASE_URL}/assets/img-new/forward.svg"></button></a>
							`
                                     }
									 */
                                     ?>

                                     if (row.is_deleted == 1 && row.status === 'Rejected') {
                                         snp_buttons = `
										<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Move to Drafts">
										<button type="button" 
												batch-id="${row.batch_id}" 
												claim-id="${row.id}"
												class="btn btn-primary" 
												data-bs-toggle="modal" 
												data-bs-target="#workflowModal" 
												route="{{ url('move-to-drafts') }}"
												action="move-to-drafts">
												<img src="${BASE_URL}/assets/img-new/draft.svg">
										</button>
										</a>
							`

                                     }

                                 }

                                 <?php /* if (hasONDCRole) {
                                     if (row.is_revert_to_ondc == 1 && row.status ===
                                         'Reverted') {
                                         SnpeditBtn = buttonEdit(
                                             "{{ url('claim-edit') }}", row
                                             .id);
                                     }
                                     if (row.is_edited == 1 && row
                                         .is_revert_to_ondc ==
                                         1 && row
                                         .status === 'Reverted') {
                                         sendNsicBtn = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Send To Nsic">
										<button type="button" batch-id="${row.batch_id}" claim-id="${row.id}" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#workflowModal" route="{{ url('resend-snp-to-nsic') }}" action="resend-to-nsic"><img src="${BASE_URL}/assets/img-new/forward.svg">
										</button></a>
							`
                                     }

                                 }



                                 if (hasNSICRole) {
                                     if (row.is_revert_to_nsic == 1 && row.status ===
                                         'Reverted') {
                                         SnpeditBtn = buttonEdit(
                                             "{{ url('claim-edit') }}", row.id);
                                     }
                                     if (row.is_edited == 1 && row
                                         .is_revert_to_nsic ==
                                         1 && row
                                         .status === 'Reverted') {
                                         sendNsicBtn = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Re-Send To Finance">
										<button type="button" batch-id="${row.batch_id}" claim-id="${row.id}" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#workflowModal" route="{{ url('resend-nsic-to-finance') }}" action="resend-nsic-to-finance"><img src="${BASE_URL}/assets/img-new/resend.svg"></button></a>
							`
                                     }

                                 } */
                                 ?>


                                 var actions = [];

                                 if (hasCARole || hasONDCRole) {
                                     if (row.status == 'Approved' || row.status == 'Rejected' ||
                                         row.status ==
                                         'Payment_completed') {
                                         actions = '';
                                     } else {
                                         if (hasONDCRole) {
                                             actions.push(`
								
										<li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
											data-bs-toggle="modal" data-bs-target="#workflowModal"
											route="{{ url('batch-claim-approve') }}" action="approve">
											<i class="bi bi-check-circle"></i> Approve
										</a></li>
										
										<?php /* <li><a class="dropdown-item text-info" batch-id="${row.batch_id}" claim-id="${row.id}"
											data-bs-toggle="modal" data-bs-target="#workflowModal"
											route="{{ url('batch-claim-revert') }}" action="revert">
											<i class="bi bi-arrow-counterclockwise"></i> Revert
										</a></li> */
          ?>
							`);
                                             actions.push(`
											<li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
												data-bs-toggle="modal" data-bs-target="#workflowModal"
												route="{{ url('batch-claim-reject') }}" action="reject">
												<i class="bi bi-x-circle"></i> Reject
												</a></li>
											`);
                                         }


                                     }
                                 }



                                 if (hasNSICRole) {
                                     if (row.status == 'Approved' || row
                                         .status == 'Rejected' || row.status ==
                                         'Payment_completed') {
                                         actions = '';
                                     } else {
                                         actions.push(`

								<li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-approve') }}" action="approve">
									<i class="bi bi-check-circle"></i> Approve
								</a></li>

								<?php /*<li><a class="dropdown-item" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-revert-to-snp') }}" action="batch-claim-revert-to-snp">
									<i class="bi bi-arrow-return-left"></i> Revert to SNP
								</a></li>
								<li><a class="dropdown-item" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-revert-to-ondc') }}" action="batch-claim-revert-to-ondc">
									<i class="bi bi-arrow-return-left"></i> Revert to ONDC
								</a></li>
								*/
        ?>
								<li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-reject') }}" action="reject">
									<i class="bi bi-x-circle"></i> Reject
								</a></li>
								
							`);
                                     }
                                 }

                                 if (hasFinanceRole) {
                                     if (row.status == 'Approved' || row
                                         .status == 'Rejected' || row
                                         .status == 'Payment Completed') {
                                         actions = '';
                                     } else {
                                         actions.push(`
								<?php /*<li><a class="dropdown-item text-info" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-revert-to-nsic') }}" action="revert">
									<i class="bi bi-arrow-counterclockwise"></i> Revert
								</a></li> */
        ?>
								
								<li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-approve') }}" action="approve">
									<i class="bi bi-check-circle"></i> Approve
								</a></li>
								
								<li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
									data-bs-toggle="modal" data-bs-target="#workflowModal"
									route="{{ url('batch-claim-reject') }}" action="reject">
									<i class="bi bi-x-circle"></i> Reject
								</a></li>
							`);
                                     }
                                 }



                                 // Dropdown
                                 let dropdown = "";
                                 if (actions.length > 0) {
                                     dropdown = `
							<div class="dropdown d-inline-block d-flex" style="position:relative !important">
								<button class="border btn btn-primary btn-sm btn-white custom-action-btn px-3" type="button"
									data-bs-toggle="dropdown" aria-expanded="false">
									<span class="dots"></span>
									<i class="fa fa-ellipsis-v" aria-hidden="true"></i>
								</button>
								 <ul class="dropdown-menu dropdown-menu-end shadow-sm">
									${actions.join('')}
								</ul>
							</div>
						`;
                                 }

                                 return `<div class="text-center d-flex">${viewBtn}${SnpeditBtn}${sendNsicBtn}${snp_buttons}${dropdown}</div>`;
                             }
                         }




                     ],


                 });
             }
         });
         return parentTable;
     }
    
    
    @endif
    
    
     //});


     /*$(document).on("click", "#move_drafts", function(e) {
     	e.preventDefault();
     	var batchId = $(this).attr('batch-id'); 
     	var dreview_status=$("#review_status").val();
     	
     	if(confirm('Are you sure move to drafts?')){
     		$.ajax({
     			url: "{{ url('move-to-drafts') }}",
     			type: 'POST',
     			data: { batch_id: batchId,review_status:dreview_status},
     			success: function(res) {
     				toastr.success(res.message);
     				setTimeout(function() {
     					window.location.href = "{{ url('claims') }}";
     				}, 2000);
     			},
     			error: function(xhr) {
     				toastr.error(xhr.responseJSON.message);
     			}
     		});
     	}
     });*/
 </script>
