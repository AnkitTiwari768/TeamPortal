@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('workshop.proposed_workshop_list') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a
                                            href="{{ url('dashboard') }}">{{ __('workshop.dashboard') }}</a></li>
                                    <li class="breadcrumb-item"><a
                                            href="#">{{ __('workshop.proposed_workshop_list') }}</a></li>
                                </ol>
                            </nav>
                        </div>
                        @if (acl('workshop-management-create'))
                            <div class="action-header ms-auto">
                                <!-- split button -->
                                <div class="btn-group drop-btn">
                                    <a href="{{ url('create-proposed-workshop') }}">
                                        <button type="button" class="btn btn-primary">
                                            <img src="{{ asset('assets/img-new/add.svg') }}">
                                            {{ __('workshop.add_proposed_workshop') }}
                                        </button>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    
                <div class="card-body d-flex justify-content-between">
                    <form id="search_form" autocomplete="off">
                        <div class="filter-bar d-flex py-2 gap-3 align-items-center">
                            <div class="row">
                                <div class="col-lg-3 col-2 mb-3">
                                    <div class="select-box">
                                        <label class="form-label">{{ __('workshop.from_date') }}</label>
                                        <input type="text" class="form-control" name="from_date" id="from_dates"
                                            placeholder="From Date">
                                    </div>
                                </div>
                            <div class="col-lg-3 col-2 mb-3">
                                <div class="select-box">
                                    <label class="form-label">{{ __('workshop.do_date') }}</label>
                                    <input type="text" class="form-control" name="to_date" id="to_dates"
                                        placeholder="To Date">
                                </div>
                            </div>
                
                            <div class="col-lg-3 col-2 mb-3">
                                <div class="select-box">
                                    <label class="form-label">{{ __('workshop.status') }}</label>
                                     <select id="status" name="status" class="form-select form-select-sm" style="width: 150px; height: 38px;">
                                        <option value="">{{ __('workshop.all') }}</option>
                                        <option value="Completed">{{ __('workshop.completed') }}</option>
                                        <option value="Cancelled">{{ __('workshop.cancelled') }}</option>
                                    </select>
                              
                                </div>
                            </div>
                            <div class="align-items-center col-2 col-lg-2 d-flex justify-content-start mb-3">

                            <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                                    src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
                        </div>

                        </div>
                        </div>
                    </form>
                </div>
                    <div class="card-body pt-1">
                        <!-- tab content -->
                        <div class="tab-content view-application-tab-content" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="national-p" role="tabpanel"
                                aria-labelledby="nav-home-tab">

                                <!-- table start -->
                                <table id="dataTable" class="table datatable table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <!-- <th><input type="checkbox" id="selectAll"></th> -->
                                            <th>{{ __('workshop.s_no') }}</th>
                                            <th>{{ __('workshop.event_title') }}</th>
                                            <th>{{ __('workshop.organiser') }}</th>
                                            <th>{{ __('workshop.event_for') }}</th>
                                            <th>{{ __('workshop.venue_address') }}</th>
                                            <th>{{ __('workshop.from_date') }}</th>
                                            <th>{{ __('workshop.date_to') }}</th>
                                            <th>{{ __('message.status') }}</th>
                                            <th class="actions">{{ __('workshop.action') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                                <!-- table end -->
                            </div>
                        </div>
                        <!-- tab ends -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">{{ __('message.confirm_delete') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="{{ __('message.close') }}"></button>
            </div>

            <div class="modal-body">
                {{ __('message.delete_modal_body') }}
                <input type="hidden" id="modal-delete-url">
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ __('message.close') }}
                </button>

                <button type="button" class="btn btn-danger" id="confirm-delete">
                    {{ __('message.delete') }}
                </button>
            </div>

        </div>
    </div>
</div>
@section('js')
<script>
    var selectedRows = {};
    var tableId = "#dataTable";

    // Show loader immediately on page load
    $('#ajax-loader').show();

    // Bind events before initialization to catch the initial AJAX request
    $(tableId).on('preXhr.dt', function() {
        $('#ajax-loader').show();
    });

    $(tableId).on('xhr.dt draw.dt', function() {
        $('#ajax-loader').hide();
    });

    // DataTable Initialization
    dataTableInit({
        id: "#dataTable",
        showExcelExport: true,
        order: {
            column: 1,
            direction: "desc"
        },
        url: "{{ url('proposed-workshop-list') }}",
        columns: [{
                "orderable": false,
                "render": function(data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.event_title;
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.organizer_name ?? '-';
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    if (!row.event_for) {
                        return '-';
                    }
                    var eventForText = row.event_for.charAt(0).toUpperCase() + row.event_for.slice(1);
                    return '<div style="white-space: normal; word-break: break-word; min-width: 150px;">' +
                        eventForText + '</div>';
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.venue_address;
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.start_date;
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.end_date;
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.status ? statusBedge(row.status) : '-';
                }
            },
            {
                "orderable": false,
                "render": function(data, type, row) {
                    var del = '';
                    @if (acl('workshop-management-delete'))
                        del = `
                            <button type="button"
                                class="btn btn-sm btn-danger delete-record"
                                data-url="{{ url('delete-proposed-workshop') }}/${row.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                        `;
                    @endif

                    var viewBtn = '';
                    @if (acl('workshop-management-view'))
                        viewBtn = buttonView("{{ url('view-proposed-workshop') }}", row.id);
                    @endif

                    var pedit = '';
                    @if (acl('workshop-management-edit'))
                        pedit = buttonEdit("{{ url('edit-proposed-workshop') }}", row.id);
                    @endif

                    var actions = createActionButtons([pedit, viewBtn, del]);
                    return actions;
                }
            }
        ],
        // Filters will be sent automatically
        filters: ["from_dates", "to_dates", "status"]
    });

    // ========================
    // HELPER: Get DataTable instance
    // ========================
    function getTable() {
        return $(tableId).DataTable();
    }

    // ========================
    // RELOAD FUNCTION
    // ========================
    function reloadTable() {
        var oTable = getTable();
        oTable.ajax.reload();
    }

    // ========================
    // DATE PICKER INIT
    // ========================
    $(function() {
        $("#from_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#to_dates").datepicker("option", "minDate", selected);
                reloadTable();
                showClearButton();
            }
        });

        $("#to_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#from_dates").datepicker("option", "maxDate", selected);
                reloadTable();
                showClearButton();
            }
        });

        // ========================
        // STATUS CHANGE - FIXED
        // ========================
        $('#status').on('change', function() {
            reloadTable();
            showClearButton();
        });

        // ========================
        // CLEAR ALL
        // ========================
        $('#reset_btn').on('click', function() {
            $('#from_dates').val('');
            $('#to_dates').val('');
            $('#status').val('');
            // Reset datepicker limits
            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);
            $(this).hide();
            reloadTable();
        });

        // ========================
        // SHOW/HIDE CLEAR BUTTON
        // ========================
        function showClearButton() {
            var hasFilter = false;
            $('#from_dates, #to_dates, #status').each(function() {
                if ($(this).val() !== '') {
                    hasFilter = true;
                    return false;
                }
            });
            if (hasFilter) {
                $('#reset_btn').show();
            } else {
                $('#reset_btn').hide();
            }
        }

        // Initial check
        showClearButton();

        // Also check on any change
        $('.filter-bar').on('change', 'input, select', function() {
            showClearButton();
        });
    });

    // ========================
    // SEARCH DEBOUNCE
    // ========================
    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }

    $(tableId).on('init.dt', function() {
        var oTable = getTable();
        $('.dataTables_filter input')
            .off()
            .on('keyup input', debounce(function() {
                oTable.search(this.value).draw();
            }, 500));
    });

    // ========================
    // DELETE FUNCTIONALITY
    // ========================
    $(document).on('click', '.delete-record', function() {
        $('#modal-delete-url').val($(this).data('url'));
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });

    $(document)
        .off('click', '#confirm-delete')
        .on('click', '#confirm-delete', function() {
            const $btn = $(this);
            if ($btn.prop('disabled')) return;
            $btn.prop('disabled', true);

            let url = $('#modal-delete-url').val();

            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    toastr.success(response.message);
                    $('#dataTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON?.message ?? 'Something went wrong';
                    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
                    toastr.error(message);
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        });

</script>
@endsection



@endsection
