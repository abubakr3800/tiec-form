<?php
/**
 * ملف تحديث قاعدة البيانات
 * يقوم بتحويل قاعدة البيانات الحالية إلى الهيكل الجديد
 */

require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "بدء تحديث قاعدة البيانات...\n";
    
    // 1. إنشاء الجداول الجديدة إذا لم تكن موجودة
    $tables = [
        'trainings' => "
            CREATE TABLE IF NOT EXISTS `trainings` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `service_id` INT NOT NULL,
                `trainer_id` INT NOT NULL,
                `title_ar` VARCHAR(255) NOT NULL,
                `title_en` VARCHAR(255) NOT NULL,
                `description_ar` TEXT,
                `description_en` TEXT,
                `start_date` DATE NOT NULL,
                `end_date` DATE NOT NULL,
                `start_time` TIME NOT NULL,
                `end_time` TIME NOT NULL,
                `max_participants` INT DEFAULT 20,
                `current_participants` INT DEFAULT 0,
                `is_active` TINYINT(1) DEFAULT 1,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_service_id` (`service_id`),
                INDEX `idx_trainer_id` (`trainer_id`),
                INDEX `idx_start_date` (`start_date`),
                INDEX `idx_is_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'registrations' => "
            CREATE TABLE IF NOT EXISTS `registrations` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `participant_id` INT NOT NULL,
                `service_id` INT NOT NULL,
                `training_id` INT NULL,
                `registration_date` DATE NOT NULL,
                `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
                `answers` JSON,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX `idx_participant_id` (`participant_id`),
                INDEX `idx_service_id` (`service_id`),
                INDEX `idx_training_id` (`training_id`),
                INDEX `idx_registration_date` (`registration_date`),
                INDEX `idx_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ",
        
        'attendance' => "
            CREATE TABLE IF NOT EXISTS `attendance` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `participant_id` INT NOT NULL,
                `training_id` INT NOT NULL,
                `attendance_date` DATE NOT NULL,
                `check_in_time` TIME,
                `check_out_time` TIME,
                `status` ENUM('present', 'absent', 'late') DEFAULT 'present',
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_participant_id` (`participant_id`),
                INDEX `idx_training_id` (`training_id`),
                INDEX `idx_attendance_date` (`attendance_date`),
                INDEX `idx_status` (`status`),
                UNIQUE KEY `unique_attendance` (`participant_id`, `training_id`, `attendance_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        "
    ];
    
    foreach ($tables as $table_name => $sql) {
        $pdo->exec($sql);
        echo "تم إنشاء جدول: $table_name\n";
    }
    
    // 2. تحديث جدول participants
    $participant_updates = [
        "ALTER TABLE `participants` ADD COLUMN IF NOT EXISTS `token` VARCHAR(64) UNIQUE AFTER `participant_type`",
        "ALTER TABLE `participants` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
        "ALTER TABLE `participants` ADD INDEX IF NOT EXISTS `idx_token` (`token`)",
        "ALTER TABLE `participants` ADD INDEX IF NOT EXISTS `idx_phone` (`phone`)"
    ];
    
    foreach ($participant_updates as $update) {
        try {
            $pdo->exec($update);
            echo "تم تحديث جدول participants\n";
        } catch (Exception $e) {
            echo "تحذير: " . $e->getMessage() . "\n";
        }
    }
    
    // 3. تحديث جدول services
    $service_updates = [
        "ALTER TABLE `services` ADD COLUMN IF NOT EXISTS `service_type` ENUM('training', 'mentoring', 'fablab', 'coworking') DEFAULT 'training' AFTER `description_en`",
        "ALTER TABLE `services` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
        "ALTER TABLE `services` ADD INDEX IF NOT EXISTS `idx_service_type` (`service_type`)"
    ];
    
    foreach ($service_updates as $update) {
        try {
            $pdo->exec($update);
            echo "تم تحديث جدول services\n";
        } catch (Exception $e) {
            echo "تحذير: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. تحديث جدول trainers
    $trainer_updates = [
        "ALTER TABLE `trainers` ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`"
    ];
    
    foreach ($trainer_updates as $update) {
        try {
            $pdo->exec($update);
            echo "تم تحديث جدول trainers\n";
        } catch (Exception $e) {
            echo "تحذير: " . $e->getMessage() . "\n";
        }
    }
    
    // 5. إضافة Foreign Keys
    $foreign_keys = [
        "ALTER TABLE `trainings` ADD CONSTRAINT IF NOT EXISTS `fk_trainings_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE",
        "ALTER TABLE `trainings` ADD CONSTRAINT IF NOT EXISTS `fk_trainings_trainer` FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE",
        "ALTER TABLE `registrations` ADD CONSTRAINT IF NOT EXISTS `fk_registrations_participant` FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON DELETE CASCADE",
        "ALTER TABLE `registrations` ADD CONSTRAINT IF NOT EXISTS `fk_registrations_service` FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE",
        "ALTER TABLE `registrations` ADD CONSTRAINT IF NOT EXISTS `fk_registrations_training` FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE SET NULL",
        "ALTER TABLE `attendance` ADD CONSTRAINT IF NOT EXISTS `fk_attendance_participant` FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON DELETE CASCADE",
        "ALTER TABLE `attendance` ADD CONSTRAINT IF NOT EXISTS `fk_attendance_training` FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE CASCADE"
    ];
    
    foreach ($foreign_keys as $fk) {
        try {
            $pdo->exec($fk);
            echo "تم إضافة Foreign Key\n";
        } catch (Exception $e) {
            echo "تحذير: " . $e->getMessage() . "\n";
        }
    }
    
    // 6. إنشاء tokens للمشاركين الموجودين
    $stmt = $pdo->query("SELECT id FROM participants WHERE token IS NULL OR token = ''");
    $participants_without_token = $stmt->fetchAll();
    
    foreach ($participants_without_token as $participant) {
        $token = generateUniqueToken($pdo);
        $update_stmt = $pdo->prepare("UPDATE participants SET token = ? WHERE id = ?");
        $update_stmt->execute([$token, $participant['id']]);
        echo "تم إنشاء token للمشارك ID: " . $participant['id'] . "\n";
    }
    
    // 7. إدخال بيانات تجريبية إذا كانت الجداول فارغة
    insertSampleData($pdo);
    
    echo "\nتم تحديث قاعدة البيانات بنجاح!\n";
    
} catch (Exception $e) {
    echo "خطأ في تحديث قاعدة البيانات: " . $e->getMessage() . "\n";
}

/**
 * إنشاء token فريد
 */
function generateUniqueToken($pdo) {
    do {
        $token = sprintf(
            '%04x-%04x-%04x-%04x-%04x%08x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffffffff)
        );
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM participants WHERE token = ?");
        $stmt->execute([$token]);
        $exists = $stmt->fetchColumn();
    } while ($exists > 0);
    
    return $token;
}

/**
 * إدخال بيانات تجريبية
 */
function insertSampleData($pdo) {
    // التحقق من وجود خدمات
    $stmt = $pdo->query("SELECT COUNT(*) FROM services");
    $services_count = $stmt->fetchColumn();
    
    if ($services_count == 0) {
        echo "إدخال بيانات تجريبية...\n";
        
        // إدخال خدمات
        $services = [
            ['التدريب المهني', 'Professional Training', 'تدريبات مهنية متخصصة', 'Specialized professional training', 'training'],
            ['الاستشارات الفردية', 'Individual Mentoring', 'استشارات فردية للطلاب والخريجين', 'Individual mentoring for students and graduates', 'mentoring'],
            ['معمل التصنيع الرقمي', 'Digital Manufacturing Lab', 'حجز فترات عمل في معمل التصنيع الرقمي', 'Book work sessions in the digital manufacturing lab', 'fablab'],
            ['مساحة العمل الحرة', 'Co-Working Space', 'حجز مساحات عمل حرة', 'Book co-working spaces', 'coworking']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO services (name_ar, name_en, description_ar, description_en, service_type, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($services as $index => $service) {
            $stmt->execute([$service[0], $service[1], $service[2], $service[3], $service[4], $index + 1]);
        }
        
        echo "تم إدخال الخدمات التجريبية\n";
    }
    
    // التحقق من وجود مدربين
    $stmt = $pdo->query("SELECT COUNT(*) FROM trainers");
    $trainers_count = $stmt->fetchColumn();
    
    if ($trainers_count == 0) {
        $trainers = [
            ['أحمد محمد', 'ahmed@tiec.com', '01012345678', 'تطوير البرمجيات'],
            ['فاطمة علي', 'fatima@tiec.com', '01087654321', 'التصميم الرقمي'],
            ['محمد حسن', 'mohamed@tiec.com', '01011223344', 'الروبوتات والذكاء الاصطناعي']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO trainers (name, email, phone, specialization, is_active) VALUES (?, ?, ?, ?, 1)");
        
        foreach ($trainers as $trainer) {
            $stmt->execute($trainer);
        }
        
        echo "تم إدخال المدربين التجريبيين\n";
    }
}
?> 