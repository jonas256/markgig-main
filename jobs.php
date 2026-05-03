<?php
/**
 * MarkGigs Opportunity Board
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Filter logic
$type_filter = $_GET['type'] ?? '';
$search = $_GET['q'] ?? '';

$query = "SELECT o.*, c.name as company_name, c.logo 
          FROM opportunities o 
          JOIN companies c ON o.company_id = c.id 
          WHERE o.status = 'open'";

$params = [];

if (!empty($type_filter)) {
    $query .= " AND o.type = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $query .= " AND (o.title LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$opps = $stmt->fetchAll();

$active_page = 'jobs';
$page_title = "Opportunities Board";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 80,
        'height' => 80,
        'strokeDasharray' => '1 4',
        'class' => 'mask-radial'
    ]); ?>
</div>

<div class="page-header mb-4" style="position: relative; z-index: 5;">
    <h1 class="page-title">Opportunity <span class="grad-text">Pipeline</span></h1>
    <p class="text-muted">Find your next internship, job, or project gig.</p>
</div>

<div class="opp-layout">
    <!-- Filters -->
    <aside class="filter-card">
        <div class="card">
            <h4>Filters</h4>
            <form action="jobs.php" method="GET">
                <div class="filter-group mt-3">
                    <label>Opportunity Type</label>
                    <select name="type" class="form-control" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="internship" <?= $type_filter === 'internship' ? 'selected' : '' ?>>Internship</option>
                        <option value="job" <?= $type_filter === 'job' ? 'selected' : '' ?>>Full-time Job</option>
                        <option value="gig" <?= $type_filter === 'gig' ? 'selected' : '' ?>>Project Gig</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Keywords</label>
                    <input type="text" name="q" class="form-control" placeholder="Search..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <button type="submit" class="btn btn-glass btn-sm w-100">Apply Filters</button>
            </form>
            
            <?php if ($_SESSION['role'] === 'company'): ?>
                <hr>
                <a href="company_dashboard.php" class="btn btn-primary btn-sm w-100">Post Opportunity</a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Opportunity List -->
    <main class="opp-list">
        <?php if (empty($opps)): ?>
            <div class="card text-center py-5">
                <p class="text-muted">No opportunities found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($opps as $opp): ?>
                <div class="card opp-card">
                    <div class="opp-card-top">
                        <img src="<?= BASE_URL ?>/uploads/logos/default.svg" class="opp-company-logo" style="background: white;">
                        <div class="opp-info">
                            <h3 class="opp-title"><?= htmlspecialchars($opp['title']) ?></h3>
                            <p class="opp-company"><?= htmlspecialchars($opp['company_name']) ?></p>
                            <div class="opp-meta">
                                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($opp['location']) ?></span>
                                <span><i class="fa-solid fa-clock"></i> Posted <?= date('M j', strtotime($opp['created_at'])) ?></span>
                                <span class="badge-type type-<?= $opp['type'] ?>"><?= $opp['type'] ?></span>
                        </div>
                        </div>
                        <a href="job_details.php?id=<?= $opp['id'] ?>" class="btn btn-glass ml-auto">Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
