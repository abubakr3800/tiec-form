<?php
require_once '../config/database.php';

try {
    $pdo = getDBConnection();
    echo "إصلاح أعمدة bio_ar و bio_en في جدول trainers...\n";
    
    $pdo->exec("ALTER TABLE trainers ADD COLUMN bio_ar TEXT AFTER specialization");
    echo "✅ تم إضافة العمود bio_ar\n";
} catch (Exception $e) {
    echo "⚠️  العمود bio_ar موجود بالفعل أو خطأ آخر: " . $e->getMessage() . "\n";
}

try {
    $pdo->exec("ALTER TABLE trainers ADD COLUMN bio_en TEXT AFTER bio_ar");
    echo "✅ تم إضافة العمود bio_en\n";
} catch (Exception $e) {
    echo "⚠️  العمود bio_en موجود بالفعل أو خطأ آخر: " . $e->getMessage() . "\n";
}

echo "تم!\n";
?> 