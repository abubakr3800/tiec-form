<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "=== اختبار الأسئلة للخدمات ===\n\n";
    
    // جلب جميع الخدمات
    $stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order");
    $services = $stmt->fetchAll();
    
    foreach ($services as $service) {
        echo "الخدمة: " . $service['name_ar'] . " (ID: " . $service['id'] . ")\n";
        echo "النوع: " . $service['service_type'] . "\n";
        
        // جلب الأسئلة
        $stmt = $pdo->prepare("SELECT * FROM service_questions WHERE service_id = ? ORDER BY sort_order");
        $stmt->execute([$service['id']]);
        $questions = $stmt->fetchAll();
        
        echo "عدد الأسئلة: " . count($questions) . "\n";
        
        foreach ($questions as $q) {
            echo "  - " . $q['question_text_ar'] . " (النوع: " . $q['question_type'] . ")\n";
            
            // جلب الخيارات إذا كان السؤال من نوع select أو radio
            if (in_array($q['question_type'], ['select', 'radio'])) {
                $opt_stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY sort_order");
                $opt_stmt->execute([$q['id']]);
                $options = $opt_stmt->fetchAll();
                
                foreach ($options as $opt) {
                    echo "    * " . $opt['option_text_ar'] . "\n";
                }
            }
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "خطأ: " . $e->getMessage() . "\n";
}
?> 