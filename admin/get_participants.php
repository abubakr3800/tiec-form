<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    exit('غير مصرح');
}

header('Content-Type: application/json');

try {
    $pdo = getDBConnection();
    
    // جلب جميع المشاركين
    $stmt = $pdo->query("
        SELECT 
            id,
            name,
            national_id,
            governorate,
            CASE 
                WHEN gender = 'male' THEN 'ذكر'
                WHEN gender = 'female' THEN 'أنثى'
                ELSE gender
            END as gender,
            age,
            phone,
            CASE 
                WHEN participant_type = 'student' THEN 'طالب'
                WHEN participant_type = 'employee' THEN 'موظف'
                WHEN participant_type = 'other' THEN 'أخرى'
                ELSE participant_type
            END as participant_type,
            DATE_FORMAT(registration_date, '%Y-%m-%d %H:%i') as registration_date,
            training_confirmation
        FROM participants 
        ORDER BY registration_date DESC
    ");
    
    $participants = $stmt->fetchAll();
    
    echo json_encode([
        'data' => $participants
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => 'خطأ غير متوقع: ' . $e->getMessage()
    ]);
}
?> 