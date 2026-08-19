<?php
/**
 * Login Action Handler
 * Verifies credentials, validates password hash, and starts session.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

try {
    // Retrieve user record
    $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address or password.']);
        exit;
    }

    // Check if password hash needs rehash to newer algorithm/cost
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $updateStmt->execute([':hash' => $newHash, ':id' => $user['id']]);
    }

    // Initialize authenticated session
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful! Redirecting...',
        'redirect' => 'dashboard.php',
        'user' => [
            'name' => $user['name'],
            'role' => $user['role']
        ]
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error during login: ' . $e->getMessage()
    ]);
    exit;
}
