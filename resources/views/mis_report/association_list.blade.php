@extends('components.admin.layout')
@section('page-content')

    <div class="container-fluid mt-2">
        <div class="card container-main-card mb-4">
            <div class="card-header d-flex justify-content-between">
                <div class="heading">
                    <h1>Association Registration Report</h1>
                </div>
               
            </div>

            <div class="card-body d-flex justify-content-between">
                <form id="search_form" autocomplete="off">
                    <div class="filter-bar d-flex py-2 gap-3 align-items-center">
                        <div class="row">
						<div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">From date</label>
                            <input type="text" class="form-control" name="from_date" id="from_dates"
                                placeholder="From Date">
                        </div>
                        </div>
                        <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label">To date</label>
                            <input type="text" class="form-control" name="to_date " id="to_dates"
                                placeholder="To Date">
                        </div>
                        </div>
                        <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label" style="display: block;">Status</label>
                            {!! Form::select('review_status', $status, '', ['class' => 'form-select select2 filter_btn', 'id' => 'review_status']) !!}
                        </div>
                        </div>
                        <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
                            <label class="form-label" style="display: block;">Entity Type</label>
                            {!! Form::select('entity_type',$entity_type,'',['class' => 'form-select select2 filter_btn','id'=> 'entity_type' ]) !!}
                        </div>
                        </div>
                        <div class="col-lg-3 col-2 mb-3">
                        <div class="select-box">
							<label class="form-label">State</label>
							{!! Form::select('state_id[]', ['' => 'Select'] + remove_select_dynamic_common_list($lists?->state_id), '', ['class' => 'form-select filter_btn', 'id' => 'state_id']) !!}
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
    
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>S.N.</th>
                                <th>Organization Name</th>
                                <th>Entity Type</th>
                                <th>Email of Entity</th>
                                <th>Registration No</th>
                                <th>Contact No</th>
                                <th>{{ __('message.status') }}</th>
                                <th class="actions">{{ __('message.action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script>
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: false,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "desc"
            },
            url: "{{ url('association-registration-report-list') }}",
            columns: [

                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
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
                        return row.entity_type;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.entity_email;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.registration_number;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.contact_number;
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.statusWithLabel;
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        var view = '';
                            view = buttonView("{{ url('/association-registration-view') }}", row.id);
                        var actions = createActionButtons([view]);

                        return actions;
                    }
                }
            ],
            createdRow: true,
            filters: ["from_dates","to_dates","review_status","state_id","entity_type"]
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

        $(".clear-action").on("click", function () {

            $("#from_dates").val('');
            $("#to_dates").val('');
            //$("#state_id").val('');

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });
    </script>
@endsection
@endsection
