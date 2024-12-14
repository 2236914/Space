$(document).ready(function() {
    // View Post Function
    function viewPost(postId) {
        // Show loading state with custom styling
        Swal.fire({
            title: 'Loading...',
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content'
            },
            allowOutsideClick: false
        });

        // Fetch post details
        $.ajax({
            url: '../../admin_operations/get_post_details.php',
            type: 'GET',
            data: { post_id: postId },
            success: function(response) {
                if (response.success) {
                    const post = response.data;
                    let modalContent = `
                        <div class="post-details">
                            <div class="author-info mb-3">
                                <div class="d-flex align-items-center">
                                    <h6 class="mb-0">${post.username}</h6>
                                    <span class="badge bg-gradient-${post.post_type === 'student' ? 'info' : 'primary'} ms-2">
                                        ${post.post_type}
                                    </span>
                                </div>
                                <small class="text-muted">${new Date(post.created_at).toLocaleString()}</small>
                            </div>
                            <div class="post-content mb-4">
                                <p>${post.content}</p>
                                ${post.image_file ? `
                                    <div class="post-image mt-2">
                                        <img src="data:image/jpeg;base64,${post.image_file}" 
                                             class="img-fluid rounded" 
                                             alt="Post Image">
                                    </div>` : ''}
                            </div>
                            <div class="engagement-info mb-3">
                                <span class="badge bg-gradient-success me-2">
                                    ${post.likes} likes
                                </span>
                                <span class="badge bg-gradient-info">
                                    ${post.comment_count} comments
                                </span>
                            </div>
                            ${post.comments && post.comments.length > 0 ? `
                                <div class="comments-section">
                                    <h6 class="mb-3">Comments</h6>
                                    ${post.comments.map(comment => `
                                        <div class="comment mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>${comment.username}</strong>
                                                <small class="text-muted">${new Date(comment.created_at).toLocaleString()}</small>
                                            </div>
                                            <p class="mb-0">${comment.comment_text}</p>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                            ${post.reports && post.reports.length > 0 ? `
                                <div class="reports-section mt-4">
                                    <h6 class="mb-3">Reports</h6>
                                    ${post.reports.map(report => `
                                        <div class="report mb-2">
                                            <div class="d-flex justify-content-between">
                                                <strong>${report.reporter_username}</strong>
                                                <small class="text-muted">${new Date(report.created_at).toLocaleString()}</small>
                                            </div>
                                            <p class="mb-0">
                                                <span class="badge bg-gradient-danger">${report.report_type}</span>
                                                ${report.reason ? `- ${report.reason}` : ''}
                                            </p>
                                        </div>
                                    `).join('')}
                                </div>
                            ` : ''}
                        </div>
                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                    `;

                    // Update modal content and show
                    $('#viewPostModal .modal-body').html(modalContent);
                    Swal.close();
                    $('#viewPostModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load post details',
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
                console.error('AJAX Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to connect to server',
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

    // Hide Post Function
    function hidePost(postId) {
        // First show confirmation
        Swal.fire({
            title: 'Hide Post',
            text: 'Are you sure you want to hide this post?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, hide it',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-warning',
                cancelButton: 'btn bg-gradient-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Hiding post...',
                    html: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content'
                    }
                });

                // Make the AJAX call
                $.ajax({
                    url: '../../admin_operations/update_post_status.php',
                    type: 'POST',
                    data: {
                        post_id: postId,
                        status: 'hidden'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Post has been hidden successfully',
                                timer: 1500,
                                showConfirmButton: false,
                                customClass: {
                                    popup: 'custom-swal-popup',
                                    title: 'custom-swal-title',
                                    content: 'custom-swal-content'
                                }
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to hide post',
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
                        Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Failed to connect to server. Please try again.',
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
    }

    // Activate Post Function
    function activatePost(postId) {
        Swal.fire({
            title: 'Activate Post',
            text: 'Are you sure you want to activate this post?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, activate it',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'btn bg-gradient-success',
                cancelButton: 'btn bg-gradient-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                updatePostStatus(postId, 'active');
            }
        });
    }

    // Update Post Status Function
    function updatePostStatus(postId, status) {
        Swal.fire({
            title: 'Processing...',
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title'
            },
            allowOutsideClick: false
        });

        $.ajax({
            url: '../../admin_operations/update_post_status.php',
            type: 'POST',
            data: {
                post_id: postId,
                status: status
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: `Post has been ${status}`,
                        customClass: {
                            popup: 'custom-swal-popup',
                            title: 'custom-swal-title',
                            content: 'custom-swal-content',
                            confirmButton: 'btn bg-gradient-success'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || `Failed to ${status} post`,
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
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to connect to server',
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

    // Make functions globally accessible
    window.viewPost = viewPost;
    window.hidePost = hidePost;
    window.activatePost = activatePost;
    window.updatePostStatus = updatePostStatus;

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});