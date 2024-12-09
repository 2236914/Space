<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
error_log("Session ID: " . session_id());
error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));
error_log("Role: " . ($_SESSION['role'] ?? 'not set'));

// Add error checking for required files
try {
    require_once '../../configs/config.php';
    require_once '../../admin_operations/dashboard_analytics.php';
    require_once '../../includes/navigation_components.php';
} catch (Exception $e) {
    error_log("File inclusion error: " . $e->getMessage());
    die("Error loading required files: " . $e->getMessage());
}

// Add PDO connection check
try {
    $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    error_log("Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Only check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}

// Initialize analytics
$analytics = new DashboardAnalytics($pdo);

// Fetch posts with like counts
// Fetch posts with like counts
try {
    $posts_stmt = $pdo->prepare("
        SELECT 
            p.*,
            CASE 
                WHEN p.post_type = 'student' THEN s.firstname
                ELSE t.firstname
            END as firstname,
            CASE 
                WHEN p.post_type = 'student' THEN s.lastname
                ELSE t.lastname
            END as lastname,
            CASE 
                WHEN p.post_type = 'student' THEN s.srcode
                ELSE t.therapist_id
            END as user_id,
            COUNT(DISTINCT l.like_id) as like_count,
            COUNT(DISTINCT c.comment_id) as comment_count,
            EXISTS(
                SELECT 1 
                FROM likes l2 
                WHERE l2.post_id = p.post_id 
                AND l2.username = :current_user
            ) as user_has_liked
        FROM posts p
        LEFT JOIN students s ON p.username = s.username AND p.post_type = 'student'
        LEFT JOIN therapists t ON p.username = t.username AND p.post_type = 'therapist'
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id
        WHERE p.status = 'active'  -- Ensure this condition is present
        GROUP BY p.post_id
        ORDER BY p.created_at DESC
    ");
    $posts_stmt = $pdo->prepare("
        SELECT 
            p.*,
            CASE 
                WHEN p.post_type = 'student' THEN s.firstname
                ELSE t.firstname
            END as firstname,
            CASE 
                WHEN p.post_type = 'student' THEN s.lastname
                ELSE t.lastname
            END as lastname,
            CASE 
                WHEN p.post_type = 'student' THEN s.srcode
                ELSE t.therapist_id
            END as user_id,
            COUNT(DISTINCT l.like_id) as like_count,
            COUNT(DISTINCT c.comment_id) as comment_count,
            EXISTS(
                SELECT 1 
                FROM likes l2 
                WHERE l2.post_id = p.post_id 
                AND l2.username = :current_user
            ) as user_has_liked
        FROM posts p
        LEFT JOIN students s ON p.username = s.username AND p.post_type = 'student'
        LEFT JOIN therapists t ON p.username = t.username AND p.post_type = 'therapist'
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id
        WHERE p.status = 'active'
        GROUP BY p.post_id
        ORDER BY p.created_at DESC
    ");
    
    // Get current user's username
    if ($_SESSION['role'] === 'student') {
        $username_stmt = $pdo->prepare("SELECT username FROM students WHERE srcode = ?");
    } else {
        $username_stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
    }
    $username_stmt->execute([$_SESSION['user_id']]);
    $current_username = $username_stmt->fetchColumn();
    
    $posts_stmt->execute([':current_user' => $current_username]);
    $posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching posts: " . $e->getMessage());
    $posts = [];
}

// Add the getTimeAgo function here
function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $timestamp;

    // Add debugging
    error_log("Current time: " . date('Y-m-d H:i:s', $current_time));
    error_log("Post time: " . date('Y-m-d H:i:s', $timestamp));
    error_log("Difference: " . $time_difference . " seconds");

    if ($time_difference < 30) {
        return "Just now";
    } elseif ($time_difference < 60) {
        return $time_difference . " seconds ago";
    } elseif ($time_difference < 3600) {
        $minutes = floor($time_difference / 60);
        return $minutes . " minute" . ($minutes != 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 86400) {
        $hours = floor($time_difference / 3600);
        return $hours . " hour" . ($hours != 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 604800) {
        $days = floor($time_difference / 86400);
        return $days . " day" . ($days != 1 ? "s" : "") . " ago";
    } elseif ($time_difference < 2592000) {
        $weeks = floor($time_difference / 604800);
        return $weeks . " week" . ($weeks != 1 ? "s" : "") . " ago";
    } else {
        return date('F j, Y', $timestamp);
    }
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
  <style>
       .hover-bg-light:hover {
        background-color: #f8f9fa;
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .highlight-post {
        animation: highlight 2s;
    }
    @keyframes highlight {
        0% { background-color: #fff3cd; }
        100% { background-color: transparent; }
    }
    #searchResults::-webkit-scrollbar {
        width: 8px;
    }
    #searchResults::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    #searchResults::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    #searchResults::-webkit-scrollbar-thumb:hover {
        background: #666;
    }
    .search-result {
        transition: background-color 0.2s;
        border-radius: 8px;
        margin: 4px 0;
    }
    .search-result:last-child {
        border-bottom: none !important;
    }
  </style>
</head>

<body class="g-sidenav-show bg-gray-200" data-user-role="student">
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
                        'icon' => 'message',
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
                                <img alt="Profile picture" class="avatar rounded-circle me-3" 
                                     src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=student"
                                     onerror="this.src='../../assets/img/default-avatar.png'">
                            </div>
                            <div class="flex-grow-1">
                                <textarea name="content" class="form-control border-0" rows="4" 
                                          placeholder="Share your thoughts..."></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <input type="file" id="imageInput" accept="image/*" 
                                               onchange="previewImage(this)" style="display: none;">
                                        <button type="button" class="btn btn-link p-0" 
                                                onclick="document.getElementById('imageInput').click()">
                                            <i class="material-symbols-rounded">image</i>
                                        </button>
                                    </div>
                                    <button type="button" class="btn bg-gradient-dark mb-0" 
                                            onclick="createPost()">Post</button>
                                </div>
                                <div id="imagePreview" style="display: none;" class="mt-3">
                                    <img id="preview" src="#" alt="Preview" class="img-fluid rounded">
                                    <button type="button" class="btn btn-link text-danger p-0 mt-2" 
                                            onclick="removeImage()">
                                        <i class="material-symbols-rounded">delete</i> Remove Image
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
                <?php foreach ($posts as $post): ?>
                    <div class="card px-4 mb-4">
                        <!-- Post Header -->
                        <div class="card-header d-flex align-items-center py-3">
                            <div class="d-block d-md-flex align-items-center">
                                <a href="javascript:;">
                                    <img src="../../admin_operations/get_profile_picture.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>&user_type=<?php echo htmlspecialchars($post['post_type']); ?>" 
                                        class="avatar" 
                                        alt="profile-image"
                                        onerror="this.src='../../assets/img/default-avatar.png';">
                                </a>
                                <div class="d-flex px-2 py-1">
                                    <div>
                                        <h6 class="mb-0 text-sm d-flex align-items-center">
                                            <?php echo htmlspecialchars($post['firstname'] . ' ' . $post['lastname']); ?>
                                            <span class="badge bg-<?php echo $post['post_type'] === 'therapist' ? 'info' : 'primary'; ?> ms-2">
                                                <?php echo ucfirst($post['post_type']); ?>
                                            </span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-0"><?php echo getTimeAgo($post['created_at']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end ms-auto">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-link text-secondary mb-0" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="material-symbols-rounded">more_vert</i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="reportPost(<?php echo $post['post_id']; ?>)">Report Post</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="reportUser('<?php echo htmlspecialchars($post['username']); ?>')">Report User</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <hr class="dark horizontal">
                        <!-- Post Content -->
                        <div class="card-body pt-3 px-4 mb-4">
                            <p class="mb-4">
                                <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                            </p>
                            <?php if ($post['image_file']): ?>
                                <img alt="Post image" 
                                    src="../../admin_operations/get_post_image.php?post_id=<?php echo $post['post_id']; ?>" 
                                    class="img-fluid border-radius-lg shadow-lg">
                            <?php endif; ?>

                             <!-- Interaction Counts -->
                            <div class="row align-items-center px-2 mt-4 mb-2">
                                <div class="col-sm-6">
                                    <div class="d-flex">
                                        <div class="d-flex align-items-center">
                                        <button type="button" 
                                                class="btn btn-link p-0 me-2 <?php echo $post['user_has_liked'] ? 'liked' : ''; ?>" 
                                                onclick="handleLike(this, <?php echo $post['post_id']; ?>)">
                                                <i class="material-symbols-rounded text-sm me-1"
                                                style="font-variation-settings: <?php echo $post['user_has_liked'] ? '\'FILL\' 1' : ''; ?>">
                                                    thumb_up
                                                </i>
                                                <span class="text-sm"><?php echo $post['like_count']; ?></span>
                                            </button>
                                        </div>
                                        <div class="d-flex align-items-center">
                                        <button type="button" 
                                                class="btn btn-link p-0 me-2" 
                                                onclick="showComments(this, <?php echo $post['post_id']; ?>)"
                                                data-post-id="<?php echo $post['post_id']; ?>">
                                            <i class="material-symbols-rounded text-sm me-1">mode_comment</i>
                                            <span class="text-sm"><?php echo $post['comment_count']; ?></span>
                                        </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
      </div>
    </div>
    <?php include_once('support_messages_modal.php'); ?>
  </main>

  <!-- Core Scripts -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  

  <!-- Custom Scripts -->
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/search.js"></script>
  <script src="../../assets/js/createpost.js"></script>
  <script src="../../assets/js/likepost.js"></script>
  <script src="../../assets/js/comments.js"></script>
  <script src="../../assets/js/report.js"></script>
  <script src="../../assets/js/support-messages.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <!-- Initialize Material Dashboard -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize perfect scrollbar only if element exists
      var sidenav = document.querySelector('#sidenav-scrollbar');
      if (sidenav) {
        var options = {
          damping: '0.5'
        };
        Scrollbar.init(sidenav, options);
      }

      // Material Dashboard Initialization
      if (typeof MaterialDashboard !== 'undefined') {
        window.materialDashboard = new MaterialDashboard();
        window.materialDashboard.initSidebar();
      }
    });
  </script>
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
    // Add this to verify scripts are loaded
    console.log('Scripts loaded');
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM fully loaded');
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test like functionality
        const likeButtons = document.querySelectorAll('button[onclick^="handleLike"]');
        console.log('Found like buttons:', likeButtons.length);
        
        // Verify event handlers
        likeButtons.forEach(button => {
            button.addEventListener('click', function() {
                console.log('Like button clicked via event listener');
            });
        });
    });
  </script>
  <script>
    // Debug code to verify scripts and elements
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        
        // Check if SweetAlert2 is loaded
        if (typeof Swal !== 'undefined') {
            console.log('SweetAlert2 is loaded');
        } else {
            console.error('SweetAlert2 is not loaded');
        }
        
        // Check if signout button exists
        const signoutBtn = document.getElementById('signout');
        if (signoutBtn) {
            console.log('Signout button found');
            // Test click handler
            signoutBtn.addEventListener('click', function() {
                console.log('Signout button clicked');
            });
        } else {
            console.error('Signout button not found');
        }
    });
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checking timestamps...');
    document.querySelectorAll('.text-xs.text-secondary.mb-0').forEach(el => {
        console.log('Post time:', el.textContent);
    });
});
</script>
</body>
</html>
