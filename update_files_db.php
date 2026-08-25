<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'php/db_connect.php';

try {
    // Create folders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS shared_folders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        folder_name VARCHAR(255) NOT NULL,
        parent_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Add folder_id to shared_files
    $pdo->exec("ALTER TABLE shared_files ADD COLUMN IF NOT EXISTS folder_id INT DEFAULT NULL");
    
    // Add foreign key if it doesn't exist
    // Note: SQLite doesn't support ADD CONSTRAINT easily, but MySQL does. 
    // Assuming MySQL/XAMPP:
    try {
        $pdo->exec("ALTER TABLE shared_files ADD CONSTRAINT fk_folder FOREIGN KEY (folder_id) REFERENCES shared_folders(id) ON DELETE SET NULL");
    } catch (Exception $e) {
        // Might already exist
    }

    echo "Database schema updated successfully for folders.";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
