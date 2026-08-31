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
                    <div class="card-header d-flex align-items-center py-3"
                        style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
                        <div class="heading">
                            <h1>{{ __('fund_flow.fund_allocation_details') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ url('fund-allocations') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.allocation') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page"
                                        style="color: #0f172a; font-weight: 500;">
                                        {{ __('fund_flow.fund_allocation_details') }}
                                    </li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                @include('components.admin.buttons.back-button')
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        {{-- ── Section 1: Period Details ── --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-calendar"></i> Period Details
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.financial_year') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['financial_year'] ?? '-' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.duration') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['duration_name'] ?? $duration_name ?? '-' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.sub_duration') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['sub_duration_name'] ?? $sub_duration_name ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 2: Component Allocations Table ── --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-list-ol"></i> Component Allocations
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-themed table-striped table-bordered mb-0">
                                    @php
                                        $hasDistributedToOther = collect($lines)->sum('distributed_to_other_component') > 0;
                                    @endphp
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>{{ __('fund_flow.major_component') }}</th>
                                            <th>{{ __('fund_flow.sub_component') }}</th>
                                            <th class="text-end" style="width: 250px;">{{ __('fund_flow.amount_rs') }}</th>
                                            @if($hasDistributedToOther)
                                                <th class="text-end" style="width: 250px;">Distributed Amount to Another Component</th>
                                            @endif
                                            <th class="text-end" style="width: 250px;">Remaining Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lines as $index => $line)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $line['major_component_name'] }}</td>
                                                <td>{{ $line['sub_component_name'] }}</td>
                                                <td class="text-end fw-semibold">₹ {{ format_indian_currency($line['amount']) }}</td>
                                                @if($hasDistributedToOther)
                                                    <td class="text-end">₹ {{ format_indian_currency($line['distributed_to_other_component'] ?? 0) }}</td>
                                                @endif
                                                <td class="text-end fw-semibold">₹ {{ format_indian_currency($line['remaining_amount'] ?? 0) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="{{ $hasDistributedToOther ? '6' : '5' }}" class="text-center text-muted">No component allocations found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="3" class="text-end fw-bold">{{ __('fund_flow.total_amount') }}</td>
                                            <td class="text-end fw-bold text-primary" style="font-size: 1.1rem;">
                                                ₹ {{ format_indian_currency(collect($lines)->sum('amount')) }}
                                            </td>
                                            @if($hasDistributedToOther)
                                                <td class="text-end fw-bold">₹ {{ format_indian_currency(collect($lines)->sum('distributed_to_other_component')) }}</td>
                                            @endif
                                            <td class="text-end fw-bold">₹ {{ format_indian_currency(collect($lines)->sum('remaining_amount')) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- ── Section 3: Financial & Sanction Details ── --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-file-text-o"></i> Sanction & Financial Details
                            </h5>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.total_amount_allocated_rs') }}</label>
                                    <span class="fw-bold text-primary" style="font-size: 1.1rem;">
                                        ₹ {{ format_indian_currency($row['total_amount_allocated'] ?? collect($lines)->sum('amount')) }}
                                    </span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">Total Available Amount</label>
                                    <span class="fw-bold text-primary" style="font-size: 1.1rem;">
                                        ₹ {{ format_indian_currency(max(0, $row['total_available_amount'] ?? 0)) }}
                                    </span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.sanction_order_number') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['sanction_order_number'] ?? '-' }}</span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.sanction_order_date') }}</label>
                                    <span class="fw-semibold text-dark">
                                        {{ !empty($row['sanction_order_date']) ? date('d-m-Y', strtotime($row['sanction_order_date'])) : '-' }}
                                    </span>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.uploaded_document') }}</label>
                                     @if(!empty($docUrl) && isset($row['id']))
                                         <a href="{{ route('fund-allocations.download-document', $row['id']) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1">
                                            <i class="fa fa-download"></i> Download Document
                                        </a>
                                    @else
                                        <span class="text-muted">No document uploaded</span>
                                    @endif
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.remarks') }}</label>
                                    <span class="text-dark">{{ $row['remarks'] ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 4: Allocation History ── --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-history"></i> Allocation History
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-themed table-striped table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Date</th>
                                            
                                            <th>Sanction Order No.</th>
                                           
                                            <th>Component</th>
                                            <th class="text-end">Component Allocated Amount (₹)</th>
                                            <th class="text-end">Total Allocated Amount (₹)</th>
                                            <th class="text-end">Total Available Amount (₹)</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($history ?? []) as $index => $entry)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $entry['created_at'] }}</td>
                                               
                                                <td>{{ $entry['sanction_order_number'] ?? '-' }}</td>
                                               
                                                <td>
                                                    @forelse($entry['component_lines'] as $line)
                                                        <div class="small">
                                                            {{ $line['major_component_name'] }} &gt; {{ $line['sub_component_name'] }}
                                                        </div>
                                                    @empty
                                                        <span class="text-muted small">—</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-end">
                                                    @forelse($entry['component_lines'] as $line)
                                                        <div class="small fw-semibold">₹ {{ format_indian_currency($line['amount']) }}</div>
                                                    @empty
                                                        <span class="text-muted small">—</span>
                                                    @endforelse
                                                </td>
                                                <td class="text-end fw-semibold">₹ {{ format_indian_currency($entry['total_amount_allocated_after']) }}</td>
                                                <td class="text-end fw-semibold">₹ {{ format_indian_currency(max(0, $entry['total_available_amount_after'])) }}</td>
                                                <td>{{ $entry['created_by_name'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted">No allocation history found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
