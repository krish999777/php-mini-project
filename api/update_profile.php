<?php
/**
 * Update Candidate Profile Handler
 * Validates candidate input and updates users and candidate_profiles tables.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Auth Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'candidate') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only candidates can update profiles.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$userId = (int)$_SESSION['user_id'];
$name = trim($_POST['name'] ?? '');
$headline = trim($_POST['headline'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$skills = trim($_POST['skills'] ?? '');
$experienceYears = isset($_POST['experience_years']) ? (int)$_POST['experience_years'] : 0;
$education = trim($_POST['education'] ?? '');
$bio = trim($_POST['bio'] ?? '');

// Validation
if (empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Full Name cannot be empty.']);
    exit;
}

if ($experienceYears < 0 || $experienceYears > 60) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid number of years of experience (0-60).']);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Update user name in users table
    $userStmt = $pdo->prepare("UPDATE users SET name = :name WHERE id = :id");
    $userStmt->execute([
        ':name' => $name,
        ':id' => $userId
    ]);

    // Update session name to keep in sync
    $_SESSION['name'] = $name;

    // 2. Check if candidate profile row exists, insert or update
    $checkStmt = $pdo->prepare("SELECT id FROM candidate_profiles WHERE user_id = :user_id LIMIT 1");
    $checkStmt->execute([':user_id' => $userId]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        $profileStmt = $pdo->prepare("
            UPDATE candidate_profiles 
            SET headline = :headline,
                phone = :phone,
                location = :location,
                skills = :skills,
                experience_years = :experience_years,
                education = :education,
                bio = :bio,
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = :user_id
        ");
    } else {
        $profileStmt = $pdo->prepare("
            INSERT INTO candidate_profiles 
                (user_id, headline, phone, location, skills, experience_years, education, bio)
            VALUES 
                (:user_id, :headline, :phone, :location, :skills, :experience_years, :education, :bio)
        ");
    }

    $profileStmt->execute([
        ':headline' => $headline,
        ':phone' => $phone,
        ':location' => $location,
        ':skills' => $skills,
        ':experience_years' => $experienceYears,
        ':education' => $education,
        ':bio' => $bio,
        ':user_id' => $userId
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully!',
        'profile' => [
            'name' => $name,
            'headline' => $headline,
            'phone' => $phone,
            'location' => $location,
            'skills' => $skills,
            'experience_years' => $experienceYears,
            'education' => $education,
            'bio' => $bio
        ]
    ]);
    exit;

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Profile update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error while saving profile: ' . $e->getMessage()
    ]);
    exit;
}
