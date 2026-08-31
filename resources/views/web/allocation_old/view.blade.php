@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow m-3">

                {{-- ── Card Header ─────────────────────────────────── --}}
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Allocation Details</h6>
                    <div class="action-header ms-auto">
                        <div class="btn-group drop-btn">
                            
                            @include('components.admin.buttons.back-button')
                        </div>
                    </div>
                </div>

                {{-- ── Card Body ────────────────────────────────────── --}}
                <div class="card-body snp_details">
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

                        <h4>Basic Details</h4>

                        @foreach($basic_details as $key => $label)
                            @php
                                $value = $row[$key] ?? null;
                                // Format date if it looks like Y-m-d
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

                        {{-- Replace the document link section in view.blade.php --}}
                        @if(!empty($row['document_path']))
                            <div class="col-md-4">
                                <label><strong>Document:</strong></label><br>
                                <a href="{{ route('allocation.document', $row['id']) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fa fa-file-pdf"></i> View Document
                                </a>
                            </div>
                        @endif

                        {{-- ── Allocation Lines ─────────────────────── --}}
                        <h4>Component Details</h4>

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
<td><strong>₹ {{ number_format(collect($lines)->sum(fn($l) => $l->amount), 2) }}</strong></td>                                    </tr>
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
@endsection
