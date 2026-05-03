<?php
/**
 * MarkGigs Network (User Directory)
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch all profiles (excluding current user)
$stmt = $pdo->prepare("
    SELECT u.id, u.role, COALESCE(i.full_name, c.name) as name, 
           COALESCE(i.headline, c.industry) as sub, 
           COALESCE(i.avatar, c.logo) as avatar,
           (SELECT status FROM connections 
            WHERE (requester_id = ? AND receiver_id = u.id) 
            OR (requester_id = u.id AND receiver_id = ?) LIMIT 1) as conn_status
    FROM users u
    LEFT JOIN individuals i ON i.user_id = u.id
    LEFT JOIN companies c ON c.user_id = u.id
    WHERE u.id != ?
    ORDER BY u.created_at DESC
");
$stmt->execute([$user_id, $user_id, $user_id]);
$profiles = $stmt->fetchAll();

$active_page = 'network';
$page_title = "Professional Network";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 100,
        'height' => 100,
        'strokeDasharray' => '4 1',
        'class' => 'mask-linear-br'
    ]); ?>
</div>

<div class="page-header mb-4" style="position: relative; z-index: 5;">
    <h1 class="page-title">Grow Your <span class="grad-text">Network</span></h1>
    <p class="text-muted">Connect with students, alumni, and employers from across the university.</p>
</div>

<div class="network-grid" style="position: relative; z-index: 5;">
    <?php if (empty($profiles)): ?>
        <div class="card text-center py-5 w-100">
            <p class="text-muted">No other members found yet.</p>
        </div>
    <?php else: ?>
        <?php foreach ($profiles as $p): ?>
            <div class="card profile-card">
                <div class="profile-card-banner"></div>
                <div class="profile-card-body">
                    <img src="<?= BASE_URL ?>/uploads/avatars/default.svg" class="profile-card-avatar">
                    <h3 class="profile-card-name"><?= htmlspecialchars($p['name'] ?? 'System Admin') ?></h3>
                    <p class="profile-card-sub"><?= htmlspecialchars($p['sub'] ?? ucfirst($p['role'])) ?></p>
                    
                    <div class="profile-card-actions mt-4">
                        <a href="profile.php?id=<?= $p['id'] ?>" class="btn btn-glass btn-sm">View Profile</a>
                        <a href="chat.php?to=<?= $p['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-message"></i></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.network-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
}

.profile-card { padding: 0; overflow: hidden; text-align: center; }
.profile-card-banner { height: 80px; background: var(--grad-primary); opacity: 0.2; }
.profile-card-body { padding: 1.5rem; margin-top: -40px; }
.profile-card-avatar { 
    width: 80px; height: 80px; 
    border-radius: 50%; 
    border: 4px solid var(--bg-dark); 
    margin: 0 auto 1rem; 
    background: var(--bg-dark);
}
.profile-card-name { font-size: 1.1rem; margin-bottom: 0.25rem; }
.profile-card-sub { font-size: 0.85rem; color: var(--text-muted); }
.profile-card-actions { display: flex; justify-content: center; gap: 0.5rem; }
</style>

<?php require_once 'includes/footer.php'; ?>
