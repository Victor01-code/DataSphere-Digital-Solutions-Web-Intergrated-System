<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$current_folder_id = isset($_GET['folder']) ? (int)$_GET['folder'] : null;

// Fetch documents and folders
$query = "
    SELECT d.*, u.name as owner, p.title as project
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.id
    LEFT JOIN projects p ON d.project_id = p.id
    WHERE d.parent_id " . ($current_folder_id ? "= :parent_id" : "IS NULL") . "
    ORDER BY d.is_folder DESC, d.name ASC
";
$stmt = $pdo->prepare($query);
if ($current_folder_id) {
    $stmt->execute(['parent_id' => $current_folder_id]);
} else {
    $stmt->execute();
}
$documents = $stmt->fetchAll();

// Fetch breadcrumbs
$breadcrumbs = [];
if ($current_folder_id) {
    $temp_id = $current_folder_id;
    while ($temp_id) {
        $bStmt = $pdo->prepare("SELECT id, name, parent_id FROM documents WHERE id = ?");
        $bStmt->execute([$temp_id]);
        $folder = $bStmt->fetch();
        if ($folder) {
            array_unshift($breadcrumbs, $folder);
            $temp_id = $folder['parent_id'];
        } else {
            break;
        }
    }
}

// Fetch projects for upload dropdown
$projectsStmt = $pdo->query("SELECT id, title FROM projects");
$projects = $projectsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .doc-toolbar { display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap; }
        .doc-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: var(--space-lg); }
        .doc-card { background: var(--dark-700); padding: var(--space-lg); border-radius: var(--radius-lg); border: 1px solid rgba(255,255,255,0.05); transition: all 0.3s; cursor: pointer; display: flex; align-items: center; gap: 12px; }
        .doc-card:hover { border-color: rgba(139, 92, 246, 0.3); background: var(--dark-600); transform: translateY(-2px); }
        .doc-icon { font-size: 1.5rem; flex-shrink: 0; }
        .doc-info { flex: 1; min-width: 0; }
        .doc-title { display: block; font-weight: 500; color: var(--white); font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .doc-meta { font-size: 0.7rem; color: var(--gray-500); }
        .breadcrumb { display: flex; align-items: center; gap: 8px; margin-bottom: var(--space-xl); font-size: 0.9rem; color: var(--gray-400); }
        .breadcrumb a { color: var(--primary-blue-light); text-decoration: none; }
        .breadcrumb i { font-size: 0.7rem; }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Cloud Storage";
            $header_subtitle = "Manage files, folders, and shared project resources.";
            $header_actions = '
                <div class="doc-toolbar">
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById(\'newFolderModal\').style.display=\'flex\'"><i class="fas fa-folder-plus"></i> New Folder</button>
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById(\'uploadFile\').click()"><i class="fas fa-file-upload"></i> Upload File</button>
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById(\'uploadFolder\').click()"><i class="fas fa-folder-open"></i> Upload Folder</button>
                </div>
                <input type="file" id="uploadFile" style="display:none" multiple onchange="handleUpload(this.files)">
                <input type="file" id="uploadFolder" style="display:none" webkitdirectory directory multiple onchange="handleUpload(this.files)">
            ';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Breadcrumbs -->
            <div class="breadcrumb">
                <a href="staff-documents.php"><i class="fas fa-home"></i> Root</a>
                <?php foreach ($breadcrumbs as $b): ?>
                    <i class="fas fa-chevron-right"></i>
                    <a href="staff-documents.php?folder=<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></a>
                <?php endforeach; ?>
            </div>

            <div class="doc-list">
                <?php foreach ($documents as $doc): 
                    $isFolder = (bool)$doc['is_folder'];
                    $iconClass = $isFolder ? 'fa-folder' : 'fa-file-alt';
                    $iconColor = $isFolder ? '#f59e0b' : 'var(--accent-purple)';
                    if (!$isFolder) {
                        if (strpos($doc['name'], '.pdf') !== false) { $iconClass = 'fa-file-pdf'; $iconColor = '#f43f5e'; }
                        if (strpos($doc['name'], '.doc') !== false) { $iconClass = 'fa-file-word'; $iconColor = '#3b82f6'; }
                        if (strpos($doc['name'], '.jpg') !== false || strpos($doc['name'], '.png') !== false) { $iconClass = 'fa-file-image'; $iconColor = '#10b981'; }
                    }
                    $link = $isFolder ? "staff-documents.php?folder=" . $doc['id'] : $doc['file_path'];
                ?>
                <div class="doc-card" onclick="window.location.href='<?php echo $link; ?>'">
                    <i class="fas <?php echo $iconClass; ?> doc-icon" style="color: <?php echo $iconColor; ?>;"></i>
                    <div class="doc-info">
                        <span class="doc-title"><?php echo htmlspecialchars($doc['name']); ?></span>
                        <span class="doc-meta"><?php echo $isFolder ? 'Folder' : $doc['file_size']; ?> • <?php echo date('M d', strtotime($doc['created_at'])); ?></span>
                    </div>
                    <div class="doc-actions">
                        <i class="fas fa-ellipsis-v" style="color: var(--gray-500); font-size: 0.8rem;"></i>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($documents)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px; background: rgba(255,255,255,0.02); border-radius: var(--radius-xl); border: 1px dashed rgba(255,255,255,0.05);">
                        <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.2;"></i>
                        <p style="color: var(--gray-500);">This folder is empty.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- New Folder Modal -->
    <div id="newFolderModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 400px; padding: var(--space-2xl);">
            <h3 style="margin-bottom: var(--space-xl);">Create New Folder</h3>
            <form id="createFolderForm">
                <input type="hidden" name="action" value="create_folder">
                <input type="hidden" name="parent_id" value="<?php echo $current_folder_id; ?>">
                <div class="form-group">
                    <label class="form-label">Folder Name</label>
                    <input type="text" name="name" id="newFolderName" class="form-input" placeholder="e.g. Project Assets" required autofocus>
                </div>
                <div style="display: flex; gap: 10px; margin-top: var(--space-xl);">
                    <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="document.getElementById('newFolderModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Create</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script>
    document.getElementById('createFolderForm').onsubmit = function(e) {
        e.preventDefault();
        const name = document.getElementById('newFolderName').value;
        const parentId = <?php echo $current_folder_id ?: 'null'; ?>;
        
        fetch('api/actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'create_folder',
                name: name,
                parent_id: parentId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    };
    function handleUpload(files) {
        if (!files.length) return;
        
        const formData = new FormData();
        formData.append('action', 'document_upload');
        formData.append('parent_id', '<?php echo $current_folder_id; ?>');
        
        for (let i = 0; i < files.length; i++) {
            formData.append('documents[]', files[i]);
            if (files[i].webkitRelativePath) {
                formData.append('paths[]', files[i].webkitRelativePath);
            }
        }

        // Show loading state or progress
        console.log('Uploading...', files.length, 'files');
        
        fetch('api/upload.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Upload failed: ' + data.error);
            }
        });
    }
    </script>
</body>
</html>

