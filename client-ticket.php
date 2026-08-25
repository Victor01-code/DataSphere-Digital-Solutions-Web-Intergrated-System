<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole('client');
$user_id = $_SESSION['user_id'];

$ticket_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE id = ? AND client_id = ?");
$stmt->execute([$ticket_id, $user_id]);
$ticket = $stmt->fetch();

if (!$ticket) {
    header("Location: client-support.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, message, is_staff) VALUES (?, ?, ?, 0)");
        $stmt->execute([$ticket_id, $user_id, $message]);
        
        $pdo->prepare("UPDATE support_tickets SET status = 'open' WHERE id = ?")->execute([$ticket_id]);
    }
    header("Location: client-ticket.php?id=$ticket_id");
    exit;
}

$msgStmt = $pdo->prepare("SELECT m.*, u.name as sender_name FROM ticket_messages m JOIN users u ON m.sender_id = u.id WHERE m.ticket_id = ? ORDER BY m.created_at ASC");
$msgStmt->execute([$ticket_id]);
$messages = $msgStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $ticket_id; ?> | DataSphere Client Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .msg-bubble { padding: 15px; border-radius: 12px; margin-bottom: 15px; max-width: 80%; }
        .msg-client { background: rgba(139, 92, 246, 0.2); border: 1px solid rgba(139, 92, 246, 0.3); align-self: flex-end; border-bottom-right-radius: 0; }
        .msg-staff { background: var(--dark-700); align-self: flex-start; border-bottom-left-radius: 0; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/client_sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Ticket #<?php echo str_pad($ticket['id'], 5, '0', STR_PAD_LEFT); ?></h1>
                    <p style="color: var(--gray-400);">Subject: <?php echo htmlspecialchars($ticket['subject']); ?></p>
                </div>
                <a href="client-support.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Support</a>
            </div>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl); display: flex; flex-direction: column; min-height: 500px;">
                <div style="flex: 1; overflow-y: auto; display: flex; flex-direction: column; padding-bottom: 20px;">
                    <?php if (empty($messages)): ?>
                        <div class="msg-bubble msg-client">
                            <strong>You</strong> <span style="font-size: 0.8rem; color: var(--gray-400);"><?php echo date('M d, H:i', strtotime($ticket['created_at'])); ?></span>
                            <p style="margin-top: 10px;"><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="msg-bubble <?php echo $msg['is_staff'] ? 'msg-staff' : 'msg-client'; ?>">
                                <strong><?php echo $msg['is_staff'] ? htmlspecialchars($msg['sender_name']) : 'You'; ?></strong> <span style="font-size: 0.8rem; color: var(--gray-400);"><?php echo date('M d, H:i', strtotime($msg['created_at'])); ?></span>
                                <p style="margin-top: 10px;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <form method="POST" style="margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                    <textarea name="message" class="form-input" rows="3" placeholder="Type your reply..." required></textarea>
                    <div style="text-align: right; margin-top: 10px;">
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
