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

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get current page info for breadcrumb
$current_info = [
    'parent' => 'Admin',
    'title' => 'Dashboard'
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

    <!-- Sign Out Button -->
    <div class="sidenav-footer position-absolute w-100 bottom-0">
        <div class="mx-3">
            <button type="button" class="btn bg-gradient-primary w-100 mb-3" onclick="handleSignOut()">
                <i class="material-symbols-rounded opacity-5 me-2">logout</i> Sign Out
            </button>
        </div>
    </div>
</aside>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
<!-- Navbar -->
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
            <div class="ms-md-auto pe-md-3 d-flex align-items-center position-relative">
                <div class="input-group input-group-outline">
                    <input type="text" class="form-control" id="searchInput" placeholder="Type to search..." oninput="searchMenu()">
                </div>
                <div id="searchResults" class="position-absolute bg-white rounded-3 shadow-lg p-2 mt-2 d-none" style="top: 100%; left: 0; right: 0; z-index: 1000;">
                </div>
            </div>
            <ul class="navbar-nav justify-content-end">
                <?php foreach ($admin_navbar_items as $item): ?>
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
                                        <a class="dropdown-item border-radius-md" href="javascript:;">
                                            <div class="d-flex align-items-center py-1">
                                                <span class="material-symbols-rounded"><?php echo $dropdownItem['icon']; ?></span>
                                                <div class="ms-2">
                                                    <h6 class="text-sm font-weight-normal my-auto">
                                                        <?php echo $dropdownItem['text']; ?>
                                                    </h6>
                                                </div>
                                            </div>
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
    <div class="container-fluid my-3 py-3">
      <div class="row">
      <div class="col-lg-8">
          <div class="row">
            <div class="col-xl-6 col-md-6 mb-xl-0 mb-4">
              <div class="card bg-transparent shadow-xl">
                <div class="overflow-hidden position-relative border-radius-xl">
                  <img src="../../assets/img/illustrations/pattern-tree.svg" class="position-absolute opacity-2 start-0 top-0 w-100 z-index-1 h-100" alt="pattern-tree">
                  <span class="mask bg-gradient-dark opacity-10"></span>
                  <div class="card-body position-relative z-index-1 p-3">
                   <div class="row align-items-center">
                        <div class="col-4">
                        <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                            alt="profile" 
                            class="border-radius-lg shadow shadow-dark w-100 mt-n0"
                            onerror="this.src='../../assets/img/default-avatar.png';">
                        </div>
                        <div class="col-8">
                            <h5 class="text-white mb-0"><?php echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']); ?></h5>
                            <p class="text-white text-sm opacity-8 mb-0"><?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                        </div>
                    </div>
                    <div class="d-flex">
                      <div class="d-flex mt-5">
                        <div class="me-4">
                          <p class="text-white text-sm opacity-8 mb-0">Role</p>
                          <h6 class="text-white mb-0"><?php echo htmlspecialchars(ucfirst($_SESSION['role'])); ?></h6>
                        </div>
                        <div>
                          <p class="text-white text-sm opacity-8 mb-0">Space</p>
                          <h6 class="text-white mb-0"><?php echo date('m-d-Y'); ?></h6>
                        </div>
                      </div>
                      <div class="ms-auto w-20 d-flex align-items-end justify-content-end">
                        <img class="w-60 mt-2" src="../../assets/img/logo-space.png" alt="logo">
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
</main>

    <!-- Core JS Files -->
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets/js/material-dashboard.min.js"></script>

    <!-- Custom Scripts -->
    <script>
        // Handle sign out
        function handleSignOut() {
            window.location.href = '../../auth/logout.php';
        }

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
    </script>
</body>
</html>