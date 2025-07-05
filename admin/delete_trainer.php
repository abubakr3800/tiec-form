<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'غير مصرح']));
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف غير صحيح']);
    exit();
}

$pdo = getDBConnection();

try {
    $trainer_id = $_POST['id'];
    
    // التحقق من وجود المدرب
    $stmt = $pdo->prepare("SELECT id FROM trainers WHERE id = ?");
    $stmt->execute([$trainer_id]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'المدرب غير موجود']);
        exit();
    }
    
    // حذف المدرب
    $stmt = $pdo->prepare("DELETE FROM trainers WHERE id = ?");
    $stmt->execute([$trainer_id]);
    
    echo json_encode(['success' => true, 'message' => 'تم حذف المدرب بنجاح']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في حذف المدرب']);
}
?> 