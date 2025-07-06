<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== الخدمات الموجودة ===\n";
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order");
    while($row = $stmt->fetch()) {
        echo "ID: " . $row['id'] . " - " . $row['name_ar'] . " - Type: " . $row['service_type'] . "\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
?> 