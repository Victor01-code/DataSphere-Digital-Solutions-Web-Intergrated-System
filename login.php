<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Login to DataSphere Client Portal - Access your project dashboard, files, and support tickets.">
    <meta name="robots" content="noindex, nofollow">

    <title>Client Portal Login | DataSphere Digital Solutions</title>
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
    <!-- Login Section -->
    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="DataSphere Logo" class="logo-img">
                </a>
                <h1>Welcome Back</h1>
                <p>Sign in to access your client portal and manage your projects.</p>
            </div>

            <div class="login-card">
                <form action="php/auth.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="source" value="client_login">

                    <?php if (isset($_GET['error'])): ?>
                    <div id="loginError" style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> 
                        <span>
                            <?php 
                            if ($_GET['error'] === 'invalid_credentials') echo 'Invalid email or password.';
                            elseif ($_GET['error'] === 'missing_fields') echo 'Please fill in all fields.';
                            elseif ($_GET['error'] === 'access_denied') echo 'Access denied. This portal is for clients only.';
                            else echo 'An error occurred. Please try again.';
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Enter your password" required>
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember">
                            Remember me
                        </label>
                        <a href="#" class="forgot-link">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="login-divider">
                    <span>or continue with</span>
                </div>

                <div class="social-login">
                    <button class="social-login-btn" type="button" onclick="location.href='php/social_auth.php?provider=google'">
                        <i class="fab fa-google"></i>
                    </button>
                    <button class="social-login-btn" type="button" onclick="location.href='php/social_auth.php?provider=microsoft'">
                        <i class="fab fa-microsoft"></i>
                    </button>
                </div>
            </div>

            <div class="login-footer">
                <p>Don't have an account? <a href="register.php">Register here</a></p>

                <div style="display: flex; gap: var(--space-md); margin-top: var(--space-lg);">
                    <a href="login.php"
                        style="flex: 1; padding: var(--space-md); background: var(--gradient-glow); border: 1px solid rgba(0, 102, 255, 0.4); border-radius: var(--radius-lg); text-align: center;">
                        <i class="fas fa-user"
                            style="display: block; font-size: 1.5rem; margin-bottom: var(--space-xs); color: var(--primary-blue-light);"></i>
                        <span style="font-size: 0.875rem; color: var(--gray-300);">Client Portal</span>
                    </a>
                    <a href="staff-login.php"
                        style="flex: 1; padding: var(--space-md); background: var(--dark-700); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: var(--radius-lg); text-align: center;">
                        <i class="fas fa-users-cog"
                            style="display: block; font-size: 1.5rem; margin-bottom: var(--space-xs); color: var(--accent-purple);"></i>
                        <span style="font-size: 0.875rem; color: var(--gray-300);">Staff Portal</span>
                    </a>
                </div>

                <p style="margin-top: var(--space-lg);"><a href="index.php"><i class="fas fa-arrow-left"></i> Back to
                        Website</a></p>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>

</html>
