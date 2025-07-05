<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'غير مصرح']));
}

// التحقق من أن المستخدم مشرف رئيسي
if ($_SESSION['admin_role'] !== 'super_admin') {
    http_response_code(403);
    exit(json_encode(['success' => false, 'message' => 'غير مصرح']));
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'معرف غير صحيح']);
    exit();
}

$pdo = getDBConnection();

try {
    $admin_id = $_POST['id'];
    
    // منع حذف المشرف الحالي
    if ($admin_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'لا يمكن حذف حسابك الحالي']);
        exit();
    }
    
    // التحقق من وجود المشرف
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'message' => 'المشرف غير موجود']);
        exit();
    }
    
    // حذف المشرف
    $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
    $stmt->execute([$admin_id]);
    
    echo json_encode(['success' => true, 'message' => 'تم حذف المشرف بنجاح']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في حذف المشرف']);
}
?> 