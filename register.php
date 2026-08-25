<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Register for DataSphere Client Portal - Get started with your digital solutions journey.">
    <meta name="robots" content="noindex, nofollow">

    <title>Register | DataSphere Digital Solutions</title>
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
    <!-- Registration Section -->
    <section class="login-section">
        <div class="login-container">
            <div class="login-header">
                <a href="index.php" class="logo">
                    <img src="assets/images/logo.png" alt="DataSphere Logo" class="logo-img">
                </a>
                <h1>Join DataSphere</h1>
                <p>Create your account to start booking services and tracking your projects.</p>
            </div>

            <div class="login-card">
                <form action="php/auth.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="source" value="client_register">

                    <?php if (isset($_GET['error'])): ?>
                    <div id="registerError" style="background: rgba(244, 63, 94, 0.1); border: 1px solid rgba(244, 63, 94, 0.3); color: #f43f5e; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.875rem;">
                        <i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i> 
                        <span>
                            <?php 
                            if ($_GET['error'] === 'email_exists') echo 'Email already registered. Please login instead.';
                            elseif ($_GET['error'] === 'password_mismatch') echo 'Passwords do not match.';
                            else echo 'An error occurred. Please try again.';
                            ?>
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-input" placeholder="Enter your full name"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" class="form-input"
                                placeholder="Create a password" required minlength="8">
                            <button type="button" class="password-toggle">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" class="form-input"
                                placeholder="Confirm your password" required>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="terms" required>
                            I agree to the <a href="terms.php" target="_blank">Terms</a> and <a href="privacy.php" target="_blank">Privacy</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                </form>

                <div class="login-divider">
                    <span>or sign up with</span>
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
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
                <p style="margin-top: var(--space-lg);"><a href="index.php"><i class="fas fa-arrow-left"></i> Back to
                        Website</a></p>
            </div>
        </div>
    </section>

    <script src="js/main.js"></script>
</body>

</html>
