<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all projects for this client
$stmt = $pdo->prepare("SELECT p.* FROM projects p 
                       JOIN project_members pm ON p.id = pm.project_id 
                       WHERE pm.user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects | DataSphere Client Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/client_sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "My Projects";
            $header_subtitle = "View and track all your active and completed projects.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--space-xl); margin-top: var(--space-xl);">
                <?php if (empty($projects)): ?>
                    <div class="dashboard-card" style="grid-column: 1 / -1; padding: var(--space-2xl); text-align: center;">
                        <i class="fas fa-project-diagram" style="font-size: 3rem; color: var(--gray-600); margin-bottom: var(--space-lg);"></i>
                        <h3>No projects found</h3>
                        <p style="color: var(--gray-400);">You don't have any active projects at the moment.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                        <div class="dashboard-card" style="padding: var(--space-xl);">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--space-lg);">
                                <div style="width: 48px; height: 48px; background: var(--gradient-primary); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                                    <i class="fas fa-globe"></i>
                                </div>
                                <span class="status-badge <?php echo strtolower($project['status']); ?>" style="padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light);">
                                    <?php echo htmlspecialchars($project['status']); ?>
                                </span>
                            </div>
                            <h3 style="margin-bottom: var(--space-xs);"><?php echo htmlspecialchars($project['title']); ?></h3>
                            <p style="font-size: 0.875rem; color: var(--gray-400); margin-bottom: var(--space-xl);"><?php echo htmlspecialchars($project['type']); ?></p>
                            
                            <div style="margin-bottom: var(--space-sm); display: flex; justify-content: space-between; font-size: 0.875rem;">
                                <span>Progress</span>
                                <span><?php echo $project['progress']; ?>%</span>
                            </div>
                            <div style="height: 8px; background: var(--dark-600); border-radius: 4px; overflow: hidden; margin-bottom: var(--space-xl);">
                                <div style="height: 100%; width: <?php echo $project['progress']; ?>%; background: var(--gradient-primary); border-radius: 4px;"></div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: var(--space-lg);">
                                <div style="font-size: 0.8rem; color: var(--gray-400);">
                                    <i class="fas fa-calendar-alt" style="margin-right: 4px;"></i> Due: <?php echo date('M d, Y', strtotime($project['due_date'])); ?>
                                </div>
                                <a href="#" class="btn btn-secondary btn-sm">View Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="js/main.js"></script>
</body>
</html>
