<?php
/**
 * MarkGigs Opportunity Details & Apply
 */
require_once 'includes/functions.php';
require_login();

$opp_id = $_GET['id'] ?? null;
if (!$opp_id) redirect('jobs.php');

// Fetch opportunity details
$stmt = $pdo->prepare("
    SELECT o.*, c.name as company_name, c.logo, c.description as company_info 
    FROM opportunities o 
    JOIN companies c ON o.company_id = c.id 
    WHERE o.id = ?
");
$stmt->execute([$opp_id]);
$opp = $stmt->fetch();

if (!$opp) redirect('jobs.php');

// Check if already applied
$individual_id = null;
if ($_SESSION['role'] === 'individual') {
    $stmt = $pdo->prepare("SELECT id FROM individuals WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $individual_id = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT id, applied_at FROM applications WHERE individual_id = ? AND opportunity_id = ?");
    $stmt->execute([$individual_id, $opp_id]);
    $existing_app = $stmt->fetch();
}

$page_title = $opp['title'];
require_once 'includes/header.php';
?>

<div class="row" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; margin-top: 2rem;">
    <!-- Main Content -->
    <div class="opp-details">
        <div class="card mb-4" style="background: rgba(108, 99, 255, 0.05); border-color: var(--accent-primary);">
            <div class="d-flex align-items-center mb-3">
                <img src="<?= BASE_URL ?>/uploads/logos/default.svg" style="width: 80px; height: 80px; border-radius: 12px; background: white; padding: 10px; margin-right: 20px;">
                <div>
                    <h1 style="margin: 0;"><?= htmlspecialchars($opp['title']) ?></h1>
                    <p class="text-accent h5"><?= htmlspecialchars($opp['company_name']) ?></p>
                </div>
            </div>
            <div class="opp-meta">
                <span class="chip chip-accent"><?= strtoupper($opp['type']) ?></span>
                <?php if ($opp['is_remote']): ?>
                    <span class="chip">REMOTE</span>
                <?php endif; ?>
                <span class="text-muted"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($opp['location']) ?></span>
            </div>
        </div>

        <div class="card mb-4">
            <h4 class="grad-text">Job Description</h4>
            <div class="post-body" style="font-size: 1.1rem; line-height: 1.8;">
                <?= nl2br(htmlspecialchars($opp['description'])) ?>
            </div>
        </div>

        <?php if (!empty($opp['requirements'])): ?>
            <div class="card mb-4">
                <h4 class="grad-text">Requirements</h4>
                <div class="post-body">
                    <?= nl2br(htmlspecialchars($opp['requirements'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar: Application Action -->
    <aside class="opp-actions">
        <div class="card sticky-top" style="top: 90px;">
            <?php if ($_SESSION['role'] === 'individual'): ?>
                <?php if ($existing_app): ?>
                    <div class="text-center">
                        <div class="chip chip-accent py-2 px-3 mb-3">Application Submitted</div>
                        <p class="text-muted small">You applied for this position on <?= date('M j, Y', strtotime($existing_app['applied_at'])) ?></p>
                        <a href="jobs.php" class="btn btn-glass w-100">Back to Board</a>
                    </div>
                <?php else: ?>
                    <h4>Apply Now</h4>
                    <p class="text-muted small mb-4">Submit your application to the hiring team.</p>
                    <form action="apply.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="opportunity_id" value="<?= $opp_id ?>">
                        <div class="form-group">
                            <label>Cover Letter</label>
                            <textarea name="cover_letter" class="form-control" rows="5" placeholder="Why are you a good fit?" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Resume (PDF/Doc)</label>
                            <input type="file" name="resume" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit Application</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    Only Individual accounts can apply for opportunities.
                </div>
            <?php endif; ?>

            <hr>
            <h5>About the Company</h5>
            <p class="small text-muted"><?= htmlspecialchars($opp['company_info']) ?></p>
        </div>
    </aside>
</div>

<?php require_once 'includes/footer.php'; ?>
