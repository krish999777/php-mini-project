<?php
/**
 * Get Candidate Detail API Handler
 * Returns the full profile record for a single candidate.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Auth Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only recruiters can view candidate details.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$candidateId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($candidateId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid candidate ID.']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.name, 
            u.email, 
            COALESCE(cp.headline, '') AS headline, 
            COALESCE(cp.phone, '') AS phone, 
            COALESCE(cp.location, '') AS location, 
            COALESCE(cp.skills, '') AS skills, 
            COALESCE(cp.experience_years, 0) AS experience_years, 
            COALESCE(cp.education, '') AS education, 
            COALESCE(cp.bio, '') AS bio,
            u.created_at,
            cp.updated_at
        FROM users u
        LEFT JOIN candidate_profiles cp ON u.id = cp.user_id
        WHERE u.id = :id AND u.role = 'candidate'
        LIMIT 1
    ");
    $stmt->execute([':id' => $candidateId]);
    $candidate = $stmt->fetch();

    if (!$candidate) {
        echo json_encode(['success' => false, 'message' => 'Candidate profile not found.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'candidate' => $candidate
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Candidate detail error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
