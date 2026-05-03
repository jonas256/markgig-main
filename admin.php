<?php
/**
 * MarkGigs Admin Dashboard
 */
require_once 'includes/functions.php';
require_login();

if (!has_role('admin')) {
    set_flash("Access Denied: Admin only.", "danger");
    redirect('index.php');
}

// Handle Verification
if (isset($_GET['verify'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$_GET['verify']]);
    set_flash("User verified successfully.", "success");
    redirect('admin.php');
}

// Handle Verification via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE id = ?");
    $stmt->execute([$user_id]);
    set_flash("User verified successfully.", "success");
    redirect('admin.php');
}

// Handle Admin Promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'promote_admin' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $user_id = (int)$_POST['user_id'];
    $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
    $stmt->execute([$user_id]);
    set_flash("User promoted to Admin successfully.", "success");
    redirect('admin.php');
}

// Handle Admin Demotion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'demote_admin' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id !== $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = 'individual' WHERE id = ?");
        $stmt->execute([$user_id]);
        set_flash("Admin demoted successfully.", "success");
    } else {
        set_flash("You cannot demote yourself.", "danger");
    }
    redirect('admin.php');
}

// Fetch stats
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$job_count = $pdo->query("SELECT COUNT(*) FROM opportunities")->fetchColumn();
$app_count = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

// Fetch unverified users
$stmt = $pdo->query("
    SELECT u.*, COALESCE(i.full_name, c.name) as name 
    FROM users u 
    LEFT JOIN individuals i ON i.user_id = u.id 
    LEFT JOIN companies c ON c.user_id = u.id 
    WHERE u.is_verified = 0 AND u.role != 'admin'
    ORDER BY u.created_at DESC
");
$unverified = $stmt->fetchAll();

// Fetch all users for admin management
$stmt = $pdo->query("
    SELECT u.*, COALESCE(i.full_name, c.name) as name 
    FROM users u 
    LEFT JOIN individuals i ON i.user_id = u.id 
    LEFT JOIN companies c ON c.user_id = u.id 
    ORDER BY u.role DESC, u.created_at DESC
");
$all_users = $stmt->fetchAll();

$page_title = "Admin Dashboard";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<style>
.admin-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.admin-header {
    margin-bottom: 3rem;
}

.admin-title {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.admin-subtitle {
    color: #999;
    font-size: 0.95rem;
    margin-bottom: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.stat-card {
    background: rgba(102, 126, 234, 0.08);
    border: 1px solid rgba(102, 126, 234, 0.2);
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
}

.stat-card:hover {
    border-color: rgba(102, 126, 234, 0.4);
    background: rgba(102, 126, 234, 0.12);
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #667eea;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #fff;
    margin: 0.5rem 0;
}

.stat-label {
    color: #999;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.admin-section {
    margin-bottom: 3rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.section-icon {
    font-size: 1.8rem;
    color: #667eea;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.section-description {
    color: #999;
    font-size: 0.9rem;
    margin: 0;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 12px;
    overflow: hidden;
}

.admin-table thead {
    background: rgba(102, 126, 234, 0.1);
}

.admin-table th {
    padding: 1rem;
    text-align: left;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #aaa;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.admin-table td {
    padding: 1.2rem 1rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: #ccc;
}

.admin-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
}

.admin-table tbody tr:last-child td {
    border-bottom: none;
}

.user-cell {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.user-name {
    font-weight: 600;
    color: #fff;
}

.user-email {
    font-size: 0.85rem;
    color: #999;
}

.role-chip {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    width: fit-content;
}

.role-admin {
    background: rgba(255, 107, 107, 0.2);
    color: #ff6b6b;
}

.role-individual {
    background: rgba(102, 126, 234, 0.2);
    color: #667eea;
}

.role-company {
    background: rgba(76, 175, 80, 0.2);
    color: #4caf50;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #999;
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #555;
}
</style>

<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-title"><i class="fa-solid fa-shield"></i> Admin Control Center</h1>
        <p class="admin-subtitle">Manage platform users, verify accounts, and assign admin roles</p>
    </div>

    <!-- Dashboard Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
            <div class="stat-number"><?= $user_count ?></div>
            <div class="stat-label">Total Users</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-briefcase"></i></div>
            <div class="stat-number"><?= $job_count ?></div>
            <div class="stat-label">Opportunities</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fa-solid fa-file-invoice"></i></div>
            <div class="stat-number"><?= $app_count ?></div>
            <div class="stat-label">Applications</div>
        </div>
    </div>


    <!-- Admin Management Section -->
    <div class="admin-section card" style="padding: 2rem; border: 1px solid rgba(255, 255, 255, 0.1);">
        <div class="section-header">
            <div class="section-icon"><i class="fa-solid fa-crown"></i></div>
            <div>
                <h2 class="section-title">Admin Management</h2>
                <p class="section-description">Promote or demote users from admin role</p>
            </div>
        </div>

        <?php if (empty($all_users)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-inbox"></i></div>
                <p>No users found</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User Information</th>
                        <th>Role</th>
                        <th>Email</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <span class="user-name"><?= htmlspecialchars($user['name'] ?? 'System Admin') ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="role-chip role-<?= strtolower($user['role']) ?>">
                                    <?= strtoupper($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="user-email"><?= $user['email'] ?></span>
                            </td>
                            <td>
                                <small><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                            </td>
                            <td>
                                <form action="admin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <div class="action-buttons">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <button type="submit" name="action" value="promote_admin" class="btn btn-success btn-sm">
                                                <i class="fa-solid fa-arrow-up"></i> Make Admin
                                            </button>
                                        <?php elseif ($user['id'] !== $_SESSION['user_id']): ?>
                                            <button type="submit" name="action" value="demote_admin" class="btn btn-warning btn-sm">
                                                <i class="fa-solid fa-arrow-down"></i> Demote
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="fa-solid fa-star"></i> You</span>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Verification Queue Section -->
    <div class="admin-section card" style="padding: 2rem; border: 1px solid rgba(255, 255, 255, 0.1); margin-top: 2rem;">
        <div class="section-header">
            <div class="section-icon"><i class="fa-solid fa-check-circle"></i></div>
            <div>
                <h2 class="section-title">User Verification Queue</h2>
                <p class="section-description">Review and verify pending user accounts</p>
            </div>
        </div>

        <?php if (empty($unverified)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fa-solid fa-check"></i></div>
                <p>All users are verified! Great work! 🎉</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User Information</th>
                        <th>Account Type</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($unverified as $user): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <span class="user-name"><?= htmlspecialchars($user['name'] ?? 'System Admin') ?></span>
                                    <span class="user-email"><?= $user['email'] ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="role-chip role-<?= strtolower($user['role']) ?>">
                                    <?= strtoupper($user['role']) ?>
                                </span>
                            </td>
                            <td>
                                <small><?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                            </td>
                            <td>
                                <form action="admin.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <div class="action-buttons">
                                        <button type="submit" name="action" value="verify" class="btn btn-primary btn-sm">
                                            <i class="fa-solid fa-check"></i> Verify User
                                        </button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
