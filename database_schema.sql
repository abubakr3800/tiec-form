-- =============================================
-- نظام TIEC - قاعدة البيانات الجديدة
-- =============================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS `tiec_form` 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `tiec_form`;

-- =============================================
-- جدول المشاركين
-- =============================================
CREATE TABLE `participants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `national_id` VARCHAR(20) UNIQUE NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(255),
  `governorate` VARCHAR(100) NOT NULL,
  `gender` ENUM('male', 'female') NOT NULL,
  `age` INT NOT NULL,
  `participant_type` ENUM('student', 'employee', 'other') NOT NULL,
  `token` VARCHAR(64) UNIQUE NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_national_id` (`national_id`),
  INDEX `idx_token` (`token`),
  INDEX `idx_phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الخدمات
-- =============================================
CREATE TABLE `services` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name_ar` VARCHAR(255) NOT NULL,
  `name_en` VARCHAR(255) NOT NULL,
  `description_ar` TEXT,
  `description_en` TEXT,
  `service_type` ENUM('training', 'mentoring', 'fablab', 'coworking') NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_service_type` (`service_type`),
  INDEX `idx_is_active` (`is_active`),
  INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول المدربين
-- =============================================
CREATE TABLE `trainers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `phone` VARCHAR(20),
  `specialization` VARCHAR(255),
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول التدريبات
-- =============================================
CREATE TABLE `trainings` (
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
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trainer_id`) REFERENCES `trainers`(`id`) ON DELETE CASCADE,
  INDEX `idx_service_id` (`service_id`),
  INDEX `idx_trainer_id` (`trainer_id`),
  INDEX `idx_start_date` (`start_date`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول أسئلة الخدمات
-- =============================================
CREATE TABLE `service_questions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `service_id` INT NOT NULL,
  `question_text_ar` TEXT NOT NULL,
  `question_text_en` TEXT NOT NULL,
  `question_type` ENUM('text', 'select', 'radio', 'checkbox', 'textarea') NOT NULL,
  `is_required` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  INDEX `idx_service_id` (`service_id`),
  INDEX `idx_sort_order` (`sort_order`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول خيارات الأسئلة
-- =============================================
CREATE TABLE `question_options` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `question_id` INT NOT NULL,
  `option_text_ar` VARCHAR(255) NOT NULL,
  `option_text_en` VARCHAR(255) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`question_id`) REFERENCES `service_questions`(`id`) ON DELETE CASCADE,
  INDEX `idx_question_id` (`question_id`),
  INDEX `idx_sort_order` (`sort_order`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول التسجيلات
-- =============================================
CREATE TABLE `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `participant_id` INT NOT NULL,
  `service_id` INT NOT NULL,
  `training_id` INT NULL,
  `registration_date` DATE NOT NULL,
  `status` ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
  `answers` JSON,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE SET NULL,
  INDEX `idx_participant_id` (`participant_id`),
  INDEX `idx_service_id` (`service_id`),
  INDEX `idx_training_id` (`training_id`),
  INDEX `idx_registration_date` (`registration_date`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول الحضور
-- =============================================
CREATE TABLE `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `participant_id` INT NOT NULL,
  `training_id` INT NOT NULL,
  `attendance_date` DATE NOT NULL,
  `check_in_time` TIME,
  `check_out_time` TIME,
  `status` ENUM('present', 'absent', 'late') DEFAULT 'present',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`training_id`) REFERENCES `trainings`(`id`) ON DELETE CASCADE,
  INDEX `idx_participant_id` (`participant_id`),
  INDEX `idx_training_id` (`training_id`),
  INDEX `idx_attendance_date` (`attendance_date`),
  INDEX `idx_status` (`status`),
  UNIQUE KEY `unique_attendance` (`participant_id`, `training_id`, `attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- جدول المشرفين
-- =============================================
CREATE TABLE `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'super_admin') DEFAULT 'admin',
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login` TIMESTAMP NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_username` (`username`),
  INDEX `idx_email` (`email`),
  INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- إدخال بيانات تجريبية
-- =============================================

-- إدخال خدمات تجريبية
INSERT INTO `services` (`name_ar`, `name_en`, `description_ar`, `description_en`, `service_type`, `sort_order`) VALUES
('التقديم على التدريبات المتاحة', 'Training Registration', 'التسجيل في التدريبات المهنية المتاحة مع مدربين متخصصين', 'Register for professional training courses with specialized trainers', 'training', 1),
('حجز استشارات فردية للافراد والشركات', 'Individual & Corporate Mentoring', 'حجز جلسات استشارية فردية للطلاب والخريجين والشركات', 'Book individual consultation sessions for students, graduates and companies', 'mentoring', 2),
('حجز فترة عمل فى معمل التصنيع الرقمى', 'Digital Manufacturing Lab Booking', 'حجز فترات عمل في معمل التصنيع الرقمي لتنفيذ المشاريع', 'Book work sessions in the digital manufacturing lab for project execution', 'fablab', 3),
('حجز مساحه عمل حرة', 'Co-Working Space Booking', 'حجز مساحات عمل حرة للعمل المشترك والاجتماعات', 'Book co-working spaces for collaborative work and meetings', 'coworking', 4);

-- إدخال مدربين تجريبيين
INSERT INTO `trainers` (`name`, `email`, `phone`, `specialization`, `is_active`) VALUES
('أحمد محمد', 'ahmed@tiec.com', '01012345678', 'تطوير البرمجيات', 1),
('فاطمة علي', 'fatima@tiec.com', '01087654321', 'التصميم الرقمي', 1),
('محمد حسن', 'mohamed@tiec.com', '01011223344', 'الروبوتات والذكاء الاصطناعي', 1);

-- إدخال تدريبات تجريبية
INSERT INTO `trainings` (`service_id`, `trainer_id`, `title_ar`, `title_en`, `description_ar`, `description_en`, `start_date`, `end_date`, `start_time`, `end_time`, `max_participants`) VALUES
(1, 1, 'مقدمة في تطوير الويب', 'Introduction to Web Development', 'دورة شاملة في تطوير مواقع الويب', 'Comprehensive course in web development', '2024-02-01', '2024-02-15', '09:00:00', '13:00:00', 15),
(1, 2, 'تصميم واجهات المستخدم', 'UI/UX Design', 'تعلم أساسيات تصميم واجهات المستخدم', 'Learn UI/UX design fundamentals', '2024-02-20', '2024-03-05', '14:00:00', '18:00:00', 12),
(1, 3, 'الروبوتات للمبتدئين', 'Robotics for Beginners', 'مقدمة في عالم الروبوتات', 'Introduction to robotics', '2024-03-10', '2024-03-25', '10:00:00', '14:00:00', 10);

-- إدخال أسئلة تجريبية للخدمات
INSERT INTO `service_questions` (`service_id`, `question_text_ar`, `question_text_en`, `question_type`, `is_required`, `sort_order`) VALUES
(1, 'ما هو مستوى خبرتك في البرمجة؟', 'What is your programming experience level?', 'select', 1, 1),
(1, 'ما هي لغات البرمجة التي تعرفها؟', 'What programming languages do you know?', 'checkbox', 0, 2),
(2, 'ما هو نوع الاستشارة المطلوبة؟', 'What type of consultation do you need?', 'radio', 1, 1),
(2, 'وصف المشكلة أو التحدي الذي تواجهه', 'Describe the problem or challenge you are facing', 'textarea', 1, 2),
(3, 'ما هو نوع المشروع الذي تريد العمل عليه؟', 'What type of project do you want to work on?', 'text', 1, 1),
(3, 'ما هي المعدات التي تحتاجها؟', 'What equipment do you need?', 'checkbox', 0, 2),
(4, 'ما هي مدة الحجز المطلوبة؟', 'What is the required booking duration?', 'select', 1, 1),
(4, 'عدد الأشخاص', 'Number of people', 'text', 1, 2);

-- إدخال خيارات للأسئلة
INSERT INTO `question_options` (`question_id`, `option_text_ar`, `option_text_en`, `sort_order`) VALUES
(1, 'مبتدئ', 'Beginner', 1),
(1, 'متوسط', 'Intermediate', 2),
(1, 'متقدم', 'Advanced', 3),
(2, 'Python', 'Python', 1),
(2, 'JavaScript', 'JavaScript', 2),
(2, 'Java', 'Java', 3),
(2, 'C++', 'C++', 4),
(3, 'استشارة مهنية', 'Career consultation', 1),
(3, 'استشارة تقنية', 'Technical consultation', 2),
(3, 'استشارة مشروع', 'Project consultation', 3),
(7, 'ساعة واحدة', 'One hour', 1),
(7, 'نصف يوم', 'Half day', 2),
(7, 'يوم كامل', 'Full day', 3),
(7, 'أسبوع', 'Week', 4);

-- إدخال مشرف تجريبي
INSERT INTO `admins` (`username`, `name`, `email`, `password`, `role`) VALUES
('admin', 'مدير النظام', 'admin@tiec.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'super_admin');

-- =============================================
-- إنشاء Triggers للتحديث التلقائي
-- =============================================

-- Trigger لتحديث عدد المشاركين في التدريب
DELIMITER //
CREATE TRIGGER `update_training_participants` 
AFTER INSERT ON `registrations`
FOR EACH ROW
BEGIN
    IF NEW.training_id IS NOT NULL THEN
        UPDATE `trainings` 
        SET `current_participants` = `current_participants` + 1 
        WHERE `id` = NEW.training_id;
    END IF;
END//

CREATE TRIGGER `decrease_training_participants` 
AFTER DELETE ON `registrations`
FOR EACH ROW
BEGIN
    IF OLD.training_id IS NOT NULL THEN
        UPDATE `trainings` 
        SET `current_participants` = `current_participants` - 1 
        WHERE `id` = OLD.training_id;
    END IF;
END//
DELIMITER ;

-- =============================================
-- إنشاء Views مفيدة
-- =============================================

-- View لعرض تفاصيل التسجيلات
CREATE VIEW `registration_details` AS
SELECT 
    r.id,
    p.name as participant_name,
    p.national_id,
    p.phone,
    s.name_ar as service_name,
    t.title_ar as training_title,
    tr.name as trainer_name,
    r.registration_date,
    r.status,
    r.created_at
FROM `registrations` r
JOIN `participants` p ON r.participant_id = p.id
JOIN `services` s ON r.service_id = s.id
LEFT JOIN `trainings` t ON r.training_id = t.id
LEFT JOIN `trainers` tr ON t.trainer_id = tr.id;

-- View لعرض تفاصيل الحضور
CREATE VIEW `attendance_details` AS
SELECT 
    a.id,
    p.name as participant_name,
    p.national_id,
    t.title_ar as training_title,
    tr.name as trainer_name,
    a.attendance_date,
    a.check_in_time,
    a.check_out_time,
    a.status
FROM `attendance` a
JOIN `participants` p ON a.participant_id = p.id
JOIN `trainings` t ON a.training_id = t.id
JOIN `trainers` tr ON t.trainer_id = tr.id;

-- =============================================
-- إنشاء Procedures مفيدة
-- =============================================

-- Procedure لإنشاء token فريد
DELIMITER //
CREATE PROCEDURE `generate_unique_token`(OUT token VARCHAR(64))
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE new_token VARCHAR(64);
    
    REPEAT
        SET new_token = CONCAT(
            SUBSTRING(MD5(RAND()), 1, 8), '-',
            SUBSTRING(MD5(RAND()), 1, 4), '-',
            SUBSTRING(MD5(RAND()), 1, 4), '-',
            SUBSTRING(MD5(RAND()), 1, 4), '-',
            SUBSTRING(MD5(RAND()), 1, 12)
        );
        
        SELECT COUNT(*) INTO done FROM `participants` WHERE `token` = new_token;
    UNTIL done = 0 END REPEAT;
    
    SET token = new_token;
END//
DELIMITER ;

-- =============================================
-- إنشاء Indexes إضافية للأداء
-- =============================================

-- Indexes للبحث السريع
CREATE INDEX `idx_participant_service_date` ON `registrations` (`participant_id`, `service_id`, `registration_date`);
CREATE INDEX `idx_training_date` ON `trainings` (`start_date`, `end_date`);
CREATE INDEX `idx_attendance_participant_date` ON `attendance` (`participant_id`, `attendance_date`);

-- =============================================
-- إنشاء Constraints إضافية
-- =============================================

-- التأكد من أن تاريخ التسجيل لا يكون في الماضي
ALTER TABLE `registrations` 
ADD CONSTRAINT `chk_registration_date` 
CHECK (`registration_date` >= CURDATE());

-- التأكد من أن عدد المشاركين لا يتجاوز الحد الأقصى
ALTER TABLE `trainings` 
ADD CONSTRAINT `chk_participants_limit` 
CHECK (`current_participants` <= `max_participants`);

-- =============================================
-- انتهاء إنشاء قاعدة البيانات
-- ============================================= 