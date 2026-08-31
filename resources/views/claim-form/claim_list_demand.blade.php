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
                            <th>BNP ID</th>
                            <th>BNP Name</th>
                            <th>BppID/Provider ID</th>
                            <th>Base Incentive Amount (Rs)</th>
                            <th>GST Amount (Rs)</th>
                            <th>SGST Amount (Rs)</th>
                            <th>CGST Amount (Rs)</th>
                            <th>TDS Amount (Rs)</th>
                            <th>CGST TDS Amount</th>                           
                            <th>SGST TDS Amount</th>   
                            <th>Total TDS Amount </th>                         
                            <th>Total Claimed Amount (Rs)</th>
                            <th>Low AOV Unique MSEs</th>
                            <th>Low AOV Cumulative Transactions</th>
                            <th>High AOV Unique MSEs</th>
                            <th>High AOV Cumulative Transactions</th>
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
    var selectedRows = {};

    function initClaimTable() {
        if ($.fn.DataTable.isDataTable('#dataTable')) return;
        var claimTable = dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            order: {
                column: 0,
                direction: "asc"
            },
            url: "{{ url('claims-list/' . ($tabs['claim_type_slug'] ?? 'claim-for-demand-generation')) }}",
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
                        data: null,
                        render: function(data, type, row) {
                            let total =
                                (parseFloat(row.tds_amount) || 0) +
                                (parseFloat(row.tds_cgst_amount) || 0) +
                                (parseFloat(row.tds_sgst_amount) || 0);

                            return total > 0
                                ? total.toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })
                                : '-';
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
                        return row.low_aov_eligible_records ?? '-';
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
                        return row.high_aov_eligible_records ?? '-';
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
