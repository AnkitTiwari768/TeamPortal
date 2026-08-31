<!DOCTYPE html>
<html>
<head>
    <title>BPP ID Update - Mapped Records</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #4472C4; color: #fff; }
    </style>
</head>
<body>
    <h2>BPP ID Update - Mapped Records</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Udyam No</th>
                <th>BPP ID</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['udyam_no'] ?? '' }}</td>
                <td>{{ $row['bpp_id'] ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align:center;">No records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
