<?php
// Prevent direct access
if (count(get_included_files()) == 1) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Direct access not allowed']);
    exit;
}

// Define BASE_URL if not already defined
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Space');
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_NAME', 'space');
define('DB_PASS', '');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Verify connection
    $pdo->query('SELECT 1');
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    // Check if this is an AJAX request
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed'
        ]);
    } else {
        die("Connection failed. Please try again later.");
    }
    exit;
}
?>
