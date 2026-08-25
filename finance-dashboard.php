<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_dept = $_SESSION['user_department'];

if ($user_dept !== 'Finance' && $_SESSION['user_role'] !== 'admin') {
    header('Location: staff-dashboard.php');
    exit;
}

// Finance Stats
$stats = [
    'total_revenue' => $pdo->query("SELECT SUM(amount) FROM invoices WHERE status = 'paid'")->fetchColumn() ?: 0,
    'pending_invoices' => $pdo->query("SELECT COUNT(*) FROM invoices WHERE status = 'pending'")->fetchColumn(),
    'overdue_amount' => $pdo->query("SELECT SUM(amount) FROM invoices WHERE status = 'overdue'")->fetchColumn() ?: 0,
    'billable_hours' => $pdo->query("SELECT SUM(hours) FROM time_logs WHERE status = 'approved'")->fetchColumn() ?: 0
];

// Fetch Recent Invoices
$recentInvoices = $pdo->query("
    SELECT i.*, sb.service_name, u.name as client_name 
    FROM invoices i 
    LEFT JOIN service_bookings sb ON i.booking_id = sb.id 
    LEFT JOIN users u ON i.client_id = u.id
    ORDER BY i.created_at DESC LIMIT 5
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Dashboard | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .invoice-badge { padding: 4px 10px; border-radius: var(--radius-full); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .badge-paid { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .badge-pending { background: rgba(245, 158, 11, 0.1); color: var(--accent-orange); }
        .badge-overdue { background: rgba(244, 63, 94, 0.1); color: #f43f5e; }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Financial Overview";
            $header_subtitle = "Track revenue, manage billing, and monitor project budgets.";
            include 'php/includes/dashboard_header.php'; 
            ?>

            <div class="dashboard-cards">
                <div class="dashboard-card">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase;">Total Revenue</p>
                    <h3 style="color: var(--accent-green);"><?php echo number_format($stats['total_revenue']); ?> TZS</h3>
                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 5px;"><i class="fas fa-arrow-up"></i> 12% vs last month</div>
                </div>
                <div class="dashboard-card">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase;">Pending Invoices</p>
                    <h3><?php echo $stats['pending_invoices']; ?></h3>
                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 5px;">Awaiting payment</div>
                </div>
                <div class="dashboard-card">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase;">Overdue Balance</p>
                    <h3 style="color: #f43f5e;"><?php echo number_format($stats['overdue_amount']); ?> TZS</h3>
                    <div style="font-size: 0.75rem; color: #f43f5e; margin-top: 5px;"><i class="fas fa-exclamation-circle"></i> Needs attention</div>
                </div>
                <div class="dashboard-card">
                    <p style="font-size: 0.8rem; color: var(--gray-400); text-transform: uppercase;">Billable Hours</p>
                    <h3><?php echo number_format($stats['billable_hours'], 1); ?>h</h3>
                    <div style="font-size: 0.75rem; color: var(--gray-500); margin-top: 5px;">This quarter</div>
                </div>
            </div>

            <div class="dashboard-content-grid">
                <div class="dashboard-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                        <h3 style="font-size: 1.25rem;">Recent Invoices</h3>
                        <button class="btn btn-primary btn-sm" onclick="document.getElementById('addInvoiceModal').style.display='flex'"><i class="fas fa-plus"></i> New Invoice</button>
                    </div>
                    <div class="table-container">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                                <tr>
                                    <th style="padding: 12px;">Invoice #</th>
                                    <th style="padding: 12px;">Booking / Client</th>
                                    <th style="padding: 12px;">Phase</th>
                                    <th style="padding: 12px;">Amount</th>
                                    <th style="padding: 12px;">Status</th>
                                    <th style="padding: 12px;">Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recentInvoices)): ?>
                                <tr>
                                    <td colspan="6" style="padding: 20px; text-align: center; color: var(--gray-500);">No invoices found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($recentInvoices as $inv): ?>
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                                        <td style="padding: 12px; font-weight: 600;"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                        <td style="padding: 12px; font-size: 0.9rem;">
                                            <div style="color: white;"><?php echo htmlspecialchars($inv['service_name'] ?? 'N/A'); ?></div>
                                            <div style="font-size: 0.75rem; color: var(--gray-400);"><?php echo htmlspecialchars($inv['client_name'] ?? 'Unknown'); ?></div>
                                        </td>
                                        <td style="padding: 12px; font-size: 0.85rem; color: var(--primary-blue-light);"><?php echo htmlspecialchars($inv['payment_type'] ?? 'Full'); ?></td>
                                        <td style="padding: 12px; font-weight: 700;"><?php echo number_format($inv['amount']); ?> TZS</td>
                                        <td style="padding: 12px;">
                                            <span class="invoice-badge badge-<?php echo $inv['status']; ?>"><?php echo ucfirst($inv['status']); ?></span>
                                        </td>
                                        <td style="padding: 12px; font-size: 0.85rem; color: var(--gray-400);"><?php echo date('M d, Y', strtotime($inv['due_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h3 style="font-size: 1.25rem; margin-bottom: var(--space-xl);">Financial Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-chart-line" style="margin-right: 10px;"></i> Revenue Reports
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;" onclick="document.getElementById('addInvoiceModal').style.display='flex'">
                            <i class="fas fa-file-invoice-dollar" style="margin-right: 10px;"></i> Generate Bill
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; text-align: left;">
                            <i class="fas fa-piggy-bank" style="margin-right: 10px;"></i> Expense Tracking
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php
    // Fetch clients and bookings for the modal
    $bookings = $pdo->query("
        SELECT sb.id, sb.service_name, sb.client_id, u.name as client_name 
        FROM service_bookings sb 
        LEFT JOIN users u ON sb.client_id = u.id 
        ORDER BY sb.created_at DESC
    ")->fetchAll();
    ?>
    <!-- Add Invoice Modal -->
    <div id="addInvoiceModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">Create New Invoice</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('addInvoiceModal').style.display='none'"></i>
            </div>
            <form id="addInvoiceForm">
                <input type="hidden" name="action" value="create_invoice">
                <div class="form-group">
                    <label class="form-label">Booked Service</label>
                    <select name="booking_id" class="form-input" required>
                        <option value="">Select Booking...</option>
                        <?php foreach($bookings as $b): ?>
                            <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['service_name'] . ' (' . ($b['client_name'] ?? 'Unknown Client') . ')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Phase</label>
                    <select name="payment_type" class="form-input" required>
                        <option value="70% Deposit">Initial Deposit (70%)</option>
                        <option value="30% Final Payment">Final Payment (30%)</option>
                        <option value="Full Payment">Full Payment (100%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Invoice Number</label>
                    <input type="text" name="invoice_number" class="form-input" value="INV-<?php echo time(); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (TZS) <span style="font-size: 0.75rem; font-weight: normal; color: var(--gray-400);">(Enter the exact portion to be paid in TZS)</span></label>
                    <input type="number" name="amount" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Create Invoice</button>
            </form>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script>
        document.getElementById('addInvoiceForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('api/actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error creating invoice');
                }
            } catch (error) {
                alert('An error occurred while creating the invoice.');
            }
        });
    </script>
</body>
</html>
