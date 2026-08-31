$('[data-dismiss=modal]').on('click', function (e) {
    $(this).find("textarea").val('').end();
})



var oTable;

/*
function dataTableInit(config) {
    var oTable = $(config.id).DataTable({
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]], // Add "All" option
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        scrollY: "1024px",
        deferRender: true,

        order: [[config.order.column, config.order.direction]],
        searching: config.searching !== undefined ? config.searching : true,

        ajax: {
            url: config.url,
            dataType: "json",
            type: "GET",
            data: function (d, settings) {
                var api = new $.fn.dataTable.Api(settings);
                d.page = api.page() + 1;
                d.filters = {};

                if (config.filters) {
                    for (var filter in config.filters) {
                        var field = config.filters[filter];
                        d.filters[field] = $(`#${field}`).val();
                    }
                }
            },
            dataSrc: function (json) {
                json.draw = json.data.draw;
                json.recordsTotal = json.data.recordsTotal;
                json.recordsFiltered = json.data.recordsFiltered;
                return json.data.data;
            }
        },

        columns: config.columns,

        rowCallback: function (row, data, index) {
            var pageInfo = oTable.page.info();
            var serialNumber = pageInfo.page * pageInfo.length + index + 1;
            $('td:eq(0)', row).html(serialNumber);
        },

        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ rows",
            infoEmpty: 'Showing 0 to 0 of 0 rows',
            infoFiltered: "(filtered from _MAX_ total rows)",
            lengthMenu: "Show _MENU_ rows"
        }
    });

    return oTable;
}
    */


function dataTableInit(config) {
    oTable = $(config.id).DataTable({
        "lengthMenu": [
            [10, 25, 50, 100, 1000, 10000, 100000, 1000000],
            [10, 25, 50, 100, 1000, 10000, 100000, 1000000]
        ],
        "columnDefs": [
            { "width": "auto", "targets": 0 }
        ],
        processing: true,
        serverSide: true,
        exportOptions: {
            //columns: ':visible:not(.notexport)',	
            modifer: {
                "lengthMenu": [
                    [10, 25, 50, 100, 1000, 10000, 100000, 1000000],
                    [10, 25, 50, 100, 1000, 10000, 100000, 1000000]
                ],
                search: 'none'
            }
        },

        scrollX: true,
        // "columnDefs": [
        //     { "width": "auto", "targets": 0 }
        // ],
        // lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]], // Add "All" option
        // scrollX: true,
        // scrollCollapse: true,
        // autoWidth: false,
        // scrollY: "1024px", // Set table height
        // scrollCollapse: true,
        // scroller: true, // Enable lazy loading
        // deferRender: true,

        order: [[config.order.column, config.order.direction]],
        searching: (config.searching === false) ? config.searching : true,
        oLanguage: {
            // sProcessing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
        },

        ajax: {
            url: config.url,
            dataType: "json",
            type: "GET",
            data: function (d, settings) {
                var api = new $.fn.dataTable.Api(settings);
                d.page = api.page() + 1;
                d.filters = {};

                if (config.filters) {
                    for (var filter in config.filters) {
                        var field = config.filters[filter];
                        d.filters[field] = $(`#${field}`).val();
                    }
                }

            },
            dataSrc: function (json) {

                json.draw = json.data.draw;
                json.recordsTotal = json.data.recordsTotal;
                json.recordsFiltered = json.data.recordsFiltered;
                if (typeof config.onDataLoaded === 'function') {
                    config.onDataLoaded(json.data);
                }
                return json.data.data;
            }
        },
        columns: config.columns,

        createdRow: config.createdRow ? function (row, data, dataIndex) {
            if (data.is_bulk) {
                $(row).addClass('row-bulk-exl');
            } else {
                $(row).addClass('row-bulk-manual');
            }
        } : null,


        rowReorder: config.rowReorder ? config.rowReorder : false,

        language: {
            info: "Showing _START_ to _END_ of _TOTAL_ rows",
            infoEmpty: 'Showing 0 to 0 of 0 rows',
            infoFiltered: "(filtered from _MAX_ total rows)",
            lengthMenu: "Show _MENU_ rows"
        }
    });

    // if (config.showExcelExport === true) {
    //     new $.fn.dataTable.Buttons(oTable, {
    //         buttons: [
    //             {
    //                 extend: 'excel',
    //                 text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
    //                 exportOptions: {
    //                     columns: ':visible:not(.actions)'
    //                 },
    //                 customize: function (xlsx) {
    //                     var sheet = xlsx.xl.worksheets['sheet1.xml'];
    //                     $('row:first c', sheet).attr('s', '2');
    //                 }
    //             },
    //             {
    //                 extend: 'pdfHtml5',//Pratibha
    //                 orientation: 'landscape',
    //                 pageSize: 'LEGAL',//Pratibha
    //                 text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Pdf',
    //                 exportOptions: {
    //                     columns: ':visible:not(.actions)'
    //                 },
    //                 customize: function (doc) {
    //                     doc.styles.tableHeader.fontSize = 10; // Adjust font size for table header
    //                     doc.styles.tableBodyEven.fontSize = 10; // Adjust font size for even rows
    //                     doc.styles.tableBodyOdd.fontSize = 10; // Adjust font size for odd rows
    //                     doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 2).join('*').split('');
    //                     doc.defaultStyle.alignment = 'left';
    //                     doc.styles.tableHeader.alignment = 'left';
    //                 }

    //             }
    //             // ,
    //             // {
    //             //     text: 'Download Selected', 
    //             //     action: function (e, dt, node, config) {
    //             //         var selectedIds = [];
    //             //         $('.row-checkbox:checked').each(function() {
    //             //             selectedIds.push($(this).data('id'));
    //             //         });

    //             //         if (selectedIds.length > 0) {
    //             //             downloadSelectedRecords(selectedIds);
    //             //         } else {
    //             //             alert('Please select at least one record.');
    //             //         }
    //             //     }
    //             // }
    //         ]
    //     });
    // }

    if (config.showExcelExport === true || config.showCustomExportOption === true) {
        // console.log('ss');
        var customExportUrl = window.customExportUrl;
        var token = window.csrfToken;
        var selectedRows = {};
        new $.fn.dataTable.Buttons(oTable, {
            buttons: [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
                    action: function () {
                        var selectedIds = Object.keys(selectedRows).filter(id => selectedRows[id]);

                        if (selectedIds.length > 0) {
                            var form = $('<form>', {
                                method: 'POST',
                                action: customExportUrl
                            });

                            form.append($('<input>', { type: 'hidden', name: '_token', value: token }));

                            selectedIds.forEach(function (id) {
                                form.append($('<input>', { type: 'hidden', name: 'selectedIds[]', value: id }));
                            });

                            $('body').append(form);
                            form.submit();
                            form.remove();
                        } else {
                            oTable.button('.buttons-excel-default').trigger();
                        }
                    }
                },
                {
                    extend: 'excel',
                    text: 'Default Excel',
                    className: 'buttons-excel-default d-none',
                    exportOptions: {
                        columns: ':visible:not(.actions):not(.noExport)',
                        modifier: { page: 'current' },
                        format: {
                            body: function (data, row, column, node) {

                                let div = document.createElement("div");
                                div.innerHTML = data;
                                if (div.querySelector('.full-text')) {

                                    return div.querySelector('.full-text').textContent.trim();
                                }

                                return div.innerText.trim();
                            }
                        }
                    },
                    customize: function (xlsx) {
                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        $('row:first c', sheet).attr('s', '2');
                    }
                },
                {
                    extend: 'pdfHtml5',//Pratibha
                    orientation: 'landscape',
                    pageSize: 'LEGAL',//Pratibha
                    text: '<i class="fa fa-file-pdf-o" aria-hidden="true"></i> Pdf',
                    exportOptions: {
                        columns: ':visible:not(.actions)',
                        format: {
                            body: function (data, row, column, node) {

                                let div = document.createElement("div");
                                div.innerHTML = data;

                                if (div.querySelector('.full-text')) {
                                    return div.querySelector('.full-text').textContent.trim();
                                }

                                return div.innerText.trim();
                            }
                        }
                    },
                    customize: function (doc) {
                        doc.styles.tableHeader.fontSize = 10; // Adjust font size for table header
                        doc.styles.tableBodyEven.fontSize = 10; // Adjust font size for even rows
                        doc.styles.tableBodyOdd.fontSize = 10; // Adjust font size for odd rows
                        doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 2).join('*').split('');
                        doc.defaultStyle.alignment = 'left';
                        doc.styles.tableHeader.alignment = 'left';
                    }

                }
            ]
        });

        oTable.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
    }




    oTable.buttons().container().appendTo('#dataTable_wrapper .col-md-6:eq(0)');
    return oTable;
}

// Custom function to handle the download of selected records
function downloadSelectedRecords(selectedIds) {

    $.ajax({
        url: '/download-selected-records', // Replace with the appropriate download endpoint
        method: 'POST',
        data: {
            ids: selectedIds,
            _token: '{{ csrf_token() }}' // Include CSRF token if needed
        },
        success: function (response) {
            // Handle the successful download (e.g., trigger download, show success message)
            window.location.href = response.downloadUrl; // Example: redirect to a download URL
        },
        error: function (error) {
            console.error('Download failed:', error);
        }
    });
}
function serialNumber(id, row) {
    var pageInfo = $(id).DataTable().page.info();
    return pageInfo.start + 1 + row;
}

function createActionButtons(actions) {
    var actionLinks = '';
    if (actions !== undefined && actions.length !== 0) {
        actionLinks += '<div class="action">';
        actionLinks += actions.join('');
        actionLinks += '</div>';
    }
    return actionLinks;
}

function createListButtons(actions) {
    var actionLinks = '';
    if (actions !== undefined && actions.length !== 0) {
        actionLinks += '<li>';
        actionLinks += actions.join('');
        actionLinks += '</li>';
    }
    return actionLinks;
}


function ticketBedge(status) {

    var htmlTag = '';
    if (status == 'Open') {
        htmlTag = `<span class="badge bg-success">${status}</span>`;
    } else if (status == 'Reopen') {
        htmlTag = `<span class="badge bg-primary">${status}</span>`;
    } else if (status == 'Pending' || status == 'Draft' || status == 'InProgress' || status == 'Partially Approved') {
        htmlTag = `<span class="badge bg-warning">${status}</span>`;
    } else if (status == 'Reverted') {
        htmlTag = `<span class="badge bg-info">${status}</span>`;
    } else if (status == 'Closed') {
        htmlTag = `<span class="badge bg-danger">${status}</span>`;
    } else {
        htmlTag = `<span class="badge bg-danger">${status}</span>`;
    }
    return htmlTag;
}

function statusBedge(status) {

    var htmlTag = '';
    if (status == 'Completed' || status == 'Approved' || status == 'Payment_completed') {
        htmlTag = `<span class="badge bg-success">${status}</span>`;
    } else if (status == 'Submitted') {
        htmlTag = `<span class="badge bg-primary">${status}</span>`;
    } else if (status == 'Pending' || status == 'Draft' || status == 'In Progress' || status == 'Partially Approved' || status == 'Accepted(Under Approval)') {
        htmlTag = `<span class="badge bg-warning">${status}</span>`;
    } else if (status == 'Reverted') {
        htmlTag = `<span class="badge bg-info">${status}</span>`;
    } else if (status == 'Reject') {
        htmlTag = `<span class="badge bg-danger">${status}</span>`;
    }
    else if (status == 'Success') {
        htmlTag = `<span class="badge bg-success">${status}</span>`;
    } else {
        htmlTag = `<span class="badge bg-danger">${status}</span>`;
    }
    return htmlTag;
}

function buttonView(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}" rel="tooltip" style="background-color:#4662D2;" class="btn  btn-circle m-1 view-action-btn" title="View"
      ><img src="${BASE_URL}/assets/img-new/view.svg">
    </a>`;
}

function buttonInprogress(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}" rel="tooltip" title="inprogress" class="btn  bg-primary btn-sm btn-circle m-1 view-action-btn" 
      > <img src="${BASE_URL}/assets/img-new/inprogress.svg">
    </a>`;
}

function buttonEdit(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}/edit" 
      class="edit btn btn-sm  btn-circle m-1 view-action-btn bg-primary" rel="tooltip" title="Edit"
      ><img src="${BASE_URL}/assets/img-new/edit.svg">
    </a>`;
}

function buttonRolePermission(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}/edit" rel="tooltip" title="Update Permission"
      ><img src="${BASE_URL}/assets/img-new/add-evalu.svg">
    </a>`;
}

function buttonUserPermission(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}/edit" rel="tooltip" title="Update Permission"
      ><img src="${BASE_URL}/assets/img-new/add-evalu.svg"> 
    </a>`;
}

function buttonDownload(url) {
    return `<a 
      href="${url}" 
      class="btn btn-sm btn-success btn-circle"
      download
      ><i class="fas fa-download"></i>
    </a>`;
}

function buttonDelete(endPoint, id) {
    return `<a 
      href="javascript:void(0)" 
      data-url="${endPoint}/${id}" 
      class="btn btn-sm btn-danger btn-circle btn-delete m-1" 
      data-bs-toggle="modal" 
      data-bs-target="#deleteModal" rel="tooltip" title="Delete">
        <i class="fa fa-trash"></i>
    </a>`;
}

function buttonPermission(props) {
    return `<a 
      href="javascript:void(0)" 
      data-url="${props.url}" 
      class="btn btn-sm btn-primary btn-circle ${props.className}" 
      data-id="${props.id}"
      data-toggle="modal" 
      data-target="${props.target}">
        <i class="fas fa-user-plus"></i>
    </a>`;
}

function buttonRemark(endpoint, assignment_id, app_id, to_user_id, work_flow_type) {
    return `<button 
      class="btn btn-theme btn-primary btn-change-request m-1" rel="tooltip" title="Status" 
      data-toggle="modal"   
      data-url="${endpoint}" data-assignment-id ="${assignment_id}" data-app-id="${app_id}" data-to-user-id="${to_user_id}" data-changed="1"  data-work-flow-type ="${work_flow_type}"><img src="${BASE_URL}/assets/ffo-admin/img/view-application-ico.svg">
    </button>`;
}

function buttonApproveReject(endpoint, assignment_id, app_id, to_user_id, work_flow_type) {
    return `<button 
      class="btn btn-success btn-change-request m-1" rel="tooltip" title="Status" 
      data-toggle="modal"   
      data-url="${endpoint}" data-assignment-id ="${assignment_id}" data-app-id="${app_id}" data-to-user-id="${to_user_id}" data-changed="1"  data-work-flow-type ="${work_flow_type}">Approve/Reject
    </button>`;
}

function buttonRevert(endpoint, app_id, workflow_type) {
    return `<a class="pending btn btn-primary"  href="${endpoint}/${app_id}/${workflow_type}" >Revert to Applicant</a>`;
}

function buttonForward(endpoint, assignment_id, app_id, to_user_id, work_flow_type) {
    return `<button 
      class="btn forward-btn btn-change-request m-1" rel="tooltip" title="Forward" 
      data-toggle="modal"   
      data-url="${endpoint}" data-assignment-id ="${assignment_id}" data-app-id="${app_id}" data-to-user-id="${to_user_id}" data-changed="2" data-work-flow-type ="${work_flow_type}">Forward
    </button>`;
}

function viewTHistory(id) {
    return `<a 
      href="#" data-id="${id}" rel="tooltip" title="History" class="btn  bg-warning btn-sm btn-circle m-1 view-action-btn timeline-history" 
      > <img src="${BASE_URL}/assets/img-new/history.svg">
    </a>`;
}

function viewWTHistory(id) {
    return `<a 
      href="#" data-id="${id}" rel="tooltip" title="History" class="btn  bg-warning btn-sm btn-circle m-1 view-action-btn timeline-request" 
      > <img src="${BASE_URL}/assets/img-new/history.svg">
    </a>`;
}



function buttonActivate(url) {
    return `<button type="button" data-url="${url}" class="btn btn-success btn-sm btn-activation">Activate</a>`;
}

function buttonDeactivate(url) {
    return `<button type="button" data-url="${url}" class="btn btn-danger btn-sm btn-activation">Deactivate</a>`;
}

function buttonNtmEdit(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}/editCloseData" 
      class="" rel="tooltip" title="Edit">EditCloseData</a>`;
}

function buttonCMCEMI(endPoint, id) {
    return `<a 
      href="${endPoint}/${id}/edit" 
      class="btn btn-sm btn-warning m-1" rel="tooltip" title="Next EMI"
      ><i class="fas fa-edit"></i> Next EMI
    </a>`;
}



function buttonResolve(endPoint, id) {
    return `<button 
      class="btn btn-sm btn-success btn-grievance-request m-1"   rel="tooltip" title="Resolve" data-toggle="modal"
      data-url="${endPoint}" data-id ="${id}" data-changed="1"
      >Resolve
    </button>`;
}

function buttonDiscard(endPoint, id) {
    return `<button 
      class="btn btn-sm btn-danger btn-grievance-request m-1"   rel="tooltip" title="Discard" data-toggle="modal"
      data-url="${endPoint}" data-id ="${id}" data-changed="2"
      >Discrad
    </button>`;
}



$('#reset_filter').on('click', function () {
    $('.filter').val('');
    oTable.ajax.reload();
});

$(document).on("click", ".btn-delete", function () {
    var url = $(this).data("url");
    $("#modal-delete-url").val(url);
});


$(document).on("click", ".btn-change-request", function () {
    $('.current_remarks').show();
    $('.upload_doc_se').show();
    $('#action-button').show();
    $('#confirmationModallLabel').text('');
    $("#current_remarks").val('');
    $("#current_remarks_err").html('');
    $(".downloads").html('');
    $("#upload_doc_se").val('');
    $("#uploaded_documents").val("[]");
    var url = $(this).data("url");
    var assignment_id = $(this).data("assignment-id");
    var app_id = $(this).data("app-id");
    var to_user_id = $(this).data("to-user-id");
    var work_flow_type = $(this).data("work-flow-type");
    var change_status = $(this).data("changed");

    if (change_status == 1) {
        $("#forward").hide();
        $("#approve").show();
        $("#reject").show();
        $("#draft").show();
        $('#confirmationModallLabel').text('Approve/Reject');
    }
    if (change_status == 2) {
        $("#forward").show();
        $("#approve").hide();
        $("#reject").hide();
        $("#draft").hide();
        $('#confirmationModallLabel').text('Forward To SE');
    }

    $("#modal-confirm-url").val(url);
    $("#modal-confirm-assignment-id").val(assignment_id);
    $("#modal-confirm-app-id").val(app_id);
    $("#modal-workflow-type").val(work_flow_type);
    $("#modal-confirm-to-user-id").val(to_user_id);
    //To Get History
    $("#remarks_error").html('');
    $('#current_remarks').attr('disabled', false);
    $('#upload_doc_se').attr('disabled', false);
    $('#action-button').show();
    $('#form-div').show();
    //$('#status-display').hide();
    //$('#status-show').text('');
    $('#history').text('');
    $('#application_no').text('');
    $('#app_date').text('');
    $('#fileList').text('');
    var html = '';
    $.ajax({
        url: BASE_URL + '/assigned-script-evaluator/getHistory',
        method: "POST",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {

            national_permission_application_id: app_id,
            to_user_id: to_user_id,
        },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (response) {
            $('#ajax-loader').hide();
            //console.log(response);
            if (response.application_details) {
                $("#application_no").append(response.application_details.application_number);
                $("#app_date").append(response.application_details.application_date);
            }
            if (response.history.length > 0) {
                html += '<table class="table table-bordered table-striped">';
                html += '<tr><th colspan="3" align="center" style="text-align:center">History</th></tr>';
                html += '<tr><th style="text-align:center; width:120px;">Date</th><th >Remark</th><th >Documents</th></tr>';
                $.each(response.history, function (index, val) {
                    html += '<tr>';
                    html += '<td>' + val.created_at + '</td>';
                    html += '<td style="text-align:left">' + val.remarks.replace(/\n/g, '<br>') + '</td>';
                    html += '<td>';
                    if (val.documents) {
                        var documents = JSON.parse(val.documents);
                        $.each(documents, function (index1, val1) {
                            html += '<p class="mb-1"><a class="d-flex gap-1 font-12" href="' + BASE_URL + '/storage/app/uploads/query-documents/' + val1.document_name + '" download="' + val1.document_original_name + '" >' + '<img src="' + BASE_URL + '/assets/img-new/download-ico-p.svg" class="downloads">' + ' ' + val1.document_original_name + '</a></p>';

                        });

                    }
                    html += '</td>';
                    html += '</tr>';
                    //html +='<span class="time-right">'+val.created_at+'</span>';
                    if (index === response.history.length - 1) {
                        //html += '<p>'+val.status+'</p>';
                        $("#modal-confirm-status").val(val.status);
                        if (val.status === 1 || val.status === 3) {
                            //$('#current_remarks').attr('disabled',true);
                            //$('#upload_doc_se').attr('disabled',true);
                            $('.current_remarks').hide();
                            $('.upload_doc_se').hide();
                            $('#action-button').hide();
                            //$('#form-div').hide();
                            //$('#status-display').show();
                            //$('#status-show').text(val.status_value);
                            $('#confirmationModallLabel').text('History');
                        }

                    }


                });
                html += '</table>';
                $('#history').append(html);
            }
        }
    });

    $("#confirmationModal").modal('show');

});


$(document).on("click", "#confirm-delete", function () {
    var url = $("#modal-delete-url").val();

    $.ajax({
        url: url,
        method: "GET",

        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (response) {
            $('#ajax-loader').hide();
            if (response.status) {
                success(response);
                $("#deleteModal").modal("hide");
                oTable.ajax.reload();
            } else {
                //failure(response);
                $("#deleteModal").modal("hide");
                $("#errorModal .error-msg").html(response.message);
                $("#errorModal").modal("show");

            }

        },
        error: function (response) {
            $('#ajax-loader').hide();
            var payload = response.responseJSON || {};

            if (payload.exception && payload.exception === 'Illuminate\\Database\\QueryException') {
                payload.message = 'Could not delete because this record has mapped to another record(s)';
                failure(payload);
                $("#deleteModal").modal("hide");
                return;
            }

            // response is the jqXHR object here, not the JSON body -- the backend's
            // actual validation message (e.g. from Respond::error()) lives at
            // response.responseJSON.message, not response.message. This callback only
            // ever runs for a failed request, so there's no separate "status" flag on
            // the jqXHR itself to gate on; always surface whatever message is available.
            var deleteErrorMessage = payload.message || 'Something went wrong. Please try again.';
            $("#deleteModal").modal("hide");

            if (url.indexOf('fund-allocation-delete') !== -1) {
                alert(deleteErrorMessage);
                return;
            }

            failure({ message: deleteErrorMessage });
        }
    });
});



$(document).on("click", ".confirm-request", function () {
    var url = $("#modal-confirm-url").val();
    var assignment_id = $("#modal-confirm-assignment-id").val();
    var app_id = $("#modal-confirm-app-id").val();
    var to_user_id = $("#modal-confirm-to-user-id").val();
    var change_status = $(this).data("action");
    var workflow_type = $("#modal-workflow-type").val();
    var remarks = $("#current_remarks").val() ? $("#current_remarks").val() : '';

    var files = JSON.parse($("#uploaded_documents").val());
    if (remarks == '') {
        $("#remarks_error").html('Please give some remarks');
        return false;
    }
    if (remarks.length > 300) {
        $("#remarks_error").html('Please give remarks within 300 characters');
        return false;
    }
    $('button[type="button"]').attr("disabled", "disabled");

    $.ajax({
        url: url,
        method: "POST",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        data: {
            assignment_id: assignment_id,
            national_permission_application_id: app_id,
            status: change_status,
            to_user_id: to_user_id,
            remarks: remarks,
            workflow_type: workflow_type,
            documents: files ? files : '',
        },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status) {
                $("#confirmationModal").modal("hide");
                $('button[type="button"]').removeAttr("disabled");
                oTable.ajax.reload();
                //location.reload();   
            }
            else if (!responseData.status && responseData.errors) {
                applyValidationErrors(responseData);
                $('button[type="button"]').removeAttr("disabled");
                return;
            }
            else {
                failure(responseData);
                $('button[type="button"]').removeAttr("disabled");
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            console.log(responseData.responseJSON);
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }

    });
});


function sendNationalApplicationRequest(requestData, redirectTo, isLogout = false, payment_url = null) {

    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status) {
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }

                success(responseData);
                if (form.submitter == 'payment') {
                    $("#paymentNotification").modal('show');
                    $("#redirectURL").val(redirectTo);
                    applyPayment(payment_url, responseData.data.national_permission_application_id, redirectTo);
                    return false;
                }
                if (form.submitter == 'draft_documentry') {
                    redirect(redirectTo + '/' + responseData.data.documentary_application_id);
                    return false;
                }
                if (form.submitter == 'final_submit') {
                    redirect(redirectTo);
                    return false;
                }

                redirect(redirectTo + '/' + responseData.data.national_permission_application_id);
            }
            else if (!responseData.status && responseData.errors.length == 0) {
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                applyValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }
    })
}


function applyPayment(url, applicationID, redirectTo) {
    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        data: { 'national_permission_application_id': applicationID },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status == true) {
                //redirect(redirectTo);
            } else {
                failure(responseData);
                enableSubmit();
                return;
            }
        }
    });
}


function sendRailwayApplicationRequest(requestData, redirectTo, isLogout = false, payment_url = null) {

    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status) {
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }

                success(responseData);
                if (form.submitter == 'final_submit') {
                    redirect(redirectTo);
                    return false;
                }
                redirect(redirectTo + '/' + responseData.data.railway_permission_application_id);
            }
            else if (!responseData.status && responseData.errors.length == 0) {
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                railwayValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }
    })
}

function sendASIApplicationRequest(requestData, redirectTo, isLogout = false, payment_url = null) {

    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status) {

                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }

                success(responseData);
                if (form.submitter == 'final_submit') {
                    redirect(redirectTo);
                    return false;
                }

                redirect(redirectTo + '/' + responseData.data.asi_permission_application_id);
            }
            else if (!responseData.status && responseData.errors.length == 0) {
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                railwayValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }
    })
}

function sendInterimApplicationRequest(requestData, redirectTo, isLogout = false) {

    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status == true) {
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }
                if (responseData.data.status == false) {
                    let errorMessage = 'The Application is not eligible for incentive.';
                    if (responseData.data.message.length !== 0) {
                        errorMessage = responseData.data.message;
                    }
                    toastr.error(errorMessage);
                    enableSubmit();
                    return false;
                }
                responseData.message = "Saved successfully.";
                success(responseData);
                //console.log(responseData);
                if (form.submitter == 'eligibility_btn') {
                    redirect(redirectTo + '/' + responseData.data.interim_eligibility_id);
                    return false;
                }
                if (form.submitter == 'final_eligibility_btn') {
                    redirect(redirectTo + '/' + responseData.data.incentive_interim_application_id);
                    return false;
                }
                if (form.submitter == 'final_submit') {
                    redirect(redirectTo);
                    return false;
                }
                redirect(redirectTo + '/' + responseData.data.incentive_interim_application_id);
            }
            else if (!responseData.status && responseData.errors.length == 0) {
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                railwayValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }
    })
}

function sendFinalApplicationRequest(requestData, redirectTo, isLogout = false) {

    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status == true) {
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }
                if (responseData.data.status == false) {
                    toastr.error('The Application is not eligible for incentive.');
                    enableSubmit();
                    return false;
                }

                //  success(responseData); 
                if (form.submitter == 'final_submit') {
                    redirect(redirectTo);
                    return false;
                }
                redirect(redirectTo + '/' + responseData.data.incentive_final_application_id);
            }
            else if (!responseData.status && responseData.errors.length == 0) {
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {
                railwayValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (responseData) {
            $('#ajax-loader').hide();
            toastr.error('500 Internal Server Error');
            enableSubmit();
            return false;
        }
    })
}

function railwayValidationErrors(responseData) {
    for (var error in responseData.errors) {
        var errorMessage = responseData.errors[error][0];
        var numberOfArray = (error.match(/\./g) || []).length;

        if (numberOfArray == 2) {
            var parts = error.split('.');
            $(`#${parts[2]}_${parts[1]}_error`).text(errorMessage);
            errorScroll(`#${parts[2]}_${parts[1]}_error:visible`);
        } else if (numberOfArray == 3) {
            var child = error.split('.');
            $(`#${child[3]}_${child[1]}_error`).text(errorMessage);
            errorScroll(`#${child[3]}_${child[1]}_error:visible`);
        } else if (numberOfArray == 4) {
            var childparts = error.split('.');
            childErrorId = `${childparts[3]}${childparts[4]}`;
            childErrorMessages = responseData.errors[error][0];
            //console.log(`#${childErrorId}_error`);         
            $(`#${childErrorId}_error`).text(childErrorMessages);
            errorScroll(`#${childErrorId}_error:visible`);
        } else {
            $(`#${error}_error`).text(errorMessage);
            errorScroll(`#${error}_error:visible`);

        }
    }
}

function errorScroll(errorClass) {
    if (!$('html, body').is(':animated')) {
        var errorDiv = $(errorClass).first();
        var scrollPos = errorDiv.offset().top - 150;
        $('html, body').animate({
            scrollTop: scrollPos
        }, 800);
    }
}
function sendRequest(requestData, redirectTo = '', isLogout = false) {
    disableSubmit();
    $.ajax({
        url: requestData.url,
        method: requestData.method,
        dataType: 'json',
        data: requestData.body,
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        //data: JSON.stringify(requestData.body),
        //headers: requestData.headers ? requestData.headers : { 'Content-Type': 'application/json' },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (responseData) {
            $('#ajax-loader').hide();
            //console.log(responseData);
            // return false;

            if (responseData.status) {
                success(responseData);
                if (isLogout) {
                    $(document).find("#logout-form").submit();
                    return;
                }
                if (redirectTo != '') {
                    redirect(redirectTo);
                }


            }
            else if (!responseData.status && responseData.errors.length == 0) {
                $('.form-error').text('');
                failure(responseData);
                enableSubmit();
                return;
            }

            else if (!responseData.status && responseData.errors) {

                $(`.form-error`).text('');
                applyValidationErrors(responseData);
                enableSubmit();
                return;
            }
            else {
                failure(responseData);
                enableSubmit();
            }
        },
        error: function (err) {
            $('#ajax-loader').hide();
            if (!err.responseJSON.status && err.responseJSON.errors.length == 0) {
                $('.form-error').text('');
                failure(err.responseJSON);
                enableSubmit();
                return;
            }

            else if (!err.responseJSON.status && err.responseJSON.errors) {
                console.log(err.responseJSON);
                $(`.form-error`).text('');
                applyValidationErrors(err.responseJSON);
                enableSubmit();
                return;
            }
            else {
                failure(err.responseJSON);
                enableSubmit();
            }
        }
    })
}

function applyValidationErrors(responseData) {
    for (var error in responseData.errors) {
        var errorMessage = responseData.errors[error][0];
        //console.log(error, errorMessage);
        var numberOfPeriods = (error.match(/\./g) || []).length;



        if (numberOfPeriods) {
            var parts = error.split('.');
            // $(`#${parts[2]}_${parts[1]}_error`).text(errorMessage);
            if (parts[2] !== undefined && parts[2] !== null && parts[2] !== "") {
                $(`#${parts[2]}_${parts[1]}_error`).text(errorMessage);
            } else {
                $(`#${parts[0]}_${parts[1]}_error`).text(errorMessage);
            }
            //console.log(`#${parts[1]}_error`);
        }

        $(`#${error}_error`).text(errorMessage);
    }
}

function success(responseData) {
    toastr.success(responseData.message);
    setTimeout(function () { }, 2000);
    /*  $("#alert-success-message").text(responseData.message);
     $(".alert-success").show();
     setTimeout(function () {
         $(".alert-success").hide();
     }, 1000); */
}

function failure(responseData) {
    toastr.error(responseData.message);
    setTimeout(function () { }, 1000);
    /*  $("#alert-error-message").text(responseData.message);
     $(".alert-danger").show(); */
}

function disableSubmit() {
    $('button[type="submit"]').attr("disabled", "disabled");
}

function enableSubmit() {
    $('button[type="submit"]').removeAttr("disabled");
}

function redirect(redirectTo) {
    setTimeout(function () {
        location.href = redirectTo;
    }, 1000);
}



function clearFormError() {
    $(this).parent('div').find('.form-error').text('');
    $(this).closest('td').find('.form-error').text('');
}

$(document).ready(function () {
    $(".form-control, .form-select, .form-check-input, .delete_personality_row").focus(clearFormError);
});

$(document).on("click", ".btn-activation", function () {
    var url = $(this).data("url");
    var csrfToken = $("#csrf-token").val();
    $.ajax({
        url: url,
        method: "POST",
        data: {
            _token: csrfToken
        },
        success: function (response) {
            success(response);
            oTable.ajax.reload();
        }
    });
});

function viewHistory(url, form_type_id, form_id) {
    var method = 'GET';
    var url = url + '/' + form_type_id + '/' + form_id;
    $.ajax({
        url: url,
        method: method,
        data: {},
        beforeSend: function () {
            $('#ajax-loader').show();
        },
        success: function (responseData) {
            $('#ajax-loader').hide();
            if (responseData.status) {
                var logs = responseData.data;
                var html = '';
                let i = 0;
                if (logs.length) {
                    logs.forEach(function (log) {
                        ++i
                        html += '<tr>';
                        html += '<td>' + i + '</td>';
                        html += '<td>' + log.assigned_by_name + '<br><Strong>Role:</Strong>' + log.assigned_by_role + '</td>';
                        html += '<td>' + log.assigned_to_name + '<br><Strong>Role:</Strong>' + log.assigned_to_role + '</td>';
                        html += '<td>' + log.status + '</td>';
                        html += '<td>' + log.remarks + '</td>';
                        html += '<td>' + log.created_at + '</td>';
                        html += '</tr>';
                    });

                    $('#logs_details').html(html);
                    $('#status_log_model').modal('show');
                } else {
                    html = '<h6>No data Available</h6>'
                    $('#logs_details').html(html);
                    $('#status_log_model').modal('show');
                }

            }
        },

    });

}

function shorten(str, n) {
    return (str.match(RegExp(".{" + n + "}\\S*")) || [str])[0];
}


function btnDynamic(id) {
    const contentElement = $(`.content_${id}`);
    const textElement = $(`#btnText_${id}`);
    const isTruncated = textElement.text() === 'Read More';

    if (isTruncated) {
        contentElement.html(`<b class="remark-text">Remark:</b> ${contentElement.data('full')} <a href="#" onclick="btnDynamic(${id});" id="btnText_${id}" class="btn btn-theme btn-primary btnText">Read Less</a>`);
    } else {
        contentElement.html(`<b class="remark-text">Remark:</b> ${contentElement.data('truncated')} <a href="#" onclick="btnDynamic(${id});" id="btnText_${id}" class="btn btn-theme btn-primary btnText">Read More</a>`);
    }
}

$('.filter_btn').change(function () {
    $('#reset_btn').show();
    oTable.draw();

});

$('#search_btn').click(function () {
    oTable.draw();
});

$('#reset_btn').click(function () {
    $("#search_form")[0].reset();
    // Reset all Select2 fields
    // Properly clear Select2 selections (multi-select safe)
    $('.select2').each(function () {
        $(this).val(null).trigger('change'); // clear value and update UI
    });
    $('#reset_btn').hide();

    // Optional: remove manually added Select2 choices if UI still shows them (edge case)
    // $('.select2').next('.select2-container').find('.select2-selection__rendered').empty();

    oTable.draw();
});


$('#cancel-btn').click(function () {
    $("#formId")[0].reset();
    $('.select2').each(function () {
        $(this).val(null).trigger('change'); // clear value and update UI
    });
    $('#amount_allocated').val('').removeAttr('value');
    $('#tds').val('').removeAttr('value');

    $('#tds_info').html('').hide();

    $('#cancel-btn').hide();
    oTable.draw();
});

lastTimeMouseMoved = new Date().getTime();
var t = setTimeout(function () {
    var currentTime = new Date().getTime();
    if (currentTime - lastTimeMouseMoved > 300000) {
        $(".navbar-nav.sidebar.accordion").addClass("toggled");
    }
}, 300000)

$(document).mouseover(function () {
    $(".navbar-nav.sidebar.accordion").removeClass("toggled");

});

function createActivationLabel(isActive) {
    return isActive
        ? '<div class="status"><span class="badge bg-success">Active</span></div>'
        : '<div class="status"><span class="badge bg-danger">In-Active</span></div>';
}

function createYesNoLabel(isYes) {
    return isYes
        ? '<label class="badge badge-success">Yes</label>'
        : '<label class="badge badge-danger">No</label>';
}


function createStatusLabel($status) {
    var label = null;
    switch ($status) {
        case 1:
        case '1':
            label = '<label class="badge badge-primary">Draft</label>';
            break;
        case 2:
        case '2':
            label = '<label class="badge badge-warning">Pending</label>';
            break;
        case 3:
        case '3':
            label = '<label class="badge badge-success">Approved</label>';
            break;
        case 4:
        case '4':
            label = '<label class="badge badge-danger">Rejected</label>';
            break;
    }
    return label;
}
