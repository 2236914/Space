<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../../configs/config.php';
require_once '../../includes/navigation_components.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../signin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
  <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
  <title>Self-Care Exercises</title>
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
    .meditation-card {
      transition: transform 0.3s ease;
    }
    .meditation-card:hover {
      transform: translateY(-5px);
    }
    .timer-display {
      font-size: 4rem;
      font-weight: 700;
      color: #344767;
    }
    .breathing-circle {
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: linear-gradient(45deg, #4CAF50, #2196F3);
      position: relative;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 4s ease-in-out;
    }
    .breathing-text {
      color: white;
      font-size: 1.5rem;
      font-weight: 500;
      text-align: center;
      z-index: 2;
    }
    @keyframes breatheIn {
      from { transform: scale(1); }
      to { transform: scale(1.5); }
    }
    @keyframes breatheOut {
      from { transform: scale(1.5); }
      to { transform: scale(1); }
    }
    @keyframes breatheHold {
      from { transform: scale(1.5); }
      to { transform: scale(1.5); }
    }
    .nav-pills .nav-link.active {
      background-color: #ffffff;
    }
    .nav-pills .nav-link {
      color: #ffffff;
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

    <!-- Page Content -->
    <div class="container-fluid px-2 px-md-4 py-7">
        <!-- Exercise Type Tabs -->
        <div class="card card-body mx-3 mx-md-4 mt-n6">
    <!-- Exercise Type Tabs -->
    <div class="nav-wrapper position-relative mb-4">
        <ul class="nav nav-pills nav-fill p-1" role="tablist">
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1 active" data-bs-toggle="tab" href="#pills-meditation" role="tab" aria-selected="true">
                    <span class="material-symbols-rounded align-middle mb-1">self_improvement</span>
                    Meditation
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link mb-0 px-0 py-1" data-bs-toggle="tab" href="#pills-breathing" role="tab" aria-selected="false">
                    <span class="material-symbols-rounded align-middle mb-1">air</span>
                    Breathing
                </a>
            </li>
        </ul>
    </div>
        <!-- Tab Content -->
        <div class="tab-content" id="pills-tabContent">
          <!-- Meditation Tab -->
          <div class="tab-pane fade show active" id="pills-meditation" role="tabpanel">
            <div class="row">
              <!-- Timer Section -->
              <div class="col-12 col-xl-8 mb-4">
                <div class="card meditation-card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">timer</i>
                        </div>
                        <div class="text-end pt-1">
                            <h4 class="mb-0">Meditation Timer</h4>
                        </div>
                    </div>
                    <div class="card-body text-center p-5">
                        <div class="timer-display mb-4" id="timer">15:00</div>
                        
                        <!-- Timer Controls -->
                        <div class="d-flex justify-content-center align-items-center mb-4">
                            <button class="btn btn-icon btn-lg bg-gradient-primary mx-2" onclick="setTimer(5)">5m</button>
                            <button class="btn btn-icon btn-lg bg-gradient-info mx-2" onclick="setTimer(10)">10m</button>
                            <button class="btn btn-icon btn-lg bg-gradient-success mx-2" onclick="setTimer(15)">15m</button>
                            <button class="btn btn-icon btn-lg bg-gradient-warning mx-2" onclick="setTimer(20)">20m</button>
                        </div>

                        <div class="d-flex justify-content-center align-items-center">
                            <button class="btn btn-lg bg-gradient-dark mx-2" id="startBtn" onclick="startTimer()">
                                <i class="material-symbols-rounded">play_arrow</i> Start
                            </button>
                            <button class="btn btn-lg btn-outline-dark mx-2" id="resetBtn" onclick="resetTimer()">
                                <i class="material-symbols-rounded">restart_alt</i> Reset
                            </button>
                        </div>
                    </div>
                </div>
              </div>

              <!-- Ambient Sounds -->
              <div class="col-12 col-xl-4 mb-4">
                <div class="card meditation-card h-100">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-symbols-rounded opacity-10">music_note</i>
                        </div>
                        <div class="text-end pt-1">
                            <h4 class="mb-0">Ambient Sounds</h4>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-wrap justify-content-around">
                            <div class="text-center p-3 cursor-pointer sound-button" onclick="toggleSound('rain')">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center">
                                    <i class="material-symbols-rounded opacity-10">water_drop</i>
                                </div>
                                <span class="d-block mt-2">Rain</span>
                            </div>
                            <div class="text-center p-3 cursor-pointer sound-button" onclick="toggleSound('forest')">
                                <div class="icon icon-shape bg-gradient-success shadow text-center">
                                    <i class="material-symbols-rounded opacity-10">forest</i>
                                </div>
                                <span class="d-block mt-2">Forest</span>
                            </div>
                            <div class="text-center p-3 cursor-pointer sound-button" onclick="toggleSound('waves')">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center">
                                    <i class="material-symbols-rounded opacity-10">waves</i>
                                </div>
                                <span class="d-block mt-2">Waves</span>
                            </div>
                            <div class="text-center p-3 cursor-pointer sound-button" onclick="toggleSound('birds')">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center">
                                    <i class="material-symbols-rounded opacity-10">flutter_dash</i>
                                </div>
                                <span class="d-block mt-2">Birds</span>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="text-sm">Volume</label>
                            <input type="range" class="form-range" id="volumeControl" min="0" max="100" value="50">
                        </div>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Breathing Exercise Tab -->
          <div class="tab-pane fade" id="pills-breathing" role="tabpanel">
            <div class="row">
                <!-- Breathing Exercise -->
                <div class="col-12 col-xl-8 mb-4">
                    <div class="card exercise-card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">air</i>
                            </div>
                            <div class="text-end pt-1">
                                <h4 class="mb-0">4-7-8 Breathing</h4>
                                <p class="text-sm mb-0">Breathe in for 4, hold for 7, exhale for 8</p>
                            </div>
                        </div>
                        <div class="card-body p-5 text-center">
                            <div class="breathing-circle mb-4">
                                <div class="breathing-text">Breathe In</div>
                            </div>
                            <div class="d-flex justify-content-center align-items-center mt-4">
                                <button class="btn btn-lg bg-gradient-success mx-2" id="startBreathingBtn" onclick="startBreathing()">
                                    <i class="material-symbols-rounded">play_arrow</i> Start
                                </button>
                                <button class="btn btn-lg btn-outline-success mx-2" id="resetBreathingBtn" onclick="resetBreathing()">
                                    <i class="material-symbols-rounded">restart_alt</i> Reset
                                </button>
                            </div>
                            <div class="mt-4">
                                <span class="text-lg" id="cycleCount">Cycles: 0/3</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="col-12 col-xl-4 mb-4">
                    <div class="card exercise-card h-100">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                                <i class="material-symbols-rounded opacity-10">help_outline</i>
                            </div>
                            <div class="text-end pt-1">
                                <h4 class="mb-0">Instructions</h4>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="timeline timeline-one-side">
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        <i class="material-symbols-rounded text-success text-gradient">expand_circle_down</i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">Breathe In</h6>
                                        <p class="text-secondary font-weight-normal text-xs mt-1 mb-0">Inhale quietly through the nose for 4 seconds</p>
                                    </div>
                                </div>
                                <div class="timeline-block mb-3">
                                    <span class="timeline-step">
                                        <i class="material-symbols-rounded text-warning text-gradient">radio_button_checked</i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">Hold</h6>
                                        <p class="text-secondary font-weight-normal text-xs mt-1 mb-0">Hold your breath for 7 seconds</p>
                                    </div>
                                </div>
                                <div class="timeline-block">
                                    <span class="timeline-step">
                                        <i class="material-symbols-rounded text-info text-gradient">expand_circle_up</i>
                                    </span>
                                    <div class="timeline-content">
                                        <h6 class="text-dark text-sm font-weight-bold mb-0">Exhale</h6>
                                        <p class="text-secondary font-weight-normal text-xs mt-1 mb-0">Exhale completely through mouth for 8 seconds</p>
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
    </div>
    <?php include_once('support_messages_modal.php'); ?>
  </main>


  <!-- Core JS Files -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/material-dashboard.min.js"></script>
  <script src="../../assets/js/support.js"></script>
  <script src="../../assets/js/signout.js"></script>
  <script src="../../assets/js/selfcare-exercises.js"></script>
  <script src="../../assets/js/support-messages.js"></script>

</body>
</html>