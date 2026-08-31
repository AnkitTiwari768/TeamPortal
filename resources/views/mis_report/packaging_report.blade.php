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
                                            <th>Claim No</th>
                                            <th>SNP ID</th>
                                            <th>SNP Name</th>
                                            <th>Udyam No</th>
                                            <th>Team ID of MSE</th>
                                            <th>Name of MSE</th>
                                            <th>Category of MSE</th>
                                            <th>Target Customer</th>
                                            <th>Major Category</th>
                                            <th>Product Category</th>
                                            <th>Submission Date</th>
                                            <th>Claimed Amount (Rs)</th>
                                            <th>Approved Amount (Rs)</th>
                                            <th>Status</th>
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
                url: "{{ url('mis-claim-report/' . $claimSlug) }}",
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
                        orderable: true,
                        render: function (data, type, row) {
                            return row.application_number ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.snp_id ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.snp_name ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.udyam_no ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.team_id ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            //return row.msme_name ?? '';
                            let text = row.msme_name || '';

                            if (text.length <= 30) return text;

                            return `
                                <span class="short-text">${text.slice(0,30)}...</span>
                                <span class="full-text d-none">${text}</span>
                                <a href="javascript:void(0)" class="toggle-text d-block mt-1" style="text-decoration:none;">
                                    Read More
                                </a>
                            `;
                        }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.msme_classification ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.target_customer ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.major_activity ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            //return row.subdomain_names ?? '';
                            let text = row.subdomain_names || '';
                            if (text.length <= 20) return text;

                            return `
                                <span class="short-text">${text.slice(0,30)}...</span>
                                <span class="full-text d-none">${text}</span>
                                <a href="javascript:void(0)" class="toggle-text d-block mt-1" style="text-decoration:none;">
                                    Read More
                                </a>
                            `;
                            }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.submitted_at ?? '';
                        }
                    },


                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.amount ?? '';
                        }
                    },
                    {
                        orderable: true,
                        render: function (data, type, row) {
                            return row.approved_amount ?? '';
                        }
                    },

                    {
                        orderable: true,
                        render: function (data, type, row) {
                            // let status = row.status ?? '';
                            return getStatusLabel(row.claim_status);
                        }
                    },

                    {
                        orderable: false,
                        render: function (data, type, row) {
                            var viewBtn = buttonView("{{ url('claim-show') }}", row.id);
                            return createActionButtons([viewBtn]);
                        }
                    }
                ],
                createdRow: true,
                filters: ["from_dates","to_dates","review_status"]
            });

            function getStatusLabel(status) {
                switch (status) {

                    case 'Pending':
                        return `<span class="badge bg-primary">Pending</span>`;

                    case 'Approved':
                        return `<span class="badge bg-success">Approved</span>`;

                    case 'Rejected':
                        return `<span class="badge bg-danger">Rejected</span>`;

                    case 'Payment Completed':
                        return `<span class="badge bg-info">Payment Completed</span>`;

                    default:
                        return `<span class="badge bg-primary">Pending</span>`;
                }
            }

            function formatStatus(status) {
                const map = {
                    Pending: 'Pending',
                    Approved: 'Approved',
                    Rejected: 'Rejected',
                    'Payment Completed': 'Payment Completed',
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

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('toggle-text')) {

                e.preventDefault();

                let td = e.target.parentElement;

                let shortText = td.querySelector('.short-text');
                let fullText = td.querySelector('.full-text');

                shortText.classList.toggle('d-none');
                fullText.classList.toggle('d-none');

                if (e.target.classList.contains('expanded')) {
                    e.target.textContent = 'Read More';
                    e.target.classList.remove('expanded');
                } else {
                    e.target.textContent = 'Read Less';
                    e.target.classList.add('expanded');
                }
            }
        });


        </script>
    @endsection
@endsection