<?php
require_once __DIR__ . '/includes/functions.php';

// Require login
require_login();

$page_title = "Account Settings";
$active_page = "account";

require_once __DIR__ . '/includes/header.php';
?>

<div class="container" style="max-width: 600px; margin-top: 3rem;">
    <div class="card">
        <h2 style="margin-bottom: 1.5rem;"><i class="fa-solid fa-gear"></i> Account Panel</h2>
        
        <p class="text-muted" style="margin-bottom: 2rem;">Manage your MarkGigs account and session.</p>
        
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <a href="<?= BASE_URL ?>/profile.php" class="btn btn-glass" style="justify-content: flex-start; padding: 1rem;">
                <i class="fa-solid fa-user" style="width: 30px;"></i>
                <div style="text-align: left;">
                    <div style="font-weight: bold; font-size: 1.1rem; color: var(--text-primary);">My Profile</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">View and edit your public profile</div>
                </div>
            </a>
            
            <?php if (has_role('admin')): ?>
                <a href="<?= BASE_URL ?>/admin.php" class="btn btn-glass" style="justify-content: flex-start; padding: 1rem; border-color: var(--accent-gold);">
                    <i class="fa-solid fa-shield-halved" style="width: 30px; color: var(--accent-gold);"></i>
                    <div style="text-align: left;">
                        <div style="font-weight: bold; font-size: 1.1rem; color: var(--text-primary);">Admin Panel</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">Manage users and platform settings</div>
                    </div>
                </a>
            <?php endif; ?>
            
            <hr style="border: 0; border-top: 1px solid var(--border-glass); margin: 0.5rem 0;">
            
            <a href="<?= BASE_URL ?>/auth/logout.php" class="btn btn-glass" style="justify-content: flex-start; padding: 1rem; border-color: rgba(255, 107, 107, 0.3);">
                <i class="fa-solid fa-arrow-right-from-bracket" style="width: 30px; color: var(--accent-warm);"></i>
                <div style="text-align: left;">
                    <div style="font-weight: bold; font-size: 1.1rem; color: var(--accent-warm);">Logout</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Securely end your session</div>
                </div>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
