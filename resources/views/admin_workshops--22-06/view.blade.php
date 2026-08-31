@extends('components.admin.layout')

@section('page-content')

@php
    $workshopDetails = [
        [
            'label' => 'Workshop Title',
            'value' => $row->title ?? 'N/A',
        ],
        [
            'label' => 'Financial Year',
            'value' => $row->financial_year ?? 'N/A',
        ],
        [
            'label' => 'Duration',
            'value' => $row->duration_name ?? 'N/A',
        ],
        [
            'label' => 'Workshop Mode',
            'value' => $row->workshop_mode ?? 'N/A',
        ],
        [
            'label' => 'Sub Duration',
            'value' => $row->sub_duration_name ?? 'N/A',
        ],
        [
            'label' => 'Workshop Date',
            'value' => !empty($row->workshop_date)
                        ? date('d-m-Y', strtotime($row->workshop_date))
                        : 'N/A',
        ],
        [
            'label' => 'State',
            'value' => $row->state_name ?? 'N/A',
        ],
        [
            'label' => 'District',
            'value' => $row->district_name ?? 'N/A',
        ],
        [
            'label' => 'Sub District',
            'value' => !empty($row->sub_district_id) ? sub_district_list($row->sub_district_id) : '' ,
        ],
        [
            'label' => 'Venue',
            'value' => $row->venue ?? 'N/A',
        ],
        [
            'label' => 'Organizer Name',
            'value' => $row->organizer_name_text ?? 'N/A',
        ],
        [
            'label' => 'Branch Office',
            'value' => !empty($row->branch_office_id) ? nsic_branch_offices($row->branch_office_id) : '',
        ],
        [
            'label' => 'Conducted By',
            'value' => $row->conducted_by_name ?? 'N/A',
        ],

        [
            'label' => 'Conducted By Other',
            'value' => $row->conduct_by_other ?? 'N/A',
        ],
        [
            'label' => 'Target Audience',
            'value' => $row->target_audience_name ?? 'N/A',
        ],
        [
            'label' => 'Participants',
            'value' => $row->number_of_participants ?? 'N/A',
        ],
        [
            'label' => 'Expense Amount',
            'value' => '₹ ' . number_format($row->expense_amount ?? 0, 2),
        ],
        [
            'label' => '5 % NSIC Fee',
            'value' => '₹ ' . number_format($row->nsic_fees ?? 0, 2),
        ],
        [
            'label' => 'TDS Applicable',
            'value' => ($row->tds_applicable ?? 0) == 1
                        ? '<span class="badge bg-success">Yes</span>'
                        : '<span class="badge bg-secondary">No</span>',
            'html' => true
        ],
        [
            'label' => 'TDS Percentage',
            'value' => ($row->tds_percentage ?? 0) . ' %',
        ],
        [
            'label' => 'Net Amount',
            'value' => '₹ ' . number_format($row->net_amount ?? 0, 2),
        ],
        [
            'label' => 'Sanction Order No',
            'value' => $row->sanction_order_no ?? 'N/A',
        ],
        [
            'label' => 'Sanction Order Date',
            'value' => !empty($row->sanction_order_date)
                        ? date('d-m-Y', strtotime($row->sanction_order_date))
                        : 'N/A',
        ],
        [
            'label' => 'Remarks',
            'value' => $row->remarks ?? 'N/A',
        ],
        [
            'label' => 'Workshop Description',
            'value' => $row->workshop_description ?? 'N/A',
            'full' => true
        ],
    ];
@endphp

<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                {{-- Header --}}
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0 fw-bold">
                        {{ __('View Admin Workshop') }}
                    </h4>
                    @include('components.admin.buttons.back-button')
                </div>

                {{-- Body --}}
                <div class="card-body">
                    {{-- Workshop Details --}}
                    <div class="mb-4">
                        <h5 class="fw-bold border-bottom pb-2 mb-4">
                            Workshop Details
                        </h5>
                        <div class="row">
                            @foreach($workshopDetails as $detail)
                                <div class="{{ isset($detail['full']) ? 'col-md-12' : 'col-md-4' }} mb-4">
                                    <div class="card h-100 border shadow-sm">
                                        <div class="card-body">
                                            <label class="text-muted small fw-bold d-block mb-2">
                                                {{ $detail['label'] }}
                                            </label>
                                            <div class="fw-semibold text-dark">
                                                @if(isset($detail['html']) && $detail['html'] == true)
                                                    {!! $detail['value'] !!}
                                                @else
                                                    {{ $detail['value'] }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    {{-- Supporting Documents --}}
                    <div>
                        <h5 class="fw-bold border-bottom pb-2 mb-4">
                            Supporting Documents
                        </h5>
                        <div class="row">
                            @forelse($uploadedFiles as $file)
                                <div class="col-md-4 mb-4">
                                    <div class="card border shadow-sm h-100">
                                        <div class="card-body d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-1 fw-semibold">
                                                        {{ $file->file_name }}
                                                    </h6>
                                                    <small class="text-muted">
                                                        Supporting Document
                                                    </small>
                                                </div>
                                            </div>
                                            <div>
                                                <a href="{{ asset('storage/app/' . $file->file_path . '/' . $file->file_system_name) }}"
                                                   download
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty

                                <div class="col-6">
                                    <div class="alert alert-warning mb-0">
                                        No supporting documents available.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                       </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
