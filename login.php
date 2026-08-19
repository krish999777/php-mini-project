<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$notice = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'logged_out') {
    $notice = 'You have been successfully logged out.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - ResumeHub</title>
    <!-- Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="split-page">
        <!-- Left Side: White Form Panel -->
        <div class="split-side form-panel">
            <!-- Brand Logo -->
            <a href="login.php" class="brand-logo">
                <div class="brand-icon">
                    <svg fill="currentColor" viewBox="0 0 24 24">
                        <path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>
                    </svg>
                </div>
                <span>ResumeHub</span>
            </a>

            <!-- Form Container -->
            <div class="form-container">
                <div class="form-header">
                    <h1>Welcome back</h1>
                    <p>Sign in to manage your resume and opportunities</p>
                </div>

                <!-- Alert Box -->
                <div class="alert-box <?php echo $notice ? 'alert-success' : ''; ?>" id="login-alert" <?php echo $notice ? 'style="display:block;"' : ''; ?>>
                    <?php echo htmlspecialchars($notice); ?>
                </div>

                <!-- Sign In Form -->
                <form action="api/login_action.php" method="POST" data-ajax="true" novalidate>
                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" class="form-control form-input" placeholder="you@example.com" required autofocus>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrap">
                            <input type="password" id="password" name="password" class="form-control form-input" placeholder="••••••••" required>
                            <button type="button" class="toggle-pwd toggle-password" aria-label="Toggle password visibility">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <span class="spinner"></span>
                        <span class="btn-text">Sign In</span>
                    </button>
                </form>
            </div>

            <!-- Empty footer space to balance flex layout -->
            <div style="height: 20px;"></div>
        </div>

        <!-- Right Side: Green Promo Panel -->
        <div class="split-side green-panel">
            <div class="promo-content">
                <span class="promo-tag">NEW HERE?</span>
                <h2 class="promo-title">Build a resume that gets you hired</h2>
                <p class="promo-desc">Create your profile, showcase your skills, and get discovered by recruiters looking for talent like yours.</p>
                
                <div class="promo-action-wrap">
                    <a href="register.php" class="btn-pill-outline">Create an account</a>
                </div>

                <div class="promo-features-container">
                    <!-- <ul class="promo-features">
                        <li>
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Free resume builder with PDF export</span>
                        </li>
                        <li>
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Get found by top recruiters</span>
                        </li>
                        <li>
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                            <span>Real-time feedback on your profile</span>
                        </li>
                    </ul> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/auth.js"></script>
</body>
</html>
