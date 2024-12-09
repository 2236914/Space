document.addEventListener('DOMContentLoaded', function() {
    const journalForm = document.getElementById('journalForm');
    const entriesList = document.getElementById('entriesList');

    // Set default date to today
    const dateInput = document.querySelector('input[name="date"]');
    if (dateInput) {
        dateInput.valueAsDate = new Date();
    }

    // Handle form submission
    journalForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);

        // Show loading state
        Swal.fire({
            title: 'Saving...',
            text: 'Please wait while we save your journal entry',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('../../admin_operations/save_journal.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    // Reset form
                    journalForm.reset();
                    dateInput.valueAsDate = new Date();
                    // Optionally load entries if on entries tab
                    if (document.querySelector('a[href="#past-entries"]').classList.contains('active')) {
                        loadEntries();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Something went wrong',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to save journal entry',
                confirmButtonText: 'OK'
            });
        });
    });
});

function saveJournalEntry(event) {
    event.preventDefault();
    
    // Get form values
    const title = document.querySelector('input[name="title"]').value;
    const content = document.querySelector('textarea[name="content"]').value;
    const mood = document.querySelector('select[name="mood"]').value;
    const date = document.querySelector('input[name="date"]').value;

    // Validate inputs
    if (!title || !content || !mood || !date) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill in all fields',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Show loading state
    Swal.fire({
        title: 'Saving...',
        text: 'Please wait while we save your journal entry',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Create form data
    const formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('mood', mood);
    formData.append('date', date);

    // Send to server
    fetch('../../admin_operations/save_journal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your journal entry has been saved',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reset form
                document.getElementById('journalForm').reset();
                document.querySelector('input[name="date"]').valueAsDate = new Date();
            });
        } else {
            throw new Error(data.message || 'Failed to save entry');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: error.message || 'Something went wrong',
            confirmButtonText: 'OK'
        });
    });
}

function loadJournalEntries() {
    const entriesList = document.getElementById('entriesList');
    
    // Show loading state
    entriesList.innerHTML = `
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    fetch('../../admin_operations/get_journal_entries.php')
    .then(response => response.json())
    .then(result => {
        entriesList.innerHTML = '';
        
        if (result.status === 'success' && result.data.length === 0) {
            entriesList.innerHTML = `
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <p class="mb-0">No journal entries yet. Start writing your thoughts!</p>
                        </div>
                    </div>
                </div>
            `;
            return;
        }

        result.data.forEach(entry => {
            entriesList.innerHTML += `
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0">${entry.title}</h5>
                                    <p class="text-sm mb-0">
                                        <span class="font-weight-bold">${entry.mood}</span>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p class="text-sm mb-0">${entry.formatted_date}</p>
                                    <p class="text-xs text-muted mb-0">${entry.formatted_time}</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <p class="mb-0">${entry.content}</p>
                        </div>
                    </div>
                </div>
            `;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        entriesList.innerHTML = `
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center text-danger">
                        <p class="mb-0">Failed to load entries. Please try again later.</p>
                    </div>
                </div>
            </div>
        `;
    });
}

// Add event listener for tab switch
document.querySelector('a[href="#past-entries"]').addEventListener('click', loadJournalEntries); 