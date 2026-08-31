@extends('components.admin.content-layout')

@section('action-header')
    @php
        $addUrl = url('add-fund-distribution/create');

    @endphp

    @if (acl('fund-distribution-create'))
        <div class="btn-group drop-btn">
            <button type="button" class="btn btn-danger" autocomplete="off" onclick="window.location = '{{ $addUrl }}'">
                <img src="{{ asset('assets/ffo-admin/img/add.svg') }}" />
                {{ __('Add Distribution') }}
            </button>
        </div>
    @endif
@endsection

@section('card-content')


    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-center">
                <div class="col-md-3 mb-2">
                    <label>{{ __('Financial Year') }}</label>
                    {!! Form::select('financial_year', financial_year(), '', [
                        'class' => 'form-select filter_btn',
                        'id' => 'financial_year',
                    ]) !!}
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
                        <!-- <th><input type="checkbox" id="selectAll"></th> -->
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('Financial Year') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Duration Limit') }}</th>
                        <th>{{ __('Distribute Amount Rs.') }}</th>
                        <th>{{ __('Payable Amount Rs.') }}</th>
                        <th>{{ __('TDS %') }}</th>
                        <th>{{ __('Major Component') }}</th>
                        <th>{{ __('Component') }}</th>
                        <th>{{ __('Sub Component') }}</th>
                        <th>{{ __('Sanction Order No') }}</th>
                        <th>{{ __('Sanction Order Date') }}</th>
                        <th>{{ __('Created Date') }}</th>
                        @if (acl('fund-distribution-create'))
                            <th class="actions">{{ __('message.action') }}</th>
                        @endif
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@section('js')
    ;
    <script>
        let monthly = @json(month_list());
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 12,
                direction: "desc"
            },
            url: "{{ url('fund-distributions/datalist') }}",
            columns: [
                /*{
                    "orderable": false,
                    "className": "noExport",
                    "render": function (data, type, row) {
                      var checked = selectedRows[row.id] ? 'checked' : '';
                      return '<input type="checkbox" class="row-checkbox" value="' + row.id + '" ' + checked + '>';
                    }
                  },*/
                {
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.financial_year;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.duration;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        if (row.duration === 'Monthly' && monthly[row.duration_limit]) {
                            return monthly[row.duration_limit];
                        }

                        return row.duration_limit ?? 'NA';
                        //return row.duration_limit ?? 'NA';
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        //return row.amount_allocated;
                        return Number(row.amount_allocated).toLocaleString('en-US');
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        //return row.amount_allocated;
                        return Number(row.payable_amount).toLocaleString('en-US');
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        //return row.amount_allocated;
                        return row.tds;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.majorComponent;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.component;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.subComponent;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sanction_order_no;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.sanction_order_date;
                    }
                },
                {
                    "orderable": true,
                    "render": function(data, type, row) {
                        return row.created_at;
                    }
                },
                @if (acl('fund-allocation-create'))
                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            var pedit = '';
                            @if (acl('fund-allocation-create'))
                                pedit = buttonEdit("{{ url('fund-distributions') }}", row.id);
                                pview = buttonView("{{ url('fund-distributions') }}", row.id);
                            @endif

                            var actions = createActionButtons([pedit, pview]);

                            return actions;
                        }

                    }
                @endif
            ],
            filters: ["financial_year"]
        });

        $(document).ready(function() {
            $('#major_component_id').on('change', function() {
                var majorComponentID = $(this).val(); // get selected value
                getComponent(majorComponentID);
            });
        });



        // ✅ Select/Deselect all
        $(document).on('change', '#selectAll', function() {
            const checked = $(this).is(':checked');
            $('#dataTable tbody .row-checkbox').prop('checked', checked).trigger('change');
        });

        // ✅ Track selections
        $(document).on('change', '.row-checkbox', function() {
            const id = $(this).val();
            if (this.checked) selectedRows[id] = true;
            else delete selectedRows[id];
            $('#selectAll').prop('checked', $('.row-checkbox').length === $('.row-checkbox:checked').length);
        });

        // ✅ Maintain selections after table redraw
        $('#dataTable').on('draw.dt', function() {
            $('#dataTable .row-checkbox').each(function() {
                const id = $(this).val();
                $(this).prop('checked', !!selectedRows[id]);
            });
        });

        // ✅ Excel export button logic
        // $(document).on('click', '.buttons-excel', function (e) {
        //   e.preventDefault();
        //   const ids = Object.keys(selectedRows);
        //   exportSelectedRowsToCSV(ids);
        // });
        function exportSelectedRowsToCSV(ids) {
            const table = $('#dataTable').DataTable();
            const rows = [];

            // Collect selected rows
            table.rows().every(function() {
                const data = this.data();
                if (selectedRows[data.id]) {
                    rows.push({
                        financial_year: data.financial_year,
                        duration: data.duration,
                        duration_limit: data.duration_limit,
                        amount_allocated: data.amount_allocated,
                        majorComponent: data.majorComponent,
                        component: data.component,
                        subComponent: data.subComponent,
                        sanction_order_no: data.sanction_order_no,
                        sanction_order_date: data.sanction_order_date,
                        created_at: data.created_at
                    });
                }
            });

            // ✅ CSV headers (match the row fields)
            const headers = [
                "Financial Year",
                "Duration",
                "Duration Limit",
                "Amount Allocated",
                "Major Component",
                "Component",
                "Sub Component",
                "Sanction Order No",
                "Sanction Order Date",
                "Created At"
            ];

            // ✅ Build CSV content
            const csv = [
                headers.join(','),
                ...rows.map(r => [
                        r.financial_year,
                        r.duration,
                        r.duration_limit,
                        r.amount_allocated,
                        r.majorComponent,
                        r.component,
                        r.subComponent,
                        r.sanction_order_no,
                        r.sanction_order_date,
                        r.created_at
                    ]
                    .map(v => `"${String(v || '').replace(/"/g, '""')}"`) // escape quotes
                    .join(',')
                )
            ].join('\n');


            const blob = new Blob([csv], {
                type: 'text/csv;charset=utf-8;'
            });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Distribution_List.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.location.reload();

            function stripHtml(html) {
                const div = document.createElement("div");
                div.innerHTML = html;
                return div.textContent || div.innerText || "";
            }
        }
    </script>
@endsection
@endsection
