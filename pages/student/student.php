<?php
session_start();

// Add debugging but don't display it in production
error_log("=== Page Load Start ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));

// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/navigation_components.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Initialize analytics
$analytics = new DashboardAnalytics($pdo);

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

// Only show debug info if needed (remove in production)
if (isset($_GET['debug'])) {
    echo '<div style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px; border-radius: 4px;">';
    echo '<h3>Debug Information:</h3>';
    echo '<pre>';
    echo "Session Status:\n";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Data: \n";
    print_r($_SESSION);
    echo "\nDatabase Connection:\n";
    echo "PDO Connection: " . (isset($pdo) ? "Active" : "NOT ACTIVE") . "\n";
    echo '</pre>';
    echo '</div>';
}

// Start output buffering to prevent header issues
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

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-md-8">
      <h3 class="mb-0 h4 font-weight-bolder">Space Dashboard</h3>
      <p class="mb-4">Review check-ins, mood entries, and therapy appointments to support student well-being</p>
    </div>
    <div class="row mt-1">

      <!-- My Check-ins Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-header p-2 ps-3">
            <div class="d-flex align-items-center">
              <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                <i class="material-symbols-outlined opacity-10">event_available</i>
              </div>
              <div>
                <p class="text-sm mb-0 text-capitalize">My Check-ins</p>
                <h4 class="mb-0"><?php echo $analytics->getUserTotalCheckins($_SESSION['user_id']); ?></h4>
              </div>
            </div>
          </div>
          <hr class="dark horizontal my-0">
          <div class="card-footer p-2 ps-3">
            <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+1 </span>from last week</p>
          </div>
        </div>
      </div>

      <!-- Community Post Card -->
      <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-header p-2 ps-3">
            <div class="d-flex align-items-center">
              <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                <i class="material-symbols-outlined opacity-10">stylus</i>
              </div>
              <div>
                <p class="text-sm mb-0 text-capitalize">Community Post</p>
                <h4 class="mb-0"><?php echo $analytics->getUserPostCount($_SESSION['user_id']); ?></h4>
              </div>
            </div>
          </div>
          <hr class="dark horizontal my-0">
          <div class="card-footer p-2 ps-3">
            <?php
            $postCount = $analytics->getUserPostCount($_SESSION['user_id']);
            $yesterdayCount = $analytics->getUserYesterdayPostCount($_SESSION['user_id']);
            
            // Calculate percentage change
            $percentChange = 0;
            if ($yesterdayCount > 0) {
                $percentChange = (($postCount - $yesterdayCount) / $yesterdayCount) * 100;
            }
            
            // Determine style based on change
            $changeClass = $percentChange >= 0 ? 'text-success' : 'text-danger';
            $changeSymbol = $percentChange >= 0 ? '+' : '';
            ?>
            <p class="mb-0 text-sm">
                <span class="<?php echo $changeClass; ?> font-weight-bolder">
                    <?php echo $changeSymbol . number_format($percentChange, 1) . '%'; ?>
                </span> 
                from yesterday
            </p>
          </div>
        </div>
      </div>
      <!-- Journal Entries Card -->
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-2 ps-3">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                            <i class="material-symbols-outlined opacity-10">book</i>
                        </div>
                        <div>
                            <p class="text-sm mb-0 text-capitalize">Journal Entries</p>
                            <?php
                            // Get total journal entries for current user only
                            $stmt = $pdo->prepare("
                                SELECT COUNT(*) as total 
                                FROM journal_entries 
                                WHERE srcode = ?
                            ");
                            $stmt->execute([$_SESSION['srcode']]);
                            $totalEntries = $stmt->fetch()['total'];
                            ?>
                            <h4 class="mb-0"><?php echo $totalEntries; ?></h4>
                        </div>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-2 ps-3">
                    <?php
                    // Get today's entries for current user only
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as today_count 
                        FROM journal_entries 
                        WHERE srcode = ? 
                        AND DATE(created_at) = CURDATE()
                    ");
                    $stmt->execute([$_SESSION['srcode']]);
                    $todayCount = $stmt->fetch()['today_count'];

                    echo $todayCount > 0 
                        ? "<p class='mb-0 text-sm'><span class='text-success font-weight-bolder'>+{$todayCount} </span>new entries today</p>"
                        : "<p class='mb-0 text-sm'><span class='text-secondary'>No new entries today</span></p>";
                    ?>
                </div>
            </div>
        </div>

      <!-- Sessions Card -->
      <div class="col-xl-3 col-sm-6">
        <div class="card">
          <div class="card-header p-2 ps-3">
            <div class="d-flex align-items-center">
              <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                <i class="material-symbols-outlined opacity-10">assignment_ind</i>
              </div>
              <div>
                <p class="text-sm mb-0 text-capitalize">Sessions</p>
                <h4 class="mb-0">
                  <?php 
                  // Get total sessions count
                  $sessions_query = "SELECT COUNT(*) as total 
                                   FROM therapy_sessions 
                                   WHERE srcode = ? 
                                   AND status IN ('completed', 'confirmed', 'pending')";
                  $stmt = $pdo->prepare($sessions_query);
                  $stmt->execute([$_SESSION['srcode']]);
                  $result = $stmt->fetch(PDO::FETCH_ASSOC);
                  echo $result['total'];
                  ?>
                </h4>
              </div>
            </div>
          </div>
          <hr class="dark horizontal my-0">
          <div class="card-footer p-2 ps-3">
            <?php
            // Get last month's sessions count
            $last_month_query = "SELECT COUNT(*) as last_month 
                                FROM therapy_sessions 
                                WHERE srcode = ? 
                                AND status IN ('completed', 'confirmed', 'pending')
                                AND MONTH(session_date) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
            $stmt = $pdo->prepare($last_month_query);
            $stmt->execute([$_SESSION['srcode']]);
            $last_month = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get this month's sessions count
            $this_month_query = "SELECT COUNT(*) as this_month 
                                FROM therapy_sessions 
                                WHERE srcode = ? 
                                AND status IN ('completed', 'confirmed', 'pending')
                                AND MONTH(session_date) = MONTH(CURRENT_DATE)";
            $stmt = $pdo->prepare($this_month_query);
            $stmt->execute([$_SESSION['srcode']]);
            $this_month = $stmt->fetch(PDO::FETCH_ASSOC);

            // Calculate percentage change
            $percent_change = 0;
            if ($last_month['last_month'] > 0) {
                $percent_change = (($this_month['this_month'] - $last_month['last_month']) / $last_month['last_month']) * 100;
            }

            $change_class = $percent_change >= 0 ? 'text-success' : 'text-danger';
            $change_symbol = $percent_change >= 0 ? '+' : '';
            ?>
            <p class="mb-0 text-sm">
                <span class="<?php echo $change_class; ?> font-weight-bolder">
                    <?php echo $change_symbol . number_format(abs($percent_change), 1) . '%'; ?>
                </span>
                than last month
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid py-auto">
      <div class="row align-items-stretch">
      <div class="col-lg-12">
          <div class="row">
            <div class="col-lg-4 col-md-7 d-flex">
            <div class="card bg-transparent shadow-xl"  style="height: 133px;">
    <div class="overflow-hidden position-relative border-radius-xl">
        <img src="../../assets/img/illustrations/pattern-tree.svg" class="position-absolute opacity-2 start-0 top-0 w-100 z-index-1 h-100" alt="pattern-tree">
        <span class="mask bg-gradient-dark opacity-10"></span>
        <div class="card-body position-relative z-index-1 p-3">
            <!-- Profile Header -->
            <div class="row mb-2">
                <div class="col-3">
                    <?php
                    $profile_picture_url = "../../admin_operations/get_profile_picture.php?user_id=" . $_SESSION['user_id'] . "&user_type=student";
                    ?>
                    <img src="<?php echo $profile_picture_url; ?>" 
                         alt="profile" 
                         class="border-radius-lg shadow-sm w-100"
                         onerror="this.src='../../assets/img/default-avatar.png';"
                         id="studentProfilePic"
                         style="cursor: pointer; max-width: 70px;">
                </div>
                <div class="col-6">
                    <h6 class="text-white mb-0" style="font-size: 0.9rem;">
                        <?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?>
                    </h6>
                    <p class="text-white opacity-8 mb-0" style="font-size: 0.75rem;">
                        @<?php echo strtolower(htmlspecialchars($_SESSION['firstname'])); ?>
                    </p>
                </div>
                <div class="col-3 text-end">
                    <img class="w-50" src="../../assets/img/logo-space.png" alt="logo">
                </div>
            </div>

            <!-- Profile Details -->
            <div class="row mt-2">
                <div class="col-6">
                    <p class="text-white opacity-8 mb-0" style="font-size: 0.7rem;">SR-Code</p>
                    <h6 class="text-white mb-0" style="font-size: 0.8rem;">
                        <?php echo htmlspecialchars($_SESSION['user_id']); ?>
                    </h6>
                </div>
                <div class="col-6">
                    <p class="text-white opacity-8 mb-0" style="font-size: 0.7rem;">Status</p>
                    <h6 class="text-white mb-0" style="font-size: 0.8rem;">
                        <?php
                        try {
                            $status_stmt = $pdo->prepare("SELECT status FROM students WHERE srcode = ?");
                            $status_stmt->execute([$_SESSION['user_id']]);
                            $status = $status_stmt->fetchColumn();
                            echo htmlspecialchars(ucfirst($status ?? 'Active'));
                        } catch (PDOException $e) {
                            error_log("Error fetching student status: " . $e->getMessage());
                            echo 'Active';
                        }
                        ?>
                    </h6>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>

            <div class="col-lg-4 col-md-4 mb-3">
    <div class="card" style="height: 133px;">
        <div class="overflow-hidden position-relative border-radius-xl">
            <div class="card-body position-relative z-index-1 p-2">
                <!-- Header with Icon -->
                <div class="row mb-2">
                    <div class="col-2">
                        <div class="icon icon-shape bg-gradient-dark shadow text-center border-radius-lg me-3">
                            <i class="material-symbols-rounded text-white opacity-10">event_available</i>
                        </div>
                    </div>
                    <div class="col-8">
                    <h6 class="mb-0 text-capitalize font-weight-bold">Upcoming Session</h6>
                    </div>
                </div>

                <!-- Session Details -->
                <?php
                try {
                    $session_stmt = $pdo->prepare("
                        SELECT session_date, session_type, status 
                        FROM therapy_sessions 
                        WHERE srcode = ? 
                        AND session_date >= CURRENT_DATE()
                        AND status IN ('confirmed', 'pending')
                        ORDER BY session_date ASC 
                        LIMIT 1
                    ");
                    $session_stmt->execute([$_SESSION['user_id']]);
                    $next_session = $session_stmt->fetch();

                    if ($next_session) {
                        $session_date = new DateTime($next_session['session_date']);
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <p class="text-dark mb-0" style="font-size: 0.85rem;">
                                    <?php echo $session_date->format('F j, Y - g:i A'); ?>
                                </p>
                                <p class="text-muted mb-0" style="font-size: 0.75rem;">
                                    <?php echo ucfirst($next_session['session_type']); ?> Session
                                    <span class="badge bg-<?php echo $next_session['status'] === 'confirmed' ? 'success' : 'warning'; ?> ms-2">
                                        <?php echo ucfirst($next_session['status']); ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                        <?php
                    } else {
                        echo '<p class="text-muted mb-0">No upcoming sessions scheduled</p>';
                    }
                } catch (PDOException $e) {
                    error_log("Error fetching next session: " . $e->getMessage());
                    echo '<p class="text-muted mb-0">Unable to load session information</p>';
                }
                ?>
            </div>
        </div>
    </div>
            </div>

            <div class="col-lg-4 col-md-4 mb-1">
    <div class="card" style="height: 133px;">
        <div class="overflow-hidden position-relative border-radius-xl">
            <div class="card-body position-relative z-index-1 p-2">
                <!-- Header with Icon -->
                <div class="row mb-2">
                    <div class="col-2">
                        <div class="icon icon-shape bg-gradient-dark shadow text-center border-radius-lg me-3">
                            <i class="material-symbols-rounded text-white opacity-10">schedule</i>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="mb-0 text-capitalize font-weight-bold">Current Time</h6>
                        <p class="text-muted mb-0" style="font-size: 0.75rem;">Daily Schedule</p>
                    </div>
                </div>

                <!-- Time Details -->
                <div class="row">
                    <div class="col-12">
                        <p class="text-dark mb-0" style="font-size: 0.85rem;">
                            <?php echo date('F j, Y'); ?>
                        </p>
                        <div class="d-flex align-items-center">
                            <span class="text-muted" style="font-size: 0.75rem;">
                                <?php echo date('g:i A'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
            </div>
          </div>

          </div>
      </div>
</div>

<div class="container-fluid py-auto">
    <div class="row align-items-stretch">
      <!-- Website Views Card -->
      <div class="col-lg-4 col-md-7 d-flex">
                  <div class="card w-100 my-3">
                    <div class="card-header p-3 pb-0">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                          <i class="material-symbols-rounded text-white opacity-10">activity_zone</i>
                        </div>
                        <div class="ms-3">
                          <h6 class="mb-0">Space Interactions</h6>
                          <p class="text-sm mb-0">Weekly Activity</p>
                        </div>
                      </div>
                    </div>
                      <div class="card-body p-3">
                          <div class="chart-container" style="position: relative; height:170px; width:100%">
                              <canvas id="chart-bars" style="display: block; width: 100%; height: 100%;"></canvas>
                          </div>
                          <hr class="dark horizontal">
                          <div class="d-flex justify-content-between align-items-center">
                              <div class="d-flex align-items-center">
                                  <i class="material-symbols-rounded text-sm my-auto me-1">update</i>
                                  <p class="mb-0 text-sm" id="last-activity">
                                      <?php
                                      try {
                                          // Get activities for the last 7 days
                                          $query = "SELECT 
                                              DATE_FORMAT(created_at, '%a') as day,
                                              COUNT(*) as count,
                                              created_at
                                              FROM activity_logs 
                                              WHERE srcode = :user_id 
                                              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                                              AND action IN (
                                                  'Logged In',
                                                  'Liked Quote',
                                                  'Refreshed Quote',
                                                  'Updated Profile',
                                                  'Logged Mood',
                                                  'Viewed Resource'
                                              )
                                              GROUP BY DATE(created_at), day
                                              ORDER BY created_at ASC";
                                              
                                          $stmt = $pdo->prepare($query);
                                          $stmt->execute(['user_id' => $_SESSION['user_id']]);
                                          $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                          
                                          // Get the latest activity for "time ago" display
                                          $latestQuery = "SELECT MAX(created_at) as latest_activity 
                                                        FROM activity_logs 
                                                        WHERE srcode = :user_id";
                                          $latestStmt = $pdo->prepare($latestQuery);
                                          $latestStmt->execute(['user_id' => $_SESSION['user_id']]);
                                          $latest = $latestStmt->fetch(PDO::FETCH_ASSOC);

                                          // Format the "time ago" text
                                          if ($latest && $latest['latest_activity']) {
                                              $timeAgo = time() - strtotime($latest['latest_activity']);
                                              if ($timeAgo < 3600) {
                                                  echo floor($timeAgo / 60) . " minutes ago";
                                              } elseif ($timeAgo < 86400) {
                                                  echo floor($timeAgo / 3600) . " hours ago";
                                              } else {
                                                  echo floor($timeAgo / 86400) . " days ago";
                                              }
                                          } else {
                                              echo "No recent activity";
                                          }

                                          // Prepare data for chart
                                          $chartData = [
                                              'labels' => [],
                                              'counts' => []
                                          ];

                                          // Get last 7 days including today
                                          $days = [];
                                          for ($i = 6; $i >= 0; $i--) {
                                              $date = date('Y-m-d', strtotime("-$i days"));
                                              $dayName = date('D', strtotime("-$i days"));
                                              $days[$date] = ['day' => $dayName, 'count' => 0];
                                          }

                                          // Fill in actual counts
                                          foreach ($activities as $activity) {
                                              $activityDate = date('Y-m-d', strtotime($activity['created_at']));
                                              if (isset($days[$activityDate])) {
                                                  $days[$activityDate]['count'] = (int)$activity['count'];
                                              }
                                          }

                                          // Prepare final arrays for chart
                                          foreach ($days as $day) {
                                              $chartData['labels'][] = $day['day'];
                                              $chartData['counts'][] = $day['count'];
                                          }

                                          // Pass data to JavaScript
                                          echo "<script>
                                              var activityData = {
                                                  labels: " . json_encode($chartData['labels']) . ",
                                                  counts: " . json_encode($chartData['counts']) . "
                                              };
                                          </script>";

                                      } catch (PDOException $e) {
                                          error_log("Error fetching activity data: " . $e->getMessage());
                                          echo "Error loading activity data";
                                      }
                                      ?>
                                  </p>
                              </div>
                              <p class="mb-0 text-sm text-muted">
                                  <i class="material-symbols-rounded text-sm">refresh</i>
                                  Last 7 days
                              </p>
                          </div>
                      </div>
                  </div>
      </div>
      <!-- Today's Quote Card -->
      <div class="col-lg-4 col-md-7 d-flex my-3">
          <div class="card w-100" style="min-height: 250px;">
                    <div class="card-header p-3 pb-0">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                          <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">format_quote</i>
                        </div>
                        <div class="ms-3">
                          <h6 class="mb-0">Today's Quote</h6>
                          <p class="text-sm mb-0 text-capitalize font-weight-normal">Daily Inspiration</p>
                        </div>
                      </div>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-center align-items-center">
                      <?php
                      try {
                          $today = date('Y-m-d');
                          $seed = abs(crc32($today . $_SESSION['user_id']));
                          mt_srand($seed);
                          
                          $query = "SELECT content, author FROM quotes ORDER BY RAND(" . $seed . ") LIMIT 1";
                          $stmt = $pdo->prepare($query);
                          $stmt->execute();
                          
                          if ($quote = $stmt->fetch()) {
                              ?>
                              <div class="text-center px-4">
                                  <p class="quote-content mb-3" style="font-size: 1.15rem; line-height: 1.5; font-weight: 500;">
                                      "<?php echo htmlspecialchars($quote['content']); ?>"
                                  </p>
                                  <p class="quote-author text-sm mb-0" style="font-style: italic; color: #666;">
                                      - <?php echo htmlspecialchars($quote['author']); ?>
                                  </p>
                              </div>
                              <?php
                          }
                      } catch (Exception $e) {
                          error_log("Error loading quote: " . $e->getMessage());
                          ?>
                          <div class="text-center">
                              <p class="text-muted mb-0">Error loading quote. Please try again later.</p>
                          </div>
                      <?php } ?>
                    </div>
                  </div>
      </div>
      <!-- Today's Mood Card -->
      <div class="col-lg-4 col-md-7 d-flex my-3">
          <div class="card w-100">
            <div class="card-header p-3 pb-0">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                          <i class="material-symbols-rounded text-white opacity-10">mood</i>
                        </div>
                        <div class="ms-3">
                          <h6 class="mb-0">Today's Mood</h6>
                          <p class="text-sm mb-0 text-capitalize font-weight-normal"><?php echo date('F j, Y'); ?></p>
                        </div>
                      </div>
                    </div>
                    <div class="card-body p-3">
                      <?php
                      try {
                        $tableExists = $pdo->query("SHOW TABLES LIKE 'moodlog'")->rowCount() > 0;
                        
                        if ($tableExists) {
                          $query = "SELECT TRIM(selected_emoji) as selected_emoji 
                                    FROM moodlog 
                                    WHERE srcode = :srcode 
                                    AND DATE(log_date) = CURDATE() 
                                    ORDER BY log_date DESC 
                                    LIMIT 5";
                          
                          $stmt = $pdo->prepare($query);
                          $stmt->execute(['srcode' => $_SESSION['user_id']]);
                          $moods = $stmt->fetchAll(PDO::FETCH_ASSOC);

                          if (!empty($moods)): ?>
                            <div class="d-flex flex-column align-items-center justify-content-center" style="min-height: 150px;">
                              <?php foreach($moods as $mood): ?>
                                <div class="mb-2">
                                  <span style="font-size: 32px;"><?php echo str_replace(',', '', $mood['selected_emoji']); ?></span>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          <?php else: ?>
                            <div class="text-center text-muted" style="min-height: 150px;">
                              <p class="mb-3">No mood logged today</p>
                              <a href="moodlog.php" class="btn btn-sm btn-outline-primary">Log Your Mood</a>
                            </div>
                          <?php endif;
                        } else {
                          echo '<div class="text-center text-muted" style="min-height: 150px;">';
                          echo '<p class="mb-3">Mood tracking feature is not yet set up</p>';
                          echo '<a href="moodlog.php" class="btn btn-sm btn-outline-primary">Log Your First Mood</a>';
                          echo '</div>';
                        }
                      } catch (PDOException $e) {
                        echo '<div class="text-center text-muted" style="min-height: 150px;">';
                        echo '<p>Error accessing mood data</p>';
                        echo '</div>';
                        error_log("Mood tracking error: " . $e->getMessage());
                      }
                      ?>
                    </div>
                    <?php if (!empty($moods)): ?>
                    <div class="card-footer p-3 text-center">
                      <a href="moodtracker.php" class="btn btn-sm btn-primary">See More</a>
                    </div>
                    <?php endif; ?>
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
    .then(response => response.json())
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

</body>
</html>
