@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body">
    <form id="search_form" autocomplete="off">
        <div class="filter-bar d-flex px-3 py-2 gap-3 align-items-end flex-wrap">
            <div class="select-box">
                <label>{{ __('Affected User') }}</label>
                <input type="text" class="form-control filter_btn" id="filter_user" placeholder="Name or email">
            </div>

            <div class="select-box">
                <label>{{ __('Performed By') }}</label>
                <input type="text" class="form-control filter_btn" id="filter_admin" placeholder="Name or email">
            </div>

            <div class="select-box">
                <label>{{ __('Action') }}</label>
                <select id="filter_action" class="form-select filter_btn">
                    <option value="">{{ __('message.select') }}</option>
                    @foreach($actionOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="select-box">
                <label>{{ __('From Date') }}</label>
                <input type="text" class="form-control filter_btn" id="filter_date_from" placeholder="DD-MM-YYYY" autocomplete="off">
            </div>

            <div class="select-box">
                <label>{{ __('To Date') }}</label>
                <input type="text" class="form-control filter_btn" id="filter_date_to" placeholder="DD-MM-YYYY" autocomplete="off">
            </div>

            <a href="javascript:void(0)" class="clear-action" id="reset_btn" style="display: none;">
                <img src="{{ asset('assets/ffo-admin/img/close-blue.svg') }}">{{ __('Clear all') }}
            </a>
        </div>
    </form>
</div>

<div class="card-body">
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>{{ __('message.sn') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Affected User') }}</th>
                    <th>{{ __('Performed By') }}</th>
                    <th>{{ __('Description') }}</th>
                    <th>{{ __('Date & Time') }}</th>
                    <th class="actions">{{ __('message.action') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@section('js')
<script>
$(document).ready(function() {

    var table = dataTableInit({
        id: "#dataTable",
        order: { column: 5, direction: "DESC" },
        url: "{{ url('user-logs/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function(data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) { return row.action_label; }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.user_name + (row.user_email ? '<br><small class="text-muted">' + row.user_email + '</small>' : '');
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.performed_by_name + (row.performed_by_email ? '<br><small class="text-muted">' + row.performed_by_email + '</small>' : '');
                }
            },
            {
                "orderable": false,
                "render": function(data, type, row) { return row.description; }
            },
            {
                "orderable": true,
                "render": function(data, type, row) { return row.created_at; }
            },
            {
                "orderable": false,
                "render": function(data, type, row) {
                    return createActionButtons([buttonView("{{ url('user-logs') }}", row.id)]);
                }
            }
        ],
        filters: ["filter_user", "filter_admin", "filter_action", "filter_date_from", "filter_date_to"]
    });

    function getTable() { return $('#dataTable').DataTable(); }
    function reloadTable() { getTable().ajax.reload(null, false); }

    $("#filter_date_from, #filter_date_to").datepicker({
        dateFormat: "dd-mm-yy",
        changeYear: true,
        changeMonth: true,
        maxDate: 0,
        onSelect: function(selected) {
            var target = this.id === "filter_date_from" ? "#filter_date_to" : "#filter_date_from";
            $(target).datepicker("option", this.id === "filter_date_from" ? "minDate" : "maxDate", selected);
            reloadTable();
            showClearButton();
        }
    });

    function showClearButton() {
        var hasFilter = false;
        $('#filter_user, #filter_admin, #filter_action, #filter_date_from, #filter_date_to').each(function() {
            if ($(this).val()) { hasFilter = true; return false; }
        });
        hasFilter ? $('#reset_btn').show() : $('#reset_btn').hide();
    }

    function debounce(func, wait) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function() { func.apply(context, args); }, wait);
        };
    }

    var debouncedReload = debounce(function() {
        reloadTable();
        showClearButton();
    }, 400);

    $('#filter_user, #filter_admin').on('keyup input', debouncedReload);
    $('#filter_action').on('change', function() {
        reloadTable();
        showClearButton();
    });

    $('#reset_btn').on('click', function() {
        $('#search_form')[0].reset();
        $("#filter_date_from, #filter_date_to").datepicker("option", { minDate: null, maxDate: null });
        $(this).hide();
        reloadTable();
    });

    showClearButton();
});
</script>
@endsection
@endsection
