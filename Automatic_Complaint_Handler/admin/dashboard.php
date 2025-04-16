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

// Get department-specific stats
$department = $auth::getDepartment();
$stats = $db->query(
    "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.id
     JOIN departments d ON cc.department_id = d.id
     WHERE d.name = ?",
    [$department]
)->get_result()->fetch_assoc();

// Get recent complaints
$recentComplaints = $db->query(
    "SELECT c.id, c.title, c.status, c.created_at, u.first_name, u.last_name
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
    </div>
    
    <div class="recent-complaints">
        <h2><i class="fas fa-history"></i> Recent Complaints</h2>
        <table class="complaints-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
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
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="view-all">
            <a href="complaints.php" class="btn btn-outline">
                <i class="fas fa-list"></i> View All Complaints
            </a>
        </div>
    </div>
</div>

<?php include dirname(__FILE__) . '/../includes/footer.php'; ?>