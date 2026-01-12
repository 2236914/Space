const ActivityTracker = {
    init() {
        if (!document.getElementById("chart-bars")) {
            console.warn('Chart element not found');
            return;
        }
        this.initChart();
        this.initWebSocket();
        this.listenForActivities();
    },

    initWebSocket() {
        try {
            this.ws = new WebSocket('ws://localhost:8080');
            
            this.ws.onopen = () => {
                console.log('WebSocket Connected');
            };

            this.ws.onmessage = (event) => {
                console.log('Received update:', event.data);
                this.updateChart();
            };

            this.ws.onerror = (error) => {
                console.error('WebSocket Error:', error);
                this.startAutoUpdate();
            };

            this.ws.onclose = () => {
                console.log('WebSocket Closed');
                setTimeout(() => this.initWebSocket(), 5000);
            };
        } catch (error) {
            console.error('WebSocket initialization failed:', error);
            this.startAutoUpdate();
        }
    },

    initChart() {
        const ctx = document.getElementById("chart-bars").getContext("2d");
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
        if (typeof BASE_URL === 'undefined') {
            console.error('BASE_URL is not defined');
            return;
        }

        fetch(`${BASE_URL}/admin_operations/get_weekly_activities.php`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if(this.activityChart) {
                    this.activityChart.data.labels = data.days;
                    this.activityChart.data.datasets[0].data = data.counts;
                    this.activityChart.update('active');

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
        if (this._updateInterval) {
            clearInterval(this._updateInterval);
        }
        this._updateInterval = setInterval(() => this.updateChart(), 5000);
    },

    listenForActivities() {
        document.addEventListener('spaceActivity', () => {
            this.updateChart();
        });
    },

    logActivity() {
        document.dispatchEvent(new Event('spaceActivity'));
    },

    triggerImmediateUpdate() {
        this.updateChart();
    }
};

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    ActivityTracker.init();
});

// Form submission handling
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(response => {
            if(response.success) {
                ActivityTracker.triggerImmediateUpdate();
            }
        })
        .catch(error => console.error('Form submission error:', error));
    });
});