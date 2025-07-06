<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إصلاح جدول المشرفين...\n\n";
    
    // إضافة الأعمدة المفقودة
    $alterations = [
        "ALTER TABLE admins ADD COLUMN name_ar VARCHAR(255) AFTER name",
        "ALTER TABLE admins ADD COLUMN name_en VARCHAR(255) AFTER name_ar"
    ];
    
    foreach ($alterations as $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ تم تنفيذ: " . $sql . "\n";
        } catch (Exception $e) {
            echo "⚠️  تم تخطي: " . $sql . " (قد يكون موجوداً بالفعل)\n";
        }
    }
    
    // تحديث البيانات الموجودة
    $update_sql = "UPDATE admins SET name_ar = name, name_en = name WHERE name_ar IS NULL";
    $pdo->exec($update_sql);
    echo "✅ تم تحديث البيانات الموجودة\n";
    
    echo "\n✅ تم إصلاح جدول المشرفين بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?> 