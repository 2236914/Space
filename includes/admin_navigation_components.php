<?php
// Current page and menu items setup
$current_page = basename($_SERVER['PHP_SELF']);

// Admin menu items array (for sidebar)
$admin_menu_items = [
    'admin.php' => [
        'icon' => 'dashboard',
        'text' => 'Dashboard'
    ],
    'studentManagement' => [
        'icon' => 'school',
        'text' => 'Student Management',
        'submenu' => [
            'active-students.php' => ['text' => 'Active Students', 'mini' => 'AS'],
            'student-activities.php' => ['text' => 'Student Activities', 'mini' => 'SA'],
            'support-requests.php' => ['text' => 'Support Requests', 'mini' => 'SR']
        ]
    ],
    'communityManagement' => [
        'icon' => 'groups',
        'text' => 'Community',
        'submenu' => [
            'community-posts.php' => ['text' => 'Posts', 'mini' => 'P'],
            'community-reports.php' => ['text' => 'Reports', 'mini' => 'R']
        ]
    ],
    'announcements' => [
        'icon' => 'campaign',
        'text' => 'Space Alerts',
        'submenu' => [
            'announcements.php' => ['text' => 'Announcements', 'mini' => 'A'],
            'activity-logs.php' => ['text' => 'Activity Logs', 'mini' => 'AL']
        ]
    ],
    'therapistManagement' => [
        'icon' => 'people',
        'text' => 'Therapist Management',
        'submenu' => [
            'applications.php' => ['text' => 'Applications', 'mini' => 'A'],
            'therapists.php' => ['text' => 'Therapists', 'mini' => 'T'],
            'video-tokens.php' => ['text' => 'Video Tokens', 'mini' => 'VT']
        ]
    ],
    'materials' => [
        'icon' => 'library_books',
        'text' => 'Self-Care Materials',
        'submenu' => [
            'manage-materials.php' => ['text' => 'Manage Materials', 'mini' => 'MM']
        ]
    ],
    'reports' => [
        'icon' => 'analytics',
        'text' => 'Reports',
        'submenu' => [
            'usage-reports.php' => ['text' => 'Usage Reports', 'mini' => 'UR'],
            'session-reports.php' => ['text' => 'Session Reports', 'mini' => 'SR']
        ]
    ],
    'admin-support.php' => [
        'text' => 'Support Messages',
        'icon' => 'support_agent'
    ],
    'system-logs.php' => [
        'icon' => 'receipt_long',
        'text' => 'System Logs'
    ]
];

// Admin navbar items array (for top navigation)
$admin_navbar_items = [
    [
        'href' => 'Space/pages/authentication/signin/illustration.html',
        'icon' => 'account_circle',
        'target' => '_blank'
    ],
    [
        'href' => 'javascript:;',
        'icon' => 'settings',
        'class' => 'fixed-plugin-button-nav'
    ],
    [
        'href' => 'javascript:;',
        'icon' => 'notifications',
        'badge' => '11',
        'dropdown' => [
            [
                'icon' => 'email',
                'text' => 'Check new messages'
            ],
            [
                'icon' => 'podcasts',
                'text' => 'Manage podcast session'
            ],
            [
                'icon' => 'shopping_cart',
                'text' => 'Payment successfully completed'
            ]
        ]
    ]
];

// Get current page info
$current_info = ['title' => 'Page Not Found', 'parent' => 'Pages'];
foreach ($admin_menu_items as $section => $item) {
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

// Admin search menu items array
$admin_search_menu_items = [
    ['text' => 'Dashboard', 'link' => 'admin.php'],
    ['text' => 'Active Students', 'link' => 'active-students.php'],
    ['text' => 'Applications', 'link' => 'applications.php'],
    ['text' => 'Support Messages', 'link' => 'admin-support.php'],
    ['text' => 'System Logs', 'link' => 'system-logs.php'],
    ['text' => 'Announcements', 'link' => 'announcements.php'],
    ['text' => 'Reports', 'link' => 'reports.php']
];
?> 