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
                                            <h5><span id="total_application">{{ $msmeForMeCounts['total_msme'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul >
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
                                            <h5><span id="total_application">{{ $msmeCounts['total']['option1'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul >
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['option1'] }}</span> 
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
                                            <h5><span id="total_application">{{ $msmeCounts['total']['option2'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul >
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['option2'] }}</span> 
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
                                            <h5><span id="total_application">{{ $msmeCounts['total']['onboarded'] }}</span></h5>
                                        </span>
                                    </div>
                                    <div class="card-detail">
                                        <ul >
                                            @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                <li >
                                                    <span>{{ $activity }}</span> 
                                                    <span>{{ $counts['onboarded'] }}</span> 
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
                                <h5 class="fw-bold mb-0">{{ $snpCount['totalSnp'] }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Verification Pending </p>
                                <span class="info-value">{{ $snpCount['pendingSnp'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Verified SNPs</p>
                                <span class="info-value">{{ $snpCount['approvedSnp'] }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Rejected SNPs</p>
                                <span class="info-value">{{ $snpCount['rejectedSnp'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Reverted SNPs</p>
                                <span class="info-value">{{ $snpCount['revertedSnp'] }}</span>
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
                                <h5 class="fw-bold mb-0">{{ $bnpCount['totalBnp'] }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Verification Pending</p>
                                <span class="info-value">{{ $bnpCount['pendingBnp'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Verified BNPs</p>
                                <span class="info-value">{{ $bnpCount['approvedBnp'] }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Rejected BNPs</p>
                                <span class="info-value">{{ $bnpCount['rejectedBnp'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Reverted BNPs</p>
                                <span class="info-value">{{ $bnpCount['revertedBnp'] }}</span>
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
                                <h5 class="fw-bold mb-0">{{ $claimCounts['totalClaims'] }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Total Approved Claims</p>
                                <span class="info-value">{{ $claimCounts['approvedCount'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Total Reverted Claims</p>
                                <span class="info-value">{{ $claimCounts['revertedCount'] }}</span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                <p class="info-label">Total Pending Claims</p>
                                <span class="info-value">{{ $claimCounts['pendingCount'] }}</span>
                                </div>
                                <div class="col-6">
                                <p class="info-label">Total Rejected Claims</p>
                                <span class="info-value">{{ $claimCounts['rejectedCount'] }}</span>
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
                                        <h3> MSME Onboarded Percentage</h3>
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
                                <div class="year-filter">
                                {!! Form::select('year',year_list(),$row['year'] ?? null, ['class' => 'form-select', 'id' => 'year', 'onchange' => 'getMappingMsmeCount']) !!}
                                </div>
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
                                            <span> Visual representation of MSE Onboarded with the system </span>
                                        </div>
                                        <div class="year-filter">
                                        {!! Form::select('year',year_list(),$row['year'] ?? null, ['class' => 'form-select', 'id' => 'year', 'onchange' => 'getMappingMsmeCount']) !!}
                                        </div>
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
                                <h4 class="fw-bold text-primary">{{ !hasRole('ca') ? $claimCounts['totalClaims'] : $caCountSummaryBatchWise['totalBatch'] }}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm" style="background: #e0fbe5;">
                                <i class="bi bi-check2-circle text-success fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Approved Claims' : 'Total Approved Batch'}}</h6>
                                <h4 class="fw-bold text-success">{{ !hasRole('ca') ? $claimCounts['approvedCount'] : $caCountSummaryBatchWise['approvedBatch']}}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class=" stat-card shadow-sm" style="background:#fffbea;">
                                <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Reverted Claims' : 'Total Revert Batch'}}</h6>
                                <h4 class="fw-bold text-warning">{{ !hasRole('ca') ? $claimCounts['revertedCount'] : $caCountSummaryBatchWise['pendingBatch'] }}</h4>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                                <i class="bi bi-clock-history text-warning fs-3"></i>
                                <h6 class="mt-2">{{ !hasRole('ca') ? 'Total Pending Claims' : 'Total Pending Batch'}}</h6>
                                <h4 class="fw-bold text-warning">{{ !hasRole('ca') ? $claimCounts['pendingCount'] : $caCountSummaryBatchWise['pendingBatch']}}</h4>
                            </div>
                        </div>
                        @if(!hasRole('ca'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#fff2f2;">
                                <i class="bi bi-x-circle text-danger fs-3"></i>
                                <h6 class="mt-2">Total Rejected Claims</h6>
                                <h4 class="fw-bold text-danger">{{ $claimCounts['rejectedCount'] }}</h4>
                            </div>
                        </div>
                        @if(hasRole('snp'))
                        <div class="col-lg-3">
                            <div class="stat-card shadow-sm " style="background:#eef6ff;">
                                <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                <h6 class="mt-2">Total Draft</h6>
                                <h4 class="fw-bold text-primary">{{ $claimCounts['draftCount'] }}</h4>
                            </div>
                        </div>
                        @endif
						
						  @if(!hasRole('ca'))

							<div class="col-lg-3">
								<div class="stat-card shadow-sm" style="background:#f3fff6;">
									<i class="bi bi-check2-circle text-success fs-3"></i>
									<h6 class="mt-2">Total Payment Completed</h6>
									<h4 class="fw-bold text-success">{{ $claimCounts['paymentCompletedCount'] }}</h4>
								</div>
							</div>
						 @endif
                        @endif
                    </div>
                </div>
            </div>


    </div>
        @endsection


        @section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>
 var isCA = {{ hasRole('ca') ? 'true' : 'false' }};
    $(document).ready(function () { 
        if (!isCA) {
            getMappingMsmeCount();
            $('#year').on('change', getMappingMsmeCount);
            
            getMsmeStateWiseCount();
            $('#syear').on('change', getMsmeStateWiseCount);
            getMsmeCategoryCountOnboarded();
            getMsmeByPercetageSellerAndCatalogues();
            topPerformerSnpBarChart();
        }
        $('#claims-tab').on('shown.bs.tab', function () {
            if (!$('#claim-summary').hasClass('loaded')) {
                loadClaimSummary();
            }
        });

        if (isCA && !$('#claim-summary').hasClass('loaded')) {
         loadClaimSummary();
        }
        //topPerformerSnpBarChart();
        
    });
	
	
    //for msme onboarded count
	 function getMappingMsmeCount() {
        let year = $('#year').val();
        $.ajax({
            url: "{{ url('get-mapping-msme-count') }}",
            type: "GET",
            data: { year: year },
            success: function (response) {
                //chart.series[0].setData(response);
				getMappingMsmeCountBarGraph(response);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }
	
	function getMappingMsmeCountBarGraph(response){
		Highcharts.chart('bargraph_msme_onboarded', {
            chart: { type: 'column' },
            title: { text: '' },          
            xAxis: { type: 'category' },
            yAxis: { min: 0, title: { text: 'MSME Count' } },
            legend: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> MSMEs' },
            series: [{
                name: 'MSME Count',
                colorByPoint: true,
                groupPadding: 0,
				data:response
            }]
        });
	}
	
	
    //for msme onboarded state wise count
	 function getMsmeStateWiseCount() {
        let syear = $('#syear').val();
        $.ajax({
            url: "{{ url('get-msme-state-wise-count') }}",
            type: "GET",
            data: { syear: syear },
            success: function (response) {
                //chart.series[0].setData(response);
				getMsmeStateWiseCountBarGraph(response);
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }
	
	function getMsmeStateWiseCountBarGraph(response){
		Highcharts.chart('bargraph_msme_state_wise_onboarded', {
            chart: { type: 'column' },
            title: { text: '' },           
            xAxis: { type: 'category' },
            yAxis: { min: 0, title: { text: 'MSME Count' } },
            legend: { enabled: false },
            tooltip: { pointFormat: '<b>{point.y}</b> MSMEs' },
            series: [{
                name: 'MSME Count',
                colorByPoint: true,
                groupPadding: 0,
				data:response
            }]
        });
	}

    //for msme Categories onboarded
   function getMsmeCategoryCountOnboarded(){
	   $.ajax({
			url: "{{ url('get-msme-category-count-onboarded') }}",
			type: "GET",
			dataType: "json",
			success: function (response) {
				getMsmeCategoryCountOnboardedPiechart(response);
			}
			
		});
   }


    function getMsmeCategoryCountOnboardedPiechart(response) {
        const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

        Highcharts.chart('pieChart', {
            chart: {
                type: 'pie',
                custom: {},
                events: {
                    render() {
                        const chart = this,
                            series = chart.series[0];
                        let customLabel = chart.options.chart.custom.label;

                        if (!customLabel) {
                            customLabel = chart.options.chart.custom.label =
                                chart.renderer.label(
                                    'Total<br/><strong>' + totalCount + '</strong>'
                                )
                                .css({
                                    color: 'var(--highcharts-neutral-color-100, #000)',
                                    textAnchor: 'middle'
                                })
                                .add();
                        }

                        const x = series.center[0] + chart.plotLeft,
                            y = series.center[1] + chart.plotTop - (customLabel.attr('height') / 2);

                        customLabel.attr({ x, y });
                        customLabel.css({ fontSize: `${series.center[2] / 12}px` });
                    }
                }
            },
            title: {
                text: ""
            },
            tooltip: {
                pointFormat: '<b>{point.count}</b> ({point.percentage:.1f}%)'
            },
            legend: {
                enabled: false
            },
            plotOptions: {
                series: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    borderRadius: 8,
                    innerSize: '75%',
                    dataLabels: [{
                        enabled: true,
                        distance: 15,
                        format: '{point.name}',
                        allowOverlap: true, 
                        style: {
                            fontSize: '0.8em',
                            textOutline: 'none'
                        }
                    }, {
                        enabled: true,
                        distance: -20, 
                        format: '{point.percentage:.0f}%',
                        allowOverlap: true, 
                        style: {
                            fontSize: '0.8em',
                            textOutline: 'none'
                        }
                    }]
                }
            },
            series: [{
                name: 'Categories',
                colorByPoint: true,
                innerSize: '75%',
                data: response.data
            }]
        });
    }


    function getMsmePercentageOnboardedPiechart(response) {
            // Calculate total count
            const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

            // Map data for chart
            // const chartData = response.data.map(item => ({
            //     name: item.name,
            //     y: item.count
            // }));

            // Highcharts.chart('pieChartPercentage', {
            //     chart: {
            //         type: 'pie',
            //         backgroundColor: '#fff',
            //         zooming: {
            //             type: 'xy'
            //         },
            //         panning: {
            //             enabled: true,
            //             type: 'xy'
            //         },
            //         panKey: 'shift'
            //     },
            //     title: {
            //         text: 'MSME Onboarded Percentage',
            //         style: {
            //             fontSize: '16px',
            //             fontWeight: 'bold'
            //         }
            //     },
            //     subtitle: {
            //         text: 'Showing onboarded MSMEs by category'
            //     },
            //     tooltip: {
            //         pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br>Count: {point.y}'
            //     },
            //     plotOptions: {
            //         pie: {
            //             allowPointSelect: true,
            //             cursor: 'pointer',
            //             colorByPoint: true,
            //             dataLabels: [{
            //                 enabled: true,
            //                 distance: 20,
            //                 format: '<b>{point.name}</b>'
            //             }, {
            //                 enabled: true,
            //                 distance: -40,
            //                 format: '{point.percentage:.1f}%',
            //                 style: {
            //                     fontSize: '1.1em',
            //                     textOutline: 'none',
            //                     opacity: 0.7
            //                 },
            //                 filter: {
            //                     operator: '>',
            //                     property: 'percentage',
            //                     value: 5
            //                 }
            //             }]
            //         }
            //     },
            //     series: [{
            //         name: 'Percentage',
            //         data: chartData,
            //         slicedOffset: 20
            //     }],
            //     credits: {
            //         enabled: false
            //     },
            //     legend: {
            //         enabled: true,
            //         align: 'center',
            //         verticalAlign: 'bottom'
            //     }
            // });


            Highcharts.chart('pieChartPercentage', {
            chart: {
                type: 'pie',
                zooming: {
                    type: 'xy'
                },
                panning: {
                    enabled: true,
                    type: 'xy'
                },
                panKey: 'shift'
            },
            title: {
                text: ''
            },
            tooltip: {
                valueSuffix: '%'
            },
            // subtitle: {
            //     text:
            //     'Source:<a href="https://www.mdpi.com/2072-6643/11/3/684/htm" target="_default">MDPI</a>'
            // },
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
                        format: '{point.percentage:.1f}%',
                        style: {
                            fontSize: '1.2em',
                            textOutline: 'none',
                            opacity: 0.7
                        },
                        filter: {
                            operator: '>',
                            property: 'percentage',
                            value: 10
                        }
                    }]
                }
            },
            series: [
                {
                    name: 'Percentage',
                    colorByPoint: true,
                    data: [
                        {
                            name: 'Men',
                            y: 55.02
                        },
                        {
                            name: 'Women',
                            sliced: true,
                            selected: true,
                            y: 26.71
                        },
                        {
                            name: 'Others',
                            y: 1.09
                        }
                    ]
                }
            ]
        });

    }

    
    function getMsmeByPercetageSellerAndCatalogues(){
        $.ajax({
                url: "{{ url('get-msme-percantege-seller-and-catalogues') }}",
                type: "GET",
                dataType: "json",
                success: function (response) {
                    getMsmePercentageOnboardedPiechart(response);
                }
                
            });
    }


    function topPerformerSnpBarChart() {
        $.ajax({
            url: "{{ url('get-top-performer-snps') }}",
            type: 'GET',
            success: function (response) {
                const data = response.data || [];

                // Extract names and totals
                const categories = data.map(item => item.snp_name);
                const values = data.map(item => item.total);

                Highcharts.chart('top-performer-snp', {
                    chart: {
                        type: 'bar',
                        backgroundColor: '#ffffff',
                        borderRadius: 5,
                    },
                    title: {
                        text: '',
                        align: 'center',
                        style: {
                            fontSize: '16px',
                            color: '#333333'
                        }
                    },
                    xAxis: {
                        categories: categories,
                        title: { text: null },
                        gridLineWidth: 0
                    },
                    yAxis: {
                        min: 0,
                        title: { text: null },
                        gridLineWidth: 0,
                        labels: {
                            overflow: 'justify'
                        }
                    },
                    // tooltip: {
                    //     valueSuffix: ' MSE'
                    // },
                    plotOptions: {
                        bar: {
                            dataLabels: {
                                enabled: true,
                                style: {
                                    color: '#333333',
                                    fontSize: '12px',
                                }
                            },
                            borderRadius: 5
                        }
                    },
                    legend: {
                        enabled: true,
                        align: 'center',
                        verticalAlign: 'bottom',
                        symbolHeight: 10,
                        symbolWidth: 10
                    },
                    credits: { enabled: false },
                    series: [{
                        name: 'Onboarded MSE',
                        color: '#1E88E5',
                        data: values
                    }]
                });
            },
            error: function () {
                console.error('Failed to load SNP data.');
            }
        });
    }

    
    function loadClaimSummary() {
        $.ajax({
            url: "{{ url('get-claim-counts-by-status') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.success && response.data.length > 0) {
					if(response.type_for == 1){
                        //console.log('here');
                        renderClaimCardForCA(response.data);
                    }else{
                        renderClaimCards(response.data);
                    }
                    //renderClaimCards(response.data);
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
            console.log(item);
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
