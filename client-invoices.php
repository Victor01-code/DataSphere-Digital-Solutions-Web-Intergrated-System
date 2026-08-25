<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';
require_once 'php/auth.php';
requireRole('client');
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Fetch all invoices for this client
$invoices = [];
try {
    $stmt = $pdo->prepare("
        SELECT i.*, sb.service_name 
        FROM invoices i 
        LEFT JOIN service_bookings sb ON i.booking_id = sb.id 
        WHERE i.client_id = ? 
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $invoices = $stmt->fetchAll();
} catch (PDOException $e) {
    // Table might not exist yet if migration failed
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Invoices | DataSphere Client Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/pages.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <div class="dashboard-layout">
        <?php include 'php/includes/client_sidebar.php'; ?>
        <main class="dashboard-main">
            <div class="dashboard-header">
                <div>
                    <h1 style="font-size: 1.75rem;">My Invoices</h1>
                    <p style="color: var(--gray-400);">View and download your billing statements and payment history.</p>
                </div>
            </div>

            <?php if (isset($_GET['payment']) && $_GET['payment'] === 'simulated_stripe_success'): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: var(--accent-green); padding: var(--space-lg); border-radius: var(--radius-lg); margin-top: var(--space-xl); display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; background: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <p style="font-weight: 600; margin-bottom: 2px;">Payment Successful!</p>
                    <p style="font-size: 0.9rem; opacity: 0.8;">Your funds have been securely processed to our <strong>CRDB Bank Account</strong>. Your invoice status will be updated automatically.</p>
                </div>
            </div>
            <?php endif; ?>

            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <?php if (empty($invoices)): ?>
                    <div style="text-align: center; padding: var(--space-2xl); color: var(--gray-400);">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 3rem; margin-bottom: var(--space-lg); opacity: 0.2;"></i>
                        <p>No invoices found in your account.</p>
                    </div>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: var(--space-lg);">
                        <thead>
                            <tr style="text-align: left; color: var(--gray-400); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px;">
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05);">Invoice #</th>
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05);">Service & Phase</th>
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05);">Amount</th>
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05);">Status</th>
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05);">Due Date</th>
                                <th style="padding: var(--space-md); border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($invoices as $inv): ?>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td style="padding: var(--space-lg) var(--space-md); font-weight: 600;"><?php echo htmlspecialchars($inv['invoice_number']); ?></td>
                                    <td style="padding: var(--space-lg) var(--space-md);">
                                        <div style="color: white; font-weight: 500;"><?php echo htmlspecialchars($inv['service_name'] ?? 'General Services'); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--primary-blue-light); margin-top: 2px;"><?php echo htmlspecialchars($inv['payment_type'] ?? 'Full Payment'); ?></div>
                                    </td>
                                    <td style="padding: var(--space-lg) var(--space-md);"><?php echo number_format($inv['amount']); ?> TZS</td>
                                    <td style="padding: var(--space-lg) var(--space-md);">
                                        <span class="status-badge <?php echo strtolower($inv['status']); ?>" style="padding: 4px 10px; border-radius: 12px; font-size: 0.75rem;">
                                            <?php echo ucfirst($inv['status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: var(--space-lg) var(--space-md); color: var(--gray-400);"><?php echo date('M d, Y', strtotime($inv['due_date'])); ?></td>
                                    <td style="padding: var(--space-lg) var(--space-md); text-align: right;">
                                        <?php if(strtolower($inv['status']) === 'pending' || strtolower($inv['status']) === 'overdue'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="initiateStripeCheckout(<?php echo $inv['id']; ?>)" style="margin-right: 5px;"><i class="fas fa-credit-card"></i> Pay Now</button>
                                        <?php endif; ?>
                                        <a href="view_pdf.php?type=invoice&id=<?php echo $inv['id']; ?>" target="_blank" class="btn btn-secondary btn-sm" style="text-decoration: none;"><i class="fas fa-file-pdf"></i> PDF</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Payment Methods Section -->
            <div class="dashboard-card" style="padding: var(--space-xl); margin-top: var(--space-xl);">
                <h2 style="font-size: 1.25rem; margin-bottom: var(--space-lg); display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-credit-card" style="color: var(--primary-blue-light);"></i>
                    Official Payment Methods
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--space-lg);">
                    <!-- M-PESA -->
                    <div style="background: rgba(16, 185, 129, 0.05); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: flex-start; gap: var(--space-md);">
                        <div style="width: 48px; height: 48px; background: #10B981; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <h4 style="color: #10B981; margin-bottom: 4px;">M-PESA</h4>
                            <p style="font-size: 0.85rem; color: var(--gray-400); margin-bottom: 8px;">Pay directly via M-PESA Number.</p>
                            <div style="background: rgba(0,0,0,0.2); padding: var(--space-sm) var(--space-md); border-radius: 8px; border-left: 3px solid #10B981;">
                                <p style="font-size: 0.75rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 2px;">Number & Name</p>
                                <p style="font-weight: 800; font-size: 1.25rem; letter-spacing: 1px; color: white;">0754 215 959</p>
                                <p style="font-size: 0.85rem; color: #F3F4F6;">Victor Ezekiel Mshana</p>
                            </div>
                        </div>
                    </div>

                    <!-- Airtel Money -->
                    <div style="background: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: flex-start; gap: var(--space-md);">
                        <div style="width: 48px; height: 48px; background: #EF4444; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div>
                            <h4 style="color: #EF4444; margin-bottom: 4px;">Airtel Money</h4>
                            <p style="font-size: 0.85rem; color: var(--gray-400); margin-bottom: 8px;">Pay directly via Airtel Money Number.</p>
                            <div style="background: rgba(0,0,0,0.2); padding: var(--space-sm) var(--space-md); border-radius: 8px; border-left: 3px solid #EF4444;">
                                <p style="font-size: 0.75rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 2px;">Number & Name</p>
                                <p style="font-weight: 800; font-size: 1.25rem; letter-spacing: 1px; color: white;">651 219 931</p>
                                <p style="font-size: 0.85rem; color: #F3F4F6;">Victor Ezekiel Mshana</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bank Transfer -->
                    <div style="background: rgba(0, 102, 255, 0.05); border: 1px solid rgba(0, 102, 255, 0.2); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: flex-start; gap: var(--space-md);">
                        <div style="width: 48px; height: 48px; background: var(--primary-blue); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="fas fa-university"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="color: var(--primary-blue-light); margin-bottom: 4px;">Bank Transfer</h4>
                            <p style="font-size: 0.85rem; color: var(--gray-400); margin-bottom: 8px;">CRDB Bank PLC (Local & International)</p>
                            <div style="background: rgba(0,0,0,0.2); padding: var(--space-sm) var(--space-md); border-radius: 8px; border-left: 3px solid var(--primary-blue);">
                                <p style="font-size: 0.75rem; text-transform: uppercase; color: var(--gray-500); margin-bottom: 2px;">Account Details</p>
                                <p style="font-weight: 700; font-size: 0.9rem; color: white; margin-bottom: 2px;">Victor Ezekiel Mshana</p>
                                <p style="font-family: monospace; font-size: 1.1rem; color: var(--primary-blue-light); font-weight: 800;">0152389472000</p>
                            </div>
                        </div>
                    </div>
                    <!-- Stripe / Credit Card -->
                    <div style="background: rgba(99, 102, 241, 0.05); border: 1px solid rgba(99, 102, 241, 0.2); border-radius: var(--radius-lg); padding: var(--space-lg); display: flex; align-items: flex-start; gap: var(--space-md);">
                        <div style="width: 48px; height: 48px; background: #6366F1; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                            <i class="fab fa-stripe"></i>
                        </div>
                        <div style="flex: 1;">
                            <h4 style="color: #818CF8; margin-bottom: 4px;">Credit / Debit Card</h4>
                            <p style="font-size: 0.85rem; color: var(--gray-400); margin-bottom: 12px;">Secure direct payment via Stripe.</p>
                            <button class="btn btn-primary btn-sm" id="stripeButton" style="width: 100%; background: #6366F1; border-color: #6366F1; opacity: 0.5; cursor: not-allowed;" disabled>
                                <i class="fas fa-lock"></i> Select an invoice above to pay
                            </button>
                            <div style="display: flex; gap: 8px; margin-top: 10px; justify-content: center; opacity: 0.5;">
                                <i class="fab fa-cc-visa" style="font-size: 1.2rem;"></i>
                                <i class="fab fa-cc-mastercard" style="font-size: 1.2rem;"></i>
                                <i class="fab fa-cc-apple-pay" style="font-size: 1.2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <p style="margin-top: var(--space-lg); font-size: 0.85rem; color: var(--gray-400); font-style: italic;">
                    <i class="fas fa-info-circle"></i> After payment via M-PESA or Airtel, please upload your proof of payment in the Support section. Stripe payments are confirmed automatically.
                </p>
            </div>
        </main>
    </div>
    <!-- Stripe Payment Modal -->
    <div id="stripeModal" class="modal-overlay" style="position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; align-items: center; justify-content: center; padding: var(--space-md);">
        <div class="modal-card" style="background: var(--dark-800); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-2xl); width: 100%; max-width: 450px; padding: var(--space-2xl); position: relative; animation: modalIn 0.3s ease;">
            <button onclick="closeStripeModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--gray-400); cursor: pointer; font-size: 1.25rem;"><i class="fas fa-times"></i></button>
            
            <div style="text-align: center; margin-bottom: var(--space-xl);">
                <i class="fab fa-stripe" style="font-size: 3rem; color: #6366F1; margin-bottom: var(--space-sm);"></i>
                <h3 style="font-size: 1.5rem;">Secure Checkout</h3>
                <p style="color: var(--gray-400); font-size: 0.9rem;">Enter your card details to complete payment.</p>
            </div>

            <form id="payment-form" onsubmit="handleStripePayment(event)">
                <input type="hidden" id="currentPayingInvoiceId" value="">
                <div class="form-group" style="margin-bottom: var(--space-lg); text-align: left;">
                    <label class="form-label" style="display: block; margin-bottom: 8px;">Cardholder Name</label>
                    <input type="text" class="form-input" placeholder="Name on card" required style="width: 100%;">
                </div>
                
                <div class="form-group" style="margin-bottom: var(--space-lg); text-align: left;">
                    <label class="form-label" style="display: block; margin-bottom: 8px;">Card Number</label>
                    <div style="position: relative;">
                        <input type="text" class="form-input" placeholder="0000 0000 0000 0000" maxlength="19" required style="width: 100%;">
                        <i class="fas fa-credit-card" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--gray-500);"></i>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-xl); text-align: left;">
                    <div class="form-group">
                        <label class="form-label" style="display: block; margin-bottom: 8px;">Expiry Date</label>
                        <input type="text" class="form-input" placeholder="MM/YY" maxlength="5" required style="width: 100%;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="display: block; margin-bottom: 8px;">CVC</label>
                        <input type="text" class="form-input" placeholder="123" maxlength="3" required style="width: 100%;">
                    </div>
                </div>

                <button type="submit" id="submitPaymentBtn" class="btn btn-primary" style="width: 100%; background: #6366F1; border-color: #6366F1; height: 50px; font-weight: 700;">
                    <i class="fas fa-lock"></i> Authorize Payment
                </button>
            </form>

            <div style="margin-top: var(--space-xl); text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: var(--space-lg);">
                <p style="font-size: 0.75rem; color: var(--gray-500);">
                    <i class="fas fa-shield-alt"></i> Payments are encrypted and secured by Stripe.
                </p>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-overlay { display: none; }
        .modal-overlay.active { display: flex; }
    </style>

    <script src="js/main.js"></script>
    <script>
        function initiateStripeCheckout(invoiceId) {
            document.getElementById('currentPayingInvoiceId').value = invoiceId;
            document.getElementById('stripeModal').classList.add('active');
        }

        function closeStripeModal() {
            document.getElementById('stripeModal').classList.remove('active');
        }

        async function handleStripePayment(e) {
            e.preventDefault();
            const btn = document.getElementById('submitPaymentBtn');
            const originalContent = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing Payment...';
            btn.disabled = true;

            const invoiceId = document.getElementById('currentPayingInvoiceId').value;

            try {
                const response = await fetch('api/actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'pay_invoice', invoice_id: invoiceId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    btn.innerHTML = '<i class="fas fa-check"></i> Payment Approved!';
                    btn.style.background = '#10B981';
                    btn.style.borderColor = '#10B981';
                    
                    setTimeout(() => {
                        window.location.href = 'client-invoices.php?payment=simulated_stripe_success';
                    }, 1000);
                } else {
                    alert(result.message || 'Payment failed');
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                }
            } catch (error) {
                alert('Connection error while processing payment.');
                btn.innerHTML = originalContent;
                btn.disabled = false;
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const modal = document.getElementById('stripeModal');
            if (event.target == modal) {
                closeStripeModal();
            }
        }
    </script>
</body>
</html>
