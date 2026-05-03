<?php
/**
 * MarkGigs Shared Functions
 * 
 * This file contains utility functions that are reused throughout the entire application.
 * It includes session management, security checks, and common database queries.
 */
require_once 'db.php'; // Ensures the database connection is available whenever functions are used

/**
 * Checks if the current user has an active session.
 * 
 * @return bool True if logged in, false otherwise.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Restricts page access to logged-in users only.
 * If the user is not logged in, they are immediately redirected to the login page.
 * Call this at the very top of any protected page (like profile.php or dashboard.php).
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "/auth/login.php");
        exit; // Always exit after a header redirect to stop executing the rest of the page
    }
}

/**
 * Checks if the logged-in user has a specific role.
 * 
 * @param string $role The role to check (e.g., 'admin', 'company', 'individual').
 * @return bool True if the user has the role, false otherwise.
 */
function has_role($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Verifies a Cross-Site Request Forgery (CSRF) token.
 * This ensures that form submissions are coming directly from our own website,
 * preventing malicious sites from submitting data on behalf of our users.
 * 
 * @param string $token The token submitted via the form.
 * @return bool True if the token matches the session token.
 */
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Utility function to quickly redirect users to another page within the application.
 * 
 * @param string $path The relative path to redirect to (e.g., 'index.php').
 */
function redirect($path) {
    header("Location: " . BASE_URL . "/" . ltrim($path, '/'));
    exit;
}

/**
 * Stores a temporary "flash" message in the session.
 * Flash messages are used to display success/error alerts on the NEXT page the user visits.
 * 
 * @param string $message The text to display.
 * @param string $type The alert type (e.g., 'success', 'danger', 'info').
 */
function set_flash($message, $type = 'info') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

/**
 * Retrieves the flash message and deletes it from the session.
 * This ensures the message is only shown once.
 * 
 * @return array|null Returns the message array or null if none exists.
 */
function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // Delete it so it doesn't show up again
        return $flash;
    }
    return null;
}

/**
 * Fetches the correct display name for a user based on their account type.
 * Individuals use 'full_name' and Companies use 'name'.
 * 
 * @param int $user_id The ID of the user.
 * @return string The display name, or 'User' as a fallback.
 */
function get_display_name($user_id) {
    global $pdo;
    
    // Use COALESCE to pick the first non-null value from the joined tables
    $stmt = $pdo->prepare("
        SELECT COALESCE(i.full_name, c.name) as display_name 
        FROM users u 
        LEFT JOIN individuals i ON i.user_id = u.id 
        LEFT JOIN companies c ON c.user_id = u.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $res = $stmt->fetch();
    
    return $res ? $res['display_name'] : 'User';
}

/**
 * Utility function to send emails.
 * 
 * @param string $to Recipient email address.
 * @param string $subject Email subject.
 * @param string $body Email content.
 * @return bool True if accepted for delivery, false otherwise.
 */
function send_email($to, $subject, $body) {
    // Note: In a production environment, this should be upgraded to use PHPMailer or an SMTP service.
    return mail($to, $subject, $body);
}
