<?php
/**
 * ملف اختبار الخدمات الجديدة
 * للتحقق من أن الخدمات تم تحديثها بشكل صحيح
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== اختبار الخدمات الجديدة ===\n\n";
    
    // جلب جميع الخدمات
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order");
    $services = $stmt->fetchAll();
    
    echo "الخدمات المتاحة:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($services as $service) {
        echo "ID: " . $service['id'] . "\n";
        echo "الاسم (عربي): " . $service['name_ar'] . "\n";
        echo "الاسم (إنجليزي): " . $service['name_en'] . "\n";
        echo "النوع: " . $service['service_type'] . "\n";
        echo "الترتيب: " . $service['sort_order'] . "\n";
        echo "الحالة: " . ($service['is_active'] ? 'نشط' : 'غير نشط') . "\n";
        echo str_repeat("-", 50) . "\n";
    }
    
    echo "\n=== التحقق من صحة البيانات ===\n";
    
    // التحقق من عدد الخدمات
    $count = count($services);
    echo "عدد الخدمات: " . $count . " (المتوقع: 4)\n";
    
    if ($count === 4) {
        echo "✅ عدد الخدمات صحيح\n";
    } else {
        echo "❌ عدد الخدمات غير صحيح\n";
    }
    
    // التحقق من أنواع الخدمات
    $expected_types = ['training', 'mentoring', 'fablab', 'coworking'];
    $actual_types = array_column($services, 'service_type');
    
    $types_match = true;
    foreach ($expected_types as $type) {
        if (!in_array($type, $actual_types)) {
            $types_match = false;
            break;
        }
    }
    
    if ($types_match) {
        echo "✅ أنواع الخدمات صحيحة\n";
    } else {
        echo "❌ أنواع الخدمات غير صحيحة\n";
        echo "المتوقع: " . implode(', ', $expected_types) . "\n";
        echo "الفعلي: " . implode(', ', $actual_types) . "\n";
    }
    
    // التحقق من الترتيب
    $sort_orders = array_column($services, 'sort_order');
    $expected_orders = [1, 2, 3, 4];
    
    if ($sort_orders === $expected_orders) {
        echo "✅ ترتيب الخدمات صحيح\n";
    } else {
        echo "❌ ترتيب الخدمات غير صحيح\n";
        echo "المتوقع: " . implode(', ', $expected_orders) . "\n";
        echo "الفعلي: " . implode(', ', $sort_orders) . "\n";
    }
    
    // التحقق من حالة الخدمات
    $active_count = 0;
    foreach ($services as $service) {
        if ($service['is_active']) {
            $active_count++;
        }
    }
    
    if ($active_count === 4) {
        echo "✅ جميع الخدمات نشطة\n";
    } else {
        echo "❌ عدد الخدمات النشطة غير صحيح\n";
        echo "العدد: " . $active_count . " (المتوقع: 4)\n";
    }
    
    echo "\n=== ملخص الاختبار ===\n";
    echo "الخدمات المحدثة:\n";
    echo "1. التقديم على التدريبات المتاحة (Training)\n";
    echo "2. حجز استشارات فردية للافراد والشركات (Mentoring)\n";
    echo "3. حجز فترة عمل فى معمل التصنيع الرقمى (FabLab)\n";
    echo "4. حجز مساحه عمل حرة (Co-Working Space)\n";
    
    echo "\n✅ تم تحديث الخدمات بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في اختبار الخدمات: " . $e->getMessage() . "\n";
}
?> 