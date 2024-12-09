document.addEventListener('DOMContentLoaded', function() {
    // Select all checkbox functionality
    const selectAllCheckbox = document.getElementById('selectAllTherapy');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.therapy-session-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // Individual checkbox handling
    document.querySelectorAll('.therapy-session-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Search functionality
    const searchInput = document.getElementById('therapySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.therapy-session-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }
});

// Update selected count
function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.therapy-session-checkbox:checked').length;
    const countDisplay = document.getElementById('selectedSessionsCount');
    if (countDisplay) {
        countDisplay.textContent = selectedCount;
    }

    // Show/hide bulk actions
    const bulkActions = document.getElementById('bulkActions');
    if (bulkActions) {
        bulkActions.style.display = selectedCount > 0 ? 'block' : 'none';
    }
}

// Export selected sessions
function exportSelectedSessions() {
    const selectedIds = Array.from(document.querySelectorAll('.therapy-session-checkbox:checked'))
        .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one session to export');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'export_sessions';
    input.value = JSON.stringify(selectedIds);

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

// View session details
function viewSessionDetails(sessionId) {
    // You can implement modal or redirect logic here
    console.log('Viewing session:', sessionId);
} 