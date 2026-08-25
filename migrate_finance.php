<?php
require_once 'php/db_connect.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        project_id INT,
        invoice_number VARCHAR(50) UNIQUE,
        amount DECIMAL(15,2),
        status ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
        due_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
    )");

    // Add some mock data
    $pdo->exec("INSERT IGNORE INTO invoices (project_id, invoice_number, amount, status, due_date) VALUES 
        (1, 'INV-2026-001', 4500.00, 'paid', '2026-02-15'),
        (2, 'INV-2026-002', 2800.00, 'pending', '2026-03-01'),
        (3, 'INV-2026-003', 1500.00, 'overdue', '2026-02-10')");

    echo "Migration successful: Created invoices table and added mock data.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
