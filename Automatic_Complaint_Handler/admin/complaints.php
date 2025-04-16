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
    "SELECT c.*, u.first_name, u.last_name, cc.name as category_name
     FROM complaints c
     JOIN users u ON c.user_id = u.id
     JOIN complaint_categories cc ON c.category_id = cc.id
     JOIN departments d ON cc.department_id = d.id
     WHERE d.name = ?
     ORDER BY c.created_at DESC",
    [$department]
)->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Manage Complaints";
include '../includes/header.php';
?>

<div class="admin-complaints">
    <div class="page-header">
        <h1><i class="fas fa-tasks"></i> Manage Complaints</h1>
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
                    <th>Submitted By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($complaints as $complaint): ?>
                <tr>
                    <td><?php echo $complaint['id']; ?></td>
                    <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                    <td>
                        <span class="status-badge <?php echo str_replace('_', '-', $complaint['status']); ?>">
                            <?php echo ucwords(str_replace('_', ' ', $complaint['status'])); ?>
                        </span>
                    </td>
                    <td>
                        <a href="complaints.php?action=view&id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-info">
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
</script>

<?php include '../includes/footer.php'; ?>