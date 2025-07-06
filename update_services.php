<?php
/**
 * ملف تحديث أنواع الخدمات
 * تحديث الخدمات لتكون كما هو مطلوب
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "بدء تحديث أنواع الخدمات...\n";
    
    // حذف الخدمات الموجودة
    $pdo->exec("DELETE FROM services");
    echo "تم حذف الخدمات القديمة\n";
    
    // إدخال الخدمات الجديدة
    $services = [
        [
            'name_ar' => 'التقديم على التدريبات المتاحة',
            'name_en' => 'Training Registration',
            'description_ar' => 'التسجيل في التدريبات المهنية المتاحة مع مدربين متخصصين',
            'description_en' => 'Register for professional training courses with specialized trainers',
            'service_type' => 'training',
            'sort_order' => 1
        ],
        [
            'name_ar' => 'حجز استشارات فردية للافراد والشركات',
            'name_en' => 'Individual & Corporate Mentoring',
            'description_ar' => 'حجز جلسات استشارية فردية للطلاب والخريجين والشركات',
            'description_en' => 'Book individual consultation sessions for students, graduates and companies',
            'service_type' => 'mentoring',
            'sort_order' => 2
        ],
        [
            'name_ar' => 'حجز فترة عمل فى معمل التصنيع الرقمى',
            'name_en' => 'Digital Manufacturing Lab Booking',
            'description_ar' => 'حجز فترات عمل في معمل التصنيع الرقمي لتنفيذ المشاريع',
            'description_en' => 'Book work sessions in the digital manufacturing lab for project execution',
            'service_type' => 'fablab',
            'sort_order' => 3
        ],
        [
            'name_ar' => 'حجز مساحه عمل حرة',
            'name_en' => 'Co-Working Space Booking',
            'description_ar' => 'حجز مساحات عمل حرة للعمل المشترك والاجتماعات',
            'description_en' => 'Book co-working spaces for collaborative work and meetings',
            'service_type' => 'coworking',
            'sort_order' => 4
        ]
    ];
    
    $stmt = $pdo->prepare("
        INSERT INTO services (name_ar, name_en, description_ar, description_en, service_type, sort_order, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");
    
    foreach ($services as $service) {
        $stmt->execute([
            $service['name_ar'],
            $service['name_en'],
            $service['description_ar'],
            $service['description_en'],
            $service['service_type'],
            $service['sort_order']
        ]);
        echo "تم إضافة خدمة: " . $service['name_ar'] . "\n";
    }
    
    echo "\nتم تحديث الخدمات بنجاح!\n";
    echo "الخدمات الجديدة:\n";
    echo "1. التقديم على التدريبات المتاحة (Training)\n";
    echo "2. حجز استشارات فردية للافراد والشركات (Mentoring)\n";
    echo "3. حجز فترة عمل فى معمل التصنيع الرقمى (FabLab)\n";
    echo "4. حجز مساحه عمل حرة (Co-Working Space)\n";
    
} catch (Exception $e) {
    echo "خطأ في تحديث الخدمات: " . $e->getMessage() . "\n";
}
?> 