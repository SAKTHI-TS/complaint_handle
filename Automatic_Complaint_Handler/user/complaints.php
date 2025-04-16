<?php
require_once '../config.php';
require_once '../auth.php';

$auth = new Auth();
$auth::checkSessionTimeout();
$auth::requireUser();

$db = new Database();

// Get all complaints for the current user
$complaints = $db->query(
    "SELECT c.id, c.title, c.description, cc.name as category, c.status, c.created_at, c.updated_at
     FROM complaints c
     JOIN complaint_categories cc ON c.category_id = cc.id
     WHERE c.user_id = ?
     ORDER BY c.created_at DESC",
    [$_SESSION['user_id']]
)->get_result()->fetch_all(MYSQLI_ASSOC);

$pageTitle = "My Complaints";
include '../includes/header.php';
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

    .user-complaints {
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

    .no-complaints {
        text-align: center;
        padding: 40px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        backdrop-filter: blur(12px);
    }

    @keyframes gradientBG {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
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
        padding: 20px;
        border-radius: 15px;
        width: 90%;
        max-width: 600px;
        color: white;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .close-modal {
        cursor: pointer;
        font-size: 24px;
        color: rgba(255, 255, 255, 0.8);
    }

    .complaint-details .detail-row {
        margin-bottom: 15px;
    }

    .complaint-details strong {
        display: inline-block;
        width: 120px;
        color: rgba(255, 255, 255, 0.8);
    }
</style>

<div class="user-complaints">
    <div class="page-header">
        <h1><i class="fas fa-list"></i> My Complaints</h1>
        <a href="dashboard.php" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <?php if (empty($complaints)): ?>
        <div class="no-complaints">
            <i class="fas fa-info-circle"></i>
            <p>You haven't filed any complaints yet.</p>
            <a href="new-complaint.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> File New Complaint
            </a>
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
                            <a href="#" class="btn btn-sm btn-info" data-description="<?php echo htmlspecialchars($complaint['description']); ?>">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

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
                        <strong>Date Filed:</strong>
                        <span id="modal-date"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Last Updated:</strong>
                        <span id="modal-updated"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add the modal functionality
        document.querySelectorAll('.btn-info').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('tr');
                
                // Populate modal with complaint details
                document.getElementById('modal-title').textContent = row.querySelector('td:nth-child(2)').textContent;
                document.getElementById('modal-category').textContent = row.querySelector('td:nth-child(3)').textContent;
                document.getElementById('modal-description').textContent = this.getAttribute('data-description');
                document.getElementById('modal-status').innerHTML = row.querySelector('td:nth-child(6)').innerHTML;
                document.getElementById('modal-date').textContent = row.querySelector('td:nth-child(4)').textContent;
                document.getElementById('modal-updated').textContent = row.querySelector('td:nth-child(5)').textContent;
                
                document.getElementById('complaintModal').style.display = 'block';
            });
        });

        // Close modal functionality
        document.querySelector('.close-modal').addEventListener('click', () => {
            document.getElementById('complaintModal').style.display = 'none';
        });

        window.addEventListener('click', (e) => {
            if (e.target === document.getElementById('complaintModal')) {
                document.getElementById('complaintModal').style.display = 'none';
            }
        });
    </script>
</div>

<?php include '../includes/footer.php'; ?>