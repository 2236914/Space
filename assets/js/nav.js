function searchMenu() {
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let resultsBox = document.getElementById("searchResults");

    let menuItems = [
        { text: 'Analytics', link: 'student.php' },
        { text: 'Mood Tracker', link: 'moodtracker.php' },
        { text: 'Notifications', link: 'notifications.php' },
        { text: 'Calendar', link: 'calendar.php' },
        { text: 'Profile', link: 'profile.php' },
        { text: 'Account Settings', link: 'account-settings.php' },
        { text: 'Articles', link: 'articles.php' },
        { text: 'Journal', link: 'journal.php' },
        { text: 'Support', link: 'support.php' }
    ];

    resultsBox.innerHTML = '';

    if (filter) {
        let matches = menuItems.filter(item => 
            item.text.toLowerCase().includes(filter)
        );

        if (matches.length > 0) {
            matches.forEach(item => {
                let div = document.createElement('div');
                div.className = 'search-result-item p-2';
                div.style.cursor = 'pointer';
                div.onclick = () => window.location.href = item.link;
                div.innerHTML = item.text;
                resultsBox.appendChild(div);
            });
            resultsBox.classList.remove('d-none');
        } else {
            let div = document.createElement('div');
            div.className = 'p-2 text-muted';
            div.innerHTML = 'No results found';
            resultsBox.appendChild(div);
            resultsBox.classList.remove('d-none');
        }
    } else {
        resultsBox.classList.add('d-none');
    }
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    let resultsBox = document.getElementById('searchResults');
    let searchInput = document.getElementById('searchInput');
    
    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
        resultsBox.classList.add('d-none');
    }
}); 