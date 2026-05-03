<?php
/**
 * MarkGigs Inbox (Conversations List)
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch all conversations for the user
$stmt = $pdo->prepare("
    SELECT u.id, COALESCE(i.full_name, c.name) as name, COALESCE(i.avatar, c.logo) as avatar,
           (SELECT content FROM messages 
            WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
            ORDER BY sent_at DESC LIMIT 1) as last_message,
           (SELECT sent_at FROM messages 
            WHERE (sender_id = u.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = u.id) 
            ORDER BY sent_at DESC LIMIT 1) as last_sent
    FROM users u
    LEFT JOIN individuals i ON i.user_id = u.id
    LEFT JOIN companies c ON c.user_id = u.id
    WHERE u.id IN (
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END
        FROM messages
        WHERE sender_id = ? OR receiver_id = ?
    )
    ORDER BY last_sent DESC
");
$stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
$conversations = $stmt->fetchAll();

$active_page = 'messages';
$page_title = "My Inbox";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 120,
        'height' => 120,
        'strokeDasharray' => '1 6',
        'class' => 'mask-radial'
    ]); ?>
</div>

<div class="page-header mb-4" style="position: relative; z-index: 5;">
    <h1 class="page-title">My <span class="grad-text">Messages</span></h1>
    <p class="text-muted">Stay connected with your professional network.</p>
</div>

<div class="inbox-layout" style="position: relative; z-index: 5;">
    <div class="card">
        <?php if (empty($conversations)): ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-comments text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="text-muted">No messages yet. Start a conversation from a profile!</p>
                <a href="network.php" class="btn btn-primary mt-3">Find People</a>
            </div>
        <?php else: ?>
            <div class="inbox-list">
                <?php foreach ($conversations as $conv): ?>
                    <a href="chat.php?to=<?= $conv['id'] ?>" class="inbox-item">
                        <img src="<?= BASE_URL ?>/uploads/avatars/default.svg" class="inbox-avatar">
                        <div class="inbox-info">
                            <div class="inbox-name"><?= htmlspecialchars($conv['name']) ?></div>
                            <div class="inbox-preview"><?= htmlspecialchars($conv['last_message'] ?: 'No message') ?></div>
                        </div>
                        <div class="inbox-meta">
                            <span class="small text-muted"><?= date('M j', strtotime($conv['last_sent'])) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
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
.inbox-preview { font-size: 0.9rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.inbox-meta { text-align: right; }
</style>

<?php require_once 'includes/footer.php'; ?>
