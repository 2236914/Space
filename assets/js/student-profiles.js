$(document).ready(function() {
    // Initialize DataTable
    var table = $('#studentsProfileTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ordering: true,
        ajax: {
            url: '../../admin_operations/get_student_profiles.php',
            dataSrc: function(json) {
                console.log('Received data:', json);
                return json.data || [];
             }
        },
        columns: [
            {
                data: null,
                render: function(data, type, row) {
                    if (!row.firstname) return ''; // Safety check
                    return `<div class="d-flex px-2 py-1">
                        <div>
                            <img src="../../admin_operations/get_profile_picture.php?user_id=${row.srcode}&user_type=student" 
                                 class="avatar avatar-sm me-3 border-radius-lg" 
                                 onerror="this.src='../../assets/img/default-avatar.png';">
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${row.firstname} ${row.lastname}</h6>
                            <p class="text-xs text-secondary mb-0">${row.srcode}</p>
                        </div>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (!row.course) return ''; // Safety check
                    return `<div class="d-flex flex-column">
                        <p class="text-xs font-weight-bold mb-0">${row.course}</p>
                        <p class="text-xs text-secondary mb-0">${row.year_section} | ${row.department}</p>
                    </div>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    if (!row.email) return ''; // Safety check
                    return `<div class="d-flex flex-column">
                        <p class="text-xs font-weight-bold mb-0">${row.phonenum}</p>
                        <p class="text-xs text-secondary mb-0">${row.email}</p>
                    </div>`;
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    if (!row.status) return ''; // Safety check
                    return `<span class="badge badge-sm bg-gradient-${getStatusClass(row.status)}">${row.status}</span>`;
                }
            },
            {
                data: 'created_date',
                render: function(data, type, row) {
                    return row.created_date || '';
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `<button class="btn btn-link text-secondary mb-0 view-student" 
                            data-srcode="${row.srcode || ''}"
                            data-bs-toggle="tooltip"
                            title="View Profile">
                        <i class="fas fa-eye text-xs"></i>
                    </button>
                    <button class="btn btn-link text-secondary mb-0 edit-student" 
                            data-srcode="${row.srcode || ''}"
                            data-bs-toggle="tooltip"
                            title="Edit Profile">
                        <i class="fas fa-edit text-xs"></i>
                    </button>`;
                }
            }
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search students..."
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        // Add error handling
        error: function(xhr, error, thrown) {
            console.error('DataTables error:', error);
            showErrorAlert('Error loading data');
        }
    });

    // Helper function for status classes
    function getStatusClass(status) {
        const statusClasses = {
            'active': 'success',
            'deactivated': 'secondary',
            'suspended': 'warning'
        };
        return statusClasses[status.toLowerCase()] || 'secondary';
    }

    // Custom search functionality
    $('#searchStudents').on('keyup', function() {
        table.search(this.value).draw();
    });

    // Filter handling
    $('.dropdown-item').on('click', function(e) {
        e.preventDefault();
        const filterValue = $(this).data('filter');
        
        // Update button text
        $('#filterDropdown').html(`<i class="fas fa-filter me-2"></i>${$(this).text()}`);

        if (filterValue === 'all') {
            table.column(3).search('').draw();
        } else {
            table.column(3).search(filterValue).draw();
        }
    });

    // Add error handling for AJAX requests
    $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        console.error('Ajax error occurred:', thrownError);
        console.error('Response:', jqxhr.responseText);
    });

    // Add Student Button Click Handler
    $('#addNewStudent').on('click', function() {
        const modalContent = `
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title text-white">Add New Student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addStudentForm" enctype="multipart/form-data">
                        <div class="modal-body">
                            <!-- Profile Picture Section -->
                            <div class="text-center mb-4">
                                <img id="profilePreview" src="../../assets/img/default-avatar.png" 
                                     class="avatar avatar-xxl rounded-circle mb-3">
                                <div class="form-group">
                                    <input type="file" class="form-control" name="profile_picture" id="profilePicture">
                                </div>
                            </div>

                            <!-- Required Fields Section -->
                            <h6 class="text-primary mb-3">Required Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="firstname" placeholder="First Name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="lastname" placeholder="Last Name" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="email" class="form-control" name="email" placeholder="Email" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="tel" 
                                            class="form-control" 
                                            name="phonenum" 
                                            placeholder="09XXXXXXXXX"
                                            pattern="09[0-9]{9}"
                                            title="Please enter a valid Philippine mobile number (e.g., 09123456789)"
                                            maxlength="11"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 11)"
                                            required>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="srcode" placeholder="SR-Code" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <div class="position-relative w-100">
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                                            <span class="position-absolute top-50 end-0 translate-middle-y pe-3" style="cursor: pointer;">
                                                <i class="fas fa-eye toggle-password" data-target="password"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Fields Section -->
                            <h6 class="text-primary mb-3 mt-4">Additional Information</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="course" placeholder="Course">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="department" placeholder="Department">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <div class="input-group input-group-outline">
                                        <input type="number" class="form-control" name="year" placeholder="Year" min="1" max="5">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="section" placeholder="Section">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-outline">
                                        <input type="text" class="form-control" name="address" placeholder="Address">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn bg-gradient-primary">Add Student</button>
                        </div>
                    </form>
                </div>
            </div>`;

        $('#studentModal').html(modalContent).modal('show');

        // Password visibility toggle
        $('.toggle-password').click(function() {
            const targetId = $(this).data('target');
            const input = $(`#${targetId}`);
            const icon = $(this);

            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });

        // View Student Handler
        $(document).on('click', '.view-student', function() {
            const srcode = $(this).data('srcode');
            
            $.ajax({
                url: '../../admin_operations/view_student.php',
                type: 'GET',
                data: { id: srcode },
                success: function(response) {
                    if (response.success) {
                        const student = response.data;
                        const modalContent = `
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">View Student Profile</h5>
                                        <button type="button" class="btn-close text-dark" data-bs-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center">
                                            <img src="../../admin_operations/get_profile_picture.php?user_id=${student.srcode}&user_type=student" 
                                                 class="profile-picture-preview"
                                                 onerror="this.src='../../assets/img/default-avatar.png'">
                                        </div>
                                        <div class="mt-3">
                                            <h6>Personal Information</h6>
                                            <p><strong>Name:</strong> ${student.firstname} ${student.lastname}</p>
                                            <p><strong>SR-Code:</strong> ${student.srcode}</p>
                                            <p><strong>Email:</strong> ${student.email}</p>
                                            <p><strong>Phone:</strong> ${student.phonenum}</p>
                                            
                                            <h6 class="mt-4">Academic Information</h6>
                                            <p><strong>Course:</strong> ${student.course}</p>
                                            <p><strong>Year:</strong> ${student.year || ''}</p>
                                            <p><strong>Section:</strong> ${student.section || ''}</p>
                                            <p><strong>Department:</strong> ${student.department}</p>
                                            <p><strong>Status:</strong> 
                                                <span class="badge badge-sm bg-gradient-${getStatusClass(student.status)}">
                                                    ${student.status}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
    
                        $('#studentModal').html(modalContent).modal('show');
                    } else {
                        showErrorAlert(response.message || 'Failed to load student data');
                    }
                },
                error: function() {
                    showErrorAlert('Failed to load student data. Please try again.');
                }
            });
        });
    
        // Edit Student Handler
        $(document).on('click', '.edit-student', function() {
            const srcode = $(this).data('srcode');
            
            $.ajax({
                url: '../../admin_operations/view_student.php',
                type: 'GET',
                data: { id: srcode },
                success: function(response) {
                    if (response.success) {
                        const student = response.data;
                        const modalContent = `
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Student</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form id="editStudentForm">
                                        <div class="modal-body">
                                            <input type="hidden" name="srcode" value="${student.srcode}">
                                            <div class="text-center">
                                                <img id="editProfilePreview" 
                                                     src="../../admin_operations/get_profile_picture.php?user_id=${student.srcode}&user_type=student" 
                                                     class="profile-picture-preview"
                                                     onerror="this.src='../../assets/img/default-avatar.png'">
                                                <div class="form-group mt-3">
                                                    <input type="file" class="form-control" name="profile_picture" id="editProfilePicture">
                                                </div>
                                            </div>
                                            <div class="row g-3 mt-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>First Name</label>
                                                        <input type="text" class="form-control" name="firstname" value="${student.firstname}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Last Name</label>
                                                        <input type="text" class="form-control" name="lastname" value="${student.lastname}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Email</label>
                                                        <input type="email" class="form-control" name="email" value="${student.email}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Phone Number</label>
                                                        <input type="text" class="form-control" name="phonenum" value="${student.phonenum}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Course</label>
                                                        <input type="text" class="form-control" name="course" value="${student.course || ''}">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Year</label>
                                                        <input type="number" class="form-control" name="year" value="${student.year || ''}" min="1" max="5">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Section</label>
                                                        <input type="text" class="form-control" name="section" value="${student.section || ''}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Department</label>
                                                        <input type="text" class="form-control" name="department" value="${student.department}">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control" name="status">
                                                            <option value="active" ${student.status === 'active' ? 'selected' : ''}>Active</option>
                                                            <option value="deactivated" ${student.status === 'deactivated' ? 'selected' : ''}>Deactivated</option>
                                                            <option value="suspended" ${student.status === 'suspended' ? 'selected' : ''}>Suspended</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>`;
    
                        $('#studentModal').html(modalContent).modal('show');
                    } else {
                        showErrorAlert(response.message || 'Failed to load student data');
                    }
                },
                error: function() {
                    showErrorAlert('Failed to load student data. Please try again.');
                }
            });
        });

    // Form Submissions
    // Add Student Form Submit
    $(document).on('submit', '#addStudentForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Debug: Log form data
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Add loading state
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');

        $.ajax({
            url: '../../admin_operations/aadd_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response); // Debug: Log server response
                if (response.success) {
                    $('#studentModal').modal('hide');
                    showSuccessAlert(response.message).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    showErrorAlert(response.message || 'Failed to add student. Please check all fields.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', {xhr, status, error}); // Debug: Log error details
                showErrorAlert('An error occurred while adding the student. Please try again.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Add Student');
            }
        });
    });

    // Add this function to validate phone number format
    function isValidPhoneNumber(phone) {
        return /^09\d{9}$/.test(phone);
    }

    // Edit Student Form Submit
    $(document).on('submit', '#editStudentForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Debug: Log form data
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: '../../admin_operations/update_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response); // Add this debug line
                if (response.success) {
                    $('#studentModal').modal('hide');
                    showSuccessAlert(response.message).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr, status, error) {  // Add error handling
                console.error('Ajax error:', {xhr, status, error});
                showErrorAlert('An error occurred while updating the student.');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Save Changes');
            }
        });
    });

    // Profile Picture Preview Handlers
    $(document).on('change', '#profilePicture, #editProfilePicture', function() {
        const file = this.files[0];
        const previewId = this.id === 'profilePicture' ? 'profilePreview' : 'editProfilePreview';
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(`#${previewId}`).attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Helper Functions
    function showSuccessAlert(message) {
        return Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            customClass: {
                container: 'custom-swal-container',
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-success'
            },
            buttonsStyling: false,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: true
        });
    }

    function showErrorAlert(message) {
        return Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            customClass: {
                container: 'custom-swal-container',
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false,
            showConfirmButton: true
        });
    }

    // Add warning alert for additional functionality
    function showWarningAlert(message) {
        return Swal.fire({
            icon: 'warning',
            title: 'Warning',
            text: message,
            customClass: {
                container: 'custom-swal-container',
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-warning'
            },
            buttonsStyling: false,
            showConfirmButton: true
        });
    }
}); 