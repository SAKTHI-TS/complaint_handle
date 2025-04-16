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

    .btn {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 12px 25px;
        font-size: 1.1rem;
        border-radius: 20px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-outline {
        background: rgba(255, 255, 255, 0.1);
    }

    .form-group textarea {
        width: 100%;
        padding: 15px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        color: #000;
        font-size: 1.1rem;
        line-height: 1.6;
        min-height: 150px;
        resize: vertical;
        font-family: inherit;
    }

    .form-group textarea::placeholder {
        color: rgba(0, 0, 0, 0.5);
    }

    .form-group textarea:focus {
        outline: none;
        border-color: rgba(67, 97, 238, 0.5);
        box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.1);
        background: rgba(255, 255, 255, 0.95);
    }
</style>

<div class="new-complaint">
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

<?php include '../includes/footer.php'; ?>