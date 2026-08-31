@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="card container-main-card">
        <div class="card-header">
            <div class="heading">
                <h1>Public Tracking Logs</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Logs</li>
                    </ol>
                </nav>
            </div>
        </div>
        <div class="card-body">
             <table id="dataTable" class="table datatable table-striped" width="100%">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Status Tab</th>
                        <th>Identifier (SHA256)</th>
                        <th>Result</th>
                        <th>IP Address</th>
                        <th>User Agent</th>
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
            column: 0,
            direction: "desc"
        },
        url: "{{ route('tracking-logs-datalist') }}",
        columns: [
            { "data": "created_at", "orderable": true, "render": function(data, type, row) { return moment(row.created_at).format("DD-MM-YYYY HH:mm"); } },
            { "data": "tab.label", "orderable": true },
            { "data": "identifier_hash", "orderable": true, "render": function(data, type, row) { return '<code title="' + row.identifier_hash + '">' + row.identifier_hash.substring(0, 16) + '...</code>'; } },
            { "data": "result_status_key", "orderable": true, "render": function(data, type, row) { 
                var color = row.result_status_key === 'found' ? 'success' : 'secondary';
                return '<span class="badge bg-' + color + '">' + row.result_status_key.toUpperCase() + '</span>'; 
            }},
            { "data": "ip_address", "orderable": true },
            { "data": "user_agent", "orderable": true, "render": function(data, type, row) { return '<span class="small text-muted" title="' + row.user_agent + '">' + row.user_agent.substring(0, 30) + '...</span>'; } }
        ]
    });
</script>
@endsection
