<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إضافة الخدمات الأربع...\n\n";
    
    // حذف الخدمات الموجودة
    $pdo->exec("DELETE FROM services");
    echo "تم حذف الخدمات القديمة\n\n";
    
    // إضافة الخدمات الجديدة
    $services = [
        [
            'name_ar' => 'حجز مساحه عمل حرة',
            'name_en' => 'Co-Working Space Booking',
            'description_ar' => 'احجز مساحة عمل حرة لاستخدام المكتب والإنترنت والمرافق',
            'description_en' => 'Book a co-working space with desk, internet, and facilities',
            'service_type' => 'coworking',
            'sort_order' => 1
        ],
        [
            'name_ar' => 'مختبر التصنيع الرقمي',
            'name_en' => 'FabLab',
            'description_ar' => 'استخدم معدات التصنيع الرقمي مثل الطباعة ثلاثية الأبعاد والليزر',
            'description_en' => 'Use digital fabrication equipment like 3D printing and laser cutting',
            'service_type' => 'fablab',
            'sort_order' => 2
        ],
        [
            'name_ar' => 'خدمات الإرشاد',
            'name_en' => 'Mentoring Services',
            'description_ar' => 'احصل على إرشاد من خبراء في مجال عملك',
            'description_en' => 'Get mentoring from experts in your field',
            'service_type' => 'mentoring',
            'sort_order' => 3
        ],
        [
            'name_ar' => 'التدريب المتخصص',
            'name_en' => 'Specialized Training',
            'description_ar' => 'تدريبات متخصصة في مجالات مختلفة مثل MedTech',
            'description_en' => 'Specialized training in different fields like MedTech',
            'service_type' => 'training',
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
        
        echo "✅ تم إضافة خدمة: " . $service['name_ar'] . "\n";
    }
    
    echo "\n✅ تم إضافة جميع الخدمات بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إضافة الخدمات: " . $e->getMessage() . "\n";
}
?> 