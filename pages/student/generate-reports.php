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
    $query = "SELECT * FROM moodlog WHERE srcode = :srcode ORDER BY log_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['srcode' => $_SESSION['srcode']]);
    $moodLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    exportMoodLogsToCSV($moodLogs);
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title>Space</title>
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
    <div class="container-fluid my-3 py-4">
        <div class="row mb-5">
            <div class="col-lg-12 mt-lg-0 mt-4">
                <div class="col-12">
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#moodreports" role="tab" aria-controls="moodreports" aria-selected="true">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        mood
                                    </span>
                                   Mood
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#self-care-tools" role="tab" aria-controls="self-care-tools" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        self_improvement
                                    </span>
                                    Self-Care Tools
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#community" role="tab" aria-controls="community" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        diversity_1
                                    </span>
                                    Community
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#activity-logs" role="tab" aria-controls="activity-logs" aria-selected="false">
                                    <span class="material-symbols-rounded align-middle mb-1">
                                        history
                                    </span>
                                    Activity Logs
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Mood Logs Report -->
                <div class="tab-content py-2">
                    <!-- Mood Tracker Tab Content -->
                    <div class="tab-pane fade show active" id="moodreports" role="tabpanel">
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
                                            <table class="table table-flush" id="moodLogsTable">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 40px;">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                                            </div>
                                                        </th>
                                                        <th>Date</th>
                                                        <th>Emoji</th>
                                                        <th>Mood</th>
                                                        <th>Description</th>
                                                        <th style="width: 60px;">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($moodLogs)): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center py-4">
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
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input row-checkbox" type="checkbox" value="<?php echo $log['moodlog_id']; ?>">
                                                                    </div>
                                                                </td>
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

                    <!-- Self-Care Tools Tab Content -->
                    <div class="tab-pane fade" id="self-care-tools" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header mx-4">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="mb-0">Self-Care Tools Reports</h5>
                                                <p class="text-sm mb-0">Track your self-care activities and progress</p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive">
                                                    <i class="material-symbols-rounded" style="font-size: 20px;">archive</i>
                                                </button>
                                                <button class="btn btn-primary btn-sm bg-gradient-info btn-outline-info me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Export CSV">
                                                    <i class="material-symbols-rounded me-2" style="font-size: 20px;">download</i>
                                                    CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mt-4">
                                            <div class="col-4">
                                                <div class="input-group input-group-static mb-3">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input class="form-control" id="dateRange" placeholder="Select date range" type="text">
                                                </div>
                                            </div>

                                            <div cs="col-4"></div>

                                            <div class="col-4">
                                                <div class="input-group input-group-outline">
                                                    <input type="text" class="form-control" id="searchInput" placeholder="Search records..." oninput="searchMenu()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-flush" id="datatable-search">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                            </div>
                                                        </th>
                                                        <th>Date</th>
                                                        <th>Emoji</th>
                                                        <th>Mood Name</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-sm font-weight-normal">Brielle Williamson</td>
                                                        <td class="text-sm font-weight-normal">Integration Specialist</td>
                                                        <td class="text-sm font-weight-normal">New York</td>
                                                        <td class="text-sm font-weight-normal">2012/12/02</td>
                                                        <td class="text-sm font-weight-normal">$372,000</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <ul class="pagination pagination-info justify-content-end pe-4">
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Previous">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_left
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">1</a>
                                                </li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#link">2</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">3</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">4</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">5</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Next">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_right
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Community Tab Content -->
                    <div class="tab-pane fade" id="community" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header mx-4">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="mb-0">Community Engagement Reports</h5>
                                                <p class="text-sm mb-0">View your community interactions and contributions</p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive">
                                                    <i class="material-symbols-rounded" style="font-size: 20px;">archive</i>
                                                </button>
                                                <button class="btn btn-primary btn-sm bg-gradient-info btn-outline-info me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Export CSV">
                                                    <i class="material-symbols-rounded me-2" style="font-size: 20px;">download</i>
                                                    CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mt-4">
                                            <div class="col-4">
                                                <div class="input-group input-group-static">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input class="form-control datepicker" placeholder="Select date range" type="text">
                                                </div>
                                            </div>

                                            <div class="col-4"></div>

                                            <div class="col-4">
                                                <div class="input-group input-group-outline">
                                                    <input type="text" class="form-control" id="searchInput" placeholder="Search records..." oninput="searchMenu()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-flush" id="datatable-search">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                            </div>
                                                        </th>
                                                        <th>Date</th>
                                                        <th>Emoji</th>
                                                        <th>Mood Name</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-sm font-weight-normal">Brielle Williamson</td>
                                                        <td class="text-sm font-weight-normal">Integration Specialist</td>
                                                        <td class="text-sm font-weight-normal">New York</td>
                                                        <td class="text-sm font-weight-normal">2012/12/02</td>
                                                        <td class="text-sm font-weight-normal">$372,000</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <ul class="pagination pagination-info justify-content-end pe-4">
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Previous">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_left
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">1</a>
                                                </li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#link">2</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">3</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">4</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">5</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Next">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_right
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Logs Tab Content -->
                    <div class="tab-pane fade" id="activity-logs" role="tabpanel">
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card pt-3 pb-3">
                                    <div class="card-header mx-4">
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <h5 class="mb-0">Activity Logs Reports</h5>
                                                <p class="text-sm mb-0">Review your activity history and usage patterns</p>
                                            </div>
                                            <div class="col-4 text-end">
                                                <button class="btn btn-sm btn-outline-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="Archive">
                                                    <i class="material-symbols-rounded" style="font-size: 20px;">archive</i>
                                                </button>
                                                <button class="btn btn-primary btn-sm bg-gradient-info btn-outline-info me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Export CSV">
                                                    <i class="material-symbols-rounded me-2" style="font-size: 20px;">download</i>
                                                    CSV
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body">
                                        <div class="row mt-4">
                                            <div class="col-4">
                                                <div class="input-group input-group-static">
                                                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                                    <input class="form-control datepicker" placeholder="Select date range" type="text">
                                                </div>
                                            </div>

                                            <div class="col-4"></div>

                                            <div class="col-4">
                                                <div class="input-group input-group-outline">
                                                    <input type="text" class="form-control" id="searchInput" placeholder="Search records..." oninput="searchMenu()">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mt-4">
                                            <table class="table table-flush" id="datatable-search">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                                            </div>
                                                        </th>
                                                        <th>Date</th>
                                                        <th>Emoji</th>
                                                        <th>Mood Name</th>
                                                        <th>Description</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input" type="checkbox">
                                                            </div>
                                                        </td>
                                                        <td class="text-sm font-weight-normal">Brielle Williamson</td>
                                                        <td class="text-sm font-weight-normal">Integration Specialist</td>
                                                        <td class="text-sm font-weight-normal">New York</td>
                                                        <td class="text-sm font-weight-normal">2012/12/02</td>
                                                        <td class="text-sm font-weight-normal">$372,000</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <ul class="pagination pagination-info justify-content-end pe-4">
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Previous">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_left
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">1</a>
                                                </li>
                                                <li class="page-item active">
                                                    <a class="page-link" href="#link">2</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">3</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">4</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link">5</a>
                                                </li>
                                                <li class="page-item">
                                                    <a class="page-link" href="#link" aria-label="Next">
                                                        <span aria-hidden="true">
                                                            <span class="material-symbols-rounded">
                                                                keyboard_arrow_right
                                                            </span>
                                                        </span>
                                                    </a>
                                                </li>
                                            </ul>
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

  

  </main>
  <!-- Core JS Files -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chart.min.js"></script>
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/support.js"></script>
  <script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
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
    // Initialize date range picker
    flatpickr("#dateRange", {
      mode: "range",
      dateFormat: "Y-m-d", 
      maxDate: "today",
      rangeSeparator: " to ",
      defaultDate: [new Date().fp_incr(-30), new Date()], // Last 30 days by default
      onChange: function(selectedDates, dateStr) {
        if (selectedDates.length === 2) {
          filterByDateRange(selectedDates[0], selectedDates[1]);
        }
      }
    });

    // Function to filter mood logs by date range
    function filterByDateRange(startDate, endDate) {
      // Format dates for URL
      const formattedStartDate = startDate.toISOString().split('T')[0];
      const formattedEndDate = endDate.toISOString().split('T')[0];
      
      // Get current URL and update/add date parameters
      const url = new URL(window.location.href);
      url.searchParams.set('start_date', formattedStartDate);
      url.searchParams.set('end_date', formattedEndDate);
      
      // Maintain current page and search parameters if they exist
      const currentSearch = document.getElementById('moodSearchInput')?.value;
      if (currentSearch) {
        url.searchParams.set('search', currentSearch);
      }
      
      // Reset to first page when filtering
      url.searchParams.set('page', '1');
      
      // Navigate to filtered URL
      window.location.href = url.toString();
    }
  });
  </script>
</body>
</html>