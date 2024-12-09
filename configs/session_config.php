<?php
// Only set these if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Session security settings
    ini_set('session.use_only_cookies', 1);
    session_set_cookie_params([
        'httponly' => true,
        'secure' => true
    ]);
}
?>