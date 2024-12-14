<?php
require_once '../../configs/auth_check.php';
checkAdminAuth();

// Required files
require_once '../../configs/config.php';
require_once '../../includes/admin_navigation_components.php';

// Get current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get current page info for breadcrumb
$current_info = [
    'parent' => 'Admin',
    'title' => 'Support Messages'
];

// Get all support messages
try {
    // Query for student messages
    $stmt_students = $pdo->query("
        SELECT 
            m.*, 
            s.srcode as sender_id,
            CONCAT(s.firstname, ' ', s.lastname) as sender_name,
            'student' as sender_type
        FROM support_messages m
        JOIN students s ON m.srcode = s.srcode
        ORDER BY m.created_at DESC
    ");
    $student_messages = $stmt_students->fetchAll(PDO::FETCH_ASSOC);

    // Query for therapist messages
    $stmt_therapists = $pdo->query("
        SELECT 
            m.*, 
            t.therapist_id as sender_id,
            CONCAT(t.firstname, ' ', t.lastname) as sender_name,
            'therapist' as sender_type
        FROM therapist_support_messages m
        JOIN therapists t ON m.therapist_id = t.therapist_id
        ORDER BY m.created_at DESC
    ");
    $therapist_messages = $stmt_therapists->fetchAll(PDO::FETCH_ASSOC);

    // Combine and sort all messages
    $messages = array_merge($student_messages, $therapist_messages);
    usort($messages, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $messages = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>
    Support Messages
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
        <!-- Navigation Pills -->
        <div class="row justify-space-between py-2">
            <div class="col-lg-8 mx-auto">
                <div class="nav-wrapper position-relative end-0">
                    <ul class="nav nav-pills nav-fill p-1" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center active" 
                            data-bs-toggle="tab" 
                            href="#all-messages" 
                            role="tab" 
                            aria-selected="true">
                                <i class="material-symbols-rounded text-sm me-2">all_inbox</i> All Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
                            data-bs-toggle="tab" 
                            href="#student-messages" 
                            role="tab" 
                            aria-selected="false">
                                <i class="material-symbols-rounded text-sm me-2">school</i> Student Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
                            data-bs-toggle="tab" 
                            href="#therapist-messages" 
                            role="tab" 
                            aria-selected="false">
                                <i class="material-symbols-rounded text-sm me-2">psychology</i> Therapist Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
                            data-bs-toggle="tab" 
                            href="#archived-messages" 
                            role="tab" 
                            aria-selected="false">
                                <i class="material-symbols-rounded text-sm me-2">archive</i> 
                                Archived
                                <?php 
                                $archived_count = count(array_filter($messages, function($m) {
                                    return $m['status'] === 'archived';
                                }));
                                if ($archived_count > 0):
                                ?>
                                <span class="badge bg-gradient-dark ms-2"><?= $archived_count ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    <!-- Tab Content -->
<div class="tab-content">
<!-- All Messages Tab -->
  <div class="tab-pane fade show active" id="all-messages" role="tabpanel">
        <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-primary shadow-primary border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3">All Support Messages</h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sender</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Message</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                <th class="text-secondary opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Filter out archived messages from the main view
                                            $active_messages = array_filter($messages, function($m) {
                                                return $m['status'] !== 'archived';
                                            });
                                            if (empty($active_messages)): 
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <p class="text-sm mb-0">No messages found</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($active_messages as $message): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                    class="avatar avatar-sm me-3 border-radius-lg" 
                                                                    onerror="this.src='../../assets/img/default-avatar.png';"
                                                                    alt="user image">
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    <?= ucfirst(htmlspecialchars($message['sender_type'])) ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-wrap mb-0" style="max-width: 300px;">
                                                            <?= htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : '') ?>
                                                        </p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="badge badge-sm <?= match($message['status']) {
                                                            'pending' => 'bg-gradient-warning',
                                                            'in_progress' => 'bg-gradient-info',
                                                            'resolved' => 'bg-gradient-success',
                                                            default => 'bg-gradient-secondary'
                                                        } ?>">
                                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            <?= date('M d, Y H:i', strtotime($message['created_at'])) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <!-- View Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-primary px-2 mb-0" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?= $message['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            <!-- Status Update Button & Modal -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-info px-2 mb-0"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#statusModal<?= $message['id'] ?>">
                                                                <i class="fas fa-cog"></i>
                                                            </button>

                                                            <!-- Status Update Modal -->
                                                            <div class="modal fade" id="statusModal<?= $message['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Update Status</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="d-grid gap-2">
                                                                                <button class="btn btn-warning text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'pending', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-clock me-2"></i> Pending
                                                                                </button>
                                                                                <button class="btn btn-info text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'in_progress', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-spinner me-2"></i> In Progress
                                                                                </button>
                                                                                <button class="btn btn-success text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'resolved', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-check me-2"></i> Resolved
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Archive Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-danger px-2 mb-0" 
                                                                    onclick="archiveMessage(<?= $message['id'] ?>, '<?= $message['sender_type'] ?>')">
                                                                <i class="fas fa-archive"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal for each message -->
                                                <div class="modal fade" 
                                                    id="messageModal<?= $message['id'] ?>" 
                                                    tabindex="-1" 
                                                    role="dialog"
                                                    aria-labelledby="messageModalLabel<?= $message['id'] ?>"
                                                    aria-hidden="false">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="messageModalLabel<?= $message['id'] ?>">
                                                                    Support Message Details
                                                                </h5>
                                                                <button type="button" 
                                                                    class="btn-close" 
                                                                    data-bs-dismiss="modal" 
                                                                    aria-label="Close">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                                class="avatar avatar-lg border-radius-lg shadow" 
                                                                                onerror="this.src='../../assets/img/default-avatar.png';"
                                                                                alt="user image"
                                                                                style="width: 80px; height: 80px; object-fit: cover;">
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="text-sm mb-1">From: <?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                            <p class="text-xs text-muted mb-0"><?= ucfirst($message['sender_type']) ?></p>
                                                                            <p class="text-xs text-muted mb-0"><?= date('F d, Y H:i', strtotime($message['created_at'])) ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Message:</h6>
                                                                    <p class="text-sm"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                                                </div>
                                                                <?php if (!empty($message['attachment_name'])): ?>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Attachment:</h6>
                                                                    <?php
                                                                    $file_extension = strtolower(pathinfo($message['attachment_name'], PATHINFO_EXTENSION));
                                                                    $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
                                                                    ?>
                                                                    <?php if ($is_image): ?>
                                                                    <div class="mb-2">
                                                                        <img src="../../admin_operations/get_support_attachment.php?id=<?= $message['id'] ?>" 
                                                                            class="img-fluid rounded shadow-sm" 
                                                                            alt="attachment preview"
                                                                            style="max-height: 200px;">
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <a href="../../admin_operations/get_support_attachment.php?id=<?= $message['id'] ?>&download=1" 
                                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center" 
                                                                    style="width: fit-content;">
                                                                        <i class="fa fa-download me-2"></i>
                                                                        <?= htmlspecialchars($message['attachment_name']) ?>
                                                                    </a>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Status:</h6>
                                                                    <span class="badge badge-sm <?= match($message['status']) {
                                                                        'pending' => 'bg-gradient-warning',
                                                                        'in_progress' => 'bg-gradient-info',
                                                                        'resolved' => 'bg-gradient-success',
                                                                        'archived' => 'bg-gradient-secondary',
                                                                        default => 'bg-gradient-secondary'
                                                                    } ?>">
                                                                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                                    </span>
                                                                </div>
                                                                <!-- Reply Form -->
                                                                <div class="reply-section mt-3">
                                                                    <div class="form-group mb-3">
                                                                <label class="form-label">Your Reply</label>
                                                                <textarea 
                                                                    class="form-control" 
                                                                    name="reply_text" 
                                                                    rows="4" 
                                                                    placeholder="Type your reply here..."
                                                                    style="resize: none;"
                                                                ></textarea>
                                                            </div>
                                                            <div class="form-group mb-3">
                                                                <label class="form-label">Attachment (Optional)</label>
                                                                <input type="file" class="form-control" name="attachment">
                                                            </div>
                                                            <div class="d-flex gap-2">
                                                                <button type="button" 
                                                                    class="btn bg-gradient-success send-reply-btn" onclick="submitReply('<?= $message['id'] ?>', '<?= $message['sender_type'] ?>')">
                                                                    <i class="fas fa-paper-plane me-2"></i>Send Reply
                                                                </button>
                                                                <button type="button" 
                                                                    class="btn bg-gradient-dark" 
                                                                    data-bs-dismiss="modal">
                                                                    Close
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                                                                </div>
                    </div>

            <!-- Student Messages Tab -->
            <div class="tab-pane fade" id="student-messages" role="tabpanel">
                        <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-info shadow-info border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3">Student Support Messages</h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sender</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Message</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                <th class="text-secondary opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $student_messages = array_filter($messages, function($m) {
                                                return $m['sender_type'] === 'student' && $m['status'] !== 'archived';
                                            });
                                            if (empty($student_messages)): 
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <p class="text-sm mb-0">No student messages found</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($student_messages as $message): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                    class="avatar avatar-sm me-3 border-radius-lg" 
                                                                    onerror="this.src='../../assets/img/default-avatar.png';"
                                                                    alt="user image">
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    <?= ucfirst(htmlspecialchars($message['sender_type'])) ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-wrap mb-0" style="max-width: 300px;">
                                                            <?= htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : '') ?>
                                                        </p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="badge badge-sm <?= match($message['status']) {
                                                            'pending' => 'bg-gradient-warning',
                                                            'in_progress' => 'bg-gradient-info',
                                                            'resolved' => 'bg-gradient-success',
                                                            default => 'bg-gradient-secondary'
                                                        } ?>">
                                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            <?= date('M d, Y H:i', strtotime($message['created_at'])) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <!-- View Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-primary px-2 mb-0" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?= $message['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            <!-- Status Update Button & Modal -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-info px-2 mb-0"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#statusModal<?= $message['id'] ?>">
                                                                <i class="fas fa-cog"></i>
                                                            </button>

                                                            <!-- Status Update Modal -->
                                                            <div class="modal fade" id="statusModal<?= $message['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Update Status</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="d-grid gap-2">
                                                                                <button class="btn btn-warning text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'pending', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-clock me-2"></i> Pending
                                                                                </button>
                                                                                <button class="btn btn-info text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'in_progress', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-spinner me-2"></i> In Progress
                                                                                </button>
                                                                                <button class="btn btn-success text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'resolved', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-check me-2"></i> Resolved
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Archive Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-danger px-2 mb-0" 
                                                                    onclick="archiveMessage(<?= $message['id'] ?>, '<?= $message['sender_type'] ?>')">
                                                                <i class="fas fa-archive"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal for each message -->
                                                <div class="modal fade" 
                                                    id="messageModal<?= $message['id'] ?>" 
                                                    tabindex="-1" 
                                                    role="dialog"
                                                    aria-labelledby="messageModalLabel<?= $message['id'] ?>"
                                                    aria-hidden="false">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="messageModalLabel<?= $message['id'] ?>">
                                                                    Support Message Details
                                                                </h5>
                                                                <button type="button" 
                                                                    class="btn-close" 
                                                                    data-bs-dismiss="modal" 
                                                                    aria-label="Close">
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="me-3">
                                                                            <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                                class="avatar avatar-lg border-radius-lg shadow" 
                                                                                onerror="this.src='../../assets/img/default-avatar.png';"
                                                                                alt="user image"
                                                                                style="width: 80px; height: 80px; object-fit: cover;">
                                                                        </div>
                                                                        <div>
                                                                            <h6 class="text-sm mb-1">From: <?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                            <p class="text-xs text-muted mb-0"><?= ucfirst($message['sender_type']) ?></p>
                                                                            <p class="text-xs text-muted mb-0"><?= date('F d, Y H:i', strtotime($message['created_at'])) ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Message:</h6>
                                                                    <p class="text-sm"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                                                </div>
                                                                <?php if (!empty($message['attachment_name'])): ?>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Attachment:</h6>
                                                                    <?php
                                                                    $file_extension = strtolower(pathinfo($message['attachment_name'], PATHINFO_EXTENSION));
                                                                    $is_image = in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif']);
                                                                    ?>
                                                                    <?php if ($is_image): ?>
                                                                    <div class="mb-2">
                                                                        <img src="../../admin_operations/get_support_attachment.php?id=<?= $message['id'] ?>" 
                                                                            class="img-fluid rounded shadow-sm" 
                                                                            alt="attachment preview"
                                                                            style="max-height: 200px;">
                                                                    </div>
                                                                    <?php endif; ?>
                                                                    <a href="../../admin_operations/get_support_attachment.php?id=<?= $message['id'] ?>&download=1" 
                                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center" 
                                                                    style="width: fit-content;">
                                                                        <i class="fa fa-download me-2"></i>
                                                                        <?= htmlspecialchars($message['attachment_name']) ?>
                                                                    </a>
                                                                </div>
                                                                <?php endif; ?>
                                                                <div class="mb-3">
                                                                    <h6 class="text-sm">Status:</h6>
                                                                    <span class="badge badge-sm <?= match($message['status']) {
                                                                        'pending' => 'bg-gradient-warning',
                                                                        'in_progress' => 'bg-gradient-info',
                                                                        'resolved' => 'bg-gradient-success',
                                                                        'archived' => 'bg-gradient-secondary',
                                                                        default => 'bg-gradient-secondary'
                                                                    } ?>">
                                                                        <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                                    </span>
                                                                </div>
                                                                <div class="mt-3">
                                                                    <form id="replyForm<?= $message['id'] ?>" class="reply-form">
                                                                        <div class="form-group mb-3">
                                                                            <label>Your Reply</label>
                                                                            <textarea 
                                                                                class="form-control" 
                                                                                id="replyText<?= $message['id'] ?>"
                                                                                rows="4" 
                                                                                placeholder="Type your reply here..."
                                                                                style="resize: none;"
                                                                            ></textarea>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Attachment (Optional)</label>
                                                                            <input type="file" class="form-control" id="replyAttachment<?= $message['id'] ?>">
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button 
                                                                    type="button" 
                                                                    class="btn bg-gradient-success" 
                                                                    onclick="submitReply(<?= $message['id'] ?>, '<?= $message['sender_type'] ?>')"
                                                                >
                                                                    <i class="fas fa-paper-plane me-2"></i>Send Reply
                                                                </button>
                                                                <button type="button" class="btn bg-gradient-dark" data-bs-dismiss="modal">Close</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

            <!-- Therapist Messages Tab -->
            <div class="tab-pane fade" id="therapist-messages" role="tabpanel">
                        <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-success shadow-success border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3">Therapist Support Messages</h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sender</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Message</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                <th class="text-secondary opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $therapist_messages = array_filter($messages, function($m) {
                                                return $m['sender_type'] === 'therapist' && $m['status'] !== 'archived';
                                            });
                                            if (empty($therapist_messages)): 
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <p class="text-sm mb-0">No therapist messages found</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($therapist_messages as $message): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                    class="avatar avatar-sm me-3 border-radius-lg" 
                                                                    onerror="this.src='../../assets/img/default-avatar.png';"
                                                                    alt="user image">
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    <?= ucfirst(htmlspecialchars($message['sender_type'])) ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-wrap mb-0" style="max-width: 300px;">
                                                            <?= htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : '') ?>
                                                        </p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="badge badge-sm <?= match($message['status']) {
                                                            'pending' => 'bg-gradient-warning',
                                                            'in_progress' => 'bg-gradient-info',
                                                            'resolved' => 'bg-gradient-success',
                                                            default => 'bg-gradient-secondary'
                                                        } ?>">
                                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            <?= date('M d, Y H:i', strtotime($message['created_at'])) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <!-- View Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-primary px-2 mb-0" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?= $message['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            <!-- Status Update Button & Modal -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-info px-2 mb-0"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#statusModal<?= $message['id'] ?>">
                                                                <i class="fas fa-cog"></i>
                                                            </button>

                                                            <!-- Status Update Modal -->
                                                            <div class="modal fade" id="statusModal<?= $message['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Update Status</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="d-grid gap-2">
                                                                                <button class="btn btn-warning text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'pending', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-clock me-2"></i> Pending
                                                                                </button>
                                                                                <button class="btn btn-info text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'in_progress', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-spinner me-2"></i> In Progress
                                                                                </button>
                                                                                <button class="btn btn-success text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'resolved', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-check me-2"></i> Resolved
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Archive Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-danger px-2 mb-0" 
                                                                    onclick="archiveMessage(<?= $message['id'] ?>, '<?= $message['sender_type'] ?>')">
                                                                <i class="fas fa-archive"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal for each message -->
                                                <div class="modal fade" 
                                                    id="messageModal<?= $message['id'] ?>" 
                                                    tabindex="-1" 
                                                    role="dialog"
                                                    aria-labelledby="messageModalLabel<?= $message['id'] ?>"
                                                    aria-hidden="false">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="messageModalLabel<?= $message['id'] ?>">
                                                                    Support Message Details
                                                                </h5>
                                                                <button type="button" 
                                                                    class="btn-close" 
                                                                    data-bs-dismiss="modal" 
                                                                    aria-label="Close">
                                                                </button>
                                                            </div>
                                                            <div class="reply-section">
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">Your Reply</label>
                                                                    <textarea 
                                                                        class="form-control" 
                                                                        name="reply_text" 
                                                                        rows="4" 
                                                                        placeholder="Type your reply here..."
                                                                        style="resize: none;"
                                                                    ></textarea>
                                                                </div>
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">Attachment (Optional)</label>
                                                                    <input type="file" class="form-control" name="attachment">
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" 
                                                                        class="btn bg-gradient-success send-reply-btn" 
                                                                        onclick="submitReply('<?= $message['id'] ?>', '<?= $message['sender_type'] ?>')">
                                                                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn bg-gradient-dark" 
                                                                        data-bs-dismiss="modal">
                                                                        Close
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>


            <!-- Archived Messages Tab -->
            <div class="tab-pane fade" id="archived-messages" role="tabpanel">
                <div class="card my-4">
                            <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2">
                                <div class="bg-gradient-dark shadow-dark border-radius-lg pt-4 pb-3">
                                    <h6 class="text-white text-capitalize ps-3">Archived Messages</h6>
                                </div>
                            </div>
                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sender</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Message</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Original Status</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                                <th class="text-secondary opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $archived_messages = array_filter($messages, function($m) {
                                                return $m['status'] === 'archived';
                                            });
                                            if (empty($archived_messages)): 
                                            ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4">
                                                    <p class="text-sm mb-0">No archived messages found</p>
                                                </td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach ($archived_messages as $message): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div>
                                                                <img src="../../admin_operations/get_profile_picture.php?user_id=<?= $message['sender_id'] ?>&user_type=<?= $message['sender_type'] ?>" 
                                                                    class="avatar avatar-sm me-3 border-radius-lg" 
                                                                    onerror="this.src='../../assets/img/default-avatar.png';"
                                                                    alt="user image">
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm"><?= htmlspecialchars($message['sender_name']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0">
                                                                    <?= ucfirst(htmlspecialchars($message['sender_type'])) ?>
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs text-wrap mb-0" style="max-width: 300px;">
                                                            <?= htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : '') ?>
                                                        </p>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="badge badge-sm <?= match($message['status']) {
                                                            'pending' => 'bg-gradient-warning',
                                                            'in_progress' => 'bg-gradient-info',
                                                            'resolved' => 'bg-gradient-success',
                                                            default => 'bg-gradient-secondary'
                                                        } ?>">
                                                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($message['status']))) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            <?= date('M d, Y H:i', strtotime($message['created_at'])) ?>
                                                        </span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="d-flex align-items-center gap-1">
                                                            <!-- View Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-primary px-2 mb-0" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#messageModal<?= $message['id'] ?>">
                                                                <i class="fas fa-eye"></i>
                                                            </button>

                                                            <!-- Status Update Button & Modal -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-info px-2 mb-0"
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#statusModal<?= $message['id'] ?>">
                                                                <i class="fas fa-cog"></i>
                                                            </button>

                                                            <!-- Status Update Modal -->
                                                            <div class="modal fade" id="statusModal<?= $message['id'] ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title">Update Status</h5>
                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="d-grid gap-2">
                                                                                <button class="btn btn-warning text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'pending', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-clock me-2"></i> Pending
                                                                                </button>
                                                                                <button class="btn btn-info text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'in_progress', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-spinner me-2"></i> In Progress
                                                                                </button>
                                                                                <button class="btn btn-success text-start" 
                                                                                        onclick="updateMessageStatus(<?= $message['id'] ?>, 'resolved', '<?= $message['sender_type'] ?>')"
                                                                                        data-bs-dismiss="modal">
                                                                                    <i class="fas fa-check me-2"></i> Resolved
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Archive Button -->
                                                            <button type="button" 
                                                                    class="btn btn-link text-danger px-2 mb-0" 
                                                                    onclick="archiveMessage(<?= $message['id'] ?>, '<?= $message['sender_type'] ?>')">
                                                                <i class="fas fa-archive"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Modal for each message -->
                                                <div class="modal fade" 
                                                    id="messageModal<?= $message['id'] ?>" 
                                                    tabindex="-1" 
                                                    role="dialog"
                                                    aria-labelledby="messageModalLabel<?= $message['id'] ?>"
                                                    aria-hidden="false">
                                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="messageModalLabel<?= $message['id'] ?>">
                                                                    Support Message Details
                                                                </h5>
                                                                <button type="button" 
                                                                    class="btn-close" 
                                                                    data-bs-dismiss="modal" 
                                                                    aria-label="Close">
                                                                </button>
                                                            </div>
                                                            <div class="reply-section">
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">Your Reply</label>
                                                                    <textarea 
                                                                        class="form-control" 
                                                                        name="reply_text" 
                                                                        rows="4" 
                                                                        placeholder="Type your reply here..."
                                                                        style="resize: none;"
                                                                    ></textarea>
                                                                </div>
                                                                <div class="form-group mb-3">
                                                                    <label class="form-label">Attachment (Optional)</label>
                                                                    <input type="file" class="form-control" name="attachment">
                                                                </div>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" 
                                                                        class="btn bg-gradient-success send-reply-btn" 
                                                                        onclick="submitReply('<?= $message['id'] ?>', '<?= $message['sender_type'] ?>')">
                                                                        <i class="fas fa-paper-plane me-2"></i>Send Reply
                                                                    </button>
                                                                    <button type="button" 
                                                                        class="btn bg-gradient-dark" 
                                                                        data-bs-dismiss="modal">
                                                                        Close
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
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

</body>
</html>