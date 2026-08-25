<?php
require_once 'php/db_connect.php';

try {
    // Add HR and Finance to the department options or update users
    // If the columns already exist, we just update.
    
    // 1. Add new departments to demo users if needed
    // Let's create some dummy users for these roles if they don't exist, 
    // or just assume they will be added via the admin panel.
    
    // 2. Ensure all roles have a department assigned
    $pdo->exec("UPDATE users SET department = 'HR' WHERE title LIKE '%HR%' OR title LIKE '%Human%'");
    $pdo->exec("UPDATE users SET department = 'Finance' WHERE title LIKE '%Finance%' OR title LIKE '%Accountant%'");
    $pdo->exec("UPDATE users SET department = 'Management' WHERE role = 'admin' AND department = 'General'");

    echo "Migration successful: Updated specialized departments.";
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
