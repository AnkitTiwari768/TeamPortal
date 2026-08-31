@extends('components.admin.content-layout')

@section('card-content')

    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control" name="from_date" id="from_dates" placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control" name="to_date " id="to_dates" placeholder="To Date">
                </div>

                <div class="select-box">
                <label class="form-label" style="display: block;">Status</label>
                {!! Form::select('review_status', $status, '', ['class' => 'form-select select2 filter_btn', 'id' => 'review_status']) !!}
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('Claim ID') }}</th>
                        <th>{{ __('BNP Name') }}</th>
                        <th>{{ __('Total Unique Transactions') }}</th>
                        <th>{{ __('Unique MSE Count') }}</th>
                        <th>{{ __('Total Amount Claimed') }}</th>
                        <th>{{ __('Approved Amount') }}</th>
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
         const STATUS_MAP = @json(
        collect(\App\Web\MisReport\FinalStatus::cases())
            ->mapWithKeys(fn($case) => [
                $case->value => $case->label()
            ]));

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
            url: "{{ url('demand-generation-report/datalist') }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.application_number;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.snp_name;
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.total_valid_records;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.total_unique_mse_count;
                    }
                },
               {
                    "orderable": false,
                    "render": function(data, type, row) {
                        return row.amount;
                    }
                },   
                {
                    "orderable": false,
                    "render": function(data, type, row) {

                        if (row.claim_status === 'Pending' || row.claim_status === 'Rejected') {
                            return '-';   // or return '';
                        }

                        return row.amount ?? '-';
                    }
                },
                {
                    orderable: false,
                    render: function(data, type, row) {
                        return getStatusLabel(row.claim_status);
                    }
                },

                {
                    "orderable": false,
                    "render": function(data, type, row) {                        
                        var pedit = '';
                        pedit = buttonView("{{ url('claim-show/') }}", row.id);                   
                        var actions = createActionButtons([pedit]);
                        return actions;
                    }
                }

            ],
            filters: ["from_dates","to_dates","review_status"]
        });


 function getStatusLabel(status) {
                switch (status) {
                    case 'Draft':
                        return `<span class="badge bg-warning">Draft</span>`;

                    case 'Pending':
                        return `<span class="badge bg-primary">Pending</span>`;

                    case 'Approved':
                        return `<span class="badge bg-success">Approved</span>`;

                    case 'Rejected':
                        return `<span class="badge bg-danger">Rejected</span>`;

                    case 'Payment Completed':
                        return `<span class="badge bg-info">Payment Completed</span>`;

                    default:
                        return `<span class="badge bg-secondary">N/A</span>`;
                }
    }
$('.filter_btn').select2({
  placeholder: "Select",
  allowClear: true
});

$('#claim_status').select2({
  placeholder: "Select",
  allowClear: true
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

            $("#from_dates").datepicker("option", "maxDate", 0);
            $("#to_dates").datepicker("option", "minDate", null);

            oTable.ajax.reload();

            $(".clear-action").hide();
        });
    </script>
@endsection
@endsection
