function reportPost(postId) {
    const userRole = document.body.getAttribute('data-user-role'); 
    // Validate user role
    if (!['student', 'therapist'].includes(userRole)) {
        Swal.fire({
            title: 'Error',
            text: 'Unauthorized to report posts',
            icon: 'error',
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
        return;
    }
    Swal.fire({
        title: 'Report Post',
        html: `
            <div class="form-group">
                <label class="form-label">Reason for reporting:</label>
                <select class="form-control" id="reportReason">
                    <option value="">Select a reason...</option>
                    <option value="inappropriate">Inappropriate Content</option>
                    <option value="harassment">Harassment</option>
                    <option value="spam">Spam</option>
                    <option value="hate_speech">Hate Speech</option>
                    <option value="violence">Violence</option>
                    <option value="other">Other</option>
                </select>
                <div class="mt-3" id="otherReasonDiv" style="display: none;">
                    <label class="form-label">Please specify:</label>
                    <textarea class="form-control" id="otherReason" rows="3"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Report',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
            cancelButton: 'btn btn-outline-primary btn-sm mx-2',
            actions: 'justify-content-center'
        },
        buttonsStyling: false,
        didOpen: () => {
            // Show/hide "Other" reason textarea
            document.getElementById('reportReason').addEventListener('change', function() {
                const otherDiv = document.getElementById('otherReasonDiv');
                otherDiv.style.display = this.value === 'other' ? 'block' : 'none';
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = document.getElementById('reportReason').value;
            const otherReason = document.getElementById('otherReason').value;
            const finalReason = reason === 'other' ? otherReason : reason;

            if (!finalReason) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please select a reason for reporting',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-danger'
                    },
                    buttonsStyling: false
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Submitting Report',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit report
            fetch('../../admin_operations/report_handlers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=report_post&post_id=${postId}&reason=${encodeURIComponent(finalReason)}&reporter_type=${userRole}`  // Add reporter_type
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Report Submitted',
                        text: 'Thank you for reporting. We will review this post.',
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn bg-gradient-success'
                        },
                        buttonsStyling: false
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit report');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-danger'
                    },
                    buttonsStyling: false
                });
            });
        }
    });
}

function reportUser(username) {
    const userRole = document.body.getAttribute('data-user-role');
    // Validate user role
    if (!['student', 'therapist'].includes(userRole)) {
        Swal.fire({
            title: 'Error',
            text: 'Unauthorized to report users',
            icon: 'error',
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
        return;
    }
    Swal.fire({
        title: 'Report User',
        html: `
            <div class="form-group">
                <label class="form-label">Reason for reporting:</label>
                <select class="form-control" id="reportReason">
                    <option value="">Select a reason...</option>
                    <option value="harassment">Harassment</option>
                    <option value="spam">Spam Behavior</option>
                    <option value="fake">Fake Account</option>
                    <option value="inappropriate">Inappropriate Behavior</option>
                    <option value="other">Other</option>
                </select>
                <div class="mt-3" id="otherReasonDiv" style="display: none;">
                    <label class="form-label">Please specify:</label>
                    <textarea class="form-control" id="otherReason" rows="3"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Report',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
            cancelButton: 'btn btn-outline-primary btn-sm mx-2',
            actions: 'justify-content-center'
        },
        buttonsStyling: false,
        didOpen: () => {
            // Show/hide "Other" reason textarea
            document.getElementById('reportReason').addEventListener('change', function() {
                const otherDiv = document.getElementById('otherReasonDiv');
                otherDiv.style.display = this.value === 'other' ? 'block' : 'none';
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const reason = document.getElementById('reportReason').value;
            const otherReason = document.getElementById('otherReason').value;
            const finalReason = reason === 'other' ? otherReason : reason;

            if (!finalReason) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please select a reason for reporting',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-danger'
                    },
                    buttonsStyling: false
                });
                return;
            }

            // Show loading state
            Swal.fire({
                title: 'Submitting Report',
                text: 'Please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit report
            fetch('../../admin_operations/report_handlers.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=report_user&username=${encodeURIComponent(username)}&reason=${encodeURIComponent(finalReason)}&reporter_type=${userRole}`  // Add reporter_type
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Report Submitted',
                        text: 'Thank you for reporting. We will review this user.',
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn bg-gradient-success'
                        },
                        buttonsStyling: false
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit report');
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: error.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-danger'
                    },
                    buttonsStyling: false
                });
            });
        }
    });
}

// Close the reportUser function properly
