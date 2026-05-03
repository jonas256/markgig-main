<?php
/**
 * MarkGigs Messaging Chat
 */
require_once 'includes/functions.php';
require_login();

$to_user_id = $_GET['to'] ?? null;
if (!$to_user_id) redirect('inbox.php');

$user_id = $_SESSION['user_id'];

// Handle Sending Message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $to_user_id, $content]);
        redirect("chat.php?to=$to_user_id");
    }
}

// Fetch Receiver Info
$stmt = $pdo->prepare("SELECT u.id, COALESCE(i.full_name, c.name) as name 
                       FROM users u 
                       LEFT JOIN individuals i ON i.user_id = u.id 
                       LEFT JOIN companies c ON c.user_id = u.id 
                       WHERE u.id = ?");
$stmt->execute([$to_user_id]);
$receiver = $stmt->fetch();

// Fetch Message Thread
$stmt = $pdo->prepare("SELECT * FROM messages 
                       WHERE (sender_id = ? AND receiver_id = ?) 
                       OR (sender_id = ? AND receiver_id = ?) 
                       ORDER BY sent_at ASC");
$stmt->execute([$user_id, $to_user_id, $to_user_id, $user_id]);
$messages = $stmt->fetchAll();

$page_title = "Chat with " . ($receiver['name'] ?? 'User');
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 100,
        'height' => 100,
        'strokeDasharray' => '2 2',
        'class' => 'mask-radial'
    ]); ?>
</div>

<div class="messages-layout" style="position: relative; z-index: 5;">
    <!-- Sidebar: Threads list (Simplified) -->
    <aside class="messages-sidebar">
        <div class="p-3 border-bottom">
            <h4 class="m-0">Conversations</h4>
        </div>
        <div class="thread-item active">
            <img src="<?= BASE_URL ?>/uploads/avatars/default.svg" class="thread-avatar">
            <div>
                <div class="thread-name"><?= $receiver['name'] ?></div>
                <div class="thread-preview">Active chat...</div>
            </div>
        </div>
        <a href="inbox.php" class="p-3 d-block text-center text-muted small">View all threads</a>
    </aside>

    <!-- Chat Area -->
    <main class="messages-main">
        <div class="thread-header p-3 border-bottom d-flex align-items-center bg-card">
            <h5 class="m-0"><?= $receiver['name'] ?></h5>
        </div>
        
        <div class="message-thread" id="chatThread">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted py-5">
                    Start your conversation with <?= $receiver['name'] ?>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-bubble <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                        <?= htmlspecialchars($msg['content']) ?>
                        <div class="bubble-time"><?= date('H:i', strtotime($msg['sent_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <form action="chat.php?to=<?= $to_user_id ?>" method="POST" class="message-compose">
            <textarea name="content" class="message-input" placeholder="Type a message..." required></textarea>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </main>
</div>

<?php require_once 'includes/footer.php'; ?>
