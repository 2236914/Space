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
            'student_management.php' => ['text' => 'Student Profiles', 'mini' => 'SP']
        ]
    ],
    'therapistManagement' => [
        'icon' => 'people',
        'text' => 'Therapist Management',
        'submenu' => [
            'applications.php' => ['text' => 'Applications', 'mini' => 'A'],
            'therapist_management.php' => ['text' => 'Therapist Profiles', 'mini' => 'TP'],
        ]
    ],
    'community-management.php' => [
        'text' => 'Community Management',
        'icon' => 'groups'
    ] ,
    'report-management.php' => [
        'text' => 'Report Management',
        'icon' => 'report'
    ],
    'admin-support.php' => [
        'text' => 'Support Messages',
        'icon' => 'support_agent'
    ]  
];

// Admin navbar items array (for top navigation)
$admin_navbar_items = [
    [
        'icon' => ''
    ],
    [
        'icon' => ''
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