<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== جميع الجداول في قاعدة البيانات ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    while($row = $stmt->fetch()) {
        $table = array_values($row)[0];
        echo "جدول: " . $table . "\n";
        
        // عدد الصفوف في كل جدول
        $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
        $count = $count_stmt->fetch()['count'];
        echo "  عدد الصفوف: " . $count . "\n";
        
        if ($count > 0) {
            // عرض أول صف
            $data_stmt = $pdo->query("SELECT * FROM `$table` LIMIT 1");
            $data = $data_stmt->fetch();
            echo "  عينة من البيانات: ";
            print_r($data);
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
?> 