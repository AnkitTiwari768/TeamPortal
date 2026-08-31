@extends('components.admin.layout')
@section('page-content')
    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card container-main-card">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('workshop.edit_admin_workshop') }}</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                   	<li class="breadcrumb-item"><a href="{{url('dashboard')}}">{{ __('workshop.dashboard') }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('workshop.edit_admin_workshop') }}</li>
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
                        <form id="formId">
                            <input type="hidden" name="id" value="{{ $row->id }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.financial_year') }}</label>
                                        <select name="financial_year" id="financial_year" class="form-select">
                                            <option value="">{{ __('workshop.select_fy') }}</option>
                                            <option value="2024-2025" {{ ($row->financial_year ?? '') == '2024-2025' ? 'selected' : '' }}>2024-2025</option>
                                            <option value="2025-2026" {{ ($row->financial_year ?? '') == '2025-2026' ? 'selected' : '' }}>2025-2026</option>
                                            <option value="2026-2027" {{ ($row->financial_year ?? '') == '2026-2027' ? 'selected' : '' }}>2026-2027</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.duration') }}</label>
                                        <select name="duration" id="duration" class="form-select">
                                            <option value="">{{ __('workshop.select') }}</option>
                                            <option value="yearly" {{ ($row->duration ?? '') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                            <option value="half_yearly" {{ ($row->duration ?? '') == 'half_yearly' ? 'selected' : '' }}>Half Yearly</option>
                                            <option value="quarterly" {{ ($row->duration ?? '') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="required form-label">{{ __('workshop.workshop_title') }}</label>
                                        <input type="text" class="form-control" name="title" id="title" value="{{ $row->title ?? '' }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('workshop.organizer_name') }}</label>
                                        <input type="text" class="form-control" name="organiser_name" id="organiser_name" value="{{ $row->organiser_name ?? '' }}">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">{{ __('workshop.venue_address') }}</label>
                                        <input type="text" class="form-control" name="venue_address" id="venue_address" value="{{ $row->venue_address ?? '' }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12 d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">{{ __('workshop.event_schedule') }}</h5>
                                    <button type="button" class="btn btn-sm btn-primary" id="addMoreBtn">
                                        <i class="bi bi-plus"></i> {{ __('workshop.add_more') }}
                                    </button>
                                </div>
                            </div>

                            <div id="scheduleContainer" class="mt-3">
                                @if(!empty($row->schedules))
                                    @foreach($row->schedules as $index => $schedule)
                                    <div class="row schedule-row border p-3 mb-2 rounded bg-light" id="schedule_{{ $index }}">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="required form-label">Start Date & Time</label>
                                                <input type="datetime-local" name="schedules[{{ $index }}][start_time]" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime($schedule->start_time)) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="required form-label">End Date & Time</label>
                                                <input type="datetime-local" name="schedules[{{ $index }}][end_time]" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime($schedule->end_time)) }}">
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="row schedule-row border p-3 mb-2 rounded bg-light" id="schedule_0">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="required form-label">Start Date & Time</label>
                                                <input type="datetime-local" name="schedules[0][start_time]" class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="required form-label">End Date & Time</label>
                                                <input type="datetime-local" name="schedules[0][end_time]" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="form-action mt-3 mb-3">
                                @include('components.admin.buttons.submit-button')
                                @include('components.admin.buttons.cancel-button')
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
