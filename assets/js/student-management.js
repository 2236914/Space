$(document).ready(function() {
    // First, check if DataTable already exists and destroy it
    if ($.fn.DataTable.isDataTable('#studentsTable')) {
        $('#studentsTable').DataTable().destroy();
        $('#studentsTable').empty(); // Clear the table contents
    }

    // Debug AJAX call
    $.ajax({
        url: '../../admin_operations/get_students.php',
        success: function(response) {
            console.log('Raw API Response:', response);
        },
        error: function(xhr, status, error) {
            console.error('API Error:', error);
            console.log('Response:', xhr.responseText);
        }
    });

    // Initialize DataTable
    var table = $('#studentsTable').DataTable({
        processing: true,
        serverSide: false,
        responsive: true,
        ajax: {
            url: '../../admin_operations/get_students.php',
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
                return `<div class="d-flex flex-column">
                    <p class="text-xs font-weight-bold mb-0">${row.course || ''}</p>
                    <p class="text-xs text-secondary mb-0">${row.year_section} | ${row.department || ''}</p>
                </div>`;
            }
        },
        {
            data: null,
            render: function(data, type, row) {
                return `<div class="d-flex flex-column">
                    <p class="text-xs font-weight-bold mb-0">${row.phonenum || ''}</p>
                    <p class="text-xs text-secondary mb-0">${row.email || ''}</p>
                </div>`;
            }
        },
        {
            data: 'status',
            render: function(data, type, row) {
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
                        data-srcode="${row.srcode}"
                        data-bs-toggle="tooltip"
                        title="View Profile">
                    <i class="fas fa-eye text-xs"></i>
                </button>
                <button class="btn btn-link text-secondary mb-0 edit-student" 
                        data-srcode="${row.srcode}"
                        data-bs-toggle="tooltip"
                        title="Edit Profile">
                    <i class="fas fa-edit text-xs"></i>
                </button>`;
            }
        }
    ],
    responsive: true,
    language: {
        search: "_INPUT_",
        searchPlaceholder: "Search students..."
    },
    dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
         "<'row'<'col-sm-12'tr>>" +
         "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
    // Add error handling here, inside the DataTable configuration
    error: function(xhr, error, thrown) {
        console.error('DataTables error:', error);
        showErrorAlert('Error loading data');
    }
});

// Add global AJAX error handling after the DataTable initialization
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    console.error('Ajax error occurred:', thrownError);
    console.error('Response:', jqxhr.responseText);
});

// Add after DataTable initialization
table.on('order.dt', function() {
    console.log('Sort order:', table.order());
});

// Add after DataTable initialization
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    console.error('Ajax error occurred:', thrownError);
    console.error('Response:', jqxhr.responseText);
    showErrorAlert('Failed to load data. Please try refreshing the page.');
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
    const searchValue = $(this).val().toLowerCase();
    
    table.rows().every(function() {
        const data = this.data();
        const rowText = [
            data.firstname,
            data.lastname,
            data.srcode,
            data.course,
            data.year_section,
            data.department,
            data.email,
            data.phonenum
        ].join(' ').toLowerCase();

        if (rowText.includes(searchValue)) {
            $(this.node()).show();
        } else {
            $(this.node()).hide();
        }
    });
});

    // Filter handling
$('.dropdown-item').on('click', function(e) {
    e.preventDefault();
    const filterValue = $(this).data('filter');
    
    // Update button text
    $('#filterDropdown').html(`<i class="fas fa-filter me-2"></i>${$(this).text()}`);

    // Simple jQuery filtering
    if (filterValue === 'all') {
        $('.table tbody tr').show();
    } else {
        $('.table tbody tr').hide();
        $(`.table tbody tr:contains('${filterValue}')`).show();
    }
});

    // Export to CSV
    $('#exportCSV').on('click', function() {
        window.location.href = '../../admin_operations/export_students.php';
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
                            <div class="text-center mb-4 border-bottom pb-4">
                                <img id="profilePreview" 
                                     src="../../assets/img/default-avatar.png" 
                                     class="avatar avatar-xxl rounded-circle mb-3" 
                                     style="width: 128px; height: 128px; object-fit: cover;">
                                <div class="form-group">
                                    <input type="file" 
                                           class="form-control" 
                                           name="profile_picture" 
                                           id="profilePicture" 
                                           accept="image/*"
                                           style="max-width: 300px; margin: 0 auto;">
                                    <small class="form-text text-muted">Upload profile picture (optional)</small>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-primary mb-3">Personal Information</h6>
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
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-primary mb-3">Contact Information</h6>
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
                                                   placeholder="09XXXXXXXXX"
                                                   required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Academic Information Section -->
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-primary mb-3">Academic Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Course</label>
                                            <input type="text" class="form-control border" name="course" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Year</label>
                                            <input type="text" class="form-control border" name="year" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">Section</label>
                                            <input type="text" class="form-control border" name="section" required>
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
                                            <label class="form-label">SR-Code</label>
                                            <input type="text" class="form-control border" name="srcode" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control border" name="password" required>
                                        </div>
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

        // Image preview handler
        $('#profilePicture').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profilePreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Helper function for SweetAlert
    function showSuccessAlert(message) {
        return Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-success'
            },
            buttonsStyling: false,
            showConfirmButton: true,
            confirmButtonText: 'OK'
        });
    }

    function showErrorAlert(message) {
        return Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false,
            showConfirmButton: true,
            confirmButtonText: 'OK'
        });
    }

    function showConfirmDialog(title, text) {
        return Swal.fire({
            icon: 'warning',
            title: title,
            text: text,
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-success',
                cancelButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            reverseButtons: true
        });
    }

    // Form Submit Handlers
    $(document).on('submit', '#addStudentForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        $.ajax({
            url: '../../admin_operations/add_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showSuccessAlert(response.message).then(() => {
                        $('#studentModal').modal('hide');
                        table.ajax.reload();
                    });
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function() {
                showErrorAlert('Failed to add student');
            }
        });
    });

    // Edit Student Form Submit
    $(document).on('submit', '#editStudentForm', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Show confirmation dialog
        Swal.fire({
            title: 'Update Student',
            text: 'Are you sure you want to update this student\'s information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-success',
                cancelButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Get current student data
                const srcode = formData.get('srcode');

                // Remove empty fields and use existing data
                for (let pair of formData.entries()) {
                    const [key, value] = pair;
                    const currentValue = $(`[name="${key}"]`).data('current');
                    
                    // If field is empty, remove it from formData
                    if (!value.trim() && key !== 'srcode') {
                        formData.delete(key);
                    }
                }

                // Always include srcode
                formData.append('srcode', srcode);

                $.ajax({
                    url: '../../admin_operations/update_student.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            $('#studentModal').modal('hide');
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    title: 'custom-swal-title',
                                    content: 'custom-swal-content',
                                    confirmButton: 'btn bg-gradient-success'
                                },
                                buttonsStyling: false
                            }).then(() => {
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message,
                                icon: 'error',
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    title: 'custom-swal-title',
                                    content: 'custom-swal-content',
                                    confirmButton: 'btn bg-gradient-danger'
                                },
                                buttonsStyling: false
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to update student',
                            icon: 'error',
                            customClass: {
                                popup: 'custom-swal-popup',
                                title: 'custom-swal-title',
                                content: 'custom-swal-content',
                                confirmButton: 'btn bg-gradient-danger'
                            },
                            buttonsStyling: false
                        });
                    }
                });
            }
        });
    });

    // Profile picture preview
    $(document).on('change', '#profilePicture', function() {
        // Remove empty fields and use existing data
        for (let pair of formData.entries()) {
            const [key, value] = pair;
            const currentValue = $(`[name="${key}"]`).data('current');
            
            // If field is empty, remove it from formData
            if (!value.trim() && key !== 'srcode') {
                formData.delete(key);
            }
        }

        // Always include srcode
        formData.append('srcode', srcode);

        $.ajax({
            url: '../../admin_operations/update_student.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#studentModal').modal('hide');
                    showSuccessAlert(response.message).then(() => {
                        table.ajax.reload();
                    });
                } else {
                    showErrorAlert(response.message || 'Failed to update student');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showErrorAlert('Failed to update student');
            }
        });
    });

    // Profile picture preview
    $(document).on('change', '#profilePicture', function() {
        console.log('Profile picture changed');
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#profilePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // View and Edit button handlers
    $(document).on('click', '.view-student', function() {
        const srcode = $(this).data('srcode');
        console.log('View button clicked');
        console.log('SR-Code:', srcode);
        
        $.ajax({
            url: '../../admin_operations/view_student.php',
            type: 'GET',
            data: { id: srcode },
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.success) {
                    const student = response.data;
                    const modalContent = `
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-gradient-info">
                                    <h5 class="modal-title text-white">Student Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Profile Picture Section -->
                                    <div class="text-center mb-4 border-bottom pb-4">
                                        <img src="../../admin_operations/get_profile_picture.php?user_id=${student.srcode}&user_type=student" 
                                             class="avatar avatar-xxl rounded-circle mb-3" 
                                             style="width: 128px; height: 128px; object-fit: cover;"
                                             onerror="this.src='../../assets/img/default-avatar.png'">
                                    </div>

                                    <!-- Personal Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Personal Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">First Name</label>
                                                <p class="form-control-static">${student.firstname}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Last Name</label>
                                                <p class="form-control-static">${student.lastname}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Contact Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Email</label>
                                                <p class="form-control-static">${student.email}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Contact Number</label>
                                                <p class="form-control-static">${student.contact_number}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Academic Information Section -->
                                    <div class="border-bottom pb-3 mb-3">
                                        <h6 class="text-info mb-3">Academic Information</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Course</label>
                                                <p class="form-control-static">${student.course}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Year & Section</label>
                                                <p class="form-control-static">${student.year}-${student.section}</p>
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
                                                    <span class="badge bg-gradient-${getStatusClass(student.status)}">${student.status}</span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label text-muted">Created Date</label>
                                                <p class="form-control-static">${student.created_date}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>`;

                    $('#studentModal').html(modalContent).modal('show');
                } else {
                    showErrorAlert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                showErrorAlert('Failed to fetch student details');
            }
        });
    });

    // Edit Student Handler
$(document).on('click', '.edit-student', function() {
    const srcode = $(this).data('srcode');
    console.log('Edit button clicked');
    console.log('SR-Code:', srcode);
    
    $.ajax({
        url: '../../admin_operations/view_student.php',
        type: 'GET',
        data: { id: srcode },
        success: function(response) {
            try {
                // Parse response if it's a string
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                console.log('Parsed Response:', data);

                if (data.success) {
                    const student = data.data;
                    const modalContent = `
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title text-white">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editStudentForm">
                <div class="modal-body">
                    <input type="hidden" name="srcode" value="${student.srcode}">
                    
                    <!-- Personal Information -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="firstname" 
                                       placeholder="First Name"
                                       data-current="${student.firstname}"
                                       value="${student.firstname}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="lastname" 
                                       placeholder="Last Name"
                                       data-current="${student.lastname}"
                                       value="${student.lastname}">
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <input type="email" 
                                       class="form-control" 
                                       name="email" 
                                       placeholder="Email"
                                       data-current="${student.email}"
                                       value="${student.email}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="phonenum" 
                                       placeholder="Phone Number"
                                       data-current="${student.phonenum}"
                                       value="${student.phonenum}">
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="course" 
                                       placeholder="Course"
                                       data-current="${student.course}"
                                       value="${student.course}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="year_section" 
                                       placeholder="Year & Section"
                                       data-current="${student.year_section}"
                                       value="${student.year_section}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-outline">
                                <input type="text" 
                                       class="form-control" 
                                       name="department" 
                                       placeholder="Department"
                                       data-current="${student.department}"
                                       value="${student.department}">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <div class="input-group input-group-outline">
                                <select class="form-control" 
                                        name="status" 
                                        data-current="${student.status}">
                                    <option value="active" ${student.status === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="inactive" ${student.status === 'inactive' ? 'selected' : ''}>Inactive</option>
                                    <option value="suspended" ${student.status === 'suspended' ? 'selected' : ''}>Suspended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn bg-gradient-info">Save Changes</button>
                </div>
            </form>
        </div>
    </div>`;

                    $('#studentModal').html(modalContent).modal('show');
                } else {
                    showErrorAlert(data.message || 'Failed to fetch student details');
                }
            } catch (error) {
                console.error('Error parsing response:', error);
                showErrorAlert('Failed to process student details');
            }
        },
        error: function(xhr, status, error) {
            console.error('Ajax Error:', error);
            console.error('Status:', status);
            console.error('Response:', xhr.responseText);
            showErrorAlert('Failed to fetch student details');
        }
    });
});

    // For delete functionality (if needed)
    function deleteStudent(srcode) {
        showConfirmDialog('Delete Student', 'Are you sure you want to delete this student?')
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '../../admin_operations/delete_student.php',
                        type: 'POST',
                        data: { srcode: srcode },
                        success: function(response) {
                            if (response.success) {
                                showSuccessAlert(response.message).then(() => {
                                    table.ajax.reload();
                                });
                            } else {
                                showErrorAlert(response.message);
                            }
                        },
                        error: function() {
                            showErrorAlert('Failed to delete student');
                        }
                    });
                }
            });
    }

    // Add this for tooltips
    $(function () {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });

    // Add this for search styling
    $('.dataTables_filter input').addClass('form-control');
    $('.dataTables_length select').addClass('form-control');

    // Form submission handlers
    $(document).ready(function() {
        // Add Student Form Submit
        $(document).on('submit', '#addStudentForm', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            $.ajax({
                url: '../../admin_operations/add_student.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#studentModal').modal('hide');
                        showSuccessAlert(response.message).then(() => {
                            table.ajax.reload();
                        });
                    } else {
                        showErrorAlert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showErrorAlert('Failed to add student');
                }
            });
        });

        // Edit Student Form Submit
        $(document).on('submit', '#editStudentForm', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const srcode = formData.get('srcode');

            // Remove empty fields and use existing data
            for (let pair of formData.entries()) {
                const [key, value] = pair;
                const currentValue = $(`[name="${key}"]`).data('current');
                
                // If field is empty, remove it from formData
                if (!value.trim() && key !== 'srcode') {
                    formData.delete(key);
                }
            }

            // Always include srcode
            formData.append('srcode', srcode);

            $.ajax({
                url: '../../admin_operations/update_student.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#studentModal').modal('hide');
                        showSuccessAlert(response.message).then(() => {
                            table.ajax.reload();
                        });
                    } else {
                        showErrorAlert(response.message || 'Failed to update student');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    showErrorAlert('Failed to update student');
                }
            });
        });

        // Export CSV
        $('#exportCSV').on('click', function() {
            window.location.href = '../../admin_operations/export_students.php';
        });

        // Profile Picture Preview (for both add and edit forms)
        $(document).on('change', '#profilePicture', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profilePreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            }
        });

        // Form Validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }

        // Reset form when modal is closed
        $('.modal').on('hidden.bs.modal', function() {
            const form = $(this).find('form');
            if (form.length > 0) {
                form[0].reset();
                $('#profilePreview').attr('src', '../../assets/img/default-avatar.png');
            }
        });

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Table styling
        $('.table').addClass('table-striped');
        $('.dataTables_info').addClass('ms-3');
        $('.dataTables_paginate').addClass('me-3');
        $('.paginate_button').addClass('px-3 py-2');
        $('.paginate_button.current').addClass('text-white bg-gradient-primary');

        try {
            const filterValue = $(this).data('filter');
            if (!filterValue) throw new Error('Invalid filter value');
            
            // Show loading state
            $('#filterDropdown').html('<i class="fas fa-spinner fa-spin me-2"></i>Filtering...');
            
            // Apply filter
            if (filterValue === 'all') {
                $('.table tbody tr').show();
            } else {
                $('.table tbody tr').hide();
                $(`.table tbody tr td:contains('${filterValue}')`).parent().show();
            }
            
            // Update button text
            $('#filterDropdown').html(`<i class="fas fa-filter me-2"></i>${$(this).text()}`);
            
        } catch (error) {
            console.error('Filter error:', error);
            $('#filterDropdown').html('<i class="fas fa-exclamation-triangle me-2"></i>Filter Error');
            setTimeout(function() {
                $('#filterDropdown').html('<i class="fas fa-filter me-2"></i>Filter');
            }, 2000);
        }
    });
});
