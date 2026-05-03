<?php
/**
 * MarkGigs Profile View
 */
require_once 'includes/functions.php';
require_login();

$view_id = $_GET['id'] ?? $_SESSION['user_id'];

// Fetch user core table
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$view_id]);
$u = $stmt->fetch();

if (!$u) redirect('index.php');

// Fetch profile details based on role
if ($u['role'] === 'individual') {
    $stmt = $pdo->prepare("SELECT * FROM individuals WHERE user_id = ?");
    $stmt->execute([$view_id]);
    $profile = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$view_id]);
    $profile = $stmt->fetch();
}

if (!$profile) {
    $profile = [
        'full_name' => 'System Admin',
        'name' => 'System Admin',
        'headline' => 'Administrator',
        'industry' => 'Administration',
        'location' => 'System',
        'website' => '',
        'avatar' => null,
        'logo' => null,
        'bio' => 'System administrator account.',
        'description' => 'System administrator account.',
        'skills' => '',
        'achievements_academic' => null,
        'achievements_field' => null,
        'research_implementations' => null
    ];
}

$page_title = ($u['role'] === 'individual' ? $profile['full_name'] : $profile['name']) . " | Profile";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="profile-banner">
    <div class="profile-banner-grid">
        <?php render_grid_pattern([
            'width' => 40,
            'height' => 40,
            'strokeDasharray' => '4 4',
            'class' => 'mask-radial'
        ]); ?>
    </div>
    <div class="profile-banner-overlay"></div>
</div>
<div class="profile-container">
    <div class="card profile-header-card">
        <div class="profile-avatar-wrap">
            <img src="<?= BASE_URL ?>/<?= ($u['role'] === 'individual' && $profile['avatar'] ? 'uploads/avatars/' . $profile['avatar'] : ($u['role'] === 'company' && $profile['logo'] ? 'uploads/logos/' . $profile['logo'] : 'uploads/avatars/default.svg')) ?>" class="profile-avatar-large">
        </div>
        
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="profile-name"><?= $u['role'] === 'individual' ? $profile['full_name'] : $profile['name'] ?></h1>
                <p class="profile-headline"><?= $u['role'] === 'individual' ? ($profile['headline'] ?? 'Professional') : ($profile['industry'] ?? 'Company') ?></p>
                <div class="profile-meta-row">
                    <span><i class="fa-solid fa-location-dot"></i> <?= $profile['location'] ?? 'Global' ?></span>
                    <?php if ($profile['website']): ?>
                        <span><a href="<?= $profile['website'] ?>" target="_blank"><i class="fa-solid fa-globe"></i> Website</a></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="profile-actions">
                <?php if ($view_id == $_SESSION['user_id']): ?>
                    <a href="edit_profile.php" class="btn btn-glass"><i class="fa-solid fa-pencil"></i> Edit Profile</a>
                <?php else: ?>
                    <a href="chat.php?to=<?= $view_id ?>" class="btn btn-primary">Message</a>
                    <button class="btn btn-glass ml-2">Connect</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 2rem;">
        <div class="profile-main">
            <div class="card mb-4">
                <h4 class="grad-text">About</h4>
                <p><?= nl2br(htmlspecialchars($profile['bio'] ?? $profile['description'] ?? 'No bio provided.')) ?></p>
            </div>

            <?php if ($u['role'] === 'individual'): ?>
                <div class="card mb-4">
                    <h4 class="grad-text">Skills</h4>
                    <div class="d-flex flex-wrap">
                        <?php 
                        $skills = explode(',', $profile['skills'] ?? '');
                        foreach ($skills as $skill): 
                            if (trim($skill)): ?>
                                <span class="chip"><?= trim($skill) ?></span>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <?php if ($profile['achievements_academic']): ?>
                    <div class="card mb-4">
                        <h4 class="grad-text"><i class="fa-solid fa-graduation-cap small me-2"></i> Academic Achievements</h4>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($profile['achievements_academic'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($profile['achievements_field']): ?>
                    <div class="card mb-4">
                        <h4 class="grad-text"><i class="fa-solid fa-trophy small me-2"></i> Field Achievements</h4>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($profile['achievements_field'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($profile['research_implementations']): ?>
                    <div class="card mb-4">
                        <h4 class="grad-text"><i class="fa-solid fa-microscope small me-1"></i> Research & Projects</h4>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($profile['research_implementations'])) ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <aside class="profile-sidebar">
            <div class="card">
                <h4>Quick Stats</h4>
                <div class="profile-stats-row mt-3">
                    <div class="pstat"><span>24</span> Connects</div>
                    <div class="pstat"><span>12</span> Posts</div>
                </div>
            </div>
        </aside>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
