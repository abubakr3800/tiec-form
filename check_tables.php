<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== هيكل جدول service_questions ===\n";
    $stmt = $pdo->query("DESCRIBE service_questions");
    while($row = $stmt->fetch()) {
        print_r($row);
    }
    
    echo "\n=== هيكل جدول question_options ===\n";
    $stmt = $pdo->query("DESCRIBE question_options");
    while($row = $stmt->fetch()) {
        print_r($row);
    }
    
    echo "\n=== الخدمات الموجودة ===\n";
    $stmt = $pdo->query("SELECT * FROM services");
    while($row = $stmt->fetch()) {
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
?> 