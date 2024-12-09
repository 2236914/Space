<?php
// Current page and menu items setup
$current_page = basename($_SERVER['PHP_SELF']);

// Menu items array (for sidebar)
$menu_items = [
    'dashboardsExamples' => [
        'icon' => 'space_dashboard',
        'text' => 'Dashboard',
        'submenu' => [
            'student.php' => ['text' => 'Analytics', 'mini' => 'A'],
            'notifications.php' => ['text' => 'Activity Logs', 'mini' => 'AL']
        ]
    ],
    'account' => [
        'icon' => 'account_circle',
        'text' => 'Account',
        'submenu' => [
            'account-settings.php' => ['text' => 'Settings', 'mini' => 'S'],
        ]
    ],
    'moodtracker.php' => [
        'icon' => 'mood',
        'text' => 'Mood Tracker'
    ],
    'selfCareTools' => [
        'icon' => 'self_improvement',
        'text' => 'Self-Care Tools',
        'submenu' => [
            'selfcare-med.php' => [
                'mini' => 'M',
                'text' => 'Meditation'
            ],
            'selfcare-journal.php' => [
                'mini' => 'J',
                'text' => 'Journal'
            ]
        ]
    ],
    'therapyNav' => [
        'icon' => 'calendar_month',
        'text' => 'Therapy Sessions',
        'submenu' => [
            'schedule.php' => [
                'mini' => 'S',
                'text' => 'Schedule Session'
            ],
            'upcoming_sessions.php' => [
                'mini' => 'U',
                'text' => 'Upcoming Sessions'
            ],
            'completed_sessions.php' => [
                'mini' => 'C',
                'text' => 'Completed Sessions'
            ],
        ]
    ],
    'community.php' => [
        'icon' => 'groups',
        'text' => 'Community'
    ],
    'generate-reports.php' => [
        'icon' => 'report',
        'text' => 'Generate Reports'
    ]
];

// Navbar items array (for top navigation)
$navbar_items = [
    [
        'href' => 'javascript:;',
        'icon' => 'notifications',
        'badge' => '1',
        'dropdown' => [
            [
                'icon' => 'email',
                'text' => 'Check new messages'
            ]
        ]
    ]
];

// Get current page info
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

// Search menu items array
$search_menu_items = [
    ['text' => 'Analytics', 'link' => 'student.php'],
    ['text' => 'Mood Tracker', 'link' => 'moodtracker.php'],
    ['text' => 'Notifications', 'link' => 'notifications.php'],
    ['text' => 'Calendar', 'link' => 'calendar.php'],
    ['text' => 'Profile', 'link' => 'profile.php'],
    ['text' => 'Account Settings', 'link' => 'account-settings.php'],
    ['text' => 'Articles', 'link' => 'articles.php'],
    ['text' => 'Journal', 'link' => 'journal.php'],
    ['text' => 'Support', 'link' => 'support.php']
];
?>
