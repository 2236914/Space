<?php

$current_page = basename($_SERVER['PHP_SELF']);

$menu_items = [
    'dashboardsExamples' => [
        'icon' => 'space_dashboard',
        'text' => 'Dashboard',
        'submenu' => [
            'student.php' => ['text' => 'Analytics', 'mini' => 'A'],
            'moodtracker.php' => ['text' => 'Mood Tracker', 'mini' => 'MT'],
            'notifications.php' => ['text' => 'Notifications', 'mini' => 'N']
        ]
    ],
    'account' => [
        'icon' => 'account_circle',
        'text' => 'Account',
        'submenu' => [
            'account-settings.php' => ['text' => 'Settings', 'mini' => 'S'],
            'articles.php' => ['text' => 'Articles', 'mini' => 'A'],
            'journal.php' => ['text' => 'Journal', 'mini' => 'J'],
        ]
    ],
    'generate-reports.php' => [
        'icon' => 'report',
        'text' => 'Generate Reports'
    ],
    'support.php' => [
        'icon' => 'support_agent',
        'text' => 'Space Support',
        'class' => 'mt-3'
    ]
];

// Get current page info from menu_items
$current_info = ['title' => 'Page Not Found', 'parent' => 'Pages'];
foreach ($menu_items as $section => $item) {
    if (isset($item['submenu']) && array_key_exists($current_page, $item['submenu'])) {
        $current_info = [
            'title' => $item['submenu'][$current_page]['text'],
            'parent' => $item['text']
        ];
        break;
    } elseif ($current_page === $section) {
        $current_info = [
            'title' => $item['text'],
            'parent' => 'Pages'
        ];
        break;
    }
}
?>

<!-- Top Navigation Bar with Breadcrumbs -->


<!-- Search functionality -->
<script>
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
</script>

<!-- Search Results Styling -->
<style>
.search-result-item {
    transition: background-color 0.3s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
}

#searchResults {
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    border: 1px solid #dee2e6;
}
</style>