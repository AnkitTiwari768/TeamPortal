<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>MSME All List</title>
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
    <h2>MSME All List</h2>
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
                <th>Team ID</th>
                <th>Udyam Number</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Entrepreneur Name</th>
                <th>Enterprise Name</th>
                <th>Enterprise Type</th>
                <th>Major Activity</th>
                <th>State</th>
                <th>Registration Date</th>
                <th>Transaction Type</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->team_id }}</td>
                    <td>{{ $row->udyam_no }}</td>
                    <td>{{ $row->mobile }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->entrepreneur_name }}</td>
                    <td>{{ $row->enterprise_name }}</td>
                    <td>{{ $row->organisation_type }}</td>
                    <td>{{ $row->major_activity }}</td>
                    <td>{{ $row->state_name }}</td>
                    <td>{{ date('d-m-Y', strtotime($row->created_at)) }}</td>
                    <td>{{ $row->transaction_type }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align:center;">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
