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
                    <img src="../../assets/img/team-3.jpg" class="avatar">
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
                <div class="icon icon-md icon-shape bg-primary shadow-dark shadow text-center border-radius-lg me-3">
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
                <div class="icon icon-md icon-shape bg-primary shadow-dark shadow text-center border-radius-lg me-3">
                  <i class="material-symbols-outlined opacity-10">event_available</i>
                </div>
                <div>
                  <p class="text-sm mb-0 text-capitalize">My Check-ins</p>
                  <h4 class="mb-0">2300</h4>
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
                <div class="icon icon-md icon-shape bg-primary shadow-dark shadow text-center border-radius-lg me-3">
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
                <div class="icon icon-md icon-shape bg-primary shadow-dark shadow text-center border-radius-lg me-3">
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
            <!-- Upcoming Events Card -->
            <div class="col-lg-4 col-md-7 d-flex">
              <div class="card pb-3 w-100">
                <div class="card-header p-3 pb-0">
                  <h6 class="mb-0">Upcoming events</h6>
                  <p class="text-sm mb-0 text-capitalize font-weight-normal">Joined</p>
                </div>
                <!-- Scrollable Content -->
                <div class="card-body border-radius-lg p-3 perfect-scrollbar" style="max-height: 220px; overflow-y: auto;" id="modern-scroll">
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-primary icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">savings</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">Cyber Week</h6>
                        <span class="text-sm">27 March 2020, at 12:30 PM</span>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-primary icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">notifications_active</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">Meeting with Marry</h6>
                        <span class="text-sm">24 March 2020, at 10:00 PM</span>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex mb-4">
                    <div>
                      <div class="icon icon-shape bg-primary icon-md text-center border-radius-md shadow-none">
                        <i class="material-symbols-rounded text-white opacity-10" aria-hidden="true">task</i>
                      </div>
                    </div>
                    <div class="ms-3">
                      <div class="numbers">
                        <h6 class="mb-1 text-dark text-sm">Tasks planification</h6>
                        <span class="text-sm">24 March 2020, at 12:30 AM</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <!-- Care-O-Meter Card -->
            <div class="col-lg-8 d-flex mt-lg-0 mt-4">
              <div class="card overflow-hidden h-100 w-100">
                <div class="card-header p-3 pb-0">
                  <div class="d-flex align-items-center justify-content-between flex-wrap">
                    <!-- Icon and Title -->
                    <div class="d-flex align-items-center">
                      <div class="icon icon-shape bg-primary shadow text-center border-radius-md">
                        <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                      </div>
                      <div class="ms-3">
                        <h6 class="mb-0">Care-O-Meter</h6>
                        <p class="text-sm mb-0 text-capitalize font-weight-normal">
                          Average time spent on self-care modules
                        </p>
                      </div>
                    </div>
                    <!-- Year Dropdown -->
                    <div class="dropdown mt-3 mt-lg-0">
                      <button
                        class="btn btn-sm btn-primary dropdown-toggle"
                        type="button"
                        id="dropdownMenuButtonYearCare"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                      >
                        Select Year
                      </button>
                      <ul
                        class="dropdown-menu"
                        aria-labelledby="dropdownMenuButtonYearCare"
                        id="yearDropdownCare"
                      >
                        <!-- Year options -->
                        <li><a class="dropdown-item" href="#">2023</a></li>
                        <li><a class="dropdown-item" href="#">2022</a></li>
                        <li><a class="dropdown-item" href="#">2021</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="card-body mt-3 p-2">
                  <div class="chart" style="padding: 0 5px;">
                    <canvas id="chart-line-widgets" class="chart-canvas" height="200"></canvas>
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
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>

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

</body>
</html>