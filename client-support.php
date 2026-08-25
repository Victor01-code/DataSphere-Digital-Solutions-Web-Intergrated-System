<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all support tickets for this client
$tickets = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM support_tickets WHERE client_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $tickets = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table might not exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets | DataSphere Client Portal</title>
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
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Support Tickets</h1>
                    <p style="color: var(--gray-400);">Need help? Open a ticket or check the status of existing requests.</p>
                </div>
                <button class="btn btn-primary" onclick="openTicketModal()"><i class="fas fa-plus"></i> Open New Ticket</button>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--accent-green); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-lg); margin-top: var(--space-xl); display: flex; align-items: center; gap: var(--space-md);">
                    <i class="fas fa-check-circle"></i>
                    <p>Support ticket has been successfully opened! Our team will review it shortly.</p>
                </div>
            <?php endif; ?>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                    <h3 style="font-size: 1.25rem;">Active Support Requests</h3>
                </div>

                <?php if (empty($tickets)): ?>
                    <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                        <i class="fas fa-ticket-alt" style="font-size: 3rem; margin-bottom: var(--space-lg); opacity: 0.2;"></i>
                        <p>No support tickets found. We're here to help if you need anything!</p>
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
                                        <p style="font-size: 0.8rem; color: var(--gray-400);">ID: #TCK-<?php echo str_pad($ticket['id'], 5, '0', STR_PAD_LEFT); ?> • Opened on <?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: var(--space-xl);">
                                    <span style="font-size: 0.75rem; padding: 4px 10px; border-radius: 4px; background: rgba(255,255,255,0.05); color: var(--gray-300);">
                                        Priority: <span style="font-weight: 700; color: <?php echo $ticket['priority'] == 'high' || $ticket['priority'] == 'urgent' ? 'var(--accent-pink)' : 'var(--accent-green)'; ?>;"><?php echo ucfirst($ticket['priority']); ?></span>
                                    </span>
                                    <span class="status-badge <?php echo strtolower($ticket['status']); ?>" style="padding: 6px 14px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light);">
                                        <?php echo str_replace('-', ' ', $ticket['status']); ?>
                                    </span>
                                    <a href="client-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn btn-secondary btn-sm">View Thread</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- New Ticket Modal -->
    <div class="modal" id="ticketModal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h3>Open New Support Ticket</h3>
                <button class="modal-close" onclick="closeTicketModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="php/handle_ticket.php" method="POST" id="ticketForm">
                    <div class="form-group" style="margin-bottom: var(--space-lg);">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" class="form-input" placeholder="What do you need help with?" required>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: var(--space-lg);">
                        <label for="priority">Priority Level</label>
                        <select name="priority" id="priority" class="form-input" required>
                            <option value="low">Low - General Inquiry</option>
                            <option value="medium" selected>Medium - Normal Support</option>
                            <option value="high">High - Important Issue</option>
                            <option value="urgent">Urgent - Critical Blocker</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: var(--space-xl);">
                        <label for="description">Detailed Description</label>
                        <textarea name="description" id="description" class="form-input" rows="6" placeholder="Please provide as much detail as possible so we can help you faster..." required></textarea>
                    </div>

                    <div style="display: flex; gap: var(--space-md); justify-content: flex-end;">
                        <button type="button" class="btn btn-secondary" onclick="closeTicketModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Ticket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            padding: var(--space-md);
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: var(--dark-800);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-2xl);
            width: 100%;
            overflow: hidden;
            animation: modalIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .modal-header {
            padding: var(--space-xl);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-close {
            background: none;
            border: none;
            color: var(--gray-400);
            font-size: 1.5rem;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .modal-close:hover {
            color: var(--white);
        }
        .modal-body {
            padding: var(--space-xl);
        }
    </style>

    <script>
        function openTicketModal() {
            document.getElementById('ticketModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeTicketModal() {
            document.getElementById('ticketModal').classList.remove('active');
            document.body.style.overflow = '';
        }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
