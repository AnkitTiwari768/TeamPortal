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
		.highcharts-credits{display:none}
    </style>
    <div class="container-fluid px-4 py-4">
        {{-- Filters Section --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <label>Year</label>
                {!! Form::select('year', year_list(), $row['year'] ?? $selectedYear, [
                    'class' => 'form-select',
                    'id' => 'year',
                    'onchange' => 'reloadAllCharts(false)'
                ]) !!}
            </div>

            <div class="col-md-3">
                <label>From Date</label>
                <input type="text" id="from_date_new" class="form-control"  placeholder="dd-mm-yyyy">
            </div>

            <div class="col-md-3">
                <label>To Date</label>
                <input type="text" id="to_date_new" class="form-control"  placeholder="dd-mm-yyyy">
            </div>

            <div class="col-md-3 align-self-end d-flex gap-2">
                <button id="filterSearch" class="btn btn-primary w-50">Search</button>
                 <button id="filterReset" class="btn btn-secondary w-50">Reset</button>
            </div>
        </div>


        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            @if(!hasRole('ca'))
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                    type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                    MSE Dashboard
                </button>
            </li>
             @endif
            <li class="nav-item" role="presentation">
                <button class="nav-link @if(hasRole('ca')) active @endif" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims"
                    type="button" role="tab" aria-controls="claims" aria-selected="{{ hasRole('ca') ? 'true' : 'false' }}">
                    Claim Dashboard
                </button>
            </li>
        </ul>


        
        <div class="tab-content mt-4" id="dashboardTabsContent">
            @if(!hasRole('ca'))
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">

                <div class="row doc-cards ondc-mse-cards mt-3 mb-4">
                    <div class="col-lg-3 register-mse-card">
                        <a href="{{ url('mis-reports-msme')}}" class="">
                            <div class="card w-100">
                                <div class="card-body d-flex gap-3">
                                    <div class="right text-start w-100">
                                    <div class="top-header-card">
                                        <div class="left align-items-center icon type-1">
                                            <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                        </div>
                                        <span>
                                            <p>Total Registered MSEs</p>
                                            <h5><span id="total_application" class="msmeForMeCounts_total_msme">{{ $msmeForMeCounts['total_msme'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul id="ul_msmeForMeCounts_major_activities" class="msmeForMeCounts_major_activities">
                                            @foreach($msmeForMeCounts['major_activities'] as $activity => $count)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $count }}</span> 
                                                </li>
                                            @endforeach    
                                        </ul>
                                        
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 choosen-option-card-1">
                        <a href="{{ url('mis-reports-msme')}}" class="">
                            <div class="card w-100">
                                <div class="card-body d-flex gap-3">
                                    <div class="right text-start w-100">
                                    <div class="top-header-card">
                                        <div class="left align-items-center icon type-2">
                                            <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                        </div>
                                        <span>
                                            <p>SNP Inititated Mapping</p>
                                            <h5><span id="total_application" class="msmeCounts_total_option1">{{ $msmeCounts['total']['option1'] ?? '' }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul id="ul_msmeCounts_option1" class="msmeCounts_major_activities">
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['option1'] ?? '' }}</span> 
                                                </li>
                                            @endforeach    
                                        </ul>
                                        
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 choosen-option-card-2">
                        <a href="{{ url('mis-reports-msme')}}" class="">
                            <div class="card w-100">
                                <div class="card-body d-flex gap-3">
                                    <div class="right text-start w-100">
                                    <div class="top-header-card">
                                        <div class="left align-items-center icon type-3">
                                            <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                        </div>
                                        <span>
                                            <p class="text-nowrap">MSME Inititated Mapping</p>
                                            <h5><span id="total_application" class="msmeCounts_total_option2">{{ $msmeCounts['total']['option2'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul id="ul_msmeCounts_option2" class="msmeCounts_major_activities">
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['option2'] ?? ''}}</span> 
                                                </li>
                                            @endforeach    
                                        </ul>
                                        
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-lg-3 onboarded-card">
                        <a href="{{ url('msme-snp-mapping-report') }}" class="">
                            <div class="card w-100">
                                <div class="card-body d-flex gap-3">
                                    <div class="right text-start w-100">
                                    <div class="top-header-card">
                                        <div class="left align-items-center icon type-4">
                                            <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                        </div>
                                        <span>
                                            <p>Onboarded MSEs</p>
                                            <h5><span id="total_application" class="msmeCounts_total_onboarded">{{ $msmeCounts['total']['onboarded'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul id="ul_msmeCounts_onboarded" class="msmeCounts_major_activities">
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['onboarded'] ?? '' }}</span> 
                                                </li>
                                            @endforeach    
                                        </ul>
                                        
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <div class="row g-4 mb-4">

                    <!-- Repeat for other cards -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card custom-card-nsic snp-card">
                            <div class="custom-card-nsic-header"></div>
                            <div class="card-body">
                            <h5 class="fw-bold">SNP</h5>
                            <div class="stats-box-nsic">
                                <div class="stats-icon">
                                <i class="bi bi-clipboard-check"></i>
                                </div>
                                <div>
                                <small class="text-primary d-block">Total Registrations</small>
                                <h5 class="fw-bold mb-0 snpCount_totalSnp">{{ $snpCount['totalSnp'] ?? '' }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Verification Pending </p>
                                <span class="info-value snpCount_pendingSnp">{{ $snpCount['pendingSnp'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Verified SNPs</p>
                                <span class="info-value snpCount_approvedSnp">{{ $snpCount['approvedSnp'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Rejected SNPs</p>
                                <span class="info-value snpCount_rejectedSnp">{{ $snpCount['rejectedSnp'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Reverted SNPs</p>
                                <span class="info-value snpCount_revertedSnp">{{ $snpCount['revertedSnp'] ?? '' }}</span>
                                </div>
                            </div>
                            <a href="{{ url('mis-snp-registration-reports') }}" class="explore-link">Explore</a>
                            </div>
                        </div>
                    </div>


                    <!-- Repeat for other cards -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card custom-card-nsic bnp-card">
                            <div class="custom-card-nsic-header"></div>
                            <div class="card-body">
                            <h5 class="fw-bold">BNP</h5>
                            <div class="stats-box-nsic">
                                <div class="stats-icon">
                                <i class="bi bi-clipboard-check"></i>
                                </div>
                                <div>
                                <small class="text-primary d-block">Total Registrations</small>
                                <h5 class="fw-bold mb-0 bnpCount_totalBnp">{{ $bnpCount['totalBnp'] ?? '' }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Verification Pending</p>
                                <span class="info-value bnpCount_pendingBnp">{{ $bnpCount['pendingBnp'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Verified BNPs</p>
                                <span class="info-value bnpCount_approvedBnp">{{ $bnpCount['approvedBnp'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Rejected BNPs</p>
                                <span class="info-value bnpCount_rejectedBnp">{{ $bnpCount['rejectedBnp'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Reverted BNPs</p>
                                <span class="info-value bnpCount_revertedBnp">{{ $bnpCount['revertedBnp'] ?? '' }}</span>
                                </div>
                            </div>
                            <a href="{{ url('mis-bnp-registration-reports') }}" class="explore-link">Explore</a>
                            </div>
                        </div>
                    </div>



                    <!-- Repeat for other cards -->
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="card custom-card-nsic claim-card">
                            <div class="custom-card-nsic-header"></div>
                            <div class="card-body">
                            <h5 class="fw-bold">CLAIMS</h5>
                            <div class="stats-box-nsic">
                                <div class="stats-icon">
                                <i class="bi bi-clipboard-check"></i>
                                </div>
                                <div>
                                <small class="text-primary d-block">Total Claims</small>
                                <h5 class="fw-bold mb-0 claimCounts_totalClaims">{{ $claimCounts['totalClaims'] ?? '' }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Total Approved Claims</p>
                                <span class="info-value claimCounts_approvedCount">{{ $claimCounts['approvedCount'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Total Reverted Claims</p>
                                <span class="info-value claimCounts_revertedCount">{{ $claimCounts['revertedCount'] ?? '' }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Total Pending Claims</p>
                                <span class="info-value claimCounts_pendingCount">{{ $claimCounts['pendingCount'] ?? '' }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Total Rejected Claims</p>
                                <span class="info-value claimCounts_rejectedCount">{{ $claimCounts['rejectedCount'] ?? '' }}</span>
                                </div>
                            </div>
                            
                            </div>
                        </div>
                    </div>
                    <!-- You can duplicate the above col for remaining 2 cards -->
                    
                </div>

                <div class="row mb-4">

                    <div class="col-lg-7">
                        
                        <div class="card common-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between main-charts-headding-wrap pb-2 " >
                                    <h3> MSE's By Top 10 Categories</h3>
                                </div>                                   
                                
                                <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                <figure class="highcharts-figure">
                                    <div id="pieChart"></div>
                                    
                                </figure>
                                </div>
                                
                            </div>
                        </div>
                    </div> 
                
                    <div class="col-lg-5">
                            <div class="card common-card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
                                        <h3> MSE Onboarded Percentage</h3>
                                        </div>
                                    
                                    <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                    <figure class="highcharts-figure">
                                        <div id="pieChartPercentage"></div>
                                        
                                    </figure>
                                    </div>
                                    
                                </div>
                            </div>
                    </div>
                </div>
                <div class="row mb-4">

                <div class="col-lg-6 ">
                    <div class="card common-card">
                        <div class="card-body">

                            <div class="d-flex justify-content-between main-charts-headding-wrap">
                                <div class="pb-2">
                                    <h3>MSE Onboarded</h3>
                                    <span> Visual representation of MSE Onboarded with the system </span>
                                </div>
                                <!-- <div class="year-filter">
                                {!! Form::select('year',year_list(),$row['year'] ?? null, ['class' => 'form-select', 'id' => 'year', 'onchange' => 'getMappingMsmeCount']) !!}
                                </div> -->
                            </div>
                            

                            <div class="graph-area mt-2">
                            <figure class="highcharts-figure">
                                <div id="bargraph_msme_onboarded"></div>
                            </figure>
                            </div>


                        </div>
                    </div>
                </div>
                    

                <div class="col-lg-6 ">
                        <div class="card common-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
                                    <h3> Top Performer SNP</h3>
                                    </div>
                                
                                <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                <figure class="highcharts-figure">
                                    <div id="top-performer-snp"></div>
                                    
                                </figure>
                                </div>
                                
                            </div>
                        </div>
                </div>
                </div> 


                <div class="row mb-4">  
                    <div class="col-lg-12">
                        <div class="card common-card">
                            <div class="card-body">

                                <div class="d-flex justify-content-between main-charts-headding-wrap">
                                        <div class="pb-2">
                                            <h3>Statewise MSME</h3>
                                            <span> Visual Representation of MSEs registered with the SNP </span>
                                        </div>
                                        <!-- <div class="year-filter">
                                        {!! Form::select('year',year_list(),$row['year'] ?? null, ['class' => 'form-select', 'id' => 'year', 'onchange' => 'getMappingMsmeCount']) !!}
                                        </div> -->
                                    </div>

                                <div class="graph-area mt-2">
                                <figure class="highcharts-figure">
                                    <div id="bargraph_msme_state_wise_onboarded"></div>
                                </figure>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif


            <div class="tab-pane fade @if(hasRole('ca')) show active @endif" id="claims" role="tabpanel" aria-labelledby="claims-tab">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h1 class="page-title">Claims</h1>
                    </div>
                </div>

                <div class="col-lg-12 mb-4 mt-3">
                    <div id="claim-summary" class="stat-row"></div>
                </div>

                <div class="col-lg-12 mb-4">
                    <div class="stat-row row">
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background: #e9eaff;">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Claims' : 'Total Batch' }}</h6>
                                <h4 class="fw-bold text-primary totalCliamCounts">{{ !hasRole('ca') ? $claimCounts['totalClaims'] : $caCountSummaryBatchWise['total']  }}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background: #e0fbe5;">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Approved Claims' : 'Total Approved Batch'}}</h6>
                                <h4 class="fw-bold text-success claimsApprovedClaimsCount">{{ !hasRole('ca') ? $claimCounts['approvedCount'] : $caCountSummaryBatchWise['approved']}}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class=" stat-card shadow-sm" style="background:#fffbea;">
                                <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Reverted Claims' : 'Total Revert Batch'}}</h6>
                                <h4 class="fw-bold text-warning claimCountsRevertedCount">{{ !hasRole('ca') ? $claimCounts['revertedCount'] : $caCountSummaryBatchWise['reverted'] }}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                                <i class="bi bi-clock-history text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Pending Claims' : 'Total Pending Batch'}}</h6>
                                <h4 class="fw-bold text-warning claimsPendingCounts">{{ !hasRole('ca') ? $claimCounts['pendingCount'] : $caCountSummaryBatchWise['pending'] }}</h4>
                            </div>
                        </div>
                        @if(!hasRole('ca'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#fff2f2;">
                                <i class="bi bi-x-circle text-danger fs-3"></i>
                                <h6 class="mt-2">Total Rejected Claims</h6>
                                <h4 class="fw-bold text-danger claimRejectedCounts">{{ $claimCounts['rejectedCount'] }}</h4>
                            </div>
                        </div>
                        @if(hasRole('snp'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#eef6ff;">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                <h6 class="mt-2">Total Draft</h6>
                                <h4 class="fw-bold text-primary claimCountsDraftCount">{{ $claimCounts['draftCount'] }}</h4>
                            </div>
                        </div>
                        @endif
                        @endif
                        @if(!hasRole('ca'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background:#f3fff6;">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                                <h6 class="mt-2">Total Payment Completed</h6>
                                <h4 class="fw-bold text-success claimCountsPaymentCompleted">{{ $claimCounts['paymentCompletedCount'] }}</h4>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>


    </div>
        @endsection


        @section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>


<script>
    const isCA = {{ hasRole('ca') ? 'true' : 'false' }};
    $(document).ready(function () {

        $( "#from_date_new" ).datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true,
            //minDate: new Date(),
            onSelect: function(selected) {
            $("#to_date_new").datepicker("option","minDate", selected)
            },
            onClose: function(selected) {
                $("#reset_btn").show();
            }
        });

        $( "#to_date_new" ).datepicker({
            dateFormat: "dd-mm-yy",
            changeYear: true,
            changeMonth: true, 
            minDate: new Date(),
            onSelect: function(selected) {
            $("#from_date_new").datepicker("option","maxDate", selected); 
            },
            onClose: function(selected) {
            $("#reset_btn").show();
            }
        }); 

        applyYearRestriction();

        $('#year').on('change', function () {
            const selectedYear = $(this).val();

            if (!selectedYear) {
                $('#from_date_new, #to_date_new').datepicker('option', {
                    yearRange: 'c-10:c+10'
                });
                return;
            }

            const defaultDate = new Date(selectedYear, 0, 1);

            $('#from_date_new, #to_date_new').datepicker('option', {
                defaultDate: defaultDate,
                yearRange: selectedYear + ':' + selectedYear
            });

            $('#from_date_new, #to_date_new').val('');
        });

        $('#from_date_new, #to_date_new').on('focus', function () {
            const year = $('#year').val();
            if (!year) return;

            $(this).datepicker('setDate', new Date(year, 0, 1));
            $(this).datepicker('setDate', null);
        });

        $('#filterReset').on('click', function () {
            resetFilters();
            reloadAllCharts(false);
            loadClaimSummary(false);
        });

        $('#filterSearch').on('click', function() {
            reloadAllCharts(true);
            loadClaimSummary(true);
        });

        if (!isCA) {
            reloadAllCharts(false);
        } else {
            loadClaimSummary(false);
        }

        $('#claims-tab').on('shown.bs.tab', function () {
           // resetFilters();
            if (!$('#claim-summary').hasClass('loaded')) {
                
                loadClaimSummary(false);
                $('#claim-summary').addClass('loaded');
            }
        });

        $('#dashboard-tab').on('shown.bs.tab', function () {
            //resetFilters();
            reloadAllCharts(false);
        });

        function resetFilters() {
            // $('#year').val('');
            // if ($.fn.datepicker) {
            //     $('#from_date_new, #to_date_new').datepicker('setDate', new Date());
            // } else {
            //     $('#from_date_new, #to_date_new').val(formattedDate);
            // }

            $('#year').val('');
            $('#from_date_new').val('');
            $('#to_date_new').val('');
        }
    });

    function applyYearRestriction() {
        const selectedYear = $('#year').val();

        if (!selectedYear) {
            $('#from_date_new, #to_date_new').datepicker('option', {
                yearRange: 'c-10:c+10'
            });
            return;
        }

        const defaultDate = new Date(selectedYear, 0, 1);

        $('#from_date_new, #to_date_new').datepicker('option', {
            defaultDate: defaultDate,
            yearRange: selectedYear + ':' + selectedYear
        });

        $('#from_date_new, #to_date_new').val('');
    }


    $('#year').on('change', function () {
        applyYearRestriction();
    });

    function getFilterParams(isDateSearch = false, isState = false) {
        let year = $('#year').val();
        let from = $('#from_date_new').val();
        let to   = $('#to_date_new').val();
        let params = {};

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
        getMappingMsmeCount(isDateSearch);
        getMsmeStateWiseCount(isDateSearch);
        getMsmeCategoryCountOnboarded(isDateSearch);
        getMsmePercetageByGender(isDateSearch);
        topPerformerSnpBarChart(isDateSearch);
        reloadDashboardCards(isDateSearch);
        loadClaimSummary(isDateSearch);
    }


    function reloadDashboardCards(isDateSearch = false) {
        var params = getFilterParams(isDateSearch);

        $.ajax({
            url: "{{ url('/dashboard')}}",
            type: 'GET',
            data: params,
            success: function (response) {
                updateDashboardCards(response);
            },
            error: function (xhr) {
                console.error('Error reloading cards:', xhr.responseText);
            }
        });
    }

    function updateDashboardCards(data) {
        //console.log(data);
        $('.msmeForMeCounts_total_msme').text(data.msmeForMeCounts.total_msme || 0);

        function populateUl(ulId, activities, key) {
            const ul = $(ulId);
            ul.empty();
            if (activities) {
                $.each(activities, function(activity, counts) {
                    let value = counts;
                    if (key && typeof counts === 'object') {
                        value = counts[key] != null ? counts[key] : 0;
                    } else if (key == null) {
                        value = counts != null ? counts : 0;
                    }
                    ul.append(`<li><span>${activity}</span><span>${value}</span></li>`);
                });
            }
        }

        populateUl('#ul_msmeForMeCounts_major_activities', data.msmeForMeCounts.major_activities, null);
        populateUl('#ul_msmeCounts_option1', data.msmeCounts.major_activities, 'option1');
        populateUl('#ul_msmeCounts_option2', data.msmeCounts.major_activities, 'option2');
        populateUl('#ul_msmeCounts_onboarded', data.msmeCounts.major_activities, 'onboarded');

        $('.msmeCounts_total_option1').text(data.msmeCounts.total.option1 || 0);
        $('.msmeCounts_total_option2').text(data.msmeCounts.total.option2 || 0);
        $('.msmeCounts_total_onboarded').text(data.msmeCounts.total.onboarded || 0);

        $('.snpCount_totalSnp').text(data.snpCount.totalSnp || 0);
        $('.snpCount_pendingSnp').text(data.snpCount.pendingSnp || 0);
        $('.snpCount_approvedSnp').text(data.snpCount.approvedSnp || 0);
        $('.snpCount_rejectedSnp').text(data.snpCount.rejectedSnp || 0);
        $('.snpCount_revertedSnp').text(data.snpCount.revertedSnp || 0);

        if (data.snpCount.totalSnp > 0) {
            $('.snp-card .explore-link').show();
        } else {
            $('.snp-card .explore-link').hide();
        }

        $('.bnpCount_totalBnp').text(data.bnpCount.totalBnp || 0);
        $('.bnpCount_pendingBnp').text(data.bnpCount.pendingBnp || 0);
        $('.bnpCount_approvedBnp').text(data.bnpCount.approvedBnp || 0);
        $('.bnpCount_rejectedBnp').text(data.bnpCount.rejectedBnp || 0);
        $('.bnpCount_revertedBnp').text(data.bnpCount.revertedBnp || 0);

        if (data.bnpCount.totalBnp > 0) {
            $('.bnp-card .explore-link').show();
        } else {
            $('.bnp-card .explore-link').hide();
        }

        $('.claimCounts_totalClaims').text(data.claimCounts.totalClaims || 0);
        $('.claimCounts_approvedCount').text(data.claimCounts.approvedCount || 0);
        $('.claimCounts_revertedCount').text(data.claimCounts.revertedCount || 0);
        $('.claimCounts_pendingCount').text(data.claimCounts.pendingCount || 0);
        $('.claimCounts_rejectedCount').text(data.claimCounts.rejectedCount || 0);

        $('.totalCliamCounts').text(data.claimCounts.totalClaims || 0);
        $('.claimsApprovedClaimsCount').text(data.claimCounts.approvedCount || 0);
        $('.claimCountsRevertedCount').text(data.claimCounts.revertedCount || 0);
        $('.claimsPendingCounts').text(data.claimCounts.pendingCount || 0);
        $('.claimRejectedCounts').text(data.claimCounts.rejectedCount || 0);
        $('.claimCountsDraftCount').text(data.claimCounts.draftCount || 0);
        $('.claimCountsPaymentCompleted').text(data.claimCounts.paymentCompletedCount || 0);

        if (data.claimCounts.totalClaims > 0) {
            $('.claim-card .explore-link').show();
        } else {
            $('.claim-card .explore-link').hide();
        }
    }



    function getMappingMsmeCount(isDateSearch = false) {
        $.ajax({
            url: "{{ url('get-mapping-msme-count') }}",
            type: "GET",
            data: getFilterParams(isDateSearch),
            success: getMappingMsmeCountBarGraph,
            error: function (xhr) { console.error(xhr.responseText); }
        });
    }

    function getMappingMsmeCountBarGraph(response) {
        Highcharts.chart('bargraph_msme_onboarded', {
            chart: { type: 'column' },
            title: { text: '' },
            xAxis: { type: 'category' },
            yAxis: { min: 0, title: { text: 'MSME Count' } },
            legend: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> MSMEs' },
            series: [{ name: 'MSME Count', colorByPoint: true, groupPadding: 0, data: response }]
        });
    }


    function getMsmeStateWiseCount(isDateSearch = false) {
        $.ajax({
            url: "{{ url('get-msme-state-wise-count') }}",
            type: "GET",
            data: getFilterParams(isDateSearch, true),
            success: getMsmeStateWiseCountBarGraph,
            error: function (xhr) { console.error(xhr.responseText); }
        });
    }

    function getMsmeStateWiseCountBarGraph(response) {
        Highcharts.chart('bargraph_msme_state_wise_onboarded', {
            chart: { type: 'column' },
            title: { text: '' },
            xAxis: { type: 'category' },
            yAxis: { min: 0, title: { text: 'MSME Count' } },
            legend: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> MSMEs' },
            series: [{ name: 'MSME Count', colorByPoint: true, groupPadding: 0, data: response }]
        });
    }

    function getMsmeCategoryCountOnboarded(isDateSearch = false){
        $.ajax({
            url: "{{ url('get-msme-category-count-onboarded') }}",
            type: "GET",
            data: getFilterParams(isDateSearch),
            dataType: "json",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: getMsmeCategoryCountOnboardedPiechart,
            error: function (xhr) { console.error("Error fetching category data:", xhr.responseText); }
        });
    }

    function getMsmeCategoryCountOnboardedPiechart(response) {
        const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

        Highcharts.chart('pieChart', {
            chart: {
                type: 'pie',
                events: {
                    render() {
                        const chart = this, series = chart.series[0];
                        if (!chart.customLabel) {
                            chart.customLabel = chart.renderer.label('').css({ textAnchor: 'middle' }).add();
                        }
                        chart.customLabel.attr({
                            text: `Total<br/><strong>${totalCount}</strong>`,
                            x: series.center[0] + chart.plotLeft,
                            y: series.center[1] + chart.plotTop - 10
                        }).css({ fontSize: `${series.center[2] / 12}px` });
                    }
                }
            },
            title: { text: "" },
            tooltip: { pointFormat: '<b>{point.count}</b> ({point.percentage:.1f}%)' },
            plotOptions: {
                series: {
                    innerSize: '75%',
                    borderRadius: 8,
                    dataLabels: [
                        { enabled: true, distance: 15, format: '{point.name}' },
                        { enabled: true, distance: -20, format: '{point.percentage:.0f}%' }
                    ]
                }
            },
            series: [{ name: 'Categories', colorByPoint: true, data: response.data }]
        });
    }


    function getMsmePercetageByGender(isDateSearch = false){
        $.ajax({
            url: "{{ url('get-msme-gender-percentege') }}",
            type: "GET",
            data: getFilterParams(isDateSearch),
            dataType: "json",
            success: getMsmePercentageGenderWisePiechart,
            error: function (xhr) { console.error(xhr.responseText); }
        });
    }

    function getMsmePercentageGenderWisePiechart(response) {
        const chartData = response.data.map(item => ({
            name: item.name,
            y: item.percentage,
            count: item.count
        }));

        Highcharts.chart('pieChartPercentage', {
            chart: { type: 'pie' },
            title: { text: '' },
            tooltip: { pointFormat: '<b>{point.y:.1f}%</b> ({point.count} MSMEs)' },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: [{
                        enabled: true,
                        distance: 20
                    }, {
                        enabled: true,
                        distance: -40,
                        format: '{point.name}: {point.y:.1f}% ({point.count})',
                        style: { fontSize: '0.9em', textOutline: 'none', opacity: 0.7 }
                    }]
                }
            },
            series: [{ name: 'Percentage', colorByPoint: true, data: chartData }]
        });
    }


    function topPerformerSnpBarChart(isDateSearch = false) {
        $.ajax({
            url: "{{ url('get-top-performer-snps') }}",
            type: 'GET',
            data: getFilterParams(isDateSearch),
            success: function (response) {
                const data = response.data || [];
                const categories = data.map(item => item.snp_name);
                const values = data.map(item => item.total);

                Highcharts.chart('top-performer-snp', {
                    chart: { type: 'bar', backgroundColor: '#fff', borderRadius: 5 },
                    title: { text: '' },
                    xAxis: { categories },
                    yAxis: { min: 0, title: null },
                    legend: { enabled: false },
                    plotOptions: {
                        bar: {
                            dataLabels: { enabled: true, style: { fontSize: '12px' } },
                            borderRadius: 5
                        }
                    },
                    series: [{ name: 'Onboarded MSE', color: '#1E88E5', data: values }]
                });
            },
            error: function () { console.error('Failed to load SNP data.'); }
        });
    }


    function loadClaimSummary(isDateSearch = false) {
      const params = getFilterParams(isDateSearch);
        $.ajax({
            url: "{{ url('get-claim-counts-by-status') }}",
            type: "GET",
            data: params,
            dataType: "json",
            success: function (response) {
                
                if (response.success) {
                    if(response.type_for == 1){
                        renderClaimCardForCA(response.data);
                    }else{
                        renderClaimCards(response.data);
                    }
                    
                } else {
                    $('#claim-summary').html('<p class="text-muted">No claim data available.</p>');
                }
            },
            error: function (xhr) {
                console.error("Error fetching claim summary:", xhr.responseText);
            }
        });
    }


    function renderClaimCards(data) {
        const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";

        let html = `<div class="row doc-cards">`;

        data.forEach((item, index) => {
            const typeTitle = item.claimTypeName || (item.claimType || '').replace(/-/g, ' ');
            const total = item.totalStatusCount ?? 0;
            //console.log(item);
            const approved = item.approvedCount ?? 0;
            const pending = item.pendingCount ?? 0;
            const reverted = item.revertedCount ?? 0;
            const rejected = item.rejectedCount ?? 0;
            const amount = item.totalAmount ?? 0;
            const completedAmount = item.paymentCompletedCount ?? 0;

            html += `
                <div class="col-lg-4 common-claim-card">
                        <div class="card w-100">
                            <div class="card-body d-flex gap-3">
                                <div class="right text-start w-100">
                                    <div class="top-header-card d-flex align-items-center justify-content-between">
                                        <div class="left align-items-center icon type-3">
                                            <img src="${iconSrc}" alt="${typeTitle}">
                                        </div>
                                        <span>
                                            <p class="mb-1   ">${typeTitle}</p>
                                            <h5><span id="total_application">${total}</span></h5>
                                        </span>
                                    </div>

                                    <div class="card-detail">
                                        <ul>
                                            <li>
                                                <span>Approved</span>
                                                <span>${approved}</span>
                                            </li>
                                            <li>
                                                <span>Pending</span>
                                                <span>${pending}</span>
                                            </li>
                                            <li>
                                                <span>Reverted</span>
                                                <span>${reverted}</span>
                                            </li>
                                            ${!isCA ? `
                                            <li>
                                                <span>Rejected</span>
                                                <span>${rejected}</span>
                                            </li>` : ''}
                                            <li>
                                                <span>Payment Completed</span>
                                                <span>${completedAmount}</span>
                                            </li>
                                            <li class="total_amount">
                                                <span>Total Amount</span>
                                                <span>₹${Number(amount).toLocaleString()}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            `;
        });

        html += `</div>`;

        // (Kept small styling snippet you had earlier so cards behave same)
        html += `
            <style>
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
                }
                .card h4 {
                    font-size: 28px;
                }
                .small {
                    font-size: 13px;
                }
                .doc-cards { margin-left: 0; margin-right: 0; }
                .onboarded-card .card { border-radius: 8px; }
                .top-header-card p { font-weight: 600; margin: 0; }
                .card-detail ul { list-style: none; padding: 0; margin: 0; }
                .card-detail ul li { display:flex; justify-content:space-between; padding:6px 0; border-bottom: 1px dashed #eee; }
                .card-detail ul li:last-child { border-bottom: none; }
            </style>
        `;

        $('#claim-summary').html(html);
    }

    // rendor claim card for ca

    function renderClaimCardForCA(data) {
        //console.log(data);
        const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";

        let html = `<div class="row doc-cards">`;

        Object.entries(data).forEach(([key, item]) => {
            
            const typeTitle = item.name || (item.name || '').replace(/-/g, ' ');
            const total = item.total ?? 0;
            const approved = item.approved ?? 0;
            const pending = item.pending ?? 0;
            const reverted = item.reverted ?? 0;
            const rejected = item.rejected ?? 0;
            const amount = item.totalAmount ?? 0;

            html += `
                <div class="col-lg-4 common-claim-card">
                    <div class="card w-100">
                        <div class="card-body d-flex gap-3">
                            <div class="right text-start w-100">
                                <div class="top-header-card d-flex align-items-center justify-content-between">
                                    <div class="left align-items-center icon type-3">
                                        <img src="${iconSrc}" alt="${typeTitle}">
                                    </div>
                                    <span>
                                        <p class="mb-1">${typeTitle}</p>
                                        <h5><span id="total_application">${total}</span></h5>
                                    </span>
                                </div>

                                <div class="card-detail">
                                    <ul>
                                        <li>
                                            <span>Approved</span>
                                            <span>${approved}</span>
                                        </li>
                                        <li>
                                            <span>Pending</span>
                                            <span>${pending}</span>
                                        </li>
                                        <li>
                                            <span>Reverted</span>
                                            <span>${reverted}</span>
                                        </li>
                                        ${!isCA ? `
                                        <li>
                                            <span>Rejected</span>
                                            <span>${rejected}</span>
                                        </li>
                                        <li class="total_amount">
                                            <span>Total Amount</span>
                                            <span>₹${Number(amount).toLocaleString()}</span>
                                        </li>` : ''}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += `</div>`;

        html += `
            <style>
                .card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
                }
                .card h4 {
                    font-size: 28px;
                }
                .small {
                    font-size: 13px;
                }
                .doc-cards { margin-left: 0; margin-right: 0; }
                .onboarded-card .card { border-radius: 8px; }
                .top-header-card p { font-weight: 600; margin: 0; }
                .card-detail ul { list-style: none; padding: 0; margin: 0; }
                .card-detail ul li { display:flex; justify-content:space-between; padding:6px 0; border-bottom: 1px dashed #eee; }
                .card-detail ul li:last-child { border-bottom: none; }
            </style>
        `;

        $('#claim-summary').html(html);
    }
</script>
@endsection
