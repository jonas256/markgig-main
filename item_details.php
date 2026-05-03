<?php
require_once __DIR__ . '/includes/functions.php';

// Require login
require_login();

$item_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$item_id) {
    set_flash('danger', 'Invalid item ID.');
    redirect('marketplace.php');
}

$sql = "SELECT i.*, 
        COALESCE(ind.full_name, c.name) as seller_name,
        COALESCE(ind.avatar, c.logo) as seller_avatar,
        u.role as seller_role,
        u.id as seller_user_id
        FROM items i
        JOIN users u ON i.seller_id = u.id
        LEFT JOIN individuals ind ON u.id = ind.user_id AND u.role = 'individual'
        LEFT JOIN companies c ON u.id = c.user_id AND u.role = 'company'
        WHERE i.id = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$item_id]);
$item = $stmt->fetch();

if (!$item) {
    set_flash('danger', 'Item not found.');
    redirect('marketplace.php');
}

$page_title = $item['title'];
$active_page = "marketplace";

require_once __DIR__ . '/includes/header.php';
?>

<div class="item-details-container" style="max-width: 900px; margin: 0 auto;">
    <a href="<?= BASE_URL ?>/marketplace.php" class="btn btn-ghost" style="margin-bottom: 1rem;"><i class="fa-solid fa-arrow-left"></i> Back to Marketplace</a>

    <div class="card" style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; padding: 2rem;">
        <div class="item-images">
            <?php if ($item['image_url']): ?>
                <img src="<?= BASE_URL ?>/uploads/items/<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="width: 100%; border-radius: var(--radius-md); box-shadow: var(--shadow-sm);">
            <?php else: ?>
                <div style="width: 100%; aspect-ratio: 4/3; background-color: var(--bg-body); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                    <i class="fa-solid fa-image fa-5x" style="color: var(--text-muted);"></i>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="item-info">
            <span class="badge" style="background: var(--primary-light); color: var(--primary-color); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.9rem; margin-bottom: 1rem; display: inline-block;">
                <?= htmlspecialchars($item['category']) ?>
            </span>
            
            <h1 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($item['title']) ?></h1>
            <h2 style="color: var(--success-color); margin-bottom: 1.5rem;">KSh <?= number_format($item['price'], 2) ?></h2>
            
            <div style="margin-bottom: 2rem;">
                <h4 style="margin-bottom: 0.5rem;">Description</h4>
                <p style="white-space: pre-wrap; color: var(--text-color);"><?= htmlspecialchars($item['description'] ?: 'No description provided.') ?></p>
            </div>
            
            <div class="seller-card" style="background: var(--bg-body); padding: 1rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <?php
                    $avatar_dir = $item['seller_role'] === 'company' ? 'logos' : 'avatars';
                    $avatar_src = $item['seller_avatar'] ? "uploads/{$avatar_dir}/{$item['seller_avatar']}" : 'uploads/avatars/default.svg';
                ?>
                <img src="<?= BASE_URL ?>/<?= $avatar_src ?>" alt="Seller" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                <div style="flex: 1;">
                    <h4 style="margin: 0;"><?= htmlspecialchars($item['seller_name']) ?></h4>
                    <span class="text-muted" style="font-size: 0.9rem;">Seller • Joined <?= date('M Y', strtotime($item['created_at'])) ?></span>
                </div>
            </div>
            
            <div class="actions" style="display: flex; gap: 1rem;">
                <?php if ($item['seller_user_id'] != $_SESSION['user_id']): ?>
                    <a href="<?= BASE_URL ?>/chat.php?user=<?= $item['seller_user_id'] ?>" class="btn btn-primary" style="flex: 1; text-align: center;">
                        <i class="fa-solid fa-message"></i> Message Seller
                    </a>
                <?php else: ?>
                    <button class="btn btn-outline" style="flex: 1; text-align: center;" disabled>This is your item</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
