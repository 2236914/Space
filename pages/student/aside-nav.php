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
?>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconNavbarSidenav = document.getElementById('iconNavbarSidenav');
    const iconSidenav = document.getElementById('iconSidenav');
    const body = document.getElementsByTagName('body')[0];

    function toggleSidenav() {
        if (window.innerWidth >= 1200) {
            body.classList.toggle('g-sidenav-hidden');
        } else {
            body.classList.toggle('g-sidenav-pinned');
        }
    }

    if (iconNavbarSidenav) {
        iconNavbarSidenav.addEventListener('click', toggleSidenav);
    }

    if (iconSidenav) {
        iconSidenav.addEventListener('click', toggleSidenav);
    }

    // Initial state
    if (window.innerWidth >= 1200) {
        body.classList.remove('g-sidenav-hidden');
    } else {
        body.classList.remove('g-sidenav-pinned');
    }

    // Handle window resize
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(function() {
            if (window.innerWidth >= 1200) {
                body.classList.remove('g-sidenav-hidden');
                body.classList.remove('g-sidenav-pinned');
            } else {
                body.classList.remove('g-sidenav-pinned');
                body.classList.remove('g-sidenav-hidden');
            }
        }, 100);
    });
});
</script>

<style>
/* Base sidenav styles */
.sidenav {
    z-index: 1040;
    width: 15rem;
    margin-left: 0.5rem;
}

/* Mobile styles */
@media (max-width: 1199.98px) {
    .sidenav {
        transform: translateX(-15rem);
        transition: transform 0.3s ease;
    }

    .g-sidenav-pinned .sidenav {
        transform: translateX(0);
    }

    /* Dark overlay */
    .g-sidenav-pinned::after {
        content: "";
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.3);
        z-index: 1039;
    }
}

/* Desktop styles */
@media (min-width: 1200px) {
    .sidenav {
        transform: translateX(0);
        transition: transform 0.3s ease;
    }

    .g-sidenav-hidden .sidenav {
        transform: translateX(-15rem);
    }

    main {
        margin-left: 15.5rem !important;
        transition: margin-left 0.3s ease;
    }

    .g-sidenav-hidden main {
        margin-left: 0 !important;
    }
}

/* Toggle button styles */
.sidenav-toggler-inner {
    position: relative;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.sidenav-toggler-line {
    height: 2px;
    width: 100%;
    background-color: #67748e;
    display: block;
    margin: 4px auto;
    transition: all 0.2s;
}
</style>


