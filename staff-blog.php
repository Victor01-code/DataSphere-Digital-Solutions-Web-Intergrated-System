<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

// Handle Post Actions (Delete/Publish/Draft)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'publish') {
        $pdo->prepare("UPDATE blog_posts SET status = 'published' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'draft') {
        $pdo->prepare("UPDATE blog_posts SET status = 'draft' WHERE id = ?")->execute([$id]);
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM blog_posts WHERE id = ?")->execute([$id]);
    }
    header('Location: staff-blog.php?message=updated');
    exit;
}

// Fetch Blog Posts
$posts = $pdo->query("
    SELECT b.*, u.name as author_name 
    FROM blog_posts b 
    LEFT JOIN users u ON b.author_id = u.id 
    ORDER BY b.created_at DESC
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Management | DataSphere Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>

        <main class="dashboard-main">
            <?php 
            $header_title = "Blog Management";
            $header_subtitle = "Manage your marketing content and industry insights.";
            $header_actions = '<button class="btn btn-primary" onclick="document.getElementById(\'addPostModal\').style.display=\'flex\'"><i class="fas fa-plus"></i> Create Post</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-card" style="margin-top: var(--space-xl); padding: 0; overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: rgba(255,255,255,0.02);">
                        <tr style="text-align: left; color: var(--gray-500); font-size: 0.8rem; text-transform: uppercase;">
                            <th style="padding: 20px;">Article Title</th>
                            <th style="padding: 20px;">Category</th>
                            <th style="padding: 20px;">Author</th>
                            <th style="padding: 20px;">Status</th>
                            <th style="padding: 20px;">Created</th>
                            <th style="padding: 20px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr style="border-top: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 20px;">
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" style="width: 50px; height: 35px; object-fit: cover; border-radius: 4px;">
                                    <span style="font-weight: 600; color: white;"><?php echo htmlspecialchars($post['title']); ?></span>
                                </div>
                            </td>
                            <td style="padding: 20px; color: var(--gray-400);"><?php echo htmlspecialchars($post['category']); ?></td>
                            <td style="padding: 20px; color: var(--gray-400);"><?php echo htmlspecialchars($post['author_name']); ?></td>
                            <td style="padding: 20px;">
                                <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: <?php echo $post['status'] === 'published' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(255, 255, 255, 0.05)'; ?>; color: <?php echo $post['status'] === 'published' ? 'var(--accent-green)' : 'var(--gray-400)'; ?>;">
                                    <?php echo htmlspecialchars($post['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 20px; color: var(--gray-500); font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                            <td style="padding: 20px; text-align: right;">
                                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                    <?php if ($post['status'] === 'draft'): ?>
                                        <a href="?action=publish&id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" title="Publish"><i class="fas fa-upload"></i></a>
                                    <?php else: ?>
                                        <a href="?action=draft&id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" title="Revert to Draft"><i class="fas fa-undo"></i></a>
                                    <?php endif; ?>
                                    <button class="btn btn-secondary btn-sm" title="Edit"><i class="fas fa-edit"></i></button>
                                    <a href="?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Are you sure?')" style="color: var(--accent-pink);"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Create Post Modal -->
    <div id="addPostModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 600px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3>Create New Article</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="document.getElementById('addPostModal').style.display='none'"></i>
            </div>
            <form action="php/handlers/blog_handler.php" method="POST">
                <div class="form-group">
                    <label class="form-label">Article Title</label>
                    <input type="text" name="title" class="form-input" placeholder="Enter a compelling title" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-input">
                            <option value="Technology">Technology</option>
                            <option value="Development">Development</option>
                            <option value="Branding">Branding</option>
                            <option value="Business">Business</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Read Time (min)</label>
                        <input type="text" name="read_time" class="form-input" placeholder="e.g. 5 min read">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Article Summary</label>
                    <textarea name="summary" class="form-input" style="height: 80px;" placeholder="Brief overview for the card..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Full Content (Markdown/HTML)</label>
                    <textarea name="content" class="form-input" style="height: 150px;" required placeholder="Write your full article here..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Publish Article</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
