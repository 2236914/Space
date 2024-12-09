document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for buttons
    document.querySelectorAll('.view-profile').forEach(button => {
        button.addEventListener('click', function() {
            viewTherapistProfile(this.dataset.therapistId);
        });
    });

    document.querySelectorAll('.schedule-session').forEach(button => {
        button.addEventListener('click', function() {
            scheduleSession(this.dataset.therapistId);
        });
    });

    // Initialize charts if needed
    if (document.getElementById('chart-bars')) {
        initializeCharts();
    }
});

// View Therapist Profile Function
window.viewTherapistProfile = function(therapistId) {
    fetch('../../admin_operations/get_therapist_profile.php?therapist_id=' + therapistId)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            
            const profileContent = document.getElementById('therapistProfileContent');
            profileContent.innerHTML = `
                <div class="card bg-transparent shadow-xl">
                    <div class="overflow-hidden position-relative border-radius-xl">
                        <img src="../../assets/img/illustrations/pattern-tree.svg" class="position-absolute opacity-2 start-0 top-0 w-100 z-index-1 h-100" alt="pattern-tree">
                        <span class="mask bg-gradient-dark opacity-10"></span>
                        <div class="card-body position-relative z-index-1 p-3">
                            <div class="row align-items-center">
                                <div class="col-4">
                                    <img src="../../admin_operations/get_profile_picture.php?user_id=${data.therapist_id}&user_type=therapist" 
                                        alt="profile" 
                                        class="border-radius-lg shadow shadow-dark w-100 mt-n0"
                                        onerror="this.src='../../assets/img/default-avatar.png';">
                                </div>
                                <div class="col-8">
                                    <h5 class="text-white mb-0">Dr. ${data.firstname} ${data.lastname}</h5>
                                    <p class="text-white text-sm opacity-8 mb-0">${data.specialization}</p>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="d-flex mt-5">
                                    <div class="me-4">
                                        <p class="text-white text-sm opacity-8 mb-0">License</p>
                                        <h6 class="text-white mb-0">${data.license_number || 'N/A'}</h6>
                                    </div>
                                    <div>
                                        <p class="text-white text-sm opacity-8 mb-0">Therapist ID</p>
                                        <h6 class="text-white mb-0">${data.therapist_id}</h6>
                                    </div>
                                </div>
                                <div class="ms-auto w-20 d-flex align-items-end justify-content-end">
                                    <img class="w-60 mt-2" src="../../assets/img/logo-space.png" alt="logo">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;
            
            const modal = new bootstrap.Modal(document.getElementById('therapistProfileModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load therapist profile: ' + error.message);
        });
};

// Schedule Session Function
window.scheduleSession = function(therapistId) {
    document.getElementById('selectedTherapistId').value = therapistId;
    
    if (!calendar) {
        calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            selectable: true,
            height: 'auto',
            contentHeight: 350,
            aspectRatio: 1.35,
            headerToolbar: {
                left: 'prev',
                center: 'title',
                right: 'next'
            },
            select: function(info) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(info.start);
                selectedDate.setHours(0, 0, 0, 0);
                
                // Allow today's date but prevent past dates
                if (selectedDate < today) {
                    alert('Cannot book sessions for past dates');
                    return;
                }
                
                checkAvailability(therapistId, info.startStr);
            },
            dayCellDidMount: function(info) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const cellDate = new Date(info.date);
                cellDate.setHours(0, 0, 0, 0);
                
                // Only disable dates before today
                if (cellDate < today) {
                    info.el.classList.add('fc-disabled');
                }
            }
        });
    }
    
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
    
    setTimeout(() => calendar.render(), 200);
};

function checkAvailability(therapistId, date) {
    fetch(`../../admin_operations/check_availability.php?therapist_id=${therapistId}&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }

            const currentDate = new Date();
            const selectedDate = new Date(date);
            const isToday = selectedDate.toDateString() === currentDate.toDateString();
            const currentHour = currentDate.getHours();
            
            const timeSelect = document.getElementById('sessionTime');
            timeSelect.innerHTML = '';
            
            Object.entries(data.availability).forEach(([time, available]) => {
                if (available) {
                    // For today, only show future time slots
                    const timeHour = parseInt(time.split(':')[0]);
                    if (!isToday || timeHour > currentHour) {
                        const option = document.createElement('option');
                        option.value = time;
                        option.textContent = time;
                        timeSelect.appendChild(option);
                    }
                }
            });
            
            document.getElementById('selectedDate').value = date;
            document.getElementById('sessionDetailsForm').style.display = 
                timeSelect.options.length > 0 ? 'block' : 'none';
                
            if (timeSelect.options.length === 0) {
                alert('No available time slots for this date');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to check availability: ' + error.message);
        });
}

function formatTime(time) {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    return `${hour > 12 ? hour - 12 : hour}:${minutes || '00'} ${hour >= 12 ? 'PM' : 'AM'}`;
}

function bookSession(event) {
    event.preventDefault();
    
    const formData = {
        therapist_id: document.getElementById('selectedTherapistId').value,
        session_date: document.getElementById('selectedDate').value,
        session_time: document.getElementById('sessionTime').value,
        session_type: document.getElementById('sessionType').value,
        notes: document.getElementById('sessionNotes').value
    };

    // Validate required fields
    if (!formData.therapist_id || !formData.session_date || !formData.session_time || !formData.session_type) {
        alert('Please fill in all required fields');
        return;
    }

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Booking...';
    submitButton.disabled = true;

    fetch('../../admin_operations/book_session.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Booking Successful!',
                text: 'Your session has been scheduled. Please wait for the therapist to confirm.',
                showConfirmButton: true,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn bg-gradient-dark',
                    popup: 'shadow-lg',
                    title: 'text-dark'
                },
                buttonsStyling: false
            }).then(() => {
                // Close modal and refresh page
                const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                modal.hide();
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to book session');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Booking Failed',
            text: error.message,
            customClass: {
                confirmButton: 'btn bg-gradient-dark',
                popup: 'shadow-lg',
                title: 'text-dark'
            },
            buttonsStyling: false
        });
    })
    .finally(() => {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Add event listener to the form
document.getElementById('bookSessionForm').addEventListener('submit', bookSession);

function initializeCharts() {
    var ctx = document.getElementById("chart-bars").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["M", "T", "W", "T", "F", "S", "S"],
            datasets: [{
                label: "Sessions",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                backgroundColor: "#fff",
                data: [50, 20, 10, 22, 50, 10, 40],
                maxBarThickness: 6
            }, ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                }
            },
            interaction: {
                intersect: false,
                mode: 'index',
            },
            scales: {
                y: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false,
                    },
                    ticks: {
                        suggestedMin: 0,
                        suggestedMax: 60,
                        beginAtZero: true,
                        padding: 15,
                        font: {
                            size: 14,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                        color: "#fff"
                    },
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false
                    },
                    ticks: {
                        display: true,
                        color: '#fff',
                        padding: 10,
                        font: {
                            size: 14,
                            family: "Open Sans",
                            style: 'normal',
                            lineHeight: 2
                        },
                    }
                },
            },
        },
    });
} 