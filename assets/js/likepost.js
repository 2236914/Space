// Like Handler
function handleLike(button, postId) {
    const userRole = document.body.getAttribute('data-user-role'); // Add this line
    console.log('Like button clicked:', button);
    console.log('Post ID:', postId);
    console.log('User Role:', userRole);
    
    fetch('../../admin_operations/post_handlers.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=toggle_like&post_id=${postId}&liker_type=${userRole}`
    })
    .then(response => {
        console.log('Raw response:', response);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Processed data:', data);
        if (data.success) {
            // Update like count
            const countSpan = button.querySelector('span');
            countSpan.textContent = data.like_count;
            
            // Toggle like button state
            const icon = button.querySelector('i');
            if (data.is_liked) {
                button.classList.add('liked');
                icon.style.fontVariationSettings = "'FILL' 1";
                
                // Show like animation
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: 'Post Liked!',
                    showConfirmButton: false,
                    timer: 1500,
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    }
                });
            } else {
                button.classList.remove('liked');
                icon.style.fontVariationSettings = "";
            }
        } else {
            throw new Error(data.message || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Detailed error:', error);
        Swal.fire({
            title: 'Error',
            text: 'Failed to update like status',
            icon: 'error',
            customClass: {
                confirmButton: 'btn bg-gradient-danger'
            },
            buttonsStyling: false
        });
    });
}

// Add these styles to your existing styles
const likeStyles = `
    .btn-link.liked {
        color: #e91e63 !important;
    }
    .btn-link.liked i {
        font-variation-settings: 'FILL' 1;
    }
    .animated-like {
        animation: likeAnimation 0.5s ease-in-out;
    }
    @keyframes likeAnimation {
        0% { transform: scale(0.3); opacity: 0; }
        50% { transform: scale(1.2); }
        100% { transform: scale(1); opacity: 1; }
    }
`;

// Add the new styles to the document
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent += likeStyles;
    document.head.appendChild(style);
}); 