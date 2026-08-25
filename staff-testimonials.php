<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];
$message = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $pdo->prepare("INSERT INTO testimonials (author_name, author_title, author_avatar, content, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['title'], $_POST['avatar'], $_POST['content'], 1]);
                $message = "Testimonial added successfully!";
                break;
            case 'toggle':
                $stmt = $pdo->prepare("UPDATE testimonials SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
            case 'delete':
                $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                $message = "Testimonial deleted.";
                break;
        }
    }
}

// Fetch Testimonials
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Testimonials | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-xl);
        }
        .admin-card {
            background: var(--dark-800);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            position: relative;
        }
        .admin-card.inactive {
            opacity: 0.6;
        }
        .card-actions {
            position: absolute;
            top: var(--space-md);
            right: var(--space-md);
            display: flex;
            gap: var(--space-sm);
        }
        .action-btn {
            background: rgba(255,255,255,0.05);
            border: none;
            color: var(--gray-400);
            width: 32px;
            height: 32px;
            border-radius: var(--radius-md);
            cursor: pointer;
            transition: all 0.3s;
        }
        .action-btn:hover {
            background: var(--primary-blue);
            color: white;
        }
        .action-btn.delete:hover {
            background: #ef4444;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .modal-content {
            background: var(--dark-900);
            padding: var(--space-2xl);
            border-radius: var(--radius-2xl);
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .form-group { margin-bottom: var(--space-md); }
        .form-group label { display: block; margin-bottom: var(--space-xs); color: var(--gray-400); }
        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            padding: var(--space-sm);
            border-radius: var(--radius-md);
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>

        <main class="dashboard-main">
            <?php 
            $header_title = "Client Testimonials";
            $header_subtitle = "Manage what our clients say on the homepage and services pages.";
            $header_actions = '<button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Add Testimonial</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <?php if ($message): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: var(--space-md); border-radius: var(--radius-lg); margin-bottom: var(--space-lg);">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="testimonial-grid">
                <?php foreach ($testimonials as $t): ?>
                <div class="admin-card <?php echo $t['is_active'] ? '' : 'inactive'; ?>">
                    <div class="card-actions">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <input type="hidden" name="action" value="toggle">
                            <button type="submit" class="action-btn" title="Toggle Visibility">
                                <i class="fas <?php echo $t['is_active'] ? 'fa-eye' : 'fa-eye-slash'; ?>"></i>
                            </button>
                        </form>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this testimonial?');">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="action-btn delete" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>

                    <p style="font-style: italic; color: var(--gray-300); margin-bottom: var(--space-lg);">"<?php echo htmlspecialchars($t['content']); ?>"</p>
                    
                    <div style="display: flex; align-items: center; gap: var(--space-md);">
                        <div style="width: 40px; height: 40px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                            <?php echo htmlspecialchars($t['author_avatar'] ?: substr($t['author_name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h4 style="margin: 0;"><?php echo htmlspecialchars($t['author_name']); ?></h4>
                            <p style="margin: 0; font-size: 0.85rem; color: var(--gray-400);"><?php echo htmlspecialchars($t['author_title']); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <h2 style="margin-bottom: var(--space-lg);">Add Testimonial</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Author Title (e.g. CEO, Company)</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Avatar Initials (e.g. JM)</label>
                    <input type="text" name="avatar" class="form-control" maxlength="2">
                </div>
                <div class="form-group">
                    <label>Testimonial Content</label>
                    <textarea name="content" class="form-control" rows="4" required></textarea>
                </div>
                <div style="display: flex; gap: var(--space-md); margin-top: var(--space-xl);">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()" style="flex: 1;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Save Testimonial</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() { document.getElementById('addModal').style.display = 'flex'; }
        function closeModal() { document.getElementById('addModal').style.display = 'none'; }
        window.onclick = function(event) {
            if (event.target == document.getElementById('addModal')) closeModal();
        }
    </script>
    <script src="js/notifications.js"></script>
</body>
</html>
