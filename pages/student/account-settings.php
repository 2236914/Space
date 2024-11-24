<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session variables
error_log("Current Session Variables: " . print_r($_SESSION, true));

// Use correct relative paths
require_once __DIR__ . '/../../configs/config.php';
define('ALLOW_ACCESS', true);
require_once __DIR__ . '/../../admin_operations/profile_operations.php';
require_once __DIR__ . '/../../admin_operations/SessionLogger.php';

// Initialize ProfileOperations
$profileOps = new ProfileOperations($pdo);
$sessionLogger = new SessionLogger($pdo);

// Fetch current student data
$studentData = $profileOps->getStudentData($_SESSION['user_id']);

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
                <a data-bs-toggle="collapse" href="#dashboardsExamples" class="nav-link text-dark" aria-controls="dashboardsExamples" role="button" aria-expanded="false">
                    <i class="material-symbols-rounded opacity-5">space_dashboard</i>
                    <span class="nav-link-text ms-1 ps-1">Dashboard</span>
                </a>
                <div class="collapse" id="dashboardsExamples">
                    <ul class="nav">
                        <li class="nav-item">
                            <a class="nav-link text-dark" href="student.php">
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
              <a data-bs-toggle="collapse" href="#account" class="nav-link text-dark active" aria-controls="account" role="button" aria-expanded="false">
                    <i class="material-symbols-rounded opacity-5">account_circle</i>
                    <span class="nav-link-text ms-1 ps-1">Account</span>
                </a>
                <div class="collapse show" id="account">
                    <ul class="nav">
                        <li class="nav-item active">
                            <a class="nav-link text-dark active" href="account-settings.php">
                                <span class="sidenav-mini-icon">S</span>
                                <span class="sidenav-normal ms-1 ps-1">Settings</span>
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
          <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Account</a></li>
          <li class="breadcrumb-item text-sm text-dark active font-weight-bold" aria-current="page">Settings</li>
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
                <span class="small">3</span>
                <span class="visually-hidden">unread notifications</span>
              </span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end p-2 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                    <a class="dropdown-item border-radius-md" href="calendar.php">
                        <div class="d-flex align-items-center py-1">
                            <span class="material-symbols-rounded">calendar_month</span>
                            <div class="ms-2">
                                <h6 class="text-sm font-weight-normal my-auto">
                                    Upcoming Counseling Session
                                </h6>
                            </div>
                        </div>
                    </a>
                </li>
                <li class="mb-2">
                    <a class="dropdown-item border-radius-md" href="mood-tracker.php">
                        <div class="d-flex align-items-center py-1">
                            <span class="material-symbols-rounded">mood</span>
                            <div class="ms-2">
                                <h6 class="text-sm font-weight-normal my-auto">
                                    Daily Mood Check Reminder
                                </h6>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item border-radius-md" href="support.php">
                        <div class="d-flex align-items-center py-1">
                            <span class="material-symbols-rounded">support_agent</span>
                            <div class="ms-2">
                                <h6 class="text-sm font-weight-normal my-auto">
                                    New Message from Counselor
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
    <div class="container-fluid my-3 py-4">
        <div class="row mb-5">
            <div class="col-lg-12 mt-lg-0 mt-4">
                <!-- Nav Pills -->
                <div class="col-12">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        person
                                    </span>
                                    Profile
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        lock
                                    </span>
                                    Password
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#notifications" role="tab" aria-controls="notifications" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        campaign
                                    </span>
                                    Notifications
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#sessions" role="tab" aria-controls="sessions" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        settings_applications
                                    </span>
                                    Sessions
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#deactivate" role="tab" aria-controls="deactivate" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        delete
                                    </span>
                                    Deactivate
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tab Content Container -->
                <div class="tab-content py-4">
                    <!-- Profile Tab Content -->
                    <div class="tab-pane fade show active" id="profile" role="tabpanel">
                        <!--id: Profile -->
                        <div class="card" id="profile">
                          <div class="card-body">
                              <!-- Profile Section -->
                              <div class="row mb-4">
                                  <div class="col-auto">
                                      <div class="position-relative start-0">
                                          <form id="profilePictureForm" enctype="multipart/form-data">
                                              <input type="file" id="profilePictureInput" name="profile_picture" 
                                                     style="display: none;" 
                                                     accept=".jpg, .jpeg, .png">
                                              <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                                                   alt="profile" 
                                                   id="previewImage"
                                                   class="border-radius-lg shadow shadow-dark mt-n4 avatar avatar-xxl border-radius-xl" 
                                                   style="width: 120px;">
                                              <a href="javascript:;" class="btn btn-sm btn-icon-only bg-gradient-dark position-absolute bottom-0 end-0 mb-n2 me-n2"
                                                 onclick="document.getElementById('profilePictureInput').click();">
                                                   <span class="material-symbols-rounded text-xs top-0 mt-n2" data-bs-toggle="tooltip" data-bs-placement="top" 
                                                         title="Edit Image" aria-hidden="true" data-bs-original-title="Edit Image" aria-label="Edit Image">
                                                       edit
                                                   </span>
                                              </a>
                                          </form>
                                      </div>
                                  </div>
                                  <div class="col-sm-auto col-8 my-auto">
                                      <div class="h-100">
                                          <h5 class="mb-1 font-weight-bolder">
                                              <?php echo htmlspecialchars($studentData['firstname'] . ' ' . $studentData['lastname']); ?>
                                          </h5>
                                          <small class="text-muted">
                                              <?php echo htmlspecialchars($studentData['username']); ?>
                                          </small>
                                      </div>
                                  </div>
                                  <div class="col-sm-auto ms-sm-auto mt-sm-0 mt-3">
                                      <span class="badge bg-gradient-primary">
                                          <?php echo htmlspecialchars($studentData['srcode']); ?> 
                                          <span class="mx-2">|</span> 
                                          <span style="color: <?php echo ($studentData['status'] === 'active') ? '#ffffff' : '#ff4d4d'; ?>">
                                              <?php echo strtoupper($studentData['status']); ?>
                                          </span>
                                      </span>
                                  </div>
                              </div>
                              <hr class="horizontal dark">
                      
                              <!-- Profile Form -->
                              <form id="profile-form" method="POST" action="../../admin_operations/process_profile_update.php">
                                  <!-- Hidden input for srcode -->
                                  <input type="hidden" name="srcode" value="<?php echo htmlspecialchars($studentData['srcode']); ?>">
                                  
                                  <!-- Name Row -->
                                  <div class="row mt-4">
                                      <div class="col-6">
                                          <div class="input-group input-group-static">
                                              <label>First Name</label>
                                              <input type="text" 
                                                     name="firstname" 
                                                     class="form-control" 
                                                     pattern="[A-Za-z\s]+"
                                                     title="Only letters and spaces are allowed"
                                                     value="<?php echo htmlspecialchars($studentData['firstname']); ?>"
                                                     required>
                                          </div>
                                      </div>
                                      <div class="col-6">
                                          <div class="input-group input-group-static">
                                              <label>Last Name</label>
                                              <input type="text" 
                                                     name="lastname" 
                                                     class="form-control" 
                                                     pattern="[A-Za-z\s]+"
                                                     title="Only letters and spaces are allowed"
                                                     value="<?php echo htmlspecialchars($studentData['lastname']); ?>"
                                                     required>
                                          </div>
                                      </div>
                                  </div>
                      
                                  <!-- Contact Info Row -->
                                  <div class="row mt-4">
                                      <div class="col-6">
                                          <div class="input-group input-group-static">
                                              <label>Phone Number</label>
                                              <input type="tel" name="phonenum" class="form-control" 
                                                     value="<?php echo isset($studentData['phonenum']) ? htmlspecialchars($studentData['phonenum']) : ''; ?>" 
                                                     pattern="09[0-9]{9}" placeholder="09XXXXXXXXX">
                                          </div>
                                      </div>
                                      <div class="col-6">
                                          <div class="input-group input-group-static">
                                              <label>Email</label>
                                              <input type="email" name="email" class="form-control" 
                                                     value="<?php echo htmlspecialchars($studentData['email']); ?>">
                                          </div>
                                      </div>
                                  </div>

                                  <!-- Academic Info Row -->
                                  <div class="row mt-4">
                                      <div class="col-4">
                                          <div class="input-group input-group-static">
                                              <label>Course</label>
                                              <input type="text" name="course" class="form-control" 
                                                     value="<?php echo isset($studentData['course']) ? htmlspecialchars($studentData['course']) : ''; ?>">
                                          </div>
                                      </div>
                                      <div class="col-4">
                                          <div class="input-group input-group-static">
                                              <label>Year</label>
                                              <select name="year" class="form-control">
                                                  <option value="">Select Year</option>
                                                  <option value="1" <?php echo (isset($studentData['year']) && $studentData['year'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                                                  <option value="2" <?php echo (isset($studentData['year']) && $studentData['year'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                                                  <option value="3" <?php echo (isset($studentData['year']) && $studentData['year'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                                                  <option value="4" <?php echo (isset($studentData['year']) && $studentData['year'] == '4') ? 'selected' : ''; ?>>4th Year</option>
                                              </select>
                                          </div>
                                      </div>
                                      <div class="col-4">
                                          <div class="input-group input-group-static">
                                              <label>Section</label>
                                              <input type="text" name="section" class="form-control" 
                                                     value="<?php echo isset($studentData['section']) ? htmlspecialchars($studentData['section']) : ''; ?>">
                                          </div>
                                      </div>
                                  </div>

                                  <!-- Department and Personality Row -->
                                  <div class="row mt-4">
                                      <!-- Department Column -->
                                      <div class="col-6">
                                          <div class="input-group input-group-static flex-column">
                                              <label class="mb-2">Department</label>
                                              <select name="department" class="form-control" style="max-width: 300px;">
                                                  <option value="">Select Department</option>
                                                  <option value="CABE" <?php echo (isset($studentData['department']) && $studentData['department'] == 'CABE') ? 'selected' : ''; ?>>CABE</option>
                                                  <option value="CAS" <?php echo (isset($studentData['department']) && $studentData['department'] == 'CAS') ? 'selected' : ''; ?>>CAS</option>
                                                  <option value="COE" <?php echo (isset($studentData['department']) && $studentData['department'] == 'COE') ? 'selected' : ''; ?>>COE</option>
                                                  <option value="CIT" <?php echo (isset($studentData['department']) && $studentData['department'] == 'CIT') ? 'selected' : ''; ?>>CIT</option>
                                                  <option value="CICS" <?php echo (isset($studentData['department']) && $studentData['department'] == 'CICS') ? 'selected' : ''; ?>>CICS</option>
                                                  <option value="CTE" <?php echo (isset($studentData['department']) && $studentData['department'] == 'CTE') ? 'selected' : ''; ?>>CTE</option>
                                              </select>
                                          </div>
                                      </div>

                                      <!-- Personality Column -->
                                      <div class="col-6">
                                          <div class="input-group input-group-static flex-column">
                                              <label class="mb-2">Personality Type</label>
                                              <select name="personality" class="form-control" style="max-width: 300px;">
                                                  <option value="">Select Personality Type</option>
                                                  <?php
                                                  $personalities = [
                                                      'INTJ' => 'Architect',
                                                      'INTP' => 'Logician',
                                                      'ENTJ' => 'Commander',
                                                      'ENTP' => 'Debater',
                                                      'INFJ' => 'Advocate',
                                                      'INFP' => 'Mediator',
                                                      'ENFJ' => 'Protagonist',
                                                      'ENFP' => 'Campaigner',
                                                      'ISTJ' => 'Logistician',
                                                      'ISFJ' => 'Defender',
                                                      'ESTJ' => 'Executive',
                                                      'ESFJ' => 'Consul',
                                                      'ISTP' => 'Virtuoso',
                                                      'ISFP' => 'Adventurer',
                                                      'ESTP' => 'Entrepreneur',
                                                      'ESFP' => 'Entertainer'
                                                  ];

                                                  foreach ($personalities as $code => $type) {
                                                      $selected = (isset($studentData['personality']) && $studentData['personality'] == $code) ? 'selected' : '';
                                                      echo "<option value=\"$code\" $selected>$code - $type</option>";
                                                  }
                                                  ?>
                                              </select>
                                          </div>
                                      </div>
                                  </div>

                                  <!-- Address Row -->
                                  <div class="row mt-4">
                                      <div class="col-12">
                                          <div class="input-group input-group-static">
                                              <label>Address</label>
                                              <input type="text" name="address" class="form-control" 
                                                     value="<?php echo isset($studentData['address']) ? htmlspecialchars($studentData['address']) : ''; ?>" 
                                                     placeholder="Street Address, City, Province">
                                          </div>
                                      </div>
                                  </div>

                                  <!-- Save Changes Button (Only one at the bottom) -->
                                  <<div class="row mt-4">
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn bg-gradient-primary btn-sm mb-0" id="saveChangesBtn">
                                            <i class="material-symbols-rounded text-sm">save</i>
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                              </form>
                          </div>
                      </div>
                    </div>

                    <!-- Password Tab Content -->
                    <div class="tab-pane fade" id="password" role="tabpanel">
                        <div class="card mt-4" id="password">
                            <div class="card-header">
                                <h5>Change Password</h5>
                            </div>
                            <div class="card-body pt-0">
                                <form id="password-form">
                                    <!-- Current Password -->
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">Current password</label>
                                        <input type="password" class="form-control" id="current-password" name="current_password" required>
                                        <button type="button" class="btn btn-link position-absolute end-0 px-3" onclick="togglePassword('current-password')">
                                            <i class="material-symbols-rounded" style="font-size: 1.1rem; vertical-align: middle;">visibility_off</i>
                                        </button>
                                    </div>

                                    <!-- New Password -->
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">New password</label>
                                        <input type="password" class="form-control" id="new-password" name="new_password" required>
                                        <button type="button" class="btn btn-link position-absolute end-0 px-3" onclick="togglePassword('new-password')">
                                            <i class="material-symbols-rounded" style="font-size: 1.1rem; vertical-align: middle;">visibility_off</i>
                                        </button>
                                    </div>

                                    <!-- Confirm New Password -->
                                    <div class="input-group input-group-outline my-3">
                                        <label class="form-label">Confirm New password</label>
                                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" required>
                                        <button type="button" class="btn btn-link position-absolute end-0 px-3" onclick="togglePassword('confirm-password')">
                                            <i class="material-symbols-rounded" style="font-size: 1.1rem; vertical-align: middle;">visibility_off</i>
                                        </button>
                                    </div>

                                    <!-- Password Requirements -->
                                    <h5 class="mt-5">Password requirements</h5>
                                    <p class="text-muted mb-2">Please follow this guide for a strong password:</p>
                                    <ul class="text-muted ps-4 mb-0 float-start list-unstyled" id="password-requirements">
                                        <li><span class="text-sm" id="special-char"><i class="material-symbols-rounded me-2">close</i>One special character</span></li>
                                        <li><span class="text-sm" id="min-length"><i class="material-symbols-rounded me-2">close</i>Min 6 characters</span></li>
                                        <li><span class="text-sm" id="has-number"><i class="material-symbols-rounded me-2">close</i>One number (2 are recommended)</span></li>
                                    </ul>

                                    <button type="submit" class="btn bg-gradient-primary btn-sm float-end mt-6 mb-0">
                                        Update password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Tab Content -->
                    <div class="tab-pane fade" id="notifications" role="tabpanel">
                        <!--id: Notifications -->
                        <div class="card mt-4" id="notifications">
                          <div class="card-header">
                            <h6>Notifications</h6>
                            <p class="text-sm">Choose how you receive notifications. These notification settings apply to the things youre watching.</p>
                          </div>
                          <div class="card-body pt-0">
                            <div class="table-responsive">
                              <table class="table mb-0">
                                <thead>
                                  <tr>
                                    <th class="ps-1" colspan="4">
                                      <p class="mb-0">Activity</p>
                                    </th>
                                    <th class="text-center">
                                      <p class="mb-0">Email</p>
                                    </th>
                                    <th class="text-center">
                                      <p class="mb-0">Push</p>
                                    </th>
                                    <th class="text-center">
                                      <p class="mb-0">SMS</p>
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr>
                                    <td class="ps-1" colspan="4">
                                      <div class="my-auto">
                                        <span class="text-dark d-block text-sm">Session Schedule</span>
                                        <span class="text-xs font-weight-normal">Appointment reminders and confirmations for upcoming counseling sessions</span>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault11" disabled>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault12" disabled>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault13" disabled>
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td class="ps-1" colspan="4">
                                      <div class="my-auto">
                                        <span class="text-dark d-block text-sm">Community</span>
                                        <span class="text-xs font-weight-normal">Updates on responses, mentions, and interactions in community discussions</span>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault14" disabled>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault15" disabled>
                                      </div>
                                    </td>
                                    <td>
                                      <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault16" disabled>
                                      </div>
                                    </td>
                                  </tr>
                                  <tr>
                                <td class="ps-1" colspan="4">
                                    <div class="my-auto">
                                    <span class="text-dark d-block text-sm">Announcements</span>
                                    <span class="text-xs font-weight-normal">Important updates, system notifications, and general announcements</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                    <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault17" disabled>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                    <input class="form-check-input" checked type="checkbox" id="flexSwitchCheckDefault18" disabled>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckDefault19" disabled>
                                    </div>
                                </td>
                                </tr>
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                    </div>

                    <!-- Sessions Tab Content -->
                    <div class="tab-pane fade" id="sessions" role="tabpanel">
                        <div class="card mt-4" id="sessions">
                            <div class="card-header pb-3">
                                <h5>Session History</h5>
                                <p class="text-sm">This is a list of your recent login sessions. Review them to ensure your account security.</p>
                            </div>
                            <div class="card-body pt-0">
                                <?php
                                // Get recent sessions with device info
                                $stmt = $pdo->prepare("
                                    SELECT 
                                        session_id,
                                        login_time,
                                        logout_time,
                                        ip_address,
                                        session_status
                                    FROM session_logs 
                                    WHERE srcode = ? 
                                    ORDER BY login_time DESC 
                                    LIMIT 10
                                ");
                                $stmt->execute([$_SESSION['user_id']]);
                                $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if (empty($sessions)): ?>
                                    <div class="text-center py-4">
                                        <p class="text-muted">No session history available</p>
                                    </div>
                                <?php else: 
                                    foreach ($sessions as $session): 
                                        $isActive = $session['session_status'] === 'active';
                                        $duration = '';
                                        if ($session['logout_time']) {
                                            $start = new DateTime($session['login_time']);
                                            $end = new DateTime($session['logout_time']);
                                            $diff = $start->diff($end);
                                            $duration = $diff->format('%H:%I:%S');
                                        }
                                ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="text-center">
                                            <i class="material-symbols-rounded text-lg opacity-6">
                                                <?php echo $isActive ? 'computer' : 'logout'; ?>
                                            </i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0 text-sm">
                                                    <?php echo htmlspecialchars($session['ip_address']); ?>
                                                    <?php if ($isActive && isset($_SESSION['current_session_id']) && $session['session_id'] == $_SESSION['current_session_id']): ?>
                                                        <span class="text-xs text-primary ms-1">(Current Session)</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <span class="badge badge-sm <?php echo $isActive ? 'bg-gradient-success' : 'bg-gradient-secondary'; ?>">
                                                    <?php echo ucfirst($session['session_status']); ?>
                                                </span>
                                            </div>
                                            <p class="text-xs text-muted mb-0">
                                                <span class="font-weight-bold">Login:</span> 
                                                <?php echo date('M d, Y h:i A', strtotime($session['login_time'])); ?>
                                                <?php if ($session['logout_time']): ?>
                                                    <br>
                                                    <span class="font-weight-bold">Logout:</span> 
                                                    <?php echo date('M d, Y h:i A', strtotime($session['logout_time'])); ?>
                                                    <br>
                                                    <span class="font-weight-bold">Duration:</span> 
                                                    <?php echo $duration; ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php if (!$isActive): ?>
                                        <hr class="horizontal dark my-3">
                                    <?php endif; ?>
                                <?php endforeach; 
                                endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Deactivate Tab Content -->
                    <div class="tab-pane fade" id="deactivate" role="tabpanel">
                        <div class="card mt-4" id="deactivate">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-sm-0 mb-4">
                                    <div class="w-50">
                                        <h5>Deactivate Account</h5>
                                        <p class="text-sm mb-0">Temporarily disable your account. You can reactivate it anytime.</p>
                                    </div>
                                    <div class="w-50 text-end">
                                        <form method="POST" action="../../admin_operations/deactivate_account.php" id="deactivateForm">
                                            <input type="hidden" name="deactivate_account" value="1">
                                            <button class="btn btn-outline-secondary mb-0" type="button" 
                                                    onclick="confirmDeactivation()">
                                                Deactivate
                                            </button>
                                        </form>
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
  <script src="../../assets/js/plugins/choices.min.js"></script>
  <script>
    const choices = new Choices('[data-trigger]', {
      searchResultLimit: 5,
      renderChoiceLimit: 5
    });
  </script>

  
  <script<script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script>
// Image preview and upload functionality
document.getElementById('profilePictureInput').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const reader = new FileReader();
    reader.onload = async function(event) {
        try {
            const result = await Swal.fire({
                title: 'Upload Profile Picture?',
                text: 'Do you want to upload this image as your profile picture?',
                imageUrl: event.target.result,
                imageWidth: 200,
                imageHeight: 200,
                imageAlt: 'Profile picture preview',
                showCancelButton: true,
                confirmButtonText: 'Yes, upload it!',
                cancelButtonText: 'Cancel',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mx-2',
                    cancelButton: 'btn btn-outline-secondary btn-sm mx-2'
                }
            });

            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Uploading...',
                    text: 'Please wait while we process your image.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('profile_picture', file);

                const response = await fetch('../../admin_operations/update_profile_picture.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Show success message
                    await Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK',
                        buttonsStyling: false,
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary btn-sm mx-2'
                        }
                    });
                    
                    // Reload the page after clicking OK
                    window.location.reload();
                    
                } else {
                    throw new Error(data.message || 'Failed to update profile picture');
                }
            }
        } catch (error) {
            console.error('Upload error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: error.message || 'An error occurred while uploading the image.',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mx-2'
                }
            });
        }
    };
    reader.readAsDataURL(file);
});
</script>
  <script>
    // Function to show toast
    function showToast(toastId, message = '') {
        const toast = document.getElementById(toastId);
        if (message) {
            toast.querySelector('.toast-body').textContent = message;
        }
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // Function to validate phone number format
    function validatePhone(phone) {
        const phoneRegex = /^09\d{9}$/;
        return phoneRegex.test(phone);
    }

    // Function to validate BatState-U email format
    function validateEmail(email) {
        const emailRegex = /@g\.batstate-u\.edu\.ph$/;
        return emailRegex.test(email);
    }

    // Form submission handler
    document.getElementById('profile-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Get form values
        const email = this.querySelector('input[name="email"]').value;
        const phone = this.querySelector('input[name="phonenum"]').value;
        const requiredFields = ['firstname', 'lastname', 'email', 'phonenum', 'department'];
        
        // Function to show toast
        function showToast(type, message) {
            const toast = document.createElement('div');
            toast.className = `toast fade p-2 mt-2 ${type === 'warning' || type === 'danger' ? 'bg-white' : 'bg-gradient-' + type}`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            let icon;
            switch(type) {
                case 'warning':
                    icon = 'travel_explore';
                    break;
                case 'danger':
                    icon = 'campaign';
                    break;
                case 'success':
                    icon = 'check';
                    break;
                default:
                    icon = 'notifications';
            }

            toast.innerHTML = `
                <div class="toast-header ${type !== 'warning' && type !== 'danger' ? 'bg-transparent border-0' : 'border-0'}">
                    <i class="material-symbols-rounded ${type !== 'warning' && type !== 'danger' ? 'text-white' : 'text-' + type} me-2">
                        ${icon}
                    </i>
                    <span class="me-auto ${type !== 'warning' && type !== 'danger' ? 'text-white' : ''} font-weight-bold">
                        Validation Error
                    </span>
                    <small class="${type !== 'warning' && type !== 'danger' ? 'text-white' : 'text-body'}">Just now</small>
                    <i class="fas fa-times text-md ${type !== 'warning' && type !== 'danger' ? 'text-white' : ''} ms-3 cursor-pointer" 
                       data-bs-dismiss="toast" aria-label="Close"></i>
                </div>
                <hr class="horizontal ${type !== 'warning' && type !== 'danger' ? 'light' : 'dark'} m-0">
                <div class="toast-body ${type !== 'warning' && type !== 'danger' ? 'text-white' : ''}">
                    ${message}
                </div>
            `;

            const toastContainer = document.querySelector('.position-fixed.bottom-1.end-1.z-index-2');
            toastContainer.appendChild(toast);

            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            // Remove toast after it's hidden
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Check for empty required fields
        const emptyFields = requiredFields.filter(field => {
            const value = this.querySelector(`[name="${field}"]`).value.trim();
            return value === '';
        });

        if (emptyFields.length > 0) {
            showToast('warning', 'Please fill in all required fields');
            return;
        }

        // Validate email format
        if (!email.endsWith('@g.batstate-u.edu.ph')) {
            showToast('danger', 'Please use your BatState-U email (@g.batstate-u.edu.ph)');
            return;
        }

        // Validate phone number format (PH)
        const phoneRegex = /^09\d{9}$/;
        if (!phoneRegex.test(phone)) {
            showToast('warning', 'Please enter a valid Philippine mobile number (e.g., 09123456789)');
            return;
        }

        // If validations pass, proceed with confirmation
        const result = await Swal.fire({
            title: 'Update Profile?',
            text: 'Are you sure you want to update your profile information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn bg-gradient-primary btn-sm mb-0 mx-2',
                cancelButton: 'btn bg-gradient-secondary btn-sm mb-0 mx-2',
                actions: 'mt-3',
                popup: 'px-3'
            },
            buttonsStyling: false
        });

        // If user confirms, proceed with form submission
        if (result.isConfirmed) {
            const formData = new FormData(this);
            
            try {
                const response = await fetch('../../admin_operations/process_profile_update.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary btn-sm mb-0',
                        },
                        buttonsStyling: false
                    }).then(() => {
                        // Reload page or update UI as needed
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn bg-gradient-primary btn-sm mb-0',
                        },
                        buttonsStyling: false
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while updating the profile.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mb-0',
                    },
                    buttonsStyling: false
                });
            }
        }
    });

    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        });
    });

    // Password form submission
    document.getElementById('password-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        // Get form values
        const currentPassword = document.getElementById('current-password').value;
        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Basic validation
        if (newPassword !== confirmPassword) {
            Swal.fire({
                title: 'Error!',
                text: 'New passwords do not match!',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        // Password strength validation
        const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{6,}$/;
        if (!passwordRegex.test(newPassword)) {
            Swal.fire({
                title: 'Invalid Password',
                text: 'Password must contain at least 6 characters, including one letter, one number, and one special character.',
                icon: 'warning',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        // Confirmation dialog
        const result = await Swal.fire({
            title: 'Change Password?',
            text: 'Are you sure you want to change your password?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn bg-gradient-primary btn-sm mb-0 mx-2',
                cancelButton: 'btn bg-gradient-secondary btn-sm mb-0 mx-2',
                actions: 'mt-3',
                popup: 'px-3'
            },
            buttonsStyling: false
        });

        if (!result.isConfirmed) {
            return;
        }

        try {
            const response = await fetch('../../admin_operations/process_password_update.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
                })
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                    },
                    buttonsStyling: false
                }).then(() => {
                    // Clear the form
                    this.reset();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                    },
                    buttonsStyling: false
                });
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while updating the password.',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
        }
    });

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = event.currentTarget.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.textContent = 'visibility';
        } else {
            input.type = 'password';
            icon.textContent = 'visibility_off';
        }
    }

    // Add this to your existing JavaScript
    document.getElementById('new-password').addEventListener('input', function() {
        const password = this.value;
        
        // Special character check
        const specialChar = document.getElementById('special-char');
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        updateRequirement(specialChar, hasSpecialChar);

        // Minimum length check
        const minLength = document.getElementById('min-length');
        const hasMinLength = password.length >= 6;
        updateRequirement(minLength, hasMinLength);

        // Number check
        const hasNumber = document.getElementById('has-number');
        const containsNumber = /\d/.test(password);
        updateRequirement(hasNumber, containsNumber);
    });

    function updateRequirement(element, isMet) {
        const icon = element.querySelector('i');
        if (isMet) {
            element.classList.remove('text-danger');
            element.classList.add('text-success');
            icon.textContent = 'check';
        } else {
            element.classList.remove('text-success');
            element.classList.add('text-danger');
            icon.textContent = 'close';
        }
    }

    // Update your password validation in the form submission
    document.getElementById('password-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const newPassword = document.getElementById('new-password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        // Check if all requirements are met
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(newPassword);
        const hasMinLength = newPassword.length >= 6;
        const hasNumber = /\d/.test(newPassword);

        if (!hasSpecialChar || !hasMinLength || !hasNumber) {
            Swal.fire({
                title: 'Invalid Password',
                text: 'Please meet all password requirements',
                icon: 'warning',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        // Check if passwords match
        if (newPassword !== confirmPassword) {
            Swal.fire({
                title: 'Error!',
                text: 'New passwords do not match!',
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        // Rest of your existing form submission code...
    });

    function confirmDeactivation() {
        Swal.fire({
            title: 'Deactivate Account?',
            text: "You are about to deactivate your account. You can reactivate it anytime by logging in.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, deactivate',
            cancelButtonText: 'No, cancel',
            reverseButtons: true,
            customClass: {
                confirmButton: 'btn bg-gradient-danger mb-0 ms-2',
                cancelButton: 'btn bg-gradient-secondary mb-0',
                actions: 'mt-3',
                popup: 'px-3'
            },
            buttonsStyling: false,
            padding: '2em'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add console log for debugging
                console.log('Form submission triggered');
                
                // Get the form element
                const form = document.getElementById('deactivateForm');
                
                // Add console log to verify form exists
                console.log('Form found:', form);
                
                // Submit the form
                if (form) {
                    form.submit();
                } else {
                    console.error('Form not found!');
                }
            } else {
                console.log('Deactivation cancelled');
            }
        });
    }

    // Add event listener to prevent default form submission
    document.getElementById('deactivateForm').addEventListener('submit', function(event) {
        console.log('Form submit event triggered');
    });

    document.getElementById('profilePictureInput').addEventListener('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File Type',
                text: 'Please upload only JPG, JPEG, or PNG images.',
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mx-2'
                }
            });
            this.value = ''; // Reset file input
            return;
        }
        
        // Create preview immediately after file selection
        const reader = new FileReader();
        reader.onload = function(event) {
            // Update the image source with the selected file
            document.getElementById('previewImage').src = event.target.result;
        };
        reader.readAsDataURL(file); // Convert the file to base64 string
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  </script>
  <div class="position-fixed bottom-1 end-1 z-index-2"></div>
  <script>
<?php if (isset($_GET['error']) && $_GET['error'] === 'deactivation_failed'): ?>
    Swal.fire({
        title: 'Deactivation Failed',
        text: <?php echo isset($_GET['reason']) ? 
               json_encode("Error: " . htmlspecialchars($_GET['reason'])) : 
               '"Failed to deactivate account. Please try again or contact support."'; ?>,
        icon: 'error',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
        },
        buttonsStyling: false
    });
<?php endif; ?>
</script>
<script>
// Function to capitalize first letter of each word
function capitalizeWords(input) {
    return input.value
        .toLowerCase()
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}

// Add event listeners to name fields
document.addEventListener('DOMContentLoaded', function() {
    const firstNameInput = document.querySelector('input[name="firstname"]');
    const lastNameInput = document.querySelector('input[name="lastname"]');

    // Function to handle input changes
    function handleNameInput(e) {
        const input = e.target;
        input.value = capitalizeWords(input);
    }

    // Add event listeners for both blur (when leaving the field) and input events
    if (firstNameInput) {
        firstNameInput.addEventListener('blur', handleNameInput);
        firstNameInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[0-9]/g, ''); // Remove numbers
        });
    }

    if (lastNameInput) {
        lastNameInput.addEventListener('blur', handleNameInput);
        lastNameInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[0-9]/g, ''); // Remove numbers
        });
    }
});
</script>
<script>
    // Add this function before the closingtag
    async function handleSignOut() {
        // Show confirmation dialog
        const result = await Swal.fire({
            title: 'Sign Out?',
            text: 'Are you sure you want to sign out?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, sign out',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn bg-gradient-primary btn-sm mb-0 mx-2',
                cancelButton: 'btn bg-gradient-secondary btn-sm mb-0 mx-2',
                actions: 'mt-3',
                popup: 'px-3'
            },
            buttonsStyling: false
        });

        // If user confirms, proceed with sign out
        if (result.isConfirmed) {
            try {
                // Show loading state
                Swal.fire({
                    title: 'Signing Out...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Perform sign out
                const response = await fetch('../../admin_operations/logout.php');
                if (response.ok) {
                    window.location.href = '../signin.php';
                } else {
                    throw new Error('Sign out failed');
                }
            } catch (error) {
                console.error('Sign out error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to sign out. Please try again.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                    },
                    buttonsStyling: false
                });
            }
        }
    }
</script>
</body>
</html>


