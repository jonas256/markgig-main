<?php
/**
 * MarkGigs Logout Script
 * 
 * Securely terminates the user's session and redirects them to the login page.
 */
require_once '../includes/functions.php';

// Empty the session array to remove all user data
$_SESSION = [];

// Completely destroy the session on the server
session_destroy();

// Redirect back to the login page
header("Location: " . BASE_URL . "/auth/login.php");
exit; // Stop executing any further code
