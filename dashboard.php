<?php
session_start();

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

$userId = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['name'] ?? 'User');
$userEmail = htmlspecialchars($_SESSION['email'] ?? '');
$userRole = htmlspecialchars($_SESSION['role'] ?? 'candidate');

// Baseline profile values
$profile = [
    'headline' => '',
    'phone' => '',
    'location' => '',
    'skills' => '',
    'experience_years' => 0,
    'education' => '',
    'bio' => ''
];

if ($userRole === 'candidate') {
    try {
        $stmt = $pdo->prepare("
            SELECT u.name, u.email, cp.headline, cp.phone, cp.location, cp.skills, cp.experience_years, cp.education, cp.bio
            FROM users u
            LEFT JOIN candidate_profiles cp ON u.id = cp.user_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $userId]);
        $data = $stmt->fetch();
        if ($data) {
            $userName = htmlspecialchars($data['name'] ?? $userName);
            $userEmail = htmlspecialchars($data['email'] ?? $userEmail);
            $profile['headline'] = htmlspecialchars($data['headline'] ?? '');
            $profile['phone'] = htmlspecialchars($data['phone'] ?? '');
            $profile['location'] = htmlspecialchars($data['location'] ?? '');
            $profile['skills'] = htmlspecialchars($data['skills'] ?? '');
            $profile['experience_years'] = (int)($data['experience_years'] ?? 0);
            $profile['education'] = htmlspecialchars($data['education'] ?? '');
            $profile['bio'] = htmlspecialchars($data['bio'] ?? '');
        }
    } catch (PDOException $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
$candidatesList = [];
if ($userRole === 'recruiter') {
    try {
        $candStmt = $pdo->prepare("
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
        $candStmt->execute();
        $candidatesList = $candStmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard recruiter candidate fetch error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($userRole === 'candidate') ? 'Candidate Portal' : 'Recruiter Portal'; ?> - ResumeHub</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
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

    <!-- Main Content Container -->
    <main class="dashboard-main">
        <div class="dashboard-card">
            
            <!-- User Welcome Header -->
            <div style="margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-color);">
                <h1 style="font-family: var(--font-serif); font-size: 2rem; color: var(--text-main); margin-bottom: 6px;">
                    Welcome back, <span id="header-user-name"><?php echo $userName; ?></span>!
                </h1>
                <p style="color: var(--text-muted); font-size: 0.95rem;">
                    Logged in as <strong id="cand_email_display"><?php echo $userEmail; ?></strong>
                </p>
            </div>

            <?php if ($userRole === 'candidate'): ?>
                <!-- Candidate Navigation Tabs -->
                <div class="cand-tabs-nav">
                    <button type="button" class="cand-tab-btn active" data-tab="editor-tab">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Profile Editor</span>
                    </button>
                    <button type="button" class="cand-tab-btn" data-tab="preview-tab">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <span>Live Resume Preview</span>
                    </button>
                </div>

                <!-- Alert Box for Profile Updates -->
                <div class="alert-box" id="profile-alert" style="display: none; margin-bottom: 24px;"></div>

                <!-- TAB 1: Profile Editor -->
                <div class="cand-tab-pane active" id="editor-tab">
                    <form id="candidate-profile-form" novalidate>
                        
                        <!-- Name & Professional Headline -->
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="cand_name" class="form-label">Full Name *</label>
                                <div class="input-wrap">
                                    <input type="text" id="cand_name" name="name" class="form-control form-input" value="<?php echo $userName; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cand_headline" class="form-label">Professional Headline</label>
                                <div class="input-wrap">
                                    <input type="text" id="cand_headline" name="headline" class="form-control form-input" placeholder="e.g. Senior Full Stack Developer" value="<?php echo $profile['headline']; ?>">
                                </div>
                                <span class="form-helper">Appears right below your name on search cards and resumes</span>
                            </div>
                        </div>

                        <!-- Phone, Location & Experience Years -->
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="cand_phone" class="form-label">Phone Number</label>
                                <div class="input-wrap">
                                    <input type="text" id="cand_phone" name="phone" class="form-control form-input" placeholder="+1 (555) 000-0000" value="<?php echo $profile['phone']; ?>">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="cand_location" class="form-label">Location (City, Country)</label>
                                <div class="input-wrap">
                                    <input type="text" id="cand_location" name="location" class="form-control form-input" placeholder="e.g. San Francisco, CA" value="<?php echo $profile['location']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="cand_experience_years" class="form-label">Years of Experience</label>
                                <div class="input-wrap">
                                    <input type="number" id="cand_experience_years" name="experience_years" class="form-control form-input" min="0" max="60" value="<?php echo $profile['experience_years']; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Skills Management (Interactive Tag Cloud) -->
                        <div class="form-group">
                            <label class="form-label">Skills & Technologies</label>
                            <div class="skills-interactive-box">
                                <div id="skills-pills-container" style="display: contents;"></div>
                                <input type="text" id="skill-tag-input" class="skill-tag-input" placeholder="Type a skill & press Enter or comma (e.g. PHP, React, MySQL)...">
                            </div>
                            <input type="hidden" id="skills-hidden-input" name="skills" value="<?php echo $profile['skills']; ?>">
                            <span class="form-helper">Press Enter or comma to add skills. Recruiters search by these keywords.</span>
                        </div>

                        <!-- Education -->
                        <div class="form-group">
                            <label for="cand_education" class="form-label">Education & Qualifications</label>
                            <textarea id="cand_education" name="education" class="form-textarea" placeholder="e.g. B.S. in Computer Science - Stanford University (2018 - 2022)"><?php echo $profile['education']; ?></textarea>
                            <span class="form-helper">List degrees, universities, certifications, or relevant coursework</span>
                        </div>

                        <!-- Professional Bio / Summary -->
                        <div class="form-group">
                            <label for="cand_bio" class="form-label">Professional Summary / Bio</label>
                            <textarea id="cand_bio" name="bio" class="form-textarea" style="min-height: 120px;" placeholder="Brief summary of your background, key achievements, and what role you are looking for..."><?php echo $profile['bio']; ?></textarea>
                            <span class="form-helper">Highlight your career background and what you bring to potential employers</span>
                        </div>

                        <!-- Submit Button -->
                        <div style="margin-top: 28px; display: flex; justify-content: flex-end;">
                            <button type="submit" class="btn-submit" style="width: auto; min-width: 180px; padding: 12px 28px;">
                                <span class="spinner"></span>
                                <span class="btn-text">Save Profile</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: Live Resume Preview -->
                <div class="cand-tab-pane" id="preview-tab">
                    
                    <div class="resume-action-bar">
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                            This is how your profile and credentials appear to hiring recruiters.
                        </p>
                        <button type="button" id="btn-print-resume" class="btn-pill-outline" style="border-color: #D1D5DB; color: #374151; padding: 8px 18px; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Print / Save PDF</span>
                        </button>
                    </div>

                    <!-- Clean Executive Resume Sheet -->
                    <div class="resume-preview-sheet">
                        <header class="resume-header">
                            <div>
                                <h2 class="resume-name" id="prev-name"><?php echo $userName; ?></h2>
                                <div class="resume-headline" id="prev-headline">
                                    <?php echo !empty($profile['headline']) ? $profile['headline'] : 'Professional Headline'; ?>
                                </div>
                                <div class="resume-contact-meta" id="prev-contact-bar">
                                    <span>📧 <?php echo $userEmail; ?></span>
                                    <?php if (!empty($profile['phone'])): ?>
                                        <span>&bull; 📞 <?php echo $profile['phone']; ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($profile['location'])): ?>
                                        <span>&bull; 📍 <?php echo $profile['location']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <span class="resume-exp-badge" id="prev-exp-badge">
                                    <?php echo $profile['experience_years']; ?> Year<?php echo $profile['experience_years'] === 1 ? '' : 's'; ?> Experience
                                </span>
                            </div>
                        </header>

                        <!-- Summary Section -->
                        <section class="resume-section">
                            <h3 class="resume-section-title">Professional Summary</h3>
                            <div class="resume-section-body" id="prev-bio">
                                <?php echo !empty($profile['bio']) ? nl2br($profile['bio']) : '<em>No professional summary added yet. Use the Profile Editor to describe your background.</em>'; ?>
                            </div>
                        </section>

                        <!-- Skills Section -->
                        <section class="resume-section">
                            <h3 class="resume-section-title">Key Skills & Competencies</h3>
                            <div class="resume-skills-grid" id="prev-skills-list">
                                <?php 
                                if (!empty($profile['skills'])) {
                                    $skillsArr = array_filter(array_map('trim', explode(',', $profile['skills'])));
                                    foreach ($skillsArr as $skill) {
                                        echo '<span class="resume-skill-badge">' . htmlspecialchars($skill) . '</span>';
                                    }
                                } else {
                                    echo '<em>No skills added yet.</em>';
                                }
                                ?>
                            </div>
                        </section>

                        <!-- Education Section -->
                        <section class="resume-section" style="margin-bottom: 0;">
                            <h3 class="resume-section-title">Education & Credentials</h3>
                            <div class="resume-section-body" id="prev-education">
                                <?php echo !empty($profile['education']) ? nl2br($profile['education']) : '<em>No education details specified.</em>'; ?>
                            </div>
                        </section>
                    </div>
                </div>

                <!-- Candidate Scripts -->
                <script src="assets/js/candidate.js"></script>

            <?php else: ?>
                <!-- RECRUITER DISCOVERY SUITE -->
                <div class="recruiter-suite">
                    
                    <!-- Search & Filter Toolbar -->
                    <div class="recruiter-toolbar">
                        <div class="recruiter-search-box">
                            <span class="recruiter-search-icon">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </span>
                            <input type="text" id="recruiter-search-input" class="recruiter-search-input" placeholder="Search candidates by name, skill, title, or location...">
                            <button type="button" id="btn-clear-search" class="btn-clear-search" title="Clear search">&times;</button>
                        </div>
                        <div>
                            <span class="candidate-counter-badge">
                                <span id="candidate-count-number"><?php echo count($candidatesList); ?></span> Candidates Available
                            </span>
                        </div>
                    </div>

                    <!-- Candidates Cards Grid -->
                    <div class="candidates-grid" id="candidates-grid">
                        <?php if (!empty($candidatesList)): ?>
                            <?php foreach ($candidatesList as $cand): 
                                $cName = htmlspecialchars($cand['name'] ?? 'Candidate');
                                $cHeadline = htmlspecialchars($cand['headline'] ?? 'Job Seeker');
                                $cExp = (int)($cand['experience_years'] ?? 0);
                                $cLocation = htmlspecialchars($cand['location'] ?? '');
                                $avatarInitial = strtoupper(substr($cName, 0, 1) ?: 'C');
                                $skillsRaw = trim($cand['skills'] ?? '');
                                $candSkills = !empty($skillsRaw) ? array_filter(array_map('trim', explode(',', $skillsRaw))) : [];
                            ?>
                                <div class="candidate-card">
                                    <div class="cand-card-top">
                                        <div class="cand-avatar"><?php echo $avatarInitial; ?></div>
                                        <div class="cand-card-main-info">
                                            <h3 class="cand-card-name"><?php echo $cName; ?></h3>
                                            <div class="cand-card-headline"><?php echo $cHeadline; ?></div>
                                        </div>
                                    </div>

                                    <div class="cand-card-meta">
                                        <span class="cand-card-exp-tag"><?php echo $cExp; ?> yr<?php echo $cExp === 1 ? '' : 's'; ?> exp</span>
                                        <?php if (!empty($cLocation)): ?>
                                            <span class="cand-card-location">📍 <?php echo $cLocation; ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="cand-card-skills">
                                        <?php if (!empty($candSkills)): ?>
                                            <?php foreach (array_slice($candSkills, 0, 4) as $s): ?>
                                                <span class="cand-skill-chip"><?php echo htmlspecialchars($s); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($candSkills) > 4): ?>
                                                <span class="cand-skill-chip-more">+<?php echo (count($candSkills) - 4); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: #9CA3AF; font-style: italic;">No skills specified</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="cand-card-footer">
                                        <button type="button" class="btn-view-profile" data-id="<?php echo $cand['id']; ?>">
                                            View Full Resume
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Empty State -->
                    <div class="candidates-empty-state" id="candidates-empty-state" style="<?php echo empty($candidatesList) ? 'display:block;' : 'display:none;'; ?>">
                        <div class="empty-icon">🔍</div>
                        <h3 style="font-size: 1.15rem; color: #374151; margin-bottom: 6px;">No matching candidates found</h3>
                        <p style="color: #6B7280; font-size: 0.9rem; max-width: 400px; margin: 0 auto;">
                            Try adjusting your search terms or clearing the filter to see all registered candidates.
                        </p>
                    </div>

                </div>

                <!-- Candidate Detail Modal -->
                <div class="candidate-detail-modal" id="candidate-detail-modal">
                    <div class="modal-backdrop" id="modal-backdrop"></div>
                    <div class="modal-sheet">
                        
                        <div class="modal-header-bar">
                            <span style="font-size: 0.88rem; font-weight: 600; color: #7C3AED;">
                                Candidate Profile & Resume
                            </span>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <button type="button" id="modal-print-btn" class="btn-pill-outline" style="border-color: #D1D5DB; color: #374151; padding: 6px 14px; font-size: 0.82rem; display: inline-flex; align-items: center; gap: 6px; margin: 0;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    <span>Print Resume</span>
                                </button>
                                <button type="button" class="modal-close-btn" id="modal-close-btn" aria-label="Close modal">&times;</button>
                            </div>
                        </div>

                        <div class="modal-body">
                            <!-- Resume Content Inside Modal -->
                            <header class="resume-header">
                                <div>
                                    <h2 class="resume-name" id="modal-cand-name">Candidate Name</h2>
                                    <div class="resume-headline" id="modal-cand-headline" style="color: #7C3AED;">Professional Headline</div>
                                    <div class="resume-contact-meta" id="modal-cand-contacts"></div>
                                </div>
                                <div>
                                    <span class="resume-exp-badge" id="modal-cand-exp-badge" style="background: #F5F3FF; color: #7C3AED; border-color: #DDD6FE;">
                                        0 Years Experience
                                    </span>
                                </div>
                            </header>

                            <!-- Summary -->
                            <section class="resume-section">
                                <h3 class="resume-section-title">Professional Summary</h3>
                                <div class="resume-section-body" id="modal-cand-bio"></div>
                            </section>

                            <!-- Skills -->
                            <section class="resume-section">
                                <h3 class="resume-section-title">Key Skills & Competencies</h3>
                                <div class="resume-skills-grid" id="modal-cand-skills"></div>
                            </section>

                            <!-- Education -->
                            <section class="resume-section" style="margin-bottom: 0;">
                                <h3 class="resume-section-title">Education & Credentials</h3>
                                <div class="resume-section-body" id="modal-cand-education"></div>
                            </section>
                        </div>

                    </div>
                </div>

                <!-- Recruiter Scripts -->
                <script src="assets/js/recruiter.js"></script>

            <?php endif; ?>

        </div>
    </main>
</body>
</html>


