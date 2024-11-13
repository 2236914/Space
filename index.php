<?php 
// Enable error reporting for debugging (remove or adjust in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
// Exclude E_DEPRECATED warnings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);

session_start();
require __DIR__ . '../configs/config.php';
 // Update this to point to the right file
// Home Section Data Fetch
try {
    $stmt = $pdo->query("SELECT title, subtitle, description, get_started_link, read_more_link, image_path FROM home_section ORDER BY created_at DESC LIMIT 1");
    $homeData = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error fetching home section data: ' . $e->getMessage();
    exit(); // Stop further script execution if fetching fails
}

// Fallback data in case of an empty result
if (!$homeData) {
    $homeData = [
        'title' => 'SPACE.',
        'subtitle' => 'Your Mental Health Companion',
        'description' => 'To live your life to the fullest, we\'re continuing to find ways to prevent mental health problems.',
        'get_started_link' => '#about',
        'read_more_link' => 'https://docs.google.com/document/d/1xJuN-IZA4qFk_LPhmAkxYffHKHpAl1vYyEb1AwBvl-Q/edit?usp=sharing',
        'image_path' => 'assets/img/Happy.png'
    ];
}

// FAQ Data Fetch
try {
    $stmt = $pdo->query("SELECT question, answer FROM faqs ORDER BY created_at DESC");
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error fetching FAQs: ' . $e->getMessage();
    $faqs = []; // Set to empty array to avoid undefined variable error
}

// Generate CSRF token if not set
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables to store messages
$errorMessage = "";
$successMessage = "";

// Check if there's a success message in the session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear it after using
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if CSRF token is valid
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }

    // Check which form is being submitted
    if (isset($_POST['form_type']) && $_POST['form_type'] === 'contact') {
        // Handle Contact Form Submission

        // Get the form inputs and sanitize them
        $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_SPECIAL_CHARS);
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $message = filter_var(trim($_POST['message']), FILTER_SANITIZE_SPECIAL_CHARS);

        try {
            // Ensure the email is valid before proceeding
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMessage = "Invalid email format.";
            } else {
                // Prepare the SQL insert statement
                $stmt = $pdo->prepare("INSERT INTO contact_us (name, email, message) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $message]);

                // Set a success message in the session
                $_SESSION['success_message'] = "Your message has been sent to Space!";

                // Redirect to the same page to prevent form resubmission
                header('Location: ' . $_SERVER['PHP_SELF'] . '#contact');
                exit();
            }
        } catch (Exception $e) {
            error_log($e->getMessage(), 3, __DIR__ . '/logs/error.log');
            $errorMessage = 'An unexpected error occurred. Please try again later.';
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Space</title>
    <link rel="shortcut icon" href="../assets/img/favicon.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
</head>
<body>
    <!------------ NAVHEAD ------------>
    <header class="header" id="header">
        <nav class="nav container">
            <h1 href="index.html" class="nav__logo">
                <i class="ri-planet-line"></i>Space.
            </h1>
            <div class="nav__menu" id="nav-menu">
                <ul class="nav__list">
                    <li class="nav__item">
                        <a href="#home" class="nav__link active-link">Home</a>
                    </li>
                    <li class="nav__item">
                        <a href="#about" class="nav__link">About</a>
                    </li>
                    <li class="nav__item">
                        <a href="#questions" class="nav__link">FAQ</a>
                    </li>
                    <li class="nav__item">
                        <a href="#contact" class="nav__link">Contact</a>
                    </li>
                </ul>
                <!-- Responsive Nav -->
                <div class="nav__close" id="nav-close">
                    <i class="ri-close-line"></i>
                </div>

            </div>
            <div class="nav__btns">
                <a href="signin.php" class="nav__login-button">Sign In</a>
                <i class="ri-moon-line change-theme" id="theme-button"></i>
                <div class="nav__toggle" id="nav-toggle">
                    <i class="ri-menu-line"></i>
                </div>
            </div>


        </nav>
    </header>
    <main class="main">
    <!------------ HOME ------------>
    <section class="home" id="home">
    <div class="home__container container grid">
        <img src="<?php echo htmlspecialchars($homeData['image_path']); ?>" alt="" class="home__img">
        <div class="home__data">
            <h1 class="home__title" style="font-size: 50px">
                <?php echo htmlspecialchars($homeData['title']); ?>
            </h1>
            <h2 class="home__title">
                <?php echo htmlspecialchars($homeData['subtitle']); ?>
            </h2>
            <p class="home__description">
                <?php echo nl2br(htmlspecialchars($homeData['description'])); ?>
            </p>
            <a href="<?php echo htmlspecialchars($homeData['get_started_link']); ?>" class="button button--flex">
                Get Started <i class="ri-arrow-right-down-line button__icon"></i>
            </a>
            <a href="<?php echo htmlspecialchars($homeData['read_more_link']); ?>" class="button button__bordered" target="__blank">
                Read More
            </a>
        </div>
        <div class="home__social"> 
            <span class="home__social-follow">CONTACT US</span>
            <div class="home__social-links">
                <a href="#contact" class="home__social-link"><br>
                    <i class="ri-arrow-down-s-line"></i>
                </a>
            </div>
        </div>
    </div>
    </section>
    <!------------ ABOUT ------------>
    <section class="about section container" id="about">
        <div class="about__container grid">
            <img id="myImg" src="assets/img/about.png" alt="about" class="about__img">
            <div class="about__data">
                <h2 class="section__title about__title">
                    Mental health is wealth.
                </h2>
                <p class="about__description" style="text-align: justify;">
                    Space is a web-based mental health platform tailored for students of BSU TNEU Lipa. It offers a range of mental health resources, including self-care tools, mood tracking, and virtual therapy sessions with licensed professionals. We ensure your privacy and provide tools to support your emotional well-being.
                </p>
            </div>
        </div>
        <h2 class="section__title about__title" style="text-align:center">
            Developers
        </h2>
        <div class="product__container grid">
            <article class="product__card">
                <div class="product__circle"></div>
                <img src="assets/img/faith.png" alt="Faith" class="product__img">
                <h4 style="text-align: center">Faith F. Banares</h4>     
            </article>
            <article class="product__card">
                <div class="product__circle"></div>
                <img src="assets/img/alethea.png" alt="Alethea" class="product__img">
                <h4 style="text-align: center">Alethea P. Malata </h4>  
            </article>
            <article class="product__card">
                <div class="product__circle"></div>
                <img src="assets/img/gwyn.png" alt="Gwyn" class="product__img">
                <h4 style="text-align: center">Gertrude P. Maralit</h4>
            </article>
        </div>
    </section>
    <br><br><br>
    <!------------ FAQ ------------>
    <section class="questions section" id="questions">
        <h2 class="questions__title container">
            Frequently Asked Questions
        </h2>
        <br><br><br>
        <div class="questions__container container grid">
            <div class="questions__group">
                <?php foreach ($faqs as $index => $faq): ?>
                    <div class="questions__item">
                        <header class="questions__header">
                            <i class="ri-add-line questions__icon"></i>
                            <h3 class="questions__item-title">
                                <?php echo htmlspecialchars($faq['question']); ?>
                            </h3>
                        </header>
                        <div class="questions__content">
                            <p class="questions__description" style="text-align: justify;">
                                <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                            </p>
                        </div>
                    </div>
                    <!-- To maintain grouping after a certain number of items, you can add logic here -->
                    <?php if (($index + 1) % 3 == 0 && $index + 1 < count($faqs)): ?>
                        </div><div class="questions__group">
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <br><br><br>
    </section>
    <!------------ CONTACT US ------------>
    <section class="contact section container" id="contact">
        <div class="contact__container grid">
            <img src="assets/img/contact.png" alt="Contact Us" class="contact__img">
            <div class="contact__form">
                <h2 class="section__title">Contact Us</h2>
                <form action="index.php" method="POST" id="contactForm">
                    <input type="hidden" name="form_type" value="contact">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="contact__inputs">
                        <div class="contact__content">
                            <input type="text" class="contact__input" name="name" placeholder=" " required>
                            <label for="name" class="contact__label">Name *</label>
                        </div>
                        <div class="contact__content">
                            <input type="email" class="contact__input" name="email" placeholder=" " required>
                            <label for="email" class="contact__label">Email *</label>
                        </div>
                        <div class="contact__content contact__area">
                            <textarea name="message" class="contact__input" placeholder=" " required></textarea>
                            <label for="message" class="contact__label">Message *</label>
                        </div>
                    </div>
                    <br>
                    <button type="submit" class="button button--flex">
                        <i class="ri-send-plane-line"></i>Send to Space
                    </button>
                </form>
            </div>
        </div>
    </section><br><br>
    <!------------ FOOTER ------------>
    <footer class="footer section">
        <div class="footer__container container grid">
            <div class="footer__content">
                <a href="#" class="footer__logo">
                    <i class="ri-quill-pen-line footer__logo-icon"></i> Space.
                </a>

                <h3 class="footer__title">
                    This website is for educational purposes only. 
                </h3>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">Batangas State University TNEU Lipa</h3>
                <ul class="footer__data">
                    <li class="footer__information"></li>
                </ul>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">CreoTech</h3>
                <ul class="footer__data">
                    <li class="footer__information">Banares, Faith Anne</li>
                    <li class="footer__information">Malata, Alethea</li>
                    <li class="footer__information">Maralit Gertrude Gwyn</li>
                </ul>
            </div>

            <div class="footer__content">
                <h3 class="footer__title">
                    BSIT <br> SM - 3102
                </h3>
                <ul class="footer__data">
                    <li class="footer__information"></li>
                </ul>
            </div>
        </div>

        <p class="footer__copy">&#169; Educational Purposes Only. This is a website from scratch. All rights reserved</p>
    </footer>
    <!------------ SCROLL UP ------------>
    <a href="#" class="scrollup" id="scroll-up"> 
        <i class="ri-arrow-up-fill scrollup__icon"></i>
    </a>
    <!------------ SCROLL REVEAL ------------>
    <script src="assets/js/scrollreveal.min.js"></script>
    <!------------ MAIN JS ------------>
    <script src="assets/js/main.js"></script>
    </main>
</body>
</html>
