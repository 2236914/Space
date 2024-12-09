// Comments Modal Handler
function showComments(button, postId) {
    fetch('../../admin_operations/get_comments.php?post_id=' + postId)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) {
                if (data.error) throw new Error(data.error);
                data = [];
            }

            let commentsHtml = '';
            data.forEach(comment => {
                commentsHtml += `
                    <div class="d-flex align-items-start" style="padding: 12px 16px; margin: 8px 0;">
                        <img src="../../admin_operations/get_profile_picture.php?user_id=${comment.user_id}&user_type=${comment.commenter_type}" 
                             class="avatar rounded-circle me-3"
                             style="width: 32px; height: 32px;"
                             onerror="this.src='../../assets/img/default-avatar.png'">
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <span style="font-size: 13px; font-weight: 600;">${comment.username}</span>
                                <small style="font-size: 12px; color: #8e8e8e;">${comment.time_ago}</small>
                            </div>
                            <div style="font-size: 13px; color: #262626; margin-top: 4px;text-align: left;">${comment.comment_text}</div>
                        </div>
                    </div>`;
            });

            if (data.length === 0) {
                commentsHtml = '<p class="text-center text-muted my-3">No comments yet</p>';
            }

            
            Swal.fire({
                title: 'Comments',
                html: `
                    <div class="comments-container" data-post-id="${postId}">
                        <div class="comments-section" style="max-height: 400px; overflow-y: auto;">
                            ${commentsHtml}
                        </div>
                        <div style="position: relative; padding: 16px; border-top: 1px solid #efefef;">
                            <textarea id="commentText" 
                                class="form-control border-0" 
                                placeholder="Add a comment..." 
                                rows="1" 
                                style="resize: none; background: transparent; box-shadow: none; padding: 0; font-size: 14px;"></textarea>
                            <div class="position-absolute" style="right: 16px; top: 50%; transform: translateY(-50%);">
                                <span class="text-primary" style="font-size: 14px; cursor: pointer;" onclick="handleCommentSubmit()">▶</span>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                customClass: {
                    popup: 'rounded-4',
                    closeButton: 'position-absolute end-0 top-0 pt-3 pe-3',
                    htmlContainer: 'p-0',
                },
                showClass: {
                    popup: ''
                },
                hideClass: {
                    popup: ''
                },
                width: '400px',
                padding: 0,
                closeButtonHtml: `
                    <i class="material-symbols-rounded" style="font-size: 18px; color: #262626;">close</i>
                `,
                didOpen: (popup) => {
                    // Style the title
                    const title = popup.querySelector('.swal2-title');
                    title.style.cssText = `
                        text-align: left;
                        padding: 14px 16px;
                        margin: 0;
                        font-size: 16px;
                        font-weight: 600;
                        border-bottom: 1px solid #efefef;
                        color: #262626;
                    `;
                    
                    // Add event listener for Enter key
                    document.getElementById('commentText').addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            handleCommentSubmit();
                        }
                    });
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error',
                text: error.message || 'Failed to load comments',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm'
                },
                buttonsStyling: false
            });
        });
}

// Add this new function to handle comment submission
function handleCommentSubmit() {
    const commentText = document.getElementById('commentText').value;
    if (!commentText.trim()) {
        return;
    }
    
    // Get post_id from the comments container
    const container = document.querySelector('.comments-container');
    const postId = container.getAttribute('data-post-id');
    
    // Get the original button that opened the comments
    const button = document.querySelector(`button[onclick*="showComments"][data-post-id="${postId}"]`);
    
    if (!postId || !button) {
        console.error('Could not find post_id or button');
        return;
    }
    
    addComment(postId, commentText, button);
}

// Update addComment function
function addComment(postId, content, button) {
    const userRole = document.body.getAttribute('data-user-role');
    
    fetch('../../admin_operations/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${encodeURIComponent(postId)}&content=${encodeURIComponent(content)}&commenter_type=${userRole}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update comment count
            const commentCountSpan = button.querySelector('span');
            const currentCount = parseInt(commentCountSpan.textContent);
            commentCountSpan.textContent = currentCount + 1;
            
            // Clear the textarea
            document.getElementById('commentText').value = '';
            
            // Instead of refreshing entire modal, just fetch new comments
            fetch('../../admin_operations/get_comments.php?post_id=' + postId)
                .then(response => response.json())
                .then(comments => {
                    if (!Array.isArray(comments)) {
                        if (comments.error) throw new Error(comments.error);
                        comments = [];
                    }

                    // Update just the comments section
                    const commentsSection = document.querySelector('.comments-section');
                    if (commentsSection) {
                        let commentsHtml = '';
                        comments.forEach(comment => {
                            commentsHtml += `
                                <div class="d-flex align-items-start" style="padding: 12px 16px; margin: 8px 0;">
                                    <img src="../../admin_operations/get_profile_picture.php?user_id=${comment.user_id}&user_type=${comment.commenter_type}" 
                                         class="avatar rounded-circle me-3"
                                         style="width: 32px; height: 32px;"
                                         onerror="this.src='../../assets/img/default-avatar.png'">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <span style="font-size: 13px; font-weight: 600;">${comment.username}</span>
                                            <small style="font-size: 12px; color: #8e8e8e;">${comment.time_ago}</small>
                                        </div>
                                        <div style="font-size: 13px; color: #262626; margin-top: 4px;text-align: left;">${comment.comment_text}</div>
                                    </div>
                                </div>`;
                        });
                        commentsSection.innerHTML = commentsHtml;
                    }
                });
        } else {
            throw new Error(data.message || 'Failed to add comment');
        }
    })
    .catch(error => {
        console.error('Error adding comment:', error);
        Swal.fire({
            title: 'Error',
            text: error.message,
            icon: 'error',
            showClass: { popup: '' },
            hideClass: { popup: '' },
            customClass: {
                confirmButton: 'btn bg-gradient-danger btn-sm'
            },
            buttonsStyling: false
        });
    });
}
