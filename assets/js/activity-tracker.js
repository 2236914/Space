const ActivityTracker = {
    init() {
        this.initChart();
        this.initWebSocket();
        this.startAutoUpdate(); // Fallback
    },

    initWebSocket() {
        // For local development
        this.ws = new WebSocket('ws://localhost:8080');
        
        this.ws.onopen = () => {
            console.log('WebSocket Connected');
        };

        this.ws.onmessage = (event) => {
            console.log('Received update:', event.data);
            this.updateChart(); // Update chart when message received
        };

        this.ws.onerror = (error) => {
            console.error('WebSocket Error:', error);
            // Fallback to polling if WebSocket fails
            this.startAutoUpdate();
        };

        this.ws.onclose = () => {
            console.log('WebSocket Closed');
            // Try to reconnect
            setTimeout(() => this.initWebSocket(), 5000);
        };
    },

    initChart() {
        var ctx = document.getElementById("chart-bars").getContext("2d");
        this.activityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Activities',
                    data: [0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: 'rgba(66, 135, 245, 0.8)',
                    borderRadius: 5,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                animation: {
                    duration: 1000
                }
            }
        });
    },

    updateChart() {
        fetch(`${BASE_URL}/admin_operations/get_weekly_activities.php`)
            .then(response => response.json())
            .then(data => {
                if(this.activityChart) {
                    this.activityChart.data.labels = data.days;
                    this.activityChart.data.datasets[0].data = data.counts;
                    this.activityChart.update('active');

                    // Update last activity text
                    const lastActivityEl = document.getElementById('last-activity');
                    if(lastActivityEl && data.last_activity) {
                        lastActivityEl.textContent = data.last_activity;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating chart:', error);
            });
    },

    startAutoUpdate() {
        setInterval(() => this.updateChart(), 5000);
    },

    listenForActivities() {
        // Listen for custom activity events
        document.addEventListener('spaceActivity', () => {
            this.updateChart();
        });
    },

    logActivity() {
        // Trigger update when any activity occurs
        document.dispatchEvent(new Event('spaceActivity'));
    },

    triggerImmediateUpdate() {
        this.updateChart();  // Update immediately when called
    }
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    ActivityTracker.init();
});

// Use in your forms/actions
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    // ... your form submission code ...
    .then(response => {
        if(response.success) {
            ActivityTracker.triggerImmediateUpdate();  // Immediate update
        }
    });
});