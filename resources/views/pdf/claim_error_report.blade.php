<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Claim Error Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 12px;
        }

        th {
            background: #f2f2f2;
        }
    </style>
</head>

<body>

    <h2>Claim Error Report</h2>
    <p>Generated At: {{ $generated_at }}</p>

    <table>
        <thead>
            <tr>
                <th>MSME Name</th>
                <th>Team ID</th>
                <th>Udyam Number</th>
                <th>Error Code</th>
                <th>Message</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($errors as $error)
                <tr>
                    <td>{{ $error['msme_name'] }}</td>
                    <td>{{ $error['team_id'] }}</td>
                    <td>{{ $error['udyam_number'] }}</td>
                    <td>{{ $error['code'] }}</td>
                    <td>{{ $error['message'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
