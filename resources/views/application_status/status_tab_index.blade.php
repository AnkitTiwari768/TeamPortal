@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="card container-main-card">
            <div class="card-header d-flex align-items-center">
                <div class="heading">
                    <h1>Tracking Status Tabs</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active">Status Tabs</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('status-tabs-create') }}" class="btn btn-danger">
                        <i class="fa fa-plus me-1"></i> Add Status Tab
                    </a>
                </div>
            </div>

            <div class="card-body">
                <table id="dataTable" class="table datatable table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Tab Key</th>
                            <th>Label</th>
                            <th>Status</th>
                            <th>Fields</th>
                            <th>Search Total</th>
                            <th class="actions">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        dataTableInit({
            id: "#dataTable",
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ route('status-tabs-datalist') }}",
            columns: [
                {
                    "orderable": false,
                    "render": function (data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                { "data": "tab_key", "orderable": true },
                { "data": "label", "orderable": true },
                {
                    "orderable": true,
                    "render": function (data, type, row) {
                        var status = (row.is_enabled == 1 || row.is_enabled == true) ? 'Active' : 'Inactive';
                        var badge = (status == 'Active') ? 'bg-success' : 'bg-secondary';
                        return '<span class="badge ' + badge + '">' + status + '</span>';
                    }
                },
                {
                    "orderable": false,
                    "render": function (data, type, row) {
                        return '<a href="{{ url("fields") }}/' + row.id + '" class="btn btn-sm btn-outline-info">Fields (' + (row.fields_count || 0) + ')</a>';
                    }
                },
                {
                    "data": "search_logs_count",
                    "orderable": true,
                    "render": function (data) { return data || 0; }
                },
                {
                    "orderable": false,
                    "render": function (data, type, row) {
                        var edit = buttonEdit("{{ url('status-tabs-edit') }}", row.id);
                        var del = buttonDelete("{{ url('status-tabs-delete') }}", row.id);
                        var timeline = '<a href="{{ url("timelines") }}/' + row.id + '" class="btn btn-sm btn-outline-primary ms-1" title="Timeline"><i class="fa fa-list"></i></a>';
                        return createActionButtons([edit, timeline, del]);
                    }
                }
            ]
        });
    </script>
@endsection