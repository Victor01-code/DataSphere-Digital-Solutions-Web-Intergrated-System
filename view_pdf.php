<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$type = $_GET['type'] ?? 'invoice';
$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    die("Invalid record ID.");
}

$data = null;
$title = "Document";

if ($type === 'invoice') {
    $stmt = $pdo->prepare("
        SELECT i.*, sb.service_name, u.name as client_name, u.email as client_email
        FROM invoices i 
        LEFT JOIN service_bookings sb ON i.booking_id = sb.id
        LEFT JOIN users u ON i.client_id = u.id
        WHERE i.id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) die("Invoice not found.");
    $title = "Invoice #" . htmlspecialchars($data['invoice_number']);
} elseif ($type === 'booking') {
    $stmt = $pdo->prepare("
        SELECT sb.*, u.name as client_name, u.email as client_email
        FROM service_bookings sb
        LEFT JOIN users u ON sb.client_id = u.id
        WHERE sb.id = ?
    ");
    $stmt->execute([$id]);
    $data = $stmt->fetch();
    if (!$data) die("Booking not found.");
    $title = "Booking #SB-" . str_pad($data['id'], 5, '0', STR_PAD_LEFT);
} else {
    die("Unknown document type.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> | DataSphere</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }
        .action-bar {
            background: #0f172a;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            border: none;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        .btn-primary:hover {
            background: #1d4ed8;
        }
        .btn-secondary {
            background: #334155;
            color: white;
        }
        .btn-secondary:hover {
            background: #475569;
        }
        .document-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 60px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-radius: 12px;
        }
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 30px;
            margin-bottom: 40px;
        }
        .logo-area img {
            height: 48px;
            margin-bottom: 10px;
        }
        .company-details {
            font-size: 0.85rem;
            color: #64748b;
            line-height: 1.5;
        }
        .document-meta {
            text-align: right;
        }
        .doc-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            color: #0f172a;
            margin: 0 0 5px 0;
        }
        .doc-number {
            font-size: 1.1rem;
            font-weight: 600;
            color: #3b82f6;
            margin-bottom: 15px;
        }
        .status-pill {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .status-paid, .status-completed { background: #dcfce7; color: #15803d; }
        .status-pending, .status-in-progress { background: #fef9c3; color: #a16207; }
        .status-overdue { background: #fee2e2; color: #b91c1c; }

        .client-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            background: #f8fafc;
            padding: 25px;
            border-radius: 8px;
        }
        .client-info h4 { margin: 0 0 8px 0; color: #0f172a; font-size: 1.1rem; }
        .client-info p { margin: 4px 0; color: #475569; font-size: 0.9rem; }
        
        .dates-info { text-align: right; }
        .dates-info p { margin: 4px 0; font-size: 0.9rem; color: #475569; }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .item-table th {
            background: #0f172a;
            color: white;
            text-align: left;
            padding: 14px 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .item-table td {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 0.95rem;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .totals-box {
            width: 320px;
            background: #f8fafc;
            padding: 25px;
            border-radius: 8px;
        }
        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 0.95rem;
            color: #475569;
        }
        .total-grand {
            border-top: 2px solid #cbd5e1;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }
        .footer-note {
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
            text-align: center;
            font-size: 0.85rem;
            color: #64748b;
        }

        @media print {
            .action-bar { display: none !important; }
            body { background: white; }
            .document-container { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="action-bar">
        <div>
            <a href="javascript:history.back()" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Download / Print PDF</button>
        </div>
    </div>

    <div class="document-container">
        <div class="header-section">
            <div class="logo-area">
                <img src="assets/images/logo.png" alt="DataSphere Logo" onerror="this.src='https://via.placeholder.com/150x40?text=DataSphere'">
                <div class="company-details">
                    <strong>DataSphere Digital Solutions</strong><br>
                    Arusha, Tanzania<br>
                    info@dataspheredns.com | +255 693 038 737
                </div>
            </div>
            <div class="document-meta">
                <h1 class="doc-title"><?php echo $type === 'invoice' ? 'INVOICE' : 'SERVICE BOOKING'; ?></h1>
                <div class="doc-number"><?php echo htmlspecialchars($title); ?></div>
                <span class="status-pill status-<?php echo strtolower($data['status'] ?? 'pending'); ?>">
                    <?php echo htmlspecialchars(ucfirst($data['status'] ?? 'pending')); ?>
                </span>
            </div>
        </div>

        <div class="client-section">
            <div class="client-info">
                <h4>Billed To:</h4>
                <p><strong><?php echo htmlspecialchars($data['client_name'] ?? 'Client Name'); ?></strong></p>
                <p><?php echo htmlspecialchars($data['client_email'] ?? ''); ?></p>
            </div>
            <div class="dates-info">
                <p><strong>Date Issued:</strong> <?php echo date('M d, Y', strtotime($data['created_at'])); ?></p>
                <?php if ($type === 'invoice'): ?>
                    <p><strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($data['due_date'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($type === 'invoice'): ?>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($data['service_name'] ?? 'DataSphere Digital Solution'); ?></strong><br>
                        <span style="font-size: 0.85rem; color: #64748b;"><?php echo htmlspecialchars($data['payment_type'] ?? 'Standard Billing'); ?></span>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        <?php echo number_format($data['amount']); ?> TZS
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="totals-section">
            <div class="totals-box">
                <div class="total-line">
                    <span>Subtotal</span>
                    <span><?php echo number_format($data['amount']); ?> TZS</span>
                </div>
                <div class="total-line">
                    <span>Tax (0%)</span>
                    <span>0 TZS</span>
                </div>
                <div class="total-line total-grand">
                    <span>Total Due</span>
                    <span><?php echo number_format($data['amount']); ?> TZS</span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <table class="item-table">
            <thead>
                <tr>
                    <th>Service Details</th>
                    <th style="text-align: right;">Progress</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($data['service_name']); ?></strong><br>
                        <p style="margin: 8px 0 0 0; font-size: 0.85rem; color: #64748b; line-height: 1.5;">
                            <strong>Notes:</strong> <?php echo htmlspecialchars($data['description'] ?: 'No additional notes provided.'); ?>
                        </p>
                    </td>
                    <td style="text-align: right; font-weight: 600;">
                        <?php echo $data['progress']; ?>%
                    </td>
                </tr>
            </tbody>
        </table>
        <?php endif; ?>

        <div class="footer-note">
            <p>Thank you for choosing DataSphere Digital Solutions! For payment inquiries or support, please contact info@dataspheredns.com.</p>
        </div>
    </div>
</body>
</html>
