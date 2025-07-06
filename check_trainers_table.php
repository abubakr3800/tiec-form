<?php
require_once 'cache/db.php'; // Correct path for db.php

try {
    // $pdo is available from db.php
    // Check current table structure
    echo "Current trainers table columns:\n";
    $stmt = $pdo->query('DESCRIBE trainers');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Check if last_login column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM trainers LIKE 'last_login'");
    $exists = $stmt->fetch();
    
    if (!$exists) {
        echo "\nAdding last_login column...\n";
        $pdo->exec("ALTER TABLE trainers ADD COLUMN last_login DATETIME NULL");
        echo "last_login column added successfully!\n";
    } else {
        echo "\nlast_login column already exists.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 