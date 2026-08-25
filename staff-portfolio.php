<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

// Handle Project Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM portfolio_showcase WHERE id = ?")->execute([$id]);
    } elseif ($action === 'toggle_featured') {
        $pdo->prepare("UPDATE portfolio_showcase SET is_featured = NOT is_featured WHERE id = ?")->execute([$id]);
    }
    header('Location: staff-portfolio.php?message=updated');
    exit;
}

// Fetch Portfolio Items
$projects = $pdo->query("SELECT * FROM portfolio_showcase ORDER BY created_at DESC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio Management | DataSphere Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>

        <main class="dashboard-main">
            <?php 
            $header_title = "Portfolio Showcase";
            $header_subtitle = "Manage your public case studies and featured projects.";
            $header_actions = '<button class="btn btn-primary" onclick="document.getElementById(\'addPortfolioModal\').style.display=\'flex\'"><i class="fas fa-plus"></i> Add Case Study</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: var(--space-xl); margin-top: var(--space-xl);">
                <?php foreach ($projects as $project): ?>
                <div class="dashboard-card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
                    <div style="position: relative; height: 180px;">
                        <img src="<?php echo htmlspecialchars($project['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 8px;">
                            <a href="?action=toggle_featured&id=<?php echo $project['id']; ?>" class="btn btn-secondary btn-sm" style="background: rgba(10,14,23,0.8); border: none; color: <?php echo $project['is_featured'] ? 'var(--accent-cyan)' : 'white'; ?>;">
                                <i class="<?php echo $project['is_featured'] ? 'fas' : 'far'; ?> fa-star"></i>
                            </a>
                        </div>
                    </div>
                    <div style="padding: var(--space-xl); flex: 1; display: flex; flex-direction: column;">
                        <span style="font-size: 0.75rem; color: var(--accent-purple); font-weight: 700; text-transform: uppercase; margin-bottom: 5px;"><?php echo htmlspecialchars($project['category']); ?></span>
                        <h3 style="font-size: 1.2rem; color: white; margin-bottom: 10px;"><?php echo htmlspecialchars($project['title']); ?></h3>
                        <p style="font-size: 0.85rem; color: var(--gray-400); line-height: 1.6; margin-bottom: var(--space-xl); flex: 1;"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . '...'; ?></p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: var(--space-lg); border-top: 1px solid rgba(255,255,255,0.05);">
                            <span style="font-size: 0.8rem; color: var(--gray-500);"><?php echo htmlspecialchars($project['client_name']); ?></span>
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></button>
                                <a href="?action=delete&id=<?php echo $project['id']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Delete this case study?')" style="color: var(--accent-pink);"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Case Study Modal -->
    <div id="addPortfolioModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 700px; padding: var(--space-2xl); max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3>New Case Study</h3>
                <i class="fas fa-times" style="cursor: pointer;" onclick="document.getElementById('addPortfolioModal').style.display='none'"></i>
            </div>
            <form action="php/handlers/portfolio_handler.php" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Project Title</label>
                        <input type="text" name="title" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-input" required>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-input">
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile App">Mobile App</option>
                            <option value="Branding">Branding</option>
                            <option value="Digital Strategy">Digital Strategy</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tech Stack (comma separated)</label>
                        <input type="text" name="tech_stack" class="form-input" placeholder="e.g. PHP, React, MySQL">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">The Challenge</label>
                    <textarea name="challenge" class="form-input" style="height: 80px;" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Our Solution</label>
                    <textarea name="solution" class="form-input" style="height: 80px;" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Key Outcomes</label>
                    <input type="text" name="outcome" class="form-input" placeholder="e.g. 40% faster load times" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Case Study</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
