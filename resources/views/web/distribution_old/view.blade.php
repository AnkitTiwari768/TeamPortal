@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <!-- Dashboard Breadcrumbs & Heading -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Distribution Analysis</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb text-muted small">
                        <li class="breadcrumb-item"><a href="{{ route('dist.index') }}"
                                class="text-decoration-none">Distributions</a></li>
                        <li class="breadcrumb-item active">Distribution Analysis</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="{{ route('dist.index') }}" class="btn btn-light border text-muted shadow-sm px-3">
                    <i class="fa fa-arrow-left me-2"></i> Back to Overview
                </a>
            </div>
        </div>

        <!-- Context Information Banner -->
        <div class="alert alert-dark bg-gradient border-0 shadow-sm mb-4 text-white d-flex align-items-center">
            <i class="fa fa-info-circle fa-2x me-3 opacity-50"></i>
            <div>
                <div class="small opacity-75 text-uppercase">Filtering Parameters</div>
                <span class="fw-bold fs-5">{{ $fy }}</span> &bull;
                <span class="fw-bold fs-5">{{ $majorName }}</span>
                @if($subName) &bull; <span class="fw-bold fs-5">{{ $subName }}</span> @endif
            </div>
        </div>

        <!-- Analytic Overview Cards -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <!-- Total Allocated Card -->
            <div class="col">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
                    <div class="card-body p-4">
                        <h6 class="text-muted text-uppercase fw-bold small mb-2">Total Cumulative Allocation</h6>
                        <h2 class="fw-bolder text-primary mb-0">₹{{ number_format((float) $totals->total_allocated, 2) }}
                        </h2>
                    </div>
                </div>
            </div>
            <!-- Total Distributed Card -->
            <div class="col">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body p-4">
                        <h6 class="text-muted text-uppercase fw-bold small mb-2">Aggregated Total Distributed</h6>
                        <h2 class="fw-bolder text-success mb-0">₹{{ number_format((float) $totals->total_distributed, 2) }}
                        </h2>
                    </div>
                </div>
            </div>
            <!-- Remaining Balance Card -->
            <div class="col">
                @php 
                                    $rem = (float) $totals->remaining;
                    $remColor = $rem < 0 ? 'text-danger' : 'text-info';
                    $borderColor = $rem < 0 ? '#dc3545' : '#0dcaf0';
                @endphp
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid {{ $borderColor }} !important;">
                    <div class="card-body p-4">
                        <h6 class="text-muted text-uppercase fw-bold small mb-2">Real-time Aggregate Balance</h6>
                        <h2 class="fw-bolder {{ $remColor }} mb-0">₹{{ number_format($rem, 2) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Explicit Transactions DataTable (FULL WIDTH) -->

             <div class="col-12">
                <div class="card border-0 shadow-sm">                  
                    <div class="border-bottom card-body py-3 px-4 bg-light-subtle">
                        <div class="row g-3 align-items-end">
                            <!-- Date Filtering -->

                    <div class="col-md-6">
                              
                                <div class="input-group input-group-sm">
                                    <input type="text" id="from_date" class="form-control datepicker filter-trigger" placeholder="From Date">
                                    <span class="input-group-text bg-white px-2 border-start-0 border-end-0 text-muted small">to</span>
                                    <input type="text" id="to_date" class="form-control datepicker filter-trigger" placeholder="To Date">
                                </div>
                            </div>


                           

                            <!-- Actions -->
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary border-dashed px-3" id="reset-filters">
                                    <i class="fa fa-undo me-1 small"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover border-top w-100 align-middle" id="dataTable">
                                <thead class="bg-light small">
                                    <tr>
                                        <th>S.NO</th>
                                        <th>Source</th>
                                        <th>Sanction date</th>
                                        <th>Period</th>
                                        <th class="text-end">Expected amount</th>
                                        <th class="text-center">TDS%</th>
                                        <th class="text-end">Net payable amount</th>
                                        <th class="text-center">Actions</th>
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
     @section('js')
<script>
        window.csrfToken = "{{ csrf_token() }}";
        var selectedRows = {};
        dataTableInit({
            id: "#dataTable",
            showExcelExport: true,
            showCustomExportOption: true,
            order: {
                column: 1,
                direction: "asc"
            },
            url: "{{ url('web/fund-distributions/api/drill-list/' . $fy . '/' . $majorId . '/' . $subId) }}",
            columns: [{
                    "orderable": false,
                    "render": function(data, type, full, meta) {
                        return serialNumber("#dataTable", meta.row);
                    }
                },
                {
                    "data": "source_type",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let labelClass = row.source_type === 'MANUAL' ? 'bg-primary' : 'bg-warning text-dark';
                        return `<span class="badge rounded-pill ${labelClass}">${row.source_type}</span>`;
                    }
                },
                {
                    "data": "sanction_order_date",
                    "orderable": true,
                    "render": function (data, type, row) {
                        if (!row.sanction_order_date) return 'N/A';
                        let d = new Date(row.sanction_order_date);
                        return d.toLocaleDateString('en-GB');
                    }
                },
                {
                    "data": "duration_name",
                    "orderable": true,
                    "render": function (data, type, row) {
                        let sub = row.sub_duration_name ? `<br><small class='text-muted'>${row.sub_duration_name}</small>` : '';
                        return `<span class='fw-bold'>${row.duration_name || 'N/A'}</span>${sub}`;
                    }
                },
                {
                    "data": "distribution_amount",
                    "orderable": true,
                    "className": "text-end fw-bold",
                    "render": function (data, type, row) {
                        return Number(row.distribution_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": "tds_percentage",
                    "orderable": true,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        return `<span class='text-secondary'>${row.tds_percentage}%</span>`;
                    }
                },
                {
                    "data": "net_payable_amount",
                    "orderable": true,
                    "className": "text-end fw-bold ",
                    "render": function (data, type, row) {
                        return Number(row.net_payable_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-center",
                    "render": function (data, type, row) {
                        let viewUrl = `{{url('web/fund-distributions')}}/${row.id}/show`;
                        let editUrl = `{{url('web/fund-distributions')}}/${row.id}/edit`;

                        return `
                            <div class="btn-group btn-group-sm">
                                <a href="${viewUrl}" class="btn btn-light border btn-sm" title="View Ledger"><i class="fa fa-eye text-muted"></i></a>
                                <a href="${editUrl}" class="btn btn-light border btn-sm" title="Modify Record"><i class="fa fa-edit text-primary"></i></a>
                            </div>
                        `;
                    }
                },
            ],
            filters: ["financial_year", "major_component_id", "sub_component_id"]
        });



        function getStatusLabel(status) {
            switch (status) {
                case 1:
                    return `<span class="badge bg-primary">Pending</span>`;
                    break;
                case 2:
                    return `<span class="badge bg-success">Approved</span>`;
                    break;
                case 3:
                    return `<span class="badge bg-danger">Rejected</span>`;
                case 4:
                    return `<span class="badge bg-warning">Reverted</span>`;
                    break;
                default:
                    return `<span class="badge bg-secondary">N/A</span>`;
            }
        }
    </script>
   
        <!-- <script src="{{ asset('assets/js/allocation/api.js') }}"></script>
        <script src="{{ asset('assets/js/allocation/distribution.js') }}"></script>
        <script>
            initDistributionDetails({
                drillDataUrl: "{{ url('web/fund-distributions/api/drill-list/' . $fy . '/' . $majorId . '/' . $subId) }}",
                baseUrl: "{{ url('web/fund-distributions') }}"
            });
        </script> -->
    @endsection
@endsection
