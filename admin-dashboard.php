<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole('admin', 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch Admin Stats
$stats = [
    'total_clients' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'client'")->fetchColumn(),
    'active_projects' => $pdo->query("SELECT COUNT(*) FROM projects WHERE status != 'completed'")->fetchColumn(),
    'team_members' => $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('staff', 'admin')")->fetchColumn(),
    'published_articles' => $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn(),
    'portfolio_items' => $pdo->query("SELECT COUNT(*) FROM portfolio_showcase")->fetchColumn(),
    'revenue_month' => $pdo->query("SELECT SUM(amount) FROM invoices WHERE status = 'paid' AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn() ?: 0
];

// Fetch Recent Users (Clients & Staff)
$recentUsers = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Fetch Project Status Distribution
$statusCounts = $pdo->query("SELECT status, COUNT(*) as count FROM projects GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);

// Fetch Department Distribution
$deptCounts = $pdo->query("SELECT department, COUNT(*) as count FROM users WHERE role != 'client' GROUP BY department")->fetchAll(PDO::FETCH_KEY_PAIR);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere Admin Dashboard - System management and oversight.">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Dashboard | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <?php include 'php/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <?php 
            $header_title = "Command Center 👋";
            $header_subtitle = "System-wide overview and administrative controls.";
            $header_actions = '<a href="api/export.php" class="btn btn-primary"><i class="fas fa-file-download"></i> Full System Report</a>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Stats Cards (Synchronized with client dashboard UI) -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%); color: var(--accent-purple);">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p>Total Clients</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(236, 72, 153, 0.2) 0%, rgba(236, 72, 153, 0.1) 100%); color: var(--accent-pink);">
                            <i class="fas fa-rocket"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['active_projects']; ?></h3>
                    <p>Active Projects</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%); color: var(--accent-green);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <h3><?php echo $stats['team_members']; ?></h3>
                    <p>Team Members</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon" style="background: linear-gradient(135deg, rgba(6, 182, 212, 0.2) 0%, rgba(6, 182, 212, 0.1) 100%); color: var(--accent-cyan);">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <h3><?php echo number_format($stats['revenue_month']); ?> TZS</h3>
                    <p>Revenue (MTD)</p>
                </div>
            </div>

            <!-- Content Grid (Synchronized with client dashboard UI) -->
            <div class="dashboard-content-grid">
                <!-- Recent Registrations -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Recent Registrations</h3>
                        <a href="admin-users.php" class="btn btn-secondary btn-sm">View All Users</a>
                    </div>

                    <div class="user-table-wrapper">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="text-align: left; color: var(--gray-500); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <th style="padding-bottom: 12px;">User</th>
                                    <th style="padding-bottom: 12px;">Role</th>
                                    <th style="padding-bottom: 12px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                <tr style="border-top: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td style="padding: 15px 0;">
                                        <div style="display: flex; align-items: center; gap: 12px;">
                                            <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; color: var(--white);">
                                                <?php echo htmlspecialchars($user['avatar'] ?: substr($user['name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($user['name']); ?></div>
                                                <div style="font-size: 0.75rem; color: var(--gray-500);"><?php echo htmlspecialchars($user['email']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 0;">
                                        <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: <?php 
                                            if($user['role'] === 'admin') echo 'rgba(139, 92, 246, 0.15); color: var(--accent-purple);';
                                            elseif($user['role'] === 'staff') echo 'rgba(0, 102, 255, 0.15); color: var(--primary-blue-light);';
                                            else echo 'rgba(16, 185, 129, 0.15); color: var(--accent-green);';
                                        ?>">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 15px 0;">
                                        <div style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--accent-green);">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></span>
                                            Active
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Project Oversight -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Project Oversight</h3>
                    </div>

                    <div class="status-summary">
                        <?php 
                        $statusMap = [
                            'planning' => ['label' => 'Planning', 'color' => 'var(--gray-400)', 'icon' => 'fa-pencil-ruler'],
                            'in-progress' => ['label' => 'In Progress', 'color' => 'var(--primary-blue-light)', 'icon' => 'fa-spinner'],
                            'review' => ['label' => 'Under Review', 'color' => 'var(--accent-purple)', 'icon' => 'fa-eye'],
                            'completed' => ['label' => 'Completed', 'color' => 'var(--accent-green)', 'icon' => 'fa-check-double']
                        ];
                        foreach ($statusMap as $key => $meta): 
                            $count = $statusCounts[$key] ?? 0;
                            $percent = $stats['active_projects'] > 0 ? ($count / ($stats['active_projects'] + ($statusCounts['completed'] ?? 0)) * 100) : 0;
                        ?>
                        <div style="margin-bottom: var(--space-xl);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fas <?php echo $meta['icon']; ?>" style="color: <?php echo $meta['color']; ?>; font-size: 0.9rem;"></i>
                                    <span style="color: var(--gray-300); font-size: 0.9rem;"><?php echo $meta['label']; ?></span>
                                </div>
                                <span style="font-weight: 700; font-size: 1rem;"><?php echo $count; ?></span>
                            </div>
                            <div style="height: 6px; background: var(--dark-700); border-radius: 3px; overflow: hidden;">
                                <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php echo $meta['color']; ?>; border-radius: 3px; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="margin-top: var(--space-2xl);">
                        <h3 style="font-size: 1.1rem; margin-bottom: var(--space-lg);">Quick Admin Actions</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='admin-users.php?add'">
                                <i class="fas fa-user-plus"></i> Add User
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-blog.php'">
                                <i class="fas fa-edit"></i> Write Article
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='staff-portfolio.php'">
                                <i class="fas fa-plus"></i> Showcase Work
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="window.location.href='admin-users.php'">
                                <i class="fas fa-users-cog"></i> Manage Users
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Activity -->
            <div style="margin-top: var(--space-xl);">
                <h3 style="font-size: 1.25rem; margin-bottom: var(--space-lg);">System-wide Quick Links</h3>
                <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                    <a href="finance-dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-wallet"></i> Finance & Invoices
                    </a>
                    <a href="hr-dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-user-tie"></i> HR Center
                    </a>
                    <a href="marketing-dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-bullhorn"></i> Marketing Center
                    </a>
                    <a href="staff-projects.php" class="btn btn-secondary">
                        <i class="fas fa-project-diagram"></i> Global Projects
                    </a>
                    <a href="admin-users.php" class="btn btn-secondary">
                        <i class="fas fa-users"></i> User Management
                    </a>
                    <a href="staff-testimonials.php" class="btn btn-secondary">
                        <i class="fas fa-quote-left"></i> Manage Testimonials
                    </a>
                    <a href="staff-settings.php" class="btn btn-secondary">
                        <i class="fas fa-cog"></i> System Settings
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
    <script src="js/notifications.js"></script>
</body>

</html>
