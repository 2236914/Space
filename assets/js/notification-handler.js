function checkAndShowReminder() {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes();

    // Change this to your desired time (e.g., 14:00 for 2:00 PM)
    if (hours === 14 && minutes === 0) {  // Changed from 17 to 14 for 2 PM
        Swal.fire({
            title: 'Self-Care Reminder',
            text: 'Take a moment for yourself. Would you like to do some breathing exercises or meditation?',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Yes, take me there',
            cancelButtonText: 'Maybe later',
            customClass: {
                confirmButton: 'btn bg-gradient-success',
                cancelButton: 'btn bg-gradient-secondary'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'selfcare-med.php';
            }
        });
    }
}

// Check every minute
setInterval(checkAndShowReminder, 60000);

// Also check immediately when the page loads
checkAndShowReminder(); 