@extends('components.admin.content-layout')

@section('page-content')
    <style>
        
        body {
            margin: 0;
            background: #f5f7fb;
            font-family: "Inter", Arial, sans-serif;
            color: #1f2937;
        }


        /* ---------- TOP BAR  Start---------- */
        .top-nav {
            height: 80px;
            background: #ffffff;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            padding: 0 20px;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }

        .top-nav img {
            height: 45px;
        }

        .toggle-btn {
            background: #ebebeb;
            border: none;
            width: 38px;
            height: 38px;
            margin-left: 15px;
            border-radius: 4px;
        }

        .welcome-text {
            margin-left: 15px;
            font-weight: 500;
            font-size: 16px;
        }

        /* ---------- TOP NAV CSS END ---------- */

        /* ---------- LAYOUT ---------- */
        .layout {
            display: flex;
            margin-top: 80px;
        }

        /* ===== LAYOUT ===== */
        .layout {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 16px;
        }

        .menu-item {
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 500;
            color: #374151;
            text-decoration: none;
            display: block;
            margin-bottom: 6px;
        }

        .menu-item.active {
            background: #1e3a8a;
            color: #ffffff;
        }

        /* ===== MAIN CONTENT ===== */
        .content {
            flex: 1;
            padding: 16px;
        }

        /* ===== WHITE CONTAINER ===== */
        .main-container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 20px;
            min-height: calc(100vh - 140px);
        }

        /* ===== HEADER-Heading ===== */
        .dashboard-header-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .dashboard-header-heading h4 {
            margin: 0;
            font-weight: 600;
            color: #1e40af;
        }

        /* SEARCH BOX */
        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 8px 12px;
            width: 260px;
        }

        .search-box i {
            color: #9ca3af;
            font-size: 14px;
        }

        .search-box input {
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
            color: #374151;
        }

        .search-box input::placeholder {
            color: #9ca3af;
        }





        /* ===== MSME CARD ===== */
        .msme-card {
            background: #eaf6ff;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #cfe7ff;
        }

        .msme-top {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .msme-icon-profile {
            background: #ffffff;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
        }


        /* =====New STATUS STEPPER Start ===== */
        /* ===== MSME CARD (MATCH IMAGE) ===== */
        .msme-card-new {
            background: #eef8ff;
            border-radius: 14px;
            padding: 16px;
            border: 1px solid #cfe7ff;
        }

        /* TOP SECTION */
        .msme-top-new {
            display: flex;
            gap: 14px;
            align-items: center;
            margin-bottom: 12px;
        }

        .msme-icon-profile-new {
            background: #ffffff;
            width: 52px;
            height: 52px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .msme-icon-profile-new img {
            width: 28px;
        }

        /* MSME INFO */
        .msme-meta {
            display: flex;
            gap: 60px;
            margin-top: 6px;
        }

        .msme-meta .label {
            font-size: 12px;
            color: #6b7280;
        }

        .msme-meta .value {
            font-weight: 600;
            font-size: 13px;
        }

        /* STATUS BOX */
        .status-box-new {
            background: #ddf2ff;
            border-radius: 12px;
            padding: 35px 14px;
        }
        .status-title-new {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 10px;
        }

        /* STEPPER */
        .status-row-new {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .status-step {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            white-space: nowrap;
        }

        .status-step i {
            font-size: 16px;
        }

        .status-step.active {
            color: #2563eb;
            font-weight: 600;
        }

        /* CENTER DESCRIPTION */
        .status-desc {
            font-size: 11px;
            color: #6b7280;
            line-height: 1.4;
            margin-top: 2px;
        }

        /* LINE */
        .status-line-new {
            flex: 1;
            height: 1px;
            background: #9ecbff;
            margin: 0 10px;
        }

        .status-box {
            background: #dff1ff;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .status-title {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .status-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #6b7280;
            white-space: nowrap;
        }

        .status-item.active {
            color: #2563eb;
            font-weight: 500;
        }

        .status-line {
            flex: 1;
            height: 1px;
            background: #9ecbff;
            margin: 0 8px;
        }

        .workshop-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #c7d2fe;
            overflow: hidden;
            height: 100%;
        }

        .workshop-card .title-crad {
            color: #1A2A80;
            font-weight: 600;
            font-size: 115%;
        }

        .workshop-card>img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
        }

        .workshop-footer {
            padding: 9px 0 5px 0;
        }

        .workshop-btn {
            background: #1e3a8a;
            color: #fff;
            border-radius: 10px;
            padding: 7px;
            text-align: center;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
        }
        .workshop-btn .btn-text {
            font-size: 14px;
            margin-left: -18px;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            padding: 10px;
        }

        @media (max-width: 992px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
            }
        }
    </style>

<main class="content">

    <div class="main-container">

        <!-- HEADER -->
        

        <div class="row g-3">
            <!-- MSME CARD -->
            <div class="col-lg-9">
                <div class="msme-card-new">

                    <!-- TOP INFO -->
                    <div class="msme-top-new">
                        <div class="msme-icon-profile-new">
                            <img src="{{ asset('assets/img/profile.svg') }}" alt="">
                        </div>

                        <div class="msme-info">
                            <!-- <div class="fw-semibold">MSME Team Scheme</div> -->

                            <div class="msme-meta">
                                <?php /* <div>
                                    <div class="label">Name of MSME</div>
                                    <div class="value">{{$msme_dashboard_details['entrepreneur_name']}}</div>
                                </div> */ ?>
                                <div>
                                    <div class="label">Team ID</div>
                                    <div class="value">{{$msme_dashboard_details['team_id'] ?? null}}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- APPLICATION STATUS -->
                    <div class="status-box-new">
                        <div class="status-title-new">Application Status</div>

                        <div class="status-row-new">

                            <!-- Step 1 -->
                            <div class="status-step active">
                                
                                <!-- <span>Registered Date</span> -->
                                <div class="text-left">
                                    <div class="fw-medium text-center d-flex align-items-center justify-content-center gap-2">
                                        <i class="fa fa-check-circle"></i>
                                        <span>Registered Date</span>
                                    </div>
                                    
                                    <div class="status-desc text-center">
                                        {{$msme_dashboard_details['registered_at'] ?? null}}
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="status-line-new"></div>

                            <!-- Step 2 -->
                            <div class="status-step {{$msme_dashboard_details['lead_status']?'active':''}} ">
                                
                                <div class="text-left">
                                    <div class="fw-medium text-center d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa fa-users"></i>
                                    <span>Lead Status</span>
                                    </div>

                                    <div class="status-desc ">
                                        {{$msme_dashboard_details['lead_status'] ?? null}}
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="status-line-new"></div>

                            <!-- Step 3 -->
                            <div class="status-step {{$msme_dashboard_details['onboard_at']?'active':''}}">
                                 
                                <!-- <span>Onboarded Date</span> -->
                                <div class="text-left">
                                    <div class="fw-medium text-center d-flex align-items-center justify-content-center gap-2">
                                    <i class="fa fa-thumbs-up text-primary"></i>
                                    <span>Onboarded Date On ONDC</span>
                                    </div>
                                    <div class="status-desc">
                                        {{$msme_dashboard_details['onboard_at'] ?? null}}
                                        
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="status-line-new"></div> -->

                            <!-- Step 4 -->
                            <!-- <div class="status-step">
                                <i class="fa fa-file-invoice"></i>
                                <span>Claims</span>
                            </div> -->

                        </div>
                    </div>

                </div>
            </div>


            <!-- WORKSHOP -->
            <div class="col-lg-3 p-0">
                <div class=" workshop-card p-2">
                    <div class=" mb-2 text-center fw-semibold title-crad">Workshop Management</div>
                    <img src="{{ asset('assets/img/workshop-img.png') }}" class="img-fluid">

                    <div class="workshop-footer">


                        <a href="{{ url('/event') }}" class="workshop-btn">
                            <span class="icon-box">
                                <img src="{{ asset('assets/img/export-icon.svg') }}" class="img-fluid">
                            </span>


                            <span class="btn-text">Explore Workshops</span>
                            <i class="fa fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </div>

</main>
@endsection
