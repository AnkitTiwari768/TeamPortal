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
    </style>
    <div class="container-fluid p-4">

        {{-- Filters Section --}}
        @include('dashboard.search')

        <ul class="nav nav-tabs" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard"
                    type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                    MSE Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="claims-tab" data-bs-toggle="tab" data-bs-target="#claims" type="button"
                    role="tab" aria-controls="claims" aria-selected="false">
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
                            <div class="col-lg-4 open-msme-card">
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
                                                        <h5><span id="total_application"
                                                                class="openMsmeForMeCounts_total_msme">{{ $msmeForMeCounts['total_msme'] }}</span>
                                                        </h5>
                                                    </span>
                                                </div>
                                                <div class="card-detail">
                                                    <ul id="ul__open_msmeForMeCounts_major_activities"
                                                        class="open_msmeForMeCounts_major_activities">
                                                        @foreach ($msmeForMeCounts['major_activities'] as $activity => $count)
                                                            <li>
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

                            <div class="col-lg-4 msme-select-card">
                                <a href="{{ url('msme-chossen-me') }}" class="">
                                    <div class="card w-100">
                                        <div class="card-body d-flex gap-3">
                                            <div class="right text-start w-100">
                                                <div class="top-header-card">
                                                    <div class="left align-items-center icon type-2">
                                                        <img src="{{ asset('assets/ffo-admin/img/document-ico.svg') }}">
                                                    </div>
                                                    <span>
                                                        <p>Direct Selection By MSE</p>
                                                        <h5><span id="total_application"
                                                                class="selected_me_msmeCounts_total_chossen">{{ $msmeChoosenCounts['total']['chossen'] }}</span>
                                                        </h5>
                                                    </span>
                                                </div>
                                                <div class="card-detail">
                                                    <ul id="ul__selected_me_msmeCounts_chossen"
                                                        class="selected_me_msmeCounts_major_activities">
                                                        @foreach ($msmeChoosenCounts['major_activities'] as $activity => $counts)
                                                            <li>
                                                                <span>{{ $activity }}</span>
                                                                <span>{{ $counts['chossen'] ?? '' }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-lg-4 onboarded-card">
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
                                                        <h5><span id="total_application"
                                                                class="onboarded_mses_msmeCounts_total_onboarded">{{ $msmeCounts['total']['onboarded'] }}</span>
                                                        </h5>
                                                    </span>
                                                </div>
                                                <div class="card-detail">
                                                    <ul id="ul__onboarded_mses_msmeCounts_onboarded"
                                                        class="msmeCounts_major_activities">
                                                        @foreach ($msmeCounts['major_activities'] as $activity => $counts)
                                                            <li>
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


                            <?php /*
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
                                                        <h5><span id="total_application"
                                                                class="msmeCounts_total_onboarded_mses_gender">{{ $msmeCounts['total']['onboarded'] }}</span>
                                                        </h5>
                                                    </span>
                                                </div>
                                                <div class="card-detail">
                                                    <ul d="ul_msmeCounts_onboarded_mses_gender"
                                                        class="msmeCounts_major_activities_mses_gender">
                                                        @foreach ($msmeCountsByGender['gender_wise'] as $gender => $count)
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
                            </div>*/
                            ?>
                        </div>

                    </div>
                </div>

                <div class="row mb-4 d-flex">
                    <?php /*<div class="col-lg-5 ">
                        <div class="card common-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between main-charts-headding-wrap">
                                    <h3>No. of MSEs Catalogued</h3>
                                </div>


                                <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
                                    <figure class="highcharts-figure">
                                        <div id="pieChartPercentage"></div>

                                    </figure>
                                </div>

                            </div>
                        </div>
                    </div>*/
                    ?>


                    <div class="col-lg-12">

                        <div class="card common-card h-100">
                            <div class="card-body ">
                                <div class="d-flex justify-content-between main-charts-headding-wrap">
                                    <h3>MSE By Top 10 Categories</h3>
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
                                                <h3>MSE Onboarded by Month</h3>
                                                <span> Visual representation of MSE Onboarded with the system </span>
                                            </div>
                                            <!-- <div class="year-filter">
                                                                                                                {!! Form::select('year', year_list(), $row['year'] ?? null, [
                                                                                                                    'class' => 'form-select',
                                                                                                                    'id' => 'year',
                                                                                                                    'onchange' => 'getMappingMsmeCount',
                                                                                                                ]) !!}
                                                                                                            </div>                                        -->


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
                                    <h3>MSME Onboarding (Analysis within 15 days)</h3>
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
                                        <span>Visual Representation of MSEs registered with the SNP </span>
                                    </div>
                                    <!-- <div class="year-filter">
                                                                                                                {!! Form::select('year', year_list(), $row['year'] ?? null, [
                                                                                                                    'class' => 'form-select',
                                                                                                                    'id' => 'year',
                                                                                                                    'onchange' => 'getMappingMsmeCount',
                                                                                                                ]) !!}
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

            <div class="tab-pane fade" id="claims" role="tabpanel" aria-labelledby="claims-tab">
                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h1 class="page-title">Claims</h1>
                    </div>
                </div>

                <div class="col-lg-12 mb-4 mt-3">
                    <div id="claim-summary" class="stat-row"></div>
                </div>

                @include('dashboard.claim-status-card')
            </div>

        </div>
    @endsection


    @section('js')
        <script src="https://code.highcharts.com/highcharts.js"></script>

        @include('dashboard.datepicker')

        <script>
            const isCA = {{ hasRole('ca') ? 'true' : 'false' }};
            $(document).ready(function() {

                getStaticBarChart();

                // $( "#from_date_new" ).datepicker({
                //     dateFormat: "dd-mm-yy",
                //     changeYear: true,
                //     changeMonth: true,
                //     //minDate: new Date(),
                //     onSelect: function(selected) {
                //     $("#to_date_new").datepicker("option","minDate", selected)
                //     },
                //     onClose: function(selected) {
                //         $("#reset_btn").show();
                //     }
                // });

                // $( "#to_date_new" ).datepicker({
                //     dateFormat: "dd-mm-yy",
                //     changeYear: true,
                //     changeMonth: true, 
                //     minDate: new Date(),
                //     onSelect: function(selected) {
                //     $("#from_date_new").datepicker("option","maxDate", selected); 
                //     },
                //     onClose: function(selected) {
                //     $("#reset_btn").show();
                //     }
                // }); 

                // applyYearRestriction();

                // $('#year').on('change', function () {
                //     const selectedYear = $(this).val();

                //     if (!selectedYear) {
                //         $('#from_date_new, #to_date_new').datepicker('option', {
                //             yearRange: 'c-10:c+10'
                //         });
                //         return;
                //     }

                //     const defaultDate = new Date(selectedYear, 0, 1);

                //     $('#from_date_new, #to_date_new').datepicker('option', {
                //         defaultDate: defaultDate,
                //         yearRange: selectedYear + ':' + selectedYear
                //     });

                //     $('#from_date_new, #to_date_new').val('');
                // });

                // $('#from_date_new, #to_date_new').on('focus', function () {
                //     const year = $('#year').val();
                //     if (!year) return;

                //     $(this).datepicker('setDate', new Date(year, 0, 1));
                //     $(this).datepicker('setDate', null);
                // });




                $('#filterReset').on('click', function() {
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

                $('#claims-tab').on('shown.bs.tab', function() {
                    //resetFilters();
                    if (!$('#claim-summary').hasClass('loaded')) {
                        loadClaimSummary(false);
                        $('#claim-summary').addClass('loaded');
                    }
                });

                $('#dashboard-tab').on('shown.bs.tab', function() {
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

            // function applyYearRestriction() {
            //     const selectedYear = $('#year').val();

            //     if (!selectedYear) {
            //         $('#from_date_new, #to_date_new').datepicker('option', {
            //             yearRange: 'c-10:c+10'
            //         });
            //         return;
            //     }

            //     const defaultDate = new Date(selectedYear, 0, 1);

            //     $('#from_date_new, #to_date_new').datepicker('option', {
            //         defaultDate: defaultDate,
            //         yearRange: selectedYear + ':' + selectedYear
            //     });

            //     $('#from_date_new, #to_date_new').val('');
            // }


            // $('#year').on('change', function () {
            //     applyYearRestriction();
            // });


            function getFilterParams(isDateSearch = false, isState = false) {
                let year = $('#year').val();
                let from = $('#from_date_new').val();
                let to = $('#to_date_new').val();
                let params = {
                    year: '',
                    from_date_new: '',
                    to_date_new: ''
                };

                if (year == '') {
                    params = {
                        year: '',
                        from_date_new: '',
                        to_date_new: '',
                        'type': 1
                    };
                }

                //alert();

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
                getMsmeByPercetageSellerAndCatalogues(isDateSearch);
                reloadDashboardCards(isDateSearch);

                loadClaimSummary(isDateSearch);
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
                        style: {
                            fontSize: '16px',
                            fontWeight: 'bold'
                        }
                    },
                    xAxis: {
                        type: 'category',
                        title: {
                            text: ''
                        },
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        }
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'Number of MSMEs'
                        },
                        labels: {
                            style: {
                                fontSize: '12px'
                            }
                        },
                        gridLineDashStyle: 'Dash'
                    },
                    legend: {
                        enabled: false
                    },
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
                const staticData = [{
                        name: 'Onboarded within 15 Days',
                        y: 320
                    },
                    {
                        name: 'Onboarded after 15 Days',
                        y: 180
                    }
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

            function getMappingMsmeCount(isDateSearch = false) {
                //let year = $('#year').val();
                var params = getFilterParams(isDateSearch);
                $.ajax({
                    url: "{{ url('get-mapping-msme-count') }}",
                    type: "GET",
                    data: params,
					beforeSend: function() {
						$("#ajax-loader").show();
					},
                    success: function(response) {
                        //chart.series[0].setData(response);
                        getMappingMsmeCountBarGraph(response);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    },
					complete: function() {
						$("#ajax-loader").hide();
					}
                });
            }

            function getMappingMsmeCountBarGraph(response) {
                Highcharts.chart('bargraph_msme_onboarded', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'MSME Count'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormat: '<b>{point.y}</b> MSMEs'
                    },
                    series: [{
                        name: 'MSME Count',
                        colorByPoint: true,
                        groupPadding: 0,
                        data: response
                    }]
                });
            }

            function getMsmeStateWiseCount(isDateSearch = false) {
                //let syear = $('#syear').val();
                //var params = getFilterParams(isDateSearch);
                $.ajax({
                    url: "{{ url('get-msme-state-wise-count') }}",
                    type: "GET",
                    data: getFilterParams(isDateSearch, true),
                    success: function(response) {
                        //chart.series[0].setData(response);
                        getMsmeStateWiseCountBarGraph(response);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                });
            }

            function getMsmeStateWiseCountBarGraph(response) {
                Highcharts.chart('bargraph_msme_state_wise_onboarded', {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: ''
                    },
                    xAxis: {
                        type: 'category'
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: 'MSME Count'
                        }
                    },
                    legend: {
                        enabled: false
                    },
                    tooltip: {
                        pointFormat: '<b>{point.y}</b> MSMEs'
                    },
                    series: [{
                        name: 'MSME Count',
                        colorByPoint: true,
                        groupPadding: 0,
                        data: response
                    }]
                });
            }

            function reloadDashboardCards(isDateSearch = false) {
                var params = getFilterParams(isDateSearch);

                $.ajax({
                    url: "{{ url('/dashboard') }}",
                    type: 'GET',
                    data: params,
                    success: function(response) {
                        updateDashboardCards(response);
                    },
                    error: function(xhr) {
                        console.error('Error reloading cards:', xhr.responseText);
                    }
                });
            }

            function getMsmeCategoryCountOnboarded(isDateSearch = false) {
                var params = getFilterParams(isDateSearch);
                $.ajax({
                    url: "{{ url('get-msme-category-count-onboarded') }}",
                    type: "GET",
                    dataType: "json",
                    data: params,
                    success: function(response) {
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

                                customLabel.attr({
                                    x,
                                    y
                                });
                                customLabel.css({
                                    fontSize: `${series.center[2] / 12}px`
                                });
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

            function getMsmeByPercetageSellerAndCatalogues(isDateSearch = false) {
                var params = getFilterParams(isDateSearch);
                $.ajax({
                    url: "{{ url('get-msme-percantege-seller-and-catalogues') }}",
                    type: "GET",
                    dataType: "json",
                    data: params,
                    success: function(response) {
                        getMsmePercentageOnboardedPiechart(response);
                    }

                });
            }

            function getMsmePercentageOnboardedPiechart(response) {

                const totalCount = response.data.reduce((acc, item) => acc + item.count, 0);

                const customColors = ['#f6e1ea', '#bce29e'];

                const chartData = response.data.map((item, index) => ({
                    name: item.name,
                    y: item.count,
                    color: customColors[index] || '#ccc'
                }));

                Highcharts.chart('pieChartPercentage', {
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
                                            color: '#000',
                                            textAnchor: 'middle'
                                        })
                                        .add();
                                }

                                const x = series.center[0] + chart.plotLeft,
                                    y = series.center[1] + chart.plotTop - (customLabel.attr('height') / 2);

                                customLabel.attr({
                                    x,
                                    y
                                });
                                customLabel.css({
                                    fontSize: `${series.center[2] / 12}px`
                                });
                            }
                        }
                    },

                    title: {
                        text: ''
                    },

                    tooltip: {
                        pointFormat: '<b>{point.y}</b> ({point.percentage:.1f}%)'
                    },

                    legend: {
                        enabled: false
                    },

                    plotOptions: {
                        pie: {
                            borderRadius: 8,
                            innerSize: '75%',
                            dataLabels: [{
                                    enabled: true,
                                    distance: 15,
                                    format: '{point.name}',
                                    style: {
                                        fontSize: '0.8em',
                                        textOutline: 'none'
                                    }
                                },
                                {
                                    enabled: true,
                                    distance: -20,
                                    format: '{point.percentage:.0f}%',
                                    style: {
                                        fontSize: '0.8em',
                                        textOutline: 'none'
                                    }
                                }
                            ]
                        }
                    },

                    series: [{
                        name: 'Percentage',
                        data: chartData
                    }]
                });
            }

            function updateDashboardCards(data) {
                // ===== Open MSEs =====
                $('.openMsmeForMeCounts_total_msme').text(data.msmeForMeCounts?.total_msme || 0);
                const openMsmeUl = $('.open_msmeForMeCounts_major_activities');
                openMsmeUl.empty();
                if (data.msmeForMeCounts?.major_activities) {
                    $.each(data.msmeForMeCounts.major_activities, function(activity, count) {
                        openMsmeUl.append(`<li><span>${activity}</span><span>${count}</span></li>`);
                    });
                }


                // ===== MSEs Selected ME =====
                $('.selected_me_msmeCounts_total_chossen').text(data.msmeChoosenCounts?.total?.chossen || 0);
                const selectedMeUl = $('.selected_me_msmeCounts_major_activities');
                selectedMeUl.empty();
                if (data.msmeChoosenCounts?.major_activities) {
                    $.each(data.msmeChoosenCounts.major_activities, function(activity, counts) {
                        selectedMeUl.append(`<li><span>${activity}</span><span>${counts.chossen || 0}</span></li>`);
                    });
                }

                // ===== Onboarded MSEs =====
                $('.onboarded_mses_msmeCounts_total_onboarded').text(data.msmeCounts?.total?.onboarded || 0);
                const onboardedUl = $('.msmeCounts_major_activities');
                onboardedUl.empty();
                if (data.msmeCounts?.major_activities) {
                    $.each(data.msmeCounts.major_activities, function(activity, counts) {
                        onboardedUl.append(`<li><span>${activity}</span><span>${counts.onboarded || 0}</span></li>`);
                    });
                }


                // ===== Total Onboarded by Gender =====
                /*$('.msmeCounts_total_onboarded_mses_gender').text(data.msmeCounts?.total?.onboarded || 0);
                const genderUl = $('.msmeCounts_major_activities_mses_gender');
                genderUl.empty();
                if (data.msmeCountsByGender?.gender_wise) {
                    $.each(data.msmeCountsByGender.gender_wise, function(gender, count) {
                        genderUl.append(`<li><span class="text-capitalize">${gender}</span><span>${count}</span></li>`);
                    });
                }*/

                // ===== SNP Counts =====
                // $('.snpCount_totalSnp').text(data.snpCount?.totalSnp || 0);
                // $('.snpCount_pendingSnp').text(data.snpCount?.pendingSnp || 0);
                // $('.snpCount_approvedSnp').text(data.snpCount?.approvedSnp || 0);
                // $('.snpCount_rejectedSnp').text(data.snpCount?.rejectedSnp || 0);
                // $('.snpCount_revertedSnp').text(data.snpCount?.revertedSnp || 0);

                // if ((data.snpCount?.totalSnp || 0) > 0) {
                //     $('.snp-card .explore-link').show();
                // } else {
                //     $('.snp-card .explore-link').hide();
                // }

                // ===== BNP Counts =====
                // $('.bnpCount_totalBnp').text(data.bnpCount?.totalBnp || 0);
                // $('.bnpCount_pendingBnp').text(data.bnpCount?.pendingBnp || 0);
                // $('.bnpCount_approvedBnp').text(data.bnpCount?.approvedBnp || 0);
                // $('.bnpCount_rejectedBnp').text(data.bnpCount?.rejectedBnp || 0);
                // $('.bnpCount_revertedBnp').text(data.bnpCount?.revertedBnp || 0);

                // if ((data.bnpCount?.totalBnp || 0) > 0) {
                //     $('.bnp-card .explore-link').show();
                // } else {
                //     $('.bnp-card .explore-link').hide();
                // }

                // ===== Claim Counts =====
                /*
                					$('.claimCounts_totalClaims').text(data.claimCounts?.totalClaims || 0);
                					$('.claimCounts_approvedCount').text(data.claimCounts?.approvedCount || 0);
                					$('.claimCounts_revertedCount').text(data.claimCounts?.revertedCount || 0);
                					$('.claimCounts_pendingCount').text(data.claimCounts?.pendingCount || 0);
                					$('.claimCounts_rejectedCount').text(data.claimCounts?.rejectedCount || 0);
                					$('.claimCounts_draftCount').text(data.claimCounts?.draftCount || 0);
                					$('.claimCounts_paymentCompletedCount').text(data.claimCounts?.paymentCompletedCount || 0);
                				*/

                /*
                					 $('.totalCliamCounts').text(data.claimCounts?.totalClaims || 0);
                					 $('.claimsApprovedClaimsCount').text(data.claimCounts?.approvedCount || 0);
                					 $('.claimCountsRevertedCount').text(data.claimCounts?.revertedCount || 0);
                					 $('.claimsPendingCounts').text(data.claimCounts?.pendingCount || 0);
                					 $('.claimRejectedCounts').text(data.claimCounts?.rejectedCount || 0);
                				 */


                if ((data.claimCounts?.totalClaims || 0) > 0) {
                    $('.claim-card .explore-link').show();
                } else {
                    $('.claim-card .explore-link').hide();
                }
            }



            function renderClaimCards(data) {
                const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";

                let html = `<div class="row doc-cards">`;

                data.forEach((item, index) => {
                    const typeTitle = item.claimTypeName || (item.claimType || '').replace(/-/g, ' ');
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
                                            <h5><span>${item.totalClaim}</span></h5>
                                        </span>
                                    </div>

                                    <div class="card-detail">
                                        <ul>
                                            <li>
                                                <span>Approved</span>
                                                <span>${item.approvedCount}</span>
                                            </li>
											
                                            <li>
                                                <span>Pending</span>
                                                <span>${item.pendingCount}</span>
                                            </li>
                                           
                                            ${!isCA ? `<li>
                                                        												<span>Rejected</span>
                                                        												<span>${item.rejectedCount}</span>
                                                        											</li>` : ''}
											
                                            <li>
                                                <span>Payment Completed</span>
                                                <span>${item.paymentCompletedCount}</span>
                                            </li>
                                            <li class="total_amount">
                                                <span>Total Amount</span>
                                                <span>₹${Number(item.totalAmount).toLocaleString()}</span>
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
                const iconSrc = "{{ asset('assets/ffo-admin/img/document-ico.svg') }}";
                let html = `<div class="row doc-cards">`;

                Object.entries(data).forEach(([key, item]) => {
                    const typeTitle = item.name || (item.name || '').replace(/-/g, ' ');
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
                                        <h5><span>${item.totalClaim}</span></h5>
                                    </span>
                                </div>

                                <div class="card-detail">
                                    <ul>
                                        <li>
                                            <span>Approved</span>
                                            <span>${item.approvedCount}</span>
                                        </li>
                                        <li>
                                            <span>Pending</span>
                                            <span>${item.pendingCount}</span>
                                        </li>
                                        ${!isCA ? `
                                                        										<li>
                                                        											<span>Rejected</span>
                                                        											<span>${item.rejectedCount}</span>
                                                        										</li>
                                                        										<li class="total_amount">
                                                        											<span>Total Amount</span>
                                                        											<span>₹${Number(item.totalAmount).toLocaleString()}</span>
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

        @include('dashboard.dashboard-js')
    @endsection
