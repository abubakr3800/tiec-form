<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المشارك مطلوب']);
    exit();
}

try {
    $pdo = getDBConnection();
    $participant_id = (int)$_POST['id'];
    
    // التحقق من وجود المشارك
    $stmt = $pdo->prepare("SELECT id FROM participants WHERE id = ?");
    $stmt->execute([$participant_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'المشارك غير موجود']);
        exit();
    }
    
    // حذف المشارك
    $stmt = $pdo->prepare("DELETE FROM participants WHERE id = ?");
    $result = $stmt->execute([$participant_id]);
    
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'تم حذف المشارك بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في حذف المشارك']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 