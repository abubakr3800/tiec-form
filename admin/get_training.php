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
    $stmt = $pdo->prepare("
        SELECT t.*, tr.name as trainer_name
        FROM trainings t
        LEFT JOIN trainers tr ON t.trainer_id = tr.id
        WHERE t.id = ?
    ");
    $stmt->execute([$training_id]);
    $training = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$training) {
        echo json_encode(['success' => false, 'message' => 'التدريب غير موجود']);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'training' => $training
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
}
?> 