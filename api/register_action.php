<?php
/**
 * Register Action Handler
 * Validates inputs, hashes password, inserts into users table, and starts session.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = trim($_POST['role'] ?? 'candidate');

// Input validation
if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please provide a valid email address.']);
    exit;
}

if (!in_array($role, ['candidate', 'recruiter'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid account type selected.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters long.']);
    exit;
}

// If confirm_password is provided, verify match
if (!empty($confirm_password) && $password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

try {
    // Check if email is already registered
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
        exit;
    }

    // Hash password securely with PHP native standard algorithm (BCrypt/Argon2)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password_hash' => $password_hash,
        ':role' => $role
    ]);

    $userId = (int)$pdo->lastInsertId();

    // If candidate, initialize empty profile
    if ($role === 'candidate') {
        $profileStmt = $pdo->prepare("INSERT INTO candidate_profiles (user_id) VALUES (:user_id)");
        $profileStmt->execute([':user_id' => $userId]);
    }

    // Set authenticated session
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully! Redirecting...',
        'redirect' => 'dashboard.php'
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error during registration: ' . $e->getMessage()
    ]);
    exit;
}
