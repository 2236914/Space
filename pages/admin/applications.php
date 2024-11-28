<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add logging
error_log("Session data: " . print_r($_SESSION, true));

// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    error_log("Auth failed - User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
    header('Location: ../../signin.php');
    exit;
}
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script src="../../assets/js/plugins/quotes.js"></script>
  <script src="../../assets/js/activity-tracker.js"></script>
</head>

<body class="g-sidenav-show  bg-gray-100">
<!-- Admin Aside -->
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand px-4 py-3 m-0" href="#">
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
                    <img src="assets/img/default-avatar.png" class="avatar">
                    <span class="nav-link-text ms-2 ps-1">Admin Name</span>
                </a>
            </li>
            <hr class="horizontal dark mt-0">

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link text-dark active" href="#">
                    <i class="material-symbols-rounded opacity-5">dashboard</i>
                    <span class="nav-link-text ms-1 ps-1">Dashboard</span>
                </a>
            </li>

            <!-- Student Management - New Section -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#studentManagement" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">school</i>
                    <span class="nav-link-text ms-1 ps-1">Student Management</span>
                </a>
                <div class="collapse" id="studentManagement">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">AS</span>
                                <span class="sidenav-normal ms-1">Active Students</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">SA</span>
                                <span class="sidenav-normal ms-1">Student Activities</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">SR</span>
                                <span class="sidenav-normal ms-1">Support Requests</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Community Management - New Section -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#communityManagement" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">groups</i>
                    <span class="nav-link-text ms-1 ps-1">Community</span>
                </a>
                <div class="collapse" id="communityManagement">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">P</span>
                                <span class="sidenav-normal ms-1">Posts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">RM</span>
                                <span class="sidenav-normal ms-1">Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Announcements - New Section -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#announcements" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">campaign</i>
                    <span class="nav-link-text ms-1 ps-1">Announcements</span>
                </a>
                <div class="collapse" id="announcements">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">NA</span>
                                <span class="sidenav-normal ms-1">New Announcement</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">MA</span>
                                <span class="sidenav-normal ms-1">Manage Announcements</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">NS</span>
                                <span class="sidenav-normal ms-1">Newsletter</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Therapist Management -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#therapistManagement" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">people</i>
                    <span class="nav-link-text ms-1 ps-1">Therapist Management</span>
                </a>
                <div class="collapse" id="therapistManagement">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="applications.php">
                                <span class="sidenav-mini-icon">A</span>
                                <span class="sidenav-normal ms-1">Applications</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">T</span>
                                <span class="sidenav-normal ms-1">Therapists</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">VT</span>
                                <span class="sidenav-normal ms-1">Video Tokens</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Self-Care Materials -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#materials" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">library_books</i>
                    <span class="nav-link-text ms-1 ps-1">Self-Care Materials</span>
                </a>
                <div class="collapse" id="materials">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">MM</span>
                                <span class="sidenav-normal ms-1">Manage Materials</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Reports -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#reports" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">analytics</i>
                    <span class="nav-link-text ms-1 ps-1">Reports</span>
                </a>
                <div class="collapse" id="reports">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">UR</span>
                                <span class="sidenav-normal ms-1">Usage Reports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">SR</span>
                                <span class="sidenav-normal ms-1">Session Reports</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Support -->
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#support" class="nav-link text-dark">
                    <i class="material-symbols-rounded opacity-5">support_agent</i>
                    <span class="nav-link-text ms-1 ps-1">Support</span>
                </a>
                <div class="collapse" id="support">
                    <ul class="nav nav-sm flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">SM</span>
                                <span class="sidenav-normal ms-1">Student Messages</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="#">
                                <span class="sidenav-mini-icon">TM</span>
                                <span class="sidenav-normal ms-1">Therapist Messages</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- System Logs -->
            <li class="nav-item">
                <a class="nav-link text-dark" href="#">
                    <i class="material-symbols-rounded opacity-5">receipt_long</i>
                    <span class="nav-link-text ms-1 ps-1">System Logs</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Support & Sign Out Buttons -->
    <div class="sidenav-footer position-absolute w-100 bottom-0">
        <div class="mx-3">
            <button type="button" class="btn bg-gradient-info w-100 mb-2">
                <i class="material-symbols-rounded opacity-5 me-2">support_agent</i> Support
            </button>
            <button type="button" class="btn bg-gradient-primary w-100 mb-3">
                <i class="material-symbols-rounded opacity-5 me-2">logout</i> Sign Out
            </button>
        </div>
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
                                <h4 class="mb-0">15</h4>
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
                                <h4 class="mb-0">42</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+12 </span>new messages</p>
                    </div>
                </div>
            </div>

            <!-- Self-Care Materials Card -->
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-info shadow-info text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">library_books</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Self-Care Materials</p>
                                <h4 class="mb-0">45</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+5 </span>this week</p>
                    </div>
                </div>
            </div>

            <!-- Active Sessions Card -->
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-header p-2 pe-3">
                        <div class="d-flex justify-content-between">
                            <div class="icon icon-md icon-shape bg-gradient-warning shadow-warning text-center border-radius-lg">
                                <i class="material-symbols-rounded opacity-10">person</i>
                            </div>
                            <div>
                                <p class="text-sm mb-0 text-capitalize">Active Users</p>
                                <h4 class="mb-0">8</h4>
                            </div>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                    <div class="card-footer p-2 ps-3">
                        <p class="mb-0 text-sm"><span class="text-success font-weight-bolder">+2 </span>currently online</p>
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
<script src="../../assets/js/plugins/chartjs.min.js"></script>
<script src="../../assets/js/plugins/sweetalert2.min.js"></script>
<script src="../../assets/js/plugins/jquery.min.js"></script>
<script src="../../assets/js/signout.js"></script>
<script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
        var options = {
            damping: '0.5'
        }
        Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
</script>
<!-- Github buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>
<!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
<script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
<!-- Handle sign out with SweetAlert2 confirmation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded');
    // Check if required elements exist
    console.log('Sidenav exists:', !!document.getElementById('sidenav-main'));
    console.log('Main content exists:', !!document.querySelector('.main-content'));
    
    // Log any JavaScript errors
    window.onerror = function(msg, url, lineNo, columnNo, error) {
        console.error('Error: ' + msg + '\nURL: ' + url + '\nLine: ' + lineNo + '\nColumn: ' + columnNo + '\nError object: ' + JSON.stringify(error));
        return false;
    };
});
</script>
</body>

</html>