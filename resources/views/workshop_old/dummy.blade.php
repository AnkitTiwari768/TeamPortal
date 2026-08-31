<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>MSME Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            background: #f4f6fb;
            font-family: Inter, Arial, sans-serif;
            color: #1f2937;
        }

        /* ===== Layout ===== */
        .wrapper {
            display: flex;
            min-height: 100vh
        }

        .layout {
            display: flex;
            min-height: calc(100vh - 64px);
        }

        .layout {
            display: flex;
            margin-top: 80px;
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
            height: 43px;
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
            font-size: 16px;
            font-weight: 700;
        }

        /* ---------- TOP NAV CSS END ---------- */

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 16px;
        }

        .sidebar .logo {
            font-weight: 700;
            margin-bottom: 18px;
        }

        .sidebar a {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            color: #374151;
            text-decoration: none;
            margin-bottom: 6px;
        }

        .sidebar a.active {
            background: #1e3a8a;
            color: #fff;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width: 250px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 14px 0;
        }

        /* MENU */
        .sidebar-menu {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        /* MAIN ITEM */
        .menu-item {
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
        }

        /* ACTIVE DASHBOARD */
        .menu-item.active {
            background: #1e3a8a;
            color: #ffffff;
            border-radius: 8px;
            margin: 0 12px;
        }

        /* LEFT BLUE INDICATOR */
        .menu-item.active::before {
            content: "";
            position: absolute;
            left: -12px;
            top: 0;
            height: 100%;
            width: 4px;
            background: #2563eb;
            border-radius: 4px;
        }

        /* SUB MENU GROUP */
        .menu-group .menu-item {
            margin: 0 12px;
        }

        a.menu-item {
            font-weight: 600;
        }

        /* CHEVRON */
        .menu-item.has-sub i {
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        /* OPEN STATE */
        .menu-group.open .menu-item.has-sub i {
            transform: rotate(180deg);
            display: inline-block;
            margin-left: auto;
            transition: transform 0.15s ease;
            position: absolute;
            right: 14px;
            top: 28%;
            color: #151515;
        }

        /* SUB MENU */
        .submenu {
            padding-left: 24px;
            margin-top: 4px;
            display: none;
        }

        /* OPEN BY DEFAULT */
        .menu-group.open .submenu {
            display: block;
        }

        /* SUB MENU ITEM */
        .submenu-item {
            padding: 6px 0;
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        /* HOVER */
        .submenu-item:hover {
            color: #1e40af;
        }






        /* Content */
        .content {
            flex: 1;
            padding: 24px;
        }





        /* Responsive */
        @media(max-width:992px) {
            .wrapper {
                flex-direction: column
            }

            .sidebar {
                width: 100%
            }
        }

        /*********************************************************
____________________ EVENT DETAIL PAGE CSS ___________ */


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
        }

        .event-details-page .info-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .event-details-page .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
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
            border: 1px solid #eee;
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
    </style>
</head>

<body>

    <!-- TOP BAR -->
    <div class="top-nav">
        <img src="https://web.utlhq.com/team_uat/app/assets/ffo-admin/img/msme-team-logo.svg">

        <button class="toggle-btn" id="toggleSidebar">
            <i class="fa fa-bars"></i>
        </button>

        <div class="welcome-text">Welcome BNP</div>

        <div class="dropdown ms-auto">
            <button class="btn btn-light rounded-circle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-user"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" style="">
                <li><a class="dropdown-item" href="#">My Profile</a></li>
                <li><a class="dropdown-item" href="#">Change Password</a></li>
                <li><a class="dropdown-item text-danger" href="#">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="layout">

        <!-- SIDEBAR -->
        <aside class="sidebar">

            <div class="sidebar-menu">

                <a class="menu-item active">
                    Dashboard
                </a>

                <div class="menu-group open">

                    <a class="menu-item"> MSE Profile Details</a>

                </div>

                <div class="menu-group open">

                    <a class="menu-item">SNP Details</a>

                </div>

                <div class="menu-group open">

                    <a class="menu-item">Relevant NP List</a>

                </div>





                <div class="menu-group open">

                    <a class="menu-item"> Explore Workshops </a>

                </div>

            </div>

        </aside>


        <!-- CONTENT -->
        <main class="content">
            <div class="main-container event-details-page">
                <h3>Event Details</h3>
                <div class="breadcrumb-text">Home / Event List / View Event</div>
                <hr>
                <div class="row mt-4">

                    <div class="col-md-9">
                        <h4 class="mb-2  headding ">Event Title Name</h4>

                       

                        <div class="event-info-box mt-2">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="info-item">
                                        <div class="icon-box">📅</div>
                                        <div>
                                            <div class="info-title">{{ __('workshop.date') }}</div>
                                            <div class="info-value">18/02/2026 - 20/02/2026</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <div class="info-item">
                                        <div class="icon-box">📍</div>
                                        <div>
                                            <div class="info-title">{{ __('workshop.venue_address') }}:</div>
                                            <div class="info-value">Basni I & II Industrial Area, Jodhpur, Rajasthan
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="info-item">
                                        <div class="icon-box">⏱</div>
                                        <div>
                                            <div class="info-title">{{ __('workshop.duration') }}:</div>
                                            <div class="info-value">(3 Days)</div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <h4 class="mb-3 event-detail-card  headding ">Subject Matter Experts</h4>

                        <hr>

                        <div class="expert-card p-3 ">

                            <div class="detail-grid">

                                <div class="item">
                                    <div class="label">{{ __('workshop.event_title') }}:</div>
                                    <div class="value">National Handicraft Development Workshop</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.state') }}:</div>
                                    <div class="value">Rajasthan</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.organiser_name') }}:</div>
                                    <div class="value">Ministry of MSME Development</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.district') }}:</div>
                                    <div class="value">Jodhpur</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.event_for') }}:</div>
                                    <div class="value">Seller Network Participants (SNP)</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.pincode') }}:</div>
                                    <div class="value">342005</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.latitude') }}:</div>
                                    <div class="value">26.2389</div>
                                </div>

                                <div class="item">
                                    <div class="label">{{ __('workshop.longitude') }}:</div>
                                    <div class="value">73.0243</div>
                                </div>

                                <div class="item full">
                                    <div class="label">{{ __('workshop.venue_address') }}:</div>
                                    <div class="value">Basni Industrial Area Phase II, Jodhpur, Rajasthan</div>
                                </div>

                                <div class="item full">
                                    <div class="label">{{ __('workshop.description') }}:</div>
                                    <div class="value">
                                        Training workshop helping artisans improve product branding, digital
                                        marketing, and
                                        market access.
                                    </div>
                                </div>

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

                                        <tr>
                                            <td>1</td>
                                            <td>16-11-2025</td>
                                            <td>17-11-2025</td>
                                            <td>09:41 AM</td>
                                        </tr>

                                        <tr>
                                            <td>2</td>
                                            <td>24-11-2025</td>
                                            <td>25-11-2025</td>
                                            <td>09:48 AM</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="side-image">
                            <img src="./img/workshop-1.png" class="img-fluid">
                        </div>
                    </div>

                </div>

            </div>





        </main>
    </div>

</body>

</html>