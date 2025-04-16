<?php
require_once dirname(__FILE__) . '/../includes/db.php';
require_once dirname(__FILE__) . '/../includes/auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();

// Get user's complaints
$stmt = $db->query(
    "SELECT c.id, c.title, cc.name as category, c.status, c.created_at, c.updated_at
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC",
    [$_SESSION['user_id']]
);
$complaints = $db->fetchAll($stmt);

// Count complaints by status
$stats = [
    'total' => count($complaints),
    'pending' => 0,
    'in_progress' => 0,
    'resolved' => 0
];

foreach ($complaints as $complaint) {
    $stats[$complaint['status']]++;
}

$pageTitle = "User Dashboard";
include dirname(__FILE__) . '/../includes/header.php';
?>

<div class="user-dashboard">
    <div class="dashboard-header">
        <h1><i class="fas fa-tachometer-alt"></i> User Dashboard</h1>
        <div class="welcome-message">
            <?php
            $fullName = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
            echo htmlspecialchars($fullName);
            ?>
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
    
    <div class="user-actions">
        <a href="new-complaint.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> File New Complaint
        </a>
    </div>
    
    <div class="recent-complaints">
        <h2><i class="fas fa-history"></i> Your Recent Complaints</h2>
        
        <?php if (empty($complaints)): ?>
            <div class="no-complaints">
                <i class="fas fa-info-circle"></i>
                <p>You haven't filed any complaints yet.</p>
            </div>
        <?php else: ?>
            <table class="complaints-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?php echo $complaint['id']; ?></td>
                        <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['category']); ?></td>
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
        <?php endif; ?>
    </div>
</div>

<?php include dirname(__FILE__) . '/../includes/footer.php'; ?>