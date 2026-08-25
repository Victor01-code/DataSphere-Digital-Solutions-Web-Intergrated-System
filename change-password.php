<?php 
require_once 'php/db_connect.php';
require_once 'php/auth.php';

// Special check for this page - must be logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Update your DataSphere portal password for enhanced security.">
    <meta name="robots" content="noindex, nofollow">

    <title>Update Password | DataSphere Digital Solutions</title>
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
</head>

<body>
    <!-- Change Password Section -->
    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <a href="index.php" class="logo" style="justify-content: center; margin-bottom: var(--space-lg); display: flex;">
                    <img src="assets/images/logo.png" alt="DataSphere Logo" class="logo-img">
                </a>
                <div class="staff-badge" style="background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.3); color: var(--accent-purple); margin-bottom: var(--space-lg); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: inline-flex; align-items: center; gap: 8px; margin-left: auto; margin-right: auto;">
                    <i class="fas fa-key"></i> Security Update Required
                </div>
                <h1>Setup Your Password</h1>
                <p>For your security, you must change your default password before accessing the portal.</p>
            </div>

            <div class="login-card">
                <form action="php/auth_actions.php" method="POST" id="changePasswordForm">
                    <input type="hidden" name="action" value="change_password_required">

                    <?php if (isset($_GET['error'])): ?>
                    <div id="passwordError" style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> 
                        <span>
                            <?php 
                            if ($_GET['error'] === 'mismatch') echo 'Passwords do not match.';
                            elseif ($_GET['error'] === 'too_short') echo 'Password must be at least 10 characters.';
                            elseif ($_GET['error'] === 'invalid_current') echo 'Incorrect current default password.';
                            else echo 'An error occurred. Please try again.';
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Default Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-input" placeholder="DataSphere@!2026" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Secure Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Create a new password" required minlength="10">
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 5px;">Must be at least 10 characters long.</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Confirm your new password" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-shield-check"></i> Update & Access Portal
                    </button>
                </form>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.password-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const passwordInput = document.getElementById('new_password');
                    const icon = this.querySelector('i');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.replace('fa-eye', 'fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.replace('fa-eye-slash', 'fa-eye');
                    }
                });
            }
        });
    </script>
</body>

</html>
