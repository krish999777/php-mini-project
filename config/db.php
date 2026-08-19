<?php
/**
 * Database Configuration & Connection (PDO)
 * Default XAMPP credentials: host=localhost, user=root, password=""
 */

$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_port = getenv('DB_PORT') ?: '3306';
$db_name = getenv('DB_NAME') ?: 'recruitment_portal';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // If connection failed because database doesn't exist yet, attempt connecting without dbname
    if ($e->getCode() == 1049) {
        try {
            $dsn_no_db = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
            $pdo_init = new PDO($dsn_no_db, $db_user, $db_pass, $options);
            $pdo_init->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo = new PDO($dsn, $db_user, $db_pass, $options);
        } catch (PDOException $innerException) {
            die(json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $innerException->getMessage()
            ]));
        }
    } else {
        // Return structured error message if API request or display clean error
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Database connection error. Please ensure MySQL is running in XAMPP. (' . $e->getMessage() . ')'
            ]);
            exit;
        } else {
            $db_error_message = $e->getMessage();
        }
    }
}
