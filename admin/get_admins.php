<?php
session_start();
require_once '../cache/db.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

// التحقق من أن المستخدم مشرف رئيسي
if ($_SESSION['admin_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

try {
    // $pdo is already available from cache/db.php
    
    $stmt = $pdo->query("SELECT id, username, name, email, role, is_active, created_at, updated_at, last_login FROM admins ORDER BY created_at DESC");
    $admins = $stmt->fetchAll();
    
    $data = [];
    foreach ($admins as $admin) {
        $data[] = [
            'id' => $admin['id'],
            'username' => htmlspecialchars($admin['username']),
            'name' => htmlspecialchars($admin['name']),
            'email' => htmlspecialchars($admin['email']),
            'role' => $admin['role'],
            'is_active' => $admin['is_active'],
            'created_at' => $admin['created_at'],
            'updated_at' => $admin['updated_at'],
            'last_login' => $admin['last_login']
        ];
    }
    
    echo json_encode(['data' => $data]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطأ في جلب البيانات: ' . $e->getMessage()]);
}
?> 