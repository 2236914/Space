<?php
require_once '../../configs/auth_check.php';
checkAdminAuth();


// Required files
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/admin_navigation_components.php';

// Fetch applications from database
function getApplicationsByStatus($status) {
    global $pdo;
    $query = "SELECT * FROM therapist_applications WHERE status = ? ORDER BY application_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get counts for each status
$pending_applications = getApplicationsByStatus('pending');
$approved_applications = getApplicationsByStatus('approved');
$rejected_applications = getApplicationsByStatus('rejected');

$pending_count = count($pending_applications);
$approved_count = count($approved_applications);
$rejected_count = count($rejected_applications);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Therapist Applications
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
  <style>
.reply-section {
    margin-top: 20px;
}

.reply-section textarea {
    background: white;
    border: 1px solid #d2d6da;
    padding: 8px 12px;
    font-size: 14px;
}

.reply-section textarea:focus {
    border-color: #35D1F5;
    box-shadow: 0 0 0 2px rgba(53, 209, 245, 0.2);
}

.reply-section input[type="file"] {
    padding: 8px;
    font-size: 14px;
}
</style>
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

 <div class="container-fluid py-4">
     <!-- Status Nav Pills -->
    <div class="row justify-space-between py-2">
     <div class="col-lg-8 mx-auto">
     <div class="nav-wrapper position-relative end-0">
         <ul class="nav nav-pills nav-fill p-1" role="tablist">
             <li class="nav-item">
                 <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#pending-tab" role="tab">
                     <span class="material-symbols-rounded align-middle mb-1">pending</span>
                     Pending
                     <span class="badge rounded-pill bg-warning ms-2"><?php echo $pending_count; ?></span>
                </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#approved-tab" role="tab">
                     <span class="material-symbols-rounded align-middle mb-1">check_circle</span>
                     Approved
            <span class="badge rounded-pill bg-success ms-2"><?php echo $approved_count; ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#rejected-tab" role="tab">
            <span class="material-symbols-rounded align-middle mb-1">cancel</span>
            Rejected
            <span class="badge rounded-pill bg-danger ms-2"><?php echo $rejected_count; ?></span>
          </a>
        </li>
      </ul>
    </div>
    </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Pending Applications Tab -->
      <div class="tab-pane fade show active" id="pending-tab" role="tabpanel">
        <div class="card my-4">
          <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3">Pending Applications</h6>
            </div>
          </div>
          <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
              <table class="table align-items-center mb-0">
                <thead>
                  <tr>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Applicant</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Specialization</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">License</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Application Date</th>
                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($pending_applications as $application): ?>
                      <tr>
                          <td>
                              <div class="d-flex px-2 py-1">
                                  <div>
                                      <img src="<?php echo $application['profile_picture'] ? '../../admin_operations/get_application_file.php?id=' . $application['id'] . '&type=profile' : '../../assets/img/default-avatar.png'; ?>" 
                                           class="avatar avatar-sm me-3 border-radius-lg" alt="user">
                                  </div>
                                  <div class="d-flex flex-column justify-content-center">
                                      <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h6>
                                      <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['email']); ?></p>
                                  </div>
                              </div>
                          </td>
                          <td>
                              <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($application['specialization']); ?></p>
                              <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['experience']); ?></p>
                          </td>
                          <td class="align-middle text-center text-sm">
                              <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=license" class="text-xs">View License</a>
                              <br>
                              <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=resume" class="text-xs">View Resume</a>
                          </td>
                          <td class="align-middle text-center">
                              <span class="text-secondary text-xs font-weight-bold">
                                  <?php echo date('M d, Y', strtotime($application['application_date'])); ?>
                              </span>
                          </td>
                          <td class="align-middle text-center">
                              <button class="btn btn-sm btn-success" onclick="approveApplication(<?php echo $application['id']; ?>)">
                                  <span class="material-symbols-rounded">check</span>
                              </button>
                              <button class="btn btn-sm btn-danger" onclick="rejectApplication(<?php echo $application['id']; ?>)">
                                  <span class="material-symbols-rounded">close</span>
                              </button>
                              <button class="btn btn-sm btn-info" onclick="viewApplication(<?php echo $application['id']; ?>)">
                                  <span class="material-symbols-rounded">visibility</span>
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

      <!-- Approved Applications Tab -->
      <div class="tab-pane fade" id="approved-tab" role="tabpanel">
        <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
              <h6 class="text-white text-capitalize ps-3">Approved Applications</h6>
            </div>
          </div>
            <div class="card-body px-0 pb-2">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Applicant</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Specialization</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Documents</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Application Date</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Approval Date</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_applications as $application): ?>
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div>
                                            <img src="<?php echo $application['profile_picture'] ? '../../admin_operations/get_application_file.php?id=' . $application['id'] . '&type=profile' : '../../assets/img/default-avatar.png'; ?>" 
                                                 class="avatar avatar-sm me-3 border-radius-lg" alt="user">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h6>
                                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($application['specialization']); ?></p>
                                    <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['experience']); ?></p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=license" class="text-xs">View License</a>
                                    <br>
                                    <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=resume" class="text-xs">View Resume</a>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">
                                        <?php echo date('M d, Y', strtotime($application['application_date'])); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">
                                        <?php echo date('M d, Y', strtotime($application['review_date'])); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <button class="btn btn-sm btn-info" onclick="viewApplication(<?php echo $application['id']; ?>)">
                                        <span class="material-symbols-rounded">visibility</span>
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

        <!-- Rejected Applications Tab -->
    <div class="tab-pane fade" id="rejected-tab" role="tabpanel">
        <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Rejected Applications</h6>
            </div>
            </div>
            <div class="card-body px-0 pb-2">
            <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Applicant</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Specialization</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Documents</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Application Date</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Rejection Date</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reason</th>
                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rejected_applications as $application): ?>
                            <tr>
                                <td>
                                    <div class="d-flex px-2 py-1">
                                        <div>
                                            <img src="<?php echo $application['profile_picture'] ? '../../admin_operations/get_application_file.php?id=' . $application['id'] . '&type=profile' : '../../assets/img/default-avatar.png'; ?>" 
                                                class="avatar avatar-sm me-3 border-radius-lg" alt="user">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></h6>
                                            <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-xs font-weight-bold mb-0"><?php echo htmlspecialchars($application['specialization']); ?></p>
                                    <p class="text-xs text-secondary mb-0"><?php echo htmlspecialchars($application['experience']); ?></p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=license" class="text-xs">View License</a>
                                    <br>
                                    <a href="../../admin_operations/get_application_file.php?id=<?php echo $application['id']; ?>&type=resume" class="text-xs">View Resume</a>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">
                                        <?php echo date('M d, Y', strtotime($application['application_date'])); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-secondary text-xs font-weight-bold">
                                        <?php echo date('M d, Y', strtotime($application['review_date'])); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <span class="text-danger text-xs">
                                        <?php echo htmlspecialchars($application['review_notes']); ?>
                                    </span>
                                </td>
                                <td class="align-middle text-center">
                                    <button class="btn btn-sm btn-info" onclick="viewApplication(<?php echo $application['id']; ?>)">
                                        <span class="material-symbols-rounded">visibility</span>
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
</main>

<!-- View Application Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Application Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <h6>Personal Information</h6>
            <p><strong>Name:</strong> John Michael</p>
            <p><strong>Email:</strong> john@example.com</p>
            <p><strong>Phone:</strong> +1234567890</p>
          </div>
          <div class="col-md-6">
            <h6>Professional Information</h6>
            <p><strong>Specialization:</strong> Clinical Psychology</p>
            <p><strong>License Number:</strong> LIC-2024-001</p>
            <p><strong>Experience:</strong> 5 years</p>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-12">
            <h6>Experience Details</h6>
            <p class="text-sm">Lorem ipsum dolor sit amet, consectetur adipiscing elit...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Approve Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to approve this application?</p>
        <div class="form-group">
          <label>Review Notes (Optional)</label>
          <textarea class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success">Approve</button>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reject Application</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to reject this application?</p>
        <div class="form-group">
          <label>Rejection Reason (Required)</label>
          <textarea class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger">Reject</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">View Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="documentViewer"></div>
            </div>
        </div>
    </div>
</div>

<!-- Core JS Files -->
<script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets/js/material-dashboard.min.js"></script>
    <script src="../../assets/js/sign-out.js"></script>
    <script src="../../assets/js/updatestatus.js"></script>
    <script src="../../assets/js/archive-message.js"></script>
    <script src="../../assets/js/reply-message.js"></script>

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
    </script>

    <script>
        console.log('Page loaded');
        // Verify jQuery and other dependencies are loaded
        console.log('jQuery loaded:', typeof jQuery !== 'undefined');
        console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
    </script>

    <script>
        // Add this to verify Bootstrap is initialized
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Bootstrap version:', bootstrap.version);
            // Initialize all dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });
        });
    </script>

    <script>
        function approveApplication(id) {
            // First show confirmation dialog
            Swal.fire({
                title: 'Approve Application?',
                text: "Are you sure you want to approve this application?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, approve it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show notes input dialog
                    Swal.fire({
                        title: 'Add Review Notes',
                        input: 'textarea',
                        inputLabel: 'Notes (Optional)',
                        inputPlaceholder: 'Enter any additional notes...',
                        showCancelButton: true,
                        confirmButtonColor: '#28a745',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Submit'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            updateApplicationStatus(id, 'approved', result.value || '');
                        }
                    });
                }
            });
        }

        function rejectApplication(id) {
            // First show confirmation dialog
            Swal.fire({
                title: 'Reject Application?',
                text: "Are you sure you want to reject this application?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, reject it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show required notes input dialog
                    Swal.fire({
                        title: 'Rejection Reason',
                        input: 'textarea',
                        inputLabel: 'Reason (Required)',
                        inputPlaceholder: 'Enter the reason for rejection...',
                        inputValidator: (value) => {
                            if (!value || !value.trim()) {
                                return 'You must provide a reason for rejection!';
                            }
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Submit'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            updateApplicationStatus(id, 'rejected', result.value);
                        }
                    });
                }
            });
        }

        function updateApplicationStatus(id, status, notes) {
            const formData = new FormData();
            formData.append('application_id', id);
            formData.append('status', status);
            formData.append('review_notes', notes);

            // Show loading state
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait while we update the application status.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('../../admin_operations/update_therapist_application_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: status === 'approved' ? 
                            'Application has been approved and notification sent.' : 
                            'Application has been rejected and notification sent.',
                        confirmButtonColor: '#28a745'
                    }).then(() => {
                        location.reload(); // Refresh the page to show updated status
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to update application status',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An unexpected error occurred. Please try again.',
                    confirmButtonColor: '#d33'
                });
            });
        }

        function viewApplication(id) {
            // Show loading
            Swal.fire({
                title: 'Loading...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false
            });

            // Debug log
            console.log('Fetching application ID:', id);

            fetch(`../../admin_operations/get_application_details.php?id=${id}`)
                .then(async response => {
                    // Debug log
                    console.log('Response status:', response.status);
                    
                    const text = await response.text();
                    console.log('Raw response:', text);

                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON Parse Error:', e);
                        throw new Error('Invalid response format');
                    }
                })
                .then(data => {
                    // Debug log
                    console.log('Parsed data:', data);

                    if (data.error) {
                        throw new Error(data.message || 'Failed to load application');
                    }

                    // Create modal content
                    const modalContent = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary">Personal Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Name</th>
                                        <td>${data.first_name} ${data.last_name}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>${data.email}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone</th>
                                        <td>${data.phone}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary">Professional Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Specialization</th>
                                        <td>${data.specialization}</td>
                                    </tr>
                                    <tr>
                                        <th>License Number</th>
                                        <td>${data.license_number}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            <span class="badge bg-${
                                                data.status === 'pending' ? 'warning' : 
                                                data.status === 'approved' ? 'success' : 
                                                'danger'
                                            }">${data.status.toUpperCase()}</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Experience</h6>
                                <p class="text-sm">${data.experience}</p>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Documents</h6>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewDocument(${data.id}, 'license')">
                                        <i class="fas fa-file-pdf me-1"></i> View License
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="viewDocument(${data.id}, 'resume')">
                                        <i class="fas fa-file-alt me-1"></i> View Resume
                                    </button>
                                </div>
                            </div>
                        </div>

                        ${data.review_notes ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6 class="text-primary">Review Notes</h6>
                                    <p class="text-sm">${data.review_notes}</p>
                                </div>
                            </div>
                        ` : ''}

                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary">Application Timeline</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Submitted</th>
                                        <td>${new Date(data.application_date).toLocaleString()}</td>
                                    </tr>
                                    ${data.review_date ? `
                                        <tr>
                                            <th>Reviewed</th>
                                            <td>${new Date(data.review_date).toLocaleString()}</td>
                                        </tr>
                                    ` : ''}
                                </table>
                            </div>
                        </div>
                    `;

                    // Close loading
                    Swal.close();

                    // Update and show modal
                    const modalBody = document.querySelector('#viewModal .modal-body');
                    modalBody.innerHTML = modalContent;
                    
                    const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));
                    viewModal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message || 'Failed to load application details'
                    });
                });
        }

        // Function to view documents in a new modal
        function viewDocument(id, type) {
            const documentModal = new bootstrap.Modal(document.createElement('div'));
            documentModal.element.innerHTML = `
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${type.charAt(0).toUpperCase() + type.slice(1)} Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <iframe src="../../admin_operations/get_application_file.php?id=${id}&type=${type}"
                                    style="width: 100%; height: 600px; border: none;">
                            </iframe>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(documentModal.element);
            documentModal.show();
        }
    </script>
</body>
</html>