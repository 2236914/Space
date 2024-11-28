// Handle image selection and preview
let selectedImage = null;

function previewImage(input) {
    if (input.files && input.files[0]) {
        selectedImage = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview').src = e.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    selectedImage = null;
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').style.display = 'none';
    document.getElementById('preview').src = '#';
}

function createPost() {
    const content = document.querySelector('textarea[name="content"]').value.trim();
    
    if (!content) {
        Swal.fire({
            title: 'Empty Post',
            text: 'Please write something before posting.',
            icon: 'warning',
            customClass: {
                confirmButton: 'btn bg-gradient-primary'
            },
            buttonsStyling: false
        });
        return;
    }

    const formData = new FormData();
    formData.append('content', content);
    formData.append('action', 'create_post');
    
    if (selectedImage) {
        formData.append('image', selectedImage);
    }

    // Disable post button and show loading state
    const postButton = document.querySelector('button.bg-gradient-dark');
    const originalText = postButton.innerHTML;
    postButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Posting...';
    postButton.disabled = true;

    fetch('../../admin_operations/post_handlers.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Clear form and preview
            document.querySelector('textarea[name="content"]').value = '';
            removeImage();

            // Show success message
            Swal.fire({
                title: 'Posted!',
                text: 'Your post has been shared successfully.',
                icon: 'success',
                customClass: {
                    confirmButton: 'btn bg-gradient-success'
                },
                buttonsStyling: false
            }).then(() => {
                location.reload(); // Reload to show new post
            });
        } else {
            throw new Error(data.message || 'Failed to create post');
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
    })
    .finally(() => {
        // Restore button state
        postButton.innerHTML = originalText;
        postButton.disabled = false;
    });
}