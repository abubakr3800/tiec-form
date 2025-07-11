<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'طريقة طلب غير صحيحة']);
    exit();
}

try {
    $pdo = getDBConnection();
    
    // التحقق من البيانات المطلوبة
    if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['user_type'])) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة']);
        exit();
    }
    
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // التحقق من نوع المستخدم
    if (!in_array($user_type, ['admin', 'trainer'])) {
        echo json_encode(['success' => false, 'message' => 'نوع مستخدم غير صحيح']);
        exit();
    }
    
    // البحث عن المستخدم في الجدول المناسب
    if ($user_type === 'admin') {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM trainers WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
    }
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'اسم المستخدم غير موجود أو الحساب غير مفعل']);
        exit();
    }
    
    // التحقق من كلمة المرور
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'كلمة المرور غير صحيحة']);
        exit();
    }
    
    // إنشاء جلسة للمستخدم
    $_SESSION['user_id'] = $user['id'];
    if ($user_type === 'admin') {
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['name_ar']; // استخدام الاسم العربي
    } else {
        $_SESSION['username'] = $user['email'];
        $_SESSION['user_name'] = $user['name_ar']; // أو يمكن استخدام name_en إذا أردت
    }
    $_SESSION['user_type'] = $user_type;
    $_SESSION['user_email'] = $user['email'];
    
    // إضافة معلومات إضافية حسب نوع المستخدم
    if ($user_type === 'admin') {
        $_SESSION['admin_role'] = $user['role'];
    } else {
        $_SESSION['trainer_specialization'] = $user['specialization'] ?? '';
        $_SESSION['trainer_phone'] = $user['phone'] ?? '';
    }
    
    // إنشاء JWT token (اختياري)
    $token_data = [
        'user_id' => $user['id'],
        'username' => $user_type === 'admin' ? $user['username'] : $user['email'],
        'user_type' => $user_type,
        'exp' => time() + (86400 * 7) // 7 أيام
    ];
    
    // يمكن إضافة JWT library هنا لإنشاء token
    // $_SESSION['jwt_token'] = createJWT($token_data);
    
    echo json_encode([
        'success' => true, 
        'message' => 'تم تسجيل الدخول بنجاح',
        'user_type' => $user_type
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'خطأ غير متوقع: ' . $e->getMessage()]);
}
?> 