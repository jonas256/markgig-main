<?php
/**
 * MarkGigs Mentors (Mentor Directory)
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch all mentors
$stmt = $pdo->prepare("
    SELECT u.id, i.full_name, i.headline, i.avatar, i.mentor_expertise, i.mentor_availability,
           i.achievements_academic, i.achievements_field, i.research_implementations
    FROM users u
    JOIN individuals i ON i.user_id = u.id
    WHERE i.is_mentor = 1
    ORDER BY u.created_at DESC
");
$stmt->execute();
$mentors = $stmt->fetchAll();

$active_page = 'mentorship';
$page_title = "Find a Mentor";
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
    <h1 class="page-title">Find a <span class="grad-text">Mentor</span></h1>
    <p class="text-muted">Learn from established alumni and professionals in your industry.</p>
</div>

<div class="network-grid" style="position: relative; z-index: 5;">
    <?php if (empty($mentors)): ?>
        <div class="card text-center py-5 w-100">
            <p class="text-muted">No mentors available at the moment. Check back later!</p>
        </div>
    <?php else: ?>
        <?php foreach ($mentors as $m): ?>
            <div class="card mentor-card">
                <div class="profile-card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?= BASE_URL ?>/uploads/avatars/default.svg" class="mentor-avatar">
                        <div class="text-start">
                            <h3 class="m-0" style="font-size: 1.1rem;"><?= htmlspecialchars($m['full_name']) ?></h3>
                            <p class="small text-muted m-0"><?= htmlspecialchars($m['headline']) ?></p>
                        </div>
                    </div>
                    
                    <div class="mentor-tags mb-3">
                        <span class="badge bg-primary">Expertise: <?= htmlspecialchars($m['mentor_expertise']) ?></span>
                        <span class="badge bg-secondary"><?= ucfirst($m['mentor_availability']) ?></span>
                    </div>

                    <?php if ($m['achievements_academic'] || $m['achievements_field'] || $m['research_implementations']): ?>
                        <div class="mentor-details mt-2 mb-3 small text-start">
                            <?php if ($m['achievements_academic']): ?>
                                <div class="detail-group mb-2">
                                    <div class="detail-label text-primary font-weight-bold"><i class="fa-solid fa-graduation-cap me-1"></i> Academic</div>
                                    <div class="detail-content text-muted"><?= htmlspecialchars($m['achievements_academic']) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($m['achievements_field']): ?>
                                <div class="detail-group mb-2">
                                    <div class="detail-label text-success font-weight-bold"><i class="fa-solid fa-trophy me-1"></i> Field Achievements</div>
                                    <div class="detail-content text-muted"><?= htmlspecialchars($m['achievements_field']) ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if ($m['research_implementations']): ?>
                                <div class="detail-group">
                                    <div class="detail-label text-warning font-weight-bold"><i class="fa-solid fa-microscope me-1"></i> Research & Projects</div>
                                    <div class="detail-content text-muted"><?= htmlspecialchars($m['research_implementations']) ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-card-actions mt-auto">
                        <a href="profile.php?id=<?= $m['id'] ?>" class="btn btn-glass btn-sm w-100">View Bio</a>
                        <a href="chat.php?to=<?= $m['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-graduation-cap"></i> Request</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.mentor-card { padding: 1.5rem; display: flex; flex-direction: column; }
.mentor-avatar { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; }
.mentor-tags { display: flex; flex-wrap: wrap; gap: 5px; }
.network-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}
</style>

<?php require_once 'includes/footer.php'; ?>
