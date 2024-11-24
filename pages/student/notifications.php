<?php
session_start();
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';

$analytics = new DashboardAnalytics($pdo);

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Function to get time ago with better formatting
function getTimeAgo($timestamp) {
    $datetime = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($datetime);
    
    // If it's from a different year
    if ($interval->y > 0) {
        return $datetime->format('F j, Y g:i A');
    }
    
    // If it's within last minute
    if ($interval->i == 0 && $interval->h == 0 && $interval->d == 0) {
        if ($interval->s < 30) {
            return 'Just now';
        }
        return $interval->s . ' seconds ago';
    }
    
    // If it's within the hour
    if ($interval->h == 0 && $interval->d == 0) {
        return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    }
    
    // If it's from today
    if ($interval->d == 0) {
        return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    }
    
    // If it's from yesterday
    if ($interval->d == 1) {
        return 'Yesterday at ' . $datetime->format('g:i A');
    }
    
    // If it's from this week (less than 7 days)
    if ($interval->d < 7) {
        return $interval->d . ' days ago';
    }
    
    // If it's from this year
    return $datetime->format('F j, g:i A');
}

// Define notification types with their icons and colors
$notificationTypes = [
    'activity' => ['icon' => 'edit_note', 'color_class' => 'bg-info'],
    'appointment' => ['icon' => 'calendar_month', 'color_class' => 'bg-primary'],
    'mood' => ['icon' => 'mood', 'color_class' => 'bg-success'],
    'community' => ['icon' => 'forum', 'color_class' => 'bg-dark'],
    'announcement' => ['icon' => 'campaign', 'color_class' => 'bg-warning']
];

// Fetch notifications with proper timezone handling
try {
    // Set timezone for PHP
    date_default_timezone_set('Asia/Manila');
    
    // Notifications query
    $notifQuery = "SELECT 
        'notification' as source,
        id,
        type,
        title as header,
        message,
        created_at,
        icon,
        color_class
    FROM notifications 
    WHERE user_id = :user_id";

    // Activity logs query
    $logQuery = "SELECT 
        'activity_log' as source,
        log_id as id,
        'activity' as type,
        action as header,
        action_details as message,
        created_at,
        'edit_note' as icon,
        'bg-info' as color_class
    FROM activity_logs 
    WHERE srcode = :srcode";

    // Combine both queries with proper ordering
    $query = "($notifQuery) UNION ($logQuery) ORDER BY created_at DESC LIMIT 20";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'srcode' => $_SESSION['user_id']
    ]);
    
    $allNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    $allNotifications = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Space - Notifications</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .notification-item {
            transition: all 0.3s ease;
        }
        .no-results {
            padding: 20px;
            color: #666;
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <!-- Aside Navigation -->
    <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
        <div class="sidenav-header">
            <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
            <a class="navbar-brand px-4 py-3 m-0" href="student.php">
                <img src="../../assets/img/logo-space.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
                <span class="ms-1 font-weight-bold lead text-dark">SPACE</span>
            </a>
        </div>
        <hr class="horizontal dark mt-0 mb-2">

        <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
            <ul class="navbar-nav">
                <!-- User Profile -->
                <li class="nav-item mb-2 mt-0">
                    <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav" role="button" aria-expanded="false">
                        <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" class="avatar" onerror="this.src='../../assets/img/default-avatar.png';">
                        <span class="nav-link-text ms-2 ps-1">
                            <?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?>
                        </span>
                    </a>
                    <div class="collapse" id="ProfileNav">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="profile.php">
                                    <span class="sidenav-mini-icon">P</span>
                                    <span class="sidenav-normal ms-3 ps-1">Profile</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <hr class="horizontal dark mt-0">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link text-dark active" aria-controls="dashboardsExamples" role="button" aria-expanded="true">
                        <i class="material-symbols-rounded opacity-5">space_dashboard</i>
                        <span class="nav-link-text ms-1 ps-1">Dashboard</span>
                    </a>
                    <div class="collapse show" id="dashboardsExamples">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="student.php">
                                    <span class="sidenav-mini-icon">A</span>
                                    <span class="sidenav-normal ms-1 ps-1">Analytics</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="mood-tracker.php">
                                    <span class="sidenav-mini-icon">MT</span>
                                    <span class="sidenav-normal ms-1 ps-1">Mood Tracker</span>
                                </a>
                            </li>
                            <li class="nav-item active">
                                <a class="nav-link text-dark active" href="notifications.php">
                                    <span class="sidenav-mini-icon">N</span>
                                    <span class="sidenav-normal ms-1 ps-1">Notifications</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- MENU -->
                <li class="nav-item mt-3">
                    <h6 class="ps-3 ms-2 text-uppercase text-xs font-weight-bolder text-dark">MENU</h6>
                </li>
                <li class="nav-item">
                    <a data-bs-toggle="collapse" href="#account" class="nav-link text-dark" aria-controls="selfCareExamples" role="button" aria-expanded="false">
                        <i class="material-symbols-rounded opacity-5">account_circle</i>
                        <span class="nav-link-text ms-1 ps-1">Account</span>
                    </a>
                    <div class="collapse" id="account">
                        <ul class="nav">
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="account-settings.php">
                                    <span class="sidenav-mini-icon">S</span>
                                    <span class="sidenav-normal ms-1 ps-1">Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="articles.php">
                                    <span class="sidenav-mini-icon">A</span>
                                    <span class="sidenav-normal ms-1 ps-1">Articles</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-dark" href="journal.php">
                                    <span class="sidenav-mini-icon">J</span>
                                    <span class="sidenav-normal ms-1 ps-1">Journal</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Calendar -->
                <li class="nav-item">
                    <a class="nav-link text-dark" href="calendar.php">
                        <i class="material-symbols-rounded opacity-5">calendar_month</i>
                        <span class="nav-link-text ms-1 ps-1">Calendar</span>
                    </a>
                </li>

                <!-- Bottom Buttons -->
                <li class="nav-item mt-3">
                    <hr class="horizontal dark mt-0">
                    <a href="support.php" class="btn bg-gradient-info w-90 mb-2 ms-2">
                        <i class="material-symbols-rounded opacity-5 me-2">support_agent</i> Space Support
                    </a>
                </li>
                <li class="nav-item">
                    <button type="button" class="btn bg-gradient-primary w-90 mb-2 ms-2" onclick="handleSignOut()">
                        <i class="material-symbols-rounded opacity-5 me-2">logout</i> Sign Out
                    </button>
                </li>
            </ul>
        </div>
    </aside>

    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <!-- Navbar -->
        <nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
            <div class="container-fluid py-1 px-2">
                <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none">
                    <a href="javascript:;" class="nav-link text-body p-0">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
                </div>
                <nav aria-label="breadcrumb" class="ps-2">
                    <ol class="breadcrumb bg-transparent mb-0 p-0">
                        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
                        <li class="breadcrumb-item text-sm text-dark active font-weight-bold" aria-current="page">Notifications</li>
                    </ol>
                </nav>
                <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                    <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        <div class="input-group input-group-outline">
                            <label class="form-label">Search here</label>
                            <input type="text" class="form-control" id="searchNotifications">
                        </div>
                    </div>
                    <ul class="navbar-nav justify-content-end">
                        <li class="nav-item">
                            <a href="javascript:;" class="nav-link text-body p-0 position-relative" target="_blank">
                                <i class="material-symbols-rounded">account_circle</i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="javascript:;" class="nav-link text-body p-0 position-relative">
                                <i class="material-symbols-rounded">settings</i>
                            </a>
                        </li>
                        <li class="nav-item dropdown pe-2">
                            <a href="javascript:;" class="nav-link text-body p-0 position-relative" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="material-symbols-rounded">notifications</i>
                                <span class="position-absolute top-5 start-100 translate-middle badge rounded-pill bg-danger border border-white small py-1 px-2">
                                    <span class="small">11</span>
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                                <li class="mb-2">
                                    <a class="dropdown-item border-radius-md" href="javascript:;">
                                        <div class="d-flex align-items-center py-1">
                                            <span class="material-symbols-rounded">email</span>
                                            <div class="ms-2">
                                                <h6 class="text-sm font-weight-normal my-auto">Check new messages</h6>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a class="dropdown-item border-radius-md" href="javascript:;">
                                        <div class="d-flex align-items-center py-1">
                                            <span class="material-symbols-rounded">podcasts</span>
                                            <div class="ms-2">
                                                <h6 class="text-sm font-weight-normal my-auto">Manage podcast session</h6>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item border-radius-md" href="javascript:;">
                                        <div class="d-flex align-items-center py-1">
                                            <span class="material-symbols-rounded">shopping_cart</span>
                                            <div class="ms-2">
                                                <h6 class="text-sm font-weight-normal my-auto">Payment successfully completed</h6>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="container-fluid py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-10 col-lg-8">
                    <div class="card">
                        <div class="card-header pt-5 text-center">
                            <h5 class="mb-0">Notifications</h5>
                            <p class="text-sm mb-0">
                                Activity logs and important updates
                            </p>
                        </div>
                        <div class="card-body pt-4 ps-5">
                            <div id="searchResults" class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                                <?php if (empty($allNotifications)): ?>
                                    <div class="text-center text-muted">
                                        <p>No notifications yet</p>
                                    </div>
                                <?php else: 
                                    foreach ($allNotifications as $notification): ?>
                                        <div class="timeline-block mb-3 notification-item">
                                            <span class="timeline-step <?php echo htmlspecialchars($notification['color_class']); ?> p-2">
                                                <i class="material-symbols-rounded text-white opacity-10">
                                                    <?php echo htmlspecialchars($notification['icon']); ?>
                                                </i>
                                            </span>
                                            <div class="timeline-content searchable-content">
                                                <h6 class="text-dark font-weight-bold mb-0">
                                                    <?php echo htmlspecialchars($notification['header']); ?>
                                                </h6>
                                                <p class="text-secondary text-xs mt-1 mb-0">
                                                    <?php echo getTimeAgo($notification['created_at']); ?>
                                                </p>
                                                <p class="text-sm mt-2 mb-0">
                                                    <?php echo htmlspecialchars($notification['message']); ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!--   Core JS Files   -->
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>

    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>

    <!-- DateTime Script -->
    <script>
        function updateDateTime() {
            const now = new Date();
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
            document.getElementById('time').textContent = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>

    <!-- Security Scripts -->
    <script>
        // Prevent going back to protected page after logout
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, null, window.location.href);
        };

        // Handle sign out
        function handleSignOut() {
            Swal.fire({
                title: 'Sign Out',
                text: 'Are you sure you want to sign out?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'No, cancel',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                    cancelButton: 'btn btn-outline-primary btn-sm mx-2',
                    actions: 'justify-content-center'
                },
                buttonsStyling: false,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Signing out...',
                        text: 'Please wait',
                        icon: 'info',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        window.location.href = '../../admin_operations/logout.php';
                    }, 1000);
                }
            });
        }

        // Check login status
        function checkLoginStatus() {
            fetch('../../admin_operations/check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.logged_in) {
                        window.location.href = '../signin.php';
                    }
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            checkLoginStatus();
        });

        setInterval(checkLoginStatus, 30000);
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchNotifications');
        const notificationItems = document.querySelectorAll('.notification-item');

        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            let hasResults = false;

            notificationItems.forEach(item => {
                const content = item.querySelector('.searchable-content').textContent.toLowerCase();
                
                if (content.includes(searchTerm)) {
                    item.style.display = 'flex';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });

            // Show "no results" message if nothing found
            const noResults = document.querySelector('.no-results');
            if (!hasResults && searchTerm !== '') {
                if (!noResults) {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.className = 'text-center text-muted no-results';
                    noResultsDiv.innerHTML = '<p>No notifications found matching your search</p>';
                    document.getElementById('searchResults').appendChild(noResultsDiv);
                }
            } else if (noResults) {
                noResults.remove();
            }
        });

        // Clear search when input is cleared
        searchInput.addEventListener('change', function(e) {
            if (e.target.value === '') {
                notificationItems.forEach(item => {
                    item.style.display = 'flex';
                });
                const noResults = document.querySelector('.no-results');
                if (noResults) noResults.remove();
            }
        });
    });
    </script>
</body>

</html> 