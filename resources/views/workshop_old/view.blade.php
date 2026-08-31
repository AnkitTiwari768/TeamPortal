@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card container-main-card">

                <div class="card-header d-flex">
                    <div class="heading">
                        <h1>{{ __('workshop.distribution_analysis') }}</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('dist.index') }}" class="text-decoration-none">{{ __('workshop.distributions') }}</a></li>
                                <li class="breadcrumb-item active">{{ __('workshop.distribution_analysis') }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="action-header ms-auto">
                        <div class="btn-group drop-btn">
                            <a href="{{ route('dist.index') }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-2"></i> {{ __('workshop.back_to_list') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body pt-2">

                    {{-- Context Banner --}}
                    <div class="d-flex align-items-center gap-2 mb-4 px-1">
                        <i class="fa fa-filter text-muted"></i>
                        <span class="text-muted small fw-semibold text-uppercase">{{ __('workshop.filtering_parameters') }}</span>
                        <span class="badge bg-info fw-normal">{{ $fy }}</span>
                        <span class="badge bg-primary fw-normal">{{ $majorName }}</span>
                        @if($subName)
                            <span class="badge bg-warning fw-normal">{{ $subName }}</span>
                        @endif
                    </div>

                    {{-- Summary Cards --}}
                    @php
                        $rem = (float) $totals->remaining;
                        $remIconBg  = $rem < 0 ? '#fee2e2' : '#fef3c7';
                        $remIconClr = $rem < 0 ? '#dc2626' : '#f59e0b';
                        $remValClr  = $rem < 0 ? 'text-danger' : 'text-warning';
                    @endphp
                    <div class="row g-4 mb-4">
                        {{-- Total Allocated --}}
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="card h-100">
                                <div class="card-body p-3 text-start">
                                    <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                                        <div class="fund-icon" style="width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;background-color:#ecfdf5;color:#10b981;">
                                            <i class="bi bi-wallet2"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-1 fw-semibold text-uppercase fs-7" style="font-size:12px;">{{ __('workshop.total_cumulative_allocation') }}</p>
                                    <h3 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">₹{{ number_format((float) $totals->total_allocated, 2) }}</h3>
                                </div>
                            </div>
                        </div>

                        {{-- Total Distributed --}}
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="card h-100">
                                <div class="card-body p-3 text-start">
                                    <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                                        <div class="fund-icon" style="width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;background-color:#eff6ff;color:#3b82f6;">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-1 fw-semibold text-uppercase fs-7" style="font-size:12px;">{{ __('workshop.aggregated_total_distributed') }}</p>
                                    <h3 class="fw-bold text-dark mb-0" style="font-size:1.1rem;">₹{{ number_format((float) $totals->total_distributed, 2) }}</h3>
                                </div>
                            </div>
                        </div>

                        {{-- Remaining Balance --}}
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="card h-100">
                                <div class="card-body p-3 text-start">
                                    <div class="d-flex align-items-center justify-content-between mb-2 w-100">
                                        <div class="fund-icon" style="width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;background-color:{{ $remIconBg }};color:{{ $remIconClr }};">
                                            <i class="bi bi-cash-stack"></i>
                                        </div>
                                    </div>
                                    <p class="text-muted mb-1 fw-semibold text-uppercase fs-7" style="font-size:12px;">{{ __('workshop.real_time_aggregate_balance') }}</p>
                                    <h3 class="fw-bold mb-0 {{ $remValClr }}" style="font-size:1.1rem;">₹{{ number_format($rem, 2) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Transactions Table --}}
                    <div class="card border">
                        <div class="card-body py-3 px-4 border-bottom bg-light-subtle">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="from_date" class="form-control datepicker filter-trigger" placeholder="{{ __('workshop.from_date') }}">
                                        <span class="input-group-text bg-white px-2 border-start-0 border-end-0 text-muted small">to</span>
                                        <input type="text" id="to_date" class="form-control datepicker filter-trigger" placeholder="{{ __('workshop.to_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="reset-filters">
                                        <i class="fa fa-undo me-1 small"></i> {{ __('workshop.reset') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-themed table-striped table-bordered w-100 align-middle" id="dataTable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('workshop.s_no') }}</th>
                                            <th>{{ __('workshop.source') }}</th>
                                            <th>{{ __('workshop.sanction_date') }}</th>
                                            <th>{{ __('workshop.period') }}</th>
                                            <th class="text-end">{{ __('workshop.expected_amount') }}</th>
                                            <th class="text-center">{{ __('workshop.tds_pct') }}</th>
                                            <th class="text-end">{{ __('workshop.net_payable_amount') }}</th>
                                            <th class="text-center">{{ __('workshop.action') }}</th>
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
</div>

@section('js')
<script>
    window.csrfToken = "{{ csrf_token() }}";
    dataTableInit({
        id: "#dataTable",
        showExcelExport: true,
        showCustomExportOption: true,
        order: { column: 1, direction: "asc" },
        url: "{{ url('web/fund-distributions/api/drill-list/' . $fy . '/' . $majorId . '/' . $subId) }}",
        columns: [
            {
                "orderable": false,
                "render": function(data, type, full, meta) {
                    return serialNumber("#dataTable", meta.row);
                }
            },
            {
                "data": "source_type",
                "orderable": true,
                "render": function(data, type, row) {
                    let labelClass = row.source_type === 'MANUAL' ? 'bg-primary' : 'bg-warning text-dark';
                    return `<span class="badge rounded-pill ${labelClass}">${row.source_type}</span>`;
                }
            },
            {
                "data": "sanction_order_date",
                "orderable": true,
                "render": function(data, type, row) {
                    if (!row.sanction_order_date) return 'N/A';
                    let d = new Date(row.sanction_order_date);
                    return d.toLocaleDateString('en-GB');
                }
            },
            {
                "data": "duration_name",
                "orderable": true,
                "render": function(data, type, row) {
                    let sub = row.sub_duration_name ? `<br><small class='text-muted'>${row.sub_duration_name}</small>` : '';
                    return `<span class='fw-bold'>${row.duration_name || 'N/A'}</span>${sub}`;
                }
            },
            {
                "data": "distribution_amount",
                "orderable": true,
                "className": "text-end fw-bold",
                "render": function(data, type, row) {
                    return Number(row.distribution_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                }
            },
            {
                "data": "tds_percentage",
                "orderable": true,
                "className": "text-center",
                "render": function(data, type, row) {
                    return `<span class='text-secondary'>${row.tds_percentage}%</span>`;
                }
            },
            {
                "data": "net_payable_amount",
                "orderable": true,
                "className": "text-end fw-bold",
                "render": function(data, type, row) {
                    return Number(row.net_payable_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                }
            },
            {
                "data": null,
                "orderable": false,
                "className": "text-center",
                "render": function(data, type, row) {
                    let viewUrl = `{{ url('web/fund-distributions') }}/${row.id}/show`;
                    let editUrl = `{{ url('web/fund-distributions') }}/${row.id}/edit`;
                    return `
                        <div class="btn-group btn-group-sm">
                            <a href="${viewUrl}" class="btn btn-sm btn-info" title="View Ledger"><i class="fa fa-eye"></i></a>
                            <a href="${editUrl}" class="btn btn-sm btn-primary" title="Modify Record"><i class="fa fa-edit"></i></a>
                        </div>`;
                }
            }
        ],
        filters: ["financial_year", "major_component_id", "sub_component_id"]
    });
</script>
@endsection
@endsection
