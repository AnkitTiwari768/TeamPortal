@extends('components.admin.content-layout')


@section('page-content')
    {{-- @include('dashboard.in-progress') --}}

    <style>
        span.sta.yellow {
            background: #bd9a1a;
            padding: 2px 5px 3px 6px;
            border-radius: 13px;
            font-size: 12px;
            color: #fff;
        }

        .highcharts-legend {
            display: block !important;
        }

        .highcharts-credits {
            display: none
        }



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





        /* ===== STATUS STEPPER ===== */
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





        /* ===== WORKSHOP CARD ===== */
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
            padding: 10px;
            text-align: center;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-decoration: none;
        }

        .content-area-middle .card {
            border-radius: 14px;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 3px 8px;
            border: 0px;
        }

        .content-area-middle .card.type-1 {
            background: linear-gradient(45deg, #595cff, #c6f8ff);
        }

        .content-area-middle .card.type-2 {
            background: linear-gradient(45deg, #696eff, #f8acff);
        }

        /* .filter-area {
                                                            background: #f5f5f5;
                                                            border-radius: 10px;
                                                        } */
        .filter label {
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 5px;
        }

        /* ===== FOOTER ===== */
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
    <div class="container-fluid p-4">

		
		 {{-- Filters Section --}}
        @include('dashboard.search')

        <div class="tab-content mt-4" id="dashboardTabsContent">
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h1 class="page-title mb-3">Association Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area-middle">
            <div class="row">
                <div class="col-lg-4">
                    <div class="card type-1 text-white">
                        <div class="card-body py-4 d-flex">
                            <div class="content p-0">
                                <h6>Total MSEs Registered</h6>
                                <h1 class="fw-bold" id="totalMseRegistered">{{ $totalMseRegistered }}</h1>
                            </div>
                            <img src="{{ asset('assets/img/msme-reg.svg') }}" alt="">
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card type-2 text-white">
                        <div class="card-body py-4 d-flex">
                            <div class="content p-0">
                                <h6>Total MSEs Mapped</h6>
                                <h1 class="fw-bold" id="totalMseMapped">{{ $totalMseMapped }}</h1>
                            </div>
                            <img src="{{ asset('assets/img/msme-mapped.svg') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @section('js')
		
		@include('dashboard.datepicker') 
		
          <script>
		  
			$(document).ready(function () {

				$('#filterReset').on('click', function () {
					resetFilters();
					reloadAllCharts(false);
				});

				$('#filterSearch').on('click', function() {
					reloadAllCharts(true);
				});

	
				function resetFilters() {
						$('#year').val('');
						$('#from_date_new').val('');
						$('#to_date_new').val('');
					}
				});


			function getFilterParams(isDateSearch = false, isState = false) {
				let year = $('#year').val();
				let from = $('#from_date_new').val();
				let to   = $('#to_date_new').val();
				let params = {year:'',from_date_new:'',to_date_new:''};
				
				if(year ==''){
					 params = {year:'',from_date_new:'',to_date_new:'','type':1};
				}
				
				//  DATE SEARCH MODE
				if (isDateSearch && from && to) {
					params.from_date_new = from;
					params.to_date_new = to;
					return params;
				}

				//  YEAR MODE
				if (year) {
					if (isState) params.syear = year;
					else params.year = year;
				}
				

				return params;
			}


			function reloadAllCharts(isDateSearch = false) {
				reloadDashboardCards(isDateSearch);
			}


			function reloadDashboardCards(isDateSearch = false) {
				var params = getFilterParams(isDateSearch);

				$.ajax({
					url: "{{ url('/dashboard')}}",
					type: 'GET',
					data: params,
					beforeSend: function() {
						$("#ajax-loader").show();
					},
					success: function (response) {
						updateDashboardCards(response);
					},
					error: function (xhr) {
						console.error('Error reloading cards:', xhr.responseText);
					},
					complete: function() {
						$("#ajax-loader").hide();
					}
				});
			}

			function updateDashboardCards(data) {
				$('.totalMseRegistered').text(data.totalMseRegistered || 0);
				$('.totalMseMapped').text(data.totalMseMapped || 0);
			}

        </script>
    @endsection
