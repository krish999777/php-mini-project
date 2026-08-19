<?php
/**
 * One-Click Database Setup & Diagnostics Utility
 * Runs schema.sql and verifies connection with MySQL in XAMPP
 */

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'recruitment_portal';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$status = null;
$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['run'])) {
    try {
        $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        $messages[] = "Connected to MySQL server successfully at {$db_host}:{$db_port}.";

        $schemaFile = __DIR__ . '/database/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at {$schemaFile}");
        }

        $sql = file_get_contents($schemaFile);
        $pdo->exec($sql);
        
        $messages[] = "Database `{$db_name}` and required tables created/verified successfully!";
        $status = 'success';
    } catch (PDOException $e) {
        $status = 'error';
        $messages[] = "MySQL Connection/Execution Error: " . $e->getMessage();
    } catch (Exception $e) {
        $status = 'error';
        $messages[] = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Candidate Recruitment Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card" style="max-width: 550px;">
            <div class="auth-header">
                <div class="portal-badge">Setup Utility</div>
                <h1>Database Initialization</h1>
                <p>Initialize or verify your MySQL tables for the Candidate Recruitment Portal.</p>
            </div>

            <?php if (!empty($messages)): ?>
                <div class="alert-box <?php echo $status === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php foreach ($messages as $msg): ?>
                        <div><?php echo htmlspecialchars($msg); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="setup-info-box" style="margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 12px; font-size: 0.9rem;">
                <p><strong>Configured Connection Settings:</strong></p>
                <ul style="padding-left: 20px; margin-top: 8px; color: var(--text-secondary);">
                    <li>Host: <code><?php echo htmlspecialchars($db_host); ?>:<?php echo htmlspecialchars($db_port); ?></code></li>
                    <li>User: <code><?php echo htmlspecialchars($db_user); ?></code></li>
                    <li>Database: <code><?php echo htmlspecialchars($db_name); ?></code></li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" class="btn btn-primary btn-block">
                    <span>⚡ Run Database Setup</span>
                </button>
            </form>

            <div class="auth-footer" style="margin-top: 20px;">
                <a href="register.php" class="auth-link">Go to Sign Up Page →</a>
                <span style="margin: 0 8px; opacity: 0.5;">|</span>
                <a href="login.php" class="auth-link">Go to Login Page →</a>
            </div>
        </div>
    </div>
</body>
</html>
