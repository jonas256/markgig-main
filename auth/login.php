<?php
/**
 * MarkGigs Login Page
 * 
 * Handles user authentication by verifying the provided email and password
 * against the database. If successful, it creates a secure session.
 */
require_once '../includes/functions.php';

// If the user is already logged in, redirect them to the feed so they don't see the login form again.
if (is_logged_in()) {
    redirect('index.php');
}

$error = null; // Variable to hold login error messages

// Check if the form was submitted (method is POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize user input by trimming whitespace from the email
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Query the database to find a user with this email
    // We use a prepared statement (?) to prevent SQL injection attacks
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(); // Fetch the user record if it exists

    // Verify the user exists AND the password matches the hashed password in the database
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // --- LOGIN SUCCESS ---
        // Store essential user data in the secure server-side session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // e.g. 'individual', 'company', or 'admin'
        $_SESSION['email'] = $user['email'];
        
        // Show a friendly welcome message on the next page
        set_flash("Welcome back!", "success");
        redirect('index.php'); // Send them to the main feed
        
    } else {
        // --- LOGIN FAILED ---
        // We use a generic error message so we don't accidentally reveal if an email exists
        $error = "Invalid email or password.";
    }
}

$page_title = "Login";
require_once '../includes/header.php';
require_once '../includes/components/grid_pattern.php';
?>

<section class="auth-page" style="position: relative; overflow: hidden;">
    <div class="auth-grid-wrapper">
        <?php render_grid_pattern([
            'width' => 30,
            'height' => 30,
            'strokeDasharray' => '4 2',
            'class' => 'mask-radial'
        ]); ?>
    </div>
    <div class="auth-box" style="position: relative; z-index: 10;">
        <h2 class="grad-text">Welcome Back</h2>
        <p class="text-muted mb-4">Log in to your MarkGigs portal</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <p class="mt-4 text-muted">Don't have an account? <a href="register.php" class="text-white">Join Now</a></p>
    </div>
</section>

<style>
.alert { padding: 10px; background: rgba(255,107,107,0.1); border: 1px solid var(--accent-warm); border-radius: 8px; color: var(--accent-warm); margin-bottom: 20px; font-size: 0.9rem; }
</style>

<?php require_once '../includes/footer.php'; ?>
