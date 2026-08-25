<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$user_role = $_SESSION['user_role'] ?? 'staff';
$user_dept = $_SESSION['user_department'] ?? 'General';

// Fetch Stats - Department Aware for Staff
if ($user_role === 'staff') {
    $stats = [
        'active_projects' => $pdo->prepare("SELECT COUNT(*) FROM projects WHERE status != 'completed' AND department = ?"),
        'pending_tasks' => $pdo->prepare("SELECT COUNT(*) FROM tasks t LEFT JOIN projects p ON t.project_id = p.id WHERE (t.assigned_to = ? OR p.department = ?) AND t.status = 'pending'"),
        'team_online' => 5, // Mock value
        'hours_logged' => 0
    ];
    $stats['active_projects']->execute([$user_dept]);
    $stats['active_projects'] = $stats['active_projects']->fetchColumn();
    
    $stats['pending_tasks']->execute([$user_id, $user_dept]);
    $stats['pending_tasks'] = $stats['pending_tasks']->fetchColumn();
} else {
    $stats = [
        'active_projects' => $pdo->query("SELECT COUNT(*) FROM projects WHERE status != 'completed'")->fetchColumn(),
        'pending_tasks' => $pdo->query("SELECT COUNT(*) FROM tasks WHERE status = 'pending'")->fetchColumn(),
        'team_online' => 8,
        'hours_logged' => 0
    ];
}

$hoursLoggedStmt = $pdo->prepare("SELECT SUM(hours) FROM time_logs WHERE user_id = ? AND YEARWEEK(log_date, 1) = YEARWEEK(CURDATE(), 1)");
$hoursLoggedStmt->execute([$user_id]);
$stats['hours_logged'] = $hoursLoggedStmt->fetchColumn() ?: 0;

// Fetch Recent Projects - Department Aware
if ($user_role === 'staff') {
    $recentProjectsStmt = $pdo->prepare("SELECT * FROM projects WHERE department = ? ORDER BY created_at DESC LIMIT 4");
    $recentProjectsStmt->execute([$user_dept]);
} else {
    $recentProjectsStmt = $pdo->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 4");
}
$recentProjects = $recentProjectsStmt->fetchAll();

// Fetch Tasks for today - Department Aware
if ($user_role === 'staff') {
    $tasksStmt = $pdo->prepare("
        SELECT t.*, p.title as project_title 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        WHERE (t.assigned_to = ? OR p.department = ?) AND t.status = 'pending' 
        ORDER BY t.due_date ASC LIMIT 5
    ");
    $tasksStmt->execute([$user_id, $user_dept]);
} else {
    $tasksStmt = $pdo->query("
        SELECT t.*, p.title as project_title 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        WHERE t.status = 'pending' 
        ORDER BY t.due_date ASC LIMIT 5
    ");
}
$tasks = $tasksStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere Staff Dashboard - Internal management and productivity portal.">
    <meta name="robots" content="noindex, nofollow">
    <title>Staff Dashboard | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <?php include 'php/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <?php 
            $header_title = "Welcome back, " . explode(' ', $user_name)[0] . "! 👋";
            $header_subtitle = "You are currently viewing the **" . htmlspecialchars($user_dept) . "** workspace.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Stats Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%); color: var(--accent-purple);">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['active_projects']; ?></h3>
                    <p>Active Projects</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.2) 0%, rgba(236, 72, 153, 0.1) 100%); color: var(--accent-pink);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['pending_tasks']; ?></h3>
                    <p>Pending Tasks</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%); color: var(--accent-green);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['team_online']; ?></h3>
                    <p>Team Members Online</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(6, 182, 212, 0.1) 100%); color: var(--accent-cyan);">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['hours_logged']; ?>h</h3>
                    <p>Hours This Week</p>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="dashboard-content-grid">
                <!-- Priority Tasks -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Priority Tasks</h3>
                        <a href="staff-tasks.php" class="card-action" style="font-size: 0.85rem; color: var(--primary-blue-light); text-decoration: none;">View All</a>
                    </div>

                    <div class="task-list">
                        <?php if (empty($tasks)): ?>
                            <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                                <i class="fas fa-tasks" style="font-size: 3rem; margin-bottom: var(--space-md); opacity: 0.3;"></i>
                                <p>No pending tasks. Great job!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                            <div style="background: var(--dark-700); border-radius: var(--radius-lg); padding: var(--space-lg); margin-bottom: var(--space-md); display: flex; align-items: center; gap: var(--space-md);">
                                <input type="checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                <div style="flex: 1;">
                                    <h4 style="font-size: 0.95rem; margin-bottom: 2px;"><?php echo htmlspecialchars($task['title']); ?></h4>
                                    <p style="font-size: 0.75rem; color: var(--gray-400);"><?php echo htmlspecialchars($task['project_title']); ?> • Due <?php echo date('M d', strtotime($task['due_date'])); ?></p>
                                </div>
                                <span style="padding: 4px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: <?php echo $task['priority'] === 'high' ? 'rgba(244, 63, 94, 0.1)' : 'rgba(245, 158, 11, 0.1)'; ?>; color: <?php echo $task['priority'] === 'high' ? '#f43f5e' : 'var(--accent-orange)'; ?>;">
                                    <?php echo ucfirst($task['priority']); ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Active Projects Progress -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;"><?php echo htmlspecialchars($user_dept); ?> Projects</h3>
                        <a href="staff-projects.php" class="card-action" style="font-size: 0.85rem; color: var(--primary-blue-light); text-decoration: none;">Manage</a>
                    </div>

                    <div class="project-mini-list" style="display: flex; flex-direction: column; gap: var(--space-lg);">
                        <?php if (empty($recentProjects)): ?>
                            <p style="color: var(--gray-500); text-align: center; padding: 20px;">No projects in this department yet.</p>
                        <?php else: ?>
                            <?php foreach ($recentProjects as $project): ?>
                            <div>
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                    <div>
                                        <h4 style="font-size: 0.95rem; margin-bottom: 2px;"><?php echo htmlspecialchars($project['title']); ?></h4>
                                        <p style="font-size: 0.75rem; color: var(--gray-400);"><?php echo htmlspecialchars($project['client_name']); ?></p>
                                    </div>
                                    <span style="font-weight: 700; font-size: 0.9rem;"><?php echo $project['progress']; ?>%</span>
                                </div>
                                <div style="height: 6px; background: var(--dark-700); border-radius: 3px; overflow: hidden;">
                                    <div style="height: 100%; width: <?php echo $project['progress']; ?>%; background: var(--gradient-primary); border-radius: 3px; transition: width 0.5s ease;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: var(--space-2xl);">
                        <h3 style="font-size: 1.1rem; margin-bottom: var(--space-lg);">Quick Actions</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-projects.php'">
                                <i class="fas fa-project-diagram"></i> Projects
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-tasks.php'">
                                <i class="fas fa-check-circle"></i> Tasks
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-time.php'">
                                <i class="fas fa-plus"></i> Log Time
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-messages.php'">
                                <i class="fas fa-comment"></i> Messages
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- WhatsApp Support -->
    <a href="https://wa.me/255693038737" class="whatsapp-btn" target="_blank" rel="noopener"
        aria-label="Chat on WhatsApp" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #25d366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); z-index: 1000; text-decoration: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/notifications.js"></script>
</body>

</html>
