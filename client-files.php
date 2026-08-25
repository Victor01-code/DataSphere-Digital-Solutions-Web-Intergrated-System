<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];

// Current folder context
$current_folder_id = $_GET['folder'] ?? null;
$current_folder = null;

if ($current_folder_id) {
    $stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE id = ? AND client_id = ?");
    $stmt->execute([$current_folder_id, $user_id]);
    $current_folder = $stmt->fetch();
}

// Fetch subfolders
$stmt = $pdo->prepare("SELECT * FROM shared_folders WHERE client_id = ? AND parent_id " . ($current_folder_id ? "= ?" : "IS NULL") . " ORDER BY folder_name ASC");
if ($current_folder_id) {
    $stmt->execute([$user_id, $current_folder_id]);
} else {
    $stmt->execute([$user_id]);
}
$folders = $stmt->fetchAll();

// Fetch files in current folder
$stmt = $pdo->prepare("SELECT * FROM shared_files WHERE client_id = ? AND folder_id " . ($current_folder_id ? "= ?" : "IS NULL") . " ORDER BY created_at DESC");
if ($current_folder_id) {
    $stmt->execute([$user_id, $current_folder_id]);
} else {
    $stmt->execute([$user_id]);
}
$files = $stmt->fetchAll();

// Breadcrumbs
$breadcrumbs = [];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared Files | DataSphere Client Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/client_sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Shared Files</h1>
                    <div style="display: flex; align-items: center; gap: var(--space-sm); color: var(--gray-400); margin-top: 4px;">
                        <a href="client-files.php" style="color: var(--primary-blue-light); text-decoration: none;">Root</a>
                        <?php foreach ($breadcrumbs as $crumb): ?>
                            <i class="fas fa-chevron-right" style="font-size: 0.7rem; opacity: 0.5;"></i>
                            <a href="client-files.php?folder=<?php echo $crumb['id']; ?>" style="color: var(--primary-blue-light); text-decoration: none;"><?php echo htmlspecialchars($crumb['folder_name']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
                    <h3 style="font-size: 1.25rem;">
                        <?php echo $current_folder ? htmlspecialchars($current_folder['folder_name']) : 'All Documents'; ?>
                    </h3>
                    <div style="display: flex; gap: var(--space-sm);">
                        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('newFolderModal').style.display='flex'">
                            <i class="fas fa-folder-plus"></i> New Folder
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadFolderModal').style.display='flex'">
                            <i class="fas fa-folder-open"></i> Upload Folder
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="document.getElementById('uploadModal').style.display='flex'">
                            <i class="fas fa-upload"></i> Upload File
                        </button>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg);">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg);">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Files and Folders Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--space-md);">
                    <!-- Subfolders -->
                    <?php foreach ($folders as $folder): ?>
                        <a href="client-files.php?folder=<?php echo $folder['id']; ?>" style="text-decoration: none; color: inherit;">
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
                            <div style="flex: 1; overflow: hidden;">
                                <h4 style="font-size: 1rem; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                <p style="font-size: 0.8rem; color: var(--gray-400);"><?php echo $file['file_size']; ?> • <?php echo $file['uploaded_by'] === 'staff' ? 'DataSphere' : 'You'; ?></p>
                            </div>
                            <div style="display: flex; gap: var(--space-xs);">
                                <?php if (in_array($ext, ['jpg', 'png', 'jpeg', 'pdf'])): ?>
                                    <button class="btn btn-secondary btn-sm" style="padding: 8px;" onclick="previewFile('<?php echo htmlspecialchars($file['file_path']); ?>', '<?php echo $ext; ?>')" title="Quick View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                <?php endif; ?>
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
            </div>
        </main>
    </div>

    <!-- New Folder Modal -->
    <div id="newFolderModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 400px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">New Folder</h3>
                <button onclick="document.getElementById('newFolderModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/create_folder.php" method="POST">
                <input type="hidden" name="parent_id" value="<?php echo $current_folder_id; ?>">
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
                <input type="hidden" name="folder_id" value="<?php echo $current_folder_id; ?>">
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

    <!-- Upload Folder Modal -->
    <div id="uploadFolderModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">Upload Folder</h3>
                <button onclick="document.getElementById('uploadFolderModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/handle_upload.php" method="POST" enctype="multipart/form-data" id="folderUploadForm">
                <input type="hidden" name="folder_id" value="<?php echo $current_folder_id; ?>">
                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label">Select Folder</label>
                    <input type="file" name="folder_files[]" class="form-input" webkitdirectory mozdirectory directory multiple required style="padding: var(--space-md);" onchange="updatePaths(this)">
                    <div id="pathContainer"></div>
                    <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 8px;">This will upload all files within the selected folder.</p>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('uploadFolderModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload Everything</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); display: none; align-items: center; justify-content: center; z-index: 4000; padding: var(--space-xl);">
        <div style="position: relative; width: 100%; max-width: 1000px; height: 90vh; background: var(--dark-800); border-radius: var(--radius-xl); overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: var(--space-lg); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="previewTitle" style="font-size: 1.1rem;">File Preview</h3>
                <button onclick="document.getElementById('previewModal').style.display='none'" style="background: none; border: none; color: var(--white); font-size: 1.5rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <div id="previewContent" style="flex: 1; background: #000; display: flex; align-items: center; justify-content: center;">
                <!-- Preview content will be injected here -->
            </div>
        </div>
    </div>

    <script>
    function previewFile(path, ext) {
        const modal = document.getElementById('previewModal');
        const content = document.getElementById('previewContent');
        const title = document.getElementById('previewTitle');
        
        content.innerHTML = '';
        title.innerText = path.split('/').pop();
        
        if (['jpg', 'png', 'jpeg', 'gif', 'svg'].includes(ext)) {
            content.innerHTML = `<img src="${path}" style="max-width: 100%; max-height: 100%; object-fit: contain;">`;
        } else if (ext === 'pdf') {
            content.innerHTML = `<iframe src="${path}" style="width: 100%; height: 100%; border: none;"></iframe>`;
        }
        
        modal.style.display = 'flex';
    }
    function updatePaths(input) {
        const container = document.getElementById('pathContainer');
        container.innerHTML = '';
        for (let i = 0; i < input.files.length; i++) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'full_paths[]';
            hidden.value = input.files[i].webkitRelativePath;
            container.appendChild(hidden);
        }
    }
    </script>
    <script src="js/main.js"></script>
</body>
</html>
