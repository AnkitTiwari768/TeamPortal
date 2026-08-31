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
    <div class="container-fluid p-4">
        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                    type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                    MSE Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims"
                    type="button" role="tab" aria-controls="claims" aria-selected="false">
                    Claim Dashboard
                </button>
            </li>
        </ul>

        <div class="tab-content mt-4" id="dashboardTabsContent">
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h1 class="page-title">MSE Dashboard</h1>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-lg-12 mb-4">

                        <div class="row doc-cards mse-cards">
                            <div class="col-lg-3 open-msme-card">
                                <a href="{{ url('open-msme') }}" class="">
                                    <div class="card w-100">
                                        <div class="card-body d-flex gap-3">
                                            <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-1">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>Open MSEs</p>
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
                            <div class="col-lg-3 msme-select-card">
                                <a href="{{ url('msme-chossen-me') }}" class="">
                                    <div class="card w-100">
                                        <div class="card-body d-flex gap-3">
                                            <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-2">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>MSEs selected ME</p>
                                                    <h5><span id="total_application">{{ $msmeCounts['total']['chossen'] }}</span></h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul >
                                                    @foreach($msmeCounts['major_activities'] as $activity => $counts)
                                                        <li >
                                                            <span>{{ $activity }}</span> 
                                                            <span>{{ $counts['chossen'] }}</span> 
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
                                <a href="{{ url('onboarded-msme') }}" class="">
                                    <div class="card w-100">
                                        <div class="card-body d-flex gap-3">
                                            <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-3">
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
                            <div class="col-lg-3 total-onboarded-card ">
                                <a href="{{ url('onboarded-msme') }}" class="">
                                    <div class="card w-100">
                                        <div class="card-body d-flex gap-3">
                                            <div class="right text-start w-100">
                                            <div class="top-header-card">
                                                <div class="left align-items-center icon type-3">
                                                    <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                </div>
                                                <span>
                                                    <p>Total Onboarded MSEs</p>
                                                    <h5><span id="total_application">{{ $msmeCounts['total']['onboarded'] }}</span></h5>
                                                </span>
                                            </div>
                                            <div class="card-detail">
                                                <ul >
                                                    @foreach($msmeCountsByGender['gender_wise'] as $gender => $count)
                                                        <li>
                                                            <span class="text-capitalize">{{ $gender }}</span>
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
                        </div>

                    </div>
                </div>
               
                <div class="row mb-4 d-flex">
                     <div class="col-lg-5 ">
                                <div class="card common-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between main-charts-headding-wrap">
                                            <h3>MSE Onboarded Percentage</h3>
                                        </div>
                                        
                                        
                                        <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                        <figure class="highcharts-figure">
                                            <div id="pieChartPercentage"></div>
                                            
                                        </figure>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>


                        <div class="col-lg-7 ">
                            
                            <div class="card common-card h-100">
                                <div class="card-body ">
                                     <div class="d-flex justify-content-between main-charts-headding-wrap">
                                            <h3>MSE's By Top 10 Categories</h3>
                                        </div>
                                    
                                    <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                    <figure class="highcharts-figure">
                                        <div id="pieChart"></div>
                                        
                                    </figure>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>

                </div>

                <div class="row mb-4 d-flex">
                    <div class="col-lg-6">
                        <div class="card common-card">
                            <div class="card-body">                             

                                <div class="graph-area mt-2">
                                <figure class="highcharts-figure">
                                    <div class="d-flex justify-content-between main-charts-headding-wrap">
                                        <div>
                                             <h3>MSE Onboarded</h3>
                                             <span> Visual representation of MSE Onboarded with the system </span>
                                        </div>                                       
                                        <div class="year-filter">
                                            {!! Form::select('year',year_list(),$row['year'] ?? null, ['class' => 'form-select', 'id' => 'year', 'onchange' => 'getMappingMsmeCount']) !!}
                                        </div>                                       
                                       

                                    </div>

                                    
                                    <div id="bargraph_msme_onboarded"></div>
                                    
                                </figure>
                                </div>


                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        <div class="card common-card">
                             <div class="card-body ">
                                <div class="d-flex justify-content-between main-charts-headding-wrap">
                                    <h3>MSME Onboarding Analysis (Total Onboarded: 500)</h3>
                                </div>
                                <div id="bargraph_static_onboarded"></div>

                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="row mb-4 d-flex">
                    <div class="col-lg-12">
                        <div class="card common-card">
                            <div class="card-body">

                                <div class="d-flex justify-content-between main-charts-headding-wrap">
                                        <div>
                                             <h3>Statewise MSME</h3>
                                             <span>Visual representation of MSE Onboarded with the system </span>
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
       
            <div class="tab-pane fade" id="claims" role="tabpanel" aria-labelledby="claims-tab">
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
                                    <h6 class="mt-2   ">Total Claims</h6>
                                    <h4 class="fw-bold text-primary">{{ $claimCounts['totalClaims'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class="stat-card shadow-sm" style="background: #e0fbe5;" >
                                    <i class="bi bi-check2-circle text-success fs-3"></i>
                                    <h6 class="mt-2   ">Total Approved Claims</h6>
                                    <h4 class="fw-bold text-success">{{ $claimCounts['approvedCount'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class=" stat-card shadow-sm" style="background:#fffbea;">
                                    <i class="bi bi-exclamation-circle text-warning fs-3"></i>
                                    <h6 class="mt-2   ">Total Reverted Claims</h6>
                                    <h4 class="fw-bold text-warning">{{ $claimCounts['revertedCount'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class="  stat-card shadow-sm" style="background:#fff7f0;">
                                    <i class="bi bi-clock-history text-warning fs-3"></i>
                                    <h6 class="mt-2   ">Total Pending Claims</h6>
                                    <h4 class="fw-bold text-warning">{{ $claimCounts['pendingCount'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class="stat-card shadow-sm " style="background:#fff2f2;">
                                    <i class="bi bi-x-circle text-danger fs-3"></i>
                                    <h6 class="mt-2   ">Total Rejected Claims</h6>
                                    <h4 class="fw-bold text-danger">{{ $claimCounts['rejectedCount'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class="stat-card shadow-sm " style="background:#eef6ff;">
                                    <i class="bi bi-file-earmark-text text-primary fs-3"></i>
                                    <h6 class="mt-2   ">Total Draft</h6>
                                    <h4 class="fw-bold text-primary">{{ $claimCounts['draftCount'] }}</h4>
                                </div>
                            </div>

                            <div class="col-lg-3" >
                                <div class="stat-card shadow-sm" style="background:#f3fff6;">
                                    <i class="bi bi-check2-circle text-success fs-3"></i>
                                    <h6 class="mt-2">Total Payment Completed</h6>
                                    <h4 class="fw-bold text-success">{{ $claimCounts['paymentCompletedCount'] }}</h4>
                                </div>
                            </div>
                            
                        </div>
                    </div>
            </div>
            
    </div>
@endsection


@section('js')
<script src="https://code.highcharts.com/highcharts.js"></script>
<script>

    $(document).ready(function () { 
        getMappingMsmeCount();
        $('#year').on('change', getMappingMsmeCount);
		
		getMsmeStateWiseCount();
		$('#syear').on('change', getMsmeStateWiseCount);
		getMsmeCategoryCountOnboarded();
		getMsmeByPercetageSellerAndCatalogues();
        $('#claims-tab').on('shown.bs.tab', function () {
            if (!$('#claim-summary').hasClass('loaded')) {
                loadClaimSummary();
            }
        });
        getStaticBarChart();
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
            title: { text: ''},            
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
            title: { text: ''},          
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
                                    color: 'var(--highcharts-neutral-color-100, #000000ff)',
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
                text: ''
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


    function getMsmePercentageOnboardedPiechart(response) {
        const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

        const chartData = response.data.slice(0, 2).map(item => ({
            name: item.name,
            y: item.count
        }));

        Highcharts.chart('pieChartPercentage', {
            chart: {
                type: 'pie',
                events: {
                    render() {
                        const chart = this;
                        const series = chart.series[0];
                    }
                }
            },
            title: {
                text: ''
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %'
                    }
                }
            },
            series: [{
                name: 'Percentage',
                colorByPoint: true,
                data: chartData
            }]
        });
    }


    function loadClaimSummary() {
        $.ajax({
            url: "{{ url('get-claim-counts-by-status') }}",
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (response.success && response.data.length > 0) {
                    renderClaimCards(response.data);
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
            const total = item.totalClaims ?? 0;
            const approved = item.approvedCount ?? 0;
            const pending = item.pendingCount ?? 0;
            const reverted = item.revertedCount ?? 0;
            const rejected = item.rejectedCount ?? 0;
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
                                            <li>
                                                <span>Rejected</span>
                                                <span>${rejected}</span>
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





   function getStaticBarChartGraph(response) {
    Highcharts.chart('bargraph_static_onboarded', {
        chart: {
            type: 'column',
            backgroundColor: '#ffffff',
            borderRadius: 8,
            spacingTop: 50,
            spacingBottom: 20
            
        },
        title: {
            text: '',
            style: { fontSize: '16px', fontWeight: 'bold' }
        },
        xAxis: {
            type: 'category',
            title: { text: '' },
            labels: { style: { fontSize: '12px' } }
        },
        yAxis: {
            min: 0,
            title: { text: 'Number of MSMEs' },
            labels: { style: { fontSize: '12px' } },
            gridLineDashStyle: 'Dash'
        },
        legend: { enabled: false },
        tooltip: {
            pointFormat: '<b>{point.y}</b> MSMEs'
        },
        plotOptions: {
            column: {
                colorByPoint: true,
                borderRadius: 3,
                groupPadding: 0.05
            }
        },
        series: [{
            name: 'MSME Count',
            data: response,
            color: '#f6a21e'
        }]
    });
}

function getStaticBarChart() {
    // Static data fallback (for testing before dynamic data)
    const staticData = [
        { name: 'Onboarded within 30 Days', y: 320 },
        { name: 'Onboarded after 30 Days', y: 180 }
    ];

    // Call chart with static data initially
    getStaticBarChartGraph(staticData);

    // Uncomment below to use Laravel API (once backend ready)
    /*
    $.ajax({
        url: "{{ url('get-static-bar-chart') }}",
        type: "GET",
        success: function (response) {
            getStaticBarChartGraph(response);
        },
        error: function (xhr) {
            console.error("Error fetching data:", xhr.responseText);
        }
    });
    */
}


/*function getMsmeCategoryCountOnboardedPiechart(response){  
	Highcharts.chart('pieChart', {
		chart: {
			type: 'pie',
			// height: 650,
		},
		
		title: {
			text: 'MSE`s By Categories'
		},
		
		tooltip: {
			// Show count + percentage on hover
			pointFormatter: function () {
				return '<b>' + this.count + '</b> (' + this.percentage.toFixed(1) + '%)';
			}
		},
		plotOptions: {
			pie: {
				//showInLegend: true,
				dataLabels: { enabled: false }
			}
		},
		legend: {
			labelFormatter: function () {
				// Legend shows: Chrome (1234 - 74.8%)
				return this.name + ' (' + this.count + ' - ' + this.percentage.toFixed(1) + '%)';
			}
		},
		
		series: [{
			name: 'Categories',
			colorByPoint: true,
			data:response.data
		}]
	});
}*/
    
</script>
@endsection
