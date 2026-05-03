<?php
/**
 * MarkGigs Search Results
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$query = $_GET['q'] ?? '';

// Search Users
$stmt = $pdo->prepare("
    SELECT u.id, u.role, COALESCE(i.full_name, c.name) as name, 
           COALESCE(i.headline, c.industry) as sub, 
           COALESCE(i.avatar, c.logo) as avatar
    FROM users u
    LEFT JOIN individuals i ON i.user_id = u.id
    LEFT JOIN companies c ON c.user_id = u.id
    WHERE COALESCE(i.full_name, c.name) LIKE ? OR COALESCE(i.headline, c.industry) LIKE ?
    LIMIT 20
");
$stmt->execute(["%$query%", "%$query%"]);
$results = $stmt->fetchAll();

$page_title = "Search Results for '" . htmlspecialchars($query) . "'";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 100,
        'height' => 100,
        'strokeDasharray' => '1 4',
        'class' => 'mask-radial'
    ]); ?>
</div>

<div class="page-header mb-4" style="position: relative; z-index: 5;">
    <h1 class="page-title">Search <span class="grad-text">Results</span></h1>
    <p class="text-muted">Showing results for "<?= htmlspecialchars($query) ?>"</p>
</div>

<div class="search-results" style="position: relative; z-index: 5;">
    <?php if (empty($results)): ?>
        <div class="card text-center py-5">
            <p class="text-muted">No results found for your search.</p>
        </div>
    <?php else: ?>
        <div class="card p-0 overflow-hidden">
            <div class="inbox-list">
                <?php foreach ($results as $r): ?>
                    <a href="profile.php?id=<?= $r['id'] ?>" class="inbox-item">
                        <img src="<?= BASE_URL ?>/uploads/avatars/default.svg" class="inbox-avatar">
                        <div class="inbox-info">
                            <div class="inbox-name"><?= htmlspecialchars($r['name']) ?></div>
                            <div class="inbox-preview"><?= htmlspecialchars($r['sub'] ?: ucfirst($r['role'])) ?></div>
                        </div>
                        <div class="inbox-meta">
                            <button class="btn btn-glass btn-sm">View</button>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.inbox-list { display: flex; flex-direction: column; }
.inbox-item { 
    display: flex; 
    align-items: center; 
    gap: 1.25rem; 
    padding: 1.25rem; 
    border-bottom: 1px solid var(--border-glass); 
    transition: background 0.2s ease;
}
.inbox-item:last-child { border-bottom: none; }
.inbox-item:hover { background: var(--bg-card-hover); }
.inbox-avatar { width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-glass); }
.inbox-info { flex: 1; min-width: 0; }
.inbox-name { font-weight: 700; color: white; margin-bottom: 2px; }
.inbox-preview { font-size: 0.9rem; color: var(--text-muted); }
</style>

<?php require_once 'includes/footer.php'; ?>
