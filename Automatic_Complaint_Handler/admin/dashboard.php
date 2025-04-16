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

    .admin-dashboard {
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
        color: white;
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
        color: white;
        text-align: center;
        transition: transform 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .stat-icon {
        font-size: 2rem;
        margin-top: 10px;
        opacity: 0.8;
    }

    .recent-complaints {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 20px;
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.2);
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

    .complaints-table tr:hover td {
        background: rgba(255, 255, 255, 0.1);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-badge.pending { background: rgba(255, 193, 7, 0.2); }
    .status-badge.in-progress { background: rgba(13, 110, 253, 0.2); }
    .status-badge.resolved { background: rgba(25, 135, 84, 0.2); }
    .status-badge.rejected { background: rgba(220, 53, 69, 0.2); }

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

    .stat-label {
        color: rgba(255, 255, 255, 0.95);
        font-weight: 500;
        font-size: 1.1rem;
    }

    h2 {
        color: #fff;
        margin-bottom: 20px;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
</style>

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
                <?php $counter = 1; ?>
                <?php foreach ($recentComplaints as $complaint): ?>
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
                        <a href="complaints.php?action=view&id=<?php echo $complaint['id']; ?>" class="btn btn-sm btn-info">
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

<?php include dirname(__FILE__) . '/../includes/footer.php'; ?>