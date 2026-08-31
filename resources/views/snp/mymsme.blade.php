@extends('components.admin.content-layout')

@section('card-content')

    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control filter_btn" name="from_date" id="from_date"
                        placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control filter_btn" name="to_date " id="to_date"
                        placeholder="To Date">
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>
		{{--@if (hasRole('snp'))
            <div class="card-header d-flex">
                <div class="action-header ms-auto">
                    <div class="btn-group drop-btn">
                        <a href="{{route('msme-bulk-registration-udyam')}}" class="btn btn-danger">
                            <img src="{{ asset('assets/img-new/add.svg') }}">
                            MSE Bulk Registration Form II
                        </a>
                    </div>
                </div>
            </div>
        @endif--}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>{{ __('message.sn') }}</th>
                        <th>Source of Registration</th>
                        <th>TEAMID</th>
                        <th>Udyam</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>State</th>
                        <th>Name of enterprise</th>
                        <th>Transaction Type</th>
                        
                        <th>Date </th>
                        <th class="actions">{{ __('message.action') }}</th> 
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@section('js');
    <script>
    // ========================================
    // DEBOUNCE FUNCTION
    // ========================================
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

    // ========================================
    // SHOW/HIDE AJAX LOADER
    // ========================================
    function showLoader() {
        $('#ajax-loader').show();
        $('#dataTable_wrapper').addClass('blur-background');
    }

    function hideLoader() {
        $('#ajax-loader').hide();
        $('#dataTable_wrapper').removeClass('blur-background');
    }

    // ========================================
    // GET DATATABLE INSTANCE
    // ========================================
    function getTable() {
        return $('#dataTable').DataTable();
    }

    // ========================================
    // RELOAD TABLE
    // ========================================
    function reloadTable() {
        var oTable = getTable();
        oTable.ajax.reload(null, false);
    }

    // ========================================
    // SHOW/HIDE CLEAR BUTTON
    // ========================================
    function showClearButton() {
        var hasFilter = false;
        $('#from_date, #to_date').each(function() {
            if ($(this).val() && $(this).val() !== '') {
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

    // ========================================
    // DOCUMENT READY
    // ========================================
    $(document).ready(function () {
        // Show loader initially
        showLoader();

        // ========================================
        // DATE PICKER INIT
        // ========================================
        $("#from_date").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            onSelect: function(selected) {
                $("#to_date").datepicker("option", "minDate", selected);
                reloadTable();
                showClearButton();
            }
        });

        $("#to_date").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            onSelect: function(selected) {
                $("#from_date").datepicker("option", "maxDate", selected);
                reloadTable();
                showClearButton();
            }
        });

        // ========================================
        // DATATABLE INITIALIZATION
        // ========================================
        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};

        var table = dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('snp-msme/datalist') }}",
            beforeLoad: function() {
                showLoader();
            },
            afterLoad: function() {
                hideLoader();
            },
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
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.source_of_registration;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.team_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.udyam_no;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.mobile;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.email;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.state_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.enterprise_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.transaction_type;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        var pview = '';
                        pview = buttonView("{{ url('msme-details/') }}", row.id);

                        var pinprgress = '';
						
						@if (hasRole('snp') && acl('update-bppid-open')) 
							pinprgress = buttonInprogress("{{ url('msme-inprogress/') }}", row.id);
						@endif

                        var actions = createActionButtons([pview, pinprgress]);
                        return actions;
                    }
                },
            ],
            filters: ["from_date", "to_date"]
        });

        // ========================================
        // SEARCH WITH DEBOUNCE
        // ========================================
        $(document).on('init.dt', '#dataTable', function() {
            var oTable = getTable();
            var searchInput = $('.dataTables_filter input');
            
            // Remove existing event handlers
            searchInput.off('keyup input');
            
            // Apply debounced search
            searchInput.on('keyup input', debounce(function() {
                showLoader();
                oTable.search(this.value).draw();
            }, 500));
        });

        // ========================================
        // DATATABLE EVENTS FOR LOADER
        // ========================================
        $(document).on('preXhr.dt', '#dataTable', function() {
            showLoader();
        });

        $(document).on('draw.dt', '#dataTable', function() {
            hideLoader();
        });

        $(document).on('xhr.dt', '#dataTable', function(e, settings, json, xhr) {
            if (xhr && xhr.status !== 200) {
                hideLoader();
            }
        });

        // ========================================
        // RESET BUTTON
        // ========================================
        $('#reset_btn').on('click', function() {
            $('#from_date').val('');
            $('#to_date').val('');
            // Reset datepicker limits
            $("#from_date").datepicker("option", "maxDate", null);
            $("#to_date").datepicker("option", "minDate", null);
            $(this).hide();
            
            // Clear search input as well
            $('.dataTables_filter input').val('');
            
            var oTable = getTable();
            oTable.search('').draw();
            oTable.ajax.reload(null, false);
        });

        // ========================================
        // SHOW CLEAR BUTTON ON FILTER CHANGE
        // ========================================
        $('#from_date, #to_date').on('change', function() {
            showClearButton();
        });

        // ========================================
        // INITIAL CLEAR BUTTON STATE
        // ========================================
        showClearButton();


        // ========================================
        // CHECKBOX HANDLING
        // ========================================
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

        $('#dataTable').on('draw.dt', function() {
            $(".row-checkbox").each(function() {
                var id = $(this).val();
                $(this).prop("checked", selectedRows[id] ? true : false);
            });
            var allChecked = $(".row-checkbox").length && $(".row-checkbox:checked").length === $(".row-checkbox")
                .length;
            $("#selectAll").prop("checked", allChecked);
        });

    }); // END DOCUMENT READY
    </script>

@endsection
@endsection