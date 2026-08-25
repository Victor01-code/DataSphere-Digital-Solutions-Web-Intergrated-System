<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$tickets = [];
try {
    $stmt = $pdo->query("
        SELECT t.*, u.name as client_name, u.email as client_email 
        FROM support_tickets t 
        JOIN users u ON t.client_id = u.id 
        ORDER BY t.created_at DESC
    ");
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Support Tickets";
            $header_subtitle = "Manage and resolve client support requests.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                    <h3 style="font-size: 1.25rem;">Active Support Requests</h3>
                </div>

                <?php if (empty($tickets)): ?>
                    <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                        <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: var(--space-lg); opacity: 0.2;"></i>
                        <p>No support tickets found.</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                        <?php foreach ($tickets as $ticket): ?>
                            <div style="background: var(--dark-700); border-radius: var(--radius-lg); padding: var(--space-lg); border: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: var(--space-lg);">
                                    <div style="width: 40px; height: 40px; background: rgba(139, 92, 246, 0.1); color: var(--accent-purple); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div>
                                        <h4 style="margin-bottom: 2px;"><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                        <p style="font-size: 0.8rem; color: var(--gray-400);">Client: <?php echo htmlspecialchars($ticket['client_name']); ?> • ID: #TCK-<?php echo str_pad($ticket['id'], 5, '0', STR_PAD_LEFT); ?> • Opened on <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: var(--space-xl);">
                                    <span style="font-size: 0.75rem; padding: 4px 10px; border-radius: 4px; background: rgba(255,255,255,0.05); color: var(--gray-300);">
                                        Priority: <span style="font-weight: 700; color: <?php echo $ticket['priority'] == 'high' || $ticket['priority'] == 'urgent' ? 'var(--accent-pink)' : 'var(--accent-green)'; ?>;"><?php echo ucfirst($ticket['priority']); ?></span>
                                    </span>
                                    <span class="status-badge <?php echo strtolower($ticket['status']); ?>" style="padding: 6px 14px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light);">
                                        <?php echo str_replace('-', ' ', $ticket['status']); ?>
                                    </span>
                                    <a href="staff-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-secondary btn-sm">View Thread</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="js/notifications.js"></script>
</body>
</html>
