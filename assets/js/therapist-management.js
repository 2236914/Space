$(document).ready(function() {
    var table = $('#therapistTable').DataTable({
        dom: '<"table-responsive"t>',
        paging: false,
        info: false,
        searching: false,
        lengthChange: false,
        ordering: true,
        responsive: true,
        language: {
            paginate: {
                previous: "",
                next: ""
            },
            lengthMenu: "",
            info: "",
            infoEmpty: "",
            infoFiltered: "",
            search: ""
        }
    });

    // Filter dropdown functionality
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        const filter = $(this).data('filter');
        
        if (filter === 'all') {
            table.column(4).search('').draw();
        } else {
            table.column(4).search(filter).draw();
        }
    });

    // View Therapist Modal
    $(document).on('click', '.view-therapist', function() {
        const therapistId = $(this).data('therapist-id');
        
        $.ajax({
            url: '../../admin_operations/view_therapist.php',
            type: 'GET',
            data: { id: therapistId },
            success: function(response) {
                if (response.success) {
                    const therapist = response.data;
                    const modalContent = `
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-gradient-info">
                                    <h5 class="modal-title text-white">Therapist Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Personal Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Personal Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">First Name</label>
                                                <p class="form-control-static">${therapist.firstname}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Last Name</label>
                                                <p class="form-control-static">${therapist.lastname}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Contact Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Email</label>
                                                <p class="form-control-static">${therapist.email}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Contact Number</label>
                                                <p class="form-control-static">${therapist.contact_number}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Professional Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Professional Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Specialization</label>
                                                <p class="form-control-static">${therapist.specialization}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">License Number</label>
                                                <p class="form-control-static">${therapist.license_number}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Account Information Section -->
                                    <div>
                                        <h6 class="text-info mb-3">Account Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Status</label>
                                                <p class="form-control-static">
                                                    <span class="badge bg-gradient-${getStatusClass(therapist.status)}">${therapist.status}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Created Date</label>
                                                <p class="form-control-static">${therapist.created_date}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>`;

                    $('#therapistModal').html(modalContent).modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function() {
                showErrorAlert('Failed to fetch therapist details');
            }
        });
    });

    // Handle Edit Therapist
    $(document).on('click', '.edit-therapist', function() {
        const therapistId = $(this).data('therapist-id');
        
        $.ajax({
            url: '../../admin_operations/view_therapist.php',
            type: 'GET',
            data: { id: therapistId },
            success: function(response) {
                if (response.success) {
                    const therapist = response.data;
                    const modalContent = `
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-gradient-primary">
                                    <h5 class="modal-title text-white">Edit Therapist</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form id="editTherapistForm" enctype="multipart/form-data">
                                    <div class="modal-body">
                                        <input type="hidden" name="therapist_id" value="${therapist.therapist_id}">
                                        
                                        <!-- Personal Information Section -->
                                        <div class="border-bottom pb-3 mb-3">
                                            <h6 class="text-primary mb-3">Personal Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" 
                                                               class="form-control border" 
                                                               name="firstname" 
                                                               value="${therapist.firstname}" 
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" 
                                                               class="form-control border" 
                                                               name="lastname" 
                                                               value="${therapist.lastname}" 
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Date of Birth</label>
                                                        <input type="date" 
                                                               class="form-control border" 
                                                               name="dob" 
                                                               value="${therapist.dob}"
                                                               required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Information Section -->
                                        <div class="border-bottom pb-3 mb-3">
                                            <h6 class="text-primary mb-3">Contact Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" 
                                                               class="form-control border" 
                                                               name="email" 
                                                               value="${therapist.email}" 
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Contact Number</label>
                                                        <input type="text" 
                                                               class="form-control border" 
                                                               name="contact_number" 
                                                               value="${therapist.contact_number}" 
                                                               pattern="09[0-9]{9}" 
                                                               required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Professional Information Section -->
                                        <div class="border-bottom pb-3 mb-3">
                                            <h6 class="text-primary mb-3">Professional Information</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Specialization</label>
                                                        <input type="text" 
                                                               class="form-control border" 
                                                               name="specialization" 
                                                               value="${therapist.specialization}" 
                                                               required>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">License Number</label>
                                                        <input type="text" 
                                                               class="form-control border" 
                                                               name="license_number" 
                                                               value="${therapist.license_number}" 
                                                               required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Account Settings Section -->
                                        <div>
                                            <h6 class="text-primary mb-3">Account Settings</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">Account Status</label>
                                                        <select class="form-control border" name="status" required>
                                                            <option value="active" ${therapist.status === 'active' ? 'selected' : ''}>Active</option>
                                                            <option value="inactive" ${therapist.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                                            <option value="suspended" ${therapist.status === 'suspended' ? 'selected' : ''}>Suspended</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label">New Password</label>
                                                        <input type="password" 
                                                               class="form-control border" 
                                                               name="password" 
                                                               placeholder="Leave blank to keep current">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn bg-gradient-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;

                    $('#therapistModal').html(modalContent).modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function() {
                showErrorAlert('Failed to fetch therapist details');
            }
        });
    });

    // Handle Export CSV
    $('#exportCSV').click(function() {
        window.location.href = '../../admin_operations/export_therapists.php';
    });

    // Add New Therapist Modal
    $('#addNewTherapist').click(function() {
        const modalContent = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-success">
                        <h5 class="modal-title text-white">Add New Therapist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addTherapistForm" enctype="multipart/form-data">
                        <div class="modal-body">

                            <!-- Personal Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-success mb-3">Personal Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control border" name="firstname" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control border" name="lastname" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" class="form-control border" name="dob" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-success mb-3">Contact Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control border" name="email" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Contact Number</label>
                                            <input type="text" 
                                                   class="form-control border" 
                                                   name="contact_number" 
                                                   pattern="09[0-9]{9}" 
                                                   placeholder="09xxxxxxxxx"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-success mb-3">Professional Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Specialization</label>
                                            <input type="text" class="form-control border" name="specialization" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">License Number</label>
                                            <input type="text" class="form-control border" name="license_number" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Settings Section -->
                            <div>
                                <h6 class="text-success mb-3">Account Settings</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control border" name="password" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control border" name="confirm_password" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Add Therapist</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        // Show modal
        $('#therapistModal').html(modalContent).modal('show');
    });



    // Form submission
    $(document).on('submit', '#addTherapistForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Debug: Check if file is included
        const fileInput = $('#newProfilePicture')[0];
        if (fileInput.files.length > 0) {
            console.log('File selected:', fileInput.files[0]);
        }
        
        $.ajax({
            url: '../../admin_operations/add_therapist.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    // Parse response if it's a string
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn bg-gradient-primary'
                            },
                            buttonsStyling: false
                        }).then(function() {
                            $('#therapistModal').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to add therapist',
                            customClass: {
                                confirmButton: 'btn bg-gradient-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary'
                        },
                        buttonsStyling: false
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to add therapist. Please try again.',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    });

    // Edit Therapist Form Submission
    $(document).on('submit', '#editTherapistForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        showConfirmationAlert('Update Therapist', 'Are you sure you want to update this therapist\'s information?')
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../../admin_operations/edit_therapist.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                showSuccessAlert(response.message).then(() => {
                                    $('#therapistModal').modal('hide');
                                    table.ajax.reload();
                                });
                            } else {
                                showErrorAlert(response.message);
                            }
                        },
                        error: function() {
                            showErrorAlert('Failed to process request. Please try again.');
                        }
                    });
                }
            });
    });

    // Helper function for status classes
    function getStatusClass(status) {
        const statusClasses = {
            'active': 'success',
            'inactive': 'secondary',
            'suspended': 'warning'
        };
        return statusClasses[status] || 'secondary';
    }

    // Custom SweetAlert2 styling
    const SwalCustom = Swal.mixin({
        customClass: {
            confirmButton: 'btn bg-gradient-success',
            cancelButton: 'btn bg-gradient-danger',
            container: 'custom-swal-container',
            popup: 'custom-swal-popup shadow-lg',
            header: 'custom-swal-header',
            title: 'custom-swal-title',
            closeButton: 'custom-swal-close',
            content: 'custom-swal-content'
        },
        buttonsStyling: false,
        confirmButtonText: 'Confirm',
        showClass: {
            popup: 'animate__animated animate__fadeInDown animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp animate__faster'
        }
    });

    // Success Alert
    function showSuccessAlert(message) {
        SwalCustom.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            showConfirmButton: true,
            timer: 2500,
            timerProgressBar: true
        });
    }

    // Error Alert
    function showErrorAlert(message) {
        SwalCustom.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            showConfirmButton: true
        });
    }

    // Confirmation Alert
    function showConfirmationAlert(title, text) {
        return SwalCustom.fire({
            icon: 'warning',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        });
    }

    // Add this at the bottom of your file to ensure SweetAlert2 is available
    document.addEventListener('DOMContentLoaded', function() {
        // Verify SweetAlert2 is loaded
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded!');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Debug log
        console.log('Therapist management JS loaded');
        
        const filterButtons = document.querySelectorAll('.dropdown-item[data-filter]');
        const tableRows = document.querySelectorAll('tbody tr[data-status]');
        
        // Debug log
        console.log('Filter buttons found:', filterButtons.length);
        console.log('Table rows found:', tableRows.length);

        // Add this debug line
        tableRows.forEach(row => {
            console.log('Row status:', row.getAttribute('data-status'));
        });

        filterButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const filter = this.getAttribute('data-filter');
                console.log('Filter clicked:', filter);

                tableRows.forEach(row => {
                    const status = row.getAttribute('data-status');
                    console.log('Row status:', status);
                    
                    if (filter === 'all') {
                        row.style.display = '';
                    } else {
                        row.style.display = status === filter ? '' : 'none';
                    }
                });

                // Update button text
                const dropdownButton = document.getElementById('filterDropdown');
                if (dropdownButton) {
                    dropdownButton.innerHTML = `<i class="fas fa-filter me-2"></i>${this.textContent}`;
                }
            });
        });
    });

    // Search functionality
    const searchInput = document.getElementById('searchTherapists');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            const table = $('#therapistTable').DataTable();
            
            // Apply search and redraw table
            table.search(searchText).draw();
            
            // Handle input-group focus state
            const inputGroup = this.closest('.input-group');
            if (this.value) {
                inputGroup.classList.add('is-filled');
            } else {
                inputGroup.classList.remove('is-filled');
            }
        });

        // Handle focus states
        searchInput.addEventListener('focus', function() {
            this.closest('.input-group').classList.add('focused');
        });

        searchInput.addEventListener('blur', function() {
            this.closest('.input-group').classList.remove('focused');
        });
    }

    function searchTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.querySelector(".input-group-outline input");
        filter = input.value.toUpperCase();
        table = document.getElementById("therapistTable");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        break;
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    }

    // Add Therapist Modal
    function showAddTherapistModal() {
        const modalContent = `
        <div class="modal fade" id="addTherapistModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Therapist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="addTherapistForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <!-- Profile Picture Section -->
                            <div class="text-center mb-4 border-bottom pb-4">
                                <img id="newProfilePreview" 
                                     src="../../assets/img/default-avatar.png" 
                                     class="avatar avatar-xxl rounded-circle mb-3" 
                                     style="width: 128px; height: 128px; object-fit: cover;">
                                <div class="form-group">
                                    <input type="file" 
                                           class="form-control" 
                                           name="profile_picture" 
                                           id="newProfilePicture" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           style="max-width: 300px; margin: 0 auto;">
                                </div>
                            </div>
                            <!-- Rest of your form fields -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Add Therapist</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modal = new bootstrap.Modal(document.getElementById('addTherapistModal'));
        modal.show();

        // Remove modal from DOM after it's hidden
        document.getElementById('addTherapistModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Update Therapist Modal
    function showUpdateTherapistModal(therapist) {
        const modalContent = `
        <div class="modal fade" id="updateTherapistModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Therapist</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="updateTherapistForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <!-- Profile Picture Section -->
                            <div class="text-center mb-4 border-bottom pb-4">
                                <img id="updateProfilePreview" 
                                     src="../../admin_operations/get_profile_picture.php?user_id=${therapist.therapist_id}&user_type=therapist" 
                                     class="avatar avatar-xxl rounded-circle mb-3" 
                                     style="width: 128px; height: 128px; object-fit: cover;"
                                     onerror="this.src='../../assets/img/default-avatar.png'">
                                <div class="form-group">
                                    <input type="file" 
                                           class="form-control" 
                                           name="profile_picture" 
                                           id="updateProfilePicture" 
                                           accept="image/jpeg,image/png,image/jpg"
                                           style="max-width: 300px; margin: 0 auto;">
                                </div>
                            </div>
                            <input type="hidden" name="therapist_id" value="${therapist.therapist_id}">
                            <!-- Rest of your form fields -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

        // Add modal to body and show it
        document.body.insertAdjacentHTML('beforeend', modalContent);
        const modal = new bootstrap.Modal(document.getElementById('updateTherapistModal'));
        modal.show();

        // Remove modal from DOM after it's hidden
        document.getElementById('updateTherapistModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // File preview handler for update form
    $(document).on('change', '#updateProfilePicture', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#updateProfilePreview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Update Form Submission
    $(document).on('submit', '#updateTherapistForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Debug: Log form data
        console.log('Therapist ID:', formData.get('therapist_id'));
        console.log('Has file:', formData.has('profile_picture'));
        if (formData.has('profile_picture')) {
            console.log('File size:', formData.get('profile_picture').size);
        }
        
        // Verify therapist_id is present
        if (!formData.get('therapist_id')) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Therapist ID is missing',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary'
                },
                buttonsStyling: false
            });
            return;
        }
        
        $.ajax({
            url: '../../admin_operations/update_therapist.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response);
                
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            customClass: {
                                confirmButton: 'btn bg-gradient-primary'
                            },
                            buttonsStyling: false
                        }).then(() => {
                            $('#updateTherapistModal').modal('hide');
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to update therapist',
                            customClass: {
                                confirmButton: 'btn bg-gradient-primary'
                            },
                            buttonsStyling: false
                        });
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary'
                        },
                        buttonsStyling: false
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
                console.error('Server response:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update therapist. Please try again.',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    },
                    buttonsStyling: false
                });
            }
        });
    });

    // Function to show/hide inactive therapists
    function toggleInactiveTherapists() {
        const showInactive = $('#showInactive').is(':checked');
        $('.therapist-row').each(function() {
            const status = $(this).data('status');
            if (status === 'inactive') {
                $(this).toggle(showInactive);
            }
        });
    }

    // Add this to your table header
    const tableHeader = `
    <div class="table-header d-flex justify-content-between align-items-center mb-3">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="showInactive">
            <label class="form-check-label" for="showInactive">
                Show Inactive Therapists
            </label>
        </div>
        <button class="btn bg-gradient-primary" onclick="showAddTherapistModal()">
            Add New Therapist
        </button>
    </div>`;

    // Update your therapist row template
    function getTherapistRowTemplate(therapist) {
        return `
        <tr class="therapist-row" data-status="${therapist.status}">
            <td>
                <div class="d-flex px-2 py-1">
                    <div>
                        <img src="../../admin_operations/get_profile_picture.php?user_id=${therapist.therapist_id}&user_type=therapist" 
                             class="avatar avatar-sm me-3" 
                             onerror="this.src='../../assets/img/default-avatar.png'">
                    </div>
                    <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">${therapist.firstname} ${therapist.lastname}</h6>
                        <p class="text-xs text-secondary mb-0">${therapist.email}</p>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge badge-sm bg-gradient-${therapist.status === 'active' ? 'success' : 'secondary'}">
                    ${therapist.status}
                </span>
            </td>
            <td class="align-middle text-center">
                <button class="btn btn-link text-secondary mb-0" 
                        onclick="showUpdateTherapistModal(${JSON.stringify(therapist).replace(/"/g, '&quot;')})">
                    <i class="fa fa-edit text-xs"></i>
                </button>
                <button class="btn btn-link text-secondary mb-0" 
                        onclick="toggleTherapistStatus('${therapist.therapist_id}', '${therapist.status}')">
                    <i class="fa fa-${therapist.status === 'active' ? 'times' : 'undo'} text-xs"></i>
                </button>
            </td>
        </tr>`;
    }

    // Add toggle status function
    function toggleTherapistStatus(therapistId, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = currentStatus === 'active' ? 'deactivate' : 'reactivate';
        
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${action} this therapist?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            customClass: {
                confirmButton: 'btn bg-gradient-primary mx-1',
                cancelButton: 'btn bg-gradient-secondary mx-1'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '../../admin_operations/update_therapist_status.php',
                    type: 'POST',
                    data: {
                        therapist_id: therapistId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: `Therapist ${action}d successfully`,
                                customClass: {
                                    confirmButton: 'btn bg-gradient-primary'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                customClass: {
                                    confirmButton: 'btn bg-gradient-primary'
                                },
                                buttonsStyling: false
                            });
                        }
                    }
                });
            }
        });
    }

    // Event handler for show inactive checkbox
    $(document).on('change', '#showInactive', toggleInactiveTherapists);

    // Initialize table with inactive rows hidden
    $(document).ready(function() {
        toggleInactiveTherapists();
    });
}); 