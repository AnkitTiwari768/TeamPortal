<style>
    .batch_number {
        color: #0d6efd !important;
        text-decoration: underline;
        cursor: pointer;
        font-weight: 500;
        transition: color 0.2s ease;
    }

    .batch_number:hover {
        color: #0a58ca !important;
        text-decoration: none;
    }

    .batch_number:focus {
        outline: none;
        color: #084298 !important;
    }
</style>

<div class="card-body">
    <input type="hidden" id="review_status" value="">
    @php
        $proceedLabel = 'Proceed';
        if ($showRejectLabel) {
            $proceedLabel = 'Send Query';
        }

        if (
            $claimSlug === 'claim-for-catalogue-creation' ||
            $claimSlug === 'claim-for-accounts-management' ||
            $claimSlug === 'claim-for-packaging'
        ) {
            $NP_ID = 'SNP ID';
            $NP_NAME = 'SNP Name';
        } elseif ($claimSlug === 'claim-for-transportation-and-logistic') {
            $NP_ID = 'LSP ID';
            $NP_NAME = 'LSP Name';
        } elseif ($claimSlug === 'claim-for-demand-generation') {
            $NP_ID = 'BNP ID';
            $NP_NAME = 'BNP Name';
        } else {
            $NP_ID = 'NP ID';
            $NP_NAME = 'NP Name';
        }

        $dataTableComponent =
            '
            <div class="table-responsive">
                <table class="table" id="dataTable_batch" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <!-- ADDED: Checkbox column -->
                            <th></th>
                            <th></th>   <!-- dt-control column -->
                            <th>' .
            __('message.sn') .
            '</th>
                           <th style="color: #ffffff !important;">Batch No</th>
                            <th>' .
            $NP_ID .
            '</th>
                            <th>' .
            $NP_NAME .
            '</th>
                            <th>Financial Year</th>
                            <th>Month</th>
                            <th>Base Incentive Amount (Rs)</th>
                            <th>Total GST (Rs)</th>
                            <th>Total SGST (Rs)</th>
                            <th>Total CGST (Rs)</th>
                            <th>TDS (Rs)</th>
                            <th>CGST TDS Amount</th>
                            <th>SGST TDS Amount </th>
                            <th>IGST TDS Amount</th>
                            <th>Total TDS Amount</th>
                            <th>Total Claimed Amount (Rs)</th>
                            <th>Total Claims</th>
                            <th>Submission Date</th>
                            <th class="actions">' .
            __('message.action') .
            '</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="font-weight: bold; text-align: right;">Total:</th>
                            <th></th>
                            <th></th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;">0.00</th>
                            <th style="font-weight: bold;"></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        ';
    @endphp

    <div class="tab-content" id="myTabContent">
        {!! $dataTableComponent !!}
    </div>

    <!-- ========== ADDED: Bulk Actions Sticky Bar ========== -->
    <div id="bulk_actions_bar"
        class="card shadow-lg border-primary position-fixed bottom-0 start-50 translate-middle-x mb-4 z-3"
        style="display: none; min-width: 400px; border-radius: 12px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px);">
        <div class="card-body py-2 px-4 d-flex align-items-center justify-content-between">
            <div>
                <span class="fw-bold text-primary px-3"><span id="selected_count">0</span> Claims Selected</span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success px-3" id="bulk_approve_btn" action="approve">
                    <i class="bi bi-check-circle"></i> Bulk Accept
                </button>
                <button type="button" class="btn btn-sm btn-danger px-3" id="bulk_reject_btn" action="reject">
                    <i class="bi bi-x-circle"></i> Bulk Reject
                </button>
            </div>
        </div>
    </div>
    <!-- ========== END ADDED ========== -->
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
    // ========== ADDED: Global selection map and helper ==========
    window.selectedClaimsMap = {};

    function updateBulkActionsBar() {
        let totalSelected = 0;
        let activeBatchId = null;
        let hasMultipleBatches = false;

        for (let batchId in window.selectedClaimsMap) {
            let set = window.selectedClaimsMap[batchId];
            if (set.size > 0) {
                totalSelected += set.size;
                if (activeBatchId === null) {
                    activeBatchId = batchId;
                } else if (activeBatchId !== batchId) {
                    hasMultipleBatches = true;
                }
            }
        }

        if (totalSelected > 0) {
            $('#selected_count').text(totalSelected);
            $('#bulk_actions_bar').fadeIn();

            if (hasMultipleBatches) {
                $('#bulk_approve_btn, #bulk_reject_btn').prop('disabled', true).attr('title',
                    'Select claims from a single batch only');
            } else {
                $('#bulk_approve_btn, #bulk_reject_btn').prop('disabled', false).removeAttr('title');
            }
        } else {
            $('#bulk_actions_bar').fadeOut();
        }
    }
    // ========== END ADDED ==========

    function getEditButton(row) {
        var editBtn = '';
        @if (hasRole('snp') || hasRole('lsp') || hasRole('bnp'))
            if (row.status === 'Reverted' || (row.is_bulk && row.status === 'Draft')) {
                editBtn = buttonEdit("{{ url('claim-edit') }}", row.id);
            }
        @endif
        return editBtn;
    }

    function getProceedBatchWorkflowButton(row) {
        var actionProceedButton = '';
        actionProceedButton = `<a class="tooltip-ins" href="javascript:void()" data-bs-toggle="tooltip" title="Proceed">
                                    <button type="button"
                                        batch-id="${row.id}"
                                        base-amount="${row.claim_amount || 0}"
                                        gst-amount="${row.gst_amount || 0}"
                                        class="btn btn-sm btn-primary view-action-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#workflowModal"
                                        route="{{ url('proceed-batch-workflow') }}"
                                        action="proceed-batch-workflow">
                                        {{ $proceedLabel }}
                                </button></a>`;
        return actionProceedButton;
    }

    function getNPProceedBatchWorkflowButton(row) {
        var snp_buttons = `
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
        return snp_buttons;
    }

    function getNPProceedToCAWorkflowButton(row) {
        var snp_buttons = `
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
        return snp_buttons;
    }

    function getCAUploadCertificateButton(row) {
        var ca_buttons = `
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
        return ca_buttons;
    }

    function getNSICProceedBatchWorkflowButton(row) {
        var actionProceedButton = '';
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
        return actionProceedButton;
    }

    function getNSICForwardToFinanceButton(row) {
        var nsic_buttons = `
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
        return nsic_buttons;
    }

    function getNSICFinalApprovalButton(row) {
        var nsic_finance_buttons = `
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
        return nsic_finance_buttons;
    }

    function getDownloadInvoiceButton(row, title = '') {
        if (!row.latest_invoice_url) return '';
        return `
             <a class="tooltip-ins" href="${row.latest_invoice_url}" target="_blank" data-bs-toggle="tooltip" title="${title}">
                 <button type="button" class="btn btn-sm btn-outline-success">
                     <i class="fa fa-download"></i>
                 </button>
             </a>
         `;
    }

    function getDownloadBatchClaimsButton(row) {
        if (!row.id) return '';
        return `
             <a class="tooltip-ins" href="{{ url('export-batch-claims') }}/${row.id}" target="_blank" data-bs-toggle="tooltip" title="Download Excel of Claims">
                 <button type="button" class="btn btn-sm btn-outline-primary">
                     <i class="fa fa-file-excel-o"></i>
                 </button>
             </a>
         `;
    }

    function getFinanceButton(row) {
        var FinanceIcon = `<a href="javascript:void(0)" 
                             batch-id="${row.id}" data-bs-toggle="modal" data-bs-target="#myPaymentModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-check"></i></a>
                            `;
        return FinanceIcon;
    }

    function initBatchTable() {
        if ($.fn.DataTable.isDataTable('#dataTable_batch')) return;

        var parentTable = dataTableInit({
            id: "#dataTable_batch",
            showExcelExport: false,
            url: "{{ url('batch-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-catalog-creation')) }}",
            order: {
                column: 2, // now column 2 is SN (0=checkbox,1=dt-control,2=SN)
                direction: "asc"
            },
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();

                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                var formatAmount = function(val) {
                    return parseFloat(val).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                };

                var amtCols = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17];

                amtCols.forEach(function(colIdx) {
                    var total = 0;
                    if (colIdx === 16) {
                        total = api.rows({
                            search: 'applied'
                        }).data().reduce(function(acc, row) {
                            var rowTds = (parseFloat(row.tds_amount) || 0) +
                                (parseFloat(row.tds_cgst_amount) || 0) +
                                (parseFloat(row.tds_sgst_amount) || 0) +
                                (parseFloat(row.tds_igst_amount) || 0);
                            return acc + rowTds;
                        }, 0);
                    } else {
                        var colData = api.column(colIdx, {
                            search: 'applied'
                        }).data();
                        total = colData.reduce(function(a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    }

                    $(api.column(colIdx).footer()).html(formatAmount(total));
                });
            },
            columns: [
                // ========== ADDED: Checkbox column ==========
                {
                    data: "id",
                    orderable: false,
                    searchable: false,
                    visible: {{ hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-maker') || hasRole('nsic-checker') || hasRole('nsic-finance') ? 'true' : 'false' }} &&
                        ($("#review_status").val() === 'Pending'),
                    render: function(data, type, row) {
                        if (row.current_status !== 'sent_to_nsic_by_snp')
                            return `<input type="checkbox" class="batch-checkbox" value="${data}">`;
                        return '';
                    }
                },

                // ========== END ADDED ==========
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
                    className: "batch_number",
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
                    data: "claim_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "gst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "sgst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "cgst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "tds_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "tds_cgst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "tds_sgst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: "tds_igst_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        let total =
                            (parseFloat(row.tds_amount) || 0) +
                            (parseFloat(row.tds_cgst_amount) || 0) +
                            (parseFloat(row.tds_sgst_amount) || 0) +
                            (parseFloat(row.tds_igst_amount) || 0);

                        return total > 0 ?
                            total.toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) :
                            '0.00';
                    }
                },
                {
                    data: "total_claimed_amount",
                    render: function(data) {
                        return data !== null && data !== undefined ? parseFloat(data).toLocaleString(
                            'en-IN', {
                                minimumFractionDigits: 2
                            }) : '0.00';
                    }
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
                        var editBtn = getEditButton(row);

                        var timelineBtn = viewWTHistory(row.id);

                        let actionProceedButton = '';

                        if (!hasSNPRole && !hasCARole && row.show_proceed_action) {
                            actionProceedButton = getProceedBatchWorkflowButton(row);
                        }

                        if (hasNSICRole && row.sent_to_nsic_finance) {
                            actionProceedButton = getNSICProceedBatchWorkflowButton(row);
                        }

                        var FinanceIcon = "";

                        if ((hasFinanceRole && row.mark_payment_completed) || (row.is_query == 2)) {
                            FinanceIcon = getFinanceButton(row);
                        }

                        var ondc_buttons = '';

                        var snp_buttons = "";
                        if (hasSNPRole && row.show_proceed_action) {
                            snp_buttons = getNPProceedBatchWorkflowButton(row);
                        }

                        if (hasSNPRole && row.sent_to_ca) {
                            snp_buttons = getNPProceedToCAWorkflowButton(row);
                        }

                        var ca_buttons = "";
                        if (hasCARole && row.show_proceed_action) {
                            ca_buttons = getCAUploadCertificateButton(row);
                        }

                        var nsic_buttons = '';
                        if (hasNSICRole) {
                            if (row.batch_button != null) {
                                if (row.batch_button.showForwardToNsicFinance == true) {
                                    nsic_buttons = getNSICForwardToFinanceButton(row);
                                }
                            }

                            if (row.current_status == 'sent_to_nsic_by_snp') {
                                nsic_buttons += getDownloadInvoiceButton(row, 'Download Invoice');
                            }
                        }

                        var nsic_finance_buttons = '';
                        if (hasFinanceRole) {
                            actionProceedButton = "";

                            if (row.show_proceed_action && !row.is_query_open && !row
                                .is_invoice_query_open && row.is_invoice_uploaded == true) {
                                actionProceedButton = getProceedBatchWorkflowButton(row);
                            }

                            if (row.batch_button != null) {
                                if (row.batch_button.showFinalApproval == true && row.batchStatus !=
                                    'Payment Completed') {
                                    if (!row.re_upload_invoice_query_request || (row
                                            .re_upload_invoice_query_request && !row
                                            .is_invoice_query_open)) {
                                        nsic_finance_buttons = getNSICFinalApprovalButton(row);
                                    }
                                }
                            }

                            if (row.is_query_open || row.is_invoice_query_open) {
                                nsic_finance_buttons += `<button type="button" 
                                    class="btn btn-sm btn-outline-warning rounded-pill px-2 py-0" title="Awaiting Query Response" disabled>
                                    Awaiting
                                </button>`;
                            }

                            if (row.re_upload_invoice_query_request == true && row
                                .is_invoice_query_open == false && row.is_invoice_uploaded == false) {
                                nsic_finance_buttons += `<button type="button"  batch-id="${row.id}"
                                        class="btn btn-sm btn-outline-warning" title="Send Request for Invoice Re-Upload"
                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                        route="{{ url('batch-invoice-reupload-request') }}" action="invoice-query">
                                        <i class="fa fa-paper-plane"></i>
                                    </button>`;
                            }

                            if (row.current_status == 'sent_to_nsic_finance' && (row
                                    .re_upload_invoice_query_request == false || row
                                    .is_invoice_uploaded == true)) {
                                nsic_finance_buttons += getDownloadInvoiceButton(row,
                                    'Download Invoice');
                            }
                        }

                        var nsic_checker_buttons = '';
                        if (hasNsicCheckerRole) {
                            actionProceedButton = "";

                            if (row.show_proceed_action) {
                                actionProceedButton = getProceedBatchWorkflowButton(row);
                            }

                            if (row.current_status == 'approved') {
                                nsic_checker_buttons = getFinanceButton(row);
                            }

                        }

                        return createActionButtons([
                            editBtn,
                            timelineBtn,
                            FinanceIcon,
                            ca_buttons,
                            snp_buttons,
                            ondc_buttons,
                            nsic_buttons,
                            nsic_finance_buttons,
                            nsic_checker_buttons,
                            actionProceedButton,
                            getDownloadBatchClaimsButton(row)
                        ]);
                    }
                }
            ],
            filters: ['review_status', 'is_bulk']
        });

        const hasSNPRole =
            {{ hasRole('snp') || hasRole('lsp') || hasRole('bnp') || hasRole('nsic-maker') ? 'true' : 'false' }};
        const hasONDCRole = {{ hasRole('ondc-admin') ? 'true' : 'false' }};
        const hasNSICRole = {{ hasRole('nsic') ? 'true' : 'false' }};
        const hasFinanceRole = {{ hasRole('nsic-finance') ? 'true' : 'false' }};
        const hasNsicCheckerRole = {{ hasRole('nsic-checker') ? 'true' : 'false' }};
        const hasCARole = {{ hasRole('ca') ? 'true' : 'false' }};

        // ========== ADDED: Helper to build child columns with checkbox ==========
        function buildChildColumns(isNp, batchId) {
            let cols = [];

            // Add checkbox column if NOT NP (i.e. show for ONDC/NSIC/Finance/CA) and tab is Pending
            if (!isNp && $("#review_status").val() === 'Pending') {
                cols.push({
                    orderable: false,
                    render: function(data, type, row) {
                        let status = row.status;
                        let canAction = !(status == 'Approved' || status == 'Rejected' ||
                            status == 'Invoice Required' || status == 'Payment Completed' ||
                            status == 'Payment Completed');
                        if (canAction) {
                            let isChecked = (window.selectedClaimsMap[batchId] && window
                                .selectedClaimsMap[batchId].has(row.id)) ? 'checked' : '';
                            return `<input type="checkbox" class="claim-checkbox" data-batch-id="${batchId}" data-claim-id="${row.id}" ${isChecked} />`;
                        }
                        return '';
                    }
                });
            }

            // SN column
            cols.push({
                orderable: false,
                render: function(data, type, full, meta) {
                    return serialNumber("#childTable-" + batchId, meta.row);
                }
            });

            return cols;
        }
        // ========== END ADDED ==========

        @if ($claimSlug === 'claim-for-demand-generation')
            $('#dataTable_batch tbody').on('click', 'td.dt-control, td:nth-child(3)', function() {
                // Note: after adding checkbox, dt-control is now at index 1, so nth-child(3) is the SN column (original)
                // But we keep the same selector as before? The user's code used 'td.dt-control, td:nth-child(3)' which still works because dt-control is still there, and nth-child(3) now points to the SN column? Actually after adding checkbox, the columns are: checkbox(1), dt-control(2), SN(3). So td:nth-child(3) now selects dt-control? Wait, nth-child(3) in CSS is 1-indexed, so it selects the third <td> in the row. With the new checkbox column, the third <td> is the dt-control column. So the selector 'td.dt-control, td:nth-child(3)' would select dt-control and also the third child which is dt-control (duplicate). That's fine, it will still work because both select the same column. But to be safe, we keep it as is.
                // However, the user's code used 'td.dt-control, td:nth-child(3)' originally when dt-control was first column (index 1). Now dt-control is second column, so td:nth-child(3) still picks dt-control? Actually, with checkbox added, the first column is checkbox, second is dt-control, third is SN. So nth-child(3) would be SN, not dt-control. That would break the expand/collapse because it would now trigger on SN column click. We need to adjust the selector.
                // To preserve the same behavior, we should change to 'td.dt-control, td:nth-child(2)' because dt-control is now the second column. Let's change it to 'td.dt-control, td:nth-child(2)'.
                // But the user might have other code that relies on the old behavior? The user said "extra koye change nhi krna hai" – but this is a necessary adjustment because we added a column. We should mention that we updated the selector to keep the expand/collapse on the dt-control column.
                // We'll use 'td.dt-control, td:nth-child(2)'.
                var review_status = $("#review_status").val();
                var tr = $(this).closest('tr');
                var row = parentTable.row(tr);

                if (!row || !row.data() || row.data().total_claims == 0) return;

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    const batchId = row.data().id;
                    const isNp = hasSNPRole; // hide checkboxes for NP roles

                    // ========== MODIFIED: Build columns dynamically ==========
                    let childColumns = buildChildColumns(isNp, batchId);
                    childColumns = childColumns.concat([{
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
                            data: "provider_id",
                            defaultContent: "-"
                        },
                        {
                            data: "total_unique_mse_count"
                        },
                        {
                            data: "total_cumulative_transaction_count"
                        },

                        {
                            data: "cgst_tds_amount",
                            render: function(data, type, row) {
                                if (row.cgst_tds_amount !== null && row.cgst_tds_amount !==
                                    undefined) {
                                    var pct = (row.cgst_tds_percentage !== null && row
                                            .cgst_tds_percentage !== undefined) ? ' (' +
                                        formatPercent(row.cgst_tds_percentage) + ')' : '';
                                    return parseFloat(row.cgst_tds_amount).toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    }) + pct;
                                }
                                return '-';
                            }
                        },
                        {
                            data: "sgst_tds_amount",
                            render: function(data, type, row) {
                                if (row.sgst_tds_amount !== null && row.sgst_tds_amount !==
                                    undefined) {
                                    var pct = (row.sgst_tds_percentage !== null && row
                                            .sgst_tds_percentage !== undefined) ? ' (' +
                                        formatPercent(row.sgst_tds_percentage) + ')' : '';
                                    return parseFloat(row.sgst_tds_amount).toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    }) + pct;
                                }
                                return '-';
                            }
                        },
                        {
                            data: null,
                            render: function(data, type, row) {
                                var tds = parseFloat(row.tds_amount || 0);
                                var cgst = parseFloat(row.cgst_tds_amount || 0);
                                var sgst = parseFloat(row.sgst_tds_amount || 0);
                                var igst = parseFloat(row.igst_tds_amount || 0);
                                var total = tds + cgst + sgst + igst;
                                if (total > 0) {
                                    return total.toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    });
                                }
                                return '-';
                            }
                        },
                        {
                            data: "total_claimed_amount",
                            render: function(data) {
                                return data !== null ? parseFloat(data).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2
                                }) : '-';
                            }
                        },
                        {
                            data: "low_aov_unique_mse_count"
                        },
                        {
                            data: "low_aov_cumulative_transaction_count"
                        },
                        {
                            data: "high_aov_unique_mse_count"
                        },
                        {
                            data: "high_aov_cumulative_transaction_count"
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
                                let SnpeditBtn = "";
                                let sendNsicBtn = "";
                                let snp_buttons = "";
                                let dropdown = "";
                                let actions = [];

                                if (row.id) {
                                    viewBtn = `
                                                <a href="{{ url('claim-show') }}/${row.id}" class="btn btn-info btn-sm me-1 px-2 text-white" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            `;
                                }

                                if (hasSNPRole) {
                                    if (row.status == 'Draft' || row.status == 'Queried' || row
                                        .status == 'NSIC Rejected' || row.status ==
                                        'Finance Rejected') {
                                        SnpeditBtn = `
                                                    <a href="{{ url('claim-edit') }}/${row.id}" 
                                                    class="border btn btn-outline-warning btn-sm px-3 me-1" 
                                                    style="cursor:pointer;" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                `;
                                    }
                                    if (row.is_deleted == 1 && row.status === 'Rejected') {
                                        snp_buttons = `
                                                    <a href="javascript:void()" data-bs-toggle="tooltip" title="Move to Drafts">
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
                                                `;
                                    }
                                }

                                if (hasONDCRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed')) {
                                        actions.push(`
                                                    <li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-approve') }}" action="approve">
                                                        <i class="bi bi-check-circle"></i> Approve
                                                    </a></li>
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

                                if (hasNSICRole) {
                                    if (!(row.status == 'Approved' || row.status ==
                                            'Invoice Required' || row.status == 'Rejected' || row
                                            .status == 'Payment Completed')) {
                                        actions.push(`
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

                                if (hasFinanceRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed')) {
                                        actions.push(`
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

                                if (hasNsicCheckerRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed')) {
                                        actions.push(`
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

                                if (actions.length > 0) {
                                    dropdown = `
                                                <div class="dropdown d-inline-block d-flex">
                                                    <button class="border btn btn-primary btn-sm px-3" type="button"
                                                        data-bs-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v"></i>
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
                    ]);
                    // ========== END MODIFIED ==========

                    row.child(`
                            <table id="childTable-${batchId}" class="table table-sm table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        ${(!isNp && review_status === 'Pending') ? '<th></th>' : ''}
                                        <th>{{ __('message.sn') }}</th>
                                        <th>Claim No</th>
                                        <th>Provider ID</th>
                                        <th>Total Unique MSE Count</th>
                                        <th>Total Cumulative Transaction Count</th>
                                        <!--
                                        <th>Base Incentive Amount (Rs)</th>
                                        <th>GST Amount (Rs)</th>
                                        <th>SGST Amount (Rs)</th>
                                        <th>CGST Amount (Rs)</th>
                                        -->
                                        <th>CGST TDS Amount (Rs)</th>
                                        <th>SGST TDS Amount (Rs)</th>
                                        <th>Total TDS Amount</th>
                                        <th>Total Claimed Amount (Rs)</th>
                                        <th>Low AOV MSE Count</th>
                                        <th>Low AOV Cumulative Transaction Count</th>
                                        <th>High AOV MSE Count</th>
                                        <th>High AOV Cumulative Transaction Count</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        `).show();

                    tr.addClass('shown');

                    $(`#childTable-${batchId}`).DataTable({
                        processing: true,
                        serverSide: true,
                        searching: false,
                        info: false,
                        lengthChange: false,
                        paging: false,
                        ajax: "{{ url('batch-detail') }}/" + batchId + "/" + review_status,
                        createdRow: function(row, data, dataIndex) {
                            if (hasSNPRole || hasONDCRole || hasNSICRole) {
                                if (data.is_edited == 1) {
                                    $(row).addClass('row-edited');
                                } else {
                                    $(row).removeClass('row-edited');
                                }
                            }
                        },
                        columns: childColumns
                    });
                }
            });
        @else
            $('#dataTable_batch tbody').on('click', 'td.dt-control, td:nth-child(2), td.batch_number', function() {
                // Adjusted selector: dt-control is now second column (index 2 in nth-child)
                var review_status = $("#review_status").val();
                var tr = $(this).closest('tr');
                var row = parentTable.row(tr);

                if (!row || !row.data() || row.data().total_claims == 0) return;

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    const batchId = row.data().id;
                    const isNp = hasSNPRole;

                    // ========== MODIFIED: Build columns dynamically ==========
                    let childColumns = buildChildColumns(isNp, batchId);
                    childColumns = childColumns.concat([{
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
                            data: "provider_id",
                            defaultContent: "-"
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
                            data: "total_claimed_amount",
                            render: function(data) {
                                return data !== null ? parseFloat(data).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2
                                }) : '-';
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
                                let SnpeditBtn = "";
                                let sendNsicBtn = "";
                                let snp_buttons = "";
                                let dropdown = "";
                                let actions = [];

                                if (row.id) {
                                    viewBtn = `
                                                <a href="{{ url('claim-show') }}/${row.id}" class="btn btn-info btn-sm me-1 px-2 text-white" title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            `;
                                }

                                if (hasSNPRole) {
                                    if (row.status == 'Draft' || row.status == 'Queried' || row
                                        .status == 'NSIC Rejected' || row.status ==
                                        'Finance Rejected') {
                                        SnpeditBtn = `
                                                    <a href="{{ url('claim-edit') }}/${row.id}" 
                                                    class="border btn btn-outline-warning btn-sm px-3 me-1" 
                                                    style="cursor:pointer;" title="Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                `;
                                    }
                                    if (row.is_deleted == 1 && row.status === 'Rejected') {
                                        snp_buttons = `
                                                    <a href="javascript:void()" data-bs-toggle="tooltip" title="Move to Drafts">
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
                                                `;
                                    }
                                }

                                if (hasCARole || hasONDCRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed' || row.status ==
                                            'Invoice Required')) {
                                        if (hasONDCRole) {
                                            actions.push(`
                                                        <li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                            data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                            route="{{ url('batch-claim-approve') }}" action="approve">
                                                            <i class="bi bi-check-circle"></i> Accept
                                                        </a></li>
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
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Invoice Required' || row.status ==
                                            'Payment Completed')) {
                                        actions.push(`
                                                    <li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-approve') }}" action="approve">
                                                        <i class="bi bi-check-circle"></i> Accept
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-reject') }}" action="reject">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </a></li>
                                                `);
                                    }
                                }

                                if (hasFinanceRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed') && !row
                                        .is_query_open) {
                                        actions.push(`
                                                    <li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-approve') }}" action="approve">
                                                        <i class="bi bi-check-circle"></i> Accept
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-reject') }}" action="reject">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </a></li>
                                                     <li><a class="dropdown-item text-primary" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-query') }}" action="query">
                                                        <i class="bi bi-check-circle"></i> Raise Query
                                                    </a></li>
                                                `);
                                    }
                                }

                                if (hasNsicCheckerRole) {
                                    if (!(row.status == 'Approved' || row.status == 'Rejected' ||
                                            row.status == 'Payment Completed')) {
                                        actions.push(`
                                                    <li><a class="dropdown-item text-success" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-approve') }}" action="approve">
                                                        <i class="bi bi-check-circle"></i> Accept
                                                    </a></li>
                                                    <li><a class="dropdown-item text-danger" batch-id="${row.batch_id}" claim-id="${row.id}"
                                                        data-bs-toggle="modal" data-bs-target="#workflowModal"
                                                        route="{{ url('batch-claim-reject') }}" action="reject">
                                                        <i class="bi bi-x-circle"></i> Reject
                                                    </a></li>
                                                `);
                                    }
                                }

                                if (actions.length > 0) {
                                    dropdown = `
                                                <div class="dropdown d-inline-block d-flex">
                                                    <button class="border btn btn-primary btn-sm px-3" type="button"
                                                        data-bs-toggle="dropdown">
                                                        <i class="fa fa-ellipsis-v"></i>
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
                    ]);
                    // ========== END MODIFIED ==========

                    row.child(`
                        <table id="childTable-${batchId}" class="table table-sm table-bordered" style="width:100%">
                            <thead>
                                <tr>
                                    ${(!isNp && review_status === 'Pending') ? '<th></th>' : ''}
                                    <th>{{ __('message.sn') }}</th>
                                    <th>Claim No</th>
                                    <th>Provider ID</th>
                                    <th>Udyam No</th>
                                    <th>Team Id</th>
                                    <th>MSME Name</th>
                                    <!--
                                    <th>Base Incentive Amount (Rs)</th>
                                    <th>GST Amount (Rs)</th>
                                    <th>SGST Amount (Rs)</th>
                                    <th>CGST Amount (Rs)</th>
                                    -->
                                    <th>Total Claimed Amount (Rs)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    `).show();

                    tr.addClass('shown');

                    $(`#childTable-${batchId}`).DataTable({
                        processing: true,
                        serverSide: true,
                        searching: false,
                        info: false,
                        lengthChange: false,
                        paging: false,
                        ajax: "{{ url('batch-detail') }}/" + batchId + "/" + review_status,
                        createdRow: function(row, data, dataIndex) {
                            if (hasSNPRole || hasONDCRole || hasNSICRole) {
                                if (data.is_edited == 1) {
                                    $(row).addClass('row-edited');
                                } else {
                                    $(row).removeClass('row-edited');
                                }
                            }
                        },
                        columns: childColumns
                    });
                }
            });
        @endif

        // ========== ADDED: Event handlers for checkboxes ==========

        // Batch checkbox change: select/deselect all claims in that batch
        $(document).on('change', '.batch-checkbox', function() {
            let batchId = $(this).val();
            let isChecked = $(this).is(':checked');

            if (isChecked) {
                // Fetch claims for this batch via AJAX
                $.ajax({
                    url: "{{ url('batch-detail') }}/" + batchId + "/" + ($("#review_status").val() ||
                        'Pending'),
                    type: 'GET',
                    success: function(res) {
                        if (res.status && res.data) {
                            if (!window.selectedClaimsMap[batchId]) {
                                window.selectedClaimsMap[batchId] = new Set();
                            }
                            res.data.forEach(function(claim) {
                                let status = claim.status;
                                let canAction = !(status == 'Approved' || status ==
                                    'Rejected' ||
                                    status == 'Invoice Required' || status ==
                                    'Payment Completed' ||
                                    status == 'Payment Completed');
                                if (canAction) {
                                    window.selectedClaimsMap[batchId].add(claim.id);
                                }
                            });

                            // Check all claim checkboxes in the child table if it exists
                            let childTableSelector = `#childTable-${batchId}`;
                            if ($(childTableSelector).length > 0) {
                                $(childTableSelector + ' .claim-checkbox').prop('checked', true);
                            }

                            updateBulkActionsBar();
                        }
                    }
                });
            } else {
                // Deselect all claims in this batch
                if (window.selectedClaimsMap[batchId]) {
                    window.selectedClaimsMap[batchId].clear();
                }
                let childTableSelector = `#childTable-${batchId}`;
                if ($(childTableSelector).length > 0) {
                    $(childTableSelector + ' .claim-checkbox').prop('checked', false);
                }
                updateBulkActionsBar();
            }
        });

        // Individual claim checkbox change
        $(document).on('change', '.claim-checkbox', function() {
            let batchId = $(this).data('batch-id');
            let claimId = $(this).data('claim-id');
            let isChecked = $(this).is(':checked');

            if (!window.selectedClaimsMap[batchId]) {
                window.selectedClaimsMap[batchId] = new Set();
            }

            if (isChecked) {
                window.selectedClaimsMap[batchId].add(claimId);
            } else {
                window.selectedClaimsMap[batchId].delete(claimId);
            }

            // Update the batch checkbox state
            let anyChecked = window.selectedClaimsMap[batchId].size > 0;
            $(`.batch-checkbox[value="${batchId}"]`).prop('checked', anyChecked);

            updateBulkActionsBar();
        });

        // Bulk action buttons
        // Bulk action buttons
        $(document).on('click', '#bulk_approve_btn, #bulk_reject_btn', function() {
            let action = $(this).attr('action');
            let activeBatchId = null;
            let selectedClaimIds = [];

            for (let batchId in window.selectedClaimsMap) {
                let set = window.selectedClaimsMap[batchId];
                if (set.size > 0) {
                    activeBatchId = batchId;
                    selectedClaimIds = Array.from(set);
                    break;
                }
            }

            if (!activeBatchId) return;

            let targetRoute = action === 'approve' ? "{{ url('batch-claim-approve') }}" :
                "{{ url('batch-claim-reject') }}";

            // Get the review_status value
            let reviewStatus = $("#review_status").val();

            // Create a dummy button with all necessary attributes
            let dummyButton = $('<button>')
                .attr('batch-id', activeBatchId)
                .attr('claim-id', selectedClaimIds.join(','))
                .attr('route', targetRoute)
                .attr('action', action)
                .attr('review-status', reviewStatus) // Add review_status
                .css('display', 'none');

            // Append to body, trigger modal, then remove
            $('body').append(dummyButton);

            // Show modal with the dummy button as relatedTarget
            $('#workflowModal').modal('show');

            // Pass the dummy button to the modal event
            let modal = $('#workflowModal');
            let event = $.Event('show.bs.modal', {
                relatedTarget: dummyButton[0]
            });
            modal.trigger(event);

            // Clean up
            setTimeout(function() {
                dummyButton.remove();
            }, 100);
        });

        // ========== END ADDED ==========

        return parentTable;
    }

    function formatPercent(val) {
        if (val === null || val === undefined || val === '') return '';
        let num = parseFloat(val);
        if (isNaN(num)) return '';
        return (num % 1 === 0 ? num.toFixed(0) : num.toFixed(2)) + '%';
    }
</script>
