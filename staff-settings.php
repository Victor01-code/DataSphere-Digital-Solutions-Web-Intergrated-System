<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .settings-tab-btn.active {
            background: rgba(139, 92, 246, 0.1) !important;
            color: var(--accent-purple) !important;
            border-color: rgba(139, 92, 246, 0.2) !important;
        }
        .form-group { margin-bottom: var(--space-xl); }
        .form-label { display: block; margin-bottom: var(--space-sm); color: var(--gray-400); font-size: 0.9rem; font-weight: 600; }
        .form-input { width: 100%; background: var(--dark-800); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-lg); padding: var(--space-md); color: var(--white); outline: none; transition: all 0.3s; }
        .form-input:focus { border-color: var(--accent-purple); box-shadow: 0 0 15px rgba(139, 92, 246, 0.1); }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">Account Settings</h1>
                    <p style="color: var(--gray-400);">Manage your profile, security, and notification preferences.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--space-xl); margin-top: var(--space-xl);">
                <!-- Profile Sidebar -->
                <div class="dashboard-card" style="padding: var(--space-xl); height: fit-content;">
                    <div style="text-align: center; margin-bottom: var(--space-xl);">
                        <div class="profile-image-container" style="position: relative; width: 120px; height: 120px; margin: 0 auto var(--space-lg);">
                            <?php if (strpos($user['avatar'] ?? '', '/') !== false): ?>
                                <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--accent-purple);">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-pink) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700;">
                                    <?php 
                                    $initials = '';
                                    foreach(explode(' ', $user['name']) as $n) $initials .= $n[0];
                                    echo strtoupper(substr($initials, 0, 2));
                                    ?>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="avatarInput" style="display: none;" accept="image/*">
                            <label onclick="document.getElementById('avatarInput').click()" style="position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; background: var(--accent-purple); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--dark-800); color: white; transition: transform 0.3s ease;">
                                <i class="fas fa-camera" style="font-size: 0.9rem;"></i>
                            </label>
                        </div>
                        <h3 style="margin-bottom: 4px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p style="font-size: 0.875rem; color: var(--gray-400);"><?php echo htmlspecialchars($user['email']); ?></p>
                        <p style="font-size: 0.75rem; color: var(--accent-purple); margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;"><?php echo htmlspecialchars($user['title'] ?? $user['role']); ?></p>
                    </div>
                    
                    <nav style="display: flex; flex-direction: column; gap: var(--space-sm);">
                        <button onclick="showTab('profile')" class="btn btn-secondary btn-sm settings-tab-btn active" style="justify-content: flex-start;"><i class="fas fa-user-circle"></i> Profile Information</button>
                        <button onclick="showTab('social')" class="btn btn-secondary btn-sm settings-tab-btn" style="justify-content: flex-start;"><i class="fas fa-share-nodes"></i> Social Accounts</button>
                        <button onclick="showTab('security')" class="btn btn-secondary btn-sm settings-tab-btn" style="justify-content: flex-start;"><i class="fas fa-shield-alt"></i> Security & Password</button>
                        <button onclick="showTab('notifications')" class="btn btn-secondary btn-sm settings-tab-btn" style="justify-content: flex-start;"><i class="fas fa-bell"></i> Notifications</button>
                    </nav>
                </div>

                <!-- Settings Content -->
                <div class="dashboard-card" style="padding: var(--space-2xl);">
                    <!-- Profile Tab -->
                    <div id="profile-tab" class="settings-tab active">
                        <h3 style="margin-bottom: var(--space-xl);">Profile Information</h3>
                        <form id="profileForm">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                                <div class="form-group">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="title" class="form-input" value="<?php echo htmlspecialchars($user['title'] ?? ''); ?>" placeholder="e.g. Senior Developer">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="e.g. Arusha, TZ">
                                </div>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: var(--space-md); margin-top: var(--space-xl);">
                                <button type="reset" class="btn btn-secondary">Discard Changes</button>
                                <button type="submit" class="btn btn-primary" id="saveProfileBtn">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Social Accounts Tab -->
                    <div id="social-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Social Media Profiles</h3>
                        <p style="color: var(--gray-400); margin-bottom: var(--space-xl);">Connect your professional and social accounts to your profile.</p>
                        
                        <form id="socialForm">
                            <input type="hidden" name="action" value="update_socials">
                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label"><i class="fab fa-linkedin" style="color: #0077b5; margin-right: 8px;"></i> LinkedIn URL</label>
                                <input type="url" name="social_linkedin" class="form-input" value="<?php echo htmlspecialchars($user['social_linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                            </div>
                            
                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label"><i class="fab fa-twitter" style="color: #1da1f2; margin-right: 8px;"></i> Twitter (X) URL</label>
                                <input type="url" name="social_twitter" class="form-input" value="<?php echo htmlspecialchars($user['social_twitter'] ?? ''); ?>" placeholder="https://twitter.com/username">
                            </div>

                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label"><i class="fab fa-facebook" style="color: #1877f2; margin-right: 8px;"></i> Facebook URL</label>
                                <input type="url" name="social_facebook" class="form-input" value="<?php echo htmlspecialchars($user['social_facebook'] ?? ''); ?>" placeholder="https://facebook.com/username">
                            </div>

                            <div class="form-group" style="margin-bottom: var(--space-xl);">
                                <label class="form-label"><i class="fab fa-instagram" style="color: #e4405f; margin-right: 8px;"></i> Instagram URL</label>
                                <input type="url" name="social_instagram" class="form-input" value="<?php echo htmlspecialchars($user['social_instagram'] ?? ''); ?>" placeholder="https://instagram.com/username">
                            </div>

                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary" id="saveSocialBtn">Update Social Links</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div id="security-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Security & Password</h3>
                        <form id="securityForm">
                            <div class="form-group" style="max-width: 400px; margin-bottom: var(--space-lg);">
                                <label class="form-label">New Password</label>
                                <input type="password" name="password" class="form-input" placeholder="Enter new password" required>
                            </div>
                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary" id="saveSecurityBtn">Update Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Notification Preferences</h3>
                        <p style="color: var(--gray-400); margin-bottom: var(--space-xl);">Choose how you want to be notified about tasks and team updates.</p>
                        
                        <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
                            <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                                <div>
                                    <p style="font-weight: 600;">Email Notifications</p>
                                    <p style="font-size: 0.85rem; color: var(--gray-400);">Receive project updates via email.</p>
                                </div>
                                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--accent-purple);">
                            </label>
                            <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                                <div>
                                    <p style="font-weight: 600;">System Alerts</p>
                                    <p style="font-size: 0.85rem; color: var(--gray-400);">Get notified in-app for new tasks.</p>
                                </div>
                                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--accent-purple);">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.settings-tab').forEach(tab => {
                tab.style.display = 'none';
            });
            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            
            // Update buttons
            document.querySelectorAll('.settings-tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }

        async function submitSettings(formId, btnId) {
            const form = document.getElementById(formId);
            const btn = document.getElementById(btnId);
            const originalText = btn.innerText;
            btn.innerText = 'Saving...';
            btn.disabled = true;

            const formData = new FormData(form);
            try {
                const response = await fetch('api/settings.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Connection error', 'error');
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        }

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitSettings('profileForm', 'saveProfileBtn');
        });

        document.getElementById('socialForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitSettings('socialForm', 'saveSocialBtn');
        });

        document.getElementById('securityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitSettings('securityForm', 'saveSecurityBtn');
        });

        // Avatar Upload Logic
        document.getElementById('avatarInput').addEventListener('change', async function() {
            if (!this.files || !this.files[0]) return;
            
            const formData = new FormData();
            formData.append('avatar', this.files[0]);
            formData.append('action', 'avatar_upload');

            try {
                showNotification('Uploading avatar...', 'info');
                const response = await fetch('api/upload.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Failed to update avatar', 'error');
            }
        });
    </script>
    <script src="js/main.js"></script>
    <script src="js/notifications.js"></script>
</body>
</html>
