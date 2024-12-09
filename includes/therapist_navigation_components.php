<?php
// Current page and menu items setup
$current_page = basename($_SERVER['PHP_SELF']);

// Therapist menu items array (for sidebar)
$therapist_menu_items = [
    'therapist.php' => [
        'icon' => 'dashboard',
        'text' => 'Dashboard'
    ],
    'community.php' => [
        'icon' => 'forum',
        'text' => 'Community'
    ],
    'appointments.php' => [
        'icon' => 'calendar_month',
        'text' => 'Appointments'
    ]
];


// Get current page info
$current_info = ['title' => 'Page Not Found', 'parent' => 'Pages'];
foreach ($therapist_menu_items as $page => $item) {
    if ($current_page === $page) {
        $current_info = [
            'title' => $item['text'],
            'parent' => 'Pages'
        ];
        break;
    }
}
?> 