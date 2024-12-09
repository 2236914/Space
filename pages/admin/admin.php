<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../signin.php');
    exit();
}

// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/admin_navigation_components.php';

// Get total users count
try {
    // Test connection first
    $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    
    // Get counts from all tables
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $therapistCount = $pdo->query("SELECT COUNT(*) FROM therapists")->fetchColumn();
    $studentCount = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    
    $totalUsers = $adminCount + $therapistCount + $studentCount;
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $totalUsers = 0;
}
try {
    // Count all therapist applications
    $applications_stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE 
                WHEN status = 'pending' THEN 1 
                ELSE 0 
            END) as pending,
            SUM(CASE 
                WHEN application_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 
                ELSE 0 
            END) as new_apps
        FROM therapist_applications
    ");
    $applications = $applications_stmt->fetch(PDO::FETCH_ASSOC);

    $total_applications = $applications['total'] ?? 0;
    $pending_applications = $applications['pending'] ?? 0;
    $new_applications = $applications['new_apps'] ?? 0;

} catch (PDOException $e) {
    error_log("Error counting therapist applications: " . $e->getMessage());
    $total_applications = 0;
    $pending_applications = 0;
    $new_applications = 0;
} 

// Get total support messages and new messages count
try {
    // Count all support messages (both from students and therapists)
    $total_messages_stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM support_messages) +
            (SELECT COUNT(*) FROM therapist_support_messages)
        AS total_messages
    ");
    $total_messages = $total_messages_stmt->fetchColumn();

    // Count new messages (created in the last 24 hours)
    $new_messages_stmt = $pdo->query("
        SELECT 
            (SELECT COUNT(*) 
             FROM support_messages 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) +
            (SELECT COUNT(*) 
             FROM therapist_support_messages 
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
        AS new_messages
    ");
    $new_messages = $new_messages_stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error counting support messages: " . $e->getMessage());
    $total_messages = 0;
    $new_messages = 0;
}

// Get reports statistics
try {
    // Get reports statistics
    $reports_stats = $pdo->query("
        SELECT 
            COUNT(*) as total_reports,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
            SUM(CASE 
                WHEN created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
                THEN 1 ELSE 0 END) as new_reports
        FROM reports
    ")->fetch(PDO::FETCH_ASSOC);

    $total_reports = $reports_stats['total_reports'] ?? 0;
    $pending_reports = $reports_stats['pending_reports'] ?? 0;
    $new_reports = $reports_stats['new_reports'] ?? 0;

} catch (PDOException $e) {
    error_log("Error fetching reports statistics: " . $e->getMessage());
    $total_reports = 0;
    $pending_reports = 0;
    $new_reports = 0;
}

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get current page info for breadcrumb
$current_info = [
    'parent' => 'Admin',
    'title' => 'Dashboard'
];

try {
    // Get session statistics for the last 7 days
    $session_stats = $pdo->query("
        SELECT 
            DATE(session_date) as date,
            COUNT(*) as total_sessions,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM therapy_sessions
        WHERE session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(session_date)
        ORDER BY date ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data for chart
    $dates = [];
    $completed_sessions = [];
    $cancelled_sessions = [];

    foreach ($session_stats as $stat) {
        $dates[] = date('M d', strtotime($stat['date']));
        $completed_sessions[] = $stat['completed'];
        $cancelled_sessions[] = $stat['cancelled'];
    }

} catch (PDOException $e) {
    error_log("Error fetching session statistics: " . $e->getMessage());
}

// At the top of your file, add this query to get pending counts
try {
    $pending_messages_count = $pdo->query("
        SELECT COUNT(*) 
        FROM contacts 
        WHERE status = 'pending'"
    )->fetchColumn();
} catch (PDOException $e) {
    $pending_messages_count = 0;
}

// Initialize analytics
$analytics = new DashboardAnalytics($pdo);

// Get all admin dashboard data
$overview_stats = $analytics->getAdminOverviewStats();
$system_metrics = $analytics->getSystemPerformanceMetrics();
$therapist_stats = $analytics->getTherapistPerformanceStats();
$student_metrics = $analytics->getStudentEngagementMetrics();

// Get metrics with error handling
$social_metrics = $analytics->getStudentSocialMetrics() ?: [
    'post_activity' => [],
    'interaction_metrics' => [],
    'active_users' => []
];

$session_analytics = $analytics->getSessionAnalytics() ?: [
    'completion_rates' => [],
    'avg_duration' => [],
    'session_types' => []
];

$predictive_insights = $analytics->getPredictiveInsights() ?: [
    'peak_activity_times' => [],
    'at_risk_students' => []
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Admin
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
  
</head>
<body class="g-sidenav-show bg-gray-200">
<!-- Admin Aside -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="admin.php">
            <img src="../../assets/img/logo-space.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
            <span class="ms-1 font-weight-bold lead text-dark">SPACE</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">

    <!-- Main Navigation -->
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Admin Profile -->
            <li class="nav-item mb-2 mt-0">
                <a href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav">
                    <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                         class="avatar"
                         onerror="this.src='../../assets/img/default-avatar.png';">
                         <span class="nav-link-text ms-2 ps-1"> <?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?>
                        </span>
                </a>
            </li>
            <hr class="horizontal dark mt-0">

            <!-- Menu Items -->
            <?php foreach ($admin_menu_items as $link => $item): ?>
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
                            <ul class="nav nav-sm flex-column">
                                <?php foreach ($item['submenu'] as $sublink => $subitem): ?>
                                    <li class="nav-item">
                                        <a class="nav-link text-dark <?= $current_page == $sublink ? 'active' : ''; ?>" 
                                           href="<?= $sublink ?>">
                                            <span class="sidenav-mini-icon"><?= $subitem['mini'] ?></span>
                                            <span class="sidenav-normal ms-1"><?= $subitem['text'] ?></span>
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
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Sign Out Button (with proper spacing) -->
    <div class="mt-3 w-100 px-3 mb-3">
        <button type="button" class="btn bg-gradient-primary w-100" onclick="handleSignOut()">
            <i class="material-symbols-rounded opacity-5 me-2">logout</i>
            <span class="nav-link-text">Sign Out</span>
        </button>
    </div>
</aside>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-2">
        <!-- Desktop Toggler -->
        <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconSidenavDesktop">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                </div>
            </a>
        </div>

        <!-- Mobile Toggler -->
        <div class="d-xl-none">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                    <i class="sidenav-toggler-line"></i>
                </div>
            </a>
        </div>

        <!-- Breadcrumb -->
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
    </div>
</nav>
    <!-- End Navbar -->
    <div class="container-fluid py-2">
        <div class="row">
            <div class="ms-3">
                <h3 class="mb-0 h4 font-weight-bolder">Admin Dashboard</h3>
                <p class="mb-4">Monitor therapist applications, evaluations, and system activities</p>
            </div>

            <!-- Space Total Number of Users-->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">psychology</i>
                            </div>
                            <div>
                            <p class="text-sm mb-0 text-capitalize">Space Users</p>
                            <h4 class="mb-0">
                                <?php 
                                echo "<!-- Debug: Displaying total: $totalUsers -->";
                                echo htmlspecialchars($totalUsers ?? 0); 
                                ?>
                            </h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+3 </span>new today</p>
                    </div>
                </div>
            </div>

            <!-- Support Messages Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-success shadow-success text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">support_agent</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Support Messages</p>
                                <h4 class="mb-0"><?php echo number_format($total_messages); ?></h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm">
                            <?php if ($new_messages > 0): ?>
                                <span class="text-success font-weight-bolder">+<?php echo $new_messages; ?></span> new messages
                            <?php else: ?>
                                <span class="text-muted">No new messages</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Therapist Applications Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-info shadow-info text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">assignment_ind</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Therapist Applications</p>
                                <h4 class="mb-0 d-flex align-items-center">
                                    <?php echo number_format($total_applications); ?>
                                    <?php if ($pending_applications > 0): ?>
                                        <span class="badge bg-gradient-warning ms-2" 
                                              style="font-size: 0.5em; padding: 0.35em 0.65em;">
                                            <?php echo $pending_applications; ?> pending
                                        </span>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm">
                            <?php if ($new_applications > 0): ?>
                                <span class="text-success font-weight-bolder">+<?php echo $new_applications; ?></span> new applications this week
                            <?php else: ?>
                                <span class="text-muted">No new applications this week</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Reports Card -->
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-warning shadow-warning text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">flag</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Reports</p>
                                <h4 class="mb-0 d-flex align-items-center">
                                    <?php echo number_format($total_reports); ?>
                                    <?php if ($pending_reports > 0): ?>
                                        <span class="badge bg-gradient-danger ms-2" 
                                              style="font-size: 0.5em; padding: 0.35em 0.65em;">
                                            <?php echo $pending_reports; ?> pending
                                        </span>
                                    <?php endif; ?>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm">
                            <?php if ($new_reports > 0): ?>
                                <span class="text-danger font-weight-bolder">+<?php echo $new_reports; ?></span> new reports today
                            <?php else: ?>
                                <span class="text-muted">No new reports today</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-1">
      <div class="row">
        <div class="col-lg-8 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-0" style="color: var(--bs-1stgreen);">Student Engagement</h6>
                  <p class="text-sm" style="color: var(--text-color-light);">Monthly Activity Overview</p>
                </div>
              </div>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex justify-content-between">
                <div>
                  <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                  <span class="text-sm" style="color: var(--text-color-light);">Updated today</span>
                </div>
                <div class="text-sm">
                  Peak Activity: 
                  <?php 
                      if (!empty($predictive_insights['peak_activity_times'])) {
                          $peak_times = $predictive_insights['peak_activity_times'][0];
                          echo $peak_times['day_of_week'] . 's at ' . date('ga', strtotime($peak_times['hour_of_day'] . ':00'));
                      } else {
                          echo "No data available";
                      }
                  ?>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-0" style="color: var(--bs-1stgreen);">Session Statistics</h6>
              <p class="text-sm" style="color: var(--text-color-light);">Weekly Session Completion Rate</p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="chart-line" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex">
                <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                <p class="mb-0 text-sm" style="color: var(--text-color-light);">Updated 4 min ago</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
<!-- Admin Trends and Insights Section -->
<div class="container-fluid py-1">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3" style="color: var(--bs-1stgreen);">System Insights & Trends</h6>
                    <div class="row">
                        <!-- User Growth -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">User Growth Rate</h6>
                                <p class="mb-0 h5">
                                    <?php 
                                    $student_growth = $overview_stats['total_students'];
                                    $growth_class = $student_growth >= 0 ? 'text-success' : 'text-danger';
                                    $growth_icon = $student_growth >= 0 ? '↑' : '↓';
                                    echo "<span class='{$growth_class}'>{$growth_icon} {$student_growth}%</span>";
                                    ?>
                                </p>
                                <small class="text-muted">Active students this month</small>
                            </div>
                        </div>
                        
                        <!-- System Performance -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">System Performance</h6>
                                <p class="mb-0">
                                    <?php 
                                    if (!empty($system_metrics['response_time'])) {
                                        $avg_response = array_sum(array_column($system_metrics['response_time'], 'avg_response')) / count($system_metrics['response_time']);
                                        echo number_format($avg_response, 2) . "ms";
                                    } else {
                                        echo "No data available";
                                    }
                                    ?>
                                </p>
                                <small class="text-muted">Average response time</small>
                            </div>
                        </div>
                        
                        <!-- Therapist Performance -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Therapist Performance</h6>
                                <p class="mb-0">
                                    <?php 
                                    if (!empty($therapist_stats)) {
                                        $avg_rating = array_sum(array_column($therapist_stats, 'avg_rating')) / count($therapist_stats);
                                        echo number_format($avg_rating, 1) . " / 5.0";
                                    } else {
                                        echo "No ratings yet";
                                    }
                                    ?>
                                </p>
                                <small class="text-muted">Average therapist rating</small>
                            </div>
                        </div>
                        
                        <!-- Student Engagement -->
                        <div class="col-md-3">
                            <div class="insight-box">
                                <h6 class="text-sm mb-1">Student Engagement</h6>
                                <p class="mb-0">
                                    <?php 
                                    if (isset($student_metrics['session_attendance'])) {
                                        echo "<span class='text-primary'>" . 
                                             $student_metrics['session_attendance'] . "%</span>";
                                    } else {
                                        echo "No data available";
                                    }
                                    ?>
                                </p>
                                <small class="text-muted">Session attendance rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>

    <!-- Core JS Files -->
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets/js/material-dashboard.min.js"></script>
    <script src="../../assets/js/sign-out.js"></script>
    <script src="../../assets/js/plugins/chartjs.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Search functionality
        function searchMenu() {
            // Implement search functionality here
        }

        // Initialize perfect scrollbar
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }

        document.addEventListener("DOMContentLoaded", function() {
            var ctx = document.getElementById('sessionsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dates ?? []); ?>,
                    datasets: [
                        {
                            label: 'Completed',
                            data: <?php echo json_encode($completed_sessions ?? []); ?>,
                            backgroundColor: 'rgba(66, 186, 150, 0.8)',
                            borderColor: 'rgba(66, 186, 150, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Cancelled',
                            data: <?php echo json_encode($cancelled_sessions ?? []); ?>,
                            backgroundColor: 'rgba(241, 85, 108, 0.8)',
                            borderColor: 'rgba(241, 85, 108, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });

        // Bar Chart - Student Engagement
        var ctx = document.getElementById("chart-bars").getContext("2d");

        // Debug the data being passed to the chart
        console.log('Engagement Metrics:', {
            labels: <?php echo json_encode(array_column($engagement_metrics['post_activity'] ?? [], 'month')); ?>,
            activeUsers: <?php echo json_encode(array_column($engagement_metrics['active_users'] ?? [], 'active_users')); ?>,
            completedSessions: <?php echo json_encode(array_column($session_analytics['completion_rates'] ?? [], 'completed_sessions')); ?>
        });

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: <?php echo json_encode(array_column($engagement_metrics['post_activity'] ?? [], 'month') ?: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun']); ?>,
                datasets: [{
                    label: "Active Students",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "#3c6454",
                    data: <?php echo json_encode(array_column($engagement_metrics['active_users'] ?? [], 'active_users') ?: [0, 0, 0, 0, 0, 0]); ?>,
                    maxBarThickness: 6
                }, {
                    label: "Completed Sessions",
                    tension: 0.4,
                    borderWidth: 0,
                    borderRadius: 4,
                    borderSkipped: false,
                    backgroundColor: "#93bdaa",
                    data: <?php echo json_encode(array_column($session_analytics['completion_rates'] ?? [], 'completed_sessions') ?: [0, 0, 0, 0, 0, 0]); ?>,
                    maxBarThickness: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 11,
                                family: "Inter"
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false
                        }
                    }
                }
            }
        });

        // Line Chart - Session Statistics
        var ctx2 = document.getElementById("chart-line").getContext("2d");
        new Chart(ctx2, {
            type: "line",
            data: {
                labels: <?php echo json_encode($dates); ?>,
                datasets: [{
                    label: "Completed",
                    tension: 0.4,
                    borderWidth: 2,
                    borderColor: "#3c6454",
                    backgroundColor: "rgba(60, 100, 84, 0.2)",
                    fill: true,
                    data: <?php echo json_encode($completed_sessions); ?>,
                    maxBarThickness: 6
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        grid: {
                            drawBorder: false,
                            display: true,
                            drawOnChartArea: true,
                            drawTicks: false,
                            borderDash: [5, 5],
                            color: '#c1c4ce5c'
                        },
                        ticks: {
                            display: true,
                            padding: 10,
                            color: '#9ca2b7',
                            font: {
                                size: 11,
                                family: "Inter",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                    x: {
                        grid: {
                            drawBorder: false,
                            display: false,
                            drawOnChartArea: false,
                            drawTicks: false,
                            borderDash: [5, 5]
                        },
                        ticks: {
                            display: true,
                            color: '#9ca2b7',
                            padding: 10,
                            font: {
                                size: 11,
                                family: "Inter",
                                style: 'normal',
                                lineHeight: 2
                            },
                        }
                    },
                },
            },
        });
    </script>
</body>
</html>