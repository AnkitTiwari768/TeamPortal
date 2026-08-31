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
            <table id="childTable-${row.data().id}" class="table table-sm table-bordered" style="width:100%">
                <thead>
                    <tr>
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
                        <th>TDS Amount (Rs)</th>
                        <th>IGST TDS Amount (Rs)</th>
                        <th>Total Claimed Amount (Rs)</th>
                        <th>Low AOV MSE ount</th>
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

        $(`#childTable-${row.data().id}`).DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            info: false,
            lengthChange: false,
            paging: false,
            ajax: "{{ url('batch-detail') }}/" + row.data().id + "/" + review_status,

            createdRow: function (row, data, dataIndex) {
                if (hasSNPRole || hasONDCRole || hasNSICRole) {
                    if (data.is_edited == 1) {
                        $(row).addClass('row-edited');
                    } else {
                        $(row).removeClass('row-edited');
                    }
                }
            }, // ✅ FIXED (removed extra comma issue context)

            columns: [
                {
                    orderable: false,
                    render: function (data, type, full, meta) {
                        return serialNumber("#dataTable_batch", meta.row);
                    }
                },
                {
                    data: "application_number",
                    render: function (data, type, row) {
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
                { data: "provider_id", defaultContent: "-" },
                { data: "total_unique_mse_count" },
                { data: "total_cumulative_transaction_count" },
                /*
                {
                    data: "amount",
                    render: function (data) {
                        return parseFloat(data).toLocaleString('en-IN', {
                            minimumFractionDigits: 2
                        });
                    }
                },
                {
                    data: "gst_amount",
                    render: function(data, type, row) {
                        if (row.gst_amount !== null && row.gst_amount !== undefined) {
                            var pct = (row.gst_percentage !== null && row.gst_percentage !== undefined) ? ' (' + row.gst_percentage + '%)' : '';
                            return parseFloat(row.gst_amount).toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + pct;
                        }
                        return '-';
                    }
                },
                {
                    data: "sgst_amount",
                    render: function(data, type, row) {
                        if (row.sgst_amount !== null && row.sgst_amount !== undefined) {
                            var pct = (row.sgst_percentage !== null && row.sgst_percentage !== undefined) ? ' (' + row.sgst_percentage + '%)' : '';
                            return parseFloat(row.sgst_amount).toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + pct;
                        }
                        return '-';
                    }
                },
                {
                    data: "cgst_amount",
                    render: function(data, type, row) {
                        if (row.cgst_amount !== null && row.cgst_amount !== undefined) {
                            var pct = (row.cgst_percentage !== null && row.cgst_percentage !== undefined) ? ' (' + row.cgst_percentage + '%)' : '';
                            return parseFloat(row.cgst_amount).toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + pct;
                        }
                        return '-';
                    }
                },
                */
                {
                    data: "tds_amount",
                    render: function(data, type, row) {
                        if (row.tds_amount !== null && row.tds_amount !== undefined) {
                            var pct = (row.tds_percentage !== null && row.tds_percentage !== undefined) ? ' (' + row.tds_percentage + '%)' : '';
                            return parseFloat(row.tds_amount).toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + pct;
                        }
                        return '-';
                    }
                },
                {
                    data: "igst_tds_amount",
                    render: function(data, type, row) {
                        if (row.igst_tds_amount !== null && row.igst_tds_amount !== undefined) {
                            var pct = (row.igst_tds_percentage !== null && row.igst_tds_percentage !== undefined) ? ' (' + row.igst_tds_percentage + '%)' : '';
                            return parseFloat(row.igst_tds_amount).toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            }) + pct;
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
                { data: "low_aov_unique_mse_count" },
                { data: "low_aov_cumulative_transaction_count" },
                { data: "high_aov_unique_mse_count" },
                { data: "high_aov_cumulative_transaction_count" },
                {
                    render: function (data, type, row) {
                        return createBadgeByStatus(row.status);
                    }
                },
                {
                    orderable: false,
                    render: function (data, type, row) {

                        let viewBtn = "";
                        let SnpeditBtn = "";
                        let sendNsicBtn = "";
                        let snp_buttons = "";

                        if (row.id) {
                            viewBtn = `
                                <a href="{{ url('claim-show') }}/${row.id}" class="btn btn-info btn-sm me-1 px-2 text-white" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                            `;
                        }

                        if (hasSNPRole) {
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

                        let actions = [];

                        if (hasCARole || hasONDCRole) {
                            if (!(row.status == 'Approved' || row.status == 'Rejected' || row.status == 'Payment_completed')) {
                                if (hasONDCRole) {
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
                        }

                        if (hasNSICRole) {
                            if (!(row.status == 'Approved' || row.status == 'Invoice Required' || row.status == 'Rejected' || row.status == 'Payment_completed')) {
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
                            if (!(row.status == 'Approved' || row.status == 'Rejected' || row.status == 'Payment Completed')) {
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

                        let dropdown = "";
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
            ]
        });
    }
