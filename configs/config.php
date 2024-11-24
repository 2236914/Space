<?php
if (!defined('BASE_URL')) {  // Only define if not already defined
    define('BASE_URL', '/Space');
}

// Database connection settings
$host = 'localhost';
$dbname = 'space';
$username = 'root';
$password = '';

try {
    // Creating the PDO instance and storing it in the $pdo variable
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit(); // Stop further script execution if the connection fails
}
?>
