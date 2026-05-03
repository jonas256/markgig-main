<?php
require_once __DIR__ . '/includes/functions.php';

// Require login
require_login();

$page_title = "Marketplace";
$active_page = "marketplace";

// Handle filtering
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

$params = [];
$sql = "SELECT i.*, 
        COALESCE(ind.full_name, c.name) as seller_name,
        COALESCE(ind.avatar, c.logo) as seller_avatar,
        u.role as seller_role
        FROM items i
        JOIN users u ON i.seller_id = u.id
        LEFT JOIN individuals ind ON u.id = ind.user_id AND u.role = 'individual'
        LEFT JOIN companies c ON u.id = c.user_id AND u.role = 'company'
        WHERE i.status = 'active'";

if ($category_filter) {
    $sql .= " AND i.category = ?";
    $params[] = $category_filter;
}

if ($search_query) {
    $sql .= " AND (i.title LIKE ? OR i.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
}

$sql .= " ORDER BY i.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="marketplace-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1>Campus Marketplace</h1>
        <p class="text-muted">Buy and sell products within the campus community.</p>
    </div>
    <a href="<?= BASE_URL ?>/sell_item.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Sell an Item</a>
</div>

<div class="marketplace-filters" style="margin-bottom: 2rem; display: flex; gap: 1rem;">
    <form action="" method="GET" style="display: flex; gap: 1rem; flex: 1;">
        <div style="flex: 1; max-width: 300px;">
            <input type="text" name="q" class="form-control" placeholder="Search items..." value="<?= htmlspecialchars($search_query) ?>">
        </div>
        <div>
            <select name="category" class="form-control">
                <option value="">All Categories</option>
                <option value="Electronics" <?= $category_filter === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                <option value="Books" <?= $category_filter === 'Books' ? 'selected' : '' ?>>Books</option>
                <option value="Clothing" <?= $category_filter === 'Clothing' ? 'selected' : '' ?>>Clothing</option>
                <option value="Services" <?= $category_filter === 'Services' ? 'selected' : '' ?>>Services</option>
                <option value="Other" <?= $category_filter === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
        <?php if ($category_filter || $search_query): ?>
            <a href="marketplace.php" class="btn btn-ghost">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
    <?php if (empty($items)): ?>
        <div class="empty-state" style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: var(--bg-surface); border-radius: var(--radius-lg);">
            <i class="fa-solid fa-store-slash fa-3x" style="color: var(--text-muted); margin-bottom: 1rem;"></i>
            <h3>No items found</h3>
            <p class="text-muted">There are currently no items matching your criteria.</p>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <div class="card item-card" style="display: flex; flex-direction: column; overflow: hidden;">
                <?php if ($item['image_url']): ?>
                    <div class="item-image" style="height: 200px; background-image: url('<?= BASE_URL ?>/uploads/items/<?= htmlspecialchars($item['image_url']) ?>'); background-size: cover; background-position: center;"></div>
                <?php else: ?>
                    <div class="item-image" style="height: 200px; background-color: var(--bg-body); display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-image fa-3x" style="color: var(--text-muted);"></i>
                    </div>
                <?php endif; ?>
                
                <div class="card-body" style="flex: 1; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                        <span class="badge" style="background: var(--primary-light); color: var(--primary-color); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem;"><?= htmlspecialchars($item['category']) ?></span>
                        <span style="font-weight: bold; font-size: 1.25rem; color: var(--success-color);">KSh <?= number_format($item['price'], 2) ?></span>
                    </div>
                    
                    <h3 style="margin-bottom: 0.5rem; font-size: 1.1rem;"><?= htmlspecialchars($item['title']) ?></h3>
                    
                    <div style="display: flex; align-items: center; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <?php
                            $avatar_dir = $item['seller_role'] === 'company' ? 'logos' : 'avatars';
                            $avatar_src = $item['seller_avatar'] ? "uploads/{$avatar_dir}/{$item['seller_avatar']}" : 'uploads/avatars/default.svg';
                        ?>
                        <img src="<?= BASE_URL ?>/<?= $avatar_src ?>" alt="Seller" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 0.5rem;">
                        <span class="text-muted" style="font-size: 0.9rem;"><?= htmlspecialchars($item['seller_name']) ?></span>
                    </div>
                </div>
                <div class="card-footer" style="padding: 1rem; border-top: 1px solid var(--border-color); display: flex; gap: 0.5rem;">
                    <a href="<?= BASE_URL ?>/item_details.php?id=<?= $item['id'] ?>" class="btn btn-outline" style="flex: 1; text-align: center;">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
