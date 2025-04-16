<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();

// Get user details
$user = $db->query(
    "SELECT * FROM users WHERE id = ?",
    [$_SESSION['user_id']]
)->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    $db->query(
        "UPDATE users 
         SET first_name = ?, last_name = ?, email = ?, phone = ?
         WHERE id = ?",
        [$firstName, $lastName, $email, $phone, $_SESSION['user_id']]
    );
    
    $_SESSION['full_name'] = $firstName . ' ' . $lastName;
    $_SESSION['success_message'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit();
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (password_verify($currentPassword, $user['password'])) {
        if ($newPassword === $confirmPassword) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->query(
                "UPDATE users SET password = ? WHERE id = ?",
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

$pageTitle = "User Profile";
include '../includes/header.php';
?>

<div class="user-profile">
    <div class="page-header">
        <h1><i class="fas fa-user"></i> My Profile</h1>
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
            <form method="POST" action="">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
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
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>