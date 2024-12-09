<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session variables
error_log("Current Session Variables: " . print_r($_SESSION, true));

// Use correct relative paths
require_once '../../includes/navigation_components.php';
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
    Account Settings
  </title>
  <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
  <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
</head>

<body class="g-sidenav-show  bg-gray-100">
<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2 bg-white my-2" id="sidenav-main">
    <!-- Header -->
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-xl-none" aria-hidden="true" id="iconSidenav"></i>
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
                <a href="#ProfileNav" class="nav-link text-dark" aria-controls="ProfileNav">
                    <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" 
                         class="avatar"
                         onerror="this.src='../../assets/img/default-avatar.png';">
                    <span class="nav-link-text ms-2 ps-1">
                        <?php 
                        if (isset($_SESSION['user_id'])) {
                            if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
                                echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                            } else {
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
            </li>
            <hr class="horizontal dark mt-0">

            <!-- Menu Items -->
            <?php foreach ($menu_items as $link => $item): ?>
                <?php if (isset($item['type']) && $item['type'] === 'divider'): ?>
                    <hr class="horizontal dark mt-0">
                <?php else: ?>
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
                                <ul class="nav">
                                    <?php foreach ($item['submenu'] as $sublink => $subitem): ?>
                                        <li class="nav-item">
                                            <a class="nav-link text-dark <?= $current_page == $sublink ? 'active' : ''; ?>" 
                                               href="<?= $sublink ?>">
                                                <span class="sidenav-mini-icon"><?= $subitem['mini'] ?></span>
                                                <span class="sidenav-normal ms-1 ps-1"><?= $subitem['text'] ?></span>
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
                <?php endif; ?>
            <?php endforeach; ?>

            <!-- Space Support Button -->
            <hr class="horizontal dark mt-0">
            <div class="d-flex justify-content-center">
                <button type="button" class="btn bg-gradient-info w-85 mx-auto" onclick="showSupportDialog()">
                    <i class="material-symbols-rounded opacity-5 me-2">support_agent</i>
                    <span class="nav-link-text">Space Support</span>
                </button>
            </div>

            <!-- Sign Out Button -->
            <div class="d-flex justify-content-center mt-auto">
                <button type="button" class="btn bg-gradient-primary w-85 mx-auto" onclick="handleSignOut()">
                    <i class="material-symbols-rounded opacity-5 me-2">logout</i>
                    <span class="nav-link-text">Sign Out</span>
                </button>
            </div>
        </ul>
    </div>
</aside>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
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
                <?php
                // Define navbar items
                $navbar_items = [
                    [
                        'href' => 'javascript:;',
                        'icon' => 'notifications',
                        'badge' => '11',
                        'dropdown' => [
                            [
                                'icon' => 'email',
                                'text' => 'Check support messages',
                                'onclick' => 'openSupportMessagesModal()',
                                'href' => 'javascript:void(0)',
                                'html' => '<div class="d-flex align-items-center py-1" onclick="openSupportMessagesModal()" style="cursor: pointer;">
                                    <span class="material-symbols-rounded me-2">email</span>
                                    <div class="ms-2">
                                        <h6 class="text-sm font-weight-normal my-auto">Check support messages</h6>
                                    </div>
                                </div>'
                            ]
                        ]
                    ]
                ];

                // Loop through navbar items
                foreach ($navbar_items as $item): ?>
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
                                        <a class="dropdown-item border-radius-md" href="<?php echo $dropdownItem['href']; ?>">
                                            <?php if (isset($dropdownItem['html'])): ?>
                                                <?php echo $dropdownItem['html']; ?>
                                            <?php else: ?>
                                                <div class="d-flex align-items-center py-1">
                                                    <span class="material-symbols-rounded"><?php echo $dropdownItem['icon']; ?></span>
                                                    <div class="ms-2">
                                                        <h6 class="text-sm font-weight-normal my-auto">
                                                            <?php echo $dropdownItem['text']; ?>
                                                        </h6>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
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
                                  <div class="row mt-4">
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
                        <div class="card mt-4" id="deleteaccount">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-sm-0 mb-4">
                                    <div class="w-50">
                                        <h5>Delete Account</h5>
                                        <p class="text-sm mb-0">Once you delete your account, there is no going back. Please be certain.</p>
                                    </div>
                                    <div class="w-50 text-end">
                                        <button class="btn btn-outline-danger mb-0" type="button" onclick="confirmDeletion()">
                                            Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once('support_messages_modal.php'); ?>

    
    </main>
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="../../assets/js/plugins/choices.min.js"></script>
  <script src="../../assets/js/signout.js"></script>
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <script src="../../assets/js/support-messages.js"></script>
  <script src="../../assets/js/support.js"></script>
  <script>
    const choices = new Choices('[data-trigger]', {
      searchResultLimit: 5,
      renderChoiceLimit: 5
    });
  </script>
  <script>
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

    // Create preview URL
    const previewURL = URL.createObjectURL(file);

    // Show confirmation dialog with preview
    const willUpdate = await Swal.fire({
        title: 'Update Profile Picture?',
        text: 'Are you sure you want to update your profile picture?',
        imageUrl: previewURL,
        imageWidth: 200,
        imageHeight: 200,
        imageAlt: 'Profile Picture Preview',
        showCancelButton: true,
        confirmButtonText: 'Yes, update it!',
        cancelButtonText: 'Cancel',
        customClass: {
            confirmButton: 'btn bg-gradient-primary btn-sm mb-0 mx-2',
            cancelButton: 'btn bg-gradient-secondary btn-sm mb-0 mx-2',
            image: 'rounded-3 shadow-sm'
        },
        buttonsStyling: false
    });

    // Clean up the preview URL
    URL.revokeObjectURL(previewURL);

    if (!willUpdate.isConfirmed) {
        this.value = ''; // Reset file input if cancelled
        return;
    }

    // Create FormData and proceed with upload
    const formData = new FormData();
    formData.append('profile_picture', file);

    try {
        // Show loading state
        const loadingAlert = Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we update your profile picture.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Upload the image
        const response = await fetch('../../admin_operations/update_profile_picture.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            // Update preview immediately
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                // Update all instances of the profile picture on the page
                document.querySelectorAll('.avatar').forEach(img => {
                    img.src = e.target.result;
                });
            };
            reader.readAsDataURL(file);

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Profile picture updated successfully.',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: error.message || 'Failed to update profile picture. Please try again.',
            customClass: {
                confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
            },
            buttonsStyling: false
        });
    }
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
        
        const formData = new FormData(this);
        const email = formData.get('email').trim();
        const phone = formData.get('phonenum').trim();
        
        // Validate formats if provided
        if (email && !email.endsWith('@g.batstate-u.edu.ph')) {
            await Swal.fire({
                icon: 'warning',
                title: 'Invalid Email Format',
                text: 'Please use your BatState-U email (@g.batstate-u.edu.ph)',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        if (phone && !/^09\d{9}$/.test(phone)) {
            await Swal.fire({
                icon: 'warning',
                title: 'Invalid Phone Number',
                text: 'Please enter a valid Philippine mobile number (e.g., 09123456789)',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
            return;
        }

        // First confirmation dialog
        const willUpdate = await Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to update your profile information?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, update it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn bg-gradient-primary btn-sm mb-0 mx-2',
                cancelButton: 'btn bg-gradient-secondary btn-sm mb-0 mx-2'
            },
            buttonsStyling: false,
            allowOutsideClick: false
        });

        // Wait for user confirmation
        if (!willUpdate.isConfirmed) {
            return; // Stop if user doesn't confirm
        }

        // If confirmed, proceed with update
        try {
            const response = await fetch('../../admin_operations/process_profile_update.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                // Show success message and wait for OK
                const successResult = await Swal.fire({
                    title: 'Successfully Updated!',
                    text: 'Your profile has been updated successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                    },
                    buttonsStyling: false,
                    allowOutsideClick: false
                });

                // Only reload if OK is clicked
                if (successResult.isConfirmed) {
                    window.location.reload();
                }
            } else {
                throw new Error(data.message || 'Update failed');
            }
        } catch (error) {
            console.error('Error:', error);
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Failed to update profile',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary btn-sm mb-0'
                },
                buttonsStyling: false
            });
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
function searchMenu() {
    // Get search input
    let input = document.getElementById("searchInput");
    let filter = input.value.toLowerCase();
    let resultsBox = document.getElementById("searchResults");

    // Menu items to search through
    let menuItems = [
        { text: 'Analytics', link: 'student.php' },
        { text: 'Mood Tracker', link: 'moodtracker.php' },
        { text: 'Notifications', link: 'notifications.php' },
        { text: 'Calendar', link: 'calendar.php' },
        { text: 'Profile', link: 'profile.php' },
        { text: 'Account Settings', link: 'account-settings.php' },
        { text: 'Articles', link: 'articles.php' },
        { text: 'Journal', link: 'journal.php' },
        { text: 'Support', link: 'support.php' }
    ];

    // Clear previous results
    resultsBox.innerHTML = '';

    if (filter) {
        // Filter menu items
        let matches = menuItems.filter(item => 
            item.text.toLowerCase().includes(filter)
        );

        if (matches.length > 0) {
            matches.forEach(item => {
                let div = document.createElement('div');
                div.className = 'search-result-item p-2';
                div.style.cursor = 'pointer';
                div.onclick = () => window.location.href = item.link;
                div.innerHTML = item.text;
                resultsBox.appendChild(div);
            });
            resultsBox.classList.remove('d-none');
        } else {
            let div = document.createElement('div');
            div.className = 'p-2 text-muted';
            div.innerHTML = 'No results found';
            resultsBox.appendChild(div);
            resultsBox.classList.remove('d-none');
        }
    } else {
        resultsBox.classList.add('d-none');
    }
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    let resultsBox = document.getElementById('searchResults');
    let searchInput = document.getElementById('searchInput');
    
    if (!searchInput.contains(e.target) && !resultsBox.contains(e.target)) {
        resultsBox.classList.add('d-none');
    }
});
</script>
<script>
function confirmDeletion() {
    Swal.fire({
        title: 'Delete Account',
        html: `
            <div class="text-start">
                <p class="text-danger fw-bold">This action cannot be undone.</p>
                <p>Please select a reason for deleting your account:</p>
                <select id="deleteReason" class="form-control mb-3">
                    <option value="">Select a reason</option>
                    <option value="privacy">Privacy concerns</option>
                    <option value="not_useful">Service not useful</option>
                    <option value="other">Other reason</option>
                </select>
                <textarea id="deleteDetails" class="form-control" 
                    placeholder="Please provide additional details..." rows="3"></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Delete Account',
        cancelButtonText: 'Cancel',
        customClass: {
            container: 'custom-swal-container',
            popup: 'custom-swal-popup',
            header: 'custom-swal-header',
            title: 'custom-swal-title',
            closeButton: 'custom-swal-close',
            icon: 'custom-swal-icon',
            image: 'custom-swal-image',
            htmlContainer: 'custom-swal-html',
            input: 'custom-swal-input',
            inputLabel: 'custom-swal-input-label',
            validationMessage: 'custom-swal-validation',
            actions: 'custom-swal-actions',
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary',
            loader: 'custom-swal-loader',
            footer: 'custom-swal-footer'
        },
        preConfirm: () => {
            const reason = document.getElementById('deleteReason').value;
            const details = document.getElementById('deleteDetails').value;
            
            if (!reason) {
                Swal.showValidationMessage('Please select a reason');
                return false;
            }
            
            return { reason, details };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('delete_reason', result.value.reason);
            formData.append('delete_details', result.value.details);

            fetch('../../admin_operations/delete_account.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Deleted',
                        text: 'Your account has been successfully deleted.',
                        customClass: {
                            container: 'custom-swal-container',
                            popup: 'custom-swal-popup'
                        }
                    }).then(() => {
                        window.location.href = '../signin.php';
                    });
                } else {
                    throw new Error(data.message || 'Failed to delete account');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    customClass: {
                        container: 'custom-swal-container',
                        popup: 'custom-swal-popup'
                    }
                });
            });
        }
    });
}
</script>
</body>
</html>


