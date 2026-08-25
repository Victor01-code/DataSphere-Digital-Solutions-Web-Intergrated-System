<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_dept = $_SESSION['user_department'];

if ($user_dept !== 'HR' && $_SESSION['user_role'] !== 'admin') {
    header('Location: staff-dashboard.php');
    exit;
}

// HR Stats
$stats = [
    'total_staff' => $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('staff', 'admin')")->fetchColumn(),
    'new_this_month' => $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn(),
    'active_now' => 5, // Mock
    'pending_actions' => 2
];

// Fetch Recent Hires
$recentHires = $pdo->query("SELECT * FROM users WHERE role IN ('staff', 'admin') ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Fetch HR Tasks
$hrTasks = $pdo->prepare("SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE p.department = 'HR' OR t.title LIKE '%HR%' OR t.title LIKE '%Recruit%' ORDER BY t.due_date ASC LIMIT 5");
$hrTasks->execute();
$tasks = $hrTasks->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Human Resources Center";
            $header_subtitle = "Manage team growth, onboarding, and internal culture.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--accent-purple);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['total_staff']; ?></h3>
                    <p>Total Staff</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green);">
                            <i class="fas fa-user-plus"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['new_this_month']; ?></h3>
                    <p>New Hires (30d)</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--accent-cyan);">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['active_now']; ?></h3>
                    <p>Currently Active</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(244, 63, 94, 0.1); color: var(--accent-pink);">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['pending_actions']; ?></h3>
                    <p>Pending Reviews</p>
                </div>
            </div>

            <div class="dashboard-content-grid">
                <div class="dashboard-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Recent Team Members</h3>
                        <a href="staff-team.php" class="card-action" style="font-size: 0.85rem; color: var(--primary-blue-light); text-decoration: none;">View Directory</a>
                    </div>
                    <div class="team-list">
                        <?php foreach ($recentHires as $member): ?>
                        <div style="display: flex; align-items: center; gap: 15px; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">
                                <?php echo $member['avatar'] ?: substr($member['name'], 0, 1); ?>
                            </div>
                            <div style="flex: 1;">
                                <h4 style="font-size: 0.95rem; margin: 0;"><?php echo htmlspecialchars($member['name']); ?></h4>
                                <p style="font-size: 0.75rem; color: var(--gray-400); margin: 0;"><?php echo htmlspecialchars($member['title']); ?></p>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--gray-500);"><?php echo date('M d', strtotime($member['created_at'])); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 style="font-size: 1.25rem; margin-bottom: var(--space-xl);">HR Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;" onclick="window.location.href='admin-users.php?add'">
                            <i class="fas fa-user-plus" style="margin-right: 10px;"></i> Onboard New Staff
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-file-contract" style="margin-right: 10px;"></i> Policy Updates
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-calendar-check" style="margin-right: 10px;"></i> Leave Requests
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="js/notifications.js"></script>
</body>
</html>
