<?php
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    
    // Check if services table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'services'");
    if ($stmt->rowCount() == 0) {
        echo "Services table does not exist\n";
        exit;
    }
    
    // Check services table structure
    $stmt = $pdo->query("DESCRIBE services");
    $columns = $stmt->fetchAll();
    echo "Services table columns:\n";
    foreach ($columns as $column) {
        echo "- " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }
    
    // Check if there are any services
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM services");
    $count = $stmt->fetch();
    echo "\nNumber of services: " . $count['count'] . "\n";
    
    // Get sample services
    $stmt = $pdo->query("SELECT id, service_name FROM services LIMIT 5");
    $services = $stmt->fetchAll();
    echo "\nSample services:\n";
    foreach ($services as $service) {
        echo "- ID: " . $service['id'] . ", Name: " . $service['service_name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 