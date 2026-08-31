/**
 * distribution.js
 * Consolidated JS handlers for Enterprise Fund Distribution.
 * Uses api.js for reliable HTTP dispatch.
 */

/**
 * 1. INITIALIZE DASHBOARD LISTING VIEW
 */
function initDistributionListing(config) {
    $(document).ready(function () {
        // Master Cascade for Filters
        $('#major_component_id').on('change', function () {
            var majorId = $(this).val();
            var $subSelect = $('#sub_component_id');
            $subSelect.empty().append('<option value="">Loading...</option>');

            if (!majorId) {
                $subSelect.empty().append('<option value="">Select Sub Component</option>');
                return;
            }

            // Use standard api.js dispatcher
            apiRequest({
                url: config.subComponentUrl,
                method: 'GET',
                params: { component_id: majorId }
            }).then(function (res) {
                $subSelect.empty().append('<option value="">Select Sub Component</option>');
                var items = Array.isArray(res) ? res : (res.data || []);
                $.each(items, function (i, item) {
                    $subSelect.append('<option value="' + item.id + '">' + (item.name || item.attribute_value) + '</option>');
                });
            }).catch(function () {
                $subSelect.empty().append('<option value="">Select Sub Component</option>');
            });
        });

        // Initialize High-Fidelity Datatable
        // dataTableInit({
        //     id: "#dataTable",
        //     showExcelExport: true,
        //     showCustomExportOption: true,
        //     order: { column: 4, direction: "desc" },
        //     url: config.summaryListUrl,
        //     columns: [
        //         {
        //             "orderable": false,
        //             "render": function (data, type, full, meta) {
        //                 return serialNumber("#dataTable", meta.row);
        //             }
        //         },
        //         { "orderable": true, "data": "financial_year" },
        //         { "orderable": true, "data": "major_component_name" },
        //         {
        //             "orderable": true,
        //             "render": function (data, type, row) {
        //                 return row.sub_component_name ? row.sub_component_name : '<span class="badge bg-light text-dark text-muted">None</span>';
        //             }
        //         },
        //         {
        //             "orderable": true,
        //             "className": "text-end fw-bold text-primary",
        //             "render": function (data, type, row) {
        //                 return Number(row.total_allocated).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        //             }
        //         },
        //         {
        //             "orderable": true,
        //             "className": "text-end fw-bold text-success",
        //             "render": function (data, type, row) {
        //                 return Number(row.total_distributed).toLocaleString('en-IN', { minimumFractionDigits: 2 });
        //             }
        //         },
        //         {
        //             "orderable": true,
        //             "className": "text-end fw-bold",
        //             "render": function (data, type, row) {
        //                 let val = Number(row.remaining);
        //                 let color = val < 0 ? 'text-danger' : 'text-success';
        //                 return `<span class="${color}">${val.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>`;
        //             }
        //         },
        //         {
        //             "orderable": false,
        //             "className": "text-center",
        //             "render": function (data, type, row) {
        //                 let targetSub = row.sub_component_id ? row.sub_component_id : 'NULL';
        //                 let drillUrl = `${config.drillDownBaseUrl}/${row.financial_year}/${row.major_component_id}/${targetSub}`;

        //                 return `<a href="${drillUrl}" class="btn btn-sm btn-info text-white shadow-sm" title="Deep Drill Analytics">
        //                             <i class="fa fa-eye"></i> View Analysis
        //                         </a>`;
        //             }
        //         }
        //     ],
        //     filters: ["financial_year", "major_component_id", "sub_component_id"]
        // });
    });
}

/**
 * 2. INITIALIZE CREATION CANVAS FORM
 */
function initDistributionCreateForm(config) {
    $(document).ready(function () {
        // Form Utility: Check Pool Balance Functionality
        function checkPoolBalance() {
            var fy = $('#fy').val();
            var dur = $('#duration').val();
            var maj = $('#major_component').val();

            if (fy && dur && maj) {
                apiRequest({
                    url: config.fetchPoolUrl,
                    method: 'POST',
                    data: {
                        financial_year: fy,
                        duration_id: dur,
                        sub_duration_id: $('#sub_duration').val(),
                        major_component_id: maj,
                        sub_component_id: $('#sub_component').val()
                    }
                }).then(function (res) {
                    var $banner = $('#pool-balance-banner');
                    $banner.slideDown(200);

                    if (res.found) {
                        $('#disp-pool-remaining').text('₹' + res.remaining_balance.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $('#disp-pool-allocated').text('₹' + res.total_allocated.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $('#disp-pool-distributed').text('₹' + res.total_distributed.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
                        $banner.removeClass('alert-danger').addClass('alert-light border');
                        $('#disp-pool-remaining').removeClass('text-danger').addClass('text-dark');
                        if (res.remaining_balance < 0) $('#disp-pool-remaining').addClass('text-danger');
                    } else {
                        $('#disp-pool-remaining').text('No Allocation Detected').addClass('text-danger');
                        $('#disp-pool-allocated').text('₹0.00');
                        $('#disp-pool-distributed').text('₹0.00');
                        $banner.removeClass('alert-light').addClass('alert-danger');
                    }
                }).catch(function () {
                    $('#pool-balance-banner').slideUp(100);
                });
            } else {
                $('#pool-balance-banner').slideUp(100);
            }
        }

        // A. Master Cascades Logic
        $('#duration').on('change', function () {
            var parentId = $(this).val();
            var $sub = $('#sub_duration');
            var $container = $('#sub_duration_container');
            $sub.empty().append('<option value="">Loading...</option>');

            if (!parentId) {
                $sub.empty().append('<option value="">Select Sub-Duration</option>');
                $container.hide();
                checkPoolBalance();
                return;
            }

            apiRequest({
                url: config.subDurationUrl,
                method: 'GET',
                params: { parent_id: parentId }
            }).then(function (res) {
                $sub.empty().append('<option value="">Select Sub-Duration</option>');
                var items = Array.isArray(res) ? res : (res.data || []);
                if (items.length > 0) {
                    $container.show();
                    $.each(items, function (i, item) {
                        $sub.append('<option value="' + item.id + '">' + (item.name || item.attribute_value) + '</option>');
                    });
                } else {
                    $container.hide();
                    $sub.val('');
                }
                checkPoolBalance();
            });
        });

        $('#major_component').on('change', function () {
            var compId = $(this).val();
            var $sub = $('#sub_component');
            $sub.empty().append('<option value="">Loading...</option>');

            if (!compId) {
                $sub.empty().append('<option value="">Select Sub-Component</option>');
                return;
            }

            apiRequest({
                url: config.subComponentUrl,
                method: 'GET',
                params: { component_id: compId }
            }).then(function (res) {
                $sub.empty().append('<option value="">Select Sub-Component</option>');
                var items = Array.isArray(res) ? res : (res.data || []);
                $.each(items, function (i, item) {
                    $sub.append('<option value="' + item.id + '">' + (item.name || item.attribute_value) + '</option>');
                });
            });
        });

        // B. Trigger Pool Lookup on Dimension Change
        $('.trigger-pool').on('change', function () {
            checkPoolBalance();
        });

        // C. Automated Calculator Engine
        $('.calc-trigger').on('input', function () {
            var amt = parseFloat($('#amount').val()) || 0;
            var pct = parseFloat($('#tds_pct').val()) || 0;
            var tds = (amt * pct) / 100;
            var net = amt - tds;

            $('#calc-tds').text('₹' + tds.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
            $('#calc-net').text('₹' + net.toLocaleString('en-IN', { minimumFractionDigits: 2 }));
        });

        // D. Datepicker check
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true, autoclose: true, endDate: "today"
            });
        }

        // E. High-speed asset uploader using apiUploadFile
        $('#file_upload_raw').on('change', function () {
            var fileData = $(this).prop('files')[0];
            if (!fileData) return;

            var formData = new FormData();
            formData.append('file', fileData);

            $('#upload-status').html('<i class="fa fa-spinner fa-spin me-1"></i> Uploading...');

            apiUploadFile(config.uploadAssetUrl, formData).then(function (res) {
                if (res.status) {
                    $('#upload_document_hidden').val(res.data.file_system_name);
                    $('#upload-status').html('<span class="text-success fw-bold"><i class="fa fa-check"></i> File uploaded successfully.</span>');
                } else {
                    $('#upload-status').html('<span class="text-danger">Upload rejected.</span>');
                }
            }).catch(function () {
                $('#upload-status').html('<span class="text-danger">Upload failed.</span>');
            });
        });

       $('#distForm').on('submit', function (e) {
    e.preventDefault();

    $('.err-msg').text('');

    var $btn = $('#save-btn');
    var initialText = $btn.html();

    $btn.prop('disabled', true)
        .html('<i class="fa fa-circle-notch fa-spin"></i> Committing...');

    // hide previous alert properly
    $('#api-error-alert')
        .addClass('d-none')
        .removeClass('show')
        .find('#api-error-text')
        .text('');

    // convert form data to JSON
    var arrayData = $(this).serializeArray();
    var jsonData = {};

    $.map(arrayData, function (n) {
        jsonData[n.name] = n.value;
    });

    apiRequest({
        url: config.storeUrl,
        method: 'POST',
        data: jsonData
    })
    .then(function () {

        if (window.toastr) {
            toastr.success("Transaction Committed Successfully.");
        }

        window.location.href = config.redirectIndexUrl;

    })
    .catch(function (err) {

        $btn.prop('disabled', false).html(initialText);

        // reset alert first
        $('#api-error-alert')
            .addClass('d-none')
            .removeClass('show')
            .find('#api-error-text')
            .text('');

        if (err.status === 422 && err.body) {

            var errors = err.body.errors || err.body.message;

            // FIELD LEVEL ERRORS
            if (typeof errors === 'object') {
                $.each(errors, function (key, val) {
                    $('#' + key + '_error').text(val[0]);
                });
            }

            // GLOBAL FATAL ERROR (POOL ERROR ETC.)
            if (err.body.errors && err.body.errors.fatal) {

                $('#api-error-alert')
                    .removeClass('d-none')
                    .addClass('show')
                    .find('#api-error-text')
                    .text(err.body.errors.fatal);
            }

        } else {

            $('#api-error-alert')
                .removeClass('d-none')
                .addClass('show')
                .find('#api-error-text')
                .text("Failed storing record. Please try again.");
        }
    });
});
    });
}

/**
 * 3. INITIALIZE DETAILS / DRILLDOWN VIEW
 */
function initDistributionDetails(config) {
    $(document).ready(function () {
        // Activate Global Datepickers for detailed filtering
        $('.datepicker').datepicker({ format: 'dd-mm-yyyy', autoclose: true, todayHighlight: true });

        // Trigger Redraw on any input change
        $('.filter-trigger').on('change', function () {
            $('#transactionsTable').DataTable().draw();
        });

        // ── 1. Custom External Search Hook ─────────────────────────────────────
        // Manually relays keystrokes to underlying DataTable core SEARCH protocol
        $('#custom_search').on('keyup paste', function () {
            $('#transactionsTable').DataTable().search(this.value).draw();
        });

        // ── 2. Extended Reset Pipeline ──────────────────────────────────────────
        $('#reset-filters').on('click', function () {
            $('.filter-trigger').val('');
            $('#custom_search').val('');

            let table = $('#transactionsTable').DataTable();
            table.search('').draw(); // Clear internal search register alongside redraw
        });

        // Initialize Detail Log DataTable
        dataTableInit({
            id: "#transactionsTable",
            url: config.drillDataUrl,
            showExcelExport: true,
            filters: ["source_type", "from_date", "to_date"],
            order: { column: 2, direction: "desc" },
            columns: [
                {
                    "data": null,
                    "orderable": false,
                    "render": function (data, type, full, meta) {
                        return serialNumber("#transactionsTable", meta.row);
                    }
                },
                {
                    "data": "source_type",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let labelClass = row.source_type === 'MANUAL' ? 'bg-primary' : 'bg-warning text-dark';
                        return `<span class="badge rounded-pill ${labelClass}">${row.source_type}</span>`;
                    }
                },
                {
                    "data": "sanction_order_date",
                    "orderable": true,
                    "render": function (data, type, row) {
                        if (!row.sanction_order_date) return 'N/A';
                        let d = new Date(row.sanction_order_date);
                        return d.toLocaleDateString('en-GB');
                    }
                },
                {
                    "data": "duration_name",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let sub = row.sub_duration_name ? `<br><small class='text-muted'>${row.sub_duration_name}</small>` : '';
                        return `<span class='fw-bold'>${row.duration_name || 'N/A'}</span>${sub}`;
                    }
                },
                {
                    "data": "distribution_amount",
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function (data, type, row) {
                        return Number(row.distribution_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": "tds_percentage",
                    "orderable": true,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        return `<span class='text-secondary'>${row.tds_percentage}%</span>`;
                    }
                },
                {
                    "data": "net_payable_amount",
                    "orderable": true,
                    "className": "text-end fw-bold ",
                    "render": function (data, type, row) {
                        return Number(row.net_payable_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        let viewUrl = `${config.baseUrl}/${row.id}/show`;
                        let editUrl = `${config.baseUrl}/${row.id}/edit`;

                        return `
                            <div class="btn-group btn-group-sm">
                                <a href="${viewUrl}" class="btn btn-light border btn-sm" title="View Ledger"><i class="fa fa-eye text-muted"></i></a>
                                <a href="${editUrl}" class="btn btn-light border btn-sm" title="Modify Record"><i class="fa fa-edit text-primary"></i></a>
                            </div>
                        `;
                    }
                }
            ]
        });

        // ── 3. Visual Cleanup ─────────────────────────────────────────────
        // Hide the original generated DataTables filter so only our clean custom search is visible
        setTimeout(() => {
            $('#transactionsTable_filter').hide();
        }, 300);

    });
}
