<?php
require_once '../config.php';
require_once '../auth.php';
require_once '../services/ComplaintClassifier.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();
$pageTitle = "Submit New Complaint";
include '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Submit New Complaint</h1>
        <a href="complaints.php" class="btn btn-outline">Back to Complaints</a>
    </div>

    <div class="complaint-form">
        <form id="complaintForm" method="POST">
            <div class="form-group">
                <label for="description">Describe your complaint:</label>
                <textarea 
                    id="description"
                    name="description" 
                    class="form-control"
                    rows="6"
                    required
                    placeholder="Please describe your complaint in detail..."
                ></textarea>
            </div>
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>
        <div id="errorMessage" class="alert alert-danger" style="display:none;"></div>
        <div id="successMessage" class="alert alert-success" style="display:none;"></div>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $description = $_POST['description'];
        $user_id = $_SESSION['user_id'];

        // Use the classifier to determine department
        $classifier = new ComplaintClassifier();
        $department = $classifier->classify($description);
        
        // Get category ID for the department using proper method
        $result = $db->query(
            "SELECT id FROM complaint_categories WHERE department_id = (
                SELECT id FROM departments WHERE name = ?
            ) LIMIT 1",
            [$department]
        )->get_result();
        
        $category = $result->fetch_assoc();
        
        if (!$category) {
            throw new Exception("Could not determine appropriate department");
        }

        // Create a title from the first 50 characters of description
        $title = substr($description, 0, 50) . (strlen($description) > 50 ? '...' : '');

        // Insert complaint
        $stmt = $db->query(
            "INSERT INTO complaints (user_id, category_id, title, description, status) 
             VALUES (?, ?, ?, ?, 'pending')",
            [$user_id, $category['id'], $title, $description]
        );

        if ($stmt) {
            echo "<script>
                document.getElementById('successMessage').textContent = 'Complaint submitted successfully to " . htmlspecialchars($department) . " Department';
                document.getElementById('successMessage').style.display = 'block';
                setTimeout(() => { window.location.href = 'complaints.php'; }, 2000);
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>
            document.getElementById('errorMessage').textContent = 'Error: " . htmlspecialchars($e->getMessage()) . "';
            document.getElementById('errorMessage').style.display = 'block';
        </script>";
    }
}
?>

<style>
.complaint-form {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.alert {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 4px;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-control {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<?php include '../includes/footer.php'; ?>