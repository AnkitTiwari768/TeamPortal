@extends('components.admin.content-layout')

@section('card-content')

    <div class="card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
                <div class="select-box">
                    <label class="form-label">From date</label>
                    <input type="text" class="form-control" name="from_date" id="from_dates"
                        placeholder="From Date">
                </div>
                <div class="select-box">
                    <label class="form-label">To date</label>
                    <input type="text" class="form-control" name="to_date " id="to_dates"
                        placeholder="To Date">
                </div>
                <div class="select-box">
                    <label class="form-label" style="display: block;">Status</label>
                    {!! Form::select('review_status', $status, '', ['class' => 'form-select select2 filter_btn', 'id' => 'review_status']) !!}
                </div>
                @if(hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance'))
                <div class="select-box">
                    <label class="form-label" style="display: block;">Owner</label>
                    {!! Form::select('owner_role',['' => 'Select'] +  $ownerFilterRole, '', ['class' => 'form-select select2 filter_btn', 'id' => 'owner_role']) !!}
                </div>
                @endif
                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;"> <img
                        src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
            </div>
        </form>
    </div>
    <div class="card-body">

        @php
            $dataTableComponent = '
                            <div class="table-responsive">
                                <table class="table" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>' . __('message.sn') . '</th>
                                            <th>Registration Date</th>
                                            <th>Owner Name</th>
                                            <th>Udyam Number</th>
                                            <th>Mobile Number</th>
                                            <th>Type of Transaction</th>
                                            <th>Product Category</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        ';
        @endphp

        <div class="tab-content" id="myTabContent">
            {!! $dataTableComponent !!}
        </div>

    </div>



    @section('js')
        <script>
            var hasFinanceRole = {{ hasRole('nsic-finance') ? 'true' : 'false' }};
            window.csrfToken = "{{ csrf_token() }}";
            var selectedRows = {};
            dataTableInit({
                id: "#dataTable",
                showExcelExport: false,
                showCustomExportOption: true,
                order: {
                    column: 1,
                    direction: "asc"
                },
                url: "{{ url('get-mse-bulk-mis-report-list') }}",
                columns: [

                    {
                        orderable: false,
                        render: function (data, type, full, meta) {
                            return full.is_bulk ?
                                serialNumber("#dataTable", meta
                                    .row) :
                                serialNumber("#dataTable", meta.row);
                        }
                    },

                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.created_at ?? '';
                        }
                    },
                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.name ?? '';
                        }
                    },


                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.udyam_no ?? '';
                        }
                    },

                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.mobile ?? '';
                        }
                    },



                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.transaction_type ?? '';
                        }
                    },
                    {
                        orderable: false,
                        render: function (data, type, row) {
                            return row.product_category_id ?? '';
                        }
                    },

                    {
                        orderable: false,
                        render: function (data, type, row) {
                            // let status = row.status ?? '';
                            return getStatusLabel(row.status);
                        }
                    },

                    /*{
                        orderable: false,
                        render: function (data, type, row) {

                            var viewBtn = buttonView("{{ url('claim-show') }}", row.id);

                            return createActionButtons([viewBtn]);
                        }
                    }*/
                ],
                createdRow: true,
                filters: ["from_dates","to_dates","review_status","owner_role"]
            });



            function getStatusLabel(status) {
                switch (status) {

                    case 'Migrated':
                        return `<span class="badge bg-success">Migrated</span>`;

                    case 'Pending':
                        return `<span class="badge bg-primary">Pending</span>`;

                    case 'Approved':
                        return `<span class="badge bg-success">Approved</span>`;

                    case 'Rejected':

                        return `<span class="badge bg-danger">Rejected</span>`;

                    case 'Failed':
                        return `<span class="badge bg-danger">Failed</span>`;

                    case 'Payment Completed':
                        return `<span class="badge bg-info">Payment Completed</span>`;

                    default:
                        return `<span class="badge bg-secondary">N/A</span>`;
                }
            }

            function formatStatus(status) {
                const map = {
                    Migrated: 'Migrated',
                    Pending: 'Pending',
                    Approved: 'Approved',
                    Rejected: 'Rejected',
                    'Payment Completed': 'Payment Completed',
                    Forwarded: 'Pending',
                    Reverted: 'Rejected'
                };
                return map[status] || 'N/A';
            }


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

            document.addEventListener('click', e => {
                if (e.target.classList.contains('toggle-text')) {
                    e.preventDefault();

                    let td = e.target.parentElement;

                    td.querySelector('.short-text').classList.toggle('d-none');
                    td.querySelector('.full-text').classList.toggle('d-none');

                    e.target.textContent =
                        e.target.textContent === 'Read More' ? 'Read Less' : 'Read More';
                }
            });
        </script>
    @endsection
@endsection