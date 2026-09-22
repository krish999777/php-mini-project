<?php
/**
 * Get Candidates List API Handler
 * Allows recruiters to fetch and search registered candidate profiles.
 */
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Auth Guard
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'recruiter') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Only recruiters can search candidates.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$search = trim($_GET['search'] ?? $_GET['q'] ?? '');

try {
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
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
                u.created_at
            FROM users u
            LEFT JOIN candidate_profiles cp ON u.id = cp.user_id
            WHERE u.role = 'candidate'
              AND (
                u.name LIKE :kw1 
                OR cp.headline LIKE :kw2 
                OR cp.skills LIKE :kw3 
                OR cp.location LIKE :kw4
                OR cp.bio LIKE :kw5
              )
            ORDER BY cp.updated_at DESC, u.id DESC
        ");
        $stmt->execute([
            ':kw1' => $searchTerm,
            ':kw2' => $searchTerm,
            ':kw3' => $searchTerm,
            ':kw4' => $searchTerm,
            ':kw5' => $searchTerm,
        ]);
    } else {
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
                u.created_at
            FROM users u
            LEFT JOIN candidate_profiles cp ON u.id = cp.user_id
            WHERE u.role = 'candidate'
            ORDER BY cp.updated_at DESC, u.id DESC
        ");
        $stmt->execute();
    }

    $candidates = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count' => count($candidates),
        'search' => $search,
        'candidates' => $candidates
    ]);
    exit;

} catch (PDOException $e) {
    error_log("Get candidates error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
