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

        .metric-outline-card {
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .metric-outline-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
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
                            <h1 class="fw-bold mb-1">Transaction Summary</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.home') }}</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('dist.index') }}" style="color: #64748b; text-decoration: none;">{{ __('fund_flow.fund_distribution') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page" style="color: #0f172a; font-weight: 500;">Transaction Summary</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left me-1"></i> Back
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Left Side Attributes -->
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <h5 class="detail-section-title">
                                        <i class="fa fa-info-circle"></i> Component & Period Details
                                    </h5>
                                    <table class="table table-striped table-bordered align-middle">
                                        <tbody>
                                            <tr>
                                                <th width="35%">Financial Year</th>
                                                <td class="fw-semibold">{{ $row['financial_year'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Major Component</th>
                                                <td class="text-dark">{{ $row['major_component_name'] }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sub Component</th>
                                                <td class="text-dark">{{ $row['sub_component_name'] ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Period Name</th>
                                                <td class="fw-semibold text-primary">
                                                    {{ $row['duration_name'] }}
                                                    @if(!empty($row['sub_duration_name']) && $row['sub_duration_name'] !== 'N/A')
                                                        ({{ $row['sub_duration_name'] }})
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Right Side Logistics -->
                            <div class="col-md-6">
                                <div class="detail-card">
                                    <h5 class="detail-section-title">
                                        <i class="fa fa-file-text-o"></i> Sanction & Document Details
                                    </h5>
                                    <table class="table table-striped table-bordered align-middle">
                                        <tbody>
                                            <tr>
                                                <th width="35%">Source Type</th>
                                                <td>
                                                    <span class="badge bg-primary px-2.5 py-1">
                                                        {{ $row['source_type'] }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Sanction Order No.</th>
                                                <td class="fw-semibold text-dark">{{ $row['sanction_order_number'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Sanction Date</th>
                                                <td>{{ $row['sanction_order_date_formatted'] ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Document Link</th>
                                                <td>
                                                    @if(!empty($row['upload_document']))
                                                        <a href="{{ route('dist.document', $row['id']) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="fa fa-file-pdf-o me-1"></i> View Document File
                                                        </a>
                                                    @else
                                                        <span class="text-muted italic">Not Available</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Financial Metrices --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-money"></i> Financial Allocation Impact
                            </h5>
                            <div class="row text-center mt-2">
                                <!-- Gross Distribution -->
                                <div class="col-md-4 mb-3">
                                    <div class="card metric-outline-card border-primary bg-light-subtle">
                                        <div class="card-body p-3">
                                            <h6 class="text-muted small text-uppercase fw-bold mb-2">Distribution Amount</h6>
                                            <h3 class="text-primary fw-bold mb-0">
                                                ₹ {{ format_indian_currency($row['distribution_amount']) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- TDS percentage -->
                                <div class="col-md-4 mb-3">
                                    <div class="card metric-outline-card border-danger bg-light-subtle">
                                        <div class="card-body p-3">
                                            <h6 class="text-muted small text-uppercase fw-bold mb-2">
                                                TDS deducted ({{ (float) $row['tds_percentage'] }}%)
                                            </h6>
                                            <h3 class="text-danger fw-bold mb-0">
                                                ₹ {{ format_indian_currency($row['tds_amount']) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>

                                <!-- Net Payable Amount -->
                                <div class="col-md-4 mb-3">
                                    <div class="card metric-outline-card border-success bg-light-subtle">
                                        <div class="card-body p-3">
                                            <h6 class="text-muted small text-uppercase fw-bold mb-2">Net Payable Amount</h6>
                                            <h3 class="text-success fw-bold mb-0">
                                                ₹ {{ format_indian_currency($row['net_payable_amount']) }}
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="detail-card">
                            <h5 class="detail-section-title">
                                <i class="fa fa-comment-o"></i> Remarks / Comments
                            </h5>
                            <p class="text-dark bg-light p-3 rounded border mb-0" style="white-space: pre-wrap;">{{ $row['remarks'] ?? 'No remarks provided.' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
