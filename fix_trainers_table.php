<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إصلاح جدول المدربين...\n\n";
    
    // إضافة الأعمدة المفقودة
    $alterations = [
        "ALTER TABLE trainers ADD COLUMN name_ar VARCHAR(255) AFTER name",
        "ALTER TABLE trainers ADD COLUMN name_en VARCHAR(255) AFTER name_ar",
        "ALTER TABLE trainers ADD COLUMN bio_ar TEXT AFTER bio",
        "ALTER TABLE trainers ADD COLUMN bio_en TEXT AFTER bio_ar"
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
    $update_sql = "UPDATE trainers SET name_ar = name, name_en = name, bio_ar = bio, bio_en = bio WHERE name_ar IS NULL";
    $pdo->exec($update_sql);
    echo "✅ تم تحديث البيانات الموجودة\n";
    
    echo "\n✅ تم إصلاح جدول المدربين بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ: " . $e->getMessage() . "\n";
}
?> 