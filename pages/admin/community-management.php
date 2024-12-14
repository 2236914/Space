<?php
require_once '../../configs/auth_check.php';
checkAdminAuth();
// Required files
require_once '../../configs/config.php';
require_once '../../includes/admin_navigation_components.php';

// Function to get posts by status
function getPostsByStatus($status) {
    global $pdo;
    $query = "
        SELECT p.*, 
               COUNT(DISTINCT l.like_id) as like_count,
               COUNT(DISTINCT c.comment_id) as comment_count,
               COUNT(DISTINCT r.report_id) as report_count
        FROM posts p
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id AND c.status = 'active'
        LEFT JOIN reports r ON p.post_id = r.reported_id AND r.reported_type = 'post'
        WHERE p.status = ?
        GROUP BY p.post_id
        ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get posts for each status
$active_posts = getPostsByStatus('active');
$hidden_posts = getPostsByStatus('hidden');
$deleted_posts = getPostsByStatus('deleted');

$active_count = count($active_posts);
$hidden_count = count($hidden_posts);
$deleted_count = count($deleted_posts);
?>

<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Community Management
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
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link rel="stylesheet" href="../../assets/css/custom-swal.css">
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
                                <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#active-tab" role="tab">
                                    <span class="material-symbols-rounded align-middle mb-1">public</span>
                                    Active Posts
                                    <span class="badge rounded-pill bg-success ms-2"><?php echo $active_count; ?></span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#hidden-tab" role="tab">
                                    <span class="material-symbols-rounded align-middle mb-1">visibility_off</span>
                                    Hidden Posts
                                    <span class="badge rounded-pill bg-warning ms-2"><?php echo $hidden_count; ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
<div class="tab-content">
<div class="tab-pane fade show active" id="active-tab" role="tabpanel">
    <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Active Posts</h6>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Content</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Engagement</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reports</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Posted Date</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($active_posts as $post): ?>
                        <tr>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($post['username']); ?></h6>
                                        <p class="text-xs text-secondary mb-0"><?php echo $post['post_type']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">
                                    <?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?>
                                </p>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <span class="badge badge-sm bg-gradient-success">
                                    <?php echo $post['like_count']; ?> likes
                                </span>
                                <span class="badge badge-sm bg-gradient-info">
                                    <?php echo $post['comment_count']; ?> comments
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <?php if ($post['report_count'] > 0): ?>
                                    <span class="badge badge-sm bg-gradient-danger">
                                        <?php echo $post['report_count']; ?> reports
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-sm bg-gradient-success">No reports</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">
                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm" onclick="viewPost(<?php echo $post['post_id']; ?>)">
                                    <span class="material-symbols-rounded">visibility</span>
                                </button>
                                <button class="btn btn-sm" onclick="hidePost(<?php echo $post['post_id']; ?>)">
                                    <span class="material-symbols-rounded">visibility_off</span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Hidden Posts Tab -->
<div class="tab-pane fade" id="hidden-tab" role="tabpanel">
    <div class="card my-4">
        <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
            <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                <h6 class="text-white text-capitalize ps-3">Hidden Posts</h6>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Author</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Content</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Engagement</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Reports</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Posted Date</th>
                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hidden_posts as $post): ?>
                        <tr>
                            <td>
                                <div class="d-flex px-2 py-1">
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($post['username']); ?></h6>
                                        <p class="text-xs text-secondary mb-0"><?php echo $post['post_type']; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <p class="text-xs font-weight-bold mb-0">
                                    <?php echo substr(htmlspecialchars($post['content']), 0, 100) . '...'; ?>
                                </p>
                            </td>
                            <td class="align-middle text-center text-sm">
                                <span class="badge badge-sm bg-gradient-success">
                                    <?php echo $post['like_count']; ?> likes
                                </span>
                                <span class="badge badge-sm bg-gradient-info">
                                    <?php echo $post['comment_count']; ?> comments
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <?php if ($post['report_count'] > 0): ?>
                                    <span class="badge badge-sm bg-gradient-danger">
                                        <?php echo $post['report_count']; ?> reports
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-sm bg-gradient-success">No reports</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle text-center">
                                <span class="text-secondary text-xs font-weight-bold">
                                    <?php echo date('M d, Y', strtotime($post['created_at'])); ?>
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <button class="btn btn-sm" onclick="viewPost(<?php echo $post['post_id']; ?>)">
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

    <!-- View Post Modal -->
    <div class="modal fade" id="viewPostModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Post content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
<!-- Core JS Files -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/material-dashboard.min.js"></script>

<!-- DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<script src="../../assets/js/community-management.js"></script>

<!-- Initialize Scrollbar -->
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