<?php
require_once '../cache/db.php';

try {
    // Check service_questions table structure
    $stmt = $pdo->query("DESCRIBE service_questions");
    $columns = $stmt->fetchAll();
    
    echo "service_questions table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    echo "\n\nSample data from service_questions:\n";
    $stmt = $pdo->query("SELECT * FROM service_questions LIMIT 3");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        print_r($row);
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 