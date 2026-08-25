<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all service bookings for this client
$stmt = $pdo->prepare("SELECT * FROM service_bookings WHERE client_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Bookings | DataSphere Client Portal</title>
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
                    <h1 style="font-size: 1.75rem;">Service Bookings</h1>
                    <p style="color: var(--gray-400);">Track the progress of your service requests and consultations.</p>
                </div>
                <button class="btn btn-primary" onclick="document.getElementById('bookingModal').style.display='flex'"><i class="fas fa-plus"></i> New Booking</button>
            </div>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
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

                <?php if (empty($bookings)): ?>
                    <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: var(--space-lg); opacity: 0.2;"></i>
                        <p>No service bookings found. Start by booking a service!</p>
                        <button class="btn btn-secondary" style="margin-top: var(--space-xl);" onclick="document.getElementById('bookingModal').style.display='flex'">Book a Service</button>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: var(--space-lg);">
                        <?php foreach ($bookings as $booking): ?>
                            <div style="background: var(--dark-700); border-radius: var(--radius-lg); padding: var(--space-xl); border: 1px solid rgba(255,255,255,0.05);">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                                    <div style="display: flex; align-items: center; gap: var(--space-lg);">
                                        <div style="width: 56px; height: 56px; background: var(--gradient-glow); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center; color: var(--primary-blue-light); font-size: 1.5rem;">
                                            <i class="fas fa-concierge-bell"></i>
                                        </div>
                                        <div>
                                            <h3 style="margin-bottom: 4px;"><?php echo htmlspecialchars($booking['service_name']); ?></h3>
                                            <p style="font-size: 0.875rem; color: var(--gray-400);">Booking ID: #SB-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?> • <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <span class="status-badge <?php echo strtolower($booking['status']); ?>" style="padding: 6px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; text-transform: uppercase; background: rgba(0, 102, 255, 0.1); color: var(--primary-blue-light);">
                                            <?php echo htmlspecialchars($booking['status']); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div style="margin-bottom: var(--space-md);">
                                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: var(--space-sm);">
                                        <span style="color: var(--gray-300);">Current Progress</span>
                                        <span style="color: var(--primary-blue-light); font-weight: 600;"><?php echo $booking['progress']; ?>%</span>
                                    </div>
                                    <div style="height: 10px; background: var(--dark-600); border-radius: 5px; overflow: hidden;">
                                        <div style="height: 100%; width: <?php echo $booking['progress']; ?>%; background: var(--gradient-primary); border-radius: 5px; transition: width 0.8s ease;"></div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: var(--space-xl); margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid rgba(255,255,255,0.05);">
                                    <div style="flex: 1;">
                                        <p style="font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 1px; margin-bottom: var(--space-xs);">Notes</p>
                                        <p style="font-size: 0.9rem; color: var(--gray-300);"><?php echo $booking['description'] ?: 'No additional notes provided.'; ?></p>
                                    </div>
                                    <div style="display: flex; align-items: flex-end; gap: 10px;">
                                        <a href="view_pdf.php?type=booking&id=<?php echo $booking['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="text-decoration: none;"><i class="fas fa-file-pdf"></i> PDF</a>
                                        <?php if (!empty($booking['manager_contact'])): ?>
                                            <a href="<?php echo htmlspecialchars($booking['manager_contact']); ?>" target="_blank" class="btn btn-secondary btn-sm" style="text-decoration: none;">Contact Manager</a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled title="Manager assignment pending" style="opacity: 0.5; cursor: not-allowed;">Pending Assignment</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Booking Modal -->
    <div id="bookingModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">Book New Service</h3>
                <button onclick="document.getElementById('bookingModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="php/handle_booking.php" method="POST" enctype="multipart/form-data">
                <div class="form-group" style="margin-bottom: var(--space-lg);">
                    <label class="form-label">Select Package / Service</label>
                    <select name="service_name" class="form-input" required>
                        <option value="">-- Choose a Package --</option>
                        <optgroup label="IT Consultation">
                            <option value="IT Consultation (Individual - 1hr) - 50,000 TZS">Individual Session (1hr) - 50,000 TZS</option>
                            <option value="Business Digital Audit & Strategy - 500,000 TZS">Business Audit & Strategy - 500,000 TZS</option>
                            <option value="Enterprise Transformation Roadmap - 1,500,000 TZS">Transformation Roadmap - 1,500,000 TZS</option>
                        </optgroup>
                        <optgroup label="Design & Branding">
                            <option value="Basic Branding (Logo + Palette) - 300,000 TZS">Basic Branding (Logo) - 300,000 TZS</option>
                            <option value="Corporate Identity (Full Brand Book) - 800,000 TZS">Corporate Identity - 800,000 TZS</option>
                            <option value="UI/UX Design (Mobile/Web Interface) - 600,000 TZS">UI/UX Design - 600,000 TZS</option>
                        </optgroup>
                        <optgroup label="Programming & Systems">
                            <option value="Portfolio/Personal Website - 500,000 TZS">Portfolio Website - 500,000 TZS</option>
                            <option value="Professional Business Platform - 1,500,000 TZS">Business Platform - 1,500,000 TZS</option>
                            <option value="Basic Mobile App (Android MVP) - 1,500,000 TZS">Basic Mobile App (Android) - 1,500,000 TZS</option>
                            <option value="Cross-Platform App (iOS & Android) - 3,500,000 TZS">Cross-Platform App (iOS & Android) - 3,500,000 TZS</option>
                            <option value="Custom Enterprise System - Starts 5,000,000 TZS">Enterprise System - Starts 5,000,000 TZS</option>
                        </optgroup>
                        <optgroup label="AI Studio & Creative">
                            <option value="AI Brand Visual Assets (10 Assets) - 400,000 TZS">AI Visual Assets - 400,000 TZS</option>
                            <option value="AI Video Generation (Campaign) - 1,000,000 TZS">AI Video Campaign - 1,000,000 TZS</option>
                            <option value="Custom AI Chatbot Integration - 800,000 TZS">AI Chatbot - 800,000 TZS</option>
                        </optgroup>
                        <optgroup label="Marketing Materials (Design Fee)">
                            <option value="Event Poster (Creative) - 30,000 TZS">Event Poster - 30,000 TZS</option>
                            <option value="Educational/Info Poster - 40,000 TZS">Educational Poster - 40,000 TZS</option>
                            <option value="Advertising/Sales Flyer - 30,000 TZS">Advertising Flyer - 30,000 TZS</option>
                            <option value="Large Format (Banner/Billboard) - 70,000 TZS">Billboard/Large Format - 70,000 TZS</option>
                            <option value="Business Card (Double Sided) - 25,000 TZS">Business Card - 25,000 TZS</option>
                            <option value="Company Profile (Full Identity) - 200,000 TZS">Company Profile - 200,000 TZS</option>
                            <option value="Digital Media Kit (Social Pack) - 100,000 TZS">Digital Media Kit - 100,000 TZS</option>
                            <option value="Printing - Quoted on Invoice">Printing (Price on Invoice)</option>
                        </optgroup>
                        <optgroup label="Data Services & Analytics">
                            <option value="Data Recovery - 150 TZS / 1 MB">Data Recovery - 150 TZS / 1 MB</option>
                            <option value="Quantitative/Qualitative Data Analysis - 600,000 TZS">Data Analysis - 600,000 TZS</option>
                            <option value="Database Management & Optimization - Quote">Database Management - Quote</option>
                            <option value="BI Dashboard & Reporting - 800,000 TZS">BI Dashboard - 800,000 TZS</option>
                        </optgroup>
                        <optgroup label="Monitoring & Evaluation (M&E)">
                            <option value="Project Evaluation & Impact Assessment - 1,500,000 TZS">Project Evaluation - 1,500,000 TZS</option>
                            <option value="M&E Framework & Tool Development - 800,000 TZS">M&E Framework Dev - 800,000 TZS</option>
                            <option value="Baseline & Endline Survey Management - 2,000,000 TZS">Survey Management - 2,000,000 TZS</option>
                        </optgroup>
                        <optgroup label="Digital Marketing">
                            <option value="Monthly Social Media (Bronze) - 400,000 TZS">Monthly (Bronze) - 400,000 TZS</option>
                            <option value="Monthly Social Media (Gold) - 1,200,000 TZS">Monthly (Gold) - 1,200,000 TZS</option>
                            <option value="Full Ads Management - 800,000 TZS">Ads Management - 800,000 TZS</option>
                        </optgroup>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: var(--space-lg);">
                    <label class="form-label">Project Requirements (Brief/Description)</label>
                    <textarea name="description" class="form-input" rows="3" placeholder="Tell us more about what you need..." style="resize: none;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label">Attach Requirement File (PDF, Doc, Image) <span style="color: #ff4d4d;">*</span></label>
                    <input type="file" name="requirement_file" class="form-input" style="padding: 8px;" required>
                    <small style="color: var(--gray-400); font-size: 0.8rem; margin-top: 5px; display: block;">Required: Please upload a project brief or reference document.</small>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('bookingModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Booking</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('new') === 'true') {
            document.getElementById('bookingModal').style.display = 'flex';
        }
    }
    </script>
    <!-- WhatsApp Support -->
    <a href="https://wa.me/255693038737" class="whatsapp-btn" target="_blank" rel="noopener"
        aria-label="Chat on WhatsApp" style="position: fixed; bottom: 30px; right: 30px; width: 60px; height: 60px; background: #25d366; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; box-shadow: 0 4px 15px rgba(37, 211, 102, 0.4); z-index: 1000; text-decoration: none; transition: transform 0.3s ease, box-shadow 0.3s ease;">
        <i class="fab fa-whatsapp"></i>
    </a>

    <script src="js/main.js"></script>
</body>
</html>
