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

            // Ensure we have data to display
            if (!moodData.labels.length) {
                console.log('No mood data available');
                return new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: ['No Data'],
                        datasets: [{
                            label: "Mood Score",
                            data: [0],
                            borderColor: "#3c6454",
                            backgroundColor: "rgba(60, 100, 84, 0.1)",
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        }
                    }
                });
            }

            // Create the chart with actual data
            console.log('Creating chart with data...');
            return new Chart(ctx, {
                type: "line",
                data: {
                    labels: moodData.labels,
                    datasets: [{
                        label: "Mood Level",
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
                            display: true,
                            position: 'top',
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
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                borderDash: [5, 5],
                                color: '#c1c4ce5c'
                            },
                            ticks: {
                                stepSize: 1,
                                display: true,
                                padding: 10,
                                color: '#344767',
                                font: {
                                    size: 12,
                                    family: "Roboto",
                                },
                                callback: function(value) {
                                    const moodLabels = {
                                        1: 'Very Sad',
                                        2: 'Sad',
                                        3: 'Neutral',
                                        4: 'Happy',
                                        5: 'Very Happy'
                                    };
                                    return moodLabels[value] || '';
                                }
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                borderDash: [5, 5],
                                color: '#c1c4ce5c'
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
            // Display error message on canvas
            ctx.font = '14px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('Error loading mood data', ctx.canvas.width / 2, ctx.canvas.height / 2);
        }
    }
}

// Initialize when document loads
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing LineChartManager');
    LineChartManager.initLineChart();
});