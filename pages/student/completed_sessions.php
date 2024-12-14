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

try {
    $stmt = $pdo->prepare("
        SELECT 
            ts.*,
            t.firstname as therapist_firstname,
            t.lastname as therapist_lastname,
            t.specialization,
            sf.feedback_id,
            sf.diagnosis,
            sf.recommendations,
            sf.follow_up,
            sf.follow_up_notes,
            stf.feedback_id as student_feedback_id,
            stf.rating,
            stf.comment as student_comment
        FROM therapy_sessions ts
        JOIN therapists t ON ts.therapist_id = t.therapist_id
        LEFT JOIN session_feedback sf ON ts.session_id = sf.session_id
        LEFT JOIN student_feedback stf ON ts.session_id = stf.session_id
        WHERE ts.srcode = ? 
        AND ts.status = 'completed'
        ORDER BY ts.session_date DESC
    ");

    $stmt->execute([$_SESSION['srcode']]);
    $completed_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $completed_sessions = [];
}

$page_title = 'Completed Sessions - Space';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="../../assets/img/logo-space.png">
    <link rel="icon" type="image/png" href="../../assets/img/logo-space.png">
    <title><?php echo $page_title; ?></title>
    
    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link href="../../assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="../../assets/css/nucleo-svg.css" rel="stylesheet" />
    <script src="https://kit.fontawesome.com/ddc03e77c7.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <!-- CSS Files -->
    <link id="pagestyle" href="../../assets/css/material-dashboard.css?v=3.0.6" rel="stylesheet" />
    <link href="../../assets/css/navigation.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="../../assets/css/custom-swal.css" rel="stylesheet" />
    
    <style>
        .rating-stars {
            font-size: 24px;
            cursor: pointer;
        }
        .rating-stars i {
            margin-right: 5px;
        }
        .rating-stars i.fas {
            color: #ffc107;
        }
        .rating-stars i.far {
            color: #ccc;
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
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header p-3">
                            <h5 class="mb-0">Completed Sessions</h5>
                            <p class="text-sm mb-0">
                                View your completed sessions and provide feedback
                            </p>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (empty($completed_sessions)): ?>
                                <div class="text-center py-4">
                                    <i class="material-symbols-rounded text-3xl mb-3">history</i>
                                    <h6>No Completed Sessions</h6>
                                    <p class="text-sm text-secondary">
                                        You haven't completed any therapy sessions yet.
                                    </p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Therapist</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Session Date</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Type</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Feedback</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Your Rating</th>
                                                <th class="text-secondary opacity-7">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($completed_sessions as $session): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">Dr. <?= htmlspecialchars($session['therapist_firstname'] . ' ' . $session['therapist_lastname']) ?></h6>
                                                                <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($session['specialization']) ?></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <p class="text-xs font-weight-bold mb-0">
                                                            <?= date('F j, Y', strtotime($session['session_date'])) ?>
                                                        </p>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <?= date('g:i A', strtotime($session['session_time'])) ?>
                                                        </p>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-sm bg-gradient-info">
                                                            <?= ucfirst(htmlspecialchars($session['session_type'])) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($session['feedback_id']): ?>
                                                            <span class="badge badge-sm bg-gradient-success">Available</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-sm bg-gradient-warning">Pending</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($session['student_feedback_id']): ?>
                                                            <div class="d-flex align-items-center">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star <?= $i <= $session['rating'] ? 'text-warning' : 'text-secondary' ?> me-1"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="badge badge-sm bg-gradient-warning">Not Rated</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <?php if ($session['feedback_id']): ?>
                                                            <button class="btn btn-sm bg-gradient-info mb-0"
                                                                    onclick="viewAllFeedback(<?= $session['session_id'] ?>)">
                                                                <i class="material-symbols-rounded text-sm me-2">visibility</i>
                                                                View Details
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if (!$session['student_feedback_id']): ?>
                                                            <button class="btn btn-sm bg-gradient-dark mb-0"
                                                                    onclick="openRatingModal(<?= $session['session_id'] ?>)">
                                                                <i class="material-symbols-rounded text-sm me-2">star</i>
                                                                Rate Session
                                                            </button>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Rating Modal -->
    <div class="modal fade" id="ratingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Rate Your Session</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ratingForm">
                        <input type="hidden" id="session_id" name="session_id">
                        <div class="mb-3">
                            <label class="form-label">How would you rate this session?</label>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="far fa-star star-rating" data-rating="<?= $i ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" id="rating" name="rating" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Comments (Optional)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3"
                                    placeholder="Share your thoughts about the session..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-gradient-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn bg-gradient-primary" onclick="submitRating()">Submit Rating</button>
                </div>
            </div>
        </div>
    </div>
    <?php include_once('support_messages_modal.php'); ?>

    <!-- Scripts -->
    <script src="../../assets/js/core/popper.min.js"></script>
    <script src="../../assets/js/core/bootstrap.min.js"></script>
    <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script src="../../assets/js/material-dashboard.min.js"></script>
    <script src="../../assets/js/support-messages.js"></script>
    <script src="../../assets/js/signout.js"></script>
    <script src="../../assets/js/support.js"></script>
    
    <script>
        let selectedRating = 0;

        // Star rating system
        document.querySelectorAll('.star-rating').forEach(star => {
            star.addEventListener('click', function() {
                selectedRating = this.dataset.rating;
                document.getElementById('rating').value = selectedRating;
                updateStars();
            });

            star.addEventListener('mouseover', function() {
                const rating = this.dataset.rating;
                highlightStars(rating);
            });

            star.addEventListener('mouseout', function() {
                highlightStars(selectedRating);
            });
        });

        function highlightStars(rating) {
            document.querySelectorAll('.star-rating').forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                }
            });
        }

        function openRatingModal(sessionId) {
            document.getElementById('session_id').value = sessionId;
            selectedRating = 0;
            document.getElementById('rating').value = '';
            document.getElementById('comment').value = '';
            updateStars();
            new bootstrap.Modal(document.getElementById('ratingModal')).show();
        }

        function submitRating() {
            const formData = {
                session_id: document.getElementById('session_id').value,
                rating: document.getElementById('rating').value,
                comment: document.getElementById('comment').value
            };

            if (!formData.rating) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rating Required',
                    text: 'Please provide a rating for your session',
                    customClass: {
                        confirmButton: 'btn bg-gradient-dark'
                    },
                    buttonsStyling: false
                });
                return;
            }

            fetch('../../admin_operations/submit_student_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thank You!',
                        text: 'Your rating has been submitted successfully.',
                        customClass: {
                            confirmButton: 'btn bg-gradient-dark'
                        },
                        buttonsStyling: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit rating');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    customClass: {
                        confirmButton: 'btn bg-gradient-dark'
                    },
                    buttonsStyling: false
                });
            });
        }

        function viewAllFeedback(sessionId) {
            fetch(`../../admin_operations/get_all_feedback.php?session_id=${sessionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Session Details',
                            html: `
                                <div class="text-start">
                                    <div class="mb-4">
                                        <h6 class="text-dark mb-3">Therapist's Feedback</h6>
                                        <div class="mb-3">
                                            <strong class="text-dark">Diagnosis:</strong>
                                            <p class="text-sm">${data.therapist_feedback.diagnosis || 'No diagnosis provided'}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong class="text-dark">Recommendations:</strong>
                                            <p class="text-sm">${data.therapist_feedback.recommendations || 'No recommendations provided'}</p>
                                        </div>
                                        <div class="mb-3">
                                            <strong class="text-dark">Follow-up Required:</strong>
                                            <p class="text-sm">${data.therapist_feedback.follow_up === 'yes' ? 'Yes' : 'No'}</p>
                                        </div>
                                        ${data.therapist_feedback.follow_up === 'yes' && data.therapist_feedback.follow_up_notes ? `
                                            <div class="mb-3">
                                                <strong class="text-dark">Follow-up Notes:</strong>
                                                <p class="text-sm">${data.therapist_feedback.follow_up_notes}</p>
                                            </div>
                                        ` : ''}
                                    </div>
                                    ${data.student_feedback ? `
                                        <div class="mb-3">
                                            <h6 class="text-dark mb-3">Your Rating</h6>
                                            <div class="d-flex align-items-center mb-2">
                                                ${Array(5).fill(0).map((_, i) => 
                                                    `<i class="fas fa-star ${i < data.student_feedback.rating ? 'text-warning' : 'text-secondary'} me-1"></i>`
                                                ).join('')}
                                                <span class="ms-2">(${data.student_feedback.rating}/5)</span>
                                            </div>
                                            ${data.student_feedback.comment ? `
                                                <strong class="text-dark">Your Comment:</strong>
                                                <p class="text-sm">${data.student_feedback.comment}</p>
                                            ` : ''}
                                        </div>
                                    ` : ''}
                                </div>
                            `,
                            customClass: {
                                confirmButton: 'btn bg-gradient-dark'
                            },
                            buttonsStyling: false
                        });
                    } else {
                        throw new Error(data.message || 'Failed to load feedback');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load feedback',
                        customClass: {
                            confirmButton: 'btn bg-gradient-dark'
                        },
                        buttonsStyling: false
                    });
                });
        }
    </script>
</body>
</html> 