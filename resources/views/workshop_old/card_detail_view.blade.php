@extends('components.admin.layout')
<style>
    .event-details-page .main-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        padding: 20px;
        min-height: calc(100vh - 140px);
    }



    .event-details-page .event-details-page .breadcrumb-text {
        font-size: 14px;
        color: #6c757d;
    }

   .event-details-page .event-info-box {
    background: #bbd1e8;
    padding: 18px;
    border-radius: 12px;
    padding-top: 36px;
}
    .event-details-page .info-item {
    display: flex;
    align-items: start;
    gap: 15px;
    flex-direction: row;
}
.event-details-page .icon-box {
    width: 38px !important;
    height: 38px;
    border-radius: 10px;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.info-detail{
    width: 70%;
}

    .event-details-page .info-title {
        font-size: 14px;
        color: #555;
    }

    .event-details-page .info-value {
        font-weight: 600;
    }

    .event-details-page .section-title {
        margin-top: 25px;
        font-weight: 600;
    }

    .event-details-page .expert-card {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        width: 100%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .event-details-page .expert-name {
        color: #c76b28;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .event-details-page .event-details-page .expert-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .event-details-page .side-image {
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        border: 1.5px solid #85bdf8;
        box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
    }
    .event-details-page .side-image img {
        width: 100%;
        border-radius: 6px;
    }

    .event-details-page .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px 30px;
    }

    .event-details-page .item {
        display: flex;
        flex-direction: column;
    }

    .event-details-page .label {
        font-weight: 600;
        font-size: 14px;
        color: #000;
    }

    .event-details-page .value {
        font-size: 14px;
        color: #6c757d;
    }

    .event-details-page .full {
        grid-column: 1 / -1;
    }




    /* TABLE CSS  */
    .event-details-page h3 {
        font-size: 20px;
        padding: 2px 0;
        margin: 0;
        font-weight: 700;
    }

    .event-details-page .breadcrumb-text{
        font-size: 14px;
    }

    .event-details-page .schedule-table {
        background: #fff;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
    }

    .event-details-page.modern-table {
        margin-bottom: 0;
    }

    .event-details-page .modern-table thead {
        background: #f6f7fb;
    }

    .event-details-page .modern-table th {
        font-weight: 600;
        font-size: 14px;
        color: #333;
        padding: 11px;
        border-bottom: 1px solid #e6e6e6;
    }

    .event-details-page .modern-table td {
        font-size: 14px;
        color: #555;
        padding: 11px;
        border-bottom: 1px solid #f0f0f0;
    }

    .event-details-page .modern-table tbody tr:hover {
        background: #fafafa;
        transition: 0.2s;
    }

    .event-details-page .modern-table td:first-child {
        font-weight: 600;
        color: #333;
    }

    .event-details-page .event-shedule.headding {
        border-bottom: 1px solid rgb(217, 217, 217);
        padding-bottom: 9px !important;
    }

    .event-details-page .headding {         
        font-size: 16px !important;
        font-weight: 600 !important;
    }

    .event-details-page .event-detail-card.headding {
        margin-top: 25px;
    }

    .event-details-page .msme-card-new {
    background: #eef8ff;
    border-radius: 14px;
    padding: 16px;
    border: 1px solid #cfe7ff;
}

.event-details-page .event-info-box> .row {
    justify-content: space-between;
    min-height: 72px;
    align-items: start;
}
</style>
@section('page-content')
<div class="container-fluid p-3">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <main class="content card container-main-card p-4">
                <div class="main-container event-details-page">
                    <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('workshop.view_proposed_workshop') }}</h1>
                            <nav aria-label="breadcrumb">
                                <!-- <ol class="breadcrumb">
                                    <li class="breadcrumb-item">{{ __('workshop.home') }}</li>
                                    <li class="breadcrumb-item">{{ __('workshop.event_list') }}</li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('workshop.view_event') }}</li>
                                </ol> -->
                            </nav>
                        </div>
                        <div class="action-header ms-auto">
                            <div class="btn-group drop-btn">
                                <a href="{{ url('proposed-workshop') }}">
                                    <button type="button" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-9">
                            <div class="msme-card-new">
                            <h4 class="mb-2  headding ">
                                {{ $event->event_title }}
                            </h4>
                            <div class="event-info-box mt-2">
                                <div class="row">
                                    <div class="col-md-4 p-0 m-0">
                                        <div class="info-item">
                                        <div class="icon-box"><img src="{{ asset('assets/img-new/calender.svg') }}" class="img-fluid"></div>
                                        <div class="info-detail">
                                            <div class="info-title">{{ __('workshop.date') }}</div>
                                            <div class="info-value">
                                                {{ $scheduleData['start_date'] }} {{ __('workshop.to') }} {{ $scheduleData['end_date'] }}
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5 p-0 m-0">
                                        <div class="info-item">
                                            
                                        <div class="icon-box"><img src="{{ asset('assets/img-new/map.svg') }}" class="img-fluid"></div>
                                        <div class="info-detail">
                                            <div class="info-title">{{ __('workshop.venue_address') }}:</div>
                                            <div class="info-value">
                                                {{ $event->venue_address }}
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 p-0 m-0">
                                        <div class="info-item">
                                        <div class="icon-box"><img src="{{ asset('assets/img-new/duration.svg') }}" class="img-fluid"></div>
                                        <div class="info-detail">
                                            <div class="info-title">{{ __('workshop.duration') }}:</div>
                                            <div class="info-value">
                                                {{ $scheduleData['duration'] }} Days
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mb-3 event-detail-card headding">{{ __('workshop.subject_matter_experts') }}</h4>
                            <hr>
                            <div class="expert-card p-3">
                                <div class="detail-grid">
                                     @if(!empty($event->event_for))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.event_for') }}:</div>
                                        <div class="value">{{ ucfirst($event_for ?? '-') }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->event_title))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.event_title') }}:</div>
                                        <div class="value">{{ $event->event_title }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->state_id))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.state') }}:</div>
                                        <div class="value">{{ !empty($event->state_id) ? state_list($event->state_id): '' }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($org_name))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.organiser_name') }}:</div>
                                        <div class="value">{{ $org_name }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->district_id))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.district') }}:</div>
                                        <div class="value">{{ !empty($event->district_id) ? district_list($event->district_id) : '' }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event_for->name))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.event_for') }}:</div>
                                        <div class="value">{{ $event_for->name }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->pincode))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.pincode') }}:</div>
                                        <div class="value">{{ $event->pincode }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->latitude))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.latitude') }}:</div>
                                        <div class="value">{{ $event->latitude }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->longitude))
                                    <div class="item">
                                        <div class="label">{{ __('workshop.longitude') }}:</div>
                                        <div class="value">{{ $event->longitude }}</div>
                                    </div>
                                    @endif

                                    @if(!empty($event->venue_address))
                                    <div class="item full">
                                        <div class="label">{{ __('workshop.venue_address') }}:</div>
                                        <div class="value">
                                        {{ $event->venue_address }}
                                        </div>
                                    </div>
                                    @endif
                                    
                                    @if(!empty($event->event_description))
                                    <div class="item full">
                                        <div class="label">{{ __('workshop.description') }}:</div>
                                        <div class="value">
                                        {{ $event->event_description }}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="schedule-table mt-4">
                                <h4 class="mb-3 event-shedule headding">{{ __('workshop.event_schedules') }}</h4>
                                <div class="table-responsive">
                                    <table class="table modern-table align-middle">
                                        <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Start Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($scheduleData['schedules'] as $key => $schedule)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $schedule['start_date'] }}</td>
                                            <td>{{ $schedule['end_date'] }}</td>
                                            <td>{{ \Carbon\Carbon::createFromFormat('H:i',$schedule['start_time'])->format('h:i A') }}</td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            @if(!empty($uploadedImage[0]))
                                <div class="side-image">
                                    <img src="{{ isset($uploadedImage[0]) ? asset('storage/app/'.$uploadedImage[0]->file_path.'/'.$uploadedImage[0]->file_system_name) : asset('img/workshop-1.png') }}" class="img-fluid">
                                </div>
                            @else
                                <div class="side-image">
                                    <img src="{{ asset('assets/img-new/workshop-image-msme.png') }}" class="img-fluid">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
@endsection