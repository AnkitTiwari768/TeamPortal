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
                            <h1>{{ __('fund_flow.carry_forward_details') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a>
                                    </li>
                                    <li class="breadcrumb-item"><a href="{{ url('carry-forward-mapping') }}"
                                            style="color: #64748b; text-decoration: none;">{{ __('fund_flow.carry_forward_mapping') }}</a>
                                    </li>
                                    <li class="breadcrumb-item active" aria-current="page"
                                        style="color: #0f172a; font-weight: 500;">
                                        {{ __('fund_flow.carry_forward_details') }}
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
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.financial_year') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['financial_year'] ?? '-' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.from_duration') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['from_sub_duration_name'] ?? $row['from_duration_name'] ?? '-' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small d-block">{{ __('fund_flow.to_duration') }}</label>
                                    <span class="fw-semibold text-dark">{{ $row['to_sub_duration_name'] ?? $row['to_duration_name'] ?? '-' }}</span>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small d-block">Status</label>
                                    <span class="badge rounded-pill" style="background-color: #e6f6ec; color: #15803d; border: 1px solid #bbf7d0; font-weight: 500; font-size: 0.75rem; padding: 0.35em 0.75em;">{{ $row['status'] ?? 'COMPLETED' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- ── Section 2: Sub Component Closing Balances Table ── --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-list-ol"></i> Mapped Carry Forward Balances
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-themed table-striped table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{ __('fund_flow.major_component') }}</th>
                                            <th>{{ __('fund_flow.sub_component') }}</th>
                                            <th class="text-end" style="width: 200px;">Opening Balance</th>
                                            <th class="text-end" style="width: 250px;">{{ __('fund_flow.carry_forward_amount') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($lines as $index => $line)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    <span class="badge" style="background-color: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-weight: 600; padding: 0.35em 0.65em; border-radius: 6px;">{{ $line['major_component_name'] }}</span>
                                                </td>
                                                <td class="text-secondary">{{ $line['sub_component_name'] }}</td>
                                                <td class="text-end fw-semibold">₹ {{ format_indian_currency($line['opening_balance']) }}</td>
                                                <td class="text-end fw-bold text-dark">₹ {{ format_indian_currency($line['carried_forward_amount']) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No mapped details found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-light">
                                            <td colspan="4" class="text-end fw-bold">{{ __('fund_flow.total_amount') }}</td>
                                            <td class="text-end fw-bold text-primary" style="font-size: 1.1rem;">
                                                ₹ {{ format_indian_currency($row['total_amount'] ?? collect($lines)->sum('carried_forward_amount')) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        @if(!empty($row['remarks']))
                            <div class="detail-card">
                                <h5 class="detail-section-title">
                                    <i class="fa fa-comment"></i> Remarks
                                </h5>
                                <div class="text-secondary" style="font-size: 0.9rem; line-height: 1.5;">
                                    {{ $row['remarks'] }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
