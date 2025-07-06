<?php
session_start();
require_once '../config/database.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'غير مصرح']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    $where_conditions = [];
    $params = [];
    
    // تطبيق الفلاتر
    if (!empty($_POST['specialization'])) {
        $where_conditions[] = "specialization = ?";
        $params[] = $_POST['specialization'];
    }
    
    if (!empty($_POST['status'])) {
        $where_conditions[] = "is_active = ?";
        $params[] = $_POST['status'];
    }
    
    if (!empty($_POST['search'])) {
        $where_conditions[] = "(name_ar LIKE ? OR name_en LIKE ? OR email LIKE ?)";
        $search_term = '%' . $_POST['search'] . '%';
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    $where_clause = '';
    if (!empty($where_conditions)) {
        $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    $sql = "SELECT id, name_ar, name_en, email, specialization, phone, bio_ar, bio_en, is_active, created_at, updated_at, last_login FROM trainers $where_clause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $trainers = $stmt->fetchAll();
    
    $data = [];
    foreach ($trainers as $trainer) {
        $data[] = [
            'id' => $trainer['id'],
            'name' => htmlspecialchars($trainer['name_ar']),
            'name_en' => htmlspecialchars($trainer['name_en']),
            'email' => htmlspecialchars($trainer['email']),
            'specialization' => htmlspecialchars($trainer['specialization']),
            'phone' => htmlspecialchars($trainer['phone']),
            'bio_ar' => htmlspecialchars($trainer['bio_ar']),
            'bio_en' => htmlspecialchars($trainer['bio_en']),
            'is_active' => $trainer['is_active'],
            'created_at' => $trainer['created_at'],
            'updated_at' => $trainer['updated_at'],
            'last_login' => $trainer['last_login']
        ];
    }
    
    echo json_encode(['data' => $data]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'خطأ في جلب البيانات: ' . $e->getMessage()]);
}
?> 