<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

// Fetch all staff members
$query = "
    SELECT u.*, COUNT(pm.project_id) as project_count
    FROM users u
    LEFT JOIN project_members pm ON u.id = pm.user_id
    WHERE u.role IN ('staff', 'admin')
    GROUP BY u.id
    ORDER BY u.name ASC
";
$team = $pdo->query($query)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="DataSphere Team Directory - Connect with team members and view availability.">
    <title>Team Directory | DataSphere Staff Portal</title>
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
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: var(--space-xl);
        }
        .member-card {
            background: var(--dark-700);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: var(--radius-xl);
            padding: var(--space-xl);
            text-align: center;
            transition: all 0.3s;
            position: relative;
        }
        .member-card:hover {
            transform: translateY(-5px);
            border-color: rgba(139, 92, 246, 0.3);
            background: var(--dark-600);
        }
        .member-avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto var(--space-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 700;
            color: var(--white);
            background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-pink) 100%);
            position: relative;
        }
        .status-dot-lg {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid var(--dark-700);
            background: #10b981;
        }
        .member-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: var(--space-lg);
            padding-top: var(--space-lg);
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }
        .member-stat-val { font-weight: 700; color: var(--white); display: block; }
        .member-stat-label { font-size: 0.75rem; color: var(--gray-500); text-transform: uppercase; }
        .member-contact {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: var(--space-md);
        }
        .contact-icon {
            color: var(--gray-400);
            transition: color 0.3s;
            font-size: 1.1rem;
        }
        .contact-icon:hover { color: var(--accent-purple); }
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
                    <h1 style="font-size: 1.75rem;">Team Directory</h1>
                    <p style="color: var(--gray-400);">Meet the creative minds behind DataSphere Digital Solutions.</p>
                </div>

                <div style="display: flex; align-items: center; gap: var(--space-md);">
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin-users.php?add" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Member
                    </a>
                    <?php endif; ?>
                    <div class="header-profile">
                        <div class="profile-avatar" style="background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-pink) 100%);">
                            <?php echo htmlspecialchars($_SESSION['user_avatar']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Grid -->
            <div class="team-grid">
                <?php foreach ($team as $member): ?>
                <div class="member-card">
                    <div class="member-avatar-lg">
                        <?php echo htmlspecialchars($member['avatar']); ?>
                        <span class="status-dot-lg" style="background: <?php echo $member['id'] == 1 || $member['id'] == 3 ? '#10b981' : '#f59e0b'; ?>"></span>
                    </div>
                    <h3 style="margin-bottom: 4px; color: var(--white);"><?php echo htmlspecialchars($member['name']); ?></h3>
                    <p style="color: var(--accent-purple); font-size: 0.9rem; font-weight: 500; margin-bottom: 2px;"><?php echo htmlspecialchars($member['title']); ?></p>
                    <p style="color: var(--gray-400); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: var(--space-md);"><?php echo htmlspecialchars($member['department'] ?? 'General'); ?></p>
                    
                    <div class="member-contact">
                        <a href="mailto:<?php echo $member['email']; ?>" class="contact-icon"><i class="fas fa-envelope"></i></a>
                        <a href="#" class="contact-icon"><i class="fab fa-slack"></i></a>
                        <a href="#" class="contact-icon"><i class="fab fa-linkedin"></i></a>
                    </div>

                    <div class="member-stats">
                        <div>
                            <span class="member-stat-val"><?php echo $member['project_count']; ?></span>
                            <span class="member-stat-label">Projects</span>
                        </div>
                        <div>
                            <span class="member-stat-val"><?php echo $member['id'] == 1 ? '98%' : '92%'; ?></span>
                            <span class="member-stat-label">Availability</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
    </div>

    <script src="js/notifications.js"></script>
    <script src="js/main.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>

