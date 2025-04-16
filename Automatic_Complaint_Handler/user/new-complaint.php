<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();

// Get complaint categories
$categories = $db->query(
    "SELECT cc.id, cc.name, d.name as department 
     FROM complaint_categories cc
     JOIN departments d ON cc.department_id = d.id
     ORDER BY d.name, cc.name"
)->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Title is required';
    }
    
    if (empty($category)) {
        $errors[] = 'Category is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Description is required';
    }
    
    if (empty($errors)) {
        // Insert complaint
        $db->query(
            "INSERT INTO complaints (user_id, category_id, title, description) 
             VALUES (?, ?, ?, ?)",
            [$_SESSION['user_id'], $category, $title, $description]
        );
        
        $_SESSION['success_message'] = 'Complaint filed successfully!';
        header("Location: complaints.php");
        exit();
    }
}

$pageTitle = "File New Complaint";
include '../includes/header.php';
?>

<style>
    :root {
        --primary: #4361ee;
        --secondary: #3a0ca3;
        --accent: #f72585;
        --light: #f8f9fa;
        --dark: #212529;
    }

    body {
        background: linear-gradient(-45deg, #3a0ca3, #4361ee, #4cc9f0, #f72585);
        background-size: 400% 400%;
        min-height: 100vh;
        animation: gradientBG 15s ease infinite;
        color: #fff;
    }

    .new-complaint {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
    }

    .page-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    form {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #fff;
        font-weight: 500;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #fff;
    }

    .form-group select {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
    }

    .form-group select:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .form-group select option {
        background: #4361ee;
        color: white;
        padding: 12px;
    }

    .form-group select optgroup {
        background: #3a0ca3;
        color: white;
        font-weight: 600;
        padding: 8px;
    }

    .form-group select::-ms-expand {
        display: none;
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 30px;
    }

    .form-actions .btn {
        flex: 1;
        padding: 15px;
        font-size: 1.1rem;
        border-radius: 20px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }

    .btn-primary {
        background: rgba(67, 97, 238, 0.3) !important;
    }

    .form-actions .btn:hover {
        transform: translateY(-3px);
        background: rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
</style>

<div class="new-complaint">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> File New Complaint</h1>
        <a href="dashboard.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="title">Complaint Title</label>
            <input type="text" id="title" name="title" placeholder="Brief title of your complaint" required>
        </div>
        
        <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="">Select a category</option>
                <?php 
                $currentDept = '';
                foreach ($categories as $category): 
                    if ($category['department'] != $currentDept) {
                        if ($currentDept != '') echo '</optgroup>';
                        echo '<optgroup label="' . htmlspecialchars($category['department']) . '">';
                        $currentDept = $category['department'];
                    }
                ?>
                    <option value="<?php echo $category['id']; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
                </optgroup>
            </select>
        </div>
        
        <div class="form-group">
            <label for="description">Detailed Description</label>
            <textarea id="description" name="description" rows="6" 
                      placeholder="Describe your complaint in detail..." required></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Complaint
            </button>
            <button type="reset" class="btn btn-outline">
                <i class="fas fa-undo"></i> Reset
            </button>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>