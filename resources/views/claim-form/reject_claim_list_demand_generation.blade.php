<div class="card-body">
    <input type="hidden" id="review_status" value="">

    @php
        $dataTableComponent = '
        <div class="table-responsive">
            <table class="table" id="reject_dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>' . __('message.sn') . '</th>
                        <th>Batch No</th>
                        <th>Claim No</th>
                        <th>BNP ID</th>
                        <th>BNP Name</th>
                        <th>BppID/Provider ID</th>
                        <th>Base Incentive Amount (Rs)</th>
                        <th>GST Amount (Rs)</th>
                        <th>SGST Amount (Rs)</th>
                        <th>CGST Amount (Rs)</th>
                        <th>TDS Amount (Rs)</th>
                        <th>Total Claimed Amount (Rs)</th>
                        <th>Low AOV Unique MSEs</th>
                        <th>Low AOV Cumulative Transacions</th>
                        <th>High AOV Unique MSEs</th>
                        <th>High AOV Cumulative Transactions</th>
                        <th class="actions">' . __('message.action') . '</th>
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
var selectedRows = {};

function initRejectTable() {
    if ($.fn.DataTable.isDataTable('#reject_dataTable')) return;

    var rejecttable = dataTableInit({
        id: "#reject_dataTable",
        showExcelExport: true,
        order: {
            column: 0,
            direction: "asc"
        },
        url: "{{ url('reject-claim-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-catalog-creation')) }}",
        columns: [
            {
                orderable: false,
                className: "noExport",
                render: function(data, type, row) {
                    var checked = selectedRows[row.id] ? 'checked' : '';
                    return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
                }
            },
            {
                orderable: false,
                render: function(data, type, full, meta) {
                    return full.is_bulk
                        ? serialNumber("#reject_dataTable", meta.row)
                        : serialNumber("#reject_dataTable", meta.row);
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.batch_number ?? '';
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
                    return row.bpp_id ?? '';
                }
            },
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
                        var pct = (row.gst_percentage !== null && row.gst_percentage !== undefined) ? ' (' + row.gst_percentage + '%)' : '';
                        return row.gst_amount + pct;
                    }
                    return '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    if (row.sgst_amount !== null && row.sgst_amount !== undefined) {
                        var pct = (row.sgst_percentage !== null && row.sgst_percentage !== undefined) ? ' (' + row.sgst_percentage + '%)' : '';
                        return row.sgst_amount + pct;
                    }
                    return '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    if (row.cgst_amount !== null && row.cgst_amount !== undefined) {
                        var pct = (row.cgst_percentage !== null && row.cgst_percentage !== undefined) ? ' (' + row.cgst_percentage + '%)' : '';
                        return row.cgst_amount + pct;
                    }
                    return '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    if (row.tds_amount !== null && row.tds_amount !== undefined) {
                        var pct = (row.tds_percentage !== null && row.tds_percentage !== undefined) ? ' (' + row.tds_percentage + '%)' : '';
                        return row.tds_amount + pct;
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
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.low_aov_unique_mse_count ?? '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.low_aov_cumulative_transaction_count ?? '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.high_aov_unique_mse_count ?? '-';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.high_aov_cumulative_transaction_count ?? '-';
                }
            },
            {
                orderable: false,
                render: function(data, type, row) {
                    var timelineBtn = viewWTHistory(row.batch_id);
                    var viewBtn = buttonView("{{ url('claim-show') }}", row.id);
                    var moveDrafts = '';

                    @if (hasRole('snp') || hasRole('lsp') || hasRole('bnp'))
                        moveDrafts = `<a href="javascript:void(0)" id="move_to_drafts" claim-id="${row.id}">Move to drafts</a>`;
                    @endif

                    return createActionButtons([viewBtn, moveDrafts, timelineBtn]);
                }
            }
        ],
        createdRow: true,
        filters: ['review_status', 'is_bulk']
    });

    return rejecttable;
}

$(document).on("change", ".row-checkbox", function() {
    var id = $(this).val();
    selectedRows[id] = $(this).prop("checked");

    var allChecked = $(".row-checkbox").length &&
        $(".row-checkbox:checked").length === $(".row-checkbox").length;

    $("#selectAll").prop("checked", allChecked);
});

$(document).on("change", "#selectAll", function() {
    var checked = $(this).prop("checked");

    $(".row-checkbox").each(function() {
        $(this).prop("checked", checked);
        selectedRows[$(this).val()] = checked;
    });
});
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