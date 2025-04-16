<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();

// Get all complaints for the current user
$complaints = $db->query(
    "SELECT c.id, c.title, cc.name as category, c.status, c.created_at, c.updated_at
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC",
    [$_SESSION['user_id']]
)->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "My Complaints";
include '../includes/header.php';
?>

<div class="user-complaints">
    <div class="page-header">
        <h1><i class="fas fa-list"></i> My Complaints</h1>
        <div class="header-actions">
            <a href="new-complaint.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> File New Complaint
            </a>
            <a href="dashboard.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <?php if (empty($complaints)): ?>
        <div class="no-complaints">
            <i class="fas fa-info-circle"></i>
            <p>You haven't filed any complaints yet.</p>
        </div>
    <?php else: ?>
        <div class="complaints-list">
            <table class="complaints-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Date Filed</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td><?php echo $complaint['id']; ?></td>
                        <td><?php echo htmlspecialchars($complaint['title']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['category']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($complaint['updated_at'])); ?></td>
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
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>