 <div class="card-body">
     <input type="hidden" id="review_status" value="">
     @php
         $dataTableComponent =
             '
            <div class="table-responsive">
                <table class="table" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
						    <th><input type="checkbox" id="selectAll"></th>
                            <th>' .
             __('message.sn') .
             '</th>
                            <th>Claim No</th>
							<th>SNP ID</th>
							<th>SNP Name</th>
                            <th>Udyam No</th>
                            <th>Team ID of MSE</th>
							<th>Name of MSE</th>
							<th>Category of MSE</th>
							<th>Target Customer</th>
							<th>Major Category</th>
                            <th>BppiD/Provider ID</th>
							<th>Product Category</th>
                            <th>Base Incentive Amount (Rs)</th>
                            <th>GST Amount (Rs)</th>
                            <th>SGST Amount (Rs)</th>
                            <th>CGST Amount (Rs)</th>
                            <th>TDS Amount (Rs)</th>
                            <th>CGST TDS Amount</th>                        
                            <th>SGST TDS Amount </th>                           
                            <th>Total Claimed Amount (Rs)</th>
                            <!--<th>Status</th>-->
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
     <div><button type="button" id="create_batch" class="btn btn-primary">
             Create Batch & Forward to ONDC
         </button></div>

 </div>

 <script>
     //$(document).ready(function() {

     var selectedRows = {};

     function initClaimTable() {
         if ($.fn.DataTable.isDataTable('#dataTable')) return;
         var claimTable = dataTableInit({
             id: "#dataTable",
             showExcelExport: false,
             order: {
                 column: 0,
                 direction: "asc"
             },
             url: "{{ url('claims-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-catalog-creation')) }}",
             columns: [

                 {
                     "orderable": false,
                     "className": "noExport",
                     "render": function(data, type, row) {
                         var checked = selectedRows[row.id] ? 'checked' : '';
                         return '<input type="checkbox" class="row-checkbox" value="' + row.id +
                             '" ' + checked + '>';
                     }
                 },

                 {
                     orderable: false,
                     render: function(data, type, full, meta) {
                         return full.is_bulk ?
                             serialNumber("#dataTable", meta
                                 .row) :
                             serialNumber("#dataTable", meta.row);
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {

                         if (!row.id) {
                             return row.application_number ?? '';
                         }

                         return `
                            <a href="{{ url('claim-show') }}/${row.id}"
                            class="text-primary text-decoration-underline"
                            style="cursor:pointer;">
                                ${row.application_number}
                            </a>
                        `;
                     }
                 },


                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.snp_id ?? '';
                     }
                 },

                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.snp_name ?? '';
                     }
                 },

                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.udyam_no ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.team_id ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.msme_name ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.msme_classification ?? '';
                     }
                 },

                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.target_customer ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.major_activity ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.bpp_id ?? '';
                     }
                 },

                 {
                     orderable: true,
                     /*render: function(data, type, row) {
                         return row.subdomain_names ?? '';
                     }*/
                     "render": function(data, type, row) {
                         let text = row.subdomain_names || '';
                         if (text.length <= 20) return text;

                         /*return `
                            <span class="short">${text.slice(0,30)}...</span>
                            <span class="full d-none d-block">${text}</span>
                            <a href="#" class="toggle d-block mt-1" style="text-decoration:none;">Read More</a>
                        `;*/

                         return `
                            <span class="short-text">${text.slice(0,30)}...</span>
                            <span class="full-text d-none">${text}</span>
                            <a href="javascript:void(0)" class="toggle-text d-block mt-1" style="text-decoration:none;">
                                Read More
                            </a>
                        `;
                     }
                 },
                 /* {
                      orderable: true,
                      render: function(data, type, row) {
                          return parseFloat(row.amount) - parseFloat(row.gst_charge_amount);
                      }
                  },
                  {
                      orderable: true,
                      render: function(data, type, row) {
                          return row.gst_charge_amount ?? '';
                      }
                  },
                  */

                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.amount ?? '';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         if (row.gst_amount !== null && row.gst_amount !== undefined) {
                             var pct = (row.gst_percentage !== null && row.gst_percentage !==
                                 undefined) ? ' (' + row.gst_percentage + '%)' : '';
                             return row.gst_amount + pct;
                         }
                         return '-';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         if (row.sgst_amount !== null && row.sgst_amount !== undefined) {
                             var pct = (row.sgst_percentage !== null && row.sgst_percentage !==
                                 undefined) ? ' (' + row.sgst_percentage + '%)' : '';
                             return row.sgst_amount + pct;
                         }
                         return '-';
                     }
                 },
                 {
                     orderable: true,
                     render: function(data, type, row) {
                         if (row.cgst_amount !== null && row.cgst_amount !== undefined) {
                             var pct = (row.cgst_percentage !== null && row.cgst_percentage !==
                                 undefined) ? ' (' + row.cgst_percentage + '%)' : '';
                             return row.cgst_amount + pct;
                         }
                         return '-';
                     }
                 },
                 {
                     orderable: true,
                     visible: false,
                     render: function(data, type, row) {
                         if (row.tds_amount !== null && row.tds_amount !== undefined) {
                             var pct = (row.tds_percentage !== null && row.tds_percentage !==
                                 undefined) ? ' (' + row.tds_percentage + '%)' : '';
                             return row.tds_amount + pct;
                         }
                         return '-';
                     }
                 },
                 {
                     orderable: true,
                     visible: false,
                     render: function(data, type, row) {
                         if (row.cgst_tds_amount !== null && row.cgst_tds_amount !== undefined) {
                             var pct = (row.cgst_tds_percentage !== null && row.cgst_tds_percentage !==
                                 undefined) ? ' (' + row.cgst_tds_percentage + '%)' : '';
                             return row.cgst_tds_amount + pct;
                         }
                         return '-';
                     }
                 },
                 {
                     orderable: true,
                     visible: false,
                     render: function(data, type, row) {
                         if (row.sgst_tds_amount !== null && row.sgst_tds_amount !== undefined) {
                             var pct = (row.sgst_tds_percentage !== null && row.sgst_tds_percentage !==
                                 undefined) ? ' (' + row.sgst_tds_percentage + '%)' : '';
                             return row.sgst_tds_amount + pct;
                         }
                         return '-';
                     }
                 },

                 {
                     orderable: true,
                     render: function(data, type, row) {
                         return row.total_claimed_amount ?? '-';
                     }
                 },

                 /**{
                     orderable: true,
                     render: function(data, type, row) {
                         let status = row.status ?? '';
                         let label = '';

                         if (status === 'Submitted') {
                             label = `<span class="badge bg-primary">${status}</span>`;
                         } else if (status === 'Forwarded') {
                             label = `<span class="badge bg-warning">Pending</span>`;
                         } else if (status === 'Approved') {
                             label = `<span class="badge bg-success">${status}</span>`;
                         } else if (status === 'Rejected' || status === 'Reverted') {
                             label = `<span class="badge bg-danger">${status}</span>`;
                         } else {
                             label = `<span class="badge bg-secondary">${status}</span>`;
                         }

                         return label;
                     }
                 },*/

                 {
                     orderable: false,
                     render: function(data, type, row) {
                         var viewBtn = buttonView("{{ url('claim-show') }}", row.id);
                         var editBtn = '';

                         //  @if (hasRole('snp') || hasRole('bnp') || hasRole('lsp'))
                         //      if (row.status === null || row.status === 'Draft') {
                         //          editBtn = buttonEdit("{{ url('claim-edit') }}", row.id);
                         //      }
                         //  @endif

                         //var timelineBtn = viewTHistory(row.id);
                         var FinanceIcon = "";
                         if (hasFinanceRole && row.status === 'Approved') {
                             FinanceIcon = '<a href="javascript:void(0)" claim-id="' + row.id +
                                 '" data-bs-toggle="modal" data-bs-target="#myPaymentModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-check"></i></a>';
                         }

                         return createActionButtons([viewBtn, editBtn, FinanceIcon]);
                     }
                 }
             ],
             createdRow: true,
             filters: ['review_status', 'is_bulk']
         });

         new $.fn.dataTable.Buttons(claimTable, {
             buttons: [{
                     extend: 'excelHtml5',
                     text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
                     exportOptions: {
                         columns: ':visible:not(.actions):not(.noExport)',
                         rows: function(idx, data, node) {
                             var selectedIds = Object.keys(selectedRows).filter(function(id) {
                                 return selectedRows[id] === true;
                             });
                             if (selectedIds.length > 0) {
                                 var rowCheckbox = $(node).find('.row-checkbox');
                                 if (rowCheckbox.length) {
                                     var id = rowCheckbox.val();
                                     return selectedRows[id] === true;
                                 }
                                 return false;
                             }
                             return true;
                         }
                     },
                     customize: function(xlsx) {
                         var sheet = xlsx.xl.worksheets['sheet1.xml'];
                         $('row:first c', sheet).attr('s', '2');
                     }
                 },
                 {
                     extend: 'pdfHtml5',
                     orientation: 'landscape',
                     pageSize: 'LEGAL',
                     text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Pdf',
                     exportOptions: {
                         columns: ':visible:not(.actions):not(.noExport)',
                         rows: function(idx, data, node) {
                             var selectedIds = Object.keys(selectedRows).filter(function(id) {
                                 return selectedRows[id] === true;
                             });
                             if (selectedIds.length > 0) {
                                 var rowCheckbox = $(node).find('.row-checkbox');
                                 if (rowCheckbox.length) {
                                     var id = rowCheckbox.val();
                                     return selectedRows[id] === true;
                                 }
                                 return false;
                             }
                             return true;
                         }
                     },
                     customize: function(doc) {
                         doc.styles.tableHeader.fontSize = 10;
                         doc.styles.tableBodyEven.fontSize = 10;
                         doc.styles.tableBodyOdd.fontSize = 10;
                         doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 2)
                             .join('*').split('');
                         doc.defaultStyle.alignment = 'left';
                         doc.styles.tableHeader.alignment = 'left';
                     }
                 }
             ]
         });

         claimTable.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');

         return claimTable;
     }

     $(document).on("change", ".row-checkbox", function() {
         var id = $(this).val();
         selectedRows[id] = $(this).prop("checked");

         var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(
                 ".row-checkbox")
             .length;
         $("#selectAll").prop("checked", allChecked);
     });


     $(document).on("change", "#selectAll", function() {
         var checked = $(this).prop("checked");
         $(".row-checkbox").each(function() {
             $(this).prop("checked", checked);
             selectedRows[$(this).val()] = checked;
         });
     });
     $(document).off("click", "#create_batch").on("click", "#create_batch", function() {
         let selected = [];
         $(".row-checkbox:checked").each(function() {
             selected.push($(this).val());
         });

         if (selected.length < 1) {
             toastr.error("Please select at least 1 claim before proceeding.");
             return false;
         }

         var is_declaration_agreed = $(this).val();
         var $btn = $(this);
         var originalHtml = $btn.html();

         $.ajax({
             url: "{{ url('create-batch-claim') }}",
             type: "POST",
             data: {
                 _token: "{{ csrf_token() }}",
                 claim_id: selected,
                 claim_type_id: '{{ $claimSlug }}',
                 is_declaration_agreed: is_declaration_agreed
             },
             beforeSend: function() {
                 $btn.prop('disabled', true);
                 $btn.html('<i class="fa fa-spinner fa-spin me-1"></i> Creating Batch...');
                 if ($("#ajax-loader").length) {
                     $("#ajax-loader").show();
                 }
             },
             success: function(res) {
                 if (res.status === false) {
                     toastr.error(res.errors && res.errors.message ? res.errors.message :
                         'Failed to create batch.');
                     $btn.prop('disabled', false);
                     $btn.html(originalHtml);
                     if ($("#ajax-loader").length) {
                         $("#ajax-loader").hide();
                     }
                     return false;
                 }

                 toastr.success(res.message);

                 window.location.href = "{{ url($redirectUrl ?? 'claims') }}#Pending";
                 window.location.reload(true);
             },
             error: function(xhr) {
                 let errors = 'Something went wrong.';
                 if (xhr.responseJSON && xhr.responseJSON.errors) {
                     if (xhr.responseJSON.errors.claim_type_id) {
                         errors = xhr.responseJSON.errors.claim_type_id;
                     } else if (xhr.responseJSON.message) {
                         errors = xhr.responseJSON.message;
                     }
                 }
                 toastr.error(errors);
                 $btn.prop('disabled', false);
                 $btn.html(originalHtml);
                 if ($("#ajax-loader").length) {
                     $("#ajax-loader").hide();
                 }
             }
         });
     });
     //});
 </script>

 <script>
     document.addEventListener('click', e => {
         if (e.target.classList.contains('toggle')) {
             e.preventDefault();
             let td = e.target.parentElement;
             td.querySelector('.short').classList.toggle('d-none');
             td.querySelector('.full').classList.toggle('d-none');
             e.target.textContent =
                 e.target.textContent === 'Read More' ? 'Read Less' : 'Read More';
         }
     });
 </script>
