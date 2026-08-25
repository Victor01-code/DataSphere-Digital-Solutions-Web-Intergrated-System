<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$error = isset($_GET['error']) ? $_GET['error'] : '';
$message = isset($_GET['message']) ? $_GET['message'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="DataSphere Staff Portal - Secure access for team members to manage projects, tasks, and internal operations.">
    <meta name="robots" content="noindex, nofollow">

    <title>Staff Portal Login | DataSphere Digital Solutions</title>
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

    <style>
        .staff-login .login-container { max-width: 440px; }
        .staff-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            padding: var(--space-xs) var(--space-md);
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(236, 72, 153, 0.2) 100%);
            border: 1px solid rgba(139, 92, 246, 0.4);
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--accent-purple);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: var(--space-lg);
        }
        .error-alert { background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; padding: var(--space-md); border-radius: var(--radius-lg); margin-bottom: var(--space-lg); font-size: 0.875rem; display: flex; align-items: center; gap: 10px; }
        .success-alert { background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-lg); margin-bottom: var(--space-lg); font-size: 0.875rem; display: flex; align-items: center; gap: 10px; }
    </style>
</head>

<body>
    <!-- Login Section -->
    <section class="login-section staff-login">
        <div class="login-container">
            <div class="login-header">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="DataSphere Logo" class="logo-img">
                </a>

                <div class="staff-badge">
                    <i class="fas fa-shield-alt"></i>
                    Staff Access Only
                </div>

                <h1>Staff Portal</h1>
                <p>Sign in to access internal tools and manage DataSphere operations.</p>
            </div>

            <div class="login-card">
                <?php if ($error === 'invalid_credentials'): ?>
                    <div class="error-alert"><i class="fas fa-exclamation-circle"></i> Invalid email or password.</div>
                <?php elseif ($error === 'missing_fields'): ?>
                    <div class="error-alert"><i class="fas fa-exclamation-circle"></i> Please fill in all fields.</div>
                <?php elseif ($error === 'access_denied'): ?>
                    <div class="error-alert"><i class="fas fa-exclamation-circle"></i> Access denied. This portal is for staff only.</div>
                <?php endif; ?>

                <?php if ($message === 'logged_out'): ?>
                    <div class="success-alert"><i class="fas fa-check-circle"></i> You have been logged out safely.</div>
                <?php endif; ?>

                <form action="php/auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="source" value="staff_login">
                    
                    <div class="form-group">
                        <label class="form-label" for="staffEmail">Work Email</label>
                        <input type="email" id="staffEmail" name="email" class="form-input" placeholder="name@dataspheredns.com" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="staffPassword">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="staffPassword" name="password" class="form-input" placeholder="Enter your password" required>
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            Keep me signed in
                        </label>
                        <a href="#" class="forgot-link">Reset Password</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-lock"></i> Secure Sign In
                    </button>
                </form>
            </div>

            <div class="login-footer">
                <p style="color: var(--gray-400); font-size: 0.875rem; margin-bottom: var(--space-md);">Switch Portal</p>
                <div class="portal-switch" style="display: flex; gap: var(--space-md); margin-top: var(--space-lg);">
                    <a href="login.php" style="flex: 1; padding: var(--space-md); background: var(--dark-700); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius-lg); text-align: center; transition: all 0.3s; color: var(--gray-300);">
                        <i class="fas fa-user" style="display: block; font-size: 1.5rem; margin-bottom: 5px; color: var(--accent-purple);"></i>
                        <span>Client Portal</span>
                    </a>
                    <a href="staff-login.php" class="active" style="flex: 1; padding: var(--space-md); background: linear-gradient(135deg, rgba(139, 92, 246, 0.2) 0%, rgba(236, 72, 153, 0.2) 100%); border: 1px solid rgba(139, 92, 246, 0.4); border-radius: var(--radius-lg); text-align: center; transition: all 0.3s;">
                        <i class="fas fa-users-cog" style="display: block; font-size: 1.5rem; margin-bottom: 5px; color: var(--accent-purple);"></i>
                        <span>Staff Portal</span>
                    </a>
                </div>

                <p style="margin-top: var(--space-xl);"><a href="index.php" style="color: var(--gray-400);"><i class="fas fa-arrow-left"></i> Back to Website</a></p>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>

</html>
