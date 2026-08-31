@extends('components.admin.content-layout')
@section('card-content')

<div class="card-body">
    <!-- Filter Section -->
    <div class="mb-4 p-3 bg-light rounded border">
        <form id="filterForm" class="row align-items-end">
            <div class="col-md-4">
                <label for="filter_event_name" class="form-label font-weight-bold">{{ __('Event Name') }}</label>
                <input type="text" id="filter_event_name" name="event_name" class="form-control" placeholder="Search by event name...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> {{ __('Filter') }}</button>
            </div>
            <div class="col-md-2">
                <button type="button" id="resetBtn" class="btn btn-secondary btn-block"><i class="fa fa-undo"></i> {{ __('Reset') }}</button>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="eventLogsTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>{{ __('message.sn') }}</th>
                    <th>{{ __('Event Name') }}</th>
                    <th>{{ __('Execution Time') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

@section('js')
<script>
$(document).ready(function() {
    var table = dataTableInit({
        id: "#eventLogsTable",
        order: {
            column: 2,
            direction: "DESC"
        },
        url: "{{ url('event-logs/datalist') }}",
        columns: [
            {
                "orderable": false,
                "render": function(data, type, full, meta) {
                    return serialNumber("#eventLogsTable", meta.row);
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return `<strong>${row.event_name}</strong>`;
                }
            },
            {
                "orderable": true,
                "render": function(data, type, row) {
                    return row.executed_at;
                }
            },
            {
                "orderable": false,
                "render": function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="Delete"><i class="fa fa-trash"></i></button>
                    `;
                }
            }
        ],
        filters: ["filter_event_name"]
    });

    // Filtering
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        $(this).find('button[type="submit"]').prop('disabled', true);
        table.ajax.reload();
        $(this).find('button[type="submit"]').prop('disabled', false);
    });

    // Reset Filter
    $('#resetBtn').on('click', function() {
        $('#filterForm')[0].reset();
        table.ajax.reload();
    });

    // Delete Log Trigger
    $(document).on('click', '.delete-btn', function() {
        var logId = $(this).data('id');
        if (confirm('Are you sure you want to delete this event log?')) {
            $.ajax({
                url: "{{ url('event-logs') }}/" + logId,
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload(null, false);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Server Error: Something went wrong.');
                }
            });
        }
    });
});
</script>
@endsection
@endsection
