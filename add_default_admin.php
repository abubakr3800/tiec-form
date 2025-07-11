<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إضافة مشرف افتراضي للنظام...\n";
    
    // التحقق من وجود مشرفين
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM admins");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "✅ يوجد مشرفين بالفعل في النظام\n";
        exit();
    }
    
    // إضافة مشرف افتراضي
    $username = 'admin';
    $password = 'admin123';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO admins (username, password, name_ar, name_en, email, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $username,
        $hashed_password,
        'مدير النظام',
        'System Administrator',
        'admin@tiec.com',
        'super_admin'
    ]);
    
    if ($result) {
        echo "✅ تم إضافة المشرف الافتراضي بنجاح!\n";
        echo "اسم المستخدم: admin\n";
        echo "كلمة المرور: admin123\n";
        echo "البريد الإلكتروني: admin@tiec.com\n";
        echo "\n⚠️  يرجى تغيير كلمة المرور بعد تسجيل الدخول!\n";
    } else {
        echo "❌ فشل في إضافة المشرف الافتراضي\n";
    }
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?> 