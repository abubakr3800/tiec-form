<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // التحقق من البيانات المطلوبة
    $required_fields = ['user_type', 'username', 'password', 'confirm_password', 'name', 'email'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
        exit();
    }
    
    // التحقق من تطابق كلمات المرور
    if ($_POST['password'] !== $_POST['confirm_password']) {
        echo json_encode(['success' => false, 'message' => 'كلمات المرور غير متطابقة']);
        exit();
    }
    
    // التحقق من طول كلمة المرور
    if (strlen($_POST['password']) < 6) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل']);
        exit();
    }
    
    // التحقق من صحة البريد الإلكتروني
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني غير صحيح']);
        exit();
    }
    
    $user_type = $_POST['user_type'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // التحقق من عدم وجود اسم مستخدم مكرر
    if ($user_type === 'admin') {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? OR email = ?");
    } else {
        $stmt = $pdo->prepare("SELECT id FROM trainers WHERE username = ? OR email = ?");
    }
    $stmt->execute([$username, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'اسم المستخدم أو البريد الإلكتروني مستخدم مسبقاً']);
        exit();
    }
    
    // تشفير كلمة المرور
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // حفظ البيانات حسب نوع المستخدم
    if ($user_type === 'admin') {
        // إعداد البيانات للمشرف
        $data = [
            'username' => $username,
            'password' => $hashed_password,
            'name_ar' => trim($_POST['name']),
            'name_en' => trim($_POST['name']), // نفس الاسم بالعربية والإنجليزية
            'email' => $email
        ];
        
        $sql = "INSERT INTO admins (username, password, name_ar, name_en, email) VALUES (:username, :password, :name_ar, :name_en, :email)";
    } else {
        // إعداد البيانات للمدرب
        $data = [
            'username' => $username,
            'password' => $hashed_password,
            'name_ar' => trim($_POST['name']),
            'name_en' => trim($_POST['name']), // نفس الاسم بالعربية والإنجليزية
            'email' => $email,
            'phone' => !empty($_POST['phone']) ? trim($_POST['phone']) : null,
            'specialization' => !empty($_POST['specialization']) ? trim($_POST['specialization']) : null
        ];
        
        $sql = "INSERT INTO trainers (username, password, name_ar, name_en, email, specialization, phone) VALUES (:username, :password, :name_ar, :name_en, :email, :specialization, :phone)";
    }
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute($data);
    
    if ($result) {
        // حفظ الكوكي لمنع التسجيل المكرر
        setcookie('admin_registered', $username, time() + (86400 * 30), '/'); // 30 يوم
        
        echo json_encode([
            'success' => true, 
            'message' => 'تم التسجيل بنجاح! سيتم توجيهك لصفحة تسجيل الدخول.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في حفظ البيانات']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 