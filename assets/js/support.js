function showSupportDialog() {
    Swal.fire({
        title: 'New message to Space Support',
        html: `
            <form id="supportForm" class="text-start">
                <div class="input-group input-group-outline my-3">
                    <input type="email" 
                           class="form-control" 
                           id="supportEmail" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                           placeholder="Enter your email address" 
                           title="Please enter a valid email address"
                           required>
                </div>
                <div class="input-group input-group-outline mb-3">
                    <textarea class="form-control" 
                             id="supportMessage" 
                             rows="5" 
                             placeholder="Type your message here..." 
                             required 
                             minlength="10"></textarea>
                </div>
                <div class="input-group input-group-outline">
                    <input type="file" 
                           class="form-control" 
                           id="supportAttachment" 
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                    <label class="input-group-text text-xs text-secondary">Optional (Max 5MB)</label>
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
            const emailInput = document.getElementById('supportEmail');
            const messageInput = document.getElementById('supportMessage');
            const attachment = document.getElementById('supportAttachment').files[0];

            // Email validation
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailRegex.test(emailInput.value)) {
                Swal.showValidationMessage('Please enter a valid email address');
                return false;
            }

            // Message validation
            if (!messageInput.value) {
                Swal.showValidationMessage('Please enter your message');
                return false;
            }
            if (messageInput.value.length < 10) {
                Swal.showValidationMessage('Message must be at least 10 characters long');
                return false;
            }

            // File validation
            if (attachment) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                
                if (attachment.size > maxSize) {
                    Swal.showValidationMessage('File size must not exceed 5MB');
                    return false;
                }
                
                if (!allowedTypes.includes(attachment.type)) {
                    Swal.showValidationMessage('Only JPG, PNG, PDF, and DOC files are allowed');
                    return false;
                }
            }

            // Create FormData and send
            const formData = new FormData();
            formData.append('email', emailInput.value);
            formData.append('message', messageInput.value);
            if (attachment) {
                formData.append('attachment', attachment);
            }

            return fetch('../../admin_operations/submit_support.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            // Try to parse as JSON
                            return JSON.parse(text);
                        } catch (e) {
                            // If it's not JSON, it's probably an HTML error page
                            console.error('Server returned HTML instead of JSON:', text);
                            throw new Error('Server error occurred. Please try again later.');
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'error') {
                    throw new Error(data.message || 'An error occurred');
                }
                return data;
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Success!',
                text: result.value.message || 'Your message has been sent. We\'ll get back to you soon.',
                icon: 'success',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary'
                },
                buttonsStyling: false
            });
        }
    });
} 