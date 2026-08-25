<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_dept = $_SESSION['user_department'];

if ($user_dept !== 'Marketing' && $_SESSION['user_role'] !== 'admin') {
    header('Location: staff-dashboard.php');
    exit;
}

// Marketing Stats
$stats = [
    'active_campaigns' => $pdo->query("SELECT COUNT(*) FROM projects WHERE department = 'Marketing' AND status != 'completed'")->fetchColumn() ?: 0,
    'total_leads' => $pdo->query("SELECT COUNT(*) FROM service_bookings")->fetchColumn() ?: 0,
    'published_posts' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn() ?: 0,
    'new_subscribers' => 24 // Mock metric for newsletter
];

// Fetch Marketing Tasks
$marketingTasks = $pdo->prepare("SELECT t.*, p.title as project_title FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE p.department = 'Marketing' OR t.title LIKE '%Market%' OR t.title LIKE '%SEO%' ORDER BY t.due_date ASC LIMIT 5");
$marketingTasks->execute();
$tasks = $marketingTasks->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing Dashboard | DataSphere</title>
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
            $header_title = "Marketing Center";
            $header_subtitle = "Track campaigns, analyze leads, and manage content strategy.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(139, 92, 246, 0.1); color: var(--accent-purple);">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['active_campaigns']; ?></h3>
                    <p>Active Campaigns</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['total_leads']; ?></h3>
                    <p>Total Leads</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(6, 182, 212, 0.1); color: var(--accent-cyan);">
                            <i class="fas fa-newspaper"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['published_posts']; ?></h3>
                    <p>Published Articles</p>
                </div>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: rgba(244, 63, 94, 0.1); color: var(--accent-pink);">
                            <i class="fas fa-envelope-open-text"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['new_subscribers']; ?></h3>
                    <p>New Subscribers (30d)</p>
                </div>
            </div>

            <div class="dashboard-content-grid">
                <div class="dashboard-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Marketing Tasks</h3>
                        <a href="staff-tasks.php" class="card-action" style="font-size: 0.85rem; color: var(--primary-blue-light); text-decoration: none;">View All Tasks</a>
                    </div>
                    <div class="task-list">
                        <?php if (empty($tasks)): ?>
                            <p style="color: var(--gray-500); text-align: center; padding: 20px;">No pending marketing tasks.</p>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                            <div style="display: flex; align-items: flex-start; gap: 15px; padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <div style="width: 40px; height: 40px; border-radius: 8px; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div style="flex: 1;">
                                    <h4 style="font-size: 0.95rem; margin: 0;"><?php echo htmlspecialchars($task['title']); ?></h4>
                                    <p style="font-size: 0.75rem; color: var(--gray-400); margin: 0;"><?php echo htmlspecialchars($task['project_title']); ?></p>
                                </div>
                                <span class="status-badge" style="background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); font-size: 0.75rem; padding: 4px 10px; border-radius: 12px;">
                                    <?php echo ucfirst($task['status']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 style="font-size: 1.25rem; margin-bottom: var(--space-xl);">Marketing Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;" onclick="window.location.href='staff-blog.php'">
                            <i class="fas fa-pen-nib" style="margin-right: 10px;"></i> Manage Blog Posts
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-chart-pie" style="margin-right: 10px;"></i> SEO Reports
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-share-alt" style="margin-right: 10px;"></i> Social Media Planner
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="js/notifications.js"></script>
</body>
</html>
