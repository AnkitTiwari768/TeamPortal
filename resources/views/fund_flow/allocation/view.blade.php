@extends('components.admin.layout')

@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>Allocation Details</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('allocation.index') }}" class="text-decoration-none">All Allocations</a></li>
                                    <li class="breadcrumb-item active">Allocation Details</li>
                                </ol>
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                @include('components.admin.buttons.back-button')
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-1">
                        <div class="row g-3">
                            @php
                                $basic_details = [
                                    'financial_year'        => 'Financial Year',
                                    'duration_label'        => 'Duration',
                                    'sub_duration_label'    => 'Sub Duration',
                                    'sanction_order_number' => 'Sanction Order No',
                                    'sanction_order_date'   => 'Sanction Order Date',
                                    'remarks'               => 'Remarks',
                                ];
                            @endphp

                            <div class="col-12">
                                <h6 class="fw-medium text-dark mb-2 p-2" style="background:#efefef;">Basic Details</h6>
                            </div>

                            @foreach($basic_details as $key => $label)
                                @php
                                    $value = $row[$key] ?? null;
                                    if ($value && in_array($key, ['sanction_order_date']) && strlen($value) >= 10) {
                                        $value = date('d-m-Y', strtotime($value));
                                    }
                                @endphp
                                @if(!empty($value))
                                    <div class="col-md-4">
                                        <label><strong>{{ $label }}:</strong></label>
                                        {{ $value }}
                                    </div>
                                @endif
                            @endforeach

                            @if(!empty($row['document_path']))
                                <div class="col-md-4">
                                    <label><strong>Document:</strong></label><br>
                                    <a href="{{ route('allocation.document', $row['id']) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fa fa-file-pdf"></i> View Document
                                    </a>
                                </div>
                            @endif

                            <div class="col-12 mt-3">
                                <h6 class="fw-medium text-dark mb-2 p-2" style="background:#efefef;">Component Details</h6>
                            </div>

                            <div class="col-12">
                                @if(!empty($lines) && count($lines) > 0)
                                    <table class="table table-themed table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>S.NO.</th>
                                                <th>Major Component</th>
                                                <th>Sub Component</th>
                                                <th>Amount Rs.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($lines as $index => $line)
                                                @include('web.allocation.partials.allocation-row', [
                                                    'line'  => $line,
                                                    'index' => $index + 1,
                                                ])
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total Amount Allocated:</strong></td>
                                                <td><strong>₹ {{ number_format(collect($lines)->sum(fn($l) => $l->amount), 2) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                @else
                                    <p class="text-muted">No allocation lines found.</p>
                                @endif
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
