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

    .user-dashboard {
        padding: 20px;
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        transition: transform 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .recent-complaints {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
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
        background: rgba(255, 255, 255, 0.05);
        color: rgba(255, 255, 255, 0.9);
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
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
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .btn-sm {
        padding: 8px 15px;
        font-size: 0.9rem;
    }

    .user-actions .btn {
        background: rgba(67, 97, 238, 0.3);
    }

    .user-actions {
        margin-bottom: 30px;
        text-align: right;
    }

    .user-actions .btn {
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

    .user-actions .btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
</style>

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