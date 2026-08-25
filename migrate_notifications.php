<?php
require_once 'php/db_connect.php';

try {
    // Create Notifications Table
    $sql = "
    CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        type VARCHAR(50) DEFAULT 'general',
        title VARCHAR(255) NOT NULL,
        message TEXT,
        link VARCHAR(255),
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "Notifications table created successfully.\n";

    // Insert Sample Notifications for the admin (Sarah - ID 1 and Victor - ID 2)
    $samples = [
        [1, 'task', 'New Task Assigned', 'You have been assigned to review the TechStart mockups.', 'staff-tasks.php'],
        [1, 'message', 'New Message', 'Michael Obwoge sent you a message regarding TechStart.', 'staff-messages.php'],
        [2, 'project', 'Project Update', 'The Hope Foundation Rebranding project is now in Review.', 'staff-projects.php'],
        [2, 'general', 'Welcome Back', 'Check out the new Command Center features!', 'admin-dashboard.php']
    ];

    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    foreach ($samples as $sample) {
        $stmt->execute($sample);
    }
    echo "Sample notifications inserted.\n";

} catch (PDOException $e) {
    die("Error creating notifications table: " . $e->getMessage());
}
