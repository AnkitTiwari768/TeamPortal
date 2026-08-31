<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <div>
                <h3>Top Contributing NPS (By Claims)</h3>
                <span>Visual representation of top 5 NPS with highest claim count</span>
            </div>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
            <figure class="highcharts-figure">
                <div id="topPerformerNpsChart"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    if (typeof window.getTopPerformerNpsChart !== 'function') {
        window.getTopPerformerNpsChart = function(isDateSearch = false) {
            
            var params = typeof getFilterParams === 'function' ? getFilterParams(isDateSearch) : {};
            
            $.ajax({
                url: "{{ $url ?? url('admin-dashboard-top-performer-nps') }}",
                type: 'GET',
                data: params,
                success: function(response) {
                    const data = response.data || [];
                    const categories = data.map(item => item.name);
                    const values = data.map(item => item.y);

                    Highcharts.chart('topPerformerNpsChart', {
                        chart: {
                            type: 'bar',
                            backgroundColor: '#fff',
                            borderRadius: 5
                        },
                        title: {
                            text: ''
                        },
                        xAxis: {
                            categories: categories,
                            title: {
                                text: 'Enterprise / Organization Name'
                            }
                        },
                        yAxis: {
                            min: 0,
                            title: {
                                text: 'Claim Count',
                                align: 'high'
                            },
                            labels: {
                                overflow: 'justify'
                            }
                        },
                        tooltip: {
                            valueSuffix: ' claims'
                        },
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    enabled: true,
                                    style: {
                                        fontSize: '12px'
                                    }
                                },
                                borderRadius: 5
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        series: [{
                            name: 'Claim Count',
                            color: '#1E88E5',
                            data: values
                        }]
                    });
                },
                error: function() {
                    console.error('Failed to load Top Performer NPS Chart data.');
                }
            });
        }
    }
</script>
