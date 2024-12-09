<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../signin.php");
    exit();
}

// Required files
require_once '../../configs/config.php';
require_once '../../includes/therapist_navigation_components.php';
require_once '../../admin_operations/dashboard_analytics.php';

// Get therapist ID from session
$therapist_id = $_SESSION['user_id'];

// Initialize arrays to store dashboard data
$dashboard_data = [
    'active_clients' => ['count' => 0, 'new' => 0],
    'upcoming_sessions' => ['count' => 0, 'next' => null],
    'completed_sessions' => ['total' => 0, 'weekly' => 0],
    'ratings' => ['average' => 0, 'total' => 0, 'positive' => 0]
];

try {
    // Create database connection
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_error) {
        throw new Exception("Connection failed: " . $db->connect_error);
    }

    // Cache key for this therapist's dashboard
    $cache_key = "therapist_dashboard_" . $therapist_id;
    $cache_duration = 300; // 5 minutes cache

    // Check if we have cached data
    if (isset($_SESSION[$cache_key]) && 
        (time() - $_SESSION[$cache_key]['timestamp'] < $cache_duration)) {
        $dashboard_data = $_SESSION[$cache_key]['data'];
    } else {
        // Active Clients Query
        $active_clients_query = "SELECT 
            COUNT(DISTINCT s.srcode) as active_clients,
            COUNT(DISTINCT CASE 
                WHEN s.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK) 
                THEN s.srcode 
            END) as new_clients
            FROM therapy_sessions s 
            WHERE s.therapist_id = ? 
            AND s.status IN ('pending', 'confirmed')";
        
        if ($stmt = $db->prepare($active_clients_query)) {
            $stmt->bind_param('s', $therapist_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $dashboard_data['active_clients'] = [
                'count' => $result['active_clients'],
                'new' => $result['new_clients']
            ];
            $stmt->close();
        }

        // Upcoming Sessions Query
        $upcoming_sessions_query = "SELECT 
            COUNT(*) as upcoming_count,
            MIN(CONCAT(session_date, ' ', session_time)) as next_session
            FROM therapy_sessions 
            WHERE therapist_id = ? 
            AND status = 'confirmed' 
            AND CONCAT(session_date, ' ', session_time) > NOW()";
        
        if ($stmt = $db->prepare($upcoming_sessions_query)) {
            $stmt->bind_param('s', $therapist_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $dashboard_data['upcoming_sessions'] = [
                'count' => $result['upcoming_count'],
                'next' => $result['next_session']
            ];
            $stmt->close();
        }

        // Completed Sessions Query
        $completed_sessions_query = "SELECT 
            COUNT(*) as total_completed,
            COUNT(CASE 
                WHEN session_date >= DATE_SUB(CURDATE(), INTERVAL 1 WEEK) 
                THEN 1 
            END) as completed_this_week
            FROM therapy_sessions 
            WHERE therapist_id = ? 
            AND status = 'completed'";
        
        if ($stmt = $db->prepare($completed_sessions_query)) {
            $stmt->bind_param('s', $therapist_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $dashboard_data['completed_sessions'] = [
                'total' => $result['total_completed'],
                'weekly' => $result['completed_this_week']
            ];
            $stmt->close();
        }

        // Average Rating Query
        $rating_query = "SELECT 
            ROUND(AVG(sf.rating), 1) as avg_rating,
            COUNT(*) as total_reviews,
            ROUND((COUNT(CASE WHEN sf.rating >= 4 THEN 1 END) / COUNT(*)) * 100) as positive_percentage
            FROM therapy_sessions ts
            JOIN student_feedback sf ON ts.session_id = sf.session_id
            WHERE ts.therapist_id = ?";
        
        if ($stmt = $db->prepare($rating_query)) {
            $stmt->bind_param('s', $therapist_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $dashboard_data['ratings'] = [
                'average' => $result['avg_rating'] ?? 0,
                'total' => $result['total_reviews'] ?? 0,
                'positive' => $result['positive_percentage'] ?? 0
            ];
            $stmt->close();
        }

        // Cache the results
        $_SESSION[$cache_key] = [
            'timestamp' => time(),
            'data' => $dashboard_data
        ];
    }

    // Initialize analytics
    $analytics = new DashboardAnalytics($pdo);

    // Get all chart data
    $monthly_stats = $analytics->getTherapistMonthlyStats($therapist_id);
    $weekly_completion_rates = $analytics->getTherapistWeeklyCompletion($therapist_id);
    $peak_activity = $analytics->getTherapistPeakActivity($therapist_id);
    $session_trends = $analytics->getTherapistTrends($therapist_id);

    // Now close the database connection
    $db->close();

} catch (Exception $e) {
    // Log the error
    error_log("Dashboard Error: " . $e->getMessage());
    // Set a session flash message
    $_SESSION['error'] = "Unable to load dashboard data. Please try again later.";
    
    // Initialize empty data if there's an error
    $monthly_stats = null;
    $weekly_completion_rates = null;
    $peak_activity = "Not available";
    $session_trends = [
        'growth_rate' => 0,
        'common_times' => [],
        'cancellation_patterns' => []
    ];
}

// Function to format time until next session
function formatTimeUntilNext($next_session) {
    if (!$next_session) return "No upcoming sessions";
    
    $next = strtotime($next_session);
    $now = time();
    $diff = $next - $now;
    
    if ($diff < 3600) { // Less than 1 hour
        $minutes = ceil($diff / 60);
        return "Next session in {$minutes} minutes";
    } else if ($diff < 86400) { // Less than 24 hours
        $hours = ceil($diff / 3600);
        return "Next session in {$hours} hours";
    } else {
        $days = ceil($diff / 86400);
        return "Next session in {$days} days";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Space</title>
    <!-- Fonts and icons -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <!-- Main CSS -->
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <link href="../../assets/css/navigation.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
    <style>
    .insight-box {
        padding: 15px;
        border-radius: 10px;
        background-color: rgba(60, 100, 84, 0.05);
        height: 100%;
    }

    .insight-box h6 {
        color: var(--bs-1stgreen);
        font-weight: 600;
    }

    .insight-box p {
        color: var(--text-color-light);
    }

    .text-success {
        color: #2dce89 !important;
    }

    .text-danger {
        color: #f5365c !important;
    }
    </style>
</head>

<body class="g-sidenav-show  bg-gray-200">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="therapist.php">
            <img src="../../assets/img/logo-space.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
            <span class="ms-1 font-weight-bold lead text-dark">SPACE</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">

    <!-- Main Navigation -->
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <!-- Therapist Profile -->
            <li class="nav-item mb-2 mt-0">
                <a href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav">
                    <img src="../../assets/img/default-avatar.png" class="avatar">
                    <span class="nav-link-text ms-2 ps-1"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></span>
                </a>
            </li>
            <hr class="horizontal dark mt-0">

            <!-- Menu Items -->
            <?php foreach ($therapist_menu_items as $link => $item): ?>
                <li class="nav-item">
                    <a class="nav-link text-dark <?php echo $current_page == $link ? 'active' : ''; ?>" 
                       href="<?php echo $link; ?>">
                        <i class="material-symbols-rounded opacity-5"><?php echo $item['icon']; ?></i>
                        <span class="nav-link-text ms-1 ps-1"><?php echo $item['text']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>

            <!-- Support and Sign Out Buttons -->
            <hr class="horizontal dark mt-0">
            <div class="d-flex justify-content-center">
                <button type="button" class="btn bg-gradient-info w-85 mx-auto" onclick="showSupportDialog()">
                    <i class="material-symbols-rounded opacity-5 me-2">support_agent</i>
                    <span class="nav-link-text">Space Support</span>
                </button>
            </div>
            <div class="d-flex justify-content-center mt-2">
                <button type="button" class="btn bg-gradient-primary w-85 mx-auto" onclick="handleSignOut()">
                    <i class="material-symbols-rounded opacity-5 me-2">logout</i>
                    <span class="nav-link-text">Sign Out</span>
                </button>
            </div>
        </ul>
    </div>
</aside>
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
    <div class="container-fluid py-1 px-2">
        <!-- Desktop Toggler -->
        <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none">
            <a href="javascript:;" class="nav-link text-body p-0">
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
                    <a class="opacity-5 text-dark" href="javascript:;"><?php echo htmlspecialchars($current_info['parent']); ?></a>
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
                <h3 class="mb-0 h4 font-weight-bolder">Therapist Dashboard</h3>
                <p class="mb-4">Monitor your appointments, sessions, and client activities</p>
            </div>

            <!-- Active Clients Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">group</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Active Clients</p>
                                <h4 class="mb-0"><?php echo $dashboard_data['active_clients']['count']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+<?php echo $dashboard_data['active_clients']['new']; ?> </span>new this week</p>
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
                                <p class="text-sm mb-0 text-capitalize">Upcoming Sessions</p>
                                <h4 class="mb-0"><?php echo $dashboard_data['upcoming_sessions']['count']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><?php echo formatTimeUntilNext($dashboard_data['upcoming_sessions']['next']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Completed Sessions Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-info shadow-info text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">task_alt</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Completed Sessions</p>
                                <h4 class="mb-0"><?php echo $dashboard_data['completed_sessions']['total']; ?></h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm">
                            <span class="text-success font-weight-bolder">+<?php echo $dashboard_data['completed_sessions']['weekly']; ?></span> this week
                        </p>
                    </div>
                </div>
            </div>

            <!-- Average Rating Card -->
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-warning shadow-warning text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">star</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Average Rating</p>
                                <h4 class="mb-0 d-flex align-items-center">
                                    <?php echo $dashboard_data['ratings']['average']; ?>
                                    <span class="badge bg-gradient-success ms-2" 
                                          style="font-size: 0.5em; padding: 0.35em 0.65em;">
                                        <?php echo $dashboard_data['ratings']['positive']; ?>% positive
                                    </span>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm">
                            Based on <span class="text-warning font-weight-bolder"><?php echo $dashboard_data['ratings']['total']; ?></span> reviews
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid py-1">
      <div class="row">
        <!-- Client Activity Chart -->
        <div class="col-lg-8 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h6 class="mb-0" style="color: var(--bs-1stgreen);">Client Activity</h6>
                  <p class="text-sm" style="color: var(--text-color-light);">Monthly Session Overview</p>
                </div>
              </div>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="clientActivityChart" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex justify-content-between">
                <div>
                  <i class="material-symbols-rounded text-sm my-auto me-1">schedule</i>
                  <span class="text-sm" style="color: var(--text-color-light);">Updated today</span>
                </div>
                <div class="text-sm">
                  Peak Activity: <?php echo htmlspecialchars($peak_activity); ?>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Session Completion Rate Chart -->
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
          <div class="card">
            <div class="card-body">
              <h6 class="mb-0" style="color: var(--bs-1stgreen);">Session Completion</h6>
              <p class="text-sm" style="color: var(--text-color-light);">Weekly Completion Rate</p>
              <div class="pe-2">
                <div class="chart">
                  <canvas id="completionRateChart" class="chart-canvas" height="170"></canvas>
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
    <!-- Trends and Insights Section -->
    <div class="container-fluid py-1">
          <div class="row">
              <div class="col-12">
                  <div class="card">
                      <div class="card-body">
                          <h6 class="mb-3" style="color: var(--bs-1stgreen);">Session Insights & Trends</h6>
                          <div class="row">
                              <!-- Growth Rate -->
                              <div class="col-md-3">
                                  <div class="insight-box">
                                      <h6 class="text-sm mb-1">Monthly Growth Rate</h6>
                                      <p class="mb-0 h5">
                                          <?php 
                                          $growth = $session_trends['growth_rate'];
                                          $growth_class = $growth >= 0 ? 'text-success' : 'text-danger';
                                          $growth_icon = $growth >= 0 ? '↑' : '↓';
                                          echo "<span class='{$growth_class}'>{$growth_icon} {$growth}%</span>";
                                          ?>
                                      </p>
                                  </div>
                              </div>
                              
                              <!-- Popular Times -->
                              <div class="col-md-3">
                                  <div class="insight-box">
                                      <h6 class="text-sm mb-1">Popular Session Times</h6>
                                      <p class="mb-0">
                                          <?php 
                                          if (!empty($session_trends['common_times'])) {
                                              echo implode(', ', array_slice($session_trends['common_times'], 0, 3));
                                          } else {
                                              echo "No data available";
                                          }
                                          ?>
                                      </p>
                                  </div>
                              </div>
                              
                              <!-- Cancellation Pattern -->
                              <div class="col-md-3">
                                  <div class="insight-box">
                                      <h6 class="text-sm mb-1">Cancellation Pattern</h6>
                                      <p class="mb-0">
                                          <?php 
                                          if (!empty($session_trends['cancellation_patterns'])) {
                                              $days = $session_trends['cancellation_patterns']['days_before'];
                                              echo "Most cancellations occur {$days} days before session";
                                          } else {
                                              echo "No cancellation patterns detected";
                                          }
                                          ?>
                                      </p>
                                  </div>
                              </div>
                              
                              <!-- Recommendations -->
                              <div class="col-md-3">
                                  <div class="insight-box">
                                      <h6 class="text-sm mb-1">Recommendations</h6>
                                      <p class="mb-0">
                                          <?php 
                                          if ($growth < 0) {
                                              echo "Consider expanding availability during popular times";
                                          } else if (!empty($session_trends['cancellation_patterns'])) {
                                              echo "Send reminders " . ($session_trends['cancellation_patterns']['days_before'] + 1) . " days before sessions";
                                          } else {
                                              echo "Maintain current scheduling pattern";
                                          }
                                          ?>
                                      </p>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </main>
<!-- Core JS Files (Keep these in order) -->
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/material-dashboard.min.js"></script>
<script src="../../assets/js/thsignout.js"></script>
<script src="../../assets/js/support.js"></script>

<!-- Keep only this one Material Dashboard JS -->
<script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>

<!-- DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Chart Initialization Scripts -->
<script>
    // Client Activity Chart
    var monthlyStats = <?php echo json_encode($monthly_stats); ?>;
    var ctx1 = document.getElementById("clientActivityChart").getContext("2d");
    new Chart(ctx1, {
        type: "bar",
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
            datasets: [{
                label: "Completed Sessions",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                backgroundColor: "#3c6454",
                data: monthlyStats ? monthlyStats.completed : [],
                maxBarThickness: 6
            },
            {
                label: "Cancelled Sessions",
                tension: 0.4,
                borderWidth: 0,
                borderRadius: 4,
                borderSkipped: false,
                backgroundColor: "#93bdaa",
                data: monthlyStats ? monthlyStats.cancelled : [],
                maxBarThickness: 6
            }]
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
                        }
                    }
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
                        }
                    }
                },
                x: {
                    grid: {
                        drawBorder: false,
                        display: false,
                        drawOnChartArea: false,
                        drawTicks: false
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
                        }
                    }
                }
            }
        }
    });

    // Session Completion Rate Chart
    var weeklyRates = <?php echo json_encode($weekly_completion_rates); ?>;
    var ctx2 = document.getElementById("completionRateChart").getContext("2d");
    new Chart(ctx2, {
        type: "line",
        data: {
            labels: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
            datasets: [{
                label: "Completion Rate",
                tension: 0.4,
                borderWidth: 2,
                borderColor: "#3c6454",
                backgroundColor: "rgba(60, 100, 84, 0.2)",
                fill: true,
                data: weeklyRates || [],
                maxBarThickness: 6
            }]
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
                        callback: function(value) {
                            return value + "%";
                        }
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
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>