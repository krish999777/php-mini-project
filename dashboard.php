<?php
session_start();

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$userEmail = htmlspecialchars($_SESSION['email'] ?? '');
$userRole = htmlspecialchars($_SESSION['role'] ?? 'candidate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ResumeHub</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="dashboard-body">
    <!-- Top Navigation Bar -->
    <header class="dashboard-nav">
        <a href="dashboard.php" class="brand-logo" style="margin-bottom: 0;">
            <div class="brand-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                </svg>
            </div>
            <span>ResumeHub</span>
        </a>

        <div style="display: flex; align-items: center; gap: 16px;">
            <span class="user-badge <?php echo $userRole; ?>">
                <?php echo strtoupper($userRole); ?>
            </span>
            <a href="logout.php" class="btn-pill-outline" style="color: #374151; border-color: #D1D5DB; padding: 8px 20px; font-size: 0.85rem; margin-bottom: 0;">
                Sign out
            </a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="dashboard-main">
        <div class="dashboard-card">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--text-main); margin-bottom: 8px;">
                    Welcome back, <?php echo $userName; ?>!
                </h1>
                <p style="color: var(--text-muted); font-size: 1rem;">
                    Logged in as <strong><?php echo $userEmail; ?></strong>
                </p>
            </div>
        </div>
    </main>
</body>
</html>
