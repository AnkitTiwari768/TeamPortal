@extends('components.admin.layout')
<style>
       
    @media(max-width:992px) {
        .wrapper {
            flex-direction: column
        }

        .sidebar {
            width: 100%
        }
    }
    
    .main-container {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        padding: 20px;
        min-height: calc(100vh - 140px);
    }

    .workshop-image img {
        width: 100%;
        max-height: 180px;
        object-fit: inherit;
        min-height: 180px;
    }

    .card-body {
        padding: 16px;
    }

   .workshop-card .card-title {
    font-weight: 600;
    font-size: 16px;
    padding-bottom: 13px;
}

.workshop-card .workshop-image {
    background: #fff;
    border-radius: 10px;
    padding: 10px;
    border: 1.5px solid #cfe6ff;
    box-shadow: rgb(99 134 154 / 33%) 0px 2px 8px 0px;
}
    .card-info {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #555;
        margin-bottom: 7px;
    }

    .card-info i {
        width: 18px;
        margin-right: 6px;
        color: #2f4aa0;
    }

    .card-desc {
        font-size: 13px;
        margin-top: 10px;
        line-height: 1.4;
    }
 
    .workshop-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        height: 100%;
        position: relative;
        background-color: #2f4aa0;
    }

    .card-body {
        padding: 16px;
    }

    .card-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .card-info {
        display: flex;
        align-items: center;
        font-size: 13px;
        color: #555;
        margin-bottom: 6px;
    }

    .card-info i {
        width: 18px;
        margin-right: 6px;
        color: #2f4aa0;
    }

    .card-desc {
        font-size: 13px;
        margin-top: 10px;
    }

    .content-inner-card {
        background: #fff;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: -7px;
        /* position: absolute; */
        z-index: 999999;
        top: 0;
        background-color: #ffffff;
    }

    .workshopcard-footer {
        width: 100%;
        border-radius: 16px;
        padding-bottom: 0;
        overflow: hidden;
        margin-top: 0;
        height: 32px;
        position: relative;
        /* background-color: #2f4aa0; */
        z-index: 999;
        bottom: 0;
    }

    .workshopcard-footer .view-section { 
        color: #fff !important;
        font-weight: 600;
        font-size: 12px;
        position: absolute;
        bottom: 0;
        width: 100%;
        box-shadow: none;
        background-color: #2f4aa0;
        border: none
    }

    .workshopcard-footer .fa-eye:before {
        content: "\f06e";
        color: #fff;
    }
    .workshopcard-footer a.view-btn {
        color: #fff;
        text-decoration: none;
    }
    .workshop-card .card-body {
    padding: 12px;
    min-height: 180px;
}

</style>
@section('page-content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <main class="content container-main-card">
                <div class="main-container">

                   <div class="card-header d-flex">
                        <div class="heading">
                            <h1>{{ __('workshop.proposed_workshop') }}</h1>
                            <nav aria-label="breadcrumb">
                                <!-- <ol class="breadcrumb">
                                    <li class="breadcrumb-item">{{ __('workshop.home') }}</li>
                                    <li class="breadcrumb-item">{{ __('workshop.view_proposed_workshop') }}</li>
                                    <li class="breadcrumb-item active" aria-current="page">{{ __('workshop.view_proposed_workshop') }}</li>
                                </ol> -->
                            </nav>
                        </div>
                        
                    </div>
                    <div id="workshop-cards" class="row g-3 mt-2 row-gap-4">

                        @foreach($cardData as $workshop)

                        <div class="col-xxl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="workshop-card">

                                <div class="content-inner-card">
                                    @if(!empty($workshop->file_system_name))
                                        <div class="workshop-image">
                                            <img src="{{ asset('storage/app/'.$workshop->file_path.'/'.$workshop->file_system_name) }}" class="img-fluid">
                                        </div>
                                        @else
                                        <div class="workshop-image">
                                            <img src="{{ asset('assets/img-new/workshop-image-msme.png') }}" class="img-fluid">
                                        </div>
                                    @endif
                                    <div class="card-body">

                                        <div class="card-title">
                                            {{ ucfirst($workshop->event_title) }}
                                        </div>

                                        <div class="card-info">
                                            <i class="fa fa-map-marker"></i>
                                            {{ strtoupper($workshop->state_name ?? 'N/A') }}
                                        </div>

                                        <div class="card-info">
                                            <i class="fa fa-calendar"></i>
                                            {{ $workshop->start_date }} {{ __('workshop.to') }} {{ $workshop->end_date }}
                                        </div>

                                        <div class="card-info">
                                            <i class="fa fa-clock-o"></i>
                                            {{ $workshop->duration ?? 1 }} days
                                        </div>

                                        <div class="card-desc">
                                            Event for - {{ $workshop->event_for_name }}
                                        </div>

                                    </div>
                                </div>

                                <div class="workshopcard-footer">
                                    <button class="view-section">
                                        <a href="{{ url('view-proposed-workshop/'.$workshop->id) }}" class="view-btn">
                                            <i class="fa fa-eye"></i> {{ __('workshop.view_details') }}
                                        </a>
                                    </button>
                                </div>

                            </div>
                        </div>

                        @endforeach

                    </div>
            </main>
        </div>
    </div>
</div>
@endsection
