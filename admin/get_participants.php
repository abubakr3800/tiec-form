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
    
    // جلب جميع المشاركين مع أول تاريخ تسجيل لهم
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.name,
            p.national_id,
            p.governorate,
            CASE 
                WHEN p.gender = 'male' THEN 'ذكر'
                WHEN p.gender = 'female' THEN 'أنثى'
                ELSE p.gender
            END as gender,
            p.age,
            p.phone,
            CASE 
                WHEN p.participant_type = 'student' THEN 'طالب'
                WHEN p.participant_type = 'employee' THEN 'موظف'
                WHEN p.participant_type = 'other' THEN 'أخرى'
                ELSE p.participant_type
            END as participant_type,
            (
                SELECT MIN(r.registration_date)
                FROM registrations r
                WHERE r.participant_id = p.id
            ) as registration_date
        FROM participants p
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