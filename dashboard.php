<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';

require_once 'php/auth.php';

requireRole('client');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch active service bookings
$stmt = $pdo->prepare("SELECT * FROM service_bookings WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Fetch shared root folders
$stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE client_id = ? AND parent_id IS NULL ORDER BY folder_name ASC");
$stmt->execute([$user_id]);
$root_folders = $stmt->fetchAll();

// Fetch shared root files
$stmt = $pdo->prepare("SELECT * FROM shared_files WHERE client_id = ? AND folder_id IS NULL ORDER BY created_at DESC LIMIT 6");
$stmt->execute([$user_id]);
$root_files = $stmt->fetchAll();

// Fetch projects
$stmt = $pdo->prepare("SELECT p.* FROM projects p 
                       JOIN project_members pm ON p.id = pm.project_id 
                       WHERE pm.user_id = ? ORDER BY p.created_at DESC");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="DataSphere Client Dashboard - Access your projects, track progress, and manage your files.">
    <meta name="robots" content="noindex, nofollow">

    <title>Dashboard | DataSphere Client Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

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
        <?php include 'php/includes/client_sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header -->
            <?php 
            $header_title = "Welcome back, " . explode(' ', $user_name)[0] . "! 👋";
            $header_subtitle = "Here's what's happening with your projects and bookings today.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Stats Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon"
                            style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(16, 185, 129, 0.1) 100%); color: var(--accent-green);">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                    <h3><?php echo count($projects); ?></h3>
                    <p>Active Projects</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon"
                            style="background: linear-gradient(135deg, rgba(0, 102, 255, 0.2) 0%, rgba(0, 102, 255, 0.1) 100%);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <h3><?php echo count($bookings); ?></h3>
                    <p>Service Bookings</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon"
                            style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0.1) 100%); color: var(--accent-orange);">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <h3>2</h3>
                    <p>Pending Invoices</p>
                </div>

                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <div class="dashboard-card-icon"
                            style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(139, 92, 246, 0.1) 100%); color: var(--accent-purple);">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                    </div>
                    <h3>1</h3>
                    <p>Open Tickets</p>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="dashboard-content-grid">
                <!-- Service Bookings Progress -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Service Booking Progress</h3>
                        <a href="services.php" class="btn btn-secondary btn-sm">Book New Service</a>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                        <?php if (empty($bookings)): ?>
                            <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                                <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: var(--space-md); opacity: 0.3;"></i>
                                <p>No active service bookings found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($bookings as $booking): ?>
                                <div style="background: var(--dark-700); border-radius: var(--radius-lg); padding: var(--space-lg);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
                                        <div style="display: flex; align-items: center; gap: var(--space-md);">
                                            <div style="width: 40px; height: 40px; background: var(--gradient-glow); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--primary-blue-light);">
                                                <i class="fas fa-concierge-bell"></i>
                                            </div>
                                            <div>
                                                <h4 style="margin-bottom: 2px;"><?php echo htmlspecialchars($booking['service_name']); ?></h4>
                                                <p style="font-size: 0.75rem; color: var(--gray-400);">Booked on <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
                                            </div>
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="status-badge <?php echo strtolower($booking['status']); ?>" style="padding: 4px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light);">
                                                <?php echo htmlspecialchars($booking['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div style="margin-bottom: var(--space-xs); display: flex; justify-content: space-between; font-size: 0.8rem;">
                                        <span>Progress</span>
                                        <span><?php echo $booking['progress']; ?>%</span>
                                    </div>
                                    <div style="height: 6px; background: var(--dark-600); border-radius: 3px; overflow: hidden;">
                                        <div style="height: 100%; width: <?php echo $booking['progress']; ?>%; background: var(--gradient-primary); border-radius: 3px; transition: width 0.5s ease;"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Shared Files / Received Work -->
                    <div style="margin-top: var(--space-2xl);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                            <h3 style="font-size: 1.25rem;">Shared Files & Deliverables</h3>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                            <?php if (empty($root_folders) && empty($root_files)): ?>
                                <div style="grid-column: span 2; text-align: center; padding: var(--space-xl); background: rgba(255,255,255,0.03); border-radius: var(--radius-lg); color: var(--gray-400);">
                                    <p>No shared items yet.</p>
                                </div>
                            <?php else: ?>
                                <!-- Folders -->
                                <?php foreach ($root_folders as $folder): ?>
                                    <a href="client-files.php?folder=<?php echo $folder['id']; ?>" style="text-decoration: none; color: inherit;">
                                        <div style="background: var(--dark-700); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: var(--space-md); display: flex; align-items: center; gap: var(--space-md);">
                                            <div style="font-size: 1.5rem; color: var(--primary-blue-light);">
                                                <i class="fas fa-folder"></i>
                                            </div>
                                            <div style="flex: 1; overflow: hidden;">
                                                <h4 style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($folder['folder_name']); ?></h4>
                                                <p style="font-size: 0.7rem; color: var(--gray-400);">Folder</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>

                                <!-- Files -->
                                <?php foreach ($root_files as $file): ?>
                                    <div style="background: var(--dark-700); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: var(--space-md); display: flex; align-items: center; gap: var(--space-md);">
                                        <div style="font-size: 1.5rem; color: var(--accent-cyan);">
                                            <?php 
                                            $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                            $icon = 'fa-file-alt';
                                            $color = 'var(--gray-400)';
                                            
                                            if (in_array($ext, ['pdf'])) { $icon = 'fa-file-pdf'; $color = '#ff4d4d'; }
                                            elseif (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'svg'])) { $icon = 'fa-file-image'; $color = '#4da3ff'; }
                                            elseif (in_array($ext, ['doc', 'docx'])) { $icon = 'fa-file-word'; $color = '#2b579a'; }
                                            elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) { $icon = 'fa-file-excel'; $color = '#217346'; }
                                            ?>
                                            <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                                        </div>
                                        <div style="flex: 1; overflow: hidden;">
                                            <h4 style="font-size: 0.85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                            <p style="font-size: 0.7rem; color: var(--gray-400);"><?php echo $file['file_size']; ?> • <?php echo $file['uploaded_by'] === 'staff' ? 'DataSphere' : 'You'; ?></p>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-secondary btn-sm" style="padding: 5px 10px;" download>
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity & Notifications -->
                <div class="dashboard-card" style="padding: var(--space-xl);">
                    <h3 style="font-size: 1.25rem; margin-bottom: var(--space-xl);">Project Updates</h3>

                    <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
                        <?php if (empty($projects)): ?>
                             <p style="color: var(--gray-400); font-size: 0.9rem;">No active projects.</p>
                        <?php else: ?>
                            <?php foreach ($projects as $project): ?>
                                <div style="display: flex; gap: var(--space-md);">
                                    <div style="width: 10px; height: 10px; background: var(--accent-green); border-radius: 50%; margin-top: 6px;"></div>
                                    <div>
                                        <p style="font-size: 0.95rem; margin-bottom: var(--space-xs);">
                                            <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                        </p>
                                        <p style="font-size: 0.8rem; color: var(--gray-400);"><?php echo $project['progress']; ?>% complete • <?php echo htmlspecialchars($project['status']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: var(--space-2xl);">
                        <h3 style="font-size: 1.1rem; margin-bottom: var(--space-lg);">Quick Support</h3>
                        <div style="background: var(--gradient-dark); border-radius: var(--radius-lg); padding: var(--space-md); border: 1px solid rgba(255,255,255,0.05);">
                            <p style="font-size: 0.85rem; color: var(--gray-300); margin-bottom: var(--space-md);">Chat with your project manager now.</p>
                            <a href="https://wa.me/255693038737" target="_blank" class="btn btn-primary btn-sm" style="width: 100%; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 8px;">
                                <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: var(--space-xl);">
                <h3 style="font-size: 1.25rem; margin-bottom: var(--space-lg);">Quick Actions</h3>
                <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                    <a href="client-bookings.php?new=true" class="btn btn-secondary">
                        <i class="fas fa-plus"></i> Book Service
                    </a>
                    <a href="client-files.php" class="btn btn-secondary">
                        <i class="fas fa-upload"></i> Upload Documents
                    </a>
                    <a href="client-support.php" class="btn btn-secondary">
                        <i class="fas fa-calendar"></i> Schedule Consultation
                    </a>
                    <a href="client-files.php" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Latest Deliverables
                    </a>
                </div>
            </div>
        </main>
    </div>

    <!-- WhatsApp Support -->
    <a href="https://wa.me/255693038737" class="whatsapp-btn" target="_blank" rel="noopener"
        aria-label="Chat on WhatsApp" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #25d366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); z-index: 1000; text-decoration: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="js/notifications.js"></script>
</body>

</html>
>


