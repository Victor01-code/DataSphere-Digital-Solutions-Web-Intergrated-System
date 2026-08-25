<?php
require_once 'php/db_connect.php';

try {
    $pdo->exec("ALTER TABLE documents ADD COLUMN parent_id INT DEFAULT NULL");
    $pdo->exec("ALTER TABLE documents ADD COLUMN is_folder BOOLEAN DEFAULT FALSE");
    $pdo->exec("ALTER TABLE documents ADD FOREIGN KEY (parent_id) REFERENCES documents(id) ON DELETE CASCADE");

    echo "Migration successful: Added folder support to documents table.";
} catch (PDOException $e) {
    echo "Migration note: Columns might already exist. " . $e->getMessage();
}
?>
