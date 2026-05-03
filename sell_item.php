<?php
require_once __DIR__ . '/includes/functions.php';

// Require login
require_login();

$page_title = "Sell an Item";
$active_page = "marketplace";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = (float)$_POST['price'];
    $category = $_POST['category'];
    $seller_id = $_SESSION['user_id'];
    
    // Validate
    if (empty($title) || $price <= 0 || empty($category)) {
        set_flash('danger', 'Title, category, and a valid price are required.');
    } else {
        // Handle image upload
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/items/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_extension, $allowed_extensions)) {
                $image_name = uniqid('item_') . '.' . $file_extension;
                $destination = $upload_dir . $image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $image_url = $image_name;
                } else {
                    set_flash('danger', 'Failed to upload image.');
                }
            } else {
                set_flash('danger', 'Invalid image format. Only JPG, PNG, and WEBP are allowed.');
            }
        }
        
        if (!isset($_SESSION['flash'])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO items (seller_id, title, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$seller_id, $title, $description, $price, $category, $image_url]);
                
                set_flash('success', 'Item listed successfully!');
                redirect('marketplace.php');
            } catch (PDOException $e) {
                set_flash('danger', 'Database error: ' . $e->getMessage());
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="form-container" style="max-width: 600px; margin: 0 auto; background: var(--bg-surface); padding: 2rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
    <h2 style="margin-bottom: 1.5rem;">Sell an Item</h2>
    
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="title" class="form-label">Item Title <span class="text-danger">*</span></label>
            <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Scientific Calculator">
        </div>
        
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="price" class="form-label">Price (KSh) <span class="text-danger">*</span></label>
            <input type="number" id="price" name="price" class="form-control" required min="1" step="0.01" placeholder="e.g. 1500">
        </div>
        
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <select id="category" name="category" class="form-control" required>
                <option value="">Select Category</option>
                <option value="Electronics">Electronics</option>
                <option value="Books">Books</option>
                <option value="Clothing">Clothing</option>
                <option value="Services">Services</option>
                <option value="Other">Other</option>
            </select>
        </div>
        
        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5" placeholder="Describe your item..."></textarea>
        </div>
        
        <div class="form-group" style="margin-bottom: 1.5rem;">
            <label for="image" class="form-label">Item Image</label>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
            <small class="text-muted">Upload a clear photo of your item (JPG, PNG, WEBP).</small>
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <button type="submit" class="btn btn-primary" style="flex: 1;">List Item</button>
            <a href="<?= BASE_URL ?>/marketplace.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
