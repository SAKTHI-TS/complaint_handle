<?php
require_once dirname(__FILE__) . '/../includes/db.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

// Check if admin is logged in
if (!Auth::isAdmin()) {
    header('Location: ../login.php');
    exit();
}

$auth = new Auth();
$auth::checkSessionTimeout();

// Get department info - use both name and ID
$department = $_SESSION['department'] ?? null;
$departmentId = $_SESSION['department_id'] ?? null;

if (!$department || !$departmentId) {
    session_destroy();
    header('Location: ../login.php?error=session_expired');
    exit();
}

$auth::requireAdmin();

$db = new Database();

// Get department-specific stats with double verification
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.id
     JOIN departments d ON cc.department_id = d.id
     WHERE d.name = ? 
     AND cc.department_id = (
         SELECT department_id 
         FROM admins 
         WHERE id = ?
     )",
    [$department, $_SESSION['user_id']]
)->get_result()->fetch_assoc();

// Add null checks for stats display
$stats['total'] = $stats['total'] ?? 0;
$stats['pending'] = $stats['pending'] ?? 0;
$stats['in_progress'] = $stats['in_progress'] ?? 0;
$stats['resolved'] = $stats['resolved'] ?? 0;
$stats['rejected'] = $stats['rejected'] ?? 0;

// Get recent complaints with more details
$recentComplaints = $db->query(
    "SELECT 
        c.id, 
        c.title, 
        c.description,
        c.status, 
        c.created_at, 
        u.first_name, 
        u.last_name,
        cc.name as category_name
     FROM complaints c
     JOIN users u ON c.user_id = u.id
     JOIN complaint_categories cc ON c.category_id = cc.id
     JOIN departments d ON cc.department_id = d.id
     WHERE d.name = ?
     ORDER BY c.created_at DESC
     LIMIT 5",
    [$department]
)->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Admin Dashboard";
include dirname(__FILE__) . '/../includes/header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1><i class="fas fa-tachometer-alt"></i> <?php echo $department; ?> Department Dashboard</h1>
        <div class="welcome-message">
            Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
        </div>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total']; ?></div>
            <div class="stat-label">Total Complaints</div>
            <div class="stat-icon"><i class="fas fa-list"></i></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['pending']; ?></div>
            <div class="stat-label">Pending</div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['in_progress']; ?></div>
            <div class="stat-label">In Progress</div>
            <div class="stat-icon"><i class="fas fa-spinner"></i></div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['resolved']; ?></div>
            <div class="stat-label">Resolved</div>
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        </div>

        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['rejected']; ?></div>
            <div class="stat-label">Rejected</div>
            <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        </div>
    </div>
    
    <div class="recent-complaints">
        <h2><i class="fas fa-history"></i> Recent Complaints</h2>
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Submitted By</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentComplaints as $complaint): ?>
                <tr>
                    <td><?php echo $complaint['id']; ?></td>
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
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="view-all">
            <a href="complaints.php" class="btn btn-primary">
                <i class="fas fa-list"></i> View All Complaints
            </a>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewBtns = document.querySelectorAll('.btn-info');
    const modal = document.getElementById('complaintModal');
    const closeBtn = modal.querySelector('.close-modal');
    
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
            
            modal.style.display = 'block';
        });
    });
    
    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });
});
</script>

<?php include dirname(__FILE__) . '/../includes/footer.php'; ?>