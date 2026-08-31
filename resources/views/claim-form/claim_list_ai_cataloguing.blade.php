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
                            <th>Team ID of MSE</th>
                            <th>Name of MSE</th>
                            <th>Udyam No</th>
                            <th>Catalogue ID</th>
                            <th>Catalogue Finalization Date</th>
                            <th>Catalogue Completion Status</th>
                            <th>Digital Catalogue Footprint</th>
                            <th>Amount Claimed with Reconciliation Report</th>
                            <th>Total Claimed Amount (Rs)</th>
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
    <div>
        <button type="button" id="create_batch" class="btn btn-primary">
            Create Batch & Forward to ONDC
        </button>
    </div>
</div>

<script>
    console.log('claim_list_ai_cataloguing script block evaluated.');
    var selectedRows = {};

    function initClaimTable() {
        console.log('called datable');

        if ($.fn.DataTable.isDataTable('#dataTable')) return;

        var claimTable = dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 0,
                direction: "asc"
            },
            url: "{{ url('claims-list/' . (isset($tabs) && isset($tabs['claim_type_slug']) ? $tabs['claim_type_slug'] : 'claim-for-ai-cataloguing')) }}",
            columns: [{
                    "orderable": false,
                    "className": "noExport",
                    "render": function(data, type, row) {
                        var checked = selectedRows[row.id] ? 'checked' : '';
                        return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' +
                            checked + '>';
                    }
                },
                {
                    orderable: false,
                    render: function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
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
                        return row.catalogue_completion_status !== null ? (row
                            .catalogue_completion_status == 1 ? 'Yes' : 'No') : '-';
                    }
                },
                {
                    orderable: true,
                    render: function(data, type, row) {
                        return row.digital_catalogue_footprint !== null ? (row
                            .digital_catalogue_footprint == 1 ? 'Yes' : 'No') : '-';
                    }
                },
                {
                    orderable: true,
                    render: function(data, type, row) {
                        return row.amount_claimed_with_financial_reconciliation !== null ? (row
                            .amount_claimed_with_financial_reconciliation == 1 ? 'Yes' : 'No') : '-';
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

                        if (typeof hasFinanceRole !== "undefined" && hasFinanceRole && row.status ===
                            'Approved') {
                            FinanceIcon = '<a href="javascript:void(0)" claim-id="' + row.id +
                                '" data-bs-toggle="modal" data-bs-target="#myPaymentModal" class="btn btn-sm btn-primary btn-circle m-1" title="Payment"><i class="fa fa-check"></i></a>';
                        }

                        var deleteBtn = '<a href="javascript:void(0)" onclick="deleteClaim(\'' + row
                            .id +
                            '\')" class="btn btn-sm btn-danger btn-circle m-1" title="Delete Claim"><i class="fa fa-trash"></i></a>';

                        return createActionButtons([viewBtn, editBtn, FinanceIcon, deleteBtn]);
                    }
                }
            ],
            createdRow: true,
            filters: ['review_status', 'is_bulk']
        });

        return claimTable;
    }

    function deleteClaim(id) {
        if (!confirm('Are you sure you want to delete this claim?')) {
            return;
        }

        $.ajax({
            url: "{{ url('claims') }}/" + id,
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(res) {
                if (res.success || res.status) {
                    toastr.success(res.message || 'Claim deleted successfully.');
                    if ($.fn.DataTable.isDataTable('#dataTable')) {
                        $('#dataTable').DataTable().ajax.reload();
                    }
                } else {
                    toastr.error(res.message || 'Failed to delete claim.');
                }
            },
            error: function(xhr) {
                toastr.error('Error deleting claim.');
            }
        });
    }

    $(document).on("change", ".row-checkbox", function() {
        var id = $(this).val();
        selectedRows[id] = $(this).prop("checked");

        var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
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
</script>
