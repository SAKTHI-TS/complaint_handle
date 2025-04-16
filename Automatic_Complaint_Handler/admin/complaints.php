<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireAdmin();

$db = new Database();
$department = $auth::getDepartment();

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $complaintId = $_GET['id'];
    $action = $_GET['action'];
    
    // Validate complaint belongs to admin's department
    $validComplaint = $db->query(
        "SELECT c.id 
         FROM complaints c
         JOIN complaint_categories cc ON c.category_id = cc.id
         JOIN departments d ON cc.department_id = d.id
         WHERE d.name = ? AND c.id = ?",
        [$department, $complaintId]
    )->get_result()->fetch_assoc();
    
    if ($validComplaint) {
        switch ($action) {
            case 'view':
                // View logic here
                break;
            case 'update_status':
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                    $newStatus = $_POST['status'];
                    $remarks = $_POST['remarks'];
                    
                    $db->query(
                        "UPDATE complaints 
                         SET status = ?, updated_at = NOW(), assigned_to = ?
                         WHERE id = ?",
                        [$newStatus, $_SESSION['user_id'], $complaintId]
                    );
                    
                    // Add to complaint history
                    $db->query(
                        "INSERT INTO complaint_history (complaint_id, status, remarks, updated_by)
                         VALUES (?, ?, ?, ?)",
                        [$complaintId, $newStatus, $remarks, $_SESSION['user_id']]
                    );
                    
                    $_SESSION['success_message'] = "Complaint status updated successfully!";
                    header("Location: complaints.php");
                    exit();
                }
                break;
        }
    }
}

// Get all complaints for the department with additional details
$complaints = $db->query(
    "SELECT 
        c.*, 
        u.first_name, 
        u.last_name, 
        cc.name as category_name,
        d.name as department_name
     FROM complaints c
     JOIN users u ON c.user_id = u.id
     JOIN complaint_categories cc ON c.category_id = cc.id
     JOIN departments d ON cc.department_id = d.id
     WHERE d.name = ? AND cc.department_id = (
         SELECT department_id 
         FROM admins 
         WHERE id = ?
     )
     ORDER BY c.created_at DESC",
    [$department, $_SESSION['user_id']]
)->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Manage Complaints";
include '../includes/header.php';
?>

<div class="admin-complaints">
    <div class="page-header">
        <h1><i class="fas fa-tasks"></i> Manage <?php echo htmlspecialchars($department); ?> Department Complaints</h1>
        <a href="dashboard.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <div class="complaints-list">
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Submitted By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo str_replace('_', '-', $complaint['status']); ?>">
                            <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info" data-description="<?php echo htmlspecialchars($complaint['description']); ?>">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn btn-sm btn-primary update-status-btn" data-id="<?php echo $complaint['id']; ?>">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Complaint Status</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="statusForm" method="POST" action="complaints.php?action=update_status&id=">
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea id="remarks" name="remarks" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complaint Details Modal -->
<div class="modal" id="complaintModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Complaint Details</h3>
            <span class="close-modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="complaint-details">
                <div class="detail-row">
                    <strong>Title:</strong>
                    <span id="modal-title"></span>
                </div>
                <div class="detail-row">
                    <strong>Category:</strong>
                    <span id="modal-category"></span>
                </div>
                <div class="detail-row">
                    <strong>Description:</strong>
                    <p id="modal-description"></p>
                </div>
                <div class="detail-row">
                    <strong>Status:</strong>
                    <span id="modal-status"></span>
                </div>
                <div class="detail-row">
                    <strong>Submitted By:</strong>
                    <span id="modal-user"></span>
                </div>
                <div class="detail-row">
                    <strong>Date:</strong>
                    <span id="modal-date"></span>
                </div>
            </div>
        </div>
    </div>
</div>

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
    }

    .admin-complaints {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 30px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .complaints-list {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 1000;
    }

    .modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        padding: 30px;
        border-radius: 20px;
        width: 90%;
        max-width: 800px;
        color: white;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal-header h3 {
        font-size: 2rem;
        margin: 0;
    }

    .close-modal {
        cursor: pointer;
        font-size: 32px;
        color: rgba(255, 255, 255, 0.8);
        transition: color 0.3s ease;
    }

    .close-modal:hover {
        color: rgba(255, 255, 255, 1);
    }

    .complaint-details .detail-row {
        margin-bottom: 25px;
    }

    .complaint-details strong {
        display: inline-block;
        width: 150px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.1rem;
        vertical-align: top;
    }

    .complaint-details span,
    .complaint-details p {
        display: inline-block;
        width: calc(100% - 160px);
        font-size: 1.2rem;
        line-height: 1.6;
    }

    #modal-description {
        white-space: pre-wrap;
        background: rgba(255, 255, 255, 0.05);
        padding: 15px;
        border-radius: 10px;
        margin: 10px 0;
    }

    @media (max-width: 768px) {
        .modal-content {
            padding: 20px;
            width: 95%;
        }

        .modal-header h3 {
            font-size: 1.5rem;
        }

        .complaint-details strong {
            display: block;
            width: 100%;
            margin-bottom: 5px;
        }

        .complaint-details span,
        .complaint-details p {
            display: block;
            width: 100%;
            font-size: 1.1rem;
        }

        #modal-description {
            margin: 10px 0;
        }
    }

    @media (max-width: 480px) {
        .modal-content {
            padding: 15px;
        }

        .modal-header {
            margin-bottom: 20px;
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .modal-header h3 {
        font-size: 1.5rem;
        margin: 0;
        color: white;
    }

    .close-modal {
        cursor: pointer;
        font-size: 24px;
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        color: white;
    }

    .complaint-details .detail-row {
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
    }

    .complaint-details strong {
        min-width: 120px;
        color: rgba(255, 255, 255, 0.8);
        font-weight: 600;
    }

    /* Form elements in modal */
    #statusForm .form-group {
        margin-bottom: 20px;
    }

    #statusForm label {
        display: block;
        margin-bottom: 8px;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
    }

    #statusForm select,
    #statusForm textarea {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: white;
        font-size: 1rem;
    }

    #statusForm select:focus,
    #statusForm textarea:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.4);
    }

    #statusForm button {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: white;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #statusForm button:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
    }

    #statusForm select {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: white;
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

    #statusForm select:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    #statusForm select option {
        background: #4361ee;
        color: white;
        padding: 12px;
    }

    #statusForm select::-ms-expand {
        display: none;
    }

    #statusForm textarea {
        width: 100%;
        padding: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: white;
        font-size: 1rem;
        min-height: 100px;
        resize: vertical;
    }

    #statusForm textarea:focus {
        outline: none;
        border-color: rgba(255, 255, 255, 0.4);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .complaints-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .complaints-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        background: rgba(0, 0, 0, 0.3);
        color: #fff;
        text-transform: uppercase;
        font-size: 0.9rem;
        letter-spacing: 1px;
    }

    .complaints-table td {
        padding: 15px;
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.9);
    }

    h1, h2, h3 {
        color: #fff;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .page-header h1 {
        font-size: 1.8rem;
        margin: 0;
    }

    .complaints-table td {
        padding: 15px;
        background: rgba(255, 255, 255, 0.05);
    }

    .complaints-table tr:hover td {
        background: rgba(255, 255, 255, 0.1);
    }

    .btn {
        padding: 8px 16px;
        border-radius: 20px;
        border: none;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .btn:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.2);
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateBtns = document.querySelectorAll('.update-status-btn');
    const modal = document.getElementById('statusModal');
    const closeBtn = document.querySelector('.close-modal');
    const statusForm = document.getElementById('statusForm');
    
    updateBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const complaintId = this.getAttribute('data-id');
            statusForm.action = `complaints.php?action=update_status&id=${complaintId}`;
            modal.style.display = 'block';
        });
    });
    
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Add view modal functionality
const viewBtns = document.querySelectorAll('.btn-info');
const viewModal = document.getElementById('complaintModal');
const viewCloseBtn = viewModal.querySelector('.close-modal');

viewBtns.forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const row = this.closest('tr');
        
        // Populate modal with complaint details
        document.getElementById('modal-title').textContent = row.querySelector('td:nth-child(2)').textContent;
        document.getElementById('modal-category').textContent = row.querySelector('td:nth-child(3)').textContent;
        document.getElementById('modal-description').textContent = this.getAttribute('data-description');
        document.getElementById('modal-status').innerHTML = row.querySelector('td:nth-child(6)').innerHTML;
        document.getElementById('modal-user').textContent = row.querySelector('td:nth-child(4)').textContent;
        document.getElementById('modal-date').textContent = row.querySelector('td:nth-child(5)').textContent;
        
        viewModal.style.display = 'block';
    });
});

viewCloseBtn.addEventListener('click', () => viewModal.style.display = 'none');

window.addEventListener('click', (e) => {
    if (e.target === viewModal) viewModal.style.display = 'none';
});
</script>

<?php include '../includes/footer.php'; ?>