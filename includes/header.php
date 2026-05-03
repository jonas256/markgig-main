<?php
/**
 * MarkGigs Main Header
 * 
 * This file contains the HTML `<head>`, navigation bar, and flash message system.
 * It is included on every public and protected page.
 */
require_once __DIR__ . '/functions.php';

// Determine which avatar to display in the navigation bar
$nav_avatar = 'uploads/avatars/default.svg'; // Fallback avatar

if (is_logged_in()) {
    $user_role = $_SESSION['role'];
    
    // Individuals use the `individuals` table and have an `avatar` column
    if ($user_role === 'individual') {
        $stmt = $pdo->prepare("SELECT avatar FROM individuals WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_profile = $stmt->fetch();
        
        if ($user_profile && $user_profile['avatar']) {
            $nav_avatar = 'uploads/avatars/' . $user_profile['avatar'];
        }
    } 
    // Companies use the `companies` table and have a `logo` column
    else {
        $stmt = $pdo->prepare("SELECT logo FROM companies WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_profile = $stmt->fetch();
        
        if ($user_profile && $user_profile['logo']) {
            $nav_avatar = 'uploads/logos/' . $user_profile['logo'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | ' . SITE_NAME : SITE_NAME ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
</head>
<body>

    <!-- Flash Messages -->
    <div class="flash-container">
        <?php $flash = get_flash(); if ($flash): ?>
            <div class="flash flash-<?= $flash['type'] ?>">
                <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'danger' ? 'fa-circle-xmark' : 'fa-circle-info') ?>"></i>
                <?= $flash['message'] ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navbar -->
    <nav class="navbar" id="mainNav">
        <div class="nav-inner">
            <a href="<?= BASE_URL ?>/index.php" class="nav-logo">
                <div class="logo-icon">M</div>
                <span class="grad-text">MarkGigs</span>
            </a>

            <?php if (is_logged_in()): ?>
                <div class="nav-search">
                    <form action="<?= BASE_URL ?>/search.php" method="GET">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" name="q" placeholder="Search people, companies, gigs...">
                    </form>
                </div>

                <div class="nav-links">
                    <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $active_page === 'feed' ? 'active' : '' ?>">
                        <i class="fa-solid fa-house"></i>
                        <span>Feed</span>
                    </a>
                    <a href="<?= BASE_URL ?>/network.php" class="nav-link <?= $active_page === 'network' ? 'active' : '' ?>">
                        <i class="fa-solid fa-user-group"></i>
                        <span>Network</span>
                    </a>
                    <a href="<?= BASE_URL ?>/jobs.php" class="nav-link <?= $active_page === 'jobs' ? 'active' : '' ?>">
                        <i class="fa-solid fa-briefcase"></i>
                        <span>Jobs</span>
                    </a>
                    <a href="<?= BASE_URL ?>/marketplace.php" class="nav-link <?= $active_page === 'marketplace' ? 'active' : '' ?>">
                        <i class="fa-solid fa-store"></i>
                        <span>Marketplace</span>
                    </a>
                    <a href="<?= BASE_URL ?>/mentors.php" class="nav-link <?= $active_page === 'mentorship' ? 'active' : '' ?>">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Mentors</span>
                    </a>
                    <a href="<?= BASE_URL ?>/inbox.php" class="nav-link <?= $active_page === 'messages' ? 'active' : '' ?>">
                        <i class="fa-solid fa-message"></i>
                        <span>Messages</span>
                    </a>
                    
                    <div class="nav-avatar-wrap" id="avatarDropdownToggle">
                        <img src="<?= BASE_URL ?>/<?= $nav_avatar ?>" alt="Avatar" class="nav-avatar">
                        <div class="dropdown-menu" id="avatarDropdown">
                            <a href="<?= BASE_URL ?>/profile.php">My Profile</a>
                            <?php if (has_role('admin')): ?>
                                <a href="<?= BASE_URL ?>/admin.php">Admin Panel</a>
                            <?php endif; ?>
                            <hr>
                            <a href="<?= BASE_URL ?>/auth/logout.php" class="text-danger">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="nav-links">
                    <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-ghost">Login</a>
                    <a href="<?= BASE_URL ?>/auth/register.php" class="btn btn-primary">Join Now</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>

    <main class="container">
