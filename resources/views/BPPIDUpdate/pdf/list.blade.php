<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>BPP ID Update List</title>
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
    <h2>BPP ID Update List</h2>
    <p>Generated At: {{ $generated_at }}</p>
    @if (($total_rows ?? 0) > ($max_rows ?? 0))
        <p style="color:#c0392b;">
            Showing the first {{ $max_rows }} of {{ $total_rows }} records.
            Use "Download Excel" to get the complete dataset.
        </p>
    @else
        <p>Total Records: {{ $total_rows }}</p>
    @endif
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Old BPPID</th>
                <th>New BPPID</th>
                <th>Updated At</th>
                <th>Udyam No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>-</td>
                    <td>{{ $row->bpp_id ?: '-' }}</td>
                    <td>{{ $row->bpp_updated_at ? date('d-m-Y H:i:s', strtotime($row->bpp_updated_at)) : '-' }}</td>
                    <td>{{ $row->udyam_no }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
