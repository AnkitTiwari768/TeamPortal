<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Udyam & Mobile Verification Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
            color: var(--text-main);
            min-height: 100vh;
            padding: 2rem;
            overflow-x: hidden;
        }

        .container {
            max-width: 1300px;
            margin: 0 auto;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .badge-live {
            background: rgba(168, 85, 247, 0.2);
            color: #d8b4fe;
            border: 1px solid rgba(168, 85, 247, 0.4);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .badge-live::before {
            content: '';
            width: 8px;
            height: 8px;
            background-color: #a855f7;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 8px #a855f7;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.2); opacity: 1; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, border-color 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: transparent;
            transition: background 0.3s ease;
        }

        .stat-card.active::after {
            background: var(--primary-gradient);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-desc {
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        /* Main Workspace split */
        .workspace {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 2rem;
            align-items: start;
        }

        .panel {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .panel-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-main);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        textarea {
            width: 100%;
            height: 180px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1rem;
            color: var(--text-main);
            font-size: 0.875rem;
            resize: none;
            outline: none;
            transition: border-color 0.3s ease;
        }

        textarea:focus {
            border-color: #6366f1;
        }

        .btn {
            width: 100%;
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 1rem;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .btn:hover {
            opacity: 0.95;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn:disabled {
            background: #475569;
            box-shadow: none;
            cursor: not-allowed;
        }

        /* Results table area */
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .search-bar {
            width: 300px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            color: var(--text-main);
            outline: none;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            border-radius: 1rem;
            border: 1px solid var(--border-color);
            max-height: 550px;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(15, 23, 42, 0.8);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-muted);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.02);
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .badge-info {
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
        }

        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 9999px;
            margin-bottom: 1.5rem;
            overflow: hidden;
            display: none;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary-gradient);
            width: 0%;
            transition: width 0.3s ease;
        }

        @media (max-width: 968px) {
            .workspace {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <header>
        <div>
            <h1>Udyam Verification Center</h1>
            <p style="color: var(--text-muted); margin-top: 0.25rem;">Real-time registration & status audit suite</p>
        </div>
        <div class="badge-live">Live Stream Channel Active</div>
    </header>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card active" id="card-total">
            <div class="stat-label">Total Audited</div>
            <div class="stat-value" id="stat-total">0</div>
            <div class="stat-desc">Udyam combinations queued</div>
        </div>
        <div class="stat-card active" id="card-valid">
            <div class="stat-label">Exists Count</div>
            <div class="stat-value" id="stat-valid">0</div>
            <div class="stat-desc" style="color: var(--success);" id="stat-valid-percent">0% Verified</div>
        </div>
        <div class="stat-card" id="card-trading">
            <div class="stat-label">Trading Activity</div>
            <div class="stat-value" id="stat-trading">0</div>
            <div class="stat-desc">Major Activity: Trading</div>
        </div>
        <div class="stat-card" id="card-medium">
            <div class="stat-label">Medium Enterprises</div>
            <div class="stat-value" id="stat-medium">0</div>
            <div class="stat-desc">Classification: Medium</div>
        </div>
    </div>

    <!-- Main Workspace -->
    <div class="workspace">
        <div class="panel">
            <div class="panel-title">Verification Scope</div>
            
            <div class="form-group">
                <label class="form-label" for="combinations-input">Udyam & Mobile Combinations (JSON Format)</label>
                <textarea id="combinations-input" placeholder='{
  "UDYAM-KR-03-0061075": "9880263737",
  "UDYAM-TN-03-0011781": "8919897892"
}'></textarea>
                <span class="stat-desc" style="margin-top: 0.5rem; display: block;">Leave blank to run the pre-loaded large registry audit.</span>
            </div>

            <button class="btn" id="start-btn" onclick="startVerification()" style="margin-bottom: 1rem;">Initiate Streaming Audit</button>
            <button class="btn" id="download-btn" onclick="downloadExcel()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);" disabled>Download Excel Report</button>
        </div>

        <div class="panel" style="padding: 1.5rem;">
            <div class="results-header">
                <div class="panel-title" style="margin-bottom: 0;">Audit Log Results</div>
                <input type="text" class="search-bar" id="search-input" onkeyup="filterTable()" placeholder="Filter by Udyam, Name...">
            </div>

            <div class="progress-bar-container" id="progress-container">
                <div class="progress-bar-fill" id="progress-fill"></div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Udyam Number</th>
                            <th>Mobile</th>
                            <th>Status</th>
                            <th>Enterprise Name</th>
                            <th>Major Activity</th>
                            <th>Enterprise Type</th>
                        </tr>
                    </thead>
                    <tbody id="results-tbody">
                        <tr>
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 3rem;">
                                Click "Initiate Streaming Audit" to begin verifying records in real time.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let totalVerified = 0;
    let validCount = 0;
    let tradingCount = 0;
    let mediumCount = 0;
    let totalQueued = 0;
    let allResults = [];

    async function startVerification() {
        // Reset UI stats & download data
        totalVerified = 0;
        validCount = 0;
        tradingCount = 0;
        mediumCount = 0;
        allResults = [];
        
        document.getElementById('stat-total').innerText = '0';
        document.getElementById('stat-valid').innerText = '0';
        document.getElementById('stat-valid-percent').innerText = '0% Verified';
        document.getElementById('stat-trading').innerText = '0';
        document.getElementById('stat-medium').innerText = '0';
        document.getElementById('download-btn').disabled = true;

        const tbody = document.getElementById('results-tbody');
        tbody.innerHTML = ''; // clear table
        
        const startBtn = document.getElementById('start-btn');
        startBtn.disabled = true;
        startBtn.innerText = 'Auditing Live Stream...';

        const progressContainer = document.getElementById('progress-container');
        const progressFill = document.getElementById('progress-fill');
        progressContainer.style.display = 'block';
        progressFill.style.width = '0%';

        // Collect payload
        const rawInput = document.getElementById('combinations-input').value.trim();
        let payload = {};
        
        if (rawInput) {
            try {
                payload = JSON.parse(rawInput);
            } catch (e) {
                alert('Invalid JSON input format. Please check syntax.');
                startBtn.disabled = false;
                startBtn.innerText = 'Initiate Streaming Audit';
                return;
            }
        }

        totalQueued = Object.keys(payload).length || 800; // default large set is ~800 entries

        try {
            const response = await fetch('{{ url("/api/v1/udyam/check-combinations") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/x-ndjson',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ combinations: payload })
            });

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const reader = response.body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;

                buffer += decoder.decode(value, { stream: true });
                const lines = buffer.split('\n');
                buffer = lines.pop(); // keep unfinished line in buffer

                for (const line of lines) {
                    if (!line.trim()) continue;
                    
                    try {
                        const payloadData = JSON.parse(line);
                        
                        if (payloadData.type === 'result') {
                            appendResultRow(payloadData.udyam, payloadData.data);
                        } else if (payloadData.type === 'summary') {
                            updateFinalSummary(payloadData.data);
                        }
                    } catch (e) {
                        console.error('Failed to parse line:', line, e);
                    }
                }
            }

        } catch (error) {
            console.error('Streaming request failed:', error);
            tbody.innerHTML += `<tr><td colspan="6" style="color: var(--danger); text-align: center; font-weight: bold;">Error during streaming check: ${error.message}</td></tr>`;
        } finally {
            startBtn.disabled = false;
            startBtn.innerText = 'Initiate Streaming Audit';
            if (allResults.length > 0) {
                document.getElementById('download-btn').disabled = false;
            }
        }
    }

    function appendResultRow(udyam, data) {
        totalVerified++;
        document.getElementById('stat-total').innerText = totalVerified;

        // Update progress bar
        const progressFill = document.getElementById('progress-fill');
        const percent = Math.min((totalVerified / totalQueued) * 100, 100);
        progressFill.style.width = `${percent}%`;

        const tbody = document.getElementById('results-tbody');
        
        let statusBadge = '';
        let enterpriseName = '-';
        let majorActivity = '-';
        let enterpriseType = '-';

        if (data.exists) {
            validCount++;
            document.getElementById('stat-valid').innerText = validCount;
            const validPct = Math.round((validCount / totalVerified) * 100);
            document.getElementById('stat-valid-percent').innerText = `${validPct}% Verified`;

            statusBadge = '<span class="badge badge-success">Valid</span>';
            enterpriseName = data.enterprise_name || '-';
            majorActivity = data.major_activity || '-';
            enterpriseType = data.enterprise_type || '-';

            // Real-time checks for statistics
            if (majorActivity.toLowerCase().includes('trading')) {
                tradingCount++;
                document.getElementById('stat-trading').innerText = tradingCount;
                document.getElementById('card-trading').classList.add('active');
            }
            if (enterpriseType.toLowerCase().includes('medium')) {
                mediumCount++;
                document.getElementById('stat-medium').innerText = mediumCount;
                document.getElementById('card-medium').classList.add('active');
            }
        } else {
            statusBadge = `<span class="badge badge-danger">Invalid (${data.error || 'Wrong Detail'})</span>`;
        }

        // Push to download registry
        allResults.push({
            udyam: udyam,
            mobile: data.mobile || '',
            exists: data.exists,
            enterprise_name: data.enterprise_name || '',
            major_activity: data.major_activity || '',
            enterprise_type: data.enterprise_type || '',
            email: data.email || '',
            error: data.error || ''
        });

        const row = document.createElement('tr');
        row.innerHTML = `
            <td style="font-weight: 500;">${udyam}</td>
            <td style="color: var(--text-muted);">${data.mobile || '-'}</td>
            <td>${statusBadge}</td>
            <td>${enterpriseName}</td>
            <td>${majorActivity}</td>
            <td>${enterpriseType}</td>
        `;
        
        tbody.appendChild(row);
        
        // Auto scroll container down slightly to show new results
        const container = tbody.closest('.table-container');
        container.scrollTop = container.scrollHeight;
    }

    function downloadExcel() {
        if (!allResults.length) return;

        let csv = [];
        // Header
        csv.push(["Udyam Number", "Mobile Number", "Status", "Enterprise Name", "Major Activity", "Enterprise Type", "Email", "Error"].map(val => `"${val.replace(/"/g, '""')}"`).join(","));
        
        allResults.forEach(row => {
            const line = [
                row.udyam,
                row.mobile,
                row.exists ? 'Valid' : 'Invalid',
                row.enterprise_name,
                row.major_activity,
                row.enterprise_type,
                row.email,
                row.error
            ].map(val => `"${(val + '').replace(/"/g, '""')}"`).join(",");
            csv.push(line);
        });
        
        const csvContent = "\ufeff" + csv.join("\r\n"); // UTF-8 BOM
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", `udyam_audit_report_${new Date().toISOString().slice(0, 10)}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function updateFinalSummary(summary) {
        document.getElementById('stat-total').innerText = summary.total_checked;
        document.getElementById('stat-valid').innerText = summary.exists_count;
        document.getElementById('stat-trading').innerText = summary.trading_activity_count;
        document.getElementById('stat-medium').innerText = summary.medium_enterprise_count;
        
        const validPct = Math.round((summary.exists_count / summary.total_checked) * 100) || 0;
        document.getElementById('stat-valid-percent').innerText = `${validPct}% Verified`;

        const progressFill = document.getElementById('progress-fill');
        progressFill.style.width = '100%';

        if (summary.trading_activity_count > 0) {
            document.getElementById('card-trading').classList.add('active');
        }
        if (summary.medium_enterprise_count > 0) {
            document.getElementById('card-medium').classList.add('active');
        }
    }

    function filterTable() {
        const input = document.getElementById('search-input');
        const filter = input.value.toLowerCase();
        const tbody = document.getElementById('results-tbody');
        const trs = tbody.getElementsByTagName('tr');

        for (let i = 0; i < trs.length; i++) {
            const tr = trs[i];
            const text = tr.innerText.toLowerCase();
            if (text.includes(filter)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        }
    }
</script>

</body>
</html>
