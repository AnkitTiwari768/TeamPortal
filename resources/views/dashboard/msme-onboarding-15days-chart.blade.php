<div class="card common-card h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between main-charts-headding-wrap pb-2">
            <div>
                <h3>MSME Onboarding (Analysis within 15 days)</h3>
            </div>
        </div>

        <div class="graph-area mt-2 w-100 m-auto text-center mb-2">
            <figure class="highcharts-figure">
                <div id="bargraph_static_onboarded" style="min-height: 450px;"></div>
            </figure>
        </div>
    </div>
</div>

<script>
    if (typeof window.getStaticBarChart === 'undefined') {
        window.getStaticBarChart = function () {
            // Using the static logic previously implemented
            const staticData = [
                { name: 'Onboarded within 15 Days', y: 320 }, 
                { name: 'Onboarded after 15 Days', y: 180 }
            ];
            
            if (typeof Highcharts === 'undefined') {
                console.error("Highcharts is not loaded.");
                return;
            }

            Highcharts.chart('bargraph_static_onboarded', {
                chart: {
                    type: 'column',
                    height: 450
                },
                title: {
                    text: undefined
                },
                xAxis: {
                    type: 'category',
                    title: { text: null },
                    labels: { style: { fontSize: '12px' } }
                },
                yAxis: {
                    min: 0,
                    title: { text: 'Number of MSMEs', style: { fontWeight: 'bold' } },
                    gridLineDashStyle: 'Dash'
                },
                legend: {
                    enabled: false
                },
                tooltip: {
                    pointFormat: '<span style="color:{point.color}">●</span> <b>{point.name}</b>: <b>{point.y}</b> MSMEs'
                },
                plotOptions: {
                    column: {
                        colorByPoint: true,
                        borderRadius: 3,
                        groupPadding: 0.1,
                        pointPadding: 0.1,
                        dataLabels: {
                            enabled: true,
                            format: '{point.y}',
                            inside: false,
                            verticalAlign: 'bottom',
                            y: -5,
                            style: {
                                fontWeight: 'bold',
                                fontSize: '12px',
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
                    data: staticData,
                    colors: ['#f6a21e', '#2c7bb6']
                }],
                credits: {
                    enabled: false
                }
            });
        };
    }
</script>
