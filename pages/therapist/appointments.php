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

// Get therapist ID from session
$therapist_id = $_SESSION['user_id'];

try {
    // Fetch all therapy sessions for this therapist with student information
    $query = "SELECT 
        ts.*,
        s.firstname,
        s.lastname,
        s.email
    FROM therapy_sessions ts
    LEFT JOIN students s ON ts.srcode = s.srcode
    WHERE ts.therapist_id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$therapist_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count appointments by status
    $total_appointments = 0;
    $pending_count = 0;
    $confirmed_count = 0;
    $completed_count = 0;
    $cancelled_count = 0;

    foreach ($appointments as $appointment) {
        $total_appointments++;
        switch(strtolower($appointment['status'])) {
            case 'pending':
                $pending_count++;
                break;
            case 'confirmed':
                $confirmed_count++;
                break;
            case 'completed':
                $completed_count++;
                break;
            case 'cancelled':
                $cancelled_count++;
                break;
        }
    }

    // Debug - remove these later
    echo "<!-- Debug Counts: 
        Total: $total_appointments, 
        Pending: $pending_count, 
        Confirmed: $confirmed_count, 
        Completed: $completed_count, 
        Cancelled: $cancelled_count 
    -->";
} catch (PDOException $e) {
    error_log("Error fetching appointments: " . $e->getMessage());
    // Optionally set counts to 0 if query fails
    $total_appointments = $pending_count = $confirmed_count = $completed_count = $cancelled_count = 0;
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Appointments</title>

    <!-- Fonts -->
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    
    <!-- Core CSS -->
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    
    <!-- Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- Material Dashboard CSS with version number -->
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    
    <!-- Custom CSS - Must be last -->
    <link rel="stylesheet" href="../../assets/css/custom-swal.css">
    
    <!-- FontAwesome -->
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="../../assets/css/custom-swal.css" rel="stylesheet" />

    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0">
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

    <div class="container-fluid py-4">
    <div class="row justify-space-between py-2">
            <div class="col-lg-12 mx-auto">
                <div class="nav-wrapper position-relative end-0">
                <ul class="nav nav-pills nav-fill p-1" role="tablist">
    <!-- All Appointments -->
    <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center active" 
           data-bs-toggle="tab" 
           href="#all-appointments" 
           role="tab" 
           aria-selected="true">
            <i class="material-symbols-rounded text-sm me-2">calendar_month</i> 
            All Appointments
            <?php if ($total_appointments > 0): ?>
            <span class="badge badge-sm bg-gradient-primary"><?= $total_appointments ?></span>
            <?php endif; ?>
        </a>
    </li>
    
    <!-- Pending Appointments -->
    <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
           data-bs-toggle="tab" 
           href="#pending-appointments" 
           role="tab" 
           aria-selected="false">
            <i class="material-symbols-rounded text-sm me-2">pending</i> 
            Pending
            <?php if ($pending_count > 0): ?>
            <span class="badge badge-sm bg-gradient-warning"><?= $pending_count ?></span>
            <?php endif; ?>
        </a>
    </li>
    
    <!-- Confirmed Appointments -->
    <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
           data-bs-toggle="tab" 
           href="#confirmed-appointments" 
           role="tab" 
           aria-selected="false">
            <i class="material-symbols-rounded text-sm me-2">event_available</i> 
            Confirmed
            <?php if ($confirmed_count > 0): ?>
            <span class="badge badge-sm bg-gradient-info ms-2"><?= $confirmed_count ?></span>
            <?php endif; ?>
        </a>
    </li>
    
    <!-- Completed Appointments -->
    <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
           data-bs-toggle="tab" 
           href="#completed-appointments" 
           role="tab" 
           aria-selected="false">
            <i class="material-symbols-rounded text-sm me-2">task_alt</i> 
            Completed
            <?php if ($completed_count > 0): ?>
            <span class="badge badge-sm bg-gradient-success ms-2"><?= $completed_count ?></span>
            <?php endif; ?>
        </a>
    </li>
    
    <!-- Cancelled Appointments -->
    <li class="nav-item">
        <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
           data-bs-toggle="tab" 
           href="#cancelled-appointments" 
           role="tab" 
           aria-selected="false">
            <i class="material-symbols-rounded text-sm me-2">event_busy</i> 
            Cancelled
            <?php if ($cancelled_count > 0): ?>
            <span class="badge badge-sm bg-gradient-danger ms-2"><?= $cancelled_count ?></span>
            <?php endif; ?>
        </a>
    </li>
</ul>
                </div>
            </div>
        </div>
      <div class="row">
        <div class="col-12">
          <div class="card my-4">
            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
              <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Appointment Management</h6>
              </div>
            </div>
            <div class="card-body px-4 pb-2">

        
              <div class="tab-content" id="appointmentTabContent">
                <!-- All Appointments Tab -->
                <div class="tab-pane fade show active" id="all-appointments" role="tabpanel">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="allAppointmentsTable">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pending Appointments Tab -->
                <div class="tab-pane fade" id="pending-appointments" role="tabpanel">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingAppointmentsTable">
                                    <!-- Pending appointments will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Confirmed Appointments Tab -->
                <div class="tab-pane fade" id="confirmed-appointments" role="tabpanel">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="confirmedAppointmentsTable">
                                    <!-- Confirmed appointments will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Completed Appointments Tab -->
                <div class="tab-pane fade" id="completed-appointments" role="tabpanel">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="completedAppointmentsTable">
                                    <!-- Completed appointments will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Cancelled Appointments Tab -->
                <div class="tab-pane fade" id="cancelled-appointments" role="tabpanel">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Student</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date & Time</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                        <th class="text-secondary opacity-7">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="cancelledAppointmentsTable">
                                    <!-- Cancelled appointments will be loaded here -->
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
    <!-- Modal Container -->
    <div class="modal fade" id="appointmentModal" tabindex="-1"></div>
  </main>
<!-- At the bottom of your file, before closing </body> tag -->

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

<!-- Custom JS -->
<script src="../../assets/js/therapist-appointment.js"></script>
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
$(document).ready(function() {
    $('#appointmentsTable').DataTable({
        columns: [
            // Student SR Code column
            { 
                data: 'srcode',
                render: function(data, type, row) {
                    return `<p class="mb-0">SR-${data}</p>`;
                }
            },
            // Date & Time column
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        ${row.session_date}<br>
                        <small class="text-muted">${row.session_time}</small>
                    `;
                }
            },
            // Status column
            {
                data: 'status',
                render: function(data, type, row) {
                    return `<span class="badge badge-sm bg-gradient-${getStatusColor(data)}">${data}</span>`;
                }
            },
            // Session Type column
            {
                data: 'session_type',
                render: function(data, type, row) {
                    return `<span class="text-secondary text-xs font-weight-bold">${data}</span>`;
                }
            },
            // Actions column
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <div class="dropdown">
                            <button class="btn bg-gradient-info btn-sm" 
                                    type="button" 
                                    data-bs-toggle="dropdown" 
                                    title="Change Status">
                                <i class="material-symbols-rounded">edit_note</i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateAppointmentStatus(${row.session_id}, 'pending')">Mark as Pending</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateAppointmentStatus(${row.session_id}, 'confirmed')">Mark as Confirmed</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateAppointmentStatus(${row.session_id}, 'completed')">Mark as Completed</a></li>
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="updateAppointmentStatus(${row.session_id}, 'cancelled')">Mark as Cancelled</a></li>
                            </ul>
                        </div>
                    `;
                }
            }
        ],
        order: [[1, 'asc']], // Order by date column
        pageLength: 10,
        responsive: true
    });
});
</script>
</body>
</html>
