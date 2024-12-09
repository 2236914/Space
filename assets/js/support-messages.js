function openSupportMessagesModal() {
    console.log('Opening modal...');
    const modal = new bootstrap.Modal(document.getElementById('supportMessagesModal'));
    modal.show();
    
    fetch('../../admin_operations/get_support_messages.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            const container = document.getElementById('supportMessagesContainer');
            
            if (!data.messages || data.messages.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No messages found.</div>';
                return;
            }
            
            container.innerHTML = data.messages.map(message => `
                <div class="message-card mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="card-subtitle mb-2 text-muted">
                                    ${message.created_at}
                                </h6>
                                <span class="badge bg-${getStatusBadgeColor(message.status)}">${message.status}</span>
                            </div>
                            
                            <p class="card-text">${message.message_text}</p>
                            
                            ${message.attachment_name ? `
                                <div class="attachment">
                                    <i class="fas fa-paperclip"></i> ${message.attachment_name}
                                </div>
                            ` : ''}
                            
                            ${message.replies.map(reply => `
                                <div class="reply-card mt-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="card-subtitle mb-2">
                                                    <i class="fas fa-reply"></i> ${reply.admin_name}
                                                </h6>
                                                <small class="text-muted">${reply.created_at}</small>
                                            </div>
                                            <p class="card-text">${reply.reply_text}</p>
                                            ${reply.attachment_name ? `
                                                <div class="attachment">
                                                    <i class="fas fa-paperclip"></i> ${reply.attachment_name}
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            document.getElementById('supportMessagesContainer').innerHTML = `
                <div class="alert alert-danger">
                    Error loading messages. Please try again later.
                </div>
            `;
        });
}

function getStatusBadgeColor(status) {
    switch (status) {
        case 'pending': return 'warning';
        case 'in_progress': return 'info';
        case 'resolved': return 'success';
        case 'archived': return 'secondary';
        default: return 'primary';
    }
} 