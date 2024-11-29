function showSupportDialog() {
    Swal.fire({
        title: 'New message to Space Support',
        html: `
            <form id="supportForm" class="text-start">
                <div class="input-group input-group-outline my-3">
                    <input type="email" class="form-control" id="supportEmail" placeholder="Email to contact me" required>
                </div>
                <div class="input-group input-group-outline mb-3">
                    <textarea class="form-control" id="supportMessage" rows="5" placeholder="Type your message here..." required></textarea>
                </div>
                <div class="input-group input-group-outline">
                    <input type="file" class="form-control" id="supportAttachment" accept=".jpg,.png,.pdf,.doc,.docx">
                    <label class="input-group-text text-xs text-secondary">Optional</label>
                </div>
            </form>`,
        showCancelButton: true,
        confirmButtonText: 'Send',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary mx-1',
            cancelButton: 'btn btn-outline-secondary mx-1',
            htmlContainer: 'text-start'
        },
        buttonsStyling: false,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const email = document.getElementById('supportEmail').value;
            const message = document.getElementById('supportMessage').value;
            const attachment = document.getElementById('supportAttachment').files[0];

            if (!email || !message) {
                Swal.showValidationMessage('Please fill in all required fields');
                return false;
            }

            // Create FormData to handle file upload
            const formData = new FormData();
            formData.append('email', email);
            formData.append('message', message);
            if (attachment) {
                formData.append('attachment', attachment);
            }

            // Return the fetch promise
            return fetch('../../admin_operations/submit_support.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Server response:', text);
                        throw new Error('Failed to send message');
                    });
                }
                return response.json();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Success!',
                text: 'Your message has been sent. We\'ll get back to you soon.',
                icon: 'success',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary'
                },
                buttonsStyling: false
            });
        }
    });
} 