<?php
/**
 * MarkGigs Application Processor
 */
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opportunity_id = $_POST['opportunity_id'];
    $cover_letter = trim($_POST['cover_letter']);
    
    // Get Individual ID
    $stmt = $pdo->prepare("SELECT id FROM individuals WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $individual_id = $stmt->fetchColumn();

    if (!$individual_id) {
        set_flash("Only individual profiles can apply.", "danger");
        redirect('jobs.php');
    }

    // Handle File Upload
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/resumes/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('resume_') . '.' . $file_ext;
        $target = $upload_dir . $filename;
        
        if (move_uploaded_file($_FILES['resume']['tmp_name'], $target)) {
            $resume_path = $target;
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO applications (individual_id, opportunity_id, cover_letter, resume_path) VALUES (?, ?, ?, ?)");
        $stmt->execute([$individual_id, $opportunity_id, $cover_letter, $resume_path]);
        
        set_flash("Application submitted successfully!", "success");
        redirect('jobs.php');
    } catch (Exception $e) {
        set_flash("Failed to apply: " . $e->getMessage(), "danger");
        redirect("job_details.php?id=$opportunity_id");
    }
}
