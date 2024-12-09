<?php
// Basic error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Debug the actual session data
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in as therapist
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'therapist') {
    // Store the intended destination
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Clear session and redirect
    session_unset();
    session_destroy();
    
    // Start new session for login process
    session_start();
    $_SESSION['login_error'] = 'Please log in to access the therapist community.';
    
    header("Location: /Space/signin.php");
    exit();
}

// If we get here, user is properly authenticated
try {
    require_once '../../configs/config.php';
    require_once '../../admin_operations/dashboard_analytics.php';
    require_once '../../includes/therapist_navigation_components.php';
} catch (Exception $e) {
    error_log("File inclusion error: " . $e->getMessage());
    die("Error loading required files: " . $e->getMessage());
}

// Check database connection
try {
    $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
    error_log("Database connection successful");
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
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
    
    // Get current therapist's username
    $username_stmt = $pdo->prepare("SELECT username FROM therapists WHERE therapist_id = ?");
    $username_stmt->execute([$_SESSION['user_id']]);
    $current_username = $username_stmt->fetchColumn();
    
    $posts_stmt->execute([':current_user' => $current_username]);
    $posts = $posts_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching posts: " . $e->getMessage());
    $posts = [];
}

// Time ago function
function getTimeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $current_time = time();
    $time_difference = $current_time - $timestamp;

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

<!-- Rest of the HTML remains largely the same, but with therapist-specific changes -->
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Therapist Community</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
<body class="g-sidenav-show bg-gray-200" data-user-role="therapist">
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

    <div class="container-fluid">
        <div class="col-lg-12 py-4">
            <h3 class="mb-0 h4 font-weight-bolder">Therapist Community</h3>
            <p class="mb-4">Connect with students and fellow therapists to provide support and share professional insights.</p>
        </div>
    </div>

    <!-- Post creation section -->
    <div class="container-fluid py-1">
        <div class="row">
            <div class="col-lg-5">
                <div class="position-sticky top-1 mb-4">
                    <div class="alert alert-info bg-gradient-info text-white" role="alert">
                        <h5 class="alert-heading text-white">Share Your Expertise</h5>
                        <p class="text-sm text-white opacity-8">Share professional insights, mental health tips, or respond to student concerns. Your expertise can make a difference in our community.</p>
                        <hr class="horizontal light opacity-6">
                        <p class="text-sm mb-0 text-white opacity-8">Remember to maintain professional boundaries and follow ethical guidelines when posting.</p>
                    </div>
                    <!-- Post creation form -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img alt="Profile picture" class="avatar rounded-circle me-3" 
                                         src="../../admin_operations/get_profile_picture.php?user_id=<?php echo $_SESSION['user_id']; ?>&user_type=therapist"
                                         onerror="this.src='../../assets/img/default-avatar.png'">
                                </div>
                                <div class="flex-grow-1">
                                    <textarea name="content" class="form-control border-0" rows="4" 
                                              placeholder="Share your professional insights..."></textarea>
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

            <!-- Main Content Area - Posts Display -->
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
                                                    <span class="badge <?php echo $post['post_type'] === 'therapist' ? 'bg-info' : 'bg-primary'; ?> ms-2">
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
</main>

<!-- Scripts -->
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
<script src="../../assets/js/thsignout.js"></script>
<script src="../../assets/js/search.js"></script>
<script src="../../assets/js/createpost.js"></script>
<script src="../../assets/js/likepost.js"></script>
<script src="../../assets/js/comments.js"></script>
<script src="../../assets/js/report.js"></script>
<script src="../../assets/js/support.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var sidenav = document.querySelector('#sidenav-scrollbar');
        if (sidenav) {
            var options = {
                damping: '0.5'
            };
            Scrollbar.init(sidenav, options);
        }

        if (typeof MaterialDashboard !== 'undefined') {
            window.materialDashboard = new MaterialDashboard();
            window.materialDashboard.initSidebar();
        }
    });
</script>

</body>
</html>
