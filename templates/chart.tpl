<div style="height: 450px; width: 100%; margin-bottom: 20px;">
    <canvas id="{uid}"></canvas>
</div>
<script>
    (function() {
        const initChart = () => {
            const ctx = document.getElementById('{uid}');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {labels}, 
                    datasets: [{
                        label: 'Уникальные игроки',
                        data: {data},
                        backgroundColor: 'rgba(54, 162, 235, 0.4)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grace: '20%', 
                            ticks: { stepSize: 1, precision: 0 }
                        }
                    },
                    plugins: {
                        legend: { display: true, position: 'top' }
                    }
                }
            });
        };

        if (typeof Chart === 'undefined') {
            const script = document.createElement('script');
            script.src = "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.5.0/chart.umd.js";
            script.onload = initChart;
            document.head.appendChild(script);
        } else {
            initChart();
        }
    })();
</script>