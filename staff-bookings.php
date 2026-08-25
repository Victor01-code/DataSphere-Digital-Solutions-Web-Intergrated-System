<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_booking') {
    $bookingId = $_POST['booking_id'];
    $status = $_POST['status'];
    $progress = $_POST['progress'];
    
    $stmt = $pdo->prepare("UPDATE service_bookings SET status = ?, progress = ? WHERE id = ?");
    $stmt->execute([$status, $progress, $bookingId]);
    header('Location: staff-bookings.php?success=1');
    exit;
}

$stmt = $pdo->query("
    SELECT sb.*, u.name as client_name, u.email as client_email 
    FROM service_bookings sb 
    JOIN users u ON sb.client_id = u.id 
    ORDER BY sb.created_at DESC
");
$bookings = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Client Bookings | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Client Bookings";
            $header_subtitle = "Manage and track client service requests.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <?php if (isset($_GET['success'])): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: var(--accent-green); padding: var(--space-md); border-radius: var(--radius-md); margin-bottom: var(--space-lg);">
                        Booking updated successfully.
                    </div>
                <?php endif; ?>

                <?php if (empty($bookings)): ?>
                    <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                        <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: var(--space-lg); opacity: 0.2;"></i>
                        <p>No service bookings found.</p>
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
                                            <p style="font-size: 0.875rem; color: var(--gray-400);">Client: <?php echo htmlspecialchars($booking['client_name']); ?> • Booking ID: #SB-<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?> • <?php echo date('M d, Y', strtotime($booking['created_at'])); ?></p>
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
                                        <p style="font-size: 0.8rem; color: var(--gray-500); text-transform: uppercase; letter-spacing: 1px; margin-bottom: var(--space-xs);">Requirements</p>
                                        <p style="font-size: 0.9rem; color: var(--gray-300);"><?php echo htmlspecialchars($booking['description']) ?: 'No additional notes provided.'; ?></p>
                                        <?php if (!empty($booking['requirement_file'])): ?>
                                        <a href="<?php echo htmlspecialchars($booking['requirement_file']); ?>" target="_blank" style="display: inline-block; margin-top: 10px; color: var(--accent-purple);"><i class="fas fa-file-download"></i> View Requirement File</a>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; align-items: flex-end;">
                                        <button class="btn btn-secondary btn-sm" onclick="openUpdateModal(<?php echo $booking['id']; ?>, '<?php echo $booking['status']; ?>', <?php echo $booking['progress']; ?>)">Update Status</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Update Booking Modal -->
    <div id="updateModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); display: none; align-items: center; justify-content: center; z-index: 3000;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.25rem;">Update Booking</h3>
                <button onclick="document.getElementById('updateModal').style.display='none'" style="background: none; border: none; color: var(--gray-400); font-size: 1.2rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form action="" method="POST">
                <input type="hidden" name="action" value="update_booking">
                <input type="hidden" name="booking_id" id="modalBookingId">
                
                <div class="form-group" style="margin-bottom: var(--space-lg);">
                    <label class="form-label">Status</label>
                    <select name="status" id="modalStatus" class="form-input" required>
                        <option value="pending">Pending</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="form-group" style="margin-bottom: var(--space-xl);">
                    <label class="form-label">Progress (%)</label>
                    <input type="number" name="progress" id="modalProgress" class="form-input" min="0" max="100" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('updateModal').style.display='none'">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openUpdateModal(id, status, progress) {
        document.getElementById('modalBookingId').value = id;
        document.getElementById('modalStatus').value = status;
        document.getElementById('modalProgress').value = progress;
        document.getElementById('updateModal').style.display = 'flex';
    }
    </script>
    <script src="js/notifications.js"></script>
</body>
</html>
