<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <div>
                <h3>MSE Onboarded by Month</h3>
                <span class="text-muted small">Visual representation of MSE Onboarded with the system</span>
            </div>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2"
            data-endpoint="{{ $url ?? url('get-mapping-msme-count') }}" id="msmeOnboardedMonthChartContainer">
            <figure class="highcharts-figure">
                <div id="bargraph_msme_onboarded" style="min-height: 450px;"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    if (typeof window.getMappingMsmeCount === 'undefined') {
        window.getMappingMsmeCount = function (isDateSearch = false) {
            let filterData = {};
            if (typeof window.getFilterParams === 'function') {
                filterData = window.getFilterParams(isDateSearch);
            } else if (typeof getFilterParams === 'function') {
                filterData = getFilterParams(isDateSearch);
            }

            let endpointUrl = document.getElementById('msmeOnboardedMonthChartContainer').getAttribute('data-endpoint');

            $.ajax({
                url: endpointUrl,
                type: "GET",
                data: filterData,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    window.getMsmeOnboardedMonthBarGraph(response);
                },
                error: function (xhr) {
                    console.error("Error fetching msme onboarded data:", xhr.responseText);
                    $('#bargraph_msme_onboarded').html('<div class="alert alert-danger mt-5">Error loading chart data</div>');
                }
            });
        };

        window.getMsmeOnboardedMonthBarGraph = function (response) {
            let chartData = [];
            
            if (Array.isArray(response)) {
                chartData = response;
            } else if (response.data && Array.isArray(response.data)) {
                chartData = response.data;
            }

            if (!chartData || chartData.length === 0) {
                $('#bargraph_msme_onboarded').html('<div class="alert alert-info mt-5">No data available for the selected filters</div>');
                return;
            }

            if (typeof Highcharts === 'undefined') {
                console.error("Highcharts is not loaded.");
                return;
            }

            Highcharts.chart('bargraph_msme_onboarded', {
                chart: {
                    type: 'column',
                    height: 450
                },
                title: {
                    text: undefined
                },
                xAxis: {
                    type: 'category',
                    title: {
                        text: 'Months',
                        style: { fontWeight: 'bold' }
                    },
                    labels: {
                        style: { fontSize: '11px' }
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Number of MSMEs',
                        style: { fontWeight: 'bold' }
                    },
                    allowDecimals: false,
                    gridLineColor: '#e6e6e6'
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}">●</span> <b>{point.name}</b>: <b>{point.y}</b> MSMEs'
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        colorByPoint: true,
                        groupPadding: 0.1,
                        pointPadding: 0.05,
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}',
                            inside: false,
                            verticalAlign: 'bottom',
                            y: -5,
                            style: {
                                fontWeight: 'bold',
                                fontSize: '11px',
                                color: '#000',
                                textOutline: 'none'
                            },
                            crop: false,
                            overflow: 'allow'
                        }
                    }
                },
                series: [{
                    name: 'MSME Count',
                    data: chartData,
                    colors: [
                        '#2c7bb6', '#00a9a6', '#abd9e9', '#fdae61', '#d7191c',
                        '#ffffbf', '#91bfdb', '#fc8d59', '#fee090', '#e0f3f8',
                        '#67a9cf', '#ef8a62'
                    ]
                }],
                credits: {
                    enabled: false
                }
            });
        };
    }
</script>
