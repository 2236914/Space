function updateStatus(messageId) {
    Swal.fire({
        title: 'Update Status',
        input: 'select',
        inputOptions: {
            'pending': 'Pending',
            'in_progress': 'In Progress',
            'resolved': 'Resolved',
            'archived': 'Archived'
        },
        inputPlaceholder: 'Select a status',
        showCancelButton: true,
        confirmButtonText: 'Update',
        cancelButtonText: 'Cancel',
        customClass: {
            select: 'form-control',
            confirmButton: 'btn bg-gradient-success',
            cancelButton: 'btn bg-gradient-dark',
            actions: 'gap-2'
        },
        buttonsStyling: false,
        reverseButtons: true,
        inputValidator: (value) => {
            if (!value) {
                return 'Please select a status';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updateMessageStatus(messageId, result.value);
        }
    });
}
function updateMessageStatus(messageId, status, senderType) {
    console.log('Updating status:', {
        messageId: messageId,
        status: status,
        senderType: senderType
    });

    if (!messageId || !status || !senderType) {
        console.error('Missing required parameters:', { messageId, status, senderType });
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Missing required information for update',
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
        return;
    }

    const formData = new FormData();
    formData.append('message_id', messageId);
    formData.append('status', status);
    formData.append('sender_type', senderType);

    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('../../admin_operations/update_message_status.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        console.log('Server response:', data);
        
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Status Updated!',
                text: 'Message status has been updated successfully.',
                customClass: {
                    confirmButton: 'btn bg-gradient-success'
                },
                buttonsStyling: false
            }).then(() => {
                window.location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message,
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
    });
}