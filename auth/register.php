<?php
/**
 * MarkGigs Registration Page
 * 
 * Handles the creation of new user accounts. It securely hashes passwords 
 * and handles the split logic between "Individual" and "Company" profiles.
 */
require_once '../includes/functions.php';

// Redirect logged-in users away from the registration page
if (is_logged_in()) {
    redirect('index.php');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize basic inputs
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Will be either 'individual' or 'company'
    $name = trim($_POST['name']);

    // Basic validation to ensure no empty fields
    if (empty($email) || empty($password) || empty($name)) {
        $error = "Please fill in all fields.";
    } else {
        // --- STEP 1: Check for existing users ---
        // Ensure the email isn't already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered.";
        } else {
            // --- STEP 2: Database Transaction ---
            // A transaction ensures that if one database query fails, the others are reversed.
            // This prevents a user from being created without a matching profile.
            $pdo->beginTransaction();
            try {
                // Securely hash the password. NEVER store plain-text passwords!
                $hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert the master user record
                $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)");
                $stmt->execute([$email, $hash, $role]);
                $user_id = $pdo->lastInsertId(); // Get the ID of the newly created user

                // Insert into the corresponding profile table based on their selected role
                if ($role === 'individual') {
                    $stmt = $pdo->prepare("INSERT INTO individuals (user_id, full_name) VALUES (?, ?)");
                    $stmt->execute([$user_id, $name]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO companies (user_id, name) VALUES (?, ?)");
                    $stmt->execute([$user_id, $name]);
                }

                // If both queries succeed, commit (save) the transaction to the database
                $pdo->commit();
                
                set_flash("Account created! You can now log in.", "success");
                redirect('auth/login.php');

            } catch (Exception $e) {
                // If anything went wrong (e.g., a database constraint failed), rollback the transaction
                $pdo->rollBack();
                $error = "Registration failed: " . $e->getMessage();
            }
        }
    }
}

$page_title = "Join MarkGigs";
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
        <h2 class="grad-text">Create Account</h2>
        <p class="text-muted mb-4">Join the MarkGigs professional network</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label>I am a...</label>
                <div class="role-selector">
                    <label class="role-card">
                        <input type="radio" name="role" value="individual" checked>
                        <span>Individual (Student/Pro)</span>
                    </label>
                    <label class="role-card">
                        <input type="radio" name="role" value="company" <?= isset($_POST['role']) && $_POST['role'] === 'company' ? 'checked' : '' ?>>
                        <span>Company / Employer</span>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Full Name / Company Name</label>
                <input type="text" name="name" class="form-control" placeholder="Enter name" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Create Account</button>
        </form>

        <p class="mt-4 text-muted">Already have an account? <a href="login.php" class="text-white">Login here</a></p>
    </div>
</section>

<style>
.role-selector { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.role-card { border: 1px solid var(--border-glass); padding: 10px; border-radius: 10px; cursor: pointer; font-size: 0.85rem; display: block; }
.role-card input { display: none; }
.role-card span { opacity: 0.6; }
.role-card input:checked + span { opacity: 1; font-weight: 700; color: var(--accent-primary); }
.alert { padding: 10px; background: rgba(255,107,107,0.1); border: 1px solid var(--accent-warm); border-radius: 8px; color: var(--accent-warm); margin-bottom: 20px; font-size: 0.9rem; }
</style>

<?php require_once '../includes/footer.php'; ?>
