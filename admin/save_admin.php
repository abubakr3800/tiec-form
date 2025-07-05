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

$pdo = getDBConnection();

try {
    $id = $_POST['id'] ?? null;
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // التحقق من البيانات المطلوبة
    if (empty($username) || empty($name) || empty($role)) {
        echo json_encode(['success' => false, 'message' => 'جميع الحقول المطلوبة يجب ملؤها']);
        exit();
    }
    
    // التحقق من صحة الدور
    if (!in_array($role, ['admin', 'super_admin'])) {
        echo json_encode(['success' => false, 'message' => 'دور غير صحيح']);
        exit();
    }
    
    // التحقق من صحة البريد الإلكتروني إذا تم إدخاله
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'بريد إلكتروني غير صحيح']);
        exit();
    }
    
    if ($id) {
        // تحديث مشرف موجود
        $stmt = $pdo->prepare("SELECT username FROM admins WHERE id = ?");
        $stmt->execute([$id]);
        $existing_admin = $stmt->fetch();
        
        if (!$existing_admin) {
            echo json_encode(['success' => false, 'message' => 'المشرف غير موجود']);
            exit();
        }
        
        // التحقق من عدم تكرار اسم المستخدم
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'اسم المستخدم مستخدم بالفعل']);
            exit();
        }
        
        // التحقق من عدم تكرار البريد الإلكتروني
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
                exit();
            }
        }
        
        // تحديث البيانات
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE admins SET username = ?, name = ?, email = ?, password = ?, role = ?, is_active = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $name, $email, $hashed_password, $role, $is_active, $id]);
        } else {
            $sql = "UPDATE admins SET username = ?, name = ?, email = ?, role = ?, is_active = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $name, $email, $role, $is_active, $id]);
        }
        
    } else {
        // إضافة مشرف جديد
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'كلمة المرور مطلوبة للمشرف الجديد']);
            exit();
        }
        
        // التحقق من عدم تكرار اسم المستخدم
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'اسم المستخدم مستخدم بالفعل']);
            exit();
        }
        
        // التحقق من عدم تكرار البريد الإلكتروني
        if (!empty($email)) {
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني مستخدم بالفعل']);
                exit();
            }
        }
        
        // إضافة المشرف الجديد
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO admins (username, name, email, password, role, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $name, $email, $hashed_password, $role, $is_active]);
    }
    
    echo json_encode(['success' => true, 'message' => 'تم حفظ البيانات بنجاح']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
}
?> 