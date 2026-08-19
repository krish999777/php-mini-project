<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account - ResumeHub</title>
    <!-- Fonts: Plus Jakarta Sans & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="split-page">
        <!-- Left Side: Green Promo Panel -->
        <div class="split-side green-panel">
            <div class="promo-content">
                <span class="promo-tag">ALREADY HAVE AN ACCOUNT?</span>
                <h2 class="promo-title">Welcome back to ResumeHub</h2>
                <p class="promo-desc">Sign in to access your resume, track applications, and connect with recruiters.</p>
                
                <div class="promo-action-wrap">
                    <a href="login.php" class="btn-pill-outline">Sign in</a>
                </div>

                <div class="promo-features-container">
                    <ul class="promo-features">
                        <li>
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Manage and update your resume anytime</span>
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
                            <span>Track your profile visibility</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Right Side: White Form Panel -->
        <div class="split-side form-panel">
            <!-- Brand Logo -->
            <a href="register.php" class="brand-logo">
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
                    <h1>Create an account</h1>
                    <p>Join thousands of candidates and recruiters on ResumeHub</p>
                </div>

                <!-- Alert Box -->
                <div class="alert-box" id="register-alert"></div>

                <!-- Sign Up Form -->
                <form action="api/register_action.php" method="POST" data-ajax="true" novalidate>
                    
                    <!-- Role Tabs (Candidate / Recruiter) -->
                    <div class="role-toggle-group">
                        <label class="role-toggle-btn active" id="role-candidate">
                            <input type="radio" name="role" value="candidate" checked>
                            <span>Candidate</span>
                        </label>
                        <label class="role-toggle-btn" id="role-recruiter">
                            <input type="radio" name="role" value="recruiter">
                            <span>Recruiter</span>
                        </label>
                    </div>

                    <!-- Full Name -->
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <div class="input-wrap">
                            <input type="text" id="name" name="name" class="form-control form-input" placeholder="John Doe" required autofocus>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrap">
                            <input type="email" id="email" name="email" class="form-control form-input" placeholder="you@example.com" required>
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

                    <!-- Hidden confirm password field default to password value for single-password design or synchronized -->
                    <input type="hidden" id="confirm_password" name="confirm_password" value="">

                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit">
                        <span class="spinner"></span>
                        <span class="btn-text">Sign up</span>
                    </button>
                </form>
            </div>

            <!-- Empty footer space to balance flex layout -->
            <div style="height: 20px;"></div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/auth.js"></script>
</body>
</html>
