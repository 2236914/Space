/**
 * Handle therapist sign out with custom SweetAlert styling
 */
function handleSignOut() {
    Swal.fire({
        title: '<span class="text-dark">Sign Out</span>',
        text: 'Are you sure you want to end your session?',
        icon: 'question',
        iconColor: '#344767',
        showCancelButton: true,
        confirmButtonText: 'Yes, sign out',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        customClass: {
            container: 'custom-swal-container',
            popup: 'custom-swal-popup',
            header: 'custom-swal-header',
            title: 'custom-swal-title',
            closeButton: 'custom-swal-close',
            icon: 'custom-swal-icon',
            image: 'custom-swal-image',
            content: 'custom-swal-content',
            input: 'custom-swal-input',
            actions: 'custom-swal-actions',
            confirmButton: 'btn bg-gradient-primary btn-sm ms-2',
            cancelButton: 'btn btn-outline-secondary btn-sm ms-2',
            footer: 'custom-swal-footer'
        },
        buttonsStyling: false,
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return new Promise((resolve) => {
                // Simulate server request
                setTimeout(() => {
                    resolve();
                }, 1000);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: '<span class="text-dark">Signing out...</span>',
                html: '<div class="text-secondary">Please wait while we securely end your session</div>',
                icon: 'info',
                iconColor: '#344767',
                allowOutsideClick: false,
                showConfirmButton: false,
                customClass: {
                    container: 'custom-swal-container',
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-content',
                    icon: 'custom-swal-icon'
                },
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Clear any session data from client side
            localStorage.removeItem('therapist_session');
            sessionStorage.clear();

            // Redirect to logout script after a brief delay
            setTimeout(() => {
                window.location.href = '../../admin_operations/logout.php';
            }, 1500);
        }
    });
}

/**
 * Handle session timeout warning
 */
function showSessionTimeoutWarning() {
    Swal.fire({
        title: '<span class="text-warning">Session Timeout</span>',
        html: '<div class="text-secondary">Your session will expire in 5 minutes. Would you like to stay signed in?</div>',
        icon: 'warning',
        iconColor: '#fd7e14',
        showCancelButton: true,
        confirmButtonText: 'Stay Signed In',
        cancelButtonText: 'Sign Out',
        reverseButtons: true,
        customClass: {
            container: 'custom-swal-container',
            popup: 'custom-swal-popup',
            title: 'custom-swal-title',
            htmlContainer: 'custom-swal-content',
            icon: 'custom-swal-icon',
            actions: 'custom-swal-actions',
            confirmButton: 'btn bg-gradient-warning btn-sm ms-2',
            cancelButton: 'btn btn-outline-secondary btn-sm ms-2'
        },
        buttonsStyling: false,
        timer: 300000, // 5 minutes
        timerProgressBar: true
    }).then((result) => {
        if (result.dismiss === Swal.DismissReason.timer || !result.isConfirmed) {
            handleSignOut();
        } else {
            // Extend session via AJAX call
            extendSession();
        }
    });
}

/**
 * Extend session via AJAX
 */
function extendSession() {
    fetch('../../admin_operations/extend_session.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: '<span class="text-success">Session Extended</span>',
                html: '<div class="text-secondary">Your session has been extended successfully</div>',
                icon: 'success',
                iconColor: '#66BB6A',
                timer: 2000,
                customClass: {
                    container: 'custom-swal-container',
                    popup: 'custom-swal-popup',
                    title: 'custom-swal-title',
                    htmlContainer: 'custom-swal-content',
                    icon: 'custom-swal-icon'
                },
                showConfirmButton: false
            });
        }
    })
    .catch(error => {
        console.error('Session extension failed:', error);
        handleSignOut();
    });
}

// Initialize session timeout warning
document.addEventListener('DOMContentLoaded', function() {
    // Set timeout for 25 minutes (5 minutes before 30-minute session expires)
    setTimeout(showSessionTimeoutWarning, 25 * 60 * 1000);
});