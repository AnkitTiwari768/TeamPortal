<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Timeline History</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h2 { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 5px; font-size: 10px; }
        th { background: #2541b2; color: #fff; text-align: left; }
        tr:nth-child(even) { background: #f5f6fa; }
    </style>
</head>
<body>
    <h2>Batch Timeline History</h2>
    <p>Generated At: {{ $generated_at }}</p>
    @if (($total_rows ?? 0) > ($max_rows ?? 0))
        <p style="color:#c0392b;">
            Showing the first {{ $max_rows }} of {{ $total_rows }} matching records.
            Use "Download Excel" on the report page to get the complete dataset.
        </p>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Organization Name</th>
                <th>Batch Number</th>
                <th>Subject</th>
                <th>Action</th>
                <th>Status</th>
                <th>Date</th>
                <th>Created By Role</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->organization_name }}</td>
                    <td>{{ $row->batch_number }}</td>
                    <td>{{ $row->subject }}</td>
                    <td>{{ $row->action }}</td>
                    <td>{{ $row->status }}</td>
                    <td>{{ date('d-m-Y H:i:s', strtotime($row->created_at)) }}</td>
                    <td>{{ $row->created_by_role }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
