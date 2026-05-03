<?php
/**
 * MarkGigs Edit Profile
 * Allows users to update their profile details and branding.
 */
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch current profile data
if ($role === 'individual') {
    $stmt = $pdo->prepare("SELECT * FROM individuals WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
    $display_name = $profile['full_name'];
    $avatar_path = $profile['avatar'] ? 'uploads/avatars/' . $profile['avatar'] : 'uploads/avatars/default.svg';
} else {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
    $display_name = $profile['name'];
    $avatar_path = $profile['logo'] ? 'uploads/logos/' . $profile['logo'] : 'uploads/logos/default.svg';
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $errors = [];
    
    // Common Image Upload Logic
    $image_name = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Invalid file type. Only JPG, PNG, GIF, and SVG are allowed.";
        } elseif ($file['size'] > $max_size) {
            $errors[] = "File size too large. Max 5MB allowed.";
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_name = uniqid('profile_') . '.' . $ext;
            $upload_dir = ($role === 'individual') ? 'uploads/avatars/' : 'uploads/logos/';
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_name)) {
                $image_name = $new_name;
            } else {
                $errors[] = "Failed to upload image.";
            }
        }
    }

    if (empty($errors)) {
        if ($role === 'individual') {
            $full_name = trim($_POST['full_name']);
            $headline = trim($_POST['headline']);
            $bio = trim($_POST['bio']);
            $institution = trim($_POST['institution']);
            $course = trim($_POST['course']);
            $grad_year = !empty($_POST['graduation_year']) ? $_POST['graduation_year'] : null;
            $location = trim($_POST['location']);
            $website = trim($_POST['website']);
            $skills = trim($_POST['skills']);
            $ach_academic = trim($_POST['achievements_academic'] ?? '');
            $ach_field = trim($_POST['achievements_field'] ?? '');
            $research = trim($_POST['research_implementations'] ?? '');

            $sql = "UPDATE individuals SET 
                    full_name = ?, headline = ?, bio = ?, institution = ?, 
                    course = ?, graduation_year = ?, location = ?, 
                    website = ?, skills = ?, achievements_academic = ?, 
                    achievements_field = ?, research_implementations = ?";
            $params = [$full_name, $headline, $bio, $institution, $course, $grad_year, $location, $website, $skills, $ach_academic, $ach_field, $research];
            
            if ($image_name) {
                $sql .= ", avatar = ?";
                $params[] = $image_name;
            }
            
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
        } else {
            $name = trim($_POST['name']);
            $industry = trim($_POST['industry']);
            $description = trim($_POST['description']);
            $location = trim($_POST['location']);
            $website = trim($_POST['website']);

            $sql = "UPDATE companies SET 
                    name = ?, industry = ?, description = ?, 
                    location = ?, website = ?";
            $params = [$name, $industry, $description, $location, $website];
            
            if ($image_name) {
                $sql .= ", logo = ?";
                $params[] = $image_name;
            }
            
            $sql .= " WHERE user_id = ?";
            $params[] = $user_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        set_flash("Profile updated successfully!", "success");
        redirect('profile.php');
    } else {
        foreach ($errors as $error) set_flash($error, "danger");
    }
}

$page_title = "Edit Profile";
require_once 'includes/header.php';
require_once 'includes/components/grid_pattern.php';
?>

<style>
/* Edit Profile Specific Styles */
.edit-container {
    max-width: 800px;
    margin: 2rem auto;
}

.image-preview-wrap {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 16px;
    border: 1px dashed var(--border-glass);
}

.preview-img {
    width: 100px;
    height: 100px;
    border-radius: 16px;
    object-fit: cover;
    border: 2px solid var(--accent-primary);
}

.image-upload-btn {
    position: relative;
    overflow: hidden;
}

.image-upload-btn input {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

/* Skills Tag Input Styles */
.skills-input-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 8px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
    min-height: 48px;
    cursor: text;
}

.skill-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: rgba(108, 99, 255, 0.2);
    color: var(--accent-secondary);
    border: 1px solid rgba(108, 99, 255, 0.3);
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 500;
    animation: tagFadeIn 0.2s ease;
}

@keyframes tagFadeIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.skill-tag i {
    cursor: pointer;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.skill-tag i:hover { color: var(--accent-warm); }

.skills-input {
    border: none;
    background: transparent;
    color: white;
    outline: none;
    flex: 1;
    min-width: 120px;
    padding: 4px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-bg-grid">
    <?php render_grid_pattern([
        'width' => 50,
        'height' => 50,
        'strokeDasharray' => '1 3',
        'class' => 'mask-radial'
    ]); ?>
</div>

<div class="edit-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title mb-0">Edit <span class="grad-text">Profile</span></h1>
        <a href="profile.php" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
    </div>

    <form action="edit_profile.php" method="POST" enctype="multipart/form-data" id="editProfileForm">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        
        <div class="card mb-4">
            <h4 class="mb-4">Branding & Identity</h4>
            
            <div class="image-preview-wrap">
                <img src="<?= BASE_URL ?>/<?= $avatar_path ?>" class="preview-img" id="imgPreview">
                <div>
                    <div class="btn btn-glass image-upload-btn">
                        <i class="fa-solid fa-camera"></i> Change <?= ($role === 'individual') ? 'Avatar' : 'Logo' ?>
                        <input type="file" name="profile_image" id="profileImageInput" accept="image/*">
                    </div>
                    <p class="text-muted small mt-2">Recommended: Square image, max 5MB.</p>
                </div>
            </div>

            <?php if ($role === 'individual'): ?>
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($profile['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Headline</label>
                    <input type="text" name="headline" class="form-control" placeholder="e.g. Software Engineering Student | Aspiring Web Developer" value="<?= htmlspecialchars($profile['headline'] ?? '') ?>">
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($profile['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Industry</label>
                    <input type="text" name="industry" class="form-control" placeholder="e.g. Technology, Finance, Education" value="<?= htmlspecialchars($profile['industry'] ?? '') ?>">
                </div>
            <?php endif; ?>
        </div>

        <div class="card mb-4">
            <h4 class="mb-4">About & Details</h4>
            
            <div class="form-group">
                <label><?= ($role === 'individual') ? 'Personal Bio' : 'Company Description' ?></label>
                <textarea name="<?= ($role === 'individual') ? 'bio' : 'description' ?>" class="form-control" rows="5" placeholder="Tell the community about yourself or your company..."><?= htmlspecialchars($role === 'individual' ? $profile['bio'] : $profile['description']) ?></textarea>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" class="form-control" placeholder="e.g. Kampala, Uganda" value="<?= htmlspecialchars($profile['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Website</label>
                    <input type="url" name="website" class="form-control" placeholder="https://..." value="<?= htmlspecialchars($profile['website'] ?? '') ?>">
                </div>
            </div>

            <?php if ($role === 'individual'): ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Institution</label>
                        <input type="text" name="institution" class="form-control" placeholder="e.g. Makerere University" value="<?= htmlspecialchars($profile['institution'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Course</label>
                        <input type="text" name="course" class="form-control" placeholder="e.g. BSc. Software Engineering" value="<?= htmlspecialchars($profile['course'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Graduation Year</label>
                    <input type="number" name="graduation_year" class="form-control" min="1900" max="2100" value="<?= htmlspecialchars($profile['graduation_year'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Skills (Press Enter to add)</label>
                    <div class="skills-input-container" id="skillsContainer">
                        <!-- Tags will be rendered here -->
                        <input type="text" class="skills-input" id="skillsInput" placeholder="Add a skill...">
                    </div>
                    <input type="hidden" name="skills" id="skillsHidden" value="<?= htmlspecialchars($profile['skills'] ?? '') ?>">
                </div>

                <hr class="card-divider">
                <h4 class="mb-4 mt-4">Achievements & Research</h4>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-graduation-cap small me-2"></i> Academic Achievements</label>
                    <textarea name="achievements_academic" class="form-control" rows="3" placeholder="e.g. Dean's List 2023, Best in Mathematics..."><?= htmlspecialchars($profile['achievements_academic'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-trophy small me-2"></i> Field Achievements</label>
                    <textarea name="achievements_field" class="form-control" rows="3" placeholder="e.g. Awarded Best Junior Developer at Hackathon..."><?= htmlspecialchars($profile['achievements_field'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fa-solid fa-microscope small me-1"></i> Research & Projects</label>
                    <textarea name="research_implementations" class="form-control" rows="3" placeholder="e.g. AI-driven crop monitoring system..."><?= htmlspecialchars($profile['research_implementations'] ?? '') ?></textarea>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">Save Changes</button>
            <a href="profile.php" class="btn btn-glass btn-lg px-4">Cancel</a>
        </div>
    </form>
</div>

<script>
// Image Preview
document.getElementById('profileImageInput').addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(this.files[0]);
    }
});

// Skills Tag System
const skillsContainer = document.getElementById('skillsContainer');
const skillsInput = document.getElementById('skillsInput');
const skillsHidden = document.getElementById('skillsHidden');

let skills = skillsHidden.value ? skillsHidden.value.split(',').map(s => s.trim()).filter(s => s !== "") : [];

function renderTags() {
    // Keep the input
    const input = skillsInput;
    skillsContainer.innerHTML = '';
    
    skills.forEach((skill, index) => {
        const tag = document.createElement('span');
        tag.className = 'skill-tag';
        tag.innerHTML = `${skill} <i class="fa-solid fa-xmark" onclick="removeSkill(${index})"></i>`;
        skillsContainer.appendChild(tag);
    });
    
    skillsContainer.appendChild(input);
    skillsHidden.value = skills.join(',');
    input.focus();
}

function removeSkill(index) {
    skills.splice(index, 1);
    renderTags();
}

skillsInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const value = this.value.trim();
        if (value && !skills.includes(value)) {
            skills.push(value);
            this.value = '';
            renderTags();
        }
    } else if (e.key === 'Backspace' && this.value === '' && skills.length > 0) {
        skills.pop();
        renderTags();
    }
});

// Focus input when clicking container
skillsContainer.addEventListener('click', () => {
    skillsInput.focus();
});

// Initial render
if (skills.length > 0) {
    renderTags();
}
</script>

<?php require_once 'includes/footer.php'; ?>
