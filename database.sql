-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS tiec_form CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tiec_form;

-- جدول المشاركين
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    national_id VARCHAR(20) UNIQUE NOT NULL,
    governorate VARCHAR(100) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    age INT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    whatsapp VARCHAR(20),
    participant_type ENUM('student', 'employee', 'other') NOT NULL,
    email VARCHAR(255) UNIQUE,
    university VARCHAR(255),
    education_stage VARCHAR(100),
    faculty VARCHAR(255),
    work_employer VARCHAR(255),
    support_service TEXT,
    training_confirmation BOOLEAN DEFAULT FALSE,
    qr_code VARCHAR(255),
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول المشرفين
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول المدربين
CREATE TABLE trainers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    specialization VARCHAR(255),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الخدمات
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول خيارات الخدمة
CREATE TABLE service_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    option_text_ar VARCHAR(255) NOT NULL,
    option_text_en VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- جدول أسئلة الخدمة
CREATE TABLE service_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    question_text_ar TEXT NOT NULL,
    question_text_en TEXT NOT NULL,
    question_type ENUM('text', 'select', 'radio', 'textarea') NOT NULL,
    is_required BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- جدول خيارات الأسئلة
CREATE TABLE question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text_ar VARCHAR(255) NOT NULL,
    option_text_en VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES service_questions(id) ON DELETE CASCADE
);

-- إدخال بيانات تجريبية للمشرف الافتراضي
INSERT INTO admins (username, password, name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المشرف الرئيسي', 'admin@tiec.com', 'super_admin');

-- إدخال بيانات تجريبية للخدمات
INSERT INTO services (name_ar, name_en, description_ar, description_en, sort_order) VALUES 
('خدمات تقنية المعلومات', 'IT Services', 'خدمات تقنية المعلومات والبرمجة', 'Information Technology and Programming Services', 1),
('خدمات التدريب المهني', 'Vocational Training', 'برامج التدريب المهني والتطوير', 'Vocational Training and Development Programs', 2),
('خدمات الاستشارات', 'Consulting Services', 'خدمات الاستشارات المهنية', 'Professional Consulting Services', 3);

-- إدخال بيانات تجريبية لخيارات الخدمات
INSERT INTO service_options (service_id, option_text_ar, option_text_en, sort_order) VALUES 
(1, 'تطوير الويب', 'Web Development', 1),
(1, 'تطوير تطبيقات الموبايل', 'Mobile App Development', 2),
(1, 'قواعد البيانات', 'Database Management', 3),
(2, 'تدريب البرمجة', 'Programming Training', 1),
(2, 'تدريب إدارة المشاريع', 'Project Management Training', 2),
(3, 'استشارات تقنية', 'Technical Consulting', 1),
(3, 'استشارات إدارية', 'Management Consulting', 2);

-- إدخال بيانات تجريبية لأسئلة الخدمات
INSERT INTO service_questions (service_id, question_text_ar, question_text_en, question_type, sort_order) VALUES 
(1, 'ما هو مستوى خبرتك في البرمجة؟', 'What is your programming experience level?', 'select', 1),
(1, 'ما هي التقنيات التي تفضل العمل بها؟', 'What technologies do you prefer to work with?', 'radio', 2),
(1, 'هل لديك مشاريع سابقة؟', 'Do you have previous projects?', 'text', 3),
(2, 'ما هو مجال التدريب المطلوب؟', 'What training field do you need?', 'select', 1),
(2, 'مدة التدريب المطلوبة', 'Required training duration', 'select', 2),
(3, 'نوع الاستشارة المطلوبة', 'Type of consultation needed', 'select', 1),
(3, 'تفاصيل المشكلة أو المشروع', 'Details of the problem or project', 'textarea', 2);

-- إدخال بيانات تجريبية لخيارات الأسئلة
INSERT INTO question_options (question_id, option_text_ar, option_text_en, sort_order) VALUES 
(1, 'مبتدئ', 'Beginner', 1),
(1, 'متوسط', 'Intermediate', 2),
(1, 'متقدم', 'Advanced', 3),
(2, 'JavaScript', 'JavaScript', 1),
(2, 'Python', 'Python', 2),
(2, 'PHP', 'PHP', 3),
(2, 'Java', 'Java', 4),
(4, 'برمجة', 'Programming', 1),
(4, 'إدارة مشاريع', 'Project Management', 2),
(4, 'تصميم', 'Design', 3),
(5, 'أسبوع واحد', 'One Week', 1),
(5, 'شهر واحد', 'One Month', 2),
(5, 'ثلاثة أشهر', 'Three Months', 3),
(6, 'استشارة تقنية', 'Technical Consultation', 1),
(6, 'استشارة إدارية', 'Management Consultation', 2),
(6, 'استشارة تسويقية', 'Marketing Consultation', 3); 