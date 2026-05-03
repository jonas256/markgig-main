<?php
/**
 * MarkGigs PHP Configuration
 */

// Debugging (Set to 0 for production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Base Settings
define('SITE_NAME', 'MarkGigs');
define('BASE_URL', 'http://localhost/markgig-main/markgig-main'); // Root URL for XAMPP

// Database Settings (XAMPP Defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'markgig_db');

// Session Settings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security: CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
