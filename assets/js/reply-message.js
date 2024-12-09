function submitReply(messageId, senderType) {
    console.log('submitReply called with:', { messageId, senderType });

    // Get the modal that's currently open
    const modal = document.querySelector(`#messageModal${messageId}`);
    if (!modal) {
        console.error(`Modal not found: #messageModal${messageId}`);
        return;
    }

    // Get the form elements directly from the modal
    const textarea = modal.querySelector('textarea[name="reply_text"]');
    const attachmentInput = modal.querySelector('input[name="attachment"]');
    
    if (!textarea) {
        console.error('Textarea not found');
        return;
    }

    const replyText = textarea.value.trim();
    const attachment = attachmentInput ? attachmentInput.files[0] : null;
    
    console.log('Form data:', { replyText, hasAttachment: !!attachment });

    if (!replyText) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Please enter a reply message',
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
        return;
    }

    // Show loading state on the clicked button
    const submitBtn = modal.querySelector('.send-reply-btn');
    if (!submitBtn) {
        console.error('Submit button not found in modal');
        return;
    }

    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    submitBtn.disabled = true;

    const formData = new FormData();
    formData.append('message_id', messageId);
    formData.append('sender_type', senderType);
    formData.append('reply_text', replyText);
    if (attachment) {
        formData.append('attachment', attachment);
    }

    fetch('../../admin_operations/send_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Reply sent successfully',
                customClass: {
                    confirmButton: 'btn bg-gradient-success'
                },
                buttonsStyling: false
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Failed to send reply');
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
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Add event listener when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Debug log to verify script is loading
    console.log('Reply script loaded');

    // Add click listeners to all reply buttons
    document.querySelectorAll('.send-reply-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('Button clicked:', this.dataset);
            const messageId = this.dataset.messageId;
            const senderType = this.dataset.senderType;
            submitReply(messageId, senderType);
        });
    });
});