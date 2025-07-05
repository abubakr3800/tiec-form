<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'غير مصرح']);
    exit();
}

header('Content-Type: application/json');

if (empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف المدرب مطلوب']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    $trainer_id = (int)$_GET['id'];
    
    $stmt = $pdo->prepare("SELECT id, name, email, phone, specialization, experience_years, is_active FROM trainers WHERE id = ?");
    $stmt->execute([$trainer_id]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($trainer) {
        echo json_encode(['success' => true, 'trainer' => $trainer]);
    } else {
        echo json_encode(['success' => false, 'message' => 'المدرب غير موجود']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 