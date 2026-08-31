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
                        <th>Team ID of MSE</th>
                        <th>Name of MSE</th>
                        <th>Udyam No</th>
                        <th>Catalogue ID</th>
                        <th>Catalogue Finalization Date</th>
                        <th>Total Claimed Amount (Rs)</th>
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
        url: "{{ url('reject-claim-list/' . (isset($tabs) && isset($tabs['claim_type_slug']) ? $tabs['claim_type_slug'] : 'claim-for-ai-cataloguing')) }}",
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
                    return serialNumber("#reject_dataTable", meta.row);
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
                    return row.team_registration_id ?? '';
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
                    return row.msme_udyam_number ?? '';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.catalogue_id ?? '';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.catalogue_finalization_date ?? '';
                }
            },
            {
                orderable: true,
                render: function(data, type, row) {
                    return row.total_claimed_amount ?? '-';
                }
            },
            {
                orderable: false,
                render: function(data, type, row) {
                    var viewBtn = buttonView("{{ url('claim-show') }}", row.id);
                    var editBtn = '';
                    var FinanceIcon = "";
                    return createActionButtons([viewBtn, editBtn, FinanceIcon]);
                }
            }
        ],
        createdRow: true,
        filters: ['review_status']
    });

    return rejecttable;
}
</script>
