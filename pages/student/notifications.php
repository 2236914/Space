<?php
session_start();

// Check authentication first
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Then load required files and configurations
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/navigation_components.php';

$analytics = new DashboardAnalytics($pdo);

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Add this near the top of the file after session_start()
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add this at the top of the file after session_start()
error_log("Current session data: " . print_r($_SESSION, true));

// Verify srcode is set
if (!isset($_SESSION['srcode'])) {
    error_log("ERROR: srcode not set in session!");
    // You might want to redirect or handle this error
}

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
    date_default_timezone_set('Asia/Manila');
    
    // Debug logging
    error_log("Fetching notifications for student: " . $_SESSION['srcode']);
    
    // First, prepare the query
    $query = "SELECT 
        'activity_log' as source,
        log_id as id,
        'activity' as type,
        action as header,
        action_details as message,
        created_at,
        'edit_note' as icon,
        'bg-info' as color_class
    FROM activity_logs 
    WHERE srcode = CAST(:srcode1 AS VARCHAR(20))  -- Cast to match the column type
        AND action IN ('LOGIN', 'LOGOUT', 'Account Reactivation', 'Account Deactivation', 
                      'Profile Update', 'Password Update', 'Email Update')

    UNION 

    SELECT 
        'mood_log' as source,
        moodlog_id as id,
        'mood' as type,
        'Mood Log Added' as header,
        CONCAT('Logged mood: ', mood_name) as message,
        log_date as created_at,
        'mood' as icon,
        'bg-success' as color_class
    FROM moodlog 
    WHERE srcode = CAST(:srcode2 AS VARCHAR(20))  -- Cast to match the column type

    UNION

    SELECT 
        'notification' as source,
        id,
        type,
        title as header,
        message,
        created_at,
        icon,
        color_class
    FROM notifications 
    WHERE user_id = :user_id

    ORDER BY created_at DESC 
    LIMIT 20";
    
    // Then bind the parameters with unique names
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        'srcode1' => (string)$_SESSION['srcode'],    // Convert to string to match varchar
        'srcode2' => (string)$_SESSION['srcode'],    // Convert to string to match varchar
        'user_id' => $_SESSION['user_id']
    ]);
    
    // Add debug information
    error_log("Query parameters: " . print_r([
        'srcode1' => $_SESSION['srcode'],
        'srcode2' => $_SESSION['srcode'],
        'user_id' => $_SESSION['user_id']
    ], true));
    
    $allNotifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($allNotifications)) {
        error_log("No notifications found for user: " . $_SESSION['user_id']);
        // Debug the actual SQL query
        error_log("SQL Query: " . $query);
    } else {
        error_log("Found " . count($allNotifications) . " notifications");
        // Log the first few notifications for debugging
        error_log("First few notifications: " . print_r(array_slice($allNotifications, 0, 3), true));
    }
} catch (PDOException $e) {
    error_log("Error fetching notifications: " . $e->getMessage());
    error_log("SQL State: " . $e->errorInfo[0]);
    error_log("Error Code: " . $e->errorInfo[1]);
    error_log("Error Message: " . $e->errorInfo[2]);
    $allNotifications = [];
}

// Add this debug query
$debugQuery = "SELECT COUNT(*) as count FROM activity_logs WHERE srcode = :srcode";
$debugStmt = $pdo->prepare($debugQuery);
$debugStmt->execute(['srcode' => $_SESSION['srcode']]);
$logCount = $debugStmt->fetch(PDO::FETCH_ASSOC)['count'];
error_log("Number of activity logs in database for srcode {$_SESSION['srcode']}: $logCount");

// Add this debug query after your main query
$debugQuery = "SELECT DISTINCT action FROM activity_logs WHERE srcode = :srcode";
$debugStmt = $pdo->prepare($debugQuery);
$debugStmt->execute(['srcode' => $_SESSION['srcode']]);
$actions = $debugStmt->fetchAll(PDO::FETCH_COLUMN);
error_log("Available actions in database for srcode {$_SESSION['srcode']}: " . print_r($actions, true));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Activity Logs</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
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
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="student.php">
            <img src="../../assets/img/logo-space.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
            <span class="ms-1 font-weight-bold lead text-dark">SPACE</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">

    <!-- Main Navigation -->
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- User Profile -->
            <li class="nav-item mb-2 mt-0">
                <a href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav">
                    <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                         class="avatar"
                         onerror="this.src='../../assets/img/default-avatar.png';">
                    <span class="nav-link-text ms-2 ps-1">
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
                                echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                            } else {
                                try {
                                    require_once '../../configs/config.php';
                                    $stmt = $pdo->prepare("SELECT firstname, lastname FROM students WHERE srcode = ?");
                                    $stmt->execute([$_SESSION['user_id']]);
                                    if ($user = $stmt->fetch()) {
                                        $_SESSION['firstname'] = $user['firstname'];
                                        $_SESSION['lastname'] = $user['lastname'];
                                        echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']);
                                    } else {
                                        echo 'User Name';
                                    }
                                } catch (PDOException $e) {
                                    echo 'User Name';
                                }
                            }
                        } else {
                            header("Location: ../signin.php");
                            exit();
                        }
                        ?>
                    </span>
                </a>
            </li>
            <hr class="horizontal dark mt-0">

            <!-- Menu Items -->
            <?php foreach ($menu_items as $link => $item): ?>
                <?php if (isset($item['type']) && $item['type'] === 'divider'): ?>
                    <hr class="horizontal dark mt-0">
                <?php else: ?>
                    <li class="nav-item">
                        <?php if (isset($item['submenu'])): ?>
                            <!-- Submenu item -->
                            <a data-bs-toggle="collapse" 
                               href="#<?= $link ?>" 
                               class="nav-link text-dark <?= array_key_exists($current_page, $item['submenu']) ? 'active' : ''; ?>"
                               aria-controls="<?= $link ?>" 
                               role="button" 
                               aria-expanded="<?= array_key_exists($current_page, $item['submenu']) ? 'true' : 'false'; ?>">
                                <i class="material-symbols-rounded opacity-5"><?= $item['icon'] ?></i>
                                <span class="nav-link-text ms-1 ps-1"><?= $item['text'] ?></span>
                            </a>
                            <div class="collapse <?= array_key_exists($current_page, $item['submenu']) ? 'show' : ''; ?>" id="<?= $link ?>">
                                <ul class="nav">
                                    <?php foreach ($item['submenu'] as $sublink => $subitem): ?>
                                        <li class="nav-item">
                                            <a class="nav-link text-dark <?= $current_page == $sublink ? 'active' : ''; ?>" 
                                               href="<?= $sublink ?>">
                                                <span class="sidenav-mini-icon"><?= $subitem['mini'] ?></span>
                                                <span class="sidenav-normal ms-1 ps-1"><?= $subitem['text'] ?></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <!-- Regular item -->
                            <a class="nav-link text-dark <?= $current_page == $link ? 'active' : ''; ?>" 
                               href="<?= $link ?>">
                                <i class="material-symbols-rounded opacity-5"><?= $item['icon'] ?></i>
                                <span class="nav-link-text ms-1 ps-1"><?= $item['text'] ?></span>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Space Support Button -->
            <hr class="horizontal dark mt-0">
            <div class="d-flex justify-content-center">
                <button type="button" class="btn bg-gradient-info w-85 mx-auto" onclick="showSupportDialog()">
                    <i class="material-symbols-rounded opacity-5 me-2">support_agent</i>
                    <span class="nav-link-text">Space Support</span>
                </button>
            </div>

            <!-- Sign Out Button -->
            <div class="d-flex justify-content-center mt-auto">
                <button type="button" class="btn bg-gradient-primary w-85 mx-auto" onclick="handleSignOut()">
                    <i class="material-symbols-rounded opacity-5 me-2">logout</i>
                    <span class="nav-link-text">Sign Out</span>
                </button>
            </div>
        </ul>
    </div>
</aside>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-2">
    <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconSidenavDesktop">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                </div>
            </a>
        </div>
        <nav aria-label="breadcrumb" class="ps-2">
            <ol class="breadcrumb bg-transparent mb-0 p-0">
                <li class="breadcrumb-item text-sm">
                    <a class="opacity-5 text-dark" href="javascript:;">&nbsp;&nbsp;<?php echo htmlspecialchars($current_info['parent']); ?></a>
                </li>
                <li class="breadcrumb-item text-sm text-dark active font-weight-bold" aria-current="page">
                    <?php echo htmlspecialchars($current_info['title']); ?>
                </li>
            </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <ul class="navbar-nav ms-auto">
                <?php
                // Define navbar items
                $navbar_items = [
                    [
                        'href' => 'javascript:;',
                        'icon' => 'message',
                        'dropdown' => [
                            [
                                'icon' => 'email',
                                'text' => 'Check support messages',
                                'onclick' => 'openSupportMessagesModal()',
                                'href' => 'javascript:void(0)',
                                'html' => '<div class="d-flex align-items-center py-1" onclick="openSupportMessagesModal()" style="cursor: pointer;">
                                    <span class="material-symbols-rounded me-2">email</span>
                                    <div class="ms-2">
                                        <h6 class="text-sm font-weight-normal my-auto">Check support messages</h6>
                                    </div>
                                </div>'
                            ]
                        ]
                    ]
                ];

                // Loop through navbar items
                foreach ($navbar_items as $item): ?>
                    <li class="nav-item<?php echo isset($item['dropdown']) ? ' dropdown py-0 pe-3' : ''; ?>">
                        <a href="<?php echo $item['href']; ?>" 
                           class="<?php echo isset($item['dropdown']) ? 'nav-link py-0 px-1 position-relative line-height-0' : 'px-1 py-0 nav-link line-height-0'; ?>"
                           <?php echo isset($item['target']) ? 'target="' . $item['target'] . '"' : ''; ?>
                           <?php echo isset($item['dropdown']) ? 'id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false"' : ''; ?>>
                            <i class="material-symbols-rounded <?php echo isset($item['class']) ? $item['class'] : ''; ?>">
                                <?php echo $item['icon']; ?>
                            </i>
                            <?php if (isset($item['badge'])): ?>
                                <span class="position-absolute top-5 start-100 translate-middle badge rounded-pill bg-danger border border-white small py-1 px-2">
                                    <span class="small"><?php echo $item['badge']; ?></span>
                                    <span class="visually-hidden">unread notifications</span>
                                </span>
                            <?php endif; ?>
                        </a>
                        <?php if (isset($item['dropdown'])): ?>
                            <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                                <?php foreach ($item['dropdown'] as $index => $dropdownItem): ?>
                                    <li class="<?php echo $index < count($item['dropdown']) - 1 ? 'mb-2' : ''; ?>">
                                        <a class="dropdown-item border-radius-md" href="<?php echo $dropdownItem['href']; ?>">
                                            <?php if (isset($dropdownItem['html'])): ?>
                                                <?php echo $dropdownItem['html']; ?>
                                            <?php else: ?>
                                                <div class="d-flex align-items-center py-1">
                                                    <span class="material-symbols-rounded"><?php echo $dropdownItem['icon']; ?></span>
                                                    <div class="ms-2">
                                                        <h6 class="text-sm font-weight-normal my-auto">
                                                            <?php echo $dropdownItem['text']; ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>

                <!-- Mobile menu toggle -->
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                            <i class="sidenav-toggler-line"></i>
                        </div>
                    </a>
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
                            <h5 class="mb-0">Activity Logs</h5>
                            <p class="text-sm mb-0">
                                Activity logs and important updates
                            </p>
                            <div class="d-flex align-items-center justify-content-center mt-2">
                                <span id="date" class="text-sm text-secondary me-2"></span>
                                <span id="time" class="text-sm text-secondary"></span>
                            </div>
                        </div>
                        <div class="card-body pt-4 ps-5">
                            <?php
                            try {
                                if (empty($allNotifications)) {
                                    error_log("No notifications found");
                                } else {
                                    error_log("Found " . count($allNotifications) . " notifications");
                                }
                            } catch (PDOException $e) {
                                error_log("Error displaying notifications: " . $e->getMessage());
                            }
                            ?>
                            <div id="searchResults" class="timeline timeline-one-side" data-timeline-axis-style="dotted">
                                <?php if (empty($allNotifications)): ?>
                                    <div class="text-center text-muted py-4">
                                        <p>No notifications yet</p>
                                        <?php if (isset($_SESSION['srcode'])): ?>
                                            <p class="text-xs">Debug: Searching for srcode: <?php echo htmlspecialchars($_SESSION['srcode']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php else: 
                                    foreach ($allNotifications as $notification): 
                                        error_log("Displaying notification: " . print_r($notification, true));
                                    ?>
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
        <?php include_once('support_messages_modal.php'); ?>
    </main>

    <!--   Core JS Files   -->
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets/js/signout.js"></script>
    <script src="../../assets/js/support.js"></script>
    <script src="../../assets/js/support-messages.js"></script>



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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add auto-refresh for notifications
            function refreshNotifications() {
                fetch('../../admin_operations/get_notifications.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.html) {
                            const container = document.getElementById('searchResults');
                            container.innerHTML = data.html;
                            // Reinitialize search functionality
                            const searchInput = document.getElementById('searchNotifications');
                            const notificationItems = document.querySelectorAll('.notification-item');
                            if (searchInput.value) {
                                // Trigger search if there's an existing search term
                                searchInput.dispatchEvent(new Event('input'));
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing notifications:', error);
                        // Log the full error for debugging
                        console.log('Full error:', error);
                    });
            }

            // Initial refresh
            refreshNotifications();

            // Refresh notifications every 30 seconds
            setInterval(refreshNotifications, 30000);
        });
    </script>
</body>

</html> 