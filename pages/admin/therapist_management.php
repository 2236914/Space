<?php
require_once '../../configs/config.php';
require_once '../../includes/admin_navigation_components.php';
require_once '../../configs/auth_check.php';
checkAdminAuth();
// Fetch therapists from database
try {
    $stmt = $pdo->prepare("
        SELECT t.*, 
               CASE WHEN pp.id IS NOT NULL THEN 'yes' ELSE 'no' END as has_profile_pic
        FROM therapists t
        LEFT JOIN profile_pictures pp ON t.therapist_id = pp.user_id 
        AND pp.user_type = 'therapist'
        ORDER BY t.status ASC, t.therapist_id DESC
    ");
    $stmt->execute();
    $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching therapists: " . $e->getMessage());
    $therapists = [];
}

// Status badge helper function
function getStatusBadgeClass($status) {
    return match($status) {
        'active' => 'bg-gradient-success',
        'inactive' => 'bg-gradient-secondary',
        'suspended' => 'bg-gradient-warning',
        default => 'bg-gradient-secondary'
    };
}

// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/admin_navigation_components.php';

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get current page info for breadcrumb
$current_info = [
    'parent' => 'Therapist Management',
    'title' => 'Therapist Profiles'
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
    Therapist Profiles
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <link rel="stylesheet" href="../../assets/css/custom-swal.css">
  
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
<div class="container-fluid py-2">
      <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <!-- Card Header with Title -->
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <div class="d-flex justify-content-between align-items-center px-3">
                  <div>
                    <h6 class="text-white text-capitalize ps-3">Therapist Management</h6>
                    <p class="text-white text-sm ps-3 mb-0">Manage and monitor therapist profiles</p>
                  </div>
                  <div>
                    <button class="btn btn-sm bg-gradient-dark me-2" id="exportCSV">
                      <i class="fas fa-file-csv me-2"></i>Export CSV
                    </button>
                    <button class="btn btn-sm bg-gradient-dark" id="addNewTherapist">
                      <i class="fas fa-plus me-2"></i>New Therapist
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Table -->
            <div class="card-body px-0 pb-2">
              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0" id="therapistTable">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Therapist</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Specialization</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Contact</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">License</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Created Date</th>
                      <th class="text-secondary opacity-7">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($therapists as $therapist): ?>
                    <tr data-status="<?php echo htmlspecialchars(strtolower($therapist['status'])); ?>">
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $therapist['therapist_id']; ?>&user_type=therapist" 
                                 class="avatar avatar-sm me-3" 
                                 onerror="this.src='../../assets/img/default-avatar.png'">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm"><?php echo $therapist['firstname'] . ' ' . $therapist['lastname']; ?></h6>
                            <p class="text-xs text-secondary mb-0"> <?php echo $therapist['email']; ?></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($therapist['specialization']); ?></p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($therapist['contact_number']); ?></p>
                        <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($therapist['email']); ?></p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($therapist['license_number']); ?></p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <?php
                        $statusClass = [
                            'active' => 'success',
                            'inactive' => 'secondary',
                            'suspended' => 'warning'
                        ][$therapist['status']] ?? 'secondary';
                        ?>
                        <span class="badge badge-sm bg-gradient-<?php echo $statusClass; ?>">
                            <?php echo ucfirst(htmlspecialchars($therapist['status'])); ?>
                        </span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-secondary text-xs font-weight-bold">
                            <?php echo date('d/m/y', strtotime($therapist['created_date'])); ?>
                        </span>
                      </td>
                      <td class="align-middle">
                        <button class="btn btn-link text-secondary mb-0 view-therapist" 
                                data-therapist-id="<?php echo htmlspecialchars($therapist['therapist_id']); ?>"
                                title="View Profile">
                          <i class="fas fa-eye text-xs"></i>
                        </button>
                        <button class="btn btn-link text-secondary mb-0 edit-therapist" 
                                data-therapist-id="<?php echo htmlspecialchars($therapist['therapist_id']); ?>"
                                title="Edit Profile">
                          <i class="fas fa-edit text-xs"></i>
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
</div>
<!-- Modal Container -->
<div class="modal fade" id="therapistModal" tabindex="-1" aria-hidden="true"></div>

<!-- Core JS Files -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/material-dashboard.min.js"></script>
<script src="../../assets/js/sign-out.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Your custom JS last -->
<script src="../../assets/js/therapist-management.js"></script>
<script>
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