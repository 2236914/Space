<?php
session_start();
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';

$analytics = new DashboardAnalytics($pdo);

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
} // Add this closing brace

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
  </script>
  <script src="<?php echo BASE_URL; ?>/assets/js/plugins/quotes.js"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
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
                <a data-bs-toggle="collapse" href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav" role="button" aria-expanded="false">
                    <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                         class="avatar"
                         onerror="this.src='../../assets/img/default-avatar.png';">
                    <span class="nav-link-text ms-2 ps-1">
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
                                echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                            } else {
                                // Try to fetch from database if session variables are missing
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
                <a data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link text-dark active" aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-symbols-rounded opacity-5">space_dashboard</i>
                    <span class="nav-link-text ms-1 ps-1">Dashboard</span>
                </a>
                <div class="collapse show" id="dashboardsExamples">
                    <ul class="nav">
                        <li class="nav-item active">
                            <a class="nav-link text-dark active" href="student.php">
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
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="notifications.php">
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
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg position-sticky mt-2 top-1 px-0 py-1 mx-3 shadow-none border-radius-lg z-index-sticky" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-2">
        <div class="sidenav-toggler sidenav-toggler-inner d-xl-block d-none ">
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
            <li class="breadcrumb-item text-sm text-dark active font-weight-bold" aria-current="page">Analytics</li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
            <div class="input-group input-group-outline">
              <label class="form-label">Search here</label>
              <input type="text" class="form-control">
            </div>
          </div>
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item">
              <a href="Space/pages/authentication/signin/illustration.html" class="px-1 py-0 nav-link line-height-0" target="_blank">
                <i class="material-symbols-rounded">
              account_circle
            </i>
              </a>
            </li>
            <li class="nav-item">
              <a href="javascript:;" class="nav-link py-0 px-1 line-height-0">
                <i class="material-symbols-rounded fixed-plugin-button-nav">
              settings
            </i>
              </a>
            </li>
            <li class="nav-item dropdown py-0 pe-3">
              <a href="javascript:;" class="nav-link py-0 px-1 position-relative line-height-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="material-symbols-rounded">
              notifications
            </i>
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
                        <h6 class="text-sm font-weight-normal my-auto">
                          Check new messages
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex align-items-center py-1">
                      <span class="material-symbols-rounded">podcasts</span>
                      <div class="ms-2">
                        <h6 class="text-sm font-weight-normal my-auto">
                          Manage podcast session
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex align-items-center py-1">
                      <span class="material-symbols-rounded">shopping_cart</span>
                      <div class="ms-2">
                        <h6 class="text-sm font-weight-normal my-auto">
                          Payment successfully completed
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
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
          <p class="mb-4">
            Review check-ins, mood entries, and therapy appointments to support student well-being
          </p>
        </div>
        <div class="col-md-4 text-end d-flex align-items-center justify-content-end">
          <div class="text-muted" style="font-size: 0.9rem;">
              <i class="material-symbols-rounded me-2" style="color: #3c6454;">calendar_today</i>
              <span id="date"></span>
              <span class="mx-2">|</span>
              <span id="time"></span>
          </div>
      </div>
      <div class="row mt-1">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                  <i class="material-symbols-outlined opacity-10">psychiatry</i>
                </div>
                <div>
                    <p class="text-sm mb-0 text-capitalize">Space Check-ins</p>
                    <h4 class="mb-0"><?php echo $analytics->getTotalCheckins(); ?></h4>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
                <?php
                $newStudents = $analytics->getNewStudents();
                if (isset($newStudents['error'])) {
                    echo "<p class='mb-0 text-sm'>No data available</p>";
                } else {
                    $count = $newStudents['count'];
                    if ($count > 0) {
                        echo "<p class='mb-0 text-sm'><span class='text-success font-weight-bolder'>+{$count} </span>new students today</p>";
                    } else {
                        echo "<p class='mb-0 text-sm'><span class='text-secondary'>No new students today</span></p>";
                    }
                }
                ?>
            </div>
          </div>
        </div>
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
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                  <i class="material-symbols-outlined opacity-10">stylus</i>
                </div>
                <div>
                  <p class="text-sm mb-0 text-capitalize">My Community Post</p>
                  <h4 class="mb-0">2</h4>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+2% </span>from yesterday</p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-md icon-shape bg-gradient-primary shadow-dark shadow text-center border-radius-lg me-3">
                  <i class="material-symbols-outlined opacity-10">assignment_ind</i>
                </div>
                <div>
                  <p class="text-sm mb-0 text-capitalize">My Sessions</p>
                  <h4 class="mb-0">0</h4>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+5% </span>than last month</p>
            </div>
          </div>
        </div>

        <div class="container-fluid py-3">
        <div class="row align-items-stretch">
        <!-- Website Views Card -->
        <div class="col-lg-4 col-md-7 d-flex">
        <div class="card w-100">
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
              <div class="chart position-relative" style="height: 170px">
                  <canvas id="chart-bars"></canvas>
              </div>
              <hr class="dark horizontal">
              <div class="d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm my-auto me-1">update</i>
                      <p class="mb-0 text-sm" id="last-activity">
                          <?php
                          try {
                              // Get activities for the last 7 days including today
                              $query = "SELECT 
                                  DATE_FORMAT(created_at, '%a') as day,
                                  COUNT(*) as count,
                                  MAX(created_at) as latest_activity
                                  FROM activity_logs 
                                  WHERE srcode = :user_id 
                                  AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                  AND action IN (
                                      'Logged In',
                                      'Liked Quote',
                                      'Refreshed Quote',
                                      'Updated Profile',
                                      'Logged Mood',
                                      'Viewed Resource'
                                  )
                                  GROUP BY DATE_FORMAT(created_at, '%a')
                                  ORDER BY created_at DESC";
                                  
                              $stmt = $pdo->prepare($query);
                              $stmt->execute(['user_id' => $_SESSION['user_id']]);
                              $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                  
                              if ($result) {
                                  $timeAgo = time() - strtotime($result['latest_activity']);
                                  if ($timeAgo < 3600) {
                                      echo "-" . floor($timeAgo / 60) . " minutes ago";
                                  } elseif ($timeAgo < 86400) {
                                      echo "-" . floor($timeAgo / 3600) . " hours ago";
                                  } else {
                                      echo "-" . floor($timeAgo / 86400) . " days ago";
                                  }
                                  
                                  // Debug information
                                  error_log("Today's count: " . $result['count'] . " for day: " . $result['day']);
                              } else {
                                  echo "No recent activity";
                              }
                          } catch (PDOException $e) {
                              error_log("Error fetching activity data: " . $e->getMessage());
                              echo "Error loading activity data";
                          }
                          ?>
                      </p>
                  </div>
                  <p class="mb-0 text-sm text-muted">
                      <i class="material-symbols-rounded text-sm">refresh</i>
                      Refresh every week
                  </p>
              </div>
          </div>
        </div>
      </div>

        <div class="col-lg-4 col-md-7 d-flex">
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
                  <a href="mood-tracker.php" class="btn btn-sm btn-primary">See More</a>
                </div>
                <?php endif; ?>
              </div>
            </div>



            <!-- Announcements Card -->
            <div class="col-lg-4 col-md-7 d-flex">
            <div class="card w-100">
                <div class="card-header p-3 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                            <i class="material-symbols-rounded text-white opacity-10">campaign</i>
                        </div>
                        <div class="ms-3">
                            <h6 class="mb-0">Announcements</h6>
                            <p class="text-sm mb-0">Latest updates</p>
                        </div>
                    </div>
                </div>
                <div class="card-body border-radius-lg p-3 perfect-scrollbar" style="max-height: 220px; overflow-y: auto;" id="announcements-scroll">
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-warning icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">campaign</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">Holiday Schedule</h6>
                        <span class="text-sm">Posted 2 hours ago</span>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-warning icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">event</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">Wellness Workshop</h6>
                        <span class="text-sm">Posted yesterday</span>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-warning icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">tips_and_updates</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">New Resources Available</h6>
                        <span class="text-sm">Posted 2 days ago</span>
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


    <div class="container-fluid py-4">
  <div class="row align-items-center justify-content-between">
    <!-- Left Section -->
    <div class="col-auto ms-3">
      <h5 class="mb-0 font-weight-bolder">Mood tracker</h5>
      <p class="mb-0">Review mood entries</p>
    </div>
    <!-- Right Section (Tabs) -->
    <div class="col-auto">
      <div class="nav-wrapper position-relative end-0 me-1">
        <ul class="nav nav-pills nav-fill" role="tablist">
          <li class="nav-item">
            <a class="nav-link mb-0 px-3 py-1 active" id="line-tab" data-bs-toggle="tab" href="#line-chart-content" role="tab" aria-controls="line-chart-content" aria-selected="true">
              Line
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link mb-0 px-3 py-1" id="doughnut-tab" data-bs-toggle="tab" href="#doughnut-chart-content" role="tab" aria-controls="doughnut-chart-content" aria-selected="false">
              Doughnut
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <!-- Tab Content -->
  <div class="tab-content mt-4">
    <!-- Line Chart Content -->
    <div class="tab-pane fade show active" id="line-chart-content" role="tabpanel" aria-labelledby="line-tab">
      <!-- LINECHART -->
      <div class="row">
        <div class="col-lg-12 col-12">
          <div class="card z-index-2 mt-4">
            <div class="card-header p-3 pt-2 d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="icon icon-lg icon-shape bg-primary shadow-primary text-center border-radius-xl mt-n6 me-3">
                  <i class="material-symbols-rounded opacity-10">insights</i>
                </div>
                <div>
                  <h6 class="mb-0">Line chart</h6>
                  <p class="mb-0 text-sm">Product insights</p>
                </div>
              </div>
              <div class="d-flex gap-2 pt-3">
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Success
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="#">Action 1</a></li>
                    <li><a class="dropdown-item" href="#">Action 2</a></li>
                    <li><a class="dropdown-item" href="#">Action 3</a></li>
                  </ul>
                </div>
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                    Success
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                    <li><a class="dropdown-item" href="#">Option 1</a></li>
                    <li><a class="dropdown-item" href="#">Option 2</a></li>
                    <li><a class="dropdown-item" href="#">Option 3</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body p-3 pt-0">
              <!-- Chart Section -->
              <div class="chart">
                <canvas id="line-chart" class="chart-canvas" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Doughnut Chart Content -->
    <div class="tab-pane fade" id="doughnut-chart-content" role="tabpanel" aria-labelledby="doughnut-tab">
      <!-- DOUGHNUT -->
      <div class="row">
        <div class="col-lg-12 col-12">
          <div class="card z-index-2 mt-4">
            <div class="card-header p-3 pt-2 d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center">
                <div class="icon icon-lg icon-shape bg-primary shadow-primary text-center border-radius-xl mt-n6 me-3">
                  <i class="material-symbols-rounded opacity-10">donut_small</i>
                </div>
                <div>
                  <h6 class="mb-0">Doughnut chart</h6>
                  <p class="mb-0 text-sm">Affiliates program</p>
                </div>
              </div>
              <div class="d-flex gap-2 pt-3">
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                    Dropdown 1
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                    <li><a class="dropdown-item" href="#">Option 1</a></li>
                    <li><a class="dropdown-item" href="#">Option 2</a></li>
                    <li><a class="dropdown-item" href="#">Option 3</a></li>
                  </ul>
                </div>
                <div class="dropdown">
                  <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                    Dropdown 2
                  </button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                    <li><a class="dropdown-item" href="#">Action 1</a></li>
                    <li><a class="dropdown-item" href="#">Action 2</a></li>
                    <li><a class="dropdown-item" href="#">Action 3</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body d-flex p-3 pt-0">
              <div class="chart w-50" style="max-width: 50%; flex: 1;">
                <canvas id="doughnut-chart" class="chart-canvas" height="300"></canvas>
              </div>
              <!-- Scrollable Table Section -->
              <div class="d-flex overflow-x-auto" style="max-width: 50%; flex: 1; gap: 20px; padding: 10px;">
  <div style="min-width: 300px;">
    <table class="table align-items-center mb-0">
      <tbody>
        <tr>
          <td>😁</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">25%</span>
          </td>
        </tr>
        <tr>
          <td>☹️</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">15%</span>
          </td>
        </tr>
        <tr>
          <td>😠</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">10%</span>
          </td>
        </tr>
        <tr>
          <td>😌</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">20%</span>
          </td>
        </tr>
        <tr>
          <td>😨</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">12%</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div style="min-width: 300px;">
    <table class="table align-items-center mb-0">
      <tbody>
        <tr>
          <td>🥰</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">18%</span>
          </td>
        </tr>
        <tr>
          <td>😴</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">8%</span>
          </td>
        </tr>
        <tr>
          <td>😕</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">5%</span>
          </td>
        </tr>
        <tr>
          <td>🤔</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">4%</span>
          </td>
        </tr>
        <tr>
          <td>💤</td>
          <td class="align-middle text-center text-sm">
            <span class="text-xs font-weight-bold">3%</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

    </div>

  </main>
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  

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
    var ctx3 = document.getElementById("doughnut-chart").getContext("2d");

new Chart(ctx3, {
  type: "doughnut",
  data: {
    labels: ['😁', '☹️', '😠', '😌', '😨', '🥰', '😴', '😕', '🤔', '💤'],
    datasets: [{
      label: "Mood Distribution",
      weight: 9,
      cutout: 60,
      borderWidth: 2,
      backgroundColor: [
        '#3c6454', // Base primary color
        '#497a62', 
        '#5e9273', 
        '#74a985', 
        '#8bc197', 
        '#a1d8a9', 
        '#b8efbb', 
        '#cff7cd', 
        '#e5fddf', 
        '#fafff1'
      ],
      data: [25, 15, 10, 20, 12, 18, 8, 5, 4, 3], // Example percentage data
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
          label: function(tooltipItem) {
            return `${tooltipItem.label}: ${tooltipItem.raw}%`;
          }
        }
      }
    },
    interaction: {
      intersect: false,
      mode: 'index',
    },
  },
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
  document.addEventListener('DOMContentLoaded', function() {
    // **Months and Years Arrays**
    var months = [
      'January', 'February', 'March', 'April', 'May', 'June',
      'July', 'August', 'September', 'October', 'November', 'December'
    ];
    var years = []; // Years from 2024 to 2030
    for (var y = 2024; y <= 2030; y++) {
      years.push(y);
    }

    // **Function to Populate Months Dropdown**
    function populateMonths(monthDropdownId, monthButtonId) {
      var monthDropdown = document.getElementById(monthDropdownId);
      var monthButton = document.getElementById(monthButtonId);

      // Set default month (optional)
      var currentMonth = months[new Date().getMonth()];
      monthButton.textContent = currentMonth;

      months.forEach(function(month) {
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.className = 'dropdown-item';
        a.href = '#';
        a.textContent = month;
        a.setAttribute('data-month', month);
        li.appendChild(a);
        monthDropdown.appendChild(li);
      });

      monthDropdown.addEventListener('click', function(e) {
        if (e.target && e.target.matches('a.dropdown-item')) {
          e.preventDefault();
          var selectedMonth = e.target.getAttribute('data-month');
          monthButton.textContent = selectedMonth;
          // Perform any additional actions with selectedMonth
        }
      });
    }

    // **Function to Populate Years Dropdown**
    function populateYears(yearDropdownId, yearButtonId) {
      var yearDropdown = document.getElementById(yearDropdownId);
      var yearButton = document.getElementById(yearButtonId);

      // Set default year (optional)
      var currentYear = new Date().getFullYear();
      if (years.includes(currentYear)) {
        yearButton.textContent = currentYear;
      } else {
        yearButton.textContent = years[0]; // Default to first year in array
      }

      years.forEach(function(year) {
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.className = 'dropdown-item';
        a.href = '#';
        a.textContent = year;
        a.setAttribute('data-year', year);
        li.appendChild(a);
        yearDropdown.appendChild(li);
      });

      yearDropdown.addEventListener('click', function(e) {
        if (e.target && e.target.matches('a.dropdown-item')) {
          e.preventDefault();
          var selectedYear = e.target.getAttribute('data-year');
          yearButton.textContent = selectedYear;
          // Perform any additional actions with selectedYear
        }
      });
    }

    // **Populate Feel-O-Meter Dropdowns**
    populateMonths('monthDropdownFeel', 'dropdownMenuButtonMonthFeel');
    populateYears('yearDropdownFeel', 'dropdownMenuButtonYearFeel');

    // **Populate Care-O-Meter Dropdown**
    populateYears('yearDropdownCare', 'dropdownMenuButtonYearCare');

    // **Populate Doughnut Chart Dropdowns**
    populateMonths('monthDropdownDoughnut', 'dropdownMenuButtonMonthDoughnut');
    populateYears('yearDropdownDoughnut', 'dropdownMenuButtonYearDoughnut');
  });
</script>

  <script<script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
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
// Prevent going back to protected page after logout
window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.pushState(null, null, window.location.href);
};

// Handle sign out with SweetAlert2 confirmation
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
            // Show loading state
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

            // Redirect to logout script after a brief delay
            setTimeout(() => {
                window.location.href = '../../admin_operations/logout.php';
            }, 1000);
        }
    });
}

// Check if user is logged in
function checkLoginStatus() {
    fetch('../../admin_operations/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                window.location.href = '../signin.php';
            }
        });
}

// Check login status when page loads
document.addEventListener('DOMContentLoaded', function() {
    checkLoginStatus();
});

// Periodically check login status
setInterval(checkLoginStatus, 30000); // Check every 30 seconds
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add refresh button to card header
    const cardHeader = document.querySelector('#quote-container').closest('.card').querySelector('.card-header');
    const refreshBtn = document.createElement('button');
    refreshBtn.className = 'btn btn-link p-0 ms-auto';
    refreshBtn.innerHTML = '<i class="material-symbols-rounded">refresh</i>';
    refreshBtn.onclick = function() {
        location.reload();
    };
    cardHeader.style.display = 'flex';
    cardHeader.style.alignItems = 'start';
    cardHeader.appendChild(refreshBtn);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById("chart-bars");
    
    // Fetch activity data
    $.ajax({
        url: BASE_URL + '/admin_operations/get_activity_data.php',
        type: 'GET',
        success: function(response) {
            const chartData = typeof response === 'string' ? JSON.parse(response) : response;
            
            if (chartData.error) {
                console.error('Error:', chartData.error);
                return;
            }

            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: "Activities",
                        tension: 0.4,
                        borderWidth: 0,
                        borderRadius: 4,
                        borderSkipped: false,
                        backgroundColor: "#43A047",
                        data: chartData.data,
                        maxBarThickness: 6
                    }]
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
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    return context.parsed.y + ' activities';
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5],
                                color: '#e5e5e5'
                            },
                            ticks: {
                                suggestedMin: 0,
                                suggestedMax: 10,
                                beginAtZero: true,
                                padding: 10,
                                font: {
                                    size: 14,
                                    lineHeight: 2
                                },
                                color: "#737373"
                            },
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                            },
                            ticks: {
                                display: true,
                                color: '#737373',
                                padding: 10,
                                font: {
                                    size: 14,
                                    lineHeight: 2
                                },
                            }
                        },
                    },
                },
            });
        },
        error: function(xhr, status, error) {
            console.error('Ajax error:', error);
        }
    });
});
</script>

</body>
</html>