<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SNP Category List</title>
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
    <h2>SNP Category List</h2>
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
                <th>NP Team ID</th>
                <th>Organization Name</th>
                <th>Role</th>
                <th>Category</th>
                <th>Open MSME Count</th>
                <th>Transaction Type</th>
                <th>ONDC Domain Mapping</th>
                <th>Serviceability</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row['np_team_id'] }}</td>
                    <td>{{ $row['organization_name'] }}</td>
                    <td>{{ $row['role_name'] }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['open_msme_count'] ?? 0 }}</td>
                    <td>{{ $row['transaction_type'] }}</td>
                    <td>{{ $row['ondc_domain_mapping'] }}</td>
                    <td>{{ $row['serviceability'] }}</td>
                    <td>{{ $row['status_name'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center;">No records found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
