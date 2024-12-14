<?php
session_start();

// Add debugging but don't display it in production
error_log("=== Page Load Start ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

// Required files
require_once '../../configs/config.php';
require_once '../../includes/navigation_components.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Check if student has logged mood today
try {
    // Skip mood check if we're already on moodlog page or coming from moodlog
    if (basename($_SERVER['PHP_SELF']) !== 'moodlog.php' && !isset($_SESSION['from_moodlog'])) {
        $mood_stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM moodlog 
            WHERE srcode = ? 
            AND DATE(log_date) = CURRENT_DATE()
        ");
        $mood_stmt->execute([$_SESSION['user_id']]);
        $has_mood_today = $mood_stmt->fetchColumn() > 0;

        if (!$has_mood_today) {
            $_SESSION['from_moodlog'] = true;
            header("Location: moodlog.php");
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Mood check error: " . $e->getMessage());
    // Continue without mood check if there's an error
    $has_mood_today = true;
}

// Remove the from_moodlog flag after check
unset($_SESSION['from_moodlog']);

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Initialize arrays for mood data
$months = [];
$happy_moods = [];
$neutral_moods = [];
$sad_moods = [];

// Dashboard Data Queries
try {
    // Get mood statistics for the last 6 months
    $mood_stats = $pdo->prepare("
        SELECT 
            DATE_FORMAT(log_date, '%b') as month,
            SUM(CASE 
                WHEN (mood_name LIKE '%Happy%' OR mood_name LIKE '%Love%' OR 
                      selected_emoji IN ('😊', '😍'))
                THEN 1 ELSE 0 
            END) as happy_moods,
            SUM(CASE 
                WHEN (mood_name LIKE '%Calm%' OR mood_name LIKE '%Thoughtful%' OR 
                      selected_emoji IN ('😌', '🤔'))
                THEN 1 ELSE 0 
            END) as neutral_moods,
            SUM(CASE 
                WHEN (mood_name LIKE '%Sad%' OR mood_name LIKE '%Angry%' OR 
                      mood_name LIKE '%Fearful%' OR mood_name LIKE '%Disappointed%' OR
                      selected_emoji IN ('☹️', '😠', '😨', '😔'))
                THEN 1 ELSE 0 
            END) as sad_moods
        FROM moodlog 
        WHERE srcode = ? 
        AND log_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY MONTH(log_date), DATE_FORMAT(log_date, '%b')
        ORDER BY MIN(log_date)
    ");
    
    $mood_stats->execute([$_SESSION['user_id']]);
    $mood_stats_data = $mood_stats->fetchAll(PDO::FETCH_ASSOC);

    // Process the mood data
    if (!empty($mood_stats_data)) {
        foreach ($mood_stats_data as $data) {
            $months[] = $data['month'];
            $happy_moods[] = (int)($data['happy_moods'] ?? 0);
            $neutral_moods[] = (int)($data['neutral_moods'] ?? 0);
            $sad_moods[] = (int)($data['sad_moods'] ?? 0);
        }
    }

    // 1. Check-ins Card Data
    $checkin_query = "SELECT 
        COUNT(*) as total_checkins,
        COUNT(CASE WHEN log_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as weekly_checkins
    FROM moodlog 
    WHERE srcode = ?";

    $stmt = $pdo->prepare($checkin_query);
    $stmt->execute([$_SESSION['user_id']]);
    $checkin_data = $stmt->fetch();

    // 2. Journal Entries Card Data
    $journal_query = "SELECT 
        COUNT(*) as total_entries,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as new_entries,
        MAX(created_at) as last_entry
    FROM journal_entries 
    WHERE srcode = ?";

    $stmt = $pdo->prepare($journal_query);
    $stmt->execute([$_SESSION['user_id']]);
    $journal_data = $stmt->fetch();

    // 4. Journal Distribution Data
    $journal_distribution_query = "SELECT 
        CASE 
            WHEN HOUR(created_at) BETWEEN 5 AND 11 THEN 'Morning'
            WHEN HOUR(created_at) BETWEEN 12 AND 17 THEN 'Afternoon'
            ELSE 'Evening'
        END as time_of_day,
        COUNT(*) as entry_count
    FROM journal_entries 
    WHERE srcode = ? 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY time_of_day";

    $stmt = $pdo->prepare($journal_distribution_query);
    $stmt->execute([$_SESSION['user_id']]);
    $journal_distribution = $stmt->fetchAll();

    // Initialize journal chart data
    $journal_chart_data = [
        'morning' => 0,
        'afternoon' => 0,
        'evening' => 0
    ];

    // Process journal distribution
    foreach ($journal_distribution as $record) {
        $time_of_day = strtolower($record['time_of_day']);
        if (isset($journal_chart_data[$time_of_day])) {
            $journal_chart_data[$time_of_day] = (int)$record['entry_count'];
        }
    }

    // 5. Therapy Sessions Data
    $therapy_query = "SELECT 
        COUNT(*) as total_sessions,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_sessions
    FROM therapy_sessions 
    WHERE srcode = ? 
    AND session_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";

    $stmt = $pdo->prepare($therapy_query);
    $stmt->execute([$_SESSION['user_id']]);
    $therapy_data = $stmt->fetch();

    // Calculate attendance rate
    $attendance_rate = $therapy_data['total_sessions'] > 0 ? 
        round(($therapy_data['completed_sessions'] / $therapy_data['total_sessions']) * 100) : 0;

    // Format last entry time
    $last_entry_time = $journal_data['last_entry'] ? new DateTime($journal_data['last_entry']) : null;
    $now = new DateTime();
    $time_since_last = $last_entry_time ? $last_entry_time->diff($now) : null;
    $last_entry_text = $last_entry_time ? formatTimeSince($time_since_last) : "No entries yet";

} catch (PDOException $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    // Initialize empty data in case of error
    $checkin_data = ['total_checkins' => 0, 'weekly_checkins' => 0];
    $journal_data = ['total_entries' => 0, 'new_entries' => 0];
    $journal_chart_data = ['morning' => 0, 'afternoon' => 0, 'evening' => 0];
    $last_entry_text = "Error loading data";
}

// Helper function to format time since last entry
function formatTimeSince($interval) {
    if ($interval->y > 0) return $interval->y . " year" . ($interval->y > 1 ? "s" : "") . " ago";
    if ($interval->m > 0) return $interval->m . " month" . ($interval->m > 1 ? "s" : "") . " ago";
    if ($interval->d > 0) return $interval->d . " day" . ($interval->d > 1 ? "s" : "") . " ago";
    if ($interval->h > 0) return $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
    if ($interval->i > 0) return $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
    return "just now";
}

// Debug information
if (isset($_GET['debug'])) {
    echo '<pre>';
    print_r([
        'months' => $months,
        'happy_moods' => $happy_moods,
        'neutral_moods' => $neutral_moods,
        'sad_moods' => $sad_moods
    ]);
    echo '</pre>';
}

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Space
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <link href="../../assets/css/navigation.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/plugins/chart.min.js"></script>
  <script src="<?php echo BASE_URL; ?>/assets/js/plugins/quotes.js"></script>
  <script src="../../assets/js/activity-tracker.js"></script>
  <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
  <style>
.chart-canvas {
    transition: all 0.4s ease-in-out;
}

.card:hover .chart-canvas {
    transform: scale(1.02);
}
.insight-box {
    padding: 15px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
}

.insight-box:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-2px);
}

.insight-box h6 {
    color: var(--bs-1stgreen);
    font-weight: 600;
}

.insight-box p {
    color: var(--text-color);
    font-size: 0.875rem;
}

.chart-container {
    position: relative;
    min-height: 220px;
    width: 100%;
}

.chart-canvas {
    width: 100% !important;
    height: 100% !important;
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

<!-- End Navbar -->
<div class="container-fluid py-2">
    <div class="row">
        <div class="ms-3">
            <h3 class="mb-0 h4 font-weight-bolder">Student Dashboard</h3>
            <p class="mb-4">Track your well-being, sessions, and self-care journey</p>
        </div>

        <!-- Check-ins Card -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-2 pe-3">
                    <div class="d-flex justify-content-between">
                        <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary text-center border-radius-lg">
                            <i class="material-symbols-rounded opacity-10">event_available</i>
                        </div>
                        <div>
                            <p class="text-sm mb-0 text-capitalize">My Check-ins</p>
                            <h4 class="mb-0"><?php echo $checkin_data['total_checkins']; ?></h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-2 ps-3">
                    <p class="mb-0 text-sm">
                        <span class="text-success font-weight-bolder">+<?php echo $checkin_data['weekly_checkins']; ?></span> this week
                    </p>
                </div>
            </div>
        </div>

        <!-- Upcoming Sessions Card -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-2 pe-3">
                    <div class="d-flex justify-content-between">
                        <div class="icon icon-md icon-shape bg-gradient-success shadow-success text-center border-radius-lg">
                            <i class="material-symbols-rounded opacity-10">calendar_month</i>
                        </div>
                        <div>
                            <p class="text-sm mb-0 text-capitalize">Next Session</p>
                            <h4 class="mb-0">1</h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-2 ps-3">
                    <p class="mb-0 text-sm">Dec 15, 2023</p>
                </div>
            </div>
        </div>

        <!-- Journal Entries Card -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-2 pe-3">
                    <div class="d-flex justify-content-between">
                        <div class="icon icon-md icon-shape bg-gradient-info shadow-info text-center border-radius-lg">
                            <i class="material-symbols-rounded opacity-10">book</i>
                        </div>
                        <div>
                            <p class="text-sm mb-0 text-capitalize">Journal Entries</p>
                            <h4 class="mb-0"><?php echo $journal_data['total_entries']; ?></h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-2 ps-3">
                    <p class="mb-0 text-sm">
                        <span class="text-success font-weight-bolder">+<?php echo $journal_data['new_entries']; ?></span> new this week
                    </p>
                </div>
            </div>
        </div>

        <!-- Session Feedback Card -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-2 pe-3">
                    <div class="d-flex justify-content-between">
                        <div class="icon icon-md icon-shape bg-gradient-warning shadow-warning text-center border-radius-lg">
                            <i class="material-symbols-rounded opacity-10">rate_review</i>
                        </div>
                        <div>
                            <p class="text-sm mb-0 text-capitalize">Session Feedback</p>
                            <h4 class="mb-0">5</h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-2 ps-3">
                    <p class="mb-0 text-sm">Reviews submitted</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Charts Section -->
<div class="container-fluid py-1">
    <div class="row">
        <!-- Mood Activity Chart -->
        <div class="col-lg-8 col-md-6 mt-4 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0" style="color: var(--bs-1stgreen);">Mood Activity</h6>
                            <p class="text-sm" style="color: var(--text-color-light);">Monthly Mood Overview</p>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 220px; width: 100%;">
                        <canvas id="moodChart" class="chart-canvas"></canvas>
                    </div>
                    <hr class="dark horizontal my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                            <span class="text-sm" style="color: var(--text-color-light);">Updated today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Journal Entry Progress Chart -->
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
            <div class="card h-100">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-0" style="color: var(--bs-1stgreen);">Journal Progress</h6>
                            <p class="text-sm" style="color: var(--text-color-light);">Weekly Entry Distribution</p>
                        </div>
                    </div>
                    <div class="chart-container" style="position: relative; height: 220px; width: 100%; min-height: 220px;">
                        <canvas id="journalChart" class="chart-canvas"></canvas>
                    </div>
                    <hr class="dark horizontal my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                            <span class="text-sm" style="color: var(--text-color-light);">Last entry 2 hours ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Trends and Insights Section -->
<div class="container-fluid py-1">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3" style="color: var(--bs-1stgreen);">Personal Insights & Progress</h6>
                    <div class="row">
                        <!-- Mood Pattern -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Monthly Mood Pattern</h6>
                                <p class="mb-0 h5">
                                    <span class="text-success">↑ Improving</span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Session Attendance -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Session Attendance</h6>
                                <p class="mb-0">
                                    100% Attendance Rate
                                </p>
                            </div>
                        </div>
                        
                        <!-- Self-Care Streak -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Self-Care Streak</h6>
                                <p class="mb-0">
                                    5 Days Continuous Practice
                                </p>
                            </div>
                        </div>
                        
                        <!-- Wellness Tips -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Personalized Tips</h6>
                                <p class="mb-0">
                                    Try mindfulness exercises during peak stress times
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once('support_messages_modal.php'); ?>



  </main>
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/support.js"></script>
<script src="../../assets/js/notification-handler.js"></script>
<script src="../../assets/js/support-messages.js"></script>
  

  <script>
document.querySelectorAll('[ms-code-emoji]').forEach(element => {
  var imageUrl = element.getAttribute('ms-code-emoji');
  if (imageUrl) {
    var img = document.createElement('img');
    img.src = imageUrl;
    img.style.width = '35px';
    img.style.height = '35px';
    img.style.verticalAlign = 'middle';
    element.innerHTML = '';
    element.appendChild(img);
  }
});
</script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <script>
var ctx3 = document.getElementById("chart-line-widgets").getContext("2d");

// Adjust gradient to match the primary color (#3c6454)
var gradientStroke3 = ctx3.createLinearGradient(0, 230, 0, 50);
gradientStroke3.addColorStop(1, 'rgba(60, 100, 84, 0.2)'); // Lighter shade of primary color
gradientStroke3.addColorStop(0.5, 'rgba(60, 100, 84, 0.1)');
gradientStroke3.addColorStop(0, 'rgba(60, 100, 84, 0)'); // Transparent

new Chart(ctx3, {
    type: "line",
    data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
            label: "Engagement Time",
            tension: 0.4,
            pointRadius: 4,
            pointBackgroundColor: "#3c6454", // Primary color for points
            borderColor: "#3c6454", // Primary color for line
            borderWidth: 3,
            backgroundColor: gradientStroke3,
            data: [12, 15, 14, 13, 16, 17, 14, 13, 15],
            fill: true,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                callbacks: {
                    label: function (tooltipItem) {
                        return `Engagement Time: ${tooltipItem.raw} minutes`;
                    },
                },
            },
            annotation: {
                annotations: {
                    benchmark: {
                        type: 'line',
                        yMin: 15,
                        yMax: 15,
                        borderColor: '#FF5252', // Benchmark line in red
                        borderWidth: 2,
                        label: {
                            content: 'Target: 15 min',
                            enabled: true,
                            position: 'center',
                            backgroundColor: 'rgba(255, 82, 82, 0.8)',
                        },
                    },
                },
            },
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
        scales: {
            y: {
                grid: {
                    drawOnChartArea: true,
                    borderDash: [5, 5],
                },
                ticks: {
                    callback: function(value) {
                        return value + " min";
                    },
                },
            },
            x: {
                grid: {
                    drawOnChartArea: true,
                    borderDash: [5, 5],
                },
            },
        },
    },
});

  </script>
  <script>
function updateDateTime() {
    const now = new Date();
    
    // Format date without weekday
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    document.getElementById('date').textContent = now.toLocaleDateString('en-US', options);
    
    // Format time
    document.getElementById('time').textContent = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Update immediately and then every second
updateDateTime();
setInterval(updateDateTime, 1000);
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ActivityTracker if it exists
    if (typeof ActivityTracker !== 'undefined') {
        ActivityTracker.init();
    }

    // Add refresh button to quote card header
    const cardHeader = document.querySelector('#quote-container')?.closest('.card')?.querySelector('.card-header');
    if (cardHeader) {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-link p-0 ms-auto';
        refreshBtn.innerHTML = '<i class="material-symbols-rounded">refresh</i>';
        refreshBtn.onclick = refreshQuote;
        cardHeader.style.display = 'flex';
        cardHeader.style.alignItems = 'start';
        cardHeader.appendChild(refreshBtn);
    }
});

// Function to like/unlike a quote
function likeQuote(quoteId) {
    event.preventDefault();
    event.stopPropagation();

    const userId = '<?php echo $_SESSION['user_id']; ?>';

    fetch('../../admin_operations/like_quote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            quote_id: quoteId,
            user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const likeBtn = event.currentTarget;
            
            // Toggle button appearance
            if (data.liked) {
                likeBtn.classList.remove('btn-outline-primary');
                likeBtn.classList.add('bg-gradient-primary', 'text-white');
            } else {
                likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                likeBtn.classList.add('btn-outline-primary');
            }
            
            // Show success toast
            Swal.fire({
                icon: 'success',
                title: data.liked ? 'Quote Liked!' : 'Quote Unliked!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });

            // Log activity if tracker exists
            if (typeof ActivityTracker !== 'undefined') {
                ActivityTracker.logActivity();
            }
        } else {
            throw new Error(data.error || 'Unknown error occurred');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: error.message || 'Failed to update like status'
        });
    });
}

// Function to refresh quote
function refreshQuote() {
    const quoteContent = document.querySelector('.quote-content');
    const quoteAuthor = document.querySelector('.quote-author');
    
    if (!quoteContent || !quoteAuthor) return;
    
    const originalContent = quoteContent.innerHTML;
    quoteContent.innerHTML = 'Loading...';
    
    fetch('../../admin_operations/get_random_quote.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            quoteContent.textContent = `"${data.quote.content}"`;
            quoteAuthor.textContent = `- ${data.quote.author}`;
            
            const likeBtn = document.querySelector('button[onclick^="likeQuote"]');
            if (likeBtn) {
                likeBtn.setAttribute('onclick', `likeQuote(${data.quote.id})`);
                
                // Update like button appearance
                if (data.quote.is_liked) {
                    likeBtn.classList.remove('btn-outline-primary');
                    likeBtn.classList.add('bg-gradient-primary', 'text-white');
                } else {
                    likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                    likeBtn.classList.add('btn-outline-primary');
                }
            }

            // Log activity if tracker exists
            if (typeof ActivityTracker !== 'undefined') {
                ActivityTracker.logActivity();
            }
        } else {
            throw new Error(data.error || 'Failed to fetch quote');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Failed to refresh quote'
        });
        quoteContent.innerHTML = originalContent;
    });
}
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize ActivityTracker
    ActivityTracker.init();
    
    // Function to be called after any activity
    window.updateActivityData = function() {
        ActivityTracker.logActivity();
    };
});

// Add this to handle profile updates
document.addEventListener('profileUpdated', function() {
    if(typeof updateActivityData === 'function') {
        updateActivityData();
    }
});

// Add this to handle mood logs
document.addEventListener('moodLogged', function() {
    if(typeof updateActivityData === 'function') {
        updateActivityData();
    }
});

// Add this to handle quote interactions
document.addEventListener('quoteInteraction', function() {
    if(typeof updateActivityData === 'function') {
        updateActivityData();
    }
});
</script>

<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById("chart-bars");
    
    // Destroy existing chart if it exists
    if (window.myChart instanceof Chart) {
        window.myChart.destroy();
    }
    
    if (typeof activityData !== 'undefined') {
        window.myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: activityData.labels,
                datasets: [{
                    label: 'Activities',
                    data: activityData.counts,
                    backgroundColor: '#3c6454',
                    borderRadius: 5,
                    maxBarThickness: 35
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    } else {
        console.error('Activity data not found');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Function to like/unlike a quote
function likeQuote(quoteId) {
    event.preventDefault();
    event.stopPropagation();

    console.log('Attempting to like/unlike quote:', quoteId);
    const userId = '<?php echo $_SESSION['user_id']; ?>'; // Keep as string
    console.log('User ID:', userId);

    fetch('../../admin_operations/like_quote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            quote_id: quoteId,
            user_id: userId // Send as string
        })
    })
    .then(response => {
        console.log('Raw response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            const likeBtn = event.currentTarget;
            if (data.liked) {
                likeBtn.classList.remove('btn-outline-primary');
                likeBtn.classList.add('bg-gradient-primary', 'text-white');
            } else {
                likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                likeBtn.classList.add('btn-outline-primary');
            }
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: data.liked ? 'Quote Liked!' : 'Quote Unliked!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Failed to update like status'
        });
    });
}

// Function to refresh quote
function refreshQuote() {
    // Show loading state
    const quoteContent = document.querySelector('.quote-content');
    const quoteAuthor = document.querySelector('.quote-author');
    const originalContent = quoteContent.innerHTML;
    
    quoteContent.innerHTML = 'Loading...';
    
    fetch('../../admin_operations/get_random_quote.php')
    .then(response => {
        console.log('Raw response:', response);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            quoteContent.textContent = `"${data.quote.content}"`;
            quoteAuthor.textContent = `- ${data.quote.author}`;
            
            const likeBtn = document.querySelector('button[onclick^="likeQuote"]');
            likeBtn.setAttribute('onclick', `likeQuote(${data.quote.id})`);
            
            if (data.quote.is_liked) {
                likeBtn.classList.remove('btn-outline-primary');
                likeBtn.classList.add('bg-gradient-primary', 'text-white');
            } else {
                likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                likeBtn.classList.add('btn-outline-primary');
            }
        } else {
            throw new Error(data.error || 'Failed to fetch quote');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Failed to refresh quote'
        });
        quoteContent.innerHTML = originalContent;
    });
}
</script>

<script>
console.log('Current quote data:', <?php echo json_encode($quote); ?>);

// Enhanced like function with debugging
function likeQuote(quoteId) {
    console.log('Liking quote:', quoteId);
    console.log('User ID:', '<?php echo $_SESSION['user_id']; ?>');
    
    fetch('../../admin_operations/like_quote.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            quote_id: quoteId,
            user_id: '<?php echo $_SESSION['user_id']; ?>'
        })
    })
    .then(response => {
        console.log('Raw response:', response);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            const likeBtn = event.target.closest('button');
            if (data.liked) {
                likeBtn.classList.remove('btn-outline-primary');
                likeBtn.classList.add('bg-gradient-primary', 'text-white');
            } else {
                likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                likeBtn.classList.add('btn-outline-primary');
            }
            
            // Show success message
            Swal.fire({
                icon: 'success',
                title: data.liked ? 'Quote Liked!' : 'Quote Unliked!',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Failed to update like status'
        });
    });
}

// Enhanced refresh function with debugging
function refreshQuote() {
    console.log('Refreshing quote...');
    
    const quoteContent = document.querySelector('.quote-content');
    const quoteAuthor = document.querySelector('.quote-author');
    const originalContent = quoteContent.innerHTML;
    
    quoteContent.innerHTML = 'Loading...';
    
    fetch('../../admin_operations/get_random_quote.php')
    .then(response => {
        console.log('Raw response:', response);
        return response.json();
    })
    .then(data => {
        if (data.success) {
            quoteContent.textContent = `"${data.quote.content}"`;
            quoteAuthor.textContent = `- ${data.quote.author}`;
            
            const likeBtn = document.querySelector('button[onclick^="likeQuote"]');
            likeBtn.setAttribute('onclick', `likeQuote(${data.quote.id})`);
            
            if (data.quote.is_liked) {
                likeBtn.classList.remove('btn-outline-primary');
                likeBtn.classList.add('bg-gradient-primary', 'text-white');
            } else {
                likeBtn.classList.remove('bg-gradient-primary', 'text-white');
                likeBtn.classList.add('btn-outline-primary');
            }
        } else {
            throw new Error(data.error || 'Failed to fetch quote');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Failed to refresh quote'
        });
        quoteContent.innerHTML = originalContent;
    });
}
</script>

<script>
async function fetchQuote() {
    try {
        const response = await fetch('../../admin_operations/get_quote.php');
        const data = await response.json();
        
        const quoteContent = document.querySelector('.quote-content');
        const quoteAuthor = document.querySelector('.quote-author');
        
        if (data.success) {
            quoteContent.textContent = `"${data.content}"`;
            quoteAuthor.textContent = `- ${data.author}`;
        } else {
            throw new Error(data.error || 'Failed to fetch quote');
        }
    } catch (error) {
        console.error('Error:', error);
        const quoteContent = document.querySelector('.quote-content');
        if (quoteContent) {
            quoteContent.textContent = 'Unable to load quote at this time';
        }
    }
}

// Fetch quote when page loads
document.addEventListener('DOMContentLoaded', fetchQuote);
</script>
<script>
// Add this to your existing scripts
function checkMoodLogStatus() {
    fetch('../../admin_operations/check_mood_log.php')
        .then(response => response.json())
        .then(data => {
            if (!data.has_mood_today) {
                window.location.href = 'moodlog.php';
            }
        });
}

// Check mood log status when page loads and periodically
document.addEventListener('DOMContentLoaded', function() {
    checkMoodLogStatus();
    setInterval(checkMoodLogStatus, 300000); // Check every 5 minutes
});
</script>

<script>
// Handle reminder toggle and time change
document.addEventListener('DOMContentLoaded', function() {
    const reminderToggle = document.getElementById('enableBreathingReminder');
    const reminderTime = document.getElementById('breathingReminderTime');
    const statusBadge = document.getElementById('reminderStatus');

    // Load saved preferences
    const savedTime = localStorage.getItem('breathingReminderTime') || '17:00';
    const reminderEnabled = localStorage.getItem('breathingReminderEnabled') !== 'false';
    
    reminderTime.value = savedTime;
    reminderToggle.checked = reminderEnabled;
    updateStatus(reminderEnabled);

    // Handle toggle change
    reminderToggle.addEventListener('change', function() {
        localStorage.setItem('breathingReminderEnabled', this.checked);
        updateStatus(this.checked);
        
        if (this.checked) {
            Swal.fire({
                icon: 'success',
                title: 'Reminders Enabled',
                text: 'You will receive daily breathing exercise reminders',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    });

    // Handle time change
    reminderTime.addEventListener('change', function() {
        localStorage.setItem('breathingReminderTime', this.value);
        
        Swal.fire({
            icon: 'success',
            title: 'Reminder Time Updated',
            text: `Daily reminder set for ${formatTime(this.value)}`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    });

    function updateStatus(enabled) {
        statusBadge.textContent = enabled ? 'Active' : 'Inactive';
        statusBadge.className = `badge ${enabled ? 'bg-gradient-success' : 'bg-gradient-secondary'}`;
    }

    function formatTime(timeString) {
        return new Date('2000-01-01T' + timeString)
            .toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const reminderToggle = document.getElementById('enableReminder');
    const reminderTime = document.getElementById('reminderTime');
    const statusBadge = document.getElementById('reminderStatusBadge');

    // Load saved preferences
    const savedTime = localStorage.getItem('reminderTime') || '17:00';
    const reminderEnabled = localStorage.getItem('reminderEnabled') !== 'false';
    
    reminderTime.value = savedTime;
    reminderToggle.checked = reminderEnabled;
    updateStatus(reminderEnabled);

    // Handle toggle change
    reminderToggle.addEventListener('change', function() {
        localStorage.setItem('reminderEnabled', this.checked);
        updateStatus(this.checked);
        
        if (this.checked) {
            Swal.fire({
                icon: 'success',
                title: 'Reminders Enabled',
                text: 'You will receive daily reminders',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        }
    });

    // Handle time change
    reminderTime.addEventListener('change', function() {
        localStorage.setItem('reminderTime', this.value);
        
        Swal.fire({
            icon: 'success',
            title: 'Reminder Time Updated',
            text: `Daily reminder set for ${formatTime(this.value)}`,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    });

    function updateStatus(enabled) {
        statusBadge.textContent = enabled ? 'Active' : 'Inactive';
        statusBadge.className = `badge ${enabled ? 'bg-gradient-success' : 'bg-gradient-secondary'}`;
    }

    function formatTime(timeString) {
        return new Date('2000-01-01T' + timeString)
            .toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh session data every 5 minutes
    setInterval(function() {
        fetch('../../admin_operations/get_next_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update session information
                    const dateElement = document.querySelector('.card p.text-white.opacity-8.mb-0');
                    const typeElement = document.querySelector('.card h6.text-white.mb-0');
                    
                    if (data.session) {
                        dateElement.textContent = data.session.formatted_date;
                        typeElement.textContent = data.session.session_type;
                    } else {
                        dateElement.textContent = 'No upcoming sessions';
                        typeElement.textContent = 'N/A';
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    }, 300000); // 5 minutes
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Mood Activity Chart
    const moodCanvas = document.getElementById("moodChart");
    if (moodCanvas) {
        const ctx = moodCanvas.getContext('2d');
        
        // Create gradients using admin dashboard colors
        const primaryGradient = ctx.createLinearGradient(0, 0, 0, 400);
        primaryGradient.addColorStop(0, 'rgba(60, 100, 84, 0.8)');    // #3c6454
        primaryGradient.addColorStop(1, 'rgba(60, 100, 84, 0.6)');

        const secondaryGradient = ctx.createLinearGradient(0, 0, 0, 400);
        secondaryGradient.addColorStop(0, 'rgba(147, 189, 170, 0.8)'); // #93bdaa
        secondaryGradient.addColorStop(1, 'rgba(147, 189, 170, 0.6)');

        const tertiaryGradient = ctx.createLinearGradient(0, 0, 0, 400);
        tertiaryGradient.addColorStop(0, 'rgba(193, 196, 206, 0.8)'); // #c1c4ce
        tertiaryGradient.addColorStop(1, 'rgba(193, 196, 206, 0.6)');

        new Chart(moodCanvas, {
            type: "bar",
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [
                    {
                        label: "Happy",
                        data: <?php echo json_encode($happy_moods); ?>,
                        backgroundColor: primaryGradient,
                        borderRadius: 4,
                        maxBarThickness: 25,
                        categoryPercentage: 0.8,
                        barPercentage: 0.9
                    },
                    {
                        label: "Neutral",
                        data: <?php echo json_encode($neutral_moods); ?>,
                        backgroundColor: secondaryGradient,
                        borderRadius: 4,
                        maxBarThickness: 25,
                        categoryPercentage: 0.8,
                        barPercentage: 0.9
                    },
                    {
                        label: "Sad",
                        data: <?php echo json_encode($sad_moods); ?>,
                        backgroundColor: tertiaryGradient,
                        borderRadius: 4,
                        maxBarThickness: 25,
                        categoryPercentage: 0.8,
                        barPercentage: 0.9
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 11,
                                family: "Inter"
                            },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        padding: 10,
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#344767',
                        bodyColor: '#344767',
                        borderColor: '#e9ecef',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                family: "Inter"
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                size: 11,
                                family: "Inter"
                            }
                        },
                        grid: {
                            borderDash: [5, 5],
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    // Journal Distribution Chart
    const journalCanvas = document.getElementById("journalChart");
    if (journalCanvas) {
        new Chart(journalCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Morning', 'Afternoon', 'Evening'],
                datasets: [{
                    data: [
                        <?php echo $journal_chart_data['morning']; ?>,
                        <?php echo $journal_chart_data['afternoon']; ?>,
                        <?php echo $journal_chart_data['evening']; ?>
                    ],
                    backgroundColor: [
                        '#3c6454',  // Primary green
                        '#93bdaa',  // Secondary green
                        '#c1c4ce'   // Gray
                    ],
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            color: '#344767',
                            font: {
                                size: 11,
                                family: 'Inter'
                            },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#344767',
                        bodyColor: '#344767',
                        borderColor: '#e9ecef',
                        borderWidth: 1,
                        padding: 10,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' entries';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
</body>
</html>
