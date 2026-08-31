@extends('components.admin.layout')
@section('page-content')

    <style>
        .detail-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-section-title i {
            color: #3b82f6;
        }

        .detail-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
            margin-bottom: 1.5rem;
        }

        .summary-metric-card {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }

        .table-themed {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e2e8f0 !important;
        }

        .table-themed thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            padding: 12px;
        }

        .table-themed tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .card.container-main-card {
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
    </style>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex align-items-center py-3" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>Distribution Analysis</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dist.index') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.fund_distribution') }}</a></li>
                                    <li class="breadcrumb-item active" style="color: #0f172a; font-weight: 500;">Distribution Analysis</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ route('dist.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left me-2"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- Parameter Badges --}}
                        <div class="d-flex align-items-center gap-2 mb-4 px-1 flex-wrap">
                            <i class="fa fa-filter text-muted"></i>
                            <span class="text-muted small fw-bold text-uppercase">Analysis Scope:</span>
                            <span class="badge bg-info text-white fw-semibold px-2 py-1.5" style="font-size: 0.8rem;">FY: {{ $fy }}</span>
                            <span class="badge bg-primary text-white fw-semibold px-2 py-1.5" style="font-size: 0.8rem;">Category: {{ $majorName }}</span>
                            @if($subName)
                                <span class="badge bg-warning text-dark fw-semibold px-2 py-1.5" style="font-size: 0.8rem;">Component: {{ $subName }}</span>
                            @endif
                        </div>

                        {{-- Summary Cards --}}
                        @php
                            $rem = (float) $totals->remaining;
                            $remBg = $rem < 0 ? 'bg-danger-subtle' : 'bg-warning-subtle';
                            $remBorder = $rem < 0 ? 'border-danger' : 'border-warning';
                            $remText = $rem < 0 ? 'text-danger' : 'text-dark';
                            
                            $transferredOut = (float) ($totals->total_transferred_out ?? 0);
                            $colClass = $transferredOut > 0 ? 'col-lg-3' : 'col-lg-4';
                        @endphp
                        <div class="row g-4 mb-4">
                            <!-- Cumulative Allocation -->
                            <div class="{{ $colClass }} col-md-6 col-12">
                                <div class="card summary-metric-card h-100 bg-light-subtle">
                                    <div class="card-body p-3 text-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="fund-icon bg-success-subtle text-success border border-success-subtle" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                                                <i class="fa fa-university"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size:11px; letter-spacing: 0.05em;">Total Cumulative Allocation</p>
                                        <h3 class="fw-bold text-dark mb-0">₹ {{ format_indian_currency($totals->total_allocated) }}</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Distributed -->
                            <div class="{{ $colClass }} col-md-6 col-12">
                                <div class="card summary-metric-card h-100 bg-light-subtle">
                                    <div class="card-body p-3 text-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="fund-icon bg-primary-subtle text-primary border border-primary-subtle" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                                                <i class="fa fa-share-square-o"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size:11px; letter-spacing: 0.05em;">Aggregated Total Distributed</p>
                                        <h3 class="fw-bold text-dark mb-0">₹ {{ format_indian_currency($totals->total_distributed) }}</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Real-time Balance -->
                            <div class="{{ $colClass }} col-md-6 col-12">
                                <div class="card summary-metric-card h-100 {{ $remBg }} border {{ $remBorder }}">
                                    <div class="card-body p-3 text-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="fund-icon bg-white text-dark" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                                                <i class="fa fa-pie-chart"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size:11px; letter-spacing: 0.05em;">Real-time Remaining Balance</p>
                                        <h3 class="fw-bold {{ $remText }} mb-0">₹ {{ format_indian_currency($rem) }}</h3>
                                    </div>
                                </div>
                            </div>

                            @if($transferredOut > 0)
                            <!-- Allocate to Another Component -->
                            <div class="{{ $colClass }} col-md-6 col-12">
                                <div class="card summary-metric-card h-100 bg-light-subtle">
                                    <div class="card-body p-3 text-start">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="fund-icon bg-info-subtle text-info border border-info-subtle" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">
                                                <i class="fa fa-exchange"></i>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-1 fw-bold text-uppercase" style="font-size:11px; letter-spacing: 0.05em;">Allocate to Another Component</p>
                                        <h3 class="fw-bold text-dark mb-0">₹ {{ format_indian_currency($transferredOut) }}</h3>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>

                        {{-- Durational Breakdowns --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-clock-o"></i> Durational Pool Breakdowns
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-themed table-striped table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Duration Name</th>
                                            <th>Sub Duration</th>
                                            <th>Half Year</th>
                                            <th class="text-end">Allocated (₹)</th>
                                            <th class="text-end">Distributed (₹)</th>
                                            <th class="text-end">Remaining (₹)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($breakdowns as $b)
                                            <tr>
                                                <td class="fw-bold">{{ $b->duration_name }}</td>
                                                <td>{{ $b->sub_duration_name ?? '-' }}</td>
                                                <td>{{ $b->half_year_label ?? '-' }}</td>
                                                <td class="text-end">₹ {{ format_indian_currency($b->total_allocated_amount) }}</td>
                                                <td class="text-end text-success fw-semibold">₹ {{ format_indian_currency($b->total_distributed_amount) }}</td>
                                                <td class="text-end {{ (float)$b->remaining_balance < 0 ? 'text-danger fw-bold' : 'text-dark' }}">
                                                    ₹ {{ format_indian_currency($b->remaining_balance) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No durational breakdowns found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Transaction Ledger --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-list-alt"></i> Transaction Ledger Logs
                            </h5>
                            <!-- Ledger Date/Filter Panel -->
                            <div class="border pb-3 mb-3 bg-light p-3 rounded">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                            <input type="text" id="from_date" class="form-control datepicker filter-trigger" placeholder="From Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fa fa-calendar text-muted"></i></span>
                                            <input type="text" id="to_date" class="form-control datepicker filter-trigger" placeholder="To Date" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <select id="source_type" class="form-select filter-trigger">
                                            <option value="">Select Source Type</option>
                                            <option value="MANUAL">MANUAL</option>
                                            <option value="SYSTEM">SYSTEM</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <button type="button" class="btn btn-outline-secondary px-3" id="reset-filters">
                                            <i class="fa fa-undo me-1"></i> Reset
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive mt-3">
                                <table id="ledgerTable" class="table table-themed table-striped table-bordered align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th style="width: 50px;">S.No.</th>
                                            <th>Source</th>
                                            <th>Sanction Date</th>
                                            <th>Period Name</th>
                                            <th class="text-end">Distribution (₹)</th>
                                            <th class="text-end">Net Payable (₹)</th>
                                            <th class="text-center" style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@section('js')
    <script>
        var tableId = "#ledgerTable";
        window.csrfToken = "{{ csrf_token() }}";

        // Bind events before initialization
        $(tableId).on('preXhr.dt', function() {
            $('#ajax-loader').show();
        });

        $(tableId).on('xhr.dt draw.dt', function() {
            $('#ajax-loader').hide();
        });

        // Initialize Datepickers
        $('.datepicker').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            todayHighlight: true,
            endDate: "today"
        });

        // Trigger Table Redraw on filter change
        $('.filter-trigger').on('change', function () {
            $(tableId).DataTable().draw();
        });

        // Reset Filter Handler
        $('#reset-filters').on('click', function () {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#source_type').val('');
            $(tableId).DataTable().draw();
        });

        dataTableInit({
            id: "#ledgerTable",
            url: "{{ url('fund-distributions/api/drill-list/' . $fy . '/' . $majorId . '/' . $subId) }}",
            showExcelExport: true,
            order: { column: 2, direction: "desc" },
            columns: [
                {
                    "orderable": false,
                    "render": function (data, type, full, meta) {
                        return serialNumber("#ledgerTable", meta.row);
                    }
                },
                {
                    "data": "source_type",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let labelClass = row.source_type === 'MANUAL' ? 'bg-primary' : 'bg-warning text-dark';
                        return `<span class="badge rounded-pill ${labelClass} px-2.5 py-1">${row.source_type}</span>`;
                    }
                },
                {
                    "data": "sanction_order_date",
                    "orderable": true,
                    "render": function (data, type, row) {
                        if (!row.sanction_order_date) return 'N/A';
                        let d = new Date(row.sanction_order_date);
                        let day = String(d.getDate()).padStart(2, '0');
                        let month = String(d.getMonth() + 1).padStart(2, '0');
                        let year = d.getFullYear();
                        return `${day}-${month}-${year}`;
                    }
                },
                {
                    "data": "duration_name",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let sub = row.sub_duration_name ? `<br><small class='text-muted'>${row.sub_duration_name}</small>` : '';
                        return `<span class='fw-bold text-dark'>${row.duration_name || 'N/A'}</span>${sub}`;
                    }
                },
                {
                    "data": "distribution_amount",
                    "orderable": true,
                    "className": "text-end fw-semibold",
                    "render": function (data, type, row) {
                        return Number(row.distribution_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": "net_payable_amount",
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function (data, type, row) {
                        return Number(row.net_payable_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        let viewUrl = `{{ url('fund-distributions') }}/${row.id}/show`;

                        return `
                            <div class="d-flex justify-content-center gap-1">
                                <a href="${viewUrl}" class="btn btn-sm btn-info text-white" title="View Details"><i class="fa fa-eye"></i></a>
                            </div>
                        `;
                    }
                }
            ],
            filters: ["source_type", "from_date", "to_date"]
        });
    </script>
@endsection

@endsection
