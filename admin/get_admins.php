<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

// تم إلغاء شرط التحقق من admin_role للسماح لأي مشرف برؤية القائمة

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, username, name_ar, name_en, email, role, is_active, created_at, updated_at FROM admins ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
    
    $data = [];
    foreach ($admins as $admin) {
        $data[] = [
            'id' => $admin['id'],
            'username' => htmlspecialchars($admin['username']),
            'name' => htmlspecialchars($admin['name_ar']),
            'name_en' => htmlspecialchars($admin['name_en']),
            'email' => htmlspecialchars($admin['email']),
            'role' => $admin['role'],
            'is_active' => $admin['is_active'],
            'created_at' => $admin['created_at'],
            'updated_at' => $admin['updated_at'],
            'last_login' => null // سيتم إضافته لاحقاً عند تطوير نظام تسجيل الدخول
        ];
    }
    
    echo json_encode(['data' => $data]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطأ في جلب البيانات: ' . $e->getMessage()]);
}
?> 