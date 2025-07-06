<?php
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "إنشاء جداول الأسئلة...\n\n";
    
    // إنشاء جدول الأسئلة
    $sql = "CREATE TABLE IF NOT EXISTS service_questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        service_id INT NOT NULL,
        question_text_ar TEXT NOT NULL,
        question_text_en TEXT NOT NULL,
        question_type ENUM('text', 'textarea', 'select', 'radio', 'checkbox', 'date', 'time', 'number', 'file', 'url') NOT NULL,
        is_required BOOLEAN DEFAULT 1,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ تم إنشاء جدول service_questions\n";
    
    // إنشاء جدول خيارات الأسئلة
    $sql = "CREATE TABLE IF NOT EXISTS question_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        option_text_ar VARCHAR(255) NOT NULL,
        option_text_en VARCHAR(255) NOT NULL,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (question_id) REFERENCES service_questions(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ تم إنشاء جدول question_options\n";
    
    echo "\n✅ تم إنشاء جميع الجداول بنجاح!\n";
    
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء الجداول: " . $e->getMessage() . "\n";
}
?> 