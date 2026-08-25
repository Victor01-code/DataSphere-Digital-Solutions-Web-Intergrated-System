<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];

$user_role = $_SESSION['user_role'] ?? 'staff';
$user_dept = $_SESSION['user_department'] ?? 'General';

// Fetch projects for the task modal - Filter by department for staff
if ($user_role === 'staff') {
    $projectsStmt = $pdo->prepare("SELECT id, title FROM projects WHERE department = ?");
    $projectsStmt->execute([$user_dept]);
} else {
    $projectsStmt = $pdo->query("SELECT id, title FROM projects");
}
$userProjects = $projectsStmt->fetchAll();

// Fetch Tasks - For staff, show assigned tasks OR department projects tasks. For admins, show all.
if ($user_role === 'staff') {
    $query = "
        SELECT t.*, p.title as project_title 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        WHERE t.assigned_to = :uid OR p.department = :dept
        ORDER BY t.status ASC, t.due_date ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['uid' => $userId, 'dept' => $user_dept]);
} else {
    $query = "
        SELECT t.*, p.title as project_title 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id 
        ORDER BY t.status ASC, t.due_date ASC
    ";
    $stmt = $pdo->query($query);
}
$tasks = $stmt->fetchAll();

// Group tasks
$overdue = [];
$today = [];
$upcoming = [];
$completed = [];

$now = new DateTime();
$todayDate = $now->format('Y-m-d');

foreach ($tasks as $task) {
    if ($task['status'] === 'completed') {
        $completed[] = $task;
        continue;
    }

    $dueDate = new DateTime($task['due_date']);
    $dueStr = $dueDate->format('Y-m-d');

    if ($dueStr < $todayDate) {
        $overdue[] = $task;
    } elseif ($dueStr === $todayDate) {
        $today[] = $task;
    } else {
        $upcoming[] = $task;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere My Tasks - Manage your personal workload and track progress on assigned project tasks.">
    <title>My Tasks | DataSphere Staff Portal</title>
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
    <style>
        .task-group { margin-bottom: var(--space-2xl); }
        .task-group-title {
            font-size: 1.1rem;
            color: var(--white);
            margin-bottom: var(--space-lg);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .task-row {
            display: grid;
            grid-template-columns: 40px 1fr 200px 150px 120px 60px;
            align-items: center;
            padding: var(--space-lg);
            background: var(--dark-700);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-md);
            transition: all 0.3s;
        }
        .task-row:hover {
            border-color: rgba(139, 92, 246, 0.3);
            background: var(--dark-600);
        }
        .task-row.overdue-item { border-left: 4px solid #f43f5e; }
        .task-row.completed-item { opacity: 0.6; }
        .task-due.overdue { color: #f43f5e; font-weight: 600; }
    </style>
</head>

<body>
    <div class="dashboard-layout staff-dashboard">
        <!-- Sidebar -->
        <?php include 'php/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <?php 
            $header_title = "My Task List";
            $header_subtitle = "You have " . (count($overdue) + count($today)) . " priority tasks to address today.";
            $header_actions = '<button class="btn btn-primary" onclick="document.getElementById(\'addTaskModal\').style.display=\'flex\'"><i class="fas fa-plus"></i> Add Task</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Overdue Tasks -->
            <?php if (!empty($overdue)): ?>
            <div class="task-group">
                <h3 class="task-group-title" style="color: #f43f5e;"><i class="fas fa-exclamation-triangle"></i> Overdue</h3>
                <?php foreach ($overdue as $task): ?>
                <div class="task-row overdue-item <?php echo $task['status'] === 'completed' ? 'completed-item' : ''; ?>">
                    <input type="checkbox" class="task-checkbox" data-id="<?php echo $task['id']; ?>" <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>>
                    <div class="task-info">
                        <h4 style="margin-bottom: 4px;"><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p style="font-size: 0.85rem; color: var(--gray-400);"><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                    <div class="task-project"><?php echo htmlspecialchars($task['project_title']); ?></div>
                    <div class="task-due overdue"><i class="fas fa-clock"></i> <?php echo date('M d', strtotime($task['due_date'])); ?></div>
                    <span class="priority-badge priority-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                    <div style="text-align: right;"><i class="fas fa-ellipsis-v" style="color: var(--gray-500); cursor: pointer;"></i></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Tasks for Today -->
            <div class="task-group">
                <h3 class="task-group-title" style="color: var(--accent-purple);"><i class="fas fa-calendar-day"></i> Due Today</h3>
                <?php foreach ($today as $task): ?>
                <div class="task-row <?php echo $task['status'] === 'completed' ? 'completed-item' : ''; ?>">
                    <input type="checkbox" class="task-checkbox" data-id="<?php echo $task['id']; ?>" <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>>
                    <div class="task-info">
                        <h4 style="margin-bottom: 4px;"><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p style="font-size: 0.85rem; color: var(--gray-400);"><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                    <div class="task-project"><?php echo htmlspecialchars($task['project_title']); ?></div>
                    <div class="task-due" style="color: var(--accent-purple); font-weight: 600;">Today</div>
                    <span class="priority-badge priority-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                    <div style="text-align: right;"><i class="fas fa-ellipsis-v" style="color: var(--gray-500); cursor: pointer;"></i></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($today)): ?>
                    <p style="color: var(--gray-500); margin-bottom: var(--space-xl);">No tasks due today.</p>
                <?php endif; ?>
            </div>

            <!-- Upcoming Tasks -->
            <div class="task-group">
                <h3 class="task-group-title"><i class="fas fa-calendar-alt"></i> Upcoming</h3>
                <?php foreach ($upcoming as $task): ?>
                <div class="task-row <?php echo $task['status'] === 'completed' ? 'completed-item' : ''; ?>">
                    <input type="checkbox" class="task-checkbox" data-id="<?php echo $task['id']; ?>" <?php echo $task['status'] === 'completed' ? 'checked' : ''; ?>>
                    <div class="task-info">
                        <h4 style="margin-bottom: 4px;"><?php echo htmlspecialchars($task['title']); ?></h4>
                        <p style="font-size: 0.85rem; color: var(--gray-400);"><?php echo htmlspecialchars($task['description']); ?></p>
                    </div>
                    <div class="task-project"><?php echo htmlspecialchars($task['project_title']); ?></div>
                    <div class="task-due" style="color: var(--gray-400);"><?php echo date('M d', strtotime($task['due_date'])); ?></div>
                    <span class="priority-badge priority-<?php echo $task['priority']; ?>"><?php echo ucfirst($task['priority']); ?></span>
                    <div style="text-align: right;"><i class="fas fa-ellipsis-v" style="color: var(--gray-500); cursor: pointer;"></i></div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add Task Modal -->
    <div id="addTaskModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">Add New Task</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('addTaskModal').style.display='none'"></i>
            </div>
            <form id="addTaskForm">
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-input" required>
                        <?php foreach ($userProjects as $proj): ?>
                        <option value="<?php echo $proj['id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Task Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Design Login Page" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" style="min-height: 80px;" placeholder="Task details..."></textarea>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Due Date</label>
                        <input type="datetime-local" name="due_date" class="form-input" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Task</button>
            </form>
        </div>
    </div>

    <script src="js/notifications.js"></script>
</body>
</html>
