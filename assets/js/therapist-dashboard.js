document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
});

function loadDashboardData() {
    fetch('../../admin_operations/get_dashboard_data.php')
        .then(response => {
            if (!response.ok) {
                console.error('Response status:', response.status);
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Dashboard data:', data);
            
            document.getElementById('todayAppointments').textContent = data.today_appointments || '0';
            document.getElementById('pendingSessions').textContent = data.pending_sessions || '0';
            document.getElementById('studentInteractions').textContent = data.student_interactions || '0';
            document.getElementById('completionRate').textContent = (data.completion_rate || '0') + '%';

            if (data.session_types) {
                initializeSessionTypeChart(data.session_types);
            }
            if (data.upcoming_schedule) {
                updateUpcomingSchedule(data.upcoming_schedule);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('todayAppointments').textContent = '-';
            document.getElementById('pendingSessions').textContent = '-';
            document.getElementById('studentInteractions').textContent = '-';
            document.getElementById('completionRate').textContent = '-';
        });
}

function initializeSessionTypeChart(sessionTypes) {
    // Clear any existing chart
    const chartElement = document.getElementById('sessionTypeChart');
    if (window.sessionTypeChartInstance) {
        window.sessionTypeChartInstance.destroy();
    }

    const ctx = chartElement.getContext('2d');
    window.sessionTypeChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Online Sessions', 'Face-to-face Sessions'],
            datasets: [{
                data: [
                    sessionTypes['online'] || 0,
                    sessionTypes['face-to-face'] || 0
                ],
                backgroundColor: ['#43A047', '#2196F3'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        },
                        color: '#344767'
                    }
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

function updateUpcomingSchedule(schedule) {
    const scheduleDiv = document.getElementById('upcomingSchedule');
    if (schedule.length === 0) {
        scheduleDiv.innerHTML = '<p class="text-center text-muted">No upcoming appointments</p>';
        return;
    }

    scheduleDiv.innerHTML = schedule.map(appointment => {
        const date = new Date(appointment.session_date).toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
        const time = appointment.session_time.substring(0, 5); // Format: HH:mm
        const sessionTypeIcon = appointment.session_type === 'online' ? 
            'videocam' : 'person';

        return `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="text-sm mb-1">
                        <i class="material-symbols-rounded text-sm me-1">${sessionTypeIcon}</i>
                        ${appointment.firstname} ${appointment.lastname}
                    </h6>
                    <p class="text-xs text-muted mb-0">
                        ${appointment.session_type.charAt(0).toUpperCase() + appointment.session_type.slice(1)} Session
                    </p>
                </div>
                <div class="text-end">
                    <p class="text-sm mb-1">${date}</p>
                    <p class="text-xs text-muted mb-0">${time}</p>
                </div>
            </div>
        `;
    }).join('');
} 