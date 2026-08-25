<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$client_id = $_GET['client_id'] ?? null;
$current_folder_id = $_GET['folder'] ?? null;

// Fetch all clients
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'client' ORDER BY name ASC");
$clients = $stmt->fetchAll();

$current_folder = null;
$folders = [];
$files = [];
$breadcrumbs = [];
$selected_client_name = "";

if ($client_id) {
    // Find client name
    foreach ($clients as $c) {
        if ($c['id'] == $client_id) {
            $selected_client_name = $c['name'];
            break;
        }
    }

    if ($current_folder_id) {
        $stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE id = ? AND client_id = ?");
        $stmt->execute([$current_folder_id, $client_id]);
        $current_folder = $stmt->fetch();
    }

    // Fetch subfolders
    $stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE client_id = ? AND parent_id " . ($current_folder_id ? "= ?" : "IS NULL") . " ORDER BY folder_name ASC");
    if ($current_folder_id) {
        $stmt->execute([$client_id, $current_folder_id]);
    } else {
        $stmt->execute([$client_id]);
    }
    $folders = $stmt->fetchAll();

    // Fetch files in current folder
    $stmt = $pdo->prepare("SELECT * FROM shared_files WHERE client_id = ? AND folder_id " . ($current_folder_id ? "= ?" : "IS NULL") . " ORDER BY created_at DESC");
    if ($current_folder_id) {
        $stmt->execute([$client_id, $current_folder_id]);
    } else {
        $stmt->execute([$client_id]);
    }
    $files = $stmt->fetchAll();

    // Breadcrumbs
    $temp_folder = $current_folder;
    while ($temp_folder) {
        array_unshift($breadcrumbs, $temp_folder);
        if ($temp_folder['parent_id']) {
            $stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE id = ?");
            $stmt->execute([$temp_folder['parent_id']]);
            $temp_folder = $stmt->fetch();
        } else {
            $temp_folder = null;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Shared Files | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Client Shared Files";
            $header_subtitle = "Access and share documents with clients.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div style="display: flex; gap: var(--space-xl); margin-top: var(--space-xl);">
                <!-- Sidebar for Clients -->
                <div style="width: 250px; flex-shrink: 0;">
                    <div class="dashboard-card" style="padding: var(--space-lg);">
                        <h3 style="margin-bottom: var(--space-md); font-size: 1.1rem;">Clients</h3>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <?php foreach ($clients as $c): ?>
                                <a href="staff-client-files.php?client_id=<?php echo $c['id']; ?>" class="btn <?php echo $client_id == $c['id'] ? 'btn-primary' : 'btn-secondary'; ?>" style="justify-content: flex-start; text-align: left;">
                                    <i class="fas fa-user" style="margin-right: 8px;"></i> <?php echo htmlspecialchars($c['name']); ?>
                                </a>
                            <?php endforeach; ?>
                            <?php if (empty($clients)): ?>
                                <p style="color: var(--gray-500); font-size: 0.9rem;">No clients found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Main File Area -->
                <div style="flex: 1;">
                    <div class="dashboard-card" style="padding: var(--space-xl);">
                        <?php if (!$client_id): ?>
                            <div style="text-align: center; padding: 60px 20px; color: var(--gray-400);">
                                <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                                <p>Please select a client from the list to view their shared files.</p>
                            </div>
                        <?php else: ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
                                <div>
                                    <h3 style="font-size: 1.25rem;">
                                        <?php echo $current_folder ? htmlspecialchars($current_folder['folder_name']) : 'Files for ' . htmlspecialchars($selected_client_name); ?>
                                    </h3>
                                    <div style="display: flex; align-items: center; gap: var(--space-sm); color: var(--gray-400); margin-top: 4px; font-size: 0.9rem;">
                                        <a href="staff-client-files.php?client_id=<?php echo $client_id; ?>" style="color: var(--primary-blue-light); text-decoration: none;">Root</a>
                                        <?php foreach ($breadcrumbs as $crumb): ?>
                                            <i class="fas fa-chevron-right" style="font-size: 0.7rem; opacity: 0.5;"></i>
                                            <a href="staff-client-files.php?client_id=<?php echo $client_id; ?>&folder=<?php echo $crumb['id']; ?>" style="color: var(--primary-blue-light); text-decoration: none;"><?php echo htmlspecialchars($crumb['folder_name']); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div style="display: flex; gap: var(--space-sm);">
                                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('newFolderModal').style.display='flex'">
                                        <i class="fas fa-folder-plus"></i> New Folder
                                    </button>
                                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').style.display='flex'">
                                        <i class="fas fa-upload"></i> Share File
                                    </button>
                                </div>
                            </div>

                            <?php if (isset($_GET['success'])): ?>
                                <div style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg);">
                                    Action completed successfully.
                                </div>
                            <?php endif; ?>

                            <!-- Files and Folders Grid -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--space-md);">
                                <!-- Subfolders -->
                                <?php foreach ($folders as $folder): ?>
                                    <a href="staff-client-files.php?client_id=<?php echo $client_id; ?>&folder=<?php echo $folder['id']; ?>" style="text-decoration: none; color: inherit;">
                                        <div style="background: var(--dark-700); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: center; gap: var(--space-lg); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                                            <div style="width: 48px; height: 48px; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                                <i class="fas fa-folder"></i>
                                            </div>
                                            <div style="flex: 1; overflow: hidden;">
                                                <h4 style="font-size: 1rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($folder['folder_name']); ?></h4>
                                                <p style="font-size: 0.75rem; color: var(--gray-500);">Folder</p>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>

                                <!-- Files -->
                                <?php foreach ($files as $file): ?>
                                    <div style="background: var(--dark-700); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: center; gap: var(--space-lg);">
                                        <div style="width: 48px; height: 48px; background: rgba(6, 182, 212, 0.1); color: var(--accent-cyan); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                                            <?php 
                                            $ext = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                            $icon = 'fa-file-alt';
                                            $color = 'var(--gray-400)';
                                            
                                            if (in_array($ext, ['pdf'])) { $icon = 'fa-file-pdf'; $color = '#ff4d4d'; }
                                            elseif (in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'svg'])) { $icon = 'fa-file-image'; $color = '#4da3ff'; }
                                            elseif (in_array($ext, ['doc', 'docx'])) { $icon = 'fa-file-word'; $color = '#2b579a'; }
                                            elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) { $icon = 'fa-file-excel'; $color = '#217346'; }
                                            elseif (in_array($ext, ['zip', 'rar', '7z'])) { $icon = 'fa-file-archive'; $color = '#ffc107'; }
                                            elseif (in_array($ext, ['mp4', 'mov', 'avi'])) { $icon = 'fa-file-video'; $color = '#e91e63'; }
                                            ?>
                                            <i class="fas <?php echo $icon; ?>" style="color: <?php echo $color; ?>;"></i>
                                        </div>
                                        <div style="flex: 1; min-width: 0;">
                                            <h4 style="font-size: 1rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                            <p style="font-size: 0.8rem; color: var(--gray-400);"><?php echo $file['file_size']; ?> • <?php echo $file['uploaded_by'] === 'staff' ? 'Staff' : 'Client'; ?></p>
                                        </div>
                                        <div style="display: flex; gap: var(--space-xs);">
                                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="btn btn-secondary btn-sm" style="padding: 8px;" download title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (empty($folders) && empty($files)): ?>
                                    <div style="grid-column: 1 / -1; text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                                        <p>This folder is empty.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php if ($client_id): ?>
    <!-- New Folder Modal -->
    <div id="newFolderModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 400px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">New Folder</h3>
                <button onclick="document.getElementById('newFolderModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/create_folder.php" method="POST">
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                <input type="hidden" name="parent_id" value="<?php echo $current_folder_id; ?>">
                <input type="hidden" name="is_staff_upload" value="1">
                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label">Folder Name</label>
                    <input type="text" name="folder_name" class="form-input" placeholder="e.g. Project Assets" required autofocus>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('newFolderModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Upload File Modal -->
    <div id="uploadModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">Upload Document</h3>
                <button onclick="document.getElementById('uploadModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/handle_upload.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                <input type="hidden" name="folder_id" value="<?php echo $current_folder_id; ?>">
                <input type="hidden" name="is_staff_upload" value="1">
                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label">Select File</label>
                    <input type="file" name="document" class="form-input" required style="padding: var(--space-md);">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('uploadModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Start Upload</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="js/main.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
