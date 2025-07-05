<?php
header('Content-Type: application/json');
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

$training_id = $_GET['id'] ?? null;

if (!$training_id) {
    echo json_encode(['success' => false, 'message' => 'معرف التدريب مطلوب']);
    exit();
}

try {
    // Check if training exists
    $stmt = $pdo->prepare("SELECT * FROM trainings WHERE id = ?");
    $stmt->execute([$training_id]);
    $training = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$training) {
        echo json_encode(['success' => false, 'message' => 'التدريب غير موجود']);
        exit();
    }
    
    // Check if there are participants registered
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM training_participants WHERE training_id = ?");
    $stmt->execute([$training_id]);
    $participants_count = $stmt->fetchColumn();
    
    if ($participants_count > 0) {
        echo json_encode(['success' => false, 'message' => 'لا يمكن حذف التدريب لوجود مشاركين مسجلين فيه']);
        exit();
    }
    
    // Delete training
    $stmt = $pdo->prepare("DELETE FROM trainings WHERE id = ?");
    $stmt->execute([$training_id]);
    
    echo json_encode(['success' => true, 'message' => 'تم حذف التدريب بنجاح']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?> 