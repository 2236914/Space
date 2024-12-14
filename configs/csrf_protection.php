<?php
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token) || $token !== $_SESSION['csrf_token']) {
        header('HTTP/1.0 403 Forbidden');
        die('CSRF token validation failed');
    }
    return true;
}
