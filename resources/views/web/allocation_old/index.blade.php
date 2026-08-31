@extends('components.admin.content-layout')

@section('action-header')
    <!-- @if (acl(config('permissions.allocation-create')))
            <div class="btn-group drop-btn">
                <button type="button" class="btn btn-danger" autocomplete="off"
                    onclick="window.location = '{{ url('web/allocation/create') }}'">
                    <img src="{{ asset('assets/ffo-admin/img/add.svg') }}" />
                    {{ __('Add Allocation') }}
                </button>
            </div>
        @endif -->
@endsection

@section('card-content')

    {{-- ── Filter Bar ──────────────────────────────────────────────── --}}
    {{-- Replace the entire filter bar div --}}
    <div class="border-bottom card-body d-flex justify-content-between">
        <form id="search_form" autocomplete="off">
            <div class="filter-bar d-flex px-0 py-2 gap-3 align-items-center">

                <div class="col-md-3 mb-2">
                    <label>{{ __('Financial Year') }}</label>
                    {!! Form::select('financial_year', financial_year(), '', [
        'class' => 'form-select filter_btn',
        'id' => 'financial_year',
    ]) !!}
                </div>

                <div class="col-md-3 mb-2">
                    <label>{{ __('Major Component') }}</label>
                    <select name="major_component_id" id="major_component_id" class="form-select filter_btn">
                        <option value="">-- All Major Components --</option>
                        @foreach($components ?? [] as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2">
                    <label>{{ __('Sub Component') }}</label>
                    <select name="sub_component_id" id="sub_component_id" class="form-select filter_btn">
                        <option value="">-- All Sub-Components --</option>
                    </select>
                </div>

                <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
                    <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">Clear all
                </a>
            </div>
        </form>
    </div>

    {{-- ── DataTable ───────────────────────────────────────────────── --}}
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>{{ __('message.sn') }}</th>
                        <th>{{ __('Financial Year') }}</th>
                        <th>{{ __('Duration') }}</th>
                        <th>{{ __('Sub Duration') }}</th>
                        <th>{{ __('Sanction Order No') }}</th>
                        <th>{{ __('Sanction Order Date') }}</th>
                        <th>{{ __('Total Amount Rs.') }}</th>
                        <th>{{ __('Created Date') }}</th>
                        <th class="actions">{{ __('message.action') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
    <script>
        $(document).ready(function () {

           window.oTable = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    destroy: true,

    dom:
        "<'row mb-3'<'col-md-8 dt-toolbar'lB><'col-md-4 text-end'f>>" +
        "rt" +
        "<'row mt-3'<'col-md-6'i><'col-md-6'p>>",

 

    pageLength: 10,

  buttons: [
    {
        extend: 'excelHtml5',
        text: '<i class="fa fa-file-excel me-1"></i> Excel',
        className: 'btn btn-light border',
        exportOptions: {
            columns: [0,1,2,3,4,5,6,7]
        }
    },
    {
        extend: 'pdfHtml5',
        text: '<i class="fa fa-file-pdf me-1"></i> Pdf',
        className: 'btn btn-light border',
        exportOptions: {
            columns: [0,1,2,3,4,5,6,7]
        }
    }
],
    ajax: {
        url: "{{ url('web/allocation/datalist') }}",
        type: "GET",
        data: function (d) {
            d.financial_year = $('#financial_year').val();
            d.major_component_id = $('#major_component_id').val();
            d.sub_component_id = $('#sub_component_id').val();
        }
    },

    order: [[7, 'desc']],

    columns: [
        {
            data: null,
            orderable: false,
            render: function(data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        },
        {
            data: 'financial_year',
            defaultContent: '—'
        },
        {
            data: 'duration_name',
            defaultContent: '—'
        },
        {
            data: 'sub_duration_name',
            defaultContent: '—'
        },
        {
            data: 'sanction_order_number',
            defaultContent: '—'
        },
        {
            data: 'sanction_order_date_fmt',
            render: function(data, type, row) {
                return row.sanction_order_date_fmt || row.sanction_order_date || '—';
            }
        },
        {
            data: 'total_amount',
            className: 'text-end',
            render: function(data, type, row) {
                return '₹ ' + Number(row.total_amount || 0).toLocaleString(
                    'en-IN',
                    {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }
                );
            }
        },
        {
            data: 'created_at_fmt',
            render: function(data, type, row) {
                return row.created_at_fmt || '—';
            }
        },
        {
            data: null,
            orderable: false,
            className: 'text-center',
            render: function(data, type, row) {

                let base = "{{ url('web/allocation') }}";
                let html = '';

                @if (acl(config('permissions.allocation-update')))
                    html += `
                        <a href="${base}/${row.id}/edit"
                           class="btn btn-sm btn-primary me-1">
                           <i class="fa fa-edit"></i>
                        </a>
                    `;
                @endif

                @if (acl(config('permissions.allocation-view')))
                    html += `
                        <a href="${base}/${row.id}"
                           class="btn btn-sm btn-info">
                           <i class="fa fa-eye"></i>
                        </a>
                    `;
                @endif

                return html;
            }
        }
    ]
});
            // Filter dropdowns
            $('.filter_btn').on('change', function () {
                if ($(this).attr('id') === 'major_component_id') {
                    var majorId = $(this).val();
                    var $subSel = $('#sub_component_id');
                    $subSel.html('<option value="">-- All Sub-Components --</option>');

                    if (majorId) {
                        // Using the attribute-values endpoint as per 'create' form logic
                        apiRequest({
                            url: "{{ url('web/api/allocation/attribute-values') }}/" + encodeURIComponent("{{ config('allocation.sub_component_code', 'major-components') }}"),
                            method: 'GET',
                            params: { parent_id: majorId }
                        }).then(function (result) {
                            var items = Array.isArray(result) ? result : (result.data || []);
                            $.each(items, function (i, item) {
                                $subSel.append('<option value="' + item.id + '">' + (item.name || item.attribute_value || '') + '</option>');
                            });
                        }).catch(function () {
                            console.error("Failed to load sub-components.");
                        });
                    }
                }
                // Only reload if oTable is defined (should be here)
                if (window.oTable) {
                    window.oTable.ajax.reload();
                }
            });

            // Reset button
            $('#reset_btn').on('click', function () {
                $('#financial_year').val('');
                $('#major_component_id').val('');
                $('#sub_component_id').html('<option value="">-- All Sub-Components --</option>').val('');
                $(this).hide();
                if (window.oTable) {
                    window.oTable.ajax.reload();
                }
            });

        });
    </script>
@endsection
<style>
    /* Toolbar Layout */
    .dt-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .dataTables_length {
        margin-bottom: 0 !important;
    }

    .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
    }

    .dataTables_length select {
        min-width: 90px;
        height: 46px;
        border-radius: 6px;
    }

    .dt-buttons {
        display: flex;
        gap: 10px;
        margin-left: 10px;
    }

    /* Excel Button */
    .buttons-excel {
        background: #0d6efd !important;
        border: 1px solid #0d6efd !important;
        color: #fff !important;
        border-radius: 6px !important;
        min-width: 90px;
        height: 46px;
    }

    .buttons-excel:hover {
        background: #0b5ed7 !important;
        border-color: #0a58ca !important;
    }

    /* PDF Button */
    .buttons-pdf {
        background: #dc3545 !important;
        border: 1px solid #dc3545 !important;
        color: #fff !important;
        border-radius: 6px !important;
        min-width: 90px;
        height: 46px;
    }

    .buttons-pdf:hover {
        background: #bb2d3b !important;
        border-color: #b02a37 !important;
    }

    /* Records Button */
    .buttons-page-length {
        background: #6c757d !important;
        border: 1px solid #6c757d !important;
        color: #fff !important;
        border-radius: 6px !important;
        min-width: 100px;
        height: 46px;
    }

    .buttons-page-length:hover {
        background: #5c636a !important;
        border-color: #565e64 !important;
    }
</style>