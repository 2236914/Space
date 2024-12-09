document.addEventListener('DOMContentLoaded', function() {
    loadAppointments('all');
    
    // Add tab change listeners
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const status = event.target.getAttribute('href').replace('#', '').replace('-appointments', '');
            loadAppointments(status);
        });
    });

    // Initialize with empty modal content
    setModalContent('create');
});

// Function to set modal content based on type (create/edit)
function setModalContent(type, appointment = null) {
    const modalTitle = type === 'create' ? 'Add New Appointment' : 'Edit Appointment';
    const modalContent = `
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${modalTitle}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="appointmentForm">
                    <div class="modal-body">
                        <input type="hidden" id="appointment_id" value="${appointment ? appointment.id : ''}">
                        <div class="mb-3">
                            <label class="form-label">Student Name</label>
                            <input type="text" class="form-control" id="student_name" 
                                value="${appointment ? appointment.student_name : ''}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Student Email</label>
                            <input type="email" class="form-control" id="student_email" 
                                value="${appointment ? appointment.student_email : ''}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" id="appointment_date" 
                                    value="${appointment ? appointment.appointment_date : ''}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Time</label>
                                <input type="time" class="form-control" id="appointment_time" 
                                    value="${appointment ? appointment.appointment_time : ''}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-control" id="appointment_type" required>
                                <option value="consultation" ${appointment && appointment.appointment_type === 'consultation' ? 'selected' : ''}>Consultation</option>
                                <option value="therapy" ${appointment && appointment.appointment_type === 'therapy' ? 'selected' : ''}>Therapy</option>
                                <option value="follow_up" ${appointment && appointment.appointment_type === 'follow_up' ? 'selected' : ''}>Follow-up</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn bg-gradient-success">Save</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.getElementById('appointmentModal').innerHTML = modalContent;
    initializeFormHandlers();
}

// Modal handlers
function openCreateModal() {
    setModalContent('create');
    new bootstrap.Modal(document.getElementById('appointmentModal')).show();
}

function openEditModal(appointment) {
    setModalContent('edit', appointment);
    new bootstrap.Modal(document.getElementById('appointmentModal')).show();
}

// Initialize form handlers
function initializeFormHandlers() {
    document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            submitBtn.disabled = true;

            const appointmentData = {
                id: document.getElementById('appointment_id').value,
                student_name: document.getElementById('student_name').value,
                student_email: document.getElementById('student_email').value,
                appointment_date: document.getElementById('appointment_date').value,
                appointment_time: document.getElementById('appointment_time').value,
                appointment_type: document.getElementById('appointment_type').value,
                status: 'pending'
            };

            const isEdit = appointmentData.id !== '';
            await (isEdit ? updateAppointment(appointmentData) : createAppointment(appointmentData));
            
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('appointmentModal')).hide();
        } catch (error) {
            console.error('Form submission error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to save appointment. Please try again.',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content'
                }
            });
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });
}

// CRUD Operations
async function createAppointment(appointmentData) {
    try {
        const response = await fetch('../../api/create_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(appointmentData)
        });

        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Created!',
                text: 'New appointment has been created.',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content'
                }
            }).then(() => {
                loadAppointments('all');
                loadAppointments('pending');
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        throw error;
    }
}

async function updateAppointment(appointmentData) {
    try {
        const response = await fetch('../../api/update_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(appointmentData)
        });

        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Appointment has been updated.',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content'
                }
            }).then(() => loadAppointments('all'));
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        throw error;
    }
}

async function deleteAppointment(appointmentId) {
    const confirmation = await Swal.fire({
        icon: 'warning',
        title: 'Are you sure?',
        text: 'This appointment will be permanently deleted.',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            content: 'custom-swal-content'
        }
    });

    if (!confirmation.isConfirmed) return;

    try {
        const response = await fetch('../../api/delete_appointment.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ appointment_id: appointmentId })
        });

        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: 'Appointment has been deleted.',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content'
                }
            }).then(() => loadAppointments('all'));
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to delete appointment',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content'
            }
        });
    }
}

async function updateStatus(appointmentId, newStatus) {
    try {
        Swal.fire({
            title: 'Updating...',
            didOpen: () => {
                Swal.showLoading();
            },
            allowOutsideClick: false,
            showConfirmButton: false
        });

        const response = await fetch('../../api/update_appointment_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                appointment_id: appointmentId,
                status: newStatus
            })
        });

        const result = await response.json();
        
        if (result.success) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Appointment status has been updated.',
                customClass: {
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    content: 'custom-swal-content'
                }
            }).then(() => {
                loadAppointments('all');
                loadAppointments(newStatus);
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Failed to update appointment status',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content'
            }
        });
    }
}

// Function to load appointments based on status
async function loadAppointments(status) {
    const tableId = `${status}AppointmentsTable`;
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Loading...</td></tr>';

    try {
        const response = await fetch(`../../admin_operations/get_appointments.php?status=${status}`);
        const appointments = await response.json();
        
        if (appointments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">No appointments found</td></tr>';
            return;
        }

        tbody.innerHTML = appointments.map(apt => `
            <tr>
                <td>
                    <div class="d-flex px-2 py-1">
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${apt.firstname} ${apt.lastname}</h6>
                            <p class="text-xs text-secondary mb-0">${apt.email}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <p class="text-xs font-weight-bold mb-0">${apt.session_date}</p>
                    <p class="text-xs text-secondary mb-0">${apt.session_time}</p>
                </td>
                <td class="align-middle text-center text-sm">
                    <span class="badge badge-sm bg-gradient-${getStatusColor(apt.status)}">${apt.status}</span>
                </td>
                <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">${apt.session_type}</span>
                </td>
                <td class="align-middle">
                    ${getActionButtons(apt)}
                </td>
            </tr>
        `).join('');

    } catch (error) {
        console.error('Error loading appointments:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4">
                    <div class="text-danger">Error loading appointments. Please try again.</div>
                </td>
            </tr>
        `;
    }
}

function getStatusColor(status) {
    console.log('Status:', status);
    const colors = {
        'pending': 'warning',
        'confirmed': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    const color = colors[status.toLowerCase()] || 'secondary';
    console.log('Color chosen:', color);
    return color;
}

function getActionButtons(appointment) {
    const statusOptions = {
        pending: 'Pending',
        confirmed: 'Confirmed',
        completed: 'Completed',
        cancelled: 'Cancelled'
    };

    // Create dropdown items excluding current status
    const dropdownItems = Object.entries(statusOptions)
        .filter(([value]) => value !== appointment.status.toLowerCase())
        .map(([value, label]) => `
            <li>
                <a class="dropdown-item" href="#" 
                   onclick="updateAppointmentStatus(${appointment.session_id}, '${value}')">
                   Mark as ${label}
                </a>
            </li>
        `).join('');

    return `
        <div class="dropdown">
            <button class="btn bg-gradient-info btn-sm" 
                    type="button" 
                    data-bs-toggle="dropdown" 
                    title="Change Status">
                <i class="material-symbols-rounded">edit_note</i>
            </button>
            <ul class="dropdown-menu">
                ${dropdownItems}
            </ul>
        </div>
    `;
}

// Update appointment status
async function updateAppointmentStatus(sessionId, newStatus) {
    const result = await Swal.fire({
        title: 'Confirm Status Update',
        text: `Are you sure you want to mark this appointment as ${newStatus}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            const response = await fetch('../../admin_operations/update_appointment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    session_id: sessionId,
                    status: newStatus
                })
            });

            // For debugging
            const responseText = await response.text();
            console.log('Raw response:', responseText);

            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('JSON Parse Error:', e);
                throw new Error('Server returned invalid JSON response');
            }
            
            if (data.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Appointment status has been updated',
                    timer: 1500
                });
                
                window.location.reload();
            } else {
                throw new Error(data.message || 'Failed to update status');
            }

        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: error.message || 'Something went wrong! Please try again.'
            });
        }
    }
}

// Function to format error messages
function formatErrorMessage(error) {
    if (typeof error === 'string') {
        return error;
    }
    
    if (error.message) {
        let message = error.message;
        if (error.details) {
            message += '\n\nTechnical Details:\n';
            message += JSON.stringify(error.details, null, 2);
        }
        return message;
    }
    
    return 'An unexpected error occurred. Please try again.';
}

// Add some CSS to style the status badges
function getStatusBadgeClass(status) {
    switch(status.toLowerCase()) {
        case 'pending': return 'bg-warning';
        case 'confirmed': return 'bg-info';
        case 'completed': return 'bg-success';
        case 'cancelled': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

$(document).ready(function() {
    createAppointmentModal();
    $('#appointmentsTable').DataTable({
        columns: [
            // Student column
            { 
                data: null,
                render: function(data, type, row) {
                    return `
                        <p class="mb-0">${row.firstname} ${row.lastname}</p>
                        <small class="text-muted">${row.email}</small>
                    `;
                }
            },
            // Date & Time column
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        ${row.session_date}<br>
                        <small class="text-muted">${row.session_time}</small>
                    `;
                }
            },
            // Status column
            {
                data: 'status',
                render: function(data, type, row) {
                    return `<span class="badge bg-gradient-${getStatusColor(data)}">${data}</span>`;
                }
            },
            // Type column
            { data: 'session_type' },
            // Actions column
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div class="d-flex gap-2">
                            <button class="btn bg-gradient-info btn-sm" 
                                    onclick="viewAppointmentDetails(${row.session_id})" 
                                    title="View Details">
                                <i class="material-symbols-rounded">visibility</i>
                            </button>
                            <div class="dropdown">
                                <button class="btn bg-gradient-success btn-sm" 
                                        type="button" 
                                        data-bs-toggle="dropdown" 
                                        title="Change Status">
                                    <i class="material-symbols-rounded">edit_note</i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="updateAppointmentStatus(${row.session_id}, 'pending')">Mark as Pending</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateAppointmentStatus(${row.session_id}, 'confirmed')">Mark as Confirmed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateAppointmentStatus(${row.session_id}, 'completed')">Mark as Completed</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="updateAppointmentStatus(${row.session_id}, 'cancelled')">Mark as Cancelled</a></li>
                                </ul>
                            </div>
                        </div>
                    `;
                }
            }
        ]
    });
});

function createAppointmentModal() {
    const modalHTML = `
    <div class="modal fade" id="appointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Appointment Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Student Information -->
                    <div class="mb-4">
                        <h6 class="text-sm">Student Information</h6>
                        <div class="card card-body border card-plain border-radius-lg mb-0">
                            <h6 class="mb-0" id="studentName">Loading...</h6>
                            <p class="mb-0 text-sm" id="studentEmail"></p>
                            <p class="mb-0 text-sm" id="studentCourse"></p>
                        </div>
                    </div>

                    <!-- Appointment Information -->
                    <div class="mb-4">
                        <h6 class="text-sm">Appointment Details</h6>
                        <div class="card card-body border card-plain border-radius-lg mb-0">
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-sm mb-1">Date:</p>
                                    <p class="mb-2 text-dark font-weight-bold text-sm" id="appointmentDate"></p>
                                </div>
                                <div class="col-6">
                                    <p class="text-sm mb-1">Time:</p>
                                    <p class="mb-2 text-dark font-weight-bold text-sm" id="appointmentTime"></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-sm mb-1">Type:</p>
                                    <p class="mb-2 text-dark font-weight-bold text-sm" id="appointmentType"></p>
                                </div>
                                <div class="col-6">
                                    <p class="text-sm mb-1">Status:</p>
                                    <p class="mb-0" id="appointmentStatus"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Section -->
                    <div>
                        <h6 class="text-sm">Notes</h6>
                        <div class="card card-body border card-plain border-radius-lg">
                            <p class="mb-0 text-sm" id="appointmentNotes">No notes available.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>`;

    // Check if modal already exists
    if (!document.getElementById('appointmentModal')) {
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }
}
