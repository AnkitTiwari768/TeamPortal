@extends('components.admin.layout')

@section('page-content')
<div class="container-fluid px-4 py-4">
    <!-- Breadcrumb and Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800 fw-bold">{{ __('workshop.proposed_workshop_details') }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item text-secondary">{{ __('workshop.proposed_workshop_list') }}</li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('workshop.view_proposed_workshop') }}</li>
                </ol>
            </nav>
        </div>
        <div>
            @include('components.admin.buttons.back-button')
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details Card -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow-sm border-0 mb-4 h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="m-0 font-weight-bold text-primary fw-semibold d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i> {{ __('workshop.event_title') }} Details
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Title & Description -->
                    <div class="mb-4">
                        <h4 class="text-dark fw-bold mb-2">{{ $event->event_title ?? '-' }}</h4>
                        <div class="p-3 bg-light rounded text-muted" style="white-space: pre-line; line-height: 1.6;">
                            {{ $event->event_description ?? 'No description provided.' }}
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="p-2 border-start border-primary border-3 bg-light rounded-end">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">{{ __('workshop.organiser_name') }}</small>
                                <span class="text-dark fw-semibold">{{ $org_name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-2 border-start border-3 bg-light rounded-end" style="border-color: dodgerblue !important; word-wrap: break-word; overflow-wrap: break-word;">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">{{ __('workshop.event_for') }}</small>
                                <div class="d-flex flex-wrap gap-1 mt-1" style="max-width: 100%;">
                                    @if(!empty($event_for))
                                        @foreach(explode(',', $event_for) as $role)
                                            <span class="badge rounded-pill px-2.5 py-1.5 fs-7" style="background-color: dodgerblue; color: #fff; white-space: normal; word-break: break-word; text-align: left;">
                                                {{ ucfirst(trim($role)) }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-dark fw-semibold">-</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-2 border-start border-success border-3 bg-light rounded-end">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">{{ __('workshop.branch_office') }}</small>
                                <span class="text-dark fw-semibold">{{ !empty($event->branch_offices_id) ? nsic_branch_offices($event->branch_offices_id) : '-' }}</span>
                            </div>
                        </div>

                        @if(!empty($event->remark))
                        <div class="col-sm-6">
                            <div class="p-2 border-start border-warning border-3 bg-light rounded-end">
                                <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 11px;">{{ __('workshop.remark') }}</small>
                                <span class="text-dark">{{ ucfirst($event->remark) }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Images Section -->
                    @if(!empty($uploadedImage) && count($uploadedImage) > 0)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-images me-2 text-primary"></i> Uploaded Event Images</h6>
                            <div class="row g-2">
                                @foreach($uploadedImage as $img)
                                    <div class="col-sm-3 col-6">
                                        <div class="card h-100 border shadow-sm overflow-hidden">
                                            <a href="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}" target="_blank">
                                                <img src="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}"
                                                     class="img-fluid w-100"
                                                     style="height: 120px; object-fit: cover; transition: transform 0.3s;"
                                                     onmouseover="this.style.transform='scale(1.05)'"
                                                     onmouseout="this.style.transform='scale(1)'">
                                            </a>
                                            <div class="card-footer p-2 bg-light border-0 text-center">
                                                <a href="{{ asset('storage/app/uploads/event_image/'.$img->file_system_name) }}" download class="btn btn-sm btn-outline-primary w-100 py-1" style="font-size: 11px;">
                                                    <i class="bi bi-download me-1"></i> Download
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Side Panel: Location & Files -->
        <div class="col-xl-4 col-lg-5">
            <!-- Location Details Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="m-0 font-weight-bold text-primary fw-semibold d-flex align-items-center">
                        <i class="bi bi-geo-alt me-2"></i> {{ __('workshop.venue_address') }}
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 pb-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark">{{ __('workshop.state') }}</div>
                                <span class="text-muted">{{ !empty($event->state_id) ? state_list($event->state_id) : '-' }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 py-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark">{{ __('workshop.district') }}</div>
                                <span class="text-muted">{{ !empty($event->district_id) ? district_list($event->district_id) : '-' }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 py-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark">{{ __('workshop.sub_district') }}</div>
                                <span class="text-muted">{{ !empty($event->sub_district_id) ? sub_district_list($event->sub_district_id) : '-' }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 py-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark">{{ __('workshop.venue_address') }}</div>
                                <span class="text-muted" style="word-break: break-word;">{{ $event->venue_address ?? '-' }}</span>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-start px-0 border-0 py-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold text-dark">{{ __('workshop.pincode') }}</div>
                                <span class="text-muted">{{ $event->pincode ?? '-' }}</span>
                            </div>
                        </li>
                    </ul>

                    <!-- Coordinates Map Link -->
                    @if(!empty($event->latitude) && !empty($event->longitude))
                        <div class="mt-3 p-3 bg-light rounded d-flex align-items-center justify-content-between">
                            <div>
                                <small class="text-muted d-block" style="font-size: 11px;">Coordinates</small>
                                <span class="text-dark small fw-semibold">{{ $event->latitude }}, {{ $event->longitude }}</span>
                            </div>
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $event->latitude }},{{ $event->longitude }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-map me-1"></i> View Map
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Attachments Card -->
            @if(!empty($event->attachment))
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-transparent border-bottom py-3">
                        <h5 class="m-0 font-weight-bold text-primary fw-semibold d-flex align-items-center">
                            <i class="bi bi-paperclip me-2"></i> {{ __('workshop.attachments') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center p-3 border rounded">
                            <div class="bg-light p-2 rounded text-primary me-3">
                                <i class="bi bi-file-earmark-pdf fs-3"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="text-dark fw-bold mb-1 text-truncate">Workshop Document</h6>
                                <p class="text-muted small mb-0">Attached PDF/Document</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $event->attachment) }}" target="_blank" class="btn btn-primary w-100 mt-3 d-flex align-items-center justify-content-center">
                            <i class="bi bi-eye me-2"></i> {{ __('workshop.view_attachment') }}
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Schedules Table Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-bottom py-3">
                    <h5 class="m-0 font-weight-bold text-primary fw-semibold d-flex align-items-center">
                        <i class="bi bi-calendar-event me-2"></i> {{ __('workshop.event_schedules') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    @php
                        $schedules = $event->schedules;
                        if (is_string($schedules)) {
                            $schedules = json_decode($schedules, true);
                        }
                        if (is_string($schedules)) {
                            $schedules = json_decode($schedules, true);
                        }
                        $schedules = is_array($schedules) ? $schedules : [];
                    @endphp

                    @if(!empty($schedules))
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase fs-7" style="font-size: 12px; letter-spacing: 0.5px;">
                                    <tr>
                                        <th class="px-4 py-3" style="width: 80px;">#</th>
                                        <th class="py-3">Start Date</th>
                                        <th class="py-3">End Date</th>
                                        <th class="py-3">Start Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedules as $index => $schedule)
                                        <tr>
                                            <td class="px-4 py-3 text-muted fw-bold">{{ $index + 1 }}</td>
                                            <td class="py-3 text-dark fw-semibold">
                                                <i class="bi bi-calendar-check me-2 text-success"></i>{{ $schedule['start_date'] ?? '-' }}
                                            </td>
                                            <td class="py-3 text-dark fw-semibold">
                                                <i class="bi bi-calendar-x me-2 text-danger"></i>{{ $schedule['end_date'] ?? '-' }}
                                            </td>
                                            <td class="py-3 text-secondary">
                                                <i class="bi bi-clock me-2 text-muted"></i>{{ $schedule['start_time'] ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center">
                            <i class="bi bi-calendar2-x fs-1 text-muted d-block mb-2"></i>
                            <span class="text-muted">{{ __('workshop.no_schedules_found') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
