<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireAdmin();

$db = new Database();

// Get admin details
$admin = $db->query(
    "SELECT a.*, d.name as department 
     FROM admins a 
     JOIN departments d ON a.department_id = d.id 
     WHERE a.id = ?",
    [$_SESSION['user_id']]
)->get_result()->fetch_assoc();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (password_verify($currentPassword, $admin['password'])) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE admins SET password = ? WHERE id = ?",
                [$hashedPassword, $_SESSION['user_id']]
            );
            
            $_SESSION['success_message'] = "Password changed successfully!";
        } else {
            $_SESSION['error_message'] = "New passwords do not match";
        }
    } else {
        $_SESSION['error_message'] = "Current password is incorrect";
    }
    
    header("Location: profile.php");
    exit();
}

$pageTitle = "Admin Profile";
include '../includes/header.php';
?>

<div class="admin-profile">
    <div class="page-header">
        <h1><i class="fas fa-user-cog"></i> Admin Profile</h1>
        <a href="dashboard.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-section">
        <div class="profile-info">
            <h2><i class="fas fa-info-circle"></i> Profile Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name:</label>
                    <span><?php echo htmlspecialchars($admin['full_name']); ?></span>
                </div>
                <div class="info-item">
                    <label>Username:</label>
                    <span><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
                <div class="info-item">
                    <label>Department:</label>
                    <span><?php echo htmlspecialchars($admin['department']); ?></span>
                </div>
                <div class="info-item">
                    <label>Account Created:</label>
                    <span><?php echo date('M d, Y', strtotime($admin['created_at'])); ?></span>
                </div>
            </div>
        </div>
        
        <div class="change-password">
            <h2><i class="fas fa-key"></i> Change Password</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fas fa-save"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>