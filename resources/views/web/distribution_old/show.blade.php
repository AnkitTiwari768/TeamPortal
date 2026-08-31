@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Transaction Summary</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dist.index') }}" class="text-decoration-none">
                            Distributions
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        Transaction Summary
                    </li>
                </ol>
            </nav>
        </div>

        <a href="{{ url()->previous() }}" class="btn btn-primary">
            <i class="fa fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Main Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fa fa-file-invoice-dollar me-2"></i>
                Transaction Details
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <!-- Left Side -->
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="35%">Financial Year</th>
                                <td>{{ $row->financial_year }}</td>
                            </tr>

                            <tr>
                                <th>Major Component</th>
                                <td>{{ $row->major_component_name }}</td>
                            </tr>

                            <tr>
                                <th>Sub Component</th>
                                <td>{{ $row->sub_component_name }}</td>
                            </tr>

                            <tr>
                                <th>Duration</th>
                                <td>
                                    {{ $row->duration_name }}
                                    @if($row->sub_duration_name !== 'N/A')
                                        ({{ $row->sub_duration_name }})
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right Side -->
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th width="35%">Source Type</th>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $row->source_type }}
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Sanction Order No.</th>
                                <td>
                                    {{ $row->sanction_order_number ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Sanction Date</th>
                                <td>
                                    {{ $row->sanction_order_date_formatted ?? 'N/A' }}
                                </td>
                            </tr>

                            <tr>
                                <th>Document</th>
                                <td>
                                    @if($row->upload_document)
                                        <a href="{{ route('dist.document', $row->id) }}"
                                            target="_blank"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="fa fa-file-pdf me-1"></i>
                                            View Document
                                        </a>
                                    @else
                                        <span class="text-muted">
                                            Not Available
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>

            </div>

            <hr>

            <!-- Amount Section -->
            <div class="row text-center">

                <div class="col-md-4 mb-3">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="text-muted">Distribution Amount</h6>
                            <h4 class="text-primary fw-bold">
                                ₹{{ number_format((float) $row->distribution_amount, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="text-muted">
                                TDS ({{ (float) $row->tds_percentage }}%)
                            </h6>
                            <h4 class="text-danger fw-bold">
                                ₹{{ number_format((float) $row->tds_amount, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="text-muted">Net Payable Amount</h6>
                            <h4 class="text-success fw-bold">
                                ₹{{ number_format((float) $row->net_payable_amount, 2) }}
                            </h4>
                        </div>
                    </div>
                </div>

            </div>


        </div>

    </div>

</div>
@endsection