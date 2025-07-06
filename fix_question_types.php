<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إصلاح أنواع الأسئلة...\n\n";
    
    // تحديث أنواع الأسئلة الفارغة
    $updates = [
        // Co-Working Space
        ['id' => 145, 'type' => 'date'],      // تاريخ الحجز
        ['id' => 146, 'type' => 'time'],      // موعد الحجز
        ['id' => 147, 'type' => 'number'],    // عدد ساعات الحجز
        ['id' => 148, 'type' => 'checkbox'],  // الموافقة على الشروط
        
        // FabLab
        ['id' => 150, 'type' => 'file'],      // رفع ملف الخدمة
        ['id' => 153, 'type' => 'text'],      // نوع الخامات المطلوبة
        ['id' => 156, 'type' => 'date'],      // تاريخ يوم الحجز
        ['id' => 157, 'type' => 'time'],      // موعد الحجز
        ['id' => 158, 'type' => 'number'],    // عدد ساعات الحجز
        ['id' => 159, 'type' => 'checkbox'],  // الموافقة على الشروط
        ['id' => 160, 'type' => 'textarea'],  // ملاحظات
        
        // Mentoring
        ['id' => 162, 'type' => 'number'],    // عدد أفراد الفريق
        ['id' => 165, 'type' => 'textarea'],  // نبذة عن الفكرة
        ['id' => 166, 'type' => 'textarea'],  // ملاحظات
        
        // Training
        ['id' => 169, 'type' => 'number'],    // عدد أفراد الفريق
        ['id' => 172, 'type' => 'text'],      // نوع المشكلة
        ['id' => 175, 'type' => 'file'],      // رفع نموذج العمل التجاري
        ['id' => 178, 'type' => 'url'],       // رابط النموذج الأولي
        ['id' => 181, 'type' => 'file'],      // رفع العرض التقديمي
        ['id' => 182, 'type' => 'url'],       // رابط الموقع
    ];
    
    $stmt = $pdo->prepare("UPDATE service_questions SET question_type = ? WHERE id = ?");
    
    foreach ($updates as $update) {
        $stmt->execute([$update['type'], $update['id']]);
        echo "تم تحديث السؤال ID " . $update['id'] . " إلى نوع: " . $update['type'] . "\n";
    }
    
    echo "\n✅ تم إصلاح جميع أنواع الأسئلة!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?> 