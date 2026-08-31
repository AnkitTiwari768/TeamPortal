@extends('components.admin.content-layout')

@section('card-content')

    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control filter_btn" name="from_date" id="from_dates"
                        placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control filter_btn" name="to_date " id="to_dates"
                        placeholder="To Date">
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>
        {{-- @if (hasRole('snp'))
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
        @endif --}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        {{-- <th>Source of Registration</th> --}}
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

@section('js')
    <script>
        window.customExportUrl = "{{ url('custom-export-by-ids') }}";
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 8,
                direction: "desc"
            },
            url: "{{ url('ubp-user-list/datalist') }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                /*{
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.source_of_registration;
                    }
                },*/
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.team_id;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.udyam_no;
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.mobile;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.email;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.state_name;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.enterprise_name;
                    }
                },

                {
                    "orderable": false,
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

                        // var pinprgress = '';

                        // @if (hasRole('snp'))
                        //     pinprgress = buttonInprogress("{{ url('msme-inprogress/') }}", row.id);
                        // @endif
                        //pinprgress = buttonEdit("{{ url('mse-secondstep-registration/') }}", row.id);

                        // var actions = createActionButtons([pview, pinprgress]);
                        var actions = createActionButtons([pview]);
                        return actions;
                    }
                },

            ],
            filters: ["from_dates", "to_dates"]
        });


        $("#from_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#to_dates").datepicker("option", "minDate", selected);
                handleDateChange();
            }
        });

        $("#to_dates").datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            maxDate: 0,
            onSelect: function(selected) {
                $("#from_dates").datepicker("option", "maxDate", selected);
                handleDateChange();
            }
        });

        function handleDateChange() {
            let from = $("#from_dates").val();
            let to = $("#to_dates").val();

            if (from || to) {
                oTable.ajax.reload();
                $("#reset_btn").show();
            } else {
                $("#reset_btn").hide();
            }
        }

        $(".clear-action").on("click", function() {

            $("#from_dates").val('');
            $("#to_dates").val('');
            $("#mapping_option").val('');
            $("#product_category_id").val('');
            $("#state_id").val('');
            $("#gender").val('');
            $("#msme_classification").val('');
            $("#major_activity").val('');
            $("#ondc_transaction_type_id").val('');
            $("#source_of_registration").val('').trigger('change');
            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });

        $(document).on('click', '.toggle-text', function(e) {

            e.preventDefault();

            let td = $(this).closest('td');

            td.find('.short-text').toggleClass('d-none');
            td.find('.full-text').toggleClass('d-none');

            if ($(this).hasClass('expanded')) {
                $(this).text('Read More').removeClass('expanded');
            } else {
                $(this).text('Read Less').addClass('expanded');
            }
        });
    </script>
@endsection
@endsection
