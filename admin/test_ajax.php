<?php
// بدء output buffering
ob_start();

session_start();
require_once '../config/database.php';

// مسح أي output سابق
ob_clean();

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // اختبار جلب المدربين
    $stmt = $pdo->query("SELECT id, name_ar, email, specialization, is_active FROM trainers LIMIT 5");
    $trainers = $stmt->fetchAll();
    
    $data = [];
    foreach ($trainers as $trainer) {
        $data[] = [
            'id' => $trainer['id'],
            'name' => htmlspecialchars($trainer['name_ar']),
            'email' => htmlspecialchars($trainer['email']),
            'specialization' => htmlspecialchars($trainer['specialization']),
            'is_active' => $trainer['is_active'],
            'created_at' => date('Y-m-d'),
            'last_login' => null
        ];
    }
    
    echo json_encode(['data' => $data]);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'خطأ: ' . $e->getMessage()]);
}

// إرسال الـ output
ob_end_flush();
?> 