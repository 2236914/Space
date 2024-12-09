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

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Function to export mood logs to CSV
function exportMoodLogsToCSV($moodLogs) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="mood_logs_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Date', 'Emoji', 'Mood', 'Description']);
    
    foreach ($moodLogs as $log) {
        fputcsv($output, [
            date('Y-m-d H:i:s', strtotime($log['log_date'])),
            $log['selected_emoji'],
            $log['mood_name'],
            $log['description']
        ]);
    }
    
    fclose($output);
    exit();
}

// Handle CSV export if requested
if (isset($_POST['export_csv'])) {
    try {
        // Get all mood entries for the user
        $query = "SELECT ml.moodlog_id, ml.srcode, ml.mood_name, ml.description, 
                        ml.selected_emoji, ml.log_date
                 FROM moodlog ml 
                 WHERE ml.srcode = :srcode 
                 ORDER BY ml.log_date DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute(['srcode' => $_SESSION['srcode']]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="mood_logs_' . date('Y-m-d_His') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Create output stream
        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, ['Date', 'Emoji', 'Mood', 'Description']);

        // Add data rows
        foreach ($results as $row) {
            fputcsv($output, [
                date('Y-m-d H:i:s', strtotime($row['log_date'])),
                $row['selected_emoji'],
                $row['mood_name'],
                $row['description']
            ]);
        }

        fclose($output);
        exit();

    } catch (Exception $e) {
        error_log("CSV Export Error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to export data. Please try again.";
    }
}

// Pagination setup
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Base query
$baseQuery = "FROM moodlog WHERE srcode = :srcode";
$params = ['srcode' => $_SESSION['srcode']];

// Add date range filter if dates are set
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $baseQuery .= " AND DATE(log_date) BETWEEN :start_date AND :end_date";
    $params['start_date'] = $_GET['start_date'];
    $params['end_date'] = $_GET['end_date'];
}

// Get total number of records
$count_query = "SELECT COUNT(*) as total FROM moodlog WHERE srcode = :srcode";
$count_stmt = $pdo->prepare($count_query);
$count_stmt->execute(['srcode' => $_SESSION['srcode']]);
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $items_per_page);

// Get records for current page
$query = "SELECT * FROM moodlog WHERE srcode = :srcode 
          ORDER BY log_date DESC LIMIT :offset, :items_per_page";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':srcode', $_SESSION['srcode'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':items_per_page', $items_per_page, PDO::PARAM_INT);
$stmt->execute();
$moodLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    // Debug: Print session variable
    error_log("Current srcode: " . $_SESSION['srcode']);
    
    // First, let's check if there are any sessions at all with a simple query
    $check_stmt = $pdo->prepare("
        SELECT * FROM therapy_sessions 
        WHERE srcode = :srcode
    ");
    $check_stmt->bindValue(':srcode', $_SESSION['srcode'], PDO::PARAM_INT);
    $check_stmt->execute();
    $basic_results = $check_stmt->fetchAll(PDO::FETCH_ASSOC);
    error_log("Basic query results count: " . count($basic_results));
    error_log("Basic query results: " . print_r($basic_results, true));
    
    // If we find sessions, then try the full query
    if (!empty($basic_results)) {
        $therapy_stmt = $pdo->prepare("
            SELECT 
                ts.session_id,
                ts.session_date,
                ts.session_time,
                ts.session_type,
                ts.status,
                t.firstname as therapist_firstname,
                t.lastname as therapist_lastname
            FROM therapy_sessions ts
            JOIN therapists t ON ts.therapist_id = t.therapist_id
            WHERE ts.srcode = :srcode
            ORDER BY ts.session_date DESC, ts.session_time DESC
        ");
        
        $therapy_stmt->bindValue(':srcode', $_SESSION['srcode'], PDO::PARAM_INT);
        $therapy_stmt->execute();
        $therapy_sessions = $therapy_stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Full query results count: " . count($therapy_sessions));
    } else {
        $therapy_sessions = [];
        error_log("No sessions found in basic query");
    }

} catch (PDOException $e) {
    error_log("Error fetching therapy sessions: " . $e->getMessage());
    error_log("SQL State: " . $e->errorInfo[0]);
    $therapy_sessions = [];
}

// Debug output at the top of the page
echo "<!-- Debug Info: -->";
echo "<!-- Session srcode: " . $_SESSION['srcode'] . " -->";
echo "<!-- Therapy sessions count: " . count($therapy_sessions) . " -->";

try {
    // Fetch community activities (posts) with engagement metrics
    $community_stmt = $pdo->prepare("
        SELECT 
            p.post_id,
            p.created_at,
            p.content,
            p.status,
            p.post_type,
            p.image_name,
            COUNT(DISTINCT l.like_id) as like_count,
            COUNT(DISTINCT c.comment_id) as comment_count,
            COUNT(DISTINCT r.report_id) as report_count
        FROM posts p
        LEFT JOIN likes l ON p.post_id = l.post_id
        LEFT JOIN comments c ON p.post_id = c.post_id
        LEFT JOIN reports r ON p.post_id = r.post_id
        WHERE p.username = :username
        GROUP BY p.post_id
        ORDER BY p.created_at DESC
    ");
    
    $community_stmt->execute(['username' => $_SESSION['username']]);
    $community_activities = $community_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching community data: " . $e->getMessage());
    $community_activities = [];
}

// Add this at the top of your PHP section where you handle exports
if (isset($_POST['export_type'])) {
    try {
        $query = "";
        if ($_POST['export_type'] === 'therapy_all') {
            // Export all sessions
            $query = "SELECT 
                ts.session_date, 
                ts.session_time, 
                ts.session_type,
                ts.status,
                t.firstname as therapist_firstname,
                t.lastname as therapist_lastname,
                sf.rating,
                sf.feedback_text
            FROM therapy_sessions ts
            JOIN therapists t ON ts.therapist_id = t.therapist_id
            LEFT JOIN session_feedback sf ON ts.session_id = sf.session_id
            WHERE ts.student_id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$_SESSION['user_id']]);
        } else {
            // Export selected sessions
            $selected_sessions = json_decode($_POST['selected_sessions'] ?? '[]', true);
            if (empty($selected_sessions)) {
                throw new Exception('No sessions selected');
            }
            $placeholders = str_repeat('?,', count($selected_sessions) - 1) . '?';
            $query = "SELECT 
                ts.session_date, 
                ts.session_time, 
                ts.session_type,
                ts.status,
                t.firstname as therapist_firstname,
                t.lastname as therapist_lastname,
                sf.rating,
                sf.feedback_text
            FROM therapy_sessions ts
            JOIN therapists t ON ts.therapist_id = t.therapist_id
            LEFT JOIN session_feedback sf ON ts.session_id = sf.session_id
            WHERE ts.session_id IN ($placeholders)";
            $stmt = $pdo->prepare($query);
            $stmt->execute($selected_sessions);
        }

        $sessions_to_export = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="therapy_sessions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, ['Date', 'Time', 'Therapist', 'Type', 'Status', 'Rating', 'Feedback']);
        
        // Add data rows
        foreach ($sessions_to_export as $session) {
            fputcsv($output, [
                $session['session_date'],
                $session['session_time'],
                "Dr. {$session['therapist_firstname']} {$session['therapist_lastname']}",
                $session['session_type'],
                $session['status'],
                $session['rating'] ?? 'N/A',
                $session['feedback_text'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit();

    } catch (Exception $e) {
        error_log("Export error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to export sessions. Please try again.";
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
    <title>Generate Reports</title>
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,900" />
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.2.0" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
    <style>
  /* Optional: Add transition for smooth hiding/showing of rows */
  table tbody tr {
      transition: all 0.3s ease;
  }

  /* Optional: Style for no results */
  table tbody:not(:has(tr[style*="display: table-row"], tr:not([style*="display: none"])))::after {
      content: "No matching records found";
      display: block;
      text-align: center;
      padding: 1rem;
      color: #666;
      font-style: italic;
  }
.journal-modal .swal2-html-container {
    max-height: 70vh;
    overflow-y: auto;
}

.journal-modal .swal2-title {
    padding: 0;
    margin-bottom: 1rem;
}
  </style>

</head>

<body class="g-sidenav-show bg-gray-200">
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
            <ul class="navbar-nav ms-auto">
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
<div class="container-fluid my-3 py-4">
    <div class="row justify-space-between py-2">
        <div class="col-lg-8 mx-auto">
        <div class="nav-wrapper position-relative end-0">
            <ul class="nav nav-pills nav-fill p-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center active" 
                data-bs-toggle="tab" 
                href="#moodreports" 
                role="tab" 
                aria-controls="moodreports"
                aria-selected="true">
                    <i class="material-symbols-rounded text-sm me-2">mood</i> Mood Reports
                    <?php
                    // Get count of mood entries
                    try {
                        $mood_count_stmt = $pdo->prepare("
                            SELECT COUNT(*) as count 
                            FROM moodlog 
                            WHERE srcode = :srcode
                        ");
                        $mood_count_stmt->execute(['srcode' => $_SESSION['srcode']]);
                        $mood_count = $mood_count_stmt->fetchColumn();
                        
                        if ($mood_count > 0):
                    ?>
                        <span class="badge bg-gradient-success ms-2"><?= $mood_count ?></span>
                    <?php 
                        endif;
                    } catch (PDOException $e) {
                        error_log("Error counting mood entries: " . $e->getMessage());
                    }
                    ?>
                </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
                    data-bs-toggle="tab" 
                    href="#journal" 
                    role="tab" 
                    aria-selected="false">
                        <i class="material-symbols-rounded text-sm me-2">book</i> Journal Entries
                        <?php
                        // Get count of journal entries
                        try {
                            $journal_count_stmt = $pdo->prepare("
                                SELECT COUNT(*) as count 
                                FROM journal_entries 
                                WHERE srcode = :srcode
                            ");
                            $journal_count_stmt->execute(['srcode' => $_SESSION['srcode']]);
                            $journal_count = $journal_count_stmt->fetchColumn();
                            
                            if ($journal_count > 0):
                        ?>
                            <span class="badge bg-gradient-info ms-2"><?= $journal_count ?></span>
                        <?php 
                            endif;
                        } catch (PDOException $e) {
                            error_log("Error counting journal entries: " . $e->getMessage());
                        }
                        ?>
                    </a>
            </li>
            <li class="nav-item">
    <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center" 
       data-bs-toggle="tab" 
       href="#session-feedback" 
       role="tab" 
       aria-controls="session-feedback"
       aria-selected="false">
        <i class="material-symbols-rounded text-sm me-2">psychology</i> Feedback
        <?php
        // Get count of only existing feedback
        try {
            $feedback_count_stmt = $pdo->prepare("
                SELECT 
                    COUNT(DISTINCT sf.feedback_id) as feedback_count,
                    SUM(CASE WHEN sf.follow_up = 'yes' THEN 1 ELSE 0 END) as follow_up_needed
                FROM session_feedback sf
                JOIN therapy_sessions ts ON sf.session_id = ts.session_id
                WHERE ts.srcode = :srcode
            ");
            $feedback_count_stmt->execute(['srcode' => $_SESSION['srcode']]);
            $feedback_stats = $feedback_count_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($feedback_stats['feedback_count'] > 0):
        ?>
            <span class="badge bg-gradient-primary ms-2" title="Total Feedback">
                <?= $feedback_stats['feedback_count'] ?>
            </span>
            <?php if ($feedback_stats['follow_up_needed'] > 0): ?>
                <span class="badge bg-gradient-warning ms-1" title="Follow-ups Needed">
                    <i class="material-symbols-rounded text-xs">event_repeat</i>
                    <?= $feedback_stats['follow_up_needed'] ?>
                </span>
            <?php endif; ?>
        <?php 
            endif;
        } catch (PDOException $e) {
            error_log("Error counting session feedback: " . $e->getMessage());
        }
        ?>
    </a>
</li>
          </ul>
        </div>
        </div>

            
                <div class="tab-content py-2">
                    <!-- Mood Reports Tab -->
                    <div class="tab-pane fade show active" id="moodreports" role="tabpanel" aria-labelledby="moodreports-tab">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header p-4">
                                        <div class="row align-items-center">
                                            <!-- Left side: Title -->
                                            <div class="col">
                                                <h5 class="mb-0">Moodtracker Reports</h5>
                                                <p class="text-sm text-secondary mb-0">Track and analyze your mood patterns over time</p>
                                            </div>
                                            
                                            <!-- Right side: Search and Export -->
                                            <div class="col-auto d-flex align-items-center gap-3">
                                                <div class="input-group input-group-outline" style="width: 250px;">
                                                    <input type="text" 
                                                        class="form-control" 
                                                        id="moodSearchInput" 
                                                        placeholder="Search records...">
                                                </div>
                                                <form method="POST" style="display: inline;">
                                                    <button type="submit" name="export_csv" class="btn btn-info btn-sm">
                                                        <i class="material-symbols-rounded me-1" style="font-size: 20px;">download</i> CSV
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body px-4 pt-2">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Emoji</th>
                                                        <th>Mood</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($moodLogs)): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center py-4">
                                                                <p class="text-secondary mb-0">No mood logs found</p>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($moodLogs as $log): 
                                                            $description = strlen($log['description']) > 20 ? 
                                                                substr($log['description'], 0, 20) . '...' : 
                                                                $log['description'];
                                                        ?>
                                                            <tr>
                                                                <td class="text-sm"><?php echo date('M d, Y h:i A', strtotime($log['log_date'])); ?></td>
                                                                <td><?php echo $log['selected_emoji']; ?></td>
                                                                <td class="text-sm"><?php echo $log['mood_name']; ?></td>
                                                                <td class="text-sm"><?php echo htmlspecialchars($description); ?></td>
                                                                <td>
                                                                    <button type="button" 
                                                                            class="btn btn-link btn-sm p-1 mb-0" 
                                                                            onclick="viewMoodLog('<?php echo $log['moodlog_id']; ?>')"
                                                                            data-bs-toggle="tooltip" 
                                                                            title="View Details">
                                                                        <i class="material-symbols-rounded text-info" 
                                                                        style="font-size: 20px;">visibility</i>
                                                                    </button>
                                                                </td>
                                                            </tr>
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
                    <!-- Journal Entries Report Tab Content -->
                    <div class="tab-pane fade" id="journal" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header mx-4">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="mb-0">Journal Entries Report</h5>
                                                <p class="text-sm mb-0">Your personal journal entries and reflections</p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <!-- Search Input -->
                                                <div class="input-group input-group-outline mb-2">
                                                    <input type="text" class="form-control" id="journalSearchInput" placeholder="Search entries...">
                                                </div>
                                                <!-- Export Button -->
                                                <form method="POST" action="../../admin_operations/export_journal_data.php">
                                                    <input type="hidden" name="srcode" value="<?php echo $_SESSION['srcode']; ?>">
                                                    <button type="submit" name="export_journal_csv" class="btn btn-primary btn-sm bg-gradient-info">
                                                        <i class="material-symbols-rounded me-2">download</i>
                                                        Export CSV
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body px-4 pt-2">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0" id="journalTable">
                                                <thead>
                                                    <tr>
                                                        <th>Entry Date</th>
                                                        <th>Title</th>
                                                        <th>Content Preview</th>
                                                        <th>Mood</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    try {
                                                        // Updated query to match your table structure
                                                        $journal_stmt = $pdo->prepare("
                                                            SELECT 
                                                                entry_id,
                                                                title,
                                                                content,
                                                                mood,
                                                                entry_date,
                                                                created_at
                                                            FROM journal_entries 
                                                            WHERE srcode = :srcode
                                                            ORDER BY entry_date DESC, created_at DESC
                                                        ");
                                                        
                                                        $journal_stmt->execute(['srcode' => $_SESSION['srcode']]);
                                                        $journal_entries = $journal_stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        if ($journal_entries && count($journal_entries) > 0) {
                                                            foreach ($journal_entries as $entry):
                                                    ?>
                                                                <tr>
                                                                    <td class="text-sm">
                                                                        <?php echo date('M d, Y', strtotime($entry['entry_date'])); ?>
                                                                    </td>
                                                                    <td class="text-sm">
                                                                        <?php echo htmlspecialchars($entry['title']); ?>
                                                                    </td>
                                                                    <td class="text-sm">
                                                                        <?php 
                                                                        $content = strlen($entry['content']) > 50 ? 
                                                                            substr($entry['content'], 0, 50) . '...' : 
                                                                            $entry['content'];
                                                                        echo htmlspecialchars($content);
                                                                        ?>
                                                                    </td>
                                                                    <td class="text-sm">
                                                                        <span class="badge bg-gradient-info">
                                                                            <?php echo ucfirst($entry['mood']); ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-link btn-sm p-1"
                                                                                onclick="viewJournalEntry('<?php echo $entry['entry_id']; ?>')"
                                                                                data-bs-toggle="tooltip" 
                                                                                title="View Details">
                                                                            <i class="material-symbols-rounded text-info" style="font-size: 20px;">visibility</i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                    <?php 
                                                            endforeach;
                                                        } else {
                                                            // No entries found
                                                            echo '<tr><td colspan="5" class="text-center">No journal entries found.</td></tr>';
                                                        }
                                                    } catch (PDOException $e) {
                                                        error_log("Error fetching journal entries: " . $e->getMessage());
                                                        echo '<tr><td colspan="5" class="text-center text-danger">Error loading journal entries.</td></tr>';
                                                    }
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Feedback Report Tab Content -->
                    <div class="tab-pane fade" id="session-feedback" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header mx-4">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="mb-0">Session Feedback Reports</h5>
                                                <p class="text-sm mb-0">Therapy session feedback and recommendations</p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <!-- Search Input -->
                                                <div class="input-group input-group-outline mb-2">
                                                    <input type="text" class="form-control" id="feedbackSearchInput" placeholder="Search feedback...">
                                                </div>
                                                <!-- Export Button -->
                                                <form method="POST" action="../../admin_operations/export_feedback_data.php">
                                                    <input type="hidden" name="srcode" value="<?php echo $_SESSION['srcode']; ?>">
                                                    <button type="submit" name="export_feedback_csv" class="btn btn-primary btn-sm bg-gradient-warning">
                                                        <i class="material-symbols-rounded me-2">download</i>
                                                        Export CSV
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body px-4 pt-2">
                                        <div class="table-responsive">
                                            <table class="table align-items-center mb-0" id="feedbackTable">
                                                <thead>
                                                    <tr>
                                                        <th>Session Date</th>
                                                        <th>Diagnosis</th>
                                                        <th>Recommendations</th>
                                                        <th>Follow-up</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    try {
                                                        $feedback_stmt = $pdo->prepare("
                                                            SELECT 
                                                                sf.feedback_id,
                                                                sf.diagnosis,
                                                                sf.recommendations,
                                                                sf.follow_up,
                                                                sf.follow_up_notes,
                                                                sf.created_at,
                                                                ts.session_date
                                                            FROM session_feedback sf
                                                            JOIN therapy_sessions ts ON sf.session_id = ts.session_id
                                                            WHERE ts.srcode = :srcode
                                                            ORDER BY ts.session_date DESC
                                                        ");
                                                        
                                                        $feedback_stmt->execute(['srcode' => $_SESSION['srcode']]);
                                                        $feedbacks = $feedback_stmt->fetchAll(PDO::FETCH_ASSOC);

                                                        foreach ($feedbacks as $feedback):
                                                    ?>
                                                        <tr>
                                                            <td class="text-sm">
                                                                <?php echo date('M d, Y', strtotime($feedback['session_date'])); ?>
                                                            </td>
                                                            <td class="text-sm">
                                                                <?php 
                                                                $diagnosis = strlen($feedback['diagnosis']) > 50 ? 
                                                                    substr($feedback['diagnosis'], 0, 50) . '...' : 
                                                                    $feedback['diagnosis'];
                                                                echo htmlspecialchars($diagnosis);
                                                                ?>
                                                            </td>
                                                            <td class="text-sm">
                                                                <?php 
                                                                $recommendations = strlen($feedback['recommendations']) > 50 ? 
                                                                    substr($feedback['recommendations'], 0, 50) . '...' : 
                                                                    $feedback['recommendations'];
                                                                echo htmlspecialchars($recommendations);
                                                                ?>
                                                            </td>
                                                            <td class="text-sm">
                                                                <span class="badge bg-gradient-<?php echo $feedback['follow_up'] === 'yes' ? 'warning' : 'success'; ?>">
                                                                    <?php echo $feedback['follow_up'] === 'yes' ? 'Follow-up Needed' : 'No Follow-up'; ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <button type="button" class="btn btn-link btn-sm p-1"
                                                                        onclick="viewFeedbackDetails('<?php echo $feedback['feedback_id']; ?>')"
                                                                        data-bs-toggle="tooltip" 
                                                                        title="View Details">
                                                                    <i class="material-symbols-rounded text-info" style="font-size: 20px;">visibility</i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php 
                                                        endforeach;
                                                    } catch (PDOException $e) {
                                                        error_log("Error fetching feedback: " . $e->getMessage());
                                                    }
                                                    ?>
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
    </div>

    <?php include_once('support_messages_modal.php'); ?>  
</main>
  <!-- Core JS Files -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/support.js"></script>
  <script src="../../assets/js/support-messages.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
  <script src="../../assets/js/therapyreports.js"></script>
  <script src="../../assets/js/reports.js"></script>
  <script src="../../assets/js/generate-reports.js"></script>
  <script>
    function toggleSelectAll(source) {
        const checkboxes = document.getElementsByClassName('row-checkbox');
        for (let checkbox of checkboxes) {
            checkbox.checked = source.checked;
        }
    }

    // Listen for individual checkbox changes
    document.addEventListener('DOMContentLoaded', function() {
        const rowCheckboxes = document.getElementsByClassName('row-checkbox');
        const selectAllCheckbox = document.getElementById('selectAll');
        
        for (let checkbox of rowCheckboxes) {
            checkbox.addEventListener('change', function() {
                const anyChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                
                if (anyChecked && !allChecked) {
                    selectAllCheckbox.indeterminate = true;
                    selectAllCheckbox.checked = false;
                } else {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = allChecked;
                }
            });
        }
    });
  </script>
  <script>
function viewJournalEntry(entryId) {
    fetch(`../../admin_operations/fetch_journal_entry.php?entry_id=${entryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const entry = data.entry;
                Swal.fire({
                    title: `<h5 class="text-dark">${entry.title}</h5>`,
                    html: `
                        <div class="text-start">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <small class="text-muted">Date:</small>
                                    <p class="mb-0">${new Date(entry.entry_date).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    })}</p>
                                </div>
                                <span class="badge bg-gradient-info">
                                    ${entry.mood}
                                </span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Content:</small>
                                <p class="mt-2" style="white-space: pre-wrap;">${entry.content}</p>
                            </div>
                        </div>
                    `,
                    width: '42rem',
                    padding: '2rem',
                    showCloseButton: true,
                    showConfirmButton: false,
                    customClass: {
                        container: 'journal-modal',
                        popup: 'shadow-lg',
                        htmlContainer: 'text-start'
                    }
                });
            } else {
                throw new Error(data.message || 'Could not fetch journal entry details.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'An error occurred while fetching the entry.',
                customClass: {
                    popup: 'shadow-lg'
                }
            });
        });
}

// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('journalSearchInput');
    const table = document.getElementById('journalTable');

    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            Array.from(rows).forEach(row => {
                if (row.cells.length > 1) {
                    const title = row.cells[1].textContent.toLowerCase();
                    const content = row.cells[2].textContent.toLowerCase();
                    const mood = row.cells[3].textContent.toLowerCase();
                    
                    const matches = 
                        title.includes(searchText) || 
                        content.includes(searchText) || 
                        mood.includes(searchText);

                    row.style.display = matches ? '' : 'none';
                }
            });
        });
    }
});
</script>
  <script>
    // Search functionality
    document.getElementById('moodSearchInput').addEventListener('keyup', function() {
        let searchText = this.value.toLowerCase();
        let table = document.getElementById('moodLogsTable');
        let rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        for (let row of rows) {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchText) ? '' : 'none';
        }
    });

    // View mood log details
    function viewMoodLog(moodlogId) {
        // Fetch mood log details using AJAX
        fetch(`../../admin_operations/get_moodlog_details.php?id=${moodlogId}`)
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    title: 'Mood Log Details',
                    html: `
                        <div class="text-start">
                            <p><strong>Date:</strong> ${new Date(data.log_date).toLocaleString()}</p>
                            <p><strong>Mood:</strong> ${data.mood_name}</p>
                            <p><strong>Emoji:</strong> ${data.selected_emoji}</p>
                            <p><strong>Description:</strong> ${data.description}</p>
                        </div>
                    `,
                    confirmButtonText: 'Close',
                    customClass: {
                        confirmButton: 'btn btn-info'
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load mood log details'
                });
            });
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });

    function toggleAllCheckboxes(source) {
        const checkboxes = document.getElementsByClassName('row-checkbox');
        for(let checkbox of checkboxes) {
            checkbox.checked = source.checked;
        }
    }
  </script>
  <script>
    // Handle emoji rendering
    document.querySelectorAll('[ms-code-emoji]').forEach(element => {
      var imageUrl = element.getAttribute('ms-code-emoji');
      if (imageUrl) {
        var img = document.createElement('img');
        img.src = imageUrl;
        img.style.width = '35px';
        img.style.height = '35px';
        img.style.verticalAlign = 'middle';
        element.innerHTML = '';
        element.appendChild(img);
      }
    });

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
  document.addEventListener('DOMContentLoaded', function() {
    // Get all pagination links
    const paginationLinks = document.querySelectorAll('.pagination .page-link');
    
    paginationLinks.forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Get current URL and search parameters
        const url = new URL(this.href);
        const searchInput = document.getElementById('moodSearchInput');
        
        // If there's a search term, add it to pagination URL
        if (searchInput && searchInput.value) {
          url.searchParams.set('search', searchInput.value);
        }
        
        // Navigate to the new URL
        window.location.href = url.toString();
      });
    });
  });
  </script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('moodSearchInput');
    const tableRows = document.querySelectorAll('table tbody tr');

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            // Get text content from each cell except the action column
            const date = row.cells[1].textContent.toLowerCase();
            const mood = row.cells[3].textContent.toLowerCase();
            const description = row.cells[4].textContent.toLowerCase();

            // Check if any cell contains the search term
            const matches = 
                date.includes(searchTerm) || 
                mood.includes(searchTerm) || 
                description.includes(searchTerm);

            // Show/hide row based on match
            row.style.display = matches ? '' : 'none';
        });
    });

    // Add clear search functionality
    searchInput.addEventListener('search', function() {
        if (this.value === '') {
            tableRows.forEach(row => {
                row.style.display = '';
            });
        }
    });
  });
  </script>
  <script>
document.addEventListener('DOMContentLoaded', function() {
    // Feedback search functionality
    const feedbackSearchInput = document.getElementById('feedbackSearchInput');
    const feedbackTable = document.getElementById('feedbackTable');

    feedbackSearchInput.addEventListener('keyup', function() {
        const searchText = this.value.toLowerCase();
        const rows = feedbackTable.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

        Array.from(rows).forEach(row => {
            const diagnosis = row.cells[1].textContent.toLowerCase();
            const recommendations = row.cells[2].textContent.toLowerCase();
            const followUp = row.cells[3].textContent.toLowerCase();
            
            const matches = 
                diagnosis.includes(searchText) || 
                recommendations.includes(searchText) || 
                followUp.includes(searchText);

            row.style.display = matches ? '' : 'none';
        });
    });
});

function viewFeedbackDetails(feedbackId) {
    // Add your view feedback details logic here
    window.location.href = `view_feedback.php?id=${feedbackId}`;
}
</script>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle Export CSV button
    const exportBtn = document.querySelector('.export-csv');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            // Add export type for therapy sessions
            const form = document.createElement('form');
            form.method = 'POST';
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'export_type';
            input.value = 'therapy';
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Add tooltips for feedback icons
    const feedbackIcons = document.querySelectorAll('.feedback-icon');
    feedbackIcons.forEach(icon => {
        icon.title = icon.classList.contains('text-secondary') ? 'No feedback yet' : 'Has feedback';
    });

    // Add status color classes
    const statusBadges = document.querySelectorAll('.status-badge');
    statusBadges.forEach(badge => {
        const status = badge.textContent.toLowerCase().trim();
        switch(status) {
            case 'cancelled':
                badge.classList.add('bg-danger');
                break;
            case 'completed':
                badge.classList.add('bg-success');
                break;
            case 'pending':
                badge.classList.add('bg-warning');
                break;
            case 'confirmed':
                badge.classList.add('bg-info');
                break;
        }
    });
  });
  </script>
</body>
</html>