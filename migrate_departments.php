<?php
require_once 'php/db_connect.php';

try {
    // 1. Add department to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN department VARCHAR(100) DEFAULT 'General' AFTER role");
    
    // 2. Add department to projects table
    $pdo->exec("ALTER TABLE projects ADD COLUMN department VARCHAR(100) DEFAULT 'General' AFTER type");

    // 3. Update existing data for demo purposes
    // Staff roles
    $pdo->exec("UPDATE users SET department = 'Design & Branding' WHERE id IN (1, 4)"); // Sarah, Grace
    $pdo->exec("UPDATE users SET department = 'Development' WHERE id = 3"); // Michael
    $pdo->exec("UPDATE users SET department = 'Administrative' WHERE id = 2"); // Victor
    $pdo->exec("UPDATE users SET department = 'Digital Strategy' WHERE id = 5"); // Fatma

    // Project categories
    $pdo->exec("UPDATE projects SET department = 'Development' WHERE type = 'Web Development'");
    $pdo->exec("UPDATE projects SET department = 'Design & Branding' WHERE type = 'Brand Identity' OR type = 'UI/UX Design'");
    $pdo->exec("UPDATE projects SET department = 'Digital Strategy' WHERE type = 'Digital Marketing'");
    
    echo "Migration successful: Added 'department' to users and projects tables.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
