const handleSignOut = () => {
    Swal.fire({
        title: 'Sign Out?',
        text: "You will be logged out of the system",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sign Out',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary mx-2',
            cancelButton: 'btn btn-outline-primary',
            popup: 'sweet-popup',
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Signed Out!',
                text: 'You have been successfully logged out',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'sweet-popup'
                }
            }).then(() => {
                window.location.href = '../../pages/signin.php';
            });
        }
    });
};

// Add click event listener to sign-out button
document.addEventListener('DOMContentLoaded', () => {
    const signOutButton = document.getElementById('admin-signout');
    if (signOutButton) {
        signOutButton.addEventListener('click', signOutAdmin);
    }
});