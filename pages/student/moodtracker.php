<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    error_log("DEBUG TRACKER - Session started");
}

error_log("DEBUG TRACKER - Script accessed");
error_log("DEBUG TRACKER - Session: " . print_r($_SESSION, true));
error_log("DEBUG TRACKER - GET params: " . print_r($_GET, true));

// Session check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    error_log("DEBUG TRACKER - Session check failed, redirecting to signin");
    header("Location: ../signin.php");
    exit();
}

require_once __DIR__ . '/../../configs/config.php';
define('ALLOW_ACCESS', true);
require_once __DIR__ . '/../../admin_operations/profile_operations.php';
require_once __DIR__ . '/../../admin_operations/SessionLogger.php';
require_once __DIR__ . '/../../admin_operations/mood-tracks.php';
require_once '../../includes/navigation_components.php';

// Initialize dependencies
$profileOps = new ProfileOperations($pdo);
$sessionLogger = new SessionLogger($pdo);
$moodTracker = new MoodTracks($pdo);

$user_id = $_SESSION['user_id'];

try {
    $todayMood = $moodTracker->getTodayMood($user_id);
    $weeklyEntries = $moodTracker->getWeeklyEntriesCount($user_id);
    $streak = $moodTracker->getCurrentStreak($user_id);
    $journalStats = $moodTracker->getJournalStats($user_id);
} catch (Exception $e) {
    error_log("Error retrieving mood data for user $user_id: " . $e->getMessage());
    $todayMood = $weeklyEntries = $streak = $journalStats = null;
}

// Handle AJAX requests
if (isset($_POST['action']) && $_POST['action'] === 'getMoodData') {
  try {
      $date = $_POST['date'] ?? null;
      $moodData = $moodTracker->getMoodHistory($user_id, $date);  // Using the class method
      header('Content-Type: application/json');
      echo json_encode($moodData);
      exit;
  } catch (Exception $e) {
      error_log("Error in AJAX mood data retrieval: " . $e->getMessage());
      header('HTTP/1.1 500 Internal Server Error');
      exit;
  }
}

// Function to get mood history
function getMoodHistory($pdo, $userId, $selectedDate = null) {
  try {
      $query = "SELECT DISTINCT m1.moodlog_id, m1.mood_name, m1.description, 
                DATE_FORMAT(m1.log_date, '%Y-%m-%d %h:%i %p') as formatted_date 
                FROM moodlog m1
                WHERE m1.srcode = :userId";
      if ($selectedDate) {
          $query .= " AND DATE(m1.log_date) = :selectedDate";
      }
      $query .= " ORDER BY m1.log_date DESC LIMIT 1";

      $stmt = $pdo->prepare($query);
      $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
      if ($selectedDate) {
          $stmt->bindValue(':selectedDate', $selectedDate, PDO::PARAM_STR);
      }
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      return $result;
  } catch (PDOException $e) {
      error_log("Error in getMoodHistory: " . $e->getMessage());
      return null;
  }
}

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
    <title>Mood Tracker</title>
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
      .mood-item {
        text-align: center;
        min-width: 60px;
        transition: all 0.3s ease;
        cursor: default;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .mood-emoji {
        font-size: 32px;
        line-height: 1;
        margin-bottom: 4px;
        opacity: 1;
        transition: all 0.3s ease;
        user-select: none;
        -webkit-user-select: none;
    }

    .mood-label {
        font-size: 0.875rem;
        color: #344767;
        margin: 0;
        user-select: none;
        -webkit-user-select: none;
    }

    .mood-item.selected {
        transform: scale(1.1);
    }

    .mood-item.selected .mood-emoji {
        opacity: 1;
        filter: drop-shadow(0 0 3px rgba(0,0,0,0.3));
    }
        .flatpickr-calendar {
            background: transparent;
            box-shadow: none !important;
            width: 100% !important;
            border: none !important;
        }

        .flatpickr-months {
            background-color: transparent;
            margin-bottom: 10px;
        }

        .flatpickr-month {
            background-color: transparent;
        }

        .flatpickr-weekdays {
            background-color: transparent;
        }

        .flatpickr-weekday {
            background-color: transparent;
        }

        .flatpickr-days {
            border: none !important;
            background: transparent;
        }

        .dayContainer {
            border: none !important;
            background: transparent;
        }

        .flatpickr-day {
            border-radius: 10px;
            margin: 2px;
            background: transparent;
        }

        .flatpickr-day.selected {
            background: #344767;
            border-color: #344767;
        }

        .flatpickr-day:hover {
            background: #f8f9fa;
        }

        .input-group.input-group-static {
            margin-bottom: 0;
        }

        .input-group.input-group-static input {
            display: none; /* Hides the input since we're using inline calendar */
        }

       /* Style for year input with spinners */
.numInput.cur-year {
            text-align: center;
            border: 1px solid #ced4da;
            background: #fff;
            cursor: pointer;
        }

        .numInputWrapper {
            display: inline-flex;
            align-items: center;
        }

        .numInput.cur-year::-webkit-inner-spin-button,
        .numInput.cur-year::-webkit-outer-spin-button {
            display: block;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            padding: 5px;
            margin: 0 5px;
            cursor: pointer;
        }

        .flatpickr-calendar .flatpickr-months .flatpickr-month {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Style for year selection */
        .flatpickr-current-month {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 2px;
        }

        /* Style for year dropdown */
        .flatpickr-current-month select.flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
            appearance: menulist !important;
            background: transparent;
            border-radius: 4px;
            padding: 2px 4px;
            cursor: pointer;
        }

        .cur-year {
            width: 5ch !important;
            box-sizing: content-box;
        }

        /* Ensure the year input is visible */
        .numInputWrapper {
            display: inline-block !important;
            width: auto !important;
        }

        .numInput.cur-year {
            display: inline-block !important;
            width: 10px;
        }

        /* Card container */
        .card.calendar-card {
            width: 350px !important;
            min-height: 400px !important;
            padding: 20px !important;
        }

        /* Main calendar container */
        .flatpickr-calendar {
            width: 100% !important;
            max-width: 310px !important;
            margin: 0 auto !important;
            padding: 10px !important;
            background: transparent;
            box-shadow: none !important;
            border: none !important;
        }

        /* Days container */
        .flatpickr-rContainer {
            width: 100% !important;
        }

        .flatpickr-days {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }

        .dayContainer {
            width: 100% !important;
            min-width: unset !important;
            max-width: unset !important;
            display: grid !important;
            grid-template-columns: repeat(7, 40px) !important;
            justify-content: center !important;
            gap: 2px !important;
        }

        /* Individual day cells */
        .flatpickr-day {
            width: 40px !important;
            height: 40px !important;
            line-height: 40px !important;
            margin: 0 !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
        }

        /* Weekday headers */
        .flatpickr-weekdays {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }

        .flatpickr-weekdaycontainer {
            width: 100% !important;
            display: grid !important;
            grid-template-columns: repeat(7, 40px) !important;
            justify-content: center !important;
            gap: 2px !important;
        }

        .flatpickr-weekday {
            width: 40px !important;
            font-size: 14px !important;
        }

        .calendar-card {
            min-height: 400px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .flatpickr-calendar {
            background: transparent !important;
            box-shadow: none !important;
            border: none !important;
            width: 100% !important;
        }

        .flatpickr-months {
            margin-bottom: 10px;
        }

        .flatpickr-month {
            height: 40px !important;
            color: #344767 !important;
        }

        .flatpickr-current-month {
            padding: 0 !important;
            color: #344767 !important;
        }

        .flatpickr-weekdays {
            margin: 10px 0 !important;
        }

        .flatpickr-weekday {
            color: #7b809a !important;
            font-weight: 500 !important;
        }

        .flatpickr-day {
            border-radius: 8px !important;
            margin: 2px !important;
            height: 35px !important;
            line-height: 35px !important;
            color: #344767 !important;
        }

        .flatpickr-day.selected {
            background: #344767 !important;
            border-color: #344767 !important;
            color: white !important;
        }

        .flatpickr-day:hover {
            background: #f8f9fa !important;
        }

        .flatpickr-day.today {
            border: 1px solid #344767 !important;
        }

        .flatpickr-months .flatpickr-prev-month,
        .flatpickr-months .flatpickr-next-month {
            fill: #344767 !important;
            padding: 5px !important;
        }


    </style>
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
    <div class="container-fluid py-2">
      <div class="row">
        <div class="ms-3">
          <h3 class="mb-0 h4 font-weight-bolder">Mood Tracker</h3>
          <p class="mb-4">
            Track and monitor your daily emotional well-being with our mood tracking system.
          </p>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Today's Moods</p>
                  <div class="d-flex align-items-center">
                    <?php 
                    $todayMoods = $moodTracker->getAllTodayMoods($user_id);
                    if ($todayMoods) {
                        foreach ($todayMoods as $mood) {
                            echo '<span style="font-size: 21px;">' . 
                                 str_replace(',', '', trim($mood['selected_emoji'])) . 
                                 '</span>';
                        }
                    } else {
                        echo '<span style="font-size: 20px;">😐</span>';
                    }
                    ?>
                  </div>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-info shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">mood</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <span class="text-info font-weight-bolder">
                  <?php echo $weeklyEntries['entry_count'] == 7 ? 'Perfect' : 'Regular'; ?>
                </span> mood tracking
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Mood Streak</p>
                  <h4 class="mb-0"><?php echo $streak['streak'] ?? 0; ?> Days</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-warning shadow-warning shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">local_fire_department</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <?php if (($streak['streak'] ?? 0) > 0): ?>
                  <span class="text-warning font-weight-bolder">Keep it up! </span>You're doing great
                <?php else: ?>
                  <span class="text-muted">Start tracking your mood daily</span>
                <?php endif; ?>
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Weekly Entries</p>
                  <h4 class="mb-0"><?php echo $weeklyEntries['entry_count'] ?? 0; ?> Days</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-info shadow-info shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">calendar_month</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <span class="text-info font-weight-bolder">
                  <?php 
                  // Customize the mood tracking status message
                  if ($weeklyEntries['entry_count'] == 7) {
                      echo 'Perfect week';
                  } elseif ($weeklyEntries['entry_count'] >= 5) {
                      echo 'Great progress';
                  } elseif ($weeklyEntries['entry_count'] >= 3) {
                      echo 'Good tracking';
                  } else {
                      echo 'Keep tracking';
                  }
                  ?>
                </span>
                mood tracking
              </p>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card">
            <div class="card-header p-2 ps-3">
              <div class="d-flex justify-content-between">
                <div>
                  <p class="text-sm mb-0 text-capitalize">Journal Entries</p>
                  <h4 class="mb-0"><?php echo $journalStats['total_entries'] ?? 0; ?> Total</h4>
                </div>
                <div class="icon icon-md icon-shape bg-gradient-primary shadow-primary shadow text-center border-radius-lg">
                  <i class="material-symbols-rounded opacity-10">edit_note</i>
                </div>
              </div>
            </div>
            <hr class="dark horizontal my-0">
            <div class="card-footer p-2 ps-3">
              <p class="mb-0 text-sm">
                <span class="text-primary font-weight-bolder">+<?php echo $journalStats['new_entries'] ?? 0; ?> </span>
                new entries this week
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

<div class="container-fluid py-2">
    <div class="row">
        <!--  Datepicker -->
        <div class="col-lg-8 col-sm-12 col-md-5 col-xl-4">
            <div class="card h-100 W-100">
                <div class="card-header p-3 pb-0">
                    <div class="d-flex align-items-center">
                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                            <i class="material-symbols-rounded opacity-10">calendar_month</i>
                        </div>
                        <div class="ms-3">
                            <h6 class="font-weight-bolder mb-0">Mood Calendar</h6>
                            <p class="text-sm mb-0 text-capitalize font-weight-normal">Select date to view moods</p>
                        </div>
                    </div>
                </div>
                <div class="card-body calendar-card-body border-radius-lg p-3">
                    <div class="input-group input-group-static">
                        <input type="text" 
                               class="form-control datepicker" 
                               placeholder="Please select date"
                               readonly>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 mt-lg-0 mt-4">
          <div class="card overflow-hidden h-100">
              <div class="card-header p-3 pb-0">
                  <div class="d-flex align-items-center">
                      <div class="icon icon-shape bg-gradient-secondary shadow text-center border-radius-md">
                          <i class="material-symbols-rounded opacity-10">ar_on_you</i>
                      </div>
                      <div class="ms-3">
                          <h6 class="font-weight-bolder mb-0">Mood Entry</h6>
                          <p class="text-sm mb-0 text-capitalize font-weight-normal">See your mood entry for the selected date</p>
                      </div>
                  </div>
              </div>
              <div class="card-body mt-5 pt-3">
                  <!-- Mood Display Section -->
                  <div id="moodDisplaySection" class="d-flex justify-content-center align-items-center gap-4 mb-4 px-3">
                      <!-- This will be populated dynamically via JavaScript -->
                  </div>
                  <!-- Description Text Area -->
                  <div class="form-group px-5 mx-auto">
                      <textarea id="moodDescription" class="form-control" rows="8" readonly></textarea>
                  </div>
              </div>
          </div>
        </div>


    </div
</div>
                    
<?php include_once('support_messages_modal.php'); ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../assets/js/core/popper.min.js"></script>
<script src="../../assets/js/core/bootstrap.min.js"></script>
<script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
<script src="../../assets/js/plugins/chart.min.js"></script>
<script src="../../assets/js/material-dashboard.min.js?v=3.2.0"></script>
<script src="../../assets/js/signout.js"></script>
<script src="../../assets/js/support.js"></script>
<script src="../../assets/js/line-chart.js"></script>
<script src="../../assets/js/support-messages.js"></script>
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
document.addEventListener('DOMContentLoaded', function() {
    console.log("Initializing flatpickr..."); // Debug log
    
    // Initialize the datepicker
    const picker = flatpickr(".datepicker", {
        dateFormat: "Y-m-d",
        maxDate: "today",
        disableMobile: "true",
        inline: true,  // Always show the calendar
        defaultDate: "today",
        onChange: function(selectedDates, dateStr) {
            console.log("Selected date:", dateStr); // Debug log
            loadMoodEntryForDate(dateStr);
        }
    });

    // Function to load mood entry for selected date
    function loadMoodEntryForDate(date) {
        fetch('moodtracker.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=getMoodData&date=${date}`
        })
        .then(response => response.json())
        .then(data => {
            const moodDescription = document.getElementById('moodDescription');
            const moodDisplaySection = document.getElementById('moodDisplaySection');
            
            // Clear previous moods
            moodDisplaySection.innerHTML = '';
            
            if (data && data.length > 0) {
                const entry = data[0];
                moodDescription.value = entry.description || '';
                
                // Emoji mapping
                const moodEmojis = {
                    'Happy': '😊',
                    'Sad': '☹️',
                    'Angry': '😠',
                    'Calm': '😌',
                    'Fearful': '😨',
                    'Love': '😍',
                    'Disappointed': '😔',
                    'Confused': '😕',
                    'Tired': '😴',
                    'Thoughtful': '🤔'
                };
                
                if (entry.mood_name) {
                    const moodNames = entry.mood_name.split(',').map(mood => mood.trim());
                    
                    // Only display the selected moods
                    moodNames.forEach(mood => {
                        const normalizedMoodName = mood.charAt(0).toUpperCase() + 
                                                mood.slice(1).toLowerCase();
                        if (moodEmojis[normalizedMoodName]) {
                            const moodDiv = document.createElement('div');
                            moodDiv.className = 'mood-item';
                            moodDiv.innerHTML = `
                                <div class="mood-emoji">${moodEmojis[normalizedMoodName]}</div>
                                <div class="mood-label">${normalizedMoodName}</div>
                            `;
                            moodDisplaySection.appendChild(moodDiv);
                        }
                    });
                }
            } else {
                // Clear the description field
                moodDescription.value = '';
                
                // Clear previous moods and add centered message
                const moodDisplaySection = document.getElementById('moodDisplaySection');
                moodDisplaySection.innerHTML = `
                    <div class="text-center w-100">
                        <h5 class="text-secondary mb-0">No mood entry for this date</h5>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading mood data:', error);
            document.getElementById('moodDescription').value = 'Error loading mood data';
        });
    }

    // Load today's mood data when page loads
    const today = new Date().toISOString().split('T')[0];
    loadMoodEntryForDate(today);
});
</script>
        <script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize mood trends chart
    var ctx = document.getElementById("moodTrendsChart").getContext("2d");
    
    var gradientStroke = ctx.createLinearGradient(0, 230, 0, 50);
    gradientStroke.addColorStop(1, 'rgba(203,12,159,0.2)');
    gradientStroke.addColorStop(0.2, 'rgba(72,72,176,0.0)');
    gradientStroke.addColorStop(0, 'rgba(203,12,159,0)');

    // Fetch mood trend data
    fetch('moodtracker.php?action=getMoodTrends')
        .then(response => response.json())
        .then(data => {
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: data.dates,
                    datasets: [{
                        label: "Mood Score",
                        tension: 0.4,
                        borderWidth: 0,
                        pointRadius: 0,
                        borderColor: "#cb0c9f",
                        borderWidth: 3,
                        backgroundColor: gradientStroke,
                        fill: true,
                        data: data.scores,
                        maxBarThickness: 6
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                    scales: {
                        y: {
                            grid: {
                                drawBorder: false,
                                display: true,
                                drawOnChartArea: true,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                padding: 10,
                                color: '#b2b9bf',
                                font: {
                                    size: 11,
                                    family: "Open Sans",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                        x: {
                            grid: {
                                drawBorder: false,
                                display: false,
                                drawOnChartArea: false,
                                drawTicks: false,
                                borderDash: [5, 5]
                            },
                            ticks: {
                                display: true,
                                color: '#b2b9bf',
                                padding: 20,
                                font: {
                                    size: 11,
                                    family: "Open Sans",
                                    style: 'normal',
                                    lineHeight: 2
                                },
                            }
                        },
                    },
                },
            });
        });
});
</script>

        </body>
        </html>

