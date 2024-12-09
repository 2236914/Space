function archiveMessage(messageId, senderType) {
    Swal.fire({
        title: 'Archive Message?',
        text: "This message will be moved to the archive section",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, archive it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Send AJAX request to update status
            $.ajax({
                url: '../../admin_operations/update_message_status.php',
                type: 'POST',
                data: {
                    message_id: messageId,
                    status: 'archived',
                    sender_type: senderType
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Archived!',
                            'Message has been archived.',
                            'success'
                        ).then(() => {
                            // Refresh the page to update all tabs
                            location.reload();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            'Failed to archive message.',
                            'error'
                        );
                    }
                },
                error: function() {
                    Swal.fire(
                        'Error!',
                        'Failed to archive message.',
                        'error'
                    );
                }
            });
        }
    });
}