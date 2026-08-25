<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];

// Fetch Recent Time Logs
$query = "
    SELECT tl.*, p.title as project_title 
    FROM time_logs tl 
    LEFT JOIN projects p ON tl.project_id = p.id 
    WHERE tl.user_id = ? 
    ORDER BY tl.log_date DESC, tl.created_at DESC 
    LIMIT 10
";
$stmt = $pdo->prepare($query);
$stmt->execute([$userId]);
$logs = $stmt->fetchAll();

// Fetch Projects for the dropdown
$projects = $pdo->query("SELECT id, title FROM projects ORDER BY title ASC")->fetchAll();

// Stats
$stats = [
    'week_total' => 0,
    'month_total' => 0,
    'billable' => 0
];

$weekStmt = $pdo->prepare("SELECT SUM(hours) FROM time_logs WHERE user_id = ? AND YEARWEEK(log_date, 1) = YEARWEEK(CURDATE(), 1)");
$weekStmt->execute([$userId]);
$stats['week_total'] = $weekStmt->fetchColumn() ?: 0;

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere Time Tracking - Log your work hours and manage project billing.">
    <title>Time Tracking | DataSphere Staff Portal</title>
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
        .time-logs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: var(--space-xl);
        }
        .time-logs-table th {
            text-align: left;
            padding: var(--space-md) var(--space-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--gray-500);
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .time-logs-table td {
            padding: var(--space-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.9rem;
            color: var(--gray-300);
        }
        .status-pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-approved { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .status-pending { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
    </style>
</head>

<body>
    <div class="dashboard-layout staff-dashboard">
        <!-- Sidebar -->
        <?php include 'php/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Time Tracking</h1>
                    <p style="color: var(--gray-400);">You've logged <?php echo $stats['week_total']; ?> hours this week. Keep it up!</p>
                </div>

                <div style="display: flex; align-items: center; gap: var(--space-md);">
                    <button class="btn btn-primary" onclick="document.getElementById('timeLogModal').style.display='flex'"><i class="fas fa-plus"></i> Manual Entry</button>
                    <div class="header-profile">
                        <div class="profile-avatar" style="background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-pink) 100%);">
                            <?php echo htmlspecialchars($_SESSION['user_avatar']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="stats-grid" style="margin-bottom: var(--space-2xl);">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $stats['week_total']; ?>h</div>
                    <div class="stat-label">This Week</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">34h</div>
                    <div class="stat-label">This Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">85%</div>
                    <div class="stat-label">Billable Utilization</div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="dashboard-card" style="padding: var(--space-xl);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                    <h3 style="font-size: 1.25rem;">Recent Logs</h3>
                    <select class="search-input" style="width: 200px;">
                        <option>All Projects</option>
                        <?php foreach ($projects as $proj): ?>
                        <option value="<?php echo $proj['id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <table class="time-logs-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Project</th>
                            <th>Description</th>
                            <th>Hours</th>
                            <th>Status</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: var(--gray-500);">No time logs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($log['log_date'])); ?></td>
                                <td style="color: var(--white); font-weight: 500;"><?php echo htmlspecialchars($log['project_title']); ?></td>
                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                                <td style="color: var(--accent-purple); font-weight: 600;"><?php echo $log['hours']; ?>h</td>
                                <td><span class="status-pill status-<?php echo $log['status']; ?>"><?php echo ucfirst($log['status']); ?></span></td>
                                <td style="text-align: right;"><i class="fas fa-edit" style="color: var(--gray-500); cursor: pointer;"></i></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Time Log Modal -->
    <div id="timeLogModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">New Time Log</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('timeLogModal').style.display='none'"></i>
            </div>
            <form id="quickTimeLogForm">
                <div class="form-group">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-input" required>
                        <?php foreach ($projects as $proj): ?>
                        <option value="<?php echo $proj['id']; ?>"><?php echo htmlspecialchars($proj['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hours</label>
                    <input type="number" step="0.5" name="hours" class="form-input" placeholder="e.g. 4.5" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" style="min-height: 100px;" placeholder="What did you work on?" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Save Log</button>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>
