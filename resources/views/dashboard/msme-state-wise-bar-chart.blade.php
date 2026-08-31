<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <div>
                <h3>State Wise Onboarded MSE</h3>
                <span class="text-muted small">Visual Representation of MSEs registered with the SNP</span>
            </div>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2"
            data-endpoint="{{ $url ?? url('get-msme-state-wise-count') }}" id="msmeStateWiseChartContainer">
            <figure class="highcharts-figure">
                <div id="bargraph_msme_state_wise_onboarded" style="min-height: 450px;"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    if (typeof window.getMsmeStateWiseCount === 'undefined') {
        window.getMsmeStateWiseCount = function (isDateSearch = false) {
            let filterData = {};
            if (typeof window.getFilterParams === 'function') {
                filterData = window.getFilterParams(isDateSearch);
            } else if (typeof getFilterParams === 'function') {
                filterData = getFilterParams(isDateSearch);
            }

            let endpointUrl = document.getElementById('msmeStateWiseChartContainer').getAttribute('data-endpoint');

            $.ajax({
                url: endpointUrl,
                type: "GET",
                data: filterData,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    window.getMsmeStateWiseBarGraph(response);
                },
                error: function (xhr) {
                    console.error("Error fetching state-wise MSME data:", xhr.responseText);
                    $('#bargraph_msme_state_wise_onboarded').html('<div class="alert alert-danger mt-5">Error loading chart data</div>');
                }
            });
        };

        window.getMsmeStateWiseBarGraph = function (response) {
            // Check if response is array (direct) or object with data property
            let chartData = [];
            let totalCount = 0;

            if (Array.isArray(response)) {
                // Response is direct array like [{name: "CHHATTISGARH", y: 1}, ...]
                chartData = response;
                totalCount = response.reduce((sum, item) => sum + item.y, 0);
            } else if (response.data && Array.isArray(response.data)) {
                // Response has data property
                chartData = response.data;
                totalCount = response.total || chartData.reduce((sum, item) => sum + item.y, 0);
            } else {
                console.error("Unexpected response format:", response);
                $('#bargraph_msme_state_wise_onboarded').html('<div class="alert alert-info mt-5">No data available for the selected filters</div>');
                return;
            }

            if (!chartData || chartData.length === 0) {
                $('#bargraph_msme_state_wise_onboarded').html('<div class="alert alert-info mt-5">No data available for the selected filters</div>');
                return;
            }

            if (typeof Highcharts === 'undefined') {
                console.error("Highcharts is not loaded.");
                return;
            }

            // Sort data by count descending for better visualization
            chartData.sort((a, b) => b.y - a.y);

            Highcharts.chart('bargraph_msme_state_wise_onboarded', {
                chart: {
                    type: 'column',
                    height: 450,
                    options3d: {
                        enabled: false
                    }
                },
                title: {
                    text: undefined
                },
                subtitle: {
                    text: `Total MSEs Onboarded: ${totalCount}`,
                    align: 'center',
                    style: { fontSize: '14px', color: '#555', fontWeight: 'bold' }
                },
                xAxis: {
                    type: 'category',
                    title: {
                        text: 'States',
                        style: { fontWeight: 'bold' }
                    },
                    labels: {
                        rotation: -45,
                        style: { fontSize: '11px' },
                        formatter: function () {
                            return this.value.length > 15 ?
                                this.value.substring(0, 12) + '...' :
                                this.value;
                        }
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
                            style: {
                                fontWeight: 'normal',
                                fontSize: '10px'
                            },
                            overflow: 'justify'
                        }
                    }
                },
                series: [{
                    name: 'MSME Count',
                    data: chartData,
                    colors: [
                        '#2c7bb6', '#00a9a6', '#abd9e9', '#fdae61', '#d7191c',
                        '#ffffbf', '#91bfdb', '#fc8d59', '#fee090', '#e0f3f8',
                        '#67a9cf', '#ef8a62', '#b2182b', '#2166ac', '#f4a582'
                    ],
                    animation: {
                        duration: 1000
                    }
                }],
                credits: {
                    enabled: false
                }
            });
        };
    }
</script>