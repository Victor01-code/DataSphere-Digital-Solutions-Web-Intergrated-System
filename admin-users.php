<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole('admin', 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all users
$users = $pdo->query("SELECT * FROM users ORDER BY role ASC, name ASC")->fetchAll();

// Group users for stats
$stats = [
    'total' => count($users),
    'admin' => 0,
    'staff' => 0,
    'client' => 0
];

foreach ($users as $u) {
    $stats[$u['role']]++;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | DataSphere Admin</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
            $header_title = "User Management";
            $header_subtitle = "Add, update, and manage all system users and roles.";
            $header_actions = '<button class="btn btn-primary" id="openAddUserModal"><i class="fas fa-user-plus"></i> Add User</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>

            <!-- Stats Bar -->
            <div class="dashboard-cards" style="grid-template-columns: repeat(4, 1fr); margin-bottom: var(--space-xl);">
                <div class="dashboard-card" style="padding: var(--space-lg);">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Total Users</p>
                    <h3 style="margin-top: 5px;"><?php echo $stats['total']; ?></h3>
                </div>
                <div class="dashboard-card" style="padding: var(--space-lg); border-left: 4px solid var(--accent-purple);">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Admins</p>
                    <h3 style="margin-top: 5px;"><?php echo $stats['admin']; ?></h3>
                </div>
                <div class="dashboard-card" style="padding: var(--space-lg); border-left: 4px solid var(--primary-blue);">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Staff</p>
                    <h3 style="margin-top: 5px;"><?php echo $stats['staff']; ?></h3>
                </div>
                <div class="dashboard-card" style="padding: var(--space-lg); border-left: 4px solid var(--accent-green);">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase; letter-spacing: 1px;">Clients</p>
                    <h3 style="margin-top: 5px;"><?php echo $stats['client']; ?></h3>
                </div>
            </div>

            <!-- Users Table -->
            <div class="dashboard-card" style="padding: 0; overflow: hidden;">
                <div style="padding: var(--space-lg); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center; gap: var(--space-xl); flex-wrap: wrap;">
                    <div style="position: relative; flex: 1; min-width: 250px;">
                        <i class="fas fa-search" style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--gray-500);"></i>
                        <input type="text" id="userSearch" class="form-input" placeholder="Search by name, email or role..." style="padding-left: 45px;">
                    </div>
                    <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                        <select id="roleFilter" class="form-input" style="width: auto; appearance: auto;">
                            <option value="all">All Roles</option>
                            <option value="admin">Admins</option>
                            <option value="staff">Staff</option>
                            <option value="client">Clients</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <tr>
                            <th style="padding: var(--space-lg);">User</th>
                            <th style="padding: var(--space-lg);">Role</th>
                            <th style="padding: var(--space-lg);">Title / Position</th>
                            <th style="padding: var(--space-lg);">Location</th>
                            <th style="padding: var(--space-lg);">Last Login</th>
                            <th style="padding: var(--space-lg);">Created</th>
                            <th style="padding: var(--space-lg); text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.01)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: var(--space-lg);">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;">
                                        <?php echo htmlspecialchars($user['avatar'] ?: substr($user['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p style="font-weight: 600; margin: 0;"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p style="font-size: 0.75rem; color: var(--gray-400); margin: 0;"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: var(--space-lg);">
                                <span class="status-badge" style="background: <?php 
                                    if($user['role'] === 'admin') echo 'rgba(139, 92, 246, 0.15); color: var(--accent-purple);';
                                    elseif($user['role'] === 'staff') echo 'rgba(0, 102, 255, 0.15); color: var(--primary-blue-light);';
                                    else echo 'rgba(16, 185, 129, 0.15); color: var(--accent-green);';
                                ?> text-transform: uppercase; font-size: 0.7rem; font-weight: 700; padding: 4px 10px; border-radius: var(--radius-full);">
                                    <?php echo htmlspecialchars($user['role']); ?>
                                </span>
                            </td>
                            <td style="padding: var(--space-lg); color: var(--gray-300); font-size: 0.9rem;">
                                <?php echo htmlspecialchars($user['title'] ?: '-'); ?>
                            </td>
                            <td style="padding: var(--space-lg); color: var(--gray-400); font-size: 0.85rem;">
                                <i class="fas fa-location-dot" style="font-size: 0.75rem; margin-right: 4px;"></i>
                                <?php echo htmlspecialchars($user['location'] ?: 'Not set'); ?>
                            </td>
                            <td style="padding: var(--space-lg); color: var(--gray-400); font-size: 0.8rem;">
                                <?php echo $user['last_login'] ? date('M d, H:i', strtotime($user['last_login'])) : '<span style="opacity: 0.3;">Never</span>'; ?>
                            </td>
                            <td style="padding: var(--space-lg); color: var(--gray-500); font-size: 0.8rem;">
                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>
                            <td style="padding: var(--space-lg); text-align: right;">
                                <button class="btn btn-secondary btn-sm" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)" style="padding: 6px 10px;"><i class="fas fa-edit"></i></button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <button class="btn btn-secondary btn-sm" onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo addslashes($user['name']); ?>')" style="padding: 6px 10px; color: var(--accent-pink);"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <div id="userModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 5000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="modal-content" style="background: var(--dark-800); width: 100%; max-width: 500px; border-radius: var(--radius-2xl); border: 1px solid rgba(255,255,255,0.1); overflow: hidden;">
            <div class="modal-header" style="padding: var(--space-xl); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modalTitle" style="margin: 0;">Add User</h3>
                <button onclick="closeModal()" style="background: none; border: none; color: var(--gray-400); cursor: pointer; font-size: 1.25rem;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/auth_actions.php" method="POST" style="padding: var(--space-xl);">
                <input type="hidden" name="action" id="formAction" value="admin_add_user">
                <input type="hidden" name="user_id" id="userIdField">
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="nameField" class="form-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="emailField" class="form-input" required>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" id="roleField" class="form-input" style="appearance: auto;">
                            <option value="client">Client</option>
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Service Team (Dept)</label>
                        <select name="department" id="departmentField" class="form-input" style="appearance: auto;">
                            <option value="General">General</option>
                            <option value="HR">HR</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Design & Branding">Design & Branding</option>
                            <option value="Development">Development</option>
                            <option value="Digital Strategy">Digital Strategy</option>
                            <option value="Administrative">Administrative</option>
                        </select>
                    </div>
                </div>

                <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="title" id="titleField" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="locationField" class="form-input" required>
                    </div>
                </div>

                <div id="passwordNote" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: var(--space-md); border-radius: var(--radius-lg); margin-bottom: var(--space-xl);">
                    <p style="font-size: 0.8rem; color: var(--accent-green); margin: 0;">
                        <i class="fas fa-info-circle"></i> Default password: <strong>DataSphere@!2026</strong>.
                    </p>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> <span id="submitBtnText">Create User</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation -->
    <div id="deleteModal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 5000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div class="modal-content" style="background: var(--dark-800); width: 100%; max-width: 400px; border-radius: var(--radius-2xl); border: 1px solid rgba(255,255,255,0.1); padding: var(--space-2xl); text-align: center;">
            <div style="width: 60px; height: 60px; background: rgba(244, 63, 94, 0.1); color: var(--accent-pink); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto var(--space-xl);">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Delete User?</h3>
            <p style="color: var(--gray-400); margin-bottom: var(--space-2xl);">Are you sure you want to delete <strong id="deleteUserName" style="color: white;"></strong>? This action cannot be undone.</p>
            
            <form action="php/auth_actions.php" method="POST" style="display: flex; gap: var(--space-md);">
                <input type="hidden" name="action" value="admin_delete_user">
                <input type="hidden" name="user_id" id="deleteUserId">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex: 1; background: var(--accent-pink);">Delete</button>
            </form>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script>
        // Search and Filter Logic
        const userSearch = document.getElementById('userSearch');
        const roleFilter = document.getElementById('roleFilter');
        const userRows = document.querySelectorAll('tbody tr');

        function filterUsers() {
            const searchTerm = userSearch.value.toLowerCase();
            const roleTerm = roleFilter.value;

            userRows.forEach(row => {
                const name = row.querySelector('p:nth-child(1)').textContent.toLowerCase();
                const email = row.querySelector('p:nth-child(2)').textContent.toLowerCase();
                const role = row.querySelector('.status-badge').textContent.trim().toLowerCase();
                
                const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
                const matchesRole = roleTerm === 'all' || role === roleTerm;

                if (matchesSearch && matchesRole) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        userSearch.addEventListener('input', filterUsers);
        roleFilter.addEventListener('change', filterUsers);

        // Modal Logic
        const userModal = document.getElementById('userModal');
        const deleteModal = document.getElementById('deleteModal');

        // Check for URL parameters
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('add')) {
                openAddModal();
            }
        });

        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Add New User';
            document.getElementById('formAction').value = 'admin_add_user';
            document.getElementById('submitBtnText').textContent = 'Create User';
            document.getElementById('passwordNote').style.display = 'block';
            
            // Clear fields
            document.getElementById('userIdField').value = '';
            document.getElementById('nameField').value = '';
            document.getElementById('emailField').value = '';
            document.getElementById('roleField').value = 'staff';
            document.getElementById('departmentField').value = 'General';
            document.getElementById('titleField').value = '';
            document.getElementById('locationField').value = '';
            
            userModal.style.display = 'flex';
        }

        document.getElementById('openAddUserModal').onclick = openAddModal;

        function editUser(user) {
            document.getElementById('modalTitle').textContent = 'Edit User';
            document.getElementById('formAction').value = 'admin_update_user';
            document.getElementById('submitBtnText').textContent = 'Save Changes';
            document.getElementById('passwordNote').style.display = 'none';
            
            document.getElementById('userIdField').value = user.id;
            document.getElementById('nameField').value = user.name;
            document.getElementById('emailField').value = user.email;
            document.getElementById('roleField').value = user.role;
            document.getElementById('departmentField').value = user.department || 'General';
            document.getElementById('titleField').value = user.title || '';
            document.getElementById('locationField').value = user.location || '';
            
            userModal.style.display = 'flex';
        }

        function closeModal() { userModal.style.display = 'none'; }

        function confirmDelete(id, name) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUserName').textContent = name;
            deleteModal.style.display = 'flex';
        }

        function closeDeleteModal() { deleteModal.style.display = 'none'; }

        // Close on outside click
        window.onclick = (e) => {
            if (e.target == userModal) closeModal();
            if (e.target == deleteModal) closeDeleteModal();
        };
    </script>
</body>

</html>
