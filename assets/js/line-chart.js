// assets/js/charts/line-chart.js
class LineChartManager {
    static async initLineChart() {
        console.log('Initializing line chart...');
        const ctx = document.getElementById("line-chart")?.getContext("2d");
        
        if (!ctx) {
            console.error("Line chart canvas element not found");
            return;
        }

        try {
            console.log('Fetching mood data...');
            const response = await fetch('../../admin_operations/get_mood_data.php');
            const moodData = await response.json();
            console.log('Mood data received:', moodData);

            if (!moodData.success) {
                throw new Error(moodData.error || 'Failed to fetch mood data');
            }

            // Create the chart with actual data
            return new Chart(ctx, {
                type: "line",
                data: {
                    labels: moodData.labels,
                    datasets: [{
                        label: "Weekly Mood",
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: "#3c6454",
                        borderColor: "#3c6454",
                        backgroundColor: "rgba(60, 100, 84, 0.1)",
                        fill: true,
                        data: moodData.values,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide legend
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    const moodLabels = {
                                        1: 'Very Sad',
                                        2: 'Sad',
                                        3: 'Neutral',
                                        4: 'Happy',
                                        5: 'Very Happy'
                                    };
                                    return moodLabels[context.raw] || context.raw;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                stepSize: 1,
                                display: true,
                                padding: 10,
                                color: '#344767',
                                font: {
                                    size: 12,
                                    family: "Roboto",
                                }
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                color: '#344767',
                                padding: 10,
                                font: {
                                    size: 12,
                                    family: "Roboto",
                                }
                            }
                        },
                    },
                },
            });
        } catch (error) {
            console.error('Error initializing chart:', error);
            if (ctx) {
                ctx.font = '14px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('Error loading data', ctx.canvas.width / 2, ctx.canvas.height / 2);
            }
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    LineChartManager.initLineChart();
});