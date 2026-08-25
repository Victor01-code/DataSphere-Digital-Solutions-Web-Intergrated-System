<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$user_id = $_SESSION['user_id'];

// Fetch all notifications for this user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read if requested
if (isset($_GET['mark_all_read'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$user_id]);
    header('Location: staff-notifications.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | DataSphere Staff</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .notif-page-list { display: flex; flex-direction: column; gap: var(--space-md); margin-top: var(--space-xl); }
        .notif-page-item { background: var(--dark-700); padding: var(--space-xl); border-radius: var(--radius-xl); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s; display: flex; gap: 20px; align-items: center; position: relative; overflow: hidden; }
        .notif-page-item.unread { border-left: 4px solid var(--accent-purple); background: rgba(139, 92, 246, 0.03); }
        .notif-page-item:hover { transform: translateX(5px); border-color: rgba(255,255,255,0.1); }
        .notif-icon-circle { width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0; }
        .notif-content-full { flex: 1; }
        .notif-time-full { font-size: 0.8rem; color: var(--gray-500); white-space: nowrap; }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Notifications Hub";
            $header_subtitle = "Keep track of all your activities and alerts.";
            $header_actions = '<a href="?mark_all_read=1" class="btn btn-secondary btn-sm">Mark All as Read</a>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="notif-page-list">
                <?php if (empty($notifications)): ?>
                <div style="text-align: center; padding: 100px 20px; color: var(--gray-500);">
                    <i class="fas fa-bell-slash" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.3;"></i>
                    <h3>No notifications found</h3>
                    <p>You're all caught up!</p>
                </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): 
                        $icon = 'fa-bell';
                        $color = 'var(--gray-400)';
                        $bg = 'rgba(255,255,255,0.05)';
                        
                        switch($n['type']) {
                            case 'task': $icon = 'fa-tasks'; $color = 'var(--accent-purple)'; $bg = 'rgba(139, 92, 246, 0.1)'; break;
                            case 'project': $icon = 'fa-project-diagram'; $color = 'var(--accent-cyan)'; $bg = 'rgba(6, 182, 212, 0.1)'; break;
                            case 'message': $icon = 'fa-envelope'; $color = 'var(--accent-pink)'; $bg = 'rgba(236, 72, 153, 0.1)'; break;
                            case 'alert': $icon = 'fa-exclamation-triangle'; $color = '#f43f5e'; $bg = 'rgba(244, 63, 94, 0.1)'; break;
                        }
                    ?>
                    <a href="<?php echo htmlspecialchars($n['link'] ?? '#'); ?>" class="notif-page-item <?php echo $n['is_read'] ? '' : 'unread'; ?>" style="text-decoration: none; color: inherit;">
                        <div class="notif-icon-circle" style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="notif-content-full">
                            <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($n['title']); ?></h4>
                            <p style="color: var(--gray-400); font-size: 0.9rem; line-height: 1.5;"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                        <div class="notif-time-full">
                            <?php echo date('M d, H:i', strtotime($n['created_at'])); ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="js/notifications.js"></script>
</body>
</html>
