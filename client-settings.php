<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | DataSphere Client Portal</title>
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
                    <h1 style="font-size: 1.75rem;">Account Settings</h1>
                    <p style="color: var(--gray-400);">Manage your profile, security, and notification preferences.</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: var(--space-xl); margin-top: var(--space-xl);">
                <!-- Profile Sidebar -->
                <div class="dashboard-card" style="padding: var(--space-xl); height: fit-content;">
                    <div style="text-align: center; margin-bottom: var(--space-xl);">
                        <div class="profile-image-container" style="position: relative; width: 120px; height: 120px; margin: 0 auto var(--space-lg);">
                            <?php if (!empty($user['profile_pic'])): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-blue);">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700;">
                                    <?php 
                                    $initials = '';
                                    foreach(explode(' ', $user['name']) as $n) $initials .= $n[0];
                                    echo strtoupper($initials);
                                    ?>
                                </div>
                            <?php endif; ?>
                            <label for="profile_upload" style="position: absolute; bottom: 0; right: 0; width: 36px; height: 36px; background: var(--primary-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 2px solid var(--dark-800); color: white; transition: transform 0.3s ease;">
                                <i class="fas fa-camera" style="font-size: 0.9rem;"></i>
                            </label>
                        </div>
                        <h3 style="margin-bottom: 4px;"><?php echo htmlspecialchars($user['name']); ?></h3>
                        <p style="font-size: 0.875rem; color: var(--gray-400);"><?php echo htmlspecialchars($user['email']); ?></p>
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
                    <?php if (isset($_SESSION['success'])): ?>
                        <div style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-check-circle"></i>
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg); display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-exclamation-circle"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Tab -->
                    <div id="profile-tab" class="settings-tab active">
                        <h3 style="margin-bottom: var(--space-xl);">Profile Information</h3>
                        <form action="php/update_profile.php" method="POST" enctype="multipart/form-data">
                            <!-- Hidden input for the actual file -->
                            <input type="file" name="profile_pic" id="profile_upload" style="display: none;" onchange="this.form.submit()">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" readonly style="opacity: 0.7; cursor: not-allowed;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-input" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>" placeholder="e.g. Arusha, TZ">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. +255 693 038 737">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                                <div class="form-group">
                                    <label class="form-label">Company / Organization</label>
                                    <input type="text" name="company" class="form-input" value="<?php echo htmlspecialchars($user['company'] ?? ''); ?>" placeholder="Enter your company name">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Company Website</label>
                                    <input type="url" name="website" class="form-input" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" placeholder="https://example.com">
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label">Physical Address</label>
                                <textarea name="address" class="form-input" rows="2" placeholder="Street name, Building, City, Country"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group" style="margin-bottom: var(--space-xl);">
                                <label class="form-label">Professional Bio / About</label>
                                <textarea name="bio" class="form-input" rows="3" placeholder="Tell us a bit about yourself or your business..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            </div>

                            <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                                <button type="reset" class="btn btn-secondary">Discard Changes</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <!-- Social Accounts Tab -->
                    <div id="social-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Social Media Profiles</h3>
                        <p style="color: var(--gray-400); margin-bottom: var(--space-xl);">Connect your professional and social accounts to your profile.</p>
                        
                        <form action="php/update_socials.php" method="POST">
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
                                <button type="submit" class="btn btn-primary">Update Social Links</button>
                            </div>
                        </form>
                    </div>

                    <!-- Security Tab -->
                    <div id="security-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Security & Password</h3>
                        <form action="php/update_password.php" method="POST">
                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-input" required>
                            </div>
                            <div class="form-group" style="margin-bottom: var(--space-lg);">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-input" required>
                            </div>
                            <div class="form-group" style="margin-bottom: var(--space-xl);">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-input" required>
                            </div>
                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn btn-primary">Update Password</button>
                            </div>
                        </form>
                    </div>

                    <!-- Notifications Tab -->
                    <div id="notifications-tab" class="settings-tab" style="display: none;">
                        <h3 style="margin-bottom: var(--space-xl);">Notification Preferences</h3>
                        <p style="color: var(--gray-400); margin-bottom: var(--space-xl);">Choose how you want to be notified about project updates and invoices.</p>
                        
                        <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
                            <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                                <div>
                                    <p style="font-weight: 600;">Email Notifications</p>
                                    <p style="font-size: 0.85rem; color: var(--gray-400);">Receive project updates via email.</p>
                                </div>
                                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--primary-blue);">
                            </label>
                            <label style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;">
                                <div>
                                    <p style="font-weight: 600;">Invoice Alerts</p>
                                    <p style="font-size: 0.85rem; color: var(--gray-400);">Get notified when a new invoice is generated.</p>
                                </div>
                                <input type="checkbox" checked style="width: 20px; height: 20px; accent-color: var(--primary-blue);">
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <style>
        .settings-tab-btn.active {
            background: rgba(0, 102, 255, 0.1) !important;
            color: var(--primary-blue-light) !important;
            border-color: rgba(0, 102, 255, 0.2) !important;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--gray-300);
        }
    </style>

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
    </script>
    <script src="js/main.js"></script>
</body>
</html>
