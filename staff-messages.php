<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];

// Fetch all staff members for the sidebar
$stmt = $pdo->prepare("SELECT id, name, avatar, title FROM users WHERE id != ? AND role IN ('staff', 'admin')");
$stmt->execute([$userId]);
$contacts = $stmt->fetchAll();

// Fetch last messages for each contact (simplified)
$lastMessages = [];
foreach ($contacts as $contact) {
    $mStmt = $pdo->prepare("
        SELECT content, created_at FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at DESC LIMIT 1
    ");
    $mStmt->execute([$userId, $contact['id'], $contact['id'], $userId]);
    $lastMessages[$contact['id']] = $mStmt->fetch();
}

$activeContactId = $_GET['chat_with'] ?? ($contacts[0]['id'] ?? null);
$chatHistory = [];

if ($activeContactId) {
    $hStmt = $pdo->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC
    ");
    $hStmt->execute([$userId, $activeContactId, $activeContactId, $userId]);
    $chatHistory = $hStmt->fetchAll();
    
    // Get active contact info
    $cStmt = $pdo->prepare("SELECT name, avatar FROM users WHERE id = ?");
    $cStmt->execute([$activeContactId]);
    $activeContact = $cStmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .messages-container { display: grid; grid-template-columns: 320px 1fr; background: var(--dark-800); border-radius: var(--radius-xl); border: 1px solid rgba(255, 255, 255, 0.05); height: calc(100vh - 200px); overflow: hidden; margin-top: var(--space-xl); }
        .messages-sidebar { border-right: 1px solid rgba(255, 255, 255, 0.05); display: flex; flex-direction: column; overflow-y: auto; }
        .message-item { display: flex; gap: var(--space-md); padding: var(--space-lg); border-bottom: 1px solid rgba(255, 255, 255, 0.03); cursor: pointer; transition: all 0.3s; text-decoration: none; }
        .message-item:hover { background: rgba(255, 255, 255, 0.02); }
        .message-item.active { background: rgba(139, 92, 246, 0.1); border-left: 3px solid var(--accent-purple); }
        .chat-main { display: flex; flex-direction: column; }
        .chat-header { padding: var(--space-md) var(--space-xl); background: var(--dark-700); border-bottom: 1px solid rgba(255, 255, 255, 0.05); display: flex; justify-content: space-between; align-items: center; }
        .chat-body { flex: 1; padding: var(--space-xl); overflow-y: auto; display: flex; flex-direction: column; gap: var(--space-lg); background-image: radial-gradient(rgba(139, 92, 246, 0.03) 1px, transparent 0); background-size: 20px 20px; }
        .msg { max-width: 70%; padding: var(--space-md) var(--space-lg); border-radius: var(--radius-lg); font-size: 0.95rem; position: relative; }
        .msg-received { align-self: flex-start; background: var(--dark-700); color: var(--gray-200); border-bottom-left-radius: 0; }
        .msg-sent { align-self: flex-end; background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-pink) 100%); color: var(--white); border-bottom-right-radius: 0; }
        .chat-input-area { padding: var(--space-lg) var(--space-xl); background: var(--dark-700); border-top: 1px solid rgba(255, 255, 255, 0.05); display: flex; gap: var(--space-md); align-items: center; }
        .chat-input { flex: 1; background: var(--dark-800); border: 1px solid rgba(255, 255, 255, 0.1); color: var(--white); padding: var(--space-md) var(--space-lg); border-radius: 25px; outline: none; }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Internal Messages";
            $header_subtitle = "Secure communication with your team.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="messages-container">
                <div class="messages-sidebar">
                    <div style="padding: var(--space-lg); border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                        <input type="text" class="search-input" placeholder="Search chats..." style="width: 100%;">
                    </div>
                    <?php foreach ($contacts as $contact): ?>
                    <a href="?chat_with=<?php echo $contact['id']; ?>" class="message-item <?php echo $activeContactId == $contact['id'] ? 'active' : ''; ?>">
                        <div class="profile-avatar" style="background: var(--accent-purple); width: 44px; height: 44px; font-size: 0.9rem; flex-shrink: 0;"><?php echo $contact['avatar']; ?></div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                <h4 style="font-size: 0.9rem; color: var(--white); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($contact['name']); ?></h4>
                                <span style="font-size: 0.7rem; color: var(--gray-500);"><?php echo isset($lastMessages[$contact['id']]['created_at']) ? date('H:i', strtotime($lastMessages[$contact['id']]['created_at'])) : ''; ?></span>
                            </div>
                            <p style="font-size: 0.8rem; color: var(--gray-400); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?php echo htmlspecialchars($lastMessages[$contact['id']]['content'] ?? 'No messages yet'); ?>
                            </p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <div class="chat-main">
                    <?php if ($activeContact): ?>
                    <div class="chat-header">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <div class="profile-avatar" style="background: var(--accent-purple);"><?php echo $activeContact['avatar']; ?></div>
                            <h4 style="color: var(--white);"><?php echo htmlspecialchars($activeContact['name']); ?></h4>
                        </div>
                    </div>
                    <div class="chat-body" id="chatBody">
                        <?php foreach ($chatHistory as $msg): ?>
                        <div class="msg <?php echo $msg['sender_id'] == $userId ? 'msg-sent' : 'msg-received'; ?>">
                            <?php echo htmlspecialchars($msg['content']); ?>
                            <span style="font-size: 0.7rem; display: block; margin-top: 5px; opacity: 0.7;"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($chatHistory)): ?>
                            <div style="text-align: center; color: var(--gray-500); margin-top: 50px;">Start a new conversation</div>
                        <?php endif; ?>
                    </div>
                    <form action="api/messages.php" method="POST" class="chat-input-area" id="chatForm">
                        <input type="hidden" name="receiver_id" value="<?php echo $activeContactId; ?>">
                        <input type="text" name="content" class="chat-input" placeholder="Type a message..." required>
                        <button type="submit" style="background: var(--accent-purple); border: none; color: #fff; width: 40px; height: 40px; border-radius: 50%; cursor: pointer;"><i class="fas fa-paper-plane"></i></button>
                    </form>
                    <?php else: ?>
                    <div style="flex: 1; display: flex; align-items: center; justify-content: center; color: var(--gray-500);">Select a contact to start messaging</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="js/main.js"></script>
    <script>
        const chatBody = document.getElementById('chatBody');
        if (chatBody) chatBody.scrollTop = chatBody.scrollHeight;
    </script>
    <script src="js/notifications.js"></script>
</body>
</html>
