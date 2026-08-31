 <div class="card-body">
     <input type="hidden" id="review_status" value="">
     @php
    if (
            $claimSlug === 'claim-for-catalogue-creation' ||
            $claimSlug === 'claim-for-accounts-management' ||
            $claimSlug === 'claim-for-packaging'
        ) {
            $NP_ID = 'SNP ID';
            $NP_NAME = 'SNP Name';
         }
         elseif ($claimSlug === 'claim-for-transportation-and-logistic') {
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

@endphp
     
     @php

         $proceedLabel = 'Proceed';

         if ($showRejectLabel) {
             $proceedLabel = 'Send Query';
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
function initBatchTable() {

    // ✅ MUST be declared before usage
    const hasSNPRole = {{ hasRole('snp') || hasRole('lsp') || hasRole('bnp') ? 'true' : 'false' }};
    const hasONDCRole = {{ hasRole('ondc-admin') ? 'true' : 'false' }};
    const hasNSICRole = {{ hasRole('nsic') ? 'true' : 'false' }};
    const hasFinanceRole = {{ hasRole('nsic-finance') ? 'true' : 'false' }};
    const hasCARole = {{ hasRole('ca') ? 'true' : 'false' }};

    if ($.fn.DataTable.isDataTable('#dataTable_batch')) return;

    var parentTable = dataTableInit({
        id: "#dataTable_batch",
        showExcelExport: false,
        order: { column: 1, direction: "asc" },
        url: "{{ url('batch-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-catalog-creation')) }}",

        columns: [
            {
                className: "dt-control",
                orderable: false,
                data: null,
                defaultContent: ""
            },
            {
                orderable: false,
                render: function (data, type, full, meta) {
                    return serialNumber("#dataTable_batch", meta.row);
                }
            },
            { data: "batch_number" },
            { data: "snp_id" },
            { data: "snp_name" },
            { data: "financial_year" },
            { data: "month_name" },
            { data: "claim_amount" },
            { data: "total_claims" },
            { data: "created_at" },

            {
                orderable: false,
                render: function (data, type, row) {

                    let editBtn = '';
                    @if (hasRole('snp') || hasRole('lsp') || hasRole('bnp'))
                        if (row.status === 'Reverted' || (row.is_bulk && row.status === 'Draft')) {
                            editBtn = buttonEdit("{{ url('claim-edit') }}", row.id);
                        }
                    @endif

                    let timelineBtn = viewWTHistory(row.id);
                    let actionProceedButton = '';

                    if (!hasSNPRole && !hasCARole && row.show_proceed_action) {
                        actionProceedButton = `
                            <a href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
                                <button type="button" batch-id="${row.id}" 
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#workflowModal"
                                    route="{{ url('proceed-batch-workflow') }}"
                                    action="proceed-batch-workflow">
                                    {{ $proceedLabel }}
                                </button>
                            </a>`;
                    }

                    if (hasNSICRole && row.sent_to_nsic_finance) {
                        actionProceedButton = `
                            <a href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
                                <button type="button" batch-id="${row.id}" 
                                    class="btn btn-sm btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#workflowModal"
                                    route="{{ url('proceed-batch-workflow') }}"
                                    action="proceed-batch-workflow-sent-to-finance">
                                    Proceed
                                </button>
                            </a>`;
                    }

                    let FinanceIcon = '';
                    if ((hasFinanceRole && row.mark_payment_completed) || (row.is_query == 2)) {
                        FinanceIcon = `
                            <a href="javascript:void(0)" batch-id="${row.id}" 
                                data-bs-toggle="modal" data-bs-target="#myPaymentModal"
                                class="btn btn-sm btn-primary m-1">
                                <i class="fa fa-check"></i>
                            </a>`;
                    }

                    let snp_buttons = '';
                    if (hasSNPRole && row.show_proceed_action) {
                        snp_buttons = `
                            <button type="button" batch-id="${row.id}" 
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal" data-bs-target="#workflowModal"
                                route="{{ url('proceed-batch-workflow') }}">
                                Upload Invoice
                            </button>`;
                    }

                    if (hasSNPRole && row.sent_to_ca) {
                        snp_buttons = `
                            <button type="button" batch-id="${row.id}" 
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal" data-bs-target="#workflowModal"
                                route="{{ url('proceed-batch-workflow') }}">
                                Proceed
                            </button>`;
                    }

                    let ca_buttons = '';
                    if (hasCARole && row.show_proceed_action) {
                        ca_buttons = `
                            <button type="button" batch-id="${row.id}" 
                                class="btn btn-sm btn-primary"
                                data-bs-toggle="modal" data-bs-target="#workflowModal">
                                Upload Certificate
                            </button>`;
                    }

                    return createActionButtons([
                        editBtn, timelineBtn, FinanceIcon,
                        ca_buttons, snp_buttons, actionProceedButton
                    ]);
                }
            }
        ],

        filters: ['review_status', 'is_bulk']
    });

    @if ($claimSlug !== 'claim-for-demand-generation')

    $('#dataTable_batch tbody').on('click', 'td.dt-control, td:nth-child(3)', function () {

        var review_status = $("#review_status").val();
        var tr = $(this).closest('tr');
        var row = parentTable.row(tr);

        if (!row || !row.data()) return;

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {

            row.child(`
                <table id="childTable-${row.data().id}" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SN</th>
                            <th>Claim No</th>
                            <th>Udyam No</th>
                            <th>Team Id</th>
                            <th>MSME Name</th>
                            <th>Amount</th>
                            <th>Status</th>
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
                ajax: "{{ url('batch-detail') }}/" + row.data().id + "/" + review_status,

                columns: [
                    {
                        render: function (data, type, full, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: "application_number" },
                    { data: "udyam_no" },
                    { data: "team_id" },
                    { data: "msme_name" },
                    {
                        data: "amount",
                        render: function (data) {
                            return parseFloat(data).toLocaleString('en-IN');
                        }
                    },
                    {
                        render: function (data, type, row) {
                            return createBadgeByStatus(row.status);
                        }
                    }
                ]
            });
        }
    });

    @else  

    @include('claim-form.batch_list_demand_generation')

    @endif

    return parentTable;
}
</script>



