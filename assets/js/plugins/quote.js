// Function to handle quote likes
function likeQuote(quoteId) {
    const btn = $(`button[onclick="likeQuote(${quoteId})"]`);
    if (btn.prop('disabled')) return; // Prevent double clicks
    
    btn.prop('disabled', true);
    
    $.ajax({
        url: BASE_URL + '/admin_operations/like_quote.php',
        type: 'POST',
        data: { quote_id: quoteId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.isLiked) {
                    btn.removeClass('btn-outline-primary').addClass('bg-gradient-primary text-white');
                } else {
                    btn.removeClass('bg-gradient-primary text-white').addClass('btn-outline-primary');
                }
                showNotification('success', response.message);
            } else {
                showNotification('error', response.message);
            }
        },
        error: function() {
            showNotification('error', 'Failed to update like status');
        },
        complete: function() {
            setTimeout(() => btn.prop('disabled', false), 500); // Prevent rapid clicking
        }
    });
}

// Function to handle quote refresh
function refreshQuote() {
    const btn = $('button[onclick="refreshQuote()"]');
    if (btn.prop('disabled')) return; // Prevent double clicks
    
    btn.prop('disabled', true);
    
    $.ajax({
        url: BASE_URL + '/admin_operations/refresh_quote.php',
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload(); // Reload to show new quote
                showNotification('success', 'Quote refreshed successfully');
            } else {
                showNotification('error', response.message || 'Failed to refresh quote');
            }
        },
        error: function() {
            showNotification('error', 'Failed to refresh quote');
        },
        complete: function() {
            setTimeout(() => btn.prop('disabled', false), 500); // Prevent rapid clicking
        }
    });
}

// Add this function for notifications if not already defined
function showNotification(type, message) {
    Swal.fire({
        icon: type === 'success' ? 'success' : 'error',
        title: type === 'success' ? 'Success' : 'Error',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}