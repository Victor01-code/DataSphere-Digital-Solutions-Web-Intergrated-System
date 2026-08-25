<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];

// Fetch some stats for the report
$totalProjects = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
$completedProjects = $pdo->query("SELECT COUNT(*) FROM projects WHERE status = 'completed'")->fetchColumn();
$totalHours = $pdo->query("SELECT SUM(hours) FROM time_logs")->fetchColumn() ?: 0;
$averageProgress = $pdo->query("SELECT AVG(progress) FROM projects")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Reports & Analytics</h1>
                    <p style="color: var(--gray-400);">Key performance indicators and project metrics.</p>
                </div>
                <button class="btn btn-secondary" onclick="window.location.href='api/export.php'"><i class="fas fa-download"></i> Export Data</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalProjects; ?></div>
                    <div class="stat-label">Total Projects</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo round($averageProgress); ?>%</div>
                    <div class="stat-label">Avg. Completion</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo number_format($totalHours, 1); ?></div>
                    <div class="stat-label">Total Hours Logged</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $completedProjects; ?></div>
                    <div class="stat-label">Finished Milestones</div>
                </div>
            </div>

            <div class="dashboard-grid" style="margin-top: var(--space-2xl);">
                <div class="dashboard-card" style="grid-column: span 12;">
                    <div class="card-header">
                        <h3 class="card-title">Project Status Overview</h3>
                    </div>
                    <div style="padding: 20px; text-align: center; color: var(--gray-500);">
                        <div style="height: 200px; display: flex; align-items: flex-end; gap: 20px; justify-content: center; margin-bottom: 20px;">
                            <div style="width: 40px; background: var(--accent-purple); height: <?php echo ($totalProjects > 0 ? ($completedProjects/$totalProjects)*100 : 0); ?>%; border-radius: 4px 4px 0 0;"></div>
                            <div style="width: 40px; background: var(--accent-pink); height: <?php echo ($totalProjects > 0 ? (1 - $completedProjects/$totalProjects)*100 : 0); ?>%; border-radius: 4px 4px 0 0;"></div>
                        </div>
                        <div style="display: flex; gap: 20px; justify-content: center;">
                            <span><i class="fas fa-circle" style="color: var(--accent-purple);"></i> Completed</span>
                            <span><i class="fas fa-circle" style="color: var(--accent-pink);"></i> In Progress</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

