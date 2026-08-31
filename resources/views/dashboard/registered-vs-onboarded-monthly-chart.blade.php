<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <h3>Registered MSE vs Onboarded MSE (Monthly)</h3>
            <div class="chart-controls">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="toggleDataLabelsBtn">
                    <i class="fas fa-chart-simple"></i> Toggle Values
                </button>
            </div>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2"
            data-endpoint="{{ $url ?? url('admin-dashboard-registered-vs-onboarded-monthly') }}"
            id="registeredVsOnboardedChartContainer">
            <figure class="highcharts-figure">
                <div id="registeredVsOnboardedChart" style="min-height: 450px;"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    let currentChart = null;
    let showDataLabels = true;

    if (typeof window.getRegisteredVsOnboardedMonthly === 'undefined') {

        window.getRegisteredVsOnboardedMonthly = function (isDateSearch = false) {
            let filterData = {};
            if (typeof window.getFilterParams === 'function') {
                filterData = window.getFilterParams(isDateSearch);
            } else if (typeof getFilterParams === 'function') {
                filterData = getFilterParams(isDateSearch);
            }

            let endpointUrl = document.getElementById('registeredVsOnboardedChartContainer').getAttribute('data-endpoint');

            $.ajax({
                url: endpointUrl,
                type: "GET",
                data: filterData,
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    renderRegisteredVsOnboardedChart(response);
                },
                error: function (xhr) {
                    console.error("Error fetching monthly data:", xhr.responseText);
                    $('#registeredVsOnboardedChart').html('<div class="alert alert-danger mt-5">Error loading chart data</div>');
                }
            });
        };

        window.renderRegisteredVsOnboardedChart = function (response) {
            if (!response || !response.data || response.data.length === 0) {
                $('#registeredVsOnboardedChart').html('<div class="alert alert-info mt-5">No data available for selected filters</div>');
                return;
            }

            if (typeof Highcharts === 'undefined') {
                console.error("Highcharts is not loaded.");
                return;
            }

            if (currentChart) {
                currentChart.destroy();
            }

            const months = response.data.map(item => item.month);
            const registeredData = response.data.map(item => item.registered);
            const onboardedData = response.data.map(item => item.onboarded);
            const totalRegistered = response.total_registered;
            const totalOnboarded = response.total_onboarded;

            // Find max value for better yAxis scaling (including zero)
            const maxValue = Math.max(...registeredData, ...onboardedData, 1);
            const yAxisMax = maxValue === 0 ? 10 : maxValue * 1.2; // Set min height even if all zeros

            currentChart = Highcharts.chart('registeredVsOnboardedChart', {
                chart: {
                    type: 'column',
                    height: 480,
                    spacing: [50, 15, 40, 15],
                    style: {
                        fontFamily: 'inherit'
                    }
                },
                title: {
                    text: undefined
                },
                subtitle: {
                    text: `Total Registered: ${totalRegistered.toLocaleString()} | Total Onboarded: ${totalOnboarded.toLocaleString()}`,
                    style: { fontSize: '11px', color: '#666' }
                },
                xAxis: {
                    categories: months,
                    crosshair: true,
                    title: {
                        text: 'Month',
                        style: { fontWeight: 'bold', fontSize: '12px' }
                    },
                    labels: {
                        style: {
                            fontSize: '11px',
                            fontWeight: 'bold'
                        },
                        rotation: 0
                    }
                },
                yAxis: {
                    min: 0,
                    max: yAxisMax,
                    title: {
                        text: 'Number of MSEs',
                        align: 'high',
                        style: { fontWeight: 'bold', fontSize: '12px' },
                        offset: 20
                    },
                    labels: {
                        formatter: function () {
                            return this.value.toLocaleString();
                        },
                        style: {
                            fontSize: '11px'
                        }
                    },
                    gridLineColor: '#e0e0e0',
                    allowDecimals: false,
                    tickInterval: maxValue > 0 ? Math.ceil(maxValue / 5) : 2
                },
                tooltip: {
                    shared: true,
                    backgroundColor: '#ffffff',
                    borderColor: '#cccccc',
                    borderRadius: 6,
                    borderWidth: 1,
                    shadow: true,
                    padding: 8,
                    useHTML: true,
                    formatter: function () {
                        return `<b style="font-size: 12px;">${this.x}</b><br/>
                                <span style="color:#2E86AB; font-size: 11px;">● Registered:</span> <b>${this.points[0].y.toLocaleString()}</b><br/>
                                <span style="color:#6A994E; font-size: 11px;">● Onboarded:</span> <b>${this.points[1].y.toLocaleString()}</b>`;
                    }
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.15,
                        groupPadding: 0.1,
                        borderWidth: 0,
                        borderRadius: 6,
                        dataLabels: {
                            enabled: showDataLabels,
                            formatter: function () {
                                if (this.y > 0) {
                                    return this.y.toLocaleString();
                                }
                                return null; // Don't show zero values
                            },
                            style: {
                                fontSize: '12px',
                                fontWeight: 'bold',
                                color: '#333',
                                textOutline: '1px solid white'
                            },
                            inside: false,
                            verticalAlign: 'top',
                            y: -18,
                            crop: false,
                            overflow: 'none',
                            padding: 5
                        },
                        cursor: 'pointer',
                        pointWidth: 35
                    }
                },
                series: [
                    {
                        name: 'Registered MSE',
                        data: registeredData,
                        color: '#2E86AB',
                        dataLabels: {
                            enabled: showDataLabels
                        }
                    },
                    {
                        name: 'Onboarded MSE',
                        data: onboardedData,
                        color: '#6A994E',
                        dataLabels: {
                            enabled: showDataLabels
                        }
                    }
                ],
                credits: {
                    enabled: false
                },
                legend: {
                    align: 'center',
                    verticalAlign: 'bottom',
                    layout: 'horizontal',
                    symbolRadius: 4,
                    symbolHeight: 12,
                    symbolWidth: 12,
                    itemStyle: {
                        fontWeight: 'normal',
                        fontSize: '12px'
                    },
                    padding: 15,
                    margin: 10
                },
                responsive: {
                    rules: [{
                        condition: {
                            maxWidth: 600
                        },
                        chartOptions: {
                            xAxis: {
                                labels: {
                                    rotation: -45,
                                    style: { fontSize: '9px' }
                                }
                            },
                            plotOptions: {
                                column: {
                                    dataLabels: {
                                        style: { fontSize: '10px' },
                                        y: -14
                                    },
                                    pointWidth: 25
                                }
                            },
                            chart: {
                                height: 450
                            }
                        }
                    }]
                },
                exporting: {
                    enabled: true,
                    buttons: {
                        contextButton: {
                            menuItems: ['viewFullscreen', 'printChart', 'separator', 'downloadPNG', 'downloadJPEG', 'downloadPDF', 'downloadSVG']
                        }
                    }
                }
            });
        };

        window.toggleDataLabels = function () {
            if (!currentChart) return;

            showDataLabels = !showDataLabels;
            currentChart.series.forEach(series => {
                series.update({
                    dataLabels: {
                        enabled: showDataLabels
                    }
                });
            });
        };

        // Event listener for toggle button
        $(document).ready(function () {
            $('#toggleDataLabelsBtn').on('click', function () {
                window.toggleDataLabels();
            });
        });
    }
</script>