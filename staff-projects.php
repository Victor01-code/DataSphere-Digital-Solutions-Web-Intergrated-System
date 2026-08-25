<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];

// Fetch All Projects with Member Info
$query = "
    SELECT p.*, GROUP_CONCAT(u.name SEPARATOR ', ') as members
    FROM projects p
    LEFT JOIN project_members pm ON p.id = pm.project_id
    LEFT JOIN users u ON pm.user_id = u.id
    GROUP BY p.id
    ORDER BY p.status ASC, p.due_date ASC
";
$user_role = $_SESSION['user_role'] ?? 'staff';
$user_dept = $_SESSION['user_department'] ?? 'General';
if ($user_role === 'staff') {
    $query = str_replace("GROUP BY p.id", "WHERE p.department = '$user_dept' GROUP BY p.id", $query);
}
$projects = $pdo->query($query)->fetchAll();

// Fetch clients for dropdown
$clientStmt = $pdo->query("SELECT id, name FROM users WHERE role = 'client'");
$clientsList = $clientStmt->fetchAll();

// Stats for projects
$stats = [
    'total' => count($projects),
    'in_progress' => 0,
    'completed' => 0,
    'review' => 0
];

foreach ($projects as $p) {
    if ($p['status'] == 'in-progress') $stats['in_progress']++;
    if ($p['status'] == 'completed') $stats['completed']++;
    if ($p['status'] == 'review') $stats['review']++;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere Project Management - Track and manage active client projects and deliverables.">
    <meta name="robots" content="noindex, nofollow">
    <title>Projects | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="dashboard-layout staff-dashboard">
        <!-- Sidebar -->
        <?php include 'php/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <?php 
            $header_title = "Project Management";
            $header_subtitle = "Overview of all active client projects and their current status.";
            $header_actions = '<button class="btn btn-primary" onclick="document.getElementById(\'addProjectModal\').style.display=\'flex\'"><i class="fas fa-plus"></i> New Project</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Project Filters & Search -->
            <div class="dashboard-card" style="margin-bottom: var(--space-xl); padding: var(--space-lg);">
                <div style="display: flex; justify-content: space-between; align-items: center; gap: var(--space-xl);">
                    <div style="display: flex; gap: var(--space-md);">
                        <button class="btn btn-secondary btn-sm active" style="background: var(--dark-800);">All (<?php echo $stats['total']; ?>)</button>
                        <button class="btn btn-secondary btn-sm">In Progress (<?php echo $stats['in_progress']; ?>)</button>
                        <button class="btn btn-secondary btn-sm">Review (<?php echo $stats['review']; ?>)</button>
                        <button class="btn btn-secondary btn-sm">Planning</button>
                        <button class="btn btn-secondary btn-sm">Completed (<?php echo $stats['completed']; ?>)</button>
                    </div>
                    <div class="search-box" style="flex: 1; max-width: 400px;">
                        <i class="fas fa-search"></i>
                        <input type="text" class="search-input" placeholder="Search projects by name, client or type...">
                    </div>
                </div>
            </div>

            <!-- Projects Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: var(--space-xl);">
                <?php foreach ($projects as $project): ?>
                <div class="dashboard-card" style="padding: var(--space-xl); display: flex; flex-direction: column; gap: var(--space-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <span style="font-size: 0.75rem; color: var(--accent-purple); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;"><?php echo htmlspecialchars($project['type']); ?></span>
                        <select onchange="updateProjectStatus(<?php echo $project['id']; ?>, this.value)" style="padding: 4px 12px; font-size: 0.75rem; border-radius: 12px; background: <?php echo $project['status'] == 'completed' ? 'rgba(16, 185, 129, 0.2); color: var(--accent-green);' : ($project['status'] == 'in-progress' ? 'rgba(139, 92, 246, 0.2); color: var(--accent-purple);' : 'rgba(245, 158, 11, 0.2); color: var(--accent-orange);'); ?> border: 1px solid rgba(255,255,255,0.1); font-weight: 600; cursor: pointer;">
                            <option value="planning" <?php echo $project['status'] == 'planning' ? 'selected' : ''; ?> style="background: var(--dark-800); color: white;">Planning</option>
                            <option value="in-progress" <?php echo $project['status'] == 'in-progress' ? 'selected' : ''; ?> style="background: var(--dark-800); color: white;">In Progress</option>
                            <option value="review" <?php echo $project['status'] == 'review' ? 'selected' : ''; ?> style="background: var(--dark-800); color: white;">Review</option>
                            <option value="completed" <?php echo $project['status'] == 'completed' ? 'selected' : ''; ?> style="background: var(--dark-800); color: white;">Completed</option>
                        </select>
                    </div>

                    <h3 style="font-size: 1.25rem; font-family: var(--font-display); color: var(--white);"><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p style="color: var(--gray-400); font-size: 0.9rem;">Client: <?php echo htmlspecialchars($project['client_name']); ?></p>

                    <div style="display: flex; flex-direction: column; gap: var(--space-sm);">
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem;">
                            <span style="color: var(--gray-400);">Progress</span>
                            <span style="color: var(--white); font-weight: 600;"><?php echo $project['progress']; ?>%</span>
                        </div>
                        <div class="progress-bar-bg" style="height: 8px;">
                            <div class="progress-bar-fill" style="width: <?php echo $project['progress']; ?>%; height: 100%;"></div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; padding-top: var(--space-md); border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-size: 0.75rem; color: var(--gray-500);">Due Date</span>
                            <span style="font-size: 0.85rem; color: var(--gray-300); font-weight: 500;"><i class="fas fa-calendar-alt" style="margin-right: 5px;"></i> <?php echo date('M d, Y', strtotime($project['due_date'])); ?></span>
                        </div>
                        <div style="display: flex; -webkit-mask-image: linear-gradient(to right, transparent, black 20%);">
                            <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 0.75rem; border: 2px solid var(--dark-800); margin-left: -10px; background: var(--accent-purple);">MO</div>
                            <div class="profile-avatar" style="width: 32px; height: 32px; font-size: 0.75rem; border: 2px solid var(--dark-800); margin-left: -10px; background: var(--accent-pink);">SK</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Project Modal -->
    <div id="addProjectModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">Create New Project</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('addProjectModal').style.display='none'"></i>
            </div>
            <form id="addProjectForm">
                <div class="form-group">
                    <label class="form-label">Project Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Website Redesign" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-input" style="appearance: auto;" required>
                        <option value="">-- Select Client --</option>
                        <?php foreach($clientsList as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Department</label>
                    <select name="department" class="form-input" style="appearance: auto; margin-bottom: 15px;">
                        <option value="General">General</option>
                        <option value="Design & Branding">Design & Branding</option>
                        <option value="Development">Development</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Digital Strategy">Digital Strategy</option>
                    </select>
                    <label class="form-label">Project Type</label>
                    <select name="type" class="form-input">
                        <option value="Web Development">Web Development</option>
                        <option value="Mobile App">Mobile App</option>
                        <option value="UI/UX Design">UI/UX Design</option>
                        <option value="Digital Marketing">Digital Marketing</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Description / Overview</label>
                    <textarea name="description" class="form-input" placeholder="What is this project about? Provide details for public showcase." required style="height: 80px;"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Showcase Image URL</label>
                    <input type="text" name="image_url" class="form-input" placeholder="e.g. assets/images/service1.png" value="assets/images/service1.png" required>
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <input type="checkbox" name="is_public" value="1" checked id="isPublicCheck" style="width: 18px; height: 18px; accent-color: var(--primary-blue);">
                    <label for="isPublicCheck" style="color: white; font-size: 0.9rem; cursor: pointer;">Publish to Public Showcase ("Powered by DataSphere")</label>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Initialize Project</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/notifications.js"></script>
    <script>
        function updateProjectStatus(id, status) {
            fetch('api/actions.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_project_status', project_id: id, status: status })
            })
            .then(r => r.json())
            .then(d => {
                showNotification(d.message, d.success ? 'success' : 'error');
                if(d.success) setTimeout(() => location.reload(), 1000);
            });
        }
    </script>
</body>
</html>
