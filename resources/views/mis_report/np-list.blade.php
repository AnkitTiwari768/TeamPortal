@extends('components.admin.content-layout')

@section('card-content')

    <style>
        #roles + .select2-container .select2-selection {
            overflow: visible;
        }
    </style>

  

    <div class="card-body">
        <form id="search_form" class="mb-2" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center w-100">
                <div class="row w-100">
                    <div class="col-lg-3">
                        <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control" name="from_date" id="from_dates"
                        placeholder="From Date">
                </div>
                    </div>
                    <div class="col-lg-3">
                              <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control" name="to_date " id="to_dates"
                        placeholder="To Date">
                </div>
                    </div>
                    <div class="col-lg-3">
                            <div class="select-box">
                <label class="form-label" style="display: block;">Roles</label>
                {!! Form::select('roles[]', $roles, '', ['class' => 'form-select select2 roles filter_btn', 'id' => 'roles','multiple']) !!}
                </div>
                    </div>
                    <div class="col-lg-3">
                         <div class="select-box">
                <label class="form-label" style="display: block;">Status</label>
               <?php /* {!! Form::select('review_status', $status, '', ['class' => 'form-select select2 filter_btn', 'id' => 'review_status']) !!}*/ ?>
               {!! Form::select('review_status', $status, '', ['class' => 'form-select filter_btn', 'id' => 'review_status']) !!}
                </div>
                    </div>
                    <div class="col-lg-3">
                         <a href="javascript:void(0)" class="clear-action d-inline-block py-2" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
                    </div>
                </div>
                
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('Authorized Person Name') }}</th>
                        <th>{{ __('Network Participant ID') }}</th>
                        <th>{{ __('BPP Id') }}</th>
                        <th>{{ __('Organization Id') }}</th>
                        <th>{{ __('Organization Name') }}</th>
                        <th>{{ __('Role(s)') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Contact No') }}</th>
                        <th>{{ __('Registration Date') }}</th>
                        <th>Status</th>
                        <th class="actions">{{ __('message.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@section('js')
    ;
    <script>
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "desc"
            },
            url: "{{ url('np-register/datalist') }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.authorized_person_name;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.np_team_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.bppid_providerid;
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.organization_id;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.organization_name;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.role_names;
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
                        return row.primary_contact_no;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                },

                {
                    orderable: false,
                    render: function(data, type, row) {
                        return getStatusLabel(row.status);
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {

                        
                        var pedit = '';

                        pedit = buttonView("{{ url('np-view-details') }}", row.id);

                        

                        var actions = createActionButtons([pedit]);
                        return actions;
                    }
                }

            ],
            filters: ["from_dates","to_dates","roles","review_status"]
        });



        function getStatusLabel(status) {
            switch (status) {
                case 1:
                    return `<span class="badge bg-primary">Pending</span>`;
                    break;
                case 2:
                    return `<span class="badge bg-success">Approved</span>`;
                    break;
                case 3:
                    return `<span class="badge bg-danger">Rejected</span>`;
                case 4:
                    return `<span class="badge bg-warning">Reverted</span>`;
                    break;
                default:
                    return `<span class="badge bg-secondary">N/A</span>`;
            }
        }

            $('.roles').select2({
            placeholder: "Select",
            allowClear: true
            });

            /*$('#review_status').select2({
            placeholder: "Select",
            allowClear: true
            });*/

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


        $(".clear-action").on("click", function () {

            $("#from_dates").val('');
            $("#to_dates").val('');

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });
    </script>
@endsection
@endsection
