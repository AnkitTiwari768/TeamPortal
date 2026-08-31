@extends('components.admin.content-layout')
@section('card-content')


    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex align-items-left py-2">

                <div class="first-filter-row row  mb-3" >
                    <div class="col-lg-3 mb-2">
                        <div class="select-box">
                            <label class="form-label">From date</label>
                            <input type="text" class="form-control" name="from_date" id="from_dates"
                                placeholder="From Date">
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <div class="select-box">
                            <label class="form-label">To date</label>
                            <input type="text" class="form-control" name="to_date" id="to_dates"
                                placeholder="To Date">
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <div class="select-box">
                            <label>{{ __('Financial Year') }}</label>
                            {!! Form::select('financial_year', financial_year(), '', [
                                'class' => 'form-select filter_btn',
                                'id' => 'financial_year',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <div class="select-box">
                            <label class="form-label">{{ __('Duration') }}</label>
                            {!! Form::select('duration', duration(), $row['duration'] ?? null, [
                                'class' => 'form-select filter_btn',
                                'id' => 'duration',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2" id="duration_limit_wrapper" style="display:none;">
                        <div class="select-box" >
                            <div class="mb-3">
                                <label class="form-label">{{ __('Sub Duration') }}</label>
                                {!! Form::select('duration_limit', [], null, ['class' => 'form-select filter_btn', 'id' => 'duration_limit']) !!}
                                <span class="text-danger form-error" id="duration_limit_error"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">                   
                        <div class="select-box">
                            <label class="form-label">{{ __('Major Component') }}</label>
                            {!! Form::select(
                                'major_component_id',
                                dynamic_common_list($details?->majorcomponents),
                                $row['major_component_id'] ?? null,
                                ['class' => 'form-select filter_btn', 'id' => 'major_component_id'],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">  
                        <div class="select-box">
                            <label class="form-label">{{ __('Component') }}</label>
                            {!! Form::select('component_id', dynamic_common_list($details?->components), $row['component_id'] ?? null, [
                                'class' => 'form-select filter_btn',
                                'id' => 'component_id',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-lg-3 mb-2">  
                        <div class="select-box">
                            <label class="form-label">{{ __('Sub Component') }}</label>
                            {!! Form::select(
                                'sub_component_id',
                                dynamic_common_list($details?->subcomponents),
                                $row['sub_component_id'] ?? null,
                                ['class' => 'form-select filter_btn', 'id' => 'sub_component_id'],
                            ) !!}
                        </div>
                    </div>
                    <div class="align-items-center col-lg-3 d-flex justify-content-start mb-2">  
                        <a href="javascript:void(0)" class="clear-action ms-0" id="reset_btn" style="display: none;"> <img
                                            src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all</a>
                    </div>
                   
                </div>

                <!-- <div class="second-filter-row d-flex  gap-3" >
                   
                    <div class="select-box">
                        <label class="form-label">{{ __('Component') }}</label>
                        {!! Form::select('component_id', dynamic_common_list($details?->components), $row['component_id'] ?? null, [
                            'class' => 'form-select filter_btn',
                            'id' => 'component_id',
                        ]) !!}
                    </div>

                   
                </div> -->

                
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
                        <th>{{ __('Amount Disbursed') }}</th>
                        <!-- <th>{{ __('Payable Amount Rs.') }}</th> -->
                        <!-- <th>{{ __('TDS %') }}</th> -->
                        <!-- <th>{{ __('Major Component') }}</th> -->
                        <th>{{ __('Component') }}</th>
                        <th>{{ __('Sub Component') }}</th>
                        <!-- <th>{{ __('Sanction Order No') }}</th>
                        <th>{{ __('Sanction Order Date') }}</th>
                        <th>{{ __('Created Date') }}</th> -->
                        <th class="actions">{{ __('message.action') }}</th>
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
                column: 3,
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
                /*{
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
                },*/
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
                /*{
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
                },*/
                    {
                        "orderable": false,
                        "render": function(data, type, row) {
                            pview = buttonView("{{ url('fund-distribution-report-view') }}", row.id);
                            var actions = createActionButtons([pview]);

                            return actions;
                        }

                    }
            ],
            filters: ["financial_year","from_dates","to_dates","duration","duration_limit","major_component_id","component_id","sub_component_id"]
        });

        $(document).ready(function() {
            $('#major_component_id').on('change', function() {
                var majorComponentID = $(this).val(); // get selected value
                getComponent(majorComponentID);
            });
        });

        let halfYearly = @json(half_yearly());
        let quarterly = @json(quaterly());

        $('#duration').on('change', function() {
            var duration = $(this).val();
            var $limit = $('#duration_limit');
            $limit.empty().append('<option value="">Select</option>');

            if (duration === 'Half Yearly') {
                $.each(halfYearly, function(k, v) {
                    $limit.append('<option value="' + k + '">' + v + '</option>');
                });
                $('#duration_limit_wrapper').show();
            } else if (duration === 'Quarterly') {
                $.each(quarterly, function(k, v) {
                    $limit.append('<option value="' + k + '">' + v + '</option>');
                });
                $('#duration_limit_wrapper').show();
            } else if (duration === 'Monthly') {
                $.each(monthly, function(k, v) {
                    $limit.append('<option value="' + k + '">' + v + '</option>');
                });
                $('#duration_limit_wrapper').show();
            } else {
                $('#duration_limit_wrapper').hide();
            }
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

            $('#duration').val('').trigger('change');

            $('#duration_limit').empty().append('<option value="">Select</option>');

            $('#duration_limit_wrapper').hide();

            $('#financial_year').val('');
            $('#major_component_id').val('');
            $('#component_id').val('');
            $('#sub_component_id').val('');

            oTable.ajax.reload();

            $(".clear-action").hide();
        });
        
    </script>
@endsection
@endsection
