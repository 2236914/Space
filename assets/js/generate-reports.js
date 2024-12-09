// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    
    // Initialize features based on current tab
    const currentTab = document.querySelector('.tab-pane.active');
    console.log('Current tab:', currentTab?.id);

    // Initialize tab functionality
    initializeTabs();

    // Add tab change listener
    const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabLinks.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('href').substring(1);
            if (targetId === 'therapy-sessions') {
                initializeTherapyFeatures();
            }
        });
    });

    // If therapy tab is active or exists, initialize its features
    if (document.querySelector('#therapy-sessions')) {
        console.log('Initializing therapy features');
        initializeTherapyFeatures();
    }

    // Therapy Sessions Search Function
    const searchInput = document.getElementById('therapySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', searchTherapySessions);
    }
});

// Community Features
function initializeCommunityFeatures() {
    const communitySearchInput = document.getElementById('communitySearchInput');
    if (communitySearchInput) {
        communitySearchInput.addEventListener('input', function() {
            filterTable(this.value, 'community-activity-row');
        });
    }
}

// Activity Features
function initializeActivityFeatures() {
    const activitySearchInput = document.getElementById('activitySearchInput');
    if (activitySearchInput) {
        activitySearchInput.addEventListener('input', function() {
            filterTable(this.value, 'activity-log-row');
        });
    }
}

// Therapy Sessions Features
function initializeTherapyFeatures() {
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

    // Main Export CSV button (top of table)
    const mainExportBtn = document.querySelector('.export-csv');
    if (mainExportBtn) {
        mainExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportAllSessions();
        });
    }

    // Bulk Export button (bottom of table)
    const bulkExportBtn = document.querySelector('#bulkActions .btn');
    if (bulkExportBtn) {
        bulkExportBtn.addEventListener('click', function(e) {
            e.preventDefault();
            exportSelectedSessions('therapy');
        });
    }
}  

// Export all sessions
function exportAllSessions() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'export_type';
    typeInput.value = 'therapy_all';

    form.appendChild(typeInput);
    document.body.appendChild(form);
    form.submit();
}
// Export selected sessions
function exportSelectedSessions(type) {
    const selectedCheckboxes = document.querySelectorAll('.therapy-session-checkbox:checked');
    
    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one session to export');
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    // Add export type
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'export_type';
    typeInput.value = type;
    form.appendChild(typeInput);

    // Add selected sessions
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    const sessionsInput = document.createElement('input');
    sessionsInput.type = 'hidden';
    sessionsInput.name = 'selected_sessions';
    sessionsInput.value = JSON.stringify(selectedIds);
    form.appendChild(sessionsInput);

    document.body.appendChild(form);
    form.submit();
}

// Mood Chart Initialization
function initializeMoodChart(ctx) {
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: moodDates, // This should be defined in your PHP
            datasets: [{
                label: 'Mood Trends',
                data: moodValues, // This should be defined in your PHP
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5
                }
            }
        }
    });
}

// Tab Management
function initializeTabs() {
    const urlParams = new URLSearchParams(window.location.search);
    const currentTab = urlParams.get('tab') || localStorage.getItem('currentTab') || 'moodreports';
    
    const tabEl = document.querySelector(`a[href="#${currentTab}"]`);
    if (tabEl) {
        const tab = new bootstrap.Tab(tabEl);
        tab.show();
    }

    document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', function(event) {
            const targetId = event.target.getAttribute('href').substring(1);
            localStorage.setItem('currentTab', targetId);
        });
    });
}

// Mood Reports Features
function initializeMoodFeatures() {
    const moodSearchInput = document.getElementById('moodSearchInput');
    if (moodSearchInput) {
        moodSearchInput.addEventListener('input', function() {
            filterTable(this.value, 'mood-log-row');
        });
    }

    // Handle mood export
    const moodExportBtn = document.querySelector('.export-mood-csv');
    if (moodExportBtn) {
        moodExportBtn.addEventListener('click', function() {
            exportData('mood');
        });
    }
}

// Utility Functions
function updateSelectedCount() {
    const selectedCount = document.querySelectorAll('.therapy-session-checkbox:checked').length;
    const countDisplay = document.getElementById('selectedSessionsCount');
    if (countDisplay) {
        countDisplay.textContent = selectedCount;
    }

    const bulkActions = document.getElementById('bulkActions');
    if (bulkActions) {
        bulkActions.style.display = selectedCount > 0 ? 'block' : 'none';
    }
}

function filterTable(searchTerm, rowClass) {
    const term = searchTerm.toLowerCase();
    document.querySelectorAll(`.${rowClass}`).forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

// Initialize all DataTables
function initializeDataTables() {
    // Mood Reports Table
    const moodTable = $('#moodLogsTable').DataTable({
        order: [[0, 'desc']], // Date column descending
        pageLength: 10,
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        responsive: true
    });

    // Therapy Sessions Table
    const therapyTable = $('#therapySessionsTable').DataTable({
        order: [[0, 'desc']], // Date column descending
        pageLength: 10,
        responsive: true
    });

    // Community Activities Table
    const communityTable = $('#communityActivitiesTable').DataTable({
        order: [[0, 'desc']], // Date column descending
        pageLength: 10,
        responsive: true
    });
}

// Initialize Tooltips
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize Popovers
function initializePopovers() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

// Enhanced Table Filtering
function filterTable(searchTerm, tableId) {
    const term = searchTerm.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) { // Start from 1 to skip header
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            if (cell.textContent.toLowerCase().indexOf(term) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
    }
}

// Enhanced Export Functions
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    const csv = [];

    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, '').replace(/(\s\s)/gm, ' ');
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }

    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

// Table Sorting Functions
function sortTable(tableId, n) {
    const table = document.getElementById(tableId);
    let switching = true;
    let dir = 'asc';
    let switchcount = 0;

    while (switching) {
        switching = false;
        const rows = table.rows;

        for (let i = 1; i < (rows.length - 1); i++) {
            let shouldSwitch = false;
            const x = rows[i].getElementsByTagName('TD')[n];
            const y = rows[i + 1].getElementsByTagName('TD')[n];

            if (dir === 'asc') {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === 'desc') {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }

            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
                switchcount++;
            }
        }

        if (switchcount === 0 && dir === 'asc') {
            dir = 'desc';
            switching = true;
        }
    }
}

// Pagination Functions
function handlePagination(tableId, pageSize = 10) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');
    const pageCount = Math.ceil(rows.length / pageSize);
    
    return {
        showPage: function(page) {
            const start = (page - 1) * pageSize;
            const end = start + pageSize;
            
            for (let i = 1; i < rows.length; i++) {
                if (i < start || i >= end) {
                    rows[i].style.display = 'none';
                } else {
                    rows[i].style.display = '';
                }
            }
        },
        getPageCount: function() {
            return pageCount;
        }
    };
}

// Therapy Sessions Search Function
function searchTherapySessions() {
    const searchInput = document.getElementById('therapySearchInput');
    const searchTerm = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('#therapy-sessions tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}
  