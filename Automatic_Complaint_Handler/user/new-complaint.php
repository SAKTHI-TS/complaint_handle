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