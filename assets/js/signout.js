// Add console log to verify the function is defined
console.log('Signout.js loaded');

function handleSignOut() {
    console.log('handleSignOut function called');
    
    Swal.fire({
        title: 'Are you sure you want to sign out?',
        text: "You will be logged out of your account",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, sign me out!',
        cancelButtonText: 'No, cancel',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
            cancelButton: 'btn btn-outline-primary btn-sm mx-2',
            actions: 'justify-content-center'
        },
        buttonsStyling: false,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Sign out confirmed');
            
            // Show loading state
            Swal.fire({
                title: 'Signing out...',
                text: 'Please wait',
                icon: 'info',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Redirect to logout script after a brief delay
            setTimeout(() => {
                console.log('Redirecting to logout');
                window.location.href = '../../admin_operations/logout.php';
            }, 1000);
        }
    });
}

// Check if user is logged in
function checkLoginStatus() {
    fetch('../../admin_operations/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                window.location.href = '../signin.php';
            }
        });
}

// Check login status when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
    
    // Initialize ActivityTracker if it exists
    if (typeof ActivityTracker !== 'undefined') {
        ActivityTracker.init();
    }
});

// Periodically check login status
setInterval(checkLoginStatus, 30000); // Check every 30 seconds