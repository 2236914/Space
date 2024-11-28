<?php
session_start();
require_once '../../configs/config.php';
require_once '../../admin_operations/dashboard_analytics.php';
require_once '../../includes/navigation_components.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Initialize analytics
$analytics = new DashboardAnalytics($pdo);

// Check if student has logged mood today
try {
    $mood_stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM moodlog 
        WHERE srcode = ? 
        AND DATE(log_date) = CURRENT_DATE()
    ");
    $mood_stmt->execute([$_SESSION['user_id']]);
    $has_mood_today = $mood_stmt->fetchColumn() > 0;

    if (!$has_mood_today) {
        header("Location: moodlog.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Mood check error: " . $e->getMessage());
    $has_mood_today = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>Community</title>
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
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
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
                        'href' => 'Space/pages/authentication/signin/illustration.html',
                        'icon' => 'account_circle',
                        'target' => '_blank'
                    ],
                    [
                        'href' => 'javascript:;',
                        'icon' => 'settings',
                        'class' => 'fixed-plugin-button-nav'
                    ],
                    [
                        'href' => 'javascript:;',
                        'icon' => 'notifications',
                        'badge' => '11',
                        'dropdown' => [
                            [
                                'icon' => 'email',
                                'text' => 'Check new messages'
                            ],
                            [
                                'icon' => 'podcasts',
                                'text' => 'Manage podcast session'
                            ],
                            [
                                'icon' => 'shopping_cart',
                                'text' => 'Payment successfully completed'
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


    <!-- Main Content -->
    <div class="container-fluid">
    <div class="col-lg-12 py-4">
        <h3 class="mb-0 h4 font-weight-bolder">Space Community</h3>
        <p class="mb-4">Connect with fellow students in a safe space to share experiences and support each other through discussion groups and peer activities.</p>
      </div>
    </div>
    <div class="container-fluid py-1">
      <div class="row">
        <!-- Left Sidebar Navigation -->
        <div class="col-lg-5">
            <div class="position-sticky top-1 mb-4">
                <div class="alert alert-info bg-gradient-info text-white" role="alert">
                    <h5 class="alert-heading text-white">Create a Post</h5>
                    <p class="text-sm text-white opacity-8">Share your thoughts, questions, and experiences with the Space community. Your post can be about academic challenges, mental health, or any topic you'd like support with.</p>
                    <hr class="horizontal light opacity-6">
                    <p class="text-sm mb-0 text-white opacity-8">Remember to be respectful and follow our community guidelines when posting.</p>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <img alt="Profile picture" class="avatar rounded-circle me-3" src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=<?php echo $_SESSION['role']; ?>" onerror="this.src='../../assets/img/default-avatar.png';">
                            </div>
                            <div class="flex-grow-1">
                                <div class="input-group input-group-static">
                                    <label>What's on your mind?</label>
                                    <textarea class="form-control" name="content" placeholder="Share your thoughts..." rows="4" spellcheck="false"></textarea>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <button class="btn btn-link px-1 py-0" type="button" onclick="document.getElementById('imageInput').click()">
                                            <i class="material-symbols-rounded">image</i>
                                        </button>
                                        <input type="file" id="imageInput" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                        <!-- Add image preview container -->
                                        <div id="imagePreview" class="mt-2" style="display: none;">
                                            <div class="position-relative">
                                                <img id="preview" src="#" alt="Preview" style="max-height: 200px; max-width: 100%; border-radius: 8px;">
                                                <button type="button" class="btn btn-link text-danger position-absolute top-0 end-0 p-0" 
                                                        onclick="removeImage()">
                                                    <i class="material-symbols-rounded">close</i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button class="btn bg-gradient-dark mb-0" type="button" onclick="createPost()">
                                        Post
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

            <!-- Main Content Area -->
    <div class="col-lg-7">
      <div class="row">
        <div class="col-12">
          <div class="card px-4 mb-4">
            <!-- Post Header -->
            <div class="card-header d-flex align-items-center py-3">
              <div class="d-block d-md-flex align-items-center">
                <a href="javascript:;">
                  <img src="../../assets/img/team-4.jpg" class="avatar" alt="profile-image">
                </a>
                <div class="mx-0 mx-md-3">
                  <a href="javascript:;" class="text-dark font-weight-600 text-sm">John Snow</a>
                  <small class="d-block text-muted">3 days ago</small>
                </div>
              </div>
              <div class="text-end ms-auto">
                <div class="dropdown">
                  <button type="button" class="btn btn-link text-secondary mb-0" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="material-symbols-rounded">more_vert</i>
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="reportPost()">Report Post</a></li>
                    <li><a class="dropdown-item" href="#" onclick="reportUser()">Report User</a></li>
                  </ul>
                </div>
              </div>
            </div>

            <hr class="dark horizontal">
       <!-- Post Content -->
       <div class="card-body pt-3 px-4 mb-4">
              <p class="mb-4">
                Personal profiles are the perfect way for you to grab their attention and persuade recruiters to continue reading your CV because you're telling them from the off exactly why they should hire you.
              </p>
              <img alt="Image placeholder" src="https://images.unsplash.com/photo-1578271887552-5ac3a72752bc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80" class="img-fluid border-radius-lg shadow-lg">

              <!-- Interaction Counts -->
              <div class="row align-items-center px-2 mt-4 mb-2">
                <!-- Like, Comment, Share counts -->
                <div class="col-sm-6">
                  <div class="d-flex">
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">thumb_up</i>
                      <span class="text-sm me-3">150</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">mode_comment</i>
                      <span class="text-sm me-3">36</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">forward</i>
                      <span class="text-sm me-2">12</span>
                    </div>
                  </div>
                </div>
                 <!-- User Avatars -->
                 <div class="col-sm-6 d-none d-sm-block">
                  <div class="d-flex align-items-center justify-content-sm-end">
                    <div class="d-flex align-items-center">
                      <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-toggle="tooltip" data-original-title="Jessica Rowland">
                        <img alt="Image placeholder" src="../../assets/img/team-5.jpg">
                      </a>
                      <!-- More avatars -->
                    </div>
                    <small class="ps-2 font-weight-bold">and 30+ more</small>
                  </div>
                </div>
                <hr class="horizontal dark my-3">
              </div>
               <!-- Comments Section -->
               <div class="mb-1">
                <!-- Existing comments -->
                <div class="d-flex mt-4">
                  <div class="flex-shrink-0">
                    <img alt="Image placeholder" class="avatar rounded-circle me-3" src="../../assets/img/team-4.jpg">
                  </div>
                  <div class="flex-grow-1 my-auto">
                    <div class="input-group input-group-static">
                      <textarea class="form-control" placeholder="Write your comment" rows="4" spellcheck="false"></textarea>
                    </div>
                  </div>
                  <button class="btn bg-gradient-dark btn-sm mt-auto mb-0 ms-2" type="button" name="button">
                    <i class="material-symbols-rounded text-sm">send</i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <div class="card px-4">
            <!-- Post Header -->
            <div class="card-header d-flex align-items-center py-3">
              <div class="d-block d-md-flex align-items-center">
                <a href="javascript:;">
                  <img src="../../assets/img/team-4.jpg" class="avatar" alt="profile-image">
                </a>
                <div class="mx-0 mx-md-3">
                  <a href="javascript:;" class="text-dark font-weight-600 text-sm">John Snow</a>
                  <small class="d-block text-muted">3 days ago</small>
                </div>
              </div>
              <div class="text-end ms-auto">
                <button type="button" class="btn bg-gradient-dark mb-0">
                  <i class="material-symbols-rounded text-white pe-2 text-lg">add</i>Follow
                </button>
              </div>
            </div>

            <hr class="dark horizontal">
          <!-- Post Content -->
             <div class="card-body pt-3 px-4">
              <p class="mb-4">
                Personal profiles are the perfect way for you to grab their attention and persuade recruiters to continue reading your CV because you're telling them from the off exactly why they should hire you.
              </p>
              <img alt="Image placeholder" src="https://images.unsplash.com/photo-1578271887552-5ac3a72752bc?ixlib=rb-1.2.1&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=1950&q=80" class="img-fluid border-radius-lg shadow-lg">

              <!-- Interaction Counts -->
              <div class="row align-items-center px-2 mt-4 mb-2">
                <!-- Like, Comment, Share counts -->
                <div class="col-sm-6">
                  <div class="d-flex">
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">thumb_up</i>
                      <span class="text-sm me-3">150</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">mode_comment</i>
                      <span class="text-sm me-3">36</span>
                    </div>
                    <div class="d-flex align-items-center">
                      <i class="material-symbols-rounded text-sm me-1 cursor-pointer">forward</i>
                      <span class="text-sm me-2">12</span>
                    </div>
                  </div>
                </div>
                 <!-- User Avatars -->
                 <div class="col-sm-6 d-none d-sm-block">
                  <div class="d-flex align-items-center justify-content-sm-end">
                    <div class="d-flex align-items-center">
                      <a href="javascript:;" class="avatar avatar-xs rounded-circle" data-toggle="tooltip" data-original-title="Jessica Rowland">
                        <img alt="Image placeholder" src="../../assets/img/team-5.jpg">
                      </a>
                      <!-- More avatars -->
                    </div>
                    <small class="ps-2 font-weight-bold">and 30+ more</small>
                  </div>
                </div>
                <hr class="horizontal dark my-3">
              </div>
               <!-- Comments Section -->
               <div class="mb-1">
                <!-- Existing comments -->
                <div class="d-flex mt-4">
                  <div class="flex-shrink-0">
                    <img alt="Image placeholder" class="avatar rounded-circle me-3" src="../../assets/img/team-4.jpg">
                  </div>
                  <div class="flex-grow-1 my-auto">
                    <div class="input-group input-group-static">
                      <textarea class="form-control" placeholder="Write your comment" rows="4" spellcheck="false"></textarea>
                    </div>
                  </div>
                  <button class="btn bg-gradient-dark btn-sm mt-auto mb-0 ms-2" type="button" name="button">
                    <i class="material-symbols-rounded text-sm">send</i>
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
  </main>

  <!-- Core Scripts -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  
  <!-- Custom Scripts -->
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/support.js"></script>

  <script>
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
    let selectedImage = null;

    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            // Validate file type
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    title: 'Invalid File',
                    text: 'Please upload only image files.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    },
                    buttonsStyling: false
                });
                input.value = '';
                return;
            }

            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire({
                    title: 'File Too Large',
                    text: 'Please upload an image smaller than 5MB.',
                    icon: 'error',
                    customClass: {
                        confirmButton: 'btn bg-gradient-primary'
                    },
                    buttonsStyling: false
                });
                input.value = '';
                return;
            }

            selectedImage = file;
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            }
            reader.readAsDataURL(file);
            
            // Update icon color to indicate image selected
            const imageButton = document.querySelector('.btn.btn-link i');
            imageButton.style.color = '#344767';
        }
    }

    function removeImage() {
        selectedImage = null;
        document.getElementById('imageInput').value = '';
        document.getElementById('imagePreview').style.display = 'none';
        document.getElementById('preview').src = '#';
        const imageButton = document.querySelector('.btn.btn-link i');
        imageButton.style.color = '';
    }

    function createPost() {
        const content = document.querySelector('textarea').value.trim();
        
        if (!content) {
            Swal.fire({
                title: 'Empty Post',
                text: 'Please write something before posting.',
                icon: 'warning',
                customClass: {
                    confirmButton: 'btn bg-gradient-primary'
                },
                buttonsStyling: false
            });
            return;
        }

        const formData = new FormData();
        formData.append('content', content);
        formData.append('action', 'create_post');
        
        // Only append image if one was selected
        if (selectedImage) {
            formData.append('image', selectedImage);
        }

        // Disable post button and show loading state
        const postButton = document.querySelector('button.bg-gradient-dark');
        const originalText = postButton.innerHTML;
        postButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Posting...';
        postButton.disabled = true;

        fetch('../../admin_operations/post_handlers.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Clear form and preview
                document.querySelector('textarea').value = '';
                removeImage();

                // Show success message
                Swal.fire({
                    title: 'Posted!',
                    text: 'Your post has been shared successfully.',
                    icon: 'success',
                    customClass: {
                        confirmButton: 'btn bg-gradient-success'
                    },
                    buttonsStyling: false
                }).then(() => {
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to create post');
            }
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: error.message,
                icon: 'error',
                customClass: {
                    confirmButton: 'btn bg-gradient-danger'
                },
                buttonsStyling: false
            });
        })
        .finally(() => {
            // Restore button state
            postButton.innerHTML = originalText;
            postButton.disabled = false;
        });
    }
  </script>
</body>
</html>
