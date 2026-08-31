<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <h3> MSE Onboarded Percentage (Gender Wise)</h3>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2"
            data-endpoint="{{ $url ?? url('get-msme-gender-percentege') }}" id="msmeGenderChartContainer">
            <figure class="highcharts-figure">
                <div id="pieChartPercentage" class="pie-chart-percentage-container"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    if (typeof window.getMsmePercetageByGender === 'undefined') {
        window.getMsmePercetageByGender = function (isDateSearch = false) {
            let filterData = {};
            if (typeof window.getFilterParams === 'function') {
                filterData = window.getFilterParams(isDateSearch);
            } else if (typeof getFilterParams === 'function') {
                filterData = getFilterParams(isDateSearch);
            }

            let endpointUrl = document.getElementById('msmeGenderChartContainer').getAttribute('data-endpoint');

            $.ajax({
                url: endpointUrl,
                type: "GET",
                data: filterData,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    window.getMsmePercentageGenderWisePiechart(response);
                },
                error: function (xhr) {
                    console.error("Error fetching gender percentage data:", xhr.responseText);
                }
            });
        };

        window.getMsmePercentageGenderWisePiechart = function (response) {
            if (!response || !response.data || response.data.length === 0) {
                document.getElementById('pieChartPercentage').innerHTML = '<div class="alert alert-info mt-5">No data available</div>';
                return;
            }

            const chartData = response.data.map(item => ({
                name: item.name,
                y: item.percentage,
                count: item.count
            }));

            if (typeof Highcharts === 'undefined') {
                console.error("Highcharts is not loaded.");
                return;
            }

            Highcharts.chart('pieChartPercentage', {
                chart: {
                    type: 'pie',
                    height: 450
                },
                title: {
                    text: undefined
                },
                subtitle: {
                    text: `Total MSEs Onboarded: ${response.total}`,
                    align: 'center',
                    style: { fontSize: '14px', color: '#555', fontWeight: 'bold' }
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}">●</span> <b>{point.name}</b>: <b>{point.y:.1f}%</b> ({point.count} MSEs)'
                },
                legend: {
                    enabled: true,
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    symbolRadius: 5,
                    symbolHeight: 12,
                    symbolWidth: 12,
                    itemStyle: { fontWeight: 'normal', fontSize: '12px' },
                    title: { text: 'Gender', style: { fontWeight: 'bold' } }
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        showInLegend: true,
                        dataLabels: {
                            enabled: true,
                            format: '<b>{point.name}</b>: {point.y:.1f}% ({point.count} MSEs)',
                            style: { fontSize: '12px', fontWeight: 'normal', textOutline: 'none' },
                            distance: 30
                        }
                    }
                },
                series: [{
                    name: 'MSEs by Gender',
                    colorByPoint: true,
                    data: chartData,
                    dataLabels: {
                        connectorWidth: 1,
                        connectorColor: '#aaa'
                    }
                }],
                credits: {
                    enabled: false
                },
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 500
                        },
                        chartOptions: {
                            chart: {
                                height: 320
                            },
                            legend: {
                                layout: 'horizontal',
                                align: 'center',
                                verticalAlign: 'bottom',
                                labelFormat: '{name}: {percentage:.1f}%'
                            },
                            plotOptions: {
                                pie: {
                                    dataLabels: {
                                        enabled: false
                                    }
                                }
                            }
                        }
                    }]
                }
            });
        };
    }
</script>