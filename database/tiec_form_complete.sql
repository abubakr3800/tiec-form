-- =====================================================
-- TIEC Registration System - Complete Database Setup
-- =====================================================

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS tiec_form CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE tiec_form;

-- =====================================================
-- جدول المشاركين
-- =====================================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_national_id (national_id),
    INDEX idx_email (email),
    INDEX idx_participant_type (participant_type),
    INDEX idx_governorate (governorate),
    INDEX idx_registration_date (registration_date)
);

-- =====================================================
-- جدول المشرفين
-- =====================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    role ENUM('admin', 'super_admin') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
);

-- =====================================================
-- جدول المدربين
-- =====================================================
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
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_specialization (specialization),
    INDEX idx_is_active (is_active)
);

-- =====================================================
-- جدول الخدمات
-- =====================================================
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- =====================================================
-- جدول خيارات الخدمة
-- =====================================================
CREATE TABLE service_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    option_text_ar VARCHAR(255) NOT NULL,
    option_text_en VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- =====================================================
-- جدول أسئلة الخدمة
-- =====================================================
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
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_question_type (question_type),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- =====================================================
-- جدول خيارات الأسئلة
-- =====================================================
CREATE TABLE question_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    option_text_ar VARCHAR(255) NOT NULL,
    option_text_en VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES service_questions(id) ON DELETE CASCADE,
    INDEX idx_question_id (question_id),
    INDEX idx_is_active (is_active),
    INDEX idx_sort_order (sort_order)
);

-- =====================================================
-- إدخال بيانات تجريبية للمشرف الافتراضي
-- =====================================================
INSERT INTO admins (username, password, name, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'المشرف الرئيسي', 'admin@tiec.com', 'super_admin');

-- =====================================================
-- إدخال بيانات تجريبية للخدمات
-- =====================================================
INSERT INTO services (name_ar, name_en, description_ar, description_en, sort_order) VALUES 
('خدمات تقنية المعلومات', 'IT Services', 'خدمات تقنية المعلومات والبرمجة والتطوير', 'Information Technology and Programming Development Services', 1),
('خدمات التدريب المهني', 'Vocational Training', 'برامج التدريب المهني والتطوير الوظيفي', 'Vocational Training and Career Development Programs', 2),
('خدمات الاستشارات', 'Consulting Services', 'خدمات الاستشارات المهنية والتقنية', 'Professional and Technical Consulting Services', 3),
('خدمات التصميم', 'Design Services', 'خدمات التصميم الجرافيكي والويب', 'Graphic and Web Design Services', 4),
('خدمات التسويق الرقمي', 'Digital Marketing', 'خدمات التسويق الرقمي والإعلانات', 'Digital Marketing and Advertising Services', 5);

-- =====================================================
-- إدخال بيانات تجريبية لخيارات الخدمات
-- =====================================================
INSERT INTO service_options (service_id, option_text_ar, option_text_en, sort_order) VALUES 
-- خيارات خدمات تقنية المعلومات
(1, 'تطوير الويب', 'Web Development', 1),
(1, 'تطوير تطبيقات الموبايل', 'Mobile App Development', 2),
(1, 'قواعد البيانات', 'Database Management', 3),
(1, 'أمن المعلومات', 'Information Security', 4),
(1, 'الذكاء الاصطناعي', 'Artificial Intelligence', 5),

-- خيارات خدمات التدريب المهني
(2, 'تدريب البرمجة', 'Programming Training', 1),
(2, 'تدريب إدارة المشاريع', 'Project Management Training', 2),
(2, 'تدريب المبيعات', 'Sales Training', 3),
(2, 'تدريب القيادة', 'Leadership Training', 4),
(2, 'تدريب خدمة العملاء', 'Customer Service Training', 5),

-- خيارات خدمات الاستشارات
(3, 'استشارات تقنية', 'Technical Consulting', 1),
(3, 'استشارات إدارية', 'Management Consulting', 2),
(3, 'استشارات تسويقية', 'Marketing Consulting', 3),
(3, 'استشارات مالية', 'Financial Consulting', 4),
(3, 'استشارات قانونية', 'Legal Consulting', 5),

-- خيارات خدمات التصميم
(4, 'تصميم جرافيكي', 'Graphic Design', 1),
(4, 'تصميم مواقع الويب', 'Web Design', 2),
(4, 'تصميم الشعارات', 'Logo Design', 3),
(4, 'تصميم المطبوعات', 'Print Design', 4),
(4, 'تصميم واجهات المستخدم', 'UI/UX Design', 5),

-- خيارات خدمات التسويق الرقمي
(5, 'إدارة وسائل التواصل الاجتماعي', 'Social Media Management', 1),
(5, 'تحسين محركات البحث', 'SEO Optimization', 2),
(5, 'الإعلانات المدفوعة', 'Paid Advertising', 3),
(5, 'التسويق عبر البريد الإلكتروني', 'Email Marketing', 4),
(5, 'تحليل البيانات', 'Data Analytics', 5);

-- =====================================================
-- إدخال بيانات تجريبية لأسئلة الخدمات
-- =====================================================
INSERT INTO service_questions (service_id, question_text_ar, question_text_en, question_type, sort_order) VALUES 
-- أسئلة خدمات تقنية المعلومات
(1, 'ما هو مستوى خبرتك في البرمجة؟', 'What is your programming experience level?', 'select', 1),
(1, 'ما هي التقنيات التي تفضل العمل بها؟', 'What technologies do you prefer to work with?', 'radio', 2),
(1, 'هل لديك مشاريع سابقة؟', 'Do you have previous projects?', 'text', 3),
(1, 'ما هو نوع المشروع المطلوب؟', 'What type of project do you need?', 'select', 4),
(1, 'مدة المشروع المتوقعة', 'Expected project duration', 'select', 5),

-- أسئلة خدمات التدريب المهني
(2, 'ما هو مجال التدريب المطلوب؟', 'What training field do you need?', 'select', 1),
(2, 'مدة التدريب المطلوبة', 'Required training duration', 'select', 2),
(2, 'مستوى الخبرة الحالي', 'Current experience level', 'select', 3),
(2, 'الهدف من التدريب', 'Training objective', 'textarea', 4),
(2, 'عدد المشاركين', 'Number of participants', 'select', 5),

-- أسئلة خدمات الاستشارات
(3, 'نوع الاستشارة المطلوبة', 'Type of consultation needed', 'select', 1),
(3, 'تفاصيل المشكلة أو المشروع', 'Details of the problem or project', 'textarea', 2),
(3, 'حجم الشركة أو المؤسسة', 'Company or organization size', 'select', 3),
(3, 'الميزانية المتاحة', 'Available budget', 'select', 4),
(3, 'الجدول الزمني المطلوب', 'Required timeline', 'select', 5),

-- أسئلة خدمات التصميم
(4, 'نوع التصميم المطلوب', 'Type of design needed', 'select', 1),
(4, 'وصف المشروع', 'Project description', 'textarea', 2),
(4, 'الألوان المفضلة', 'Preferred colors', 'text', 3),
(4, 'الأنماط المفضلة', 'Preferred styles', 'select', 4),
(4, 'المواد المطلوبة', 'Required materials', 'text', 5),

-- أسئلة خدمات التسويق الرقمي
(5, 'نوع خدمة التسويق المطلوبة', 'Type of marketing service needed', 'select', 1),
(5, 'المنصة المستهدفة', 'Target platform', 'select', 2),
(5, 'الميزانية الشهرية', 'Monthly budget', 'select', 3),
(5, 'الجمهور المستهدف', 'Target audience', 'textarea', 4),
(5, 'الأهداف التسويقية', 'Marketing objectives', 'textarea', 5);

-- =====================================================
-- إدخال بيانات تجريبية لخيارات الأسئلة
-- =====================================================
INSERT INTO question_options (question_id, option_text_ar, option_text_en, sort_order) VALUES 
-- خيارات سؤال مستوى الخبرة في البرمجة
(1, 'مبتدئ', 'Beginner', 1),
(1, 'متوسط', 'Intermediate', 2),
(1, 'متقدم', 'Advanced', 3),
(1, 'خبير', 'Expert', 4),

-- خيارات سؤال التقنيات المفضلة
(2, 'JavaScript', 'JavaScript', 1),
(2, 'Python', 'Python', 2),
(2, 'PHP', 'PHP', 3),
(2, 'Java', 'Java', 4),
(2, 'C#', 'C#', 5),
(2, 'React', 'React', 6),
(2, 'Vue.js', 'Vue.js', 7),
(2, 'Laravel', 'Laravel', 8),

-- خيارات سؤال نوع المشروع
(4, 'موقع ويب', 'Website', 1),
(4, 'تطبيق موبايل', 'Mobile App', 2),
(4, 'نظام إدارة', 'Management System', 3),
(4, 'تطبيق ويب', 'Web Application', 4),
(4, 'API', 'API', 5),

-- خيارات سؤال مدة المشروع
(5, 'أسبوع واحد', 'One Week', 1),
(5, 'شهر واحد', 'One Month', 2),
(5, 'ثلاثة أشهر', 'Three Months', 3),
(5, 'ستة أشهر', 'Six Months', 4),
(5, 'سنة واحدة', 'One Year', 5),

-- خيارات سؤال مجال التدريب
(6, 'برمجة', 'Programming', 1),
(6, 'إدارة مشاريع', 'Project Management', 2),
(6, 'تصميم', 'Design', 3),
(6, 'مبيعات', 'Sales', 4),
(6, 'تسويق', 'Marketing', 5),
(6, 'قانون', 'Law', 6),
(6, 'طب', 'Medicine', 7),
(6, 'هندسة', 'Engineering', 8),

-- خيارات سؤال مدة التدريب
(7, 'أسبوع واحد', 'One Week', 1),
(7, 'شهر واحد', 'One Month', 2),
(7, 'ثلاثة أشهر', 'Three Months', 3),
(7, 'ستة أشهر', 'Six Months', 4),

-- خيارات سؤال مستوى الخبرة
(8, 'مبتدئ', 'Beginner', 1),
(8, 'متوسط', 'Intermediate', 2),
(8, 'متقدم', 'Advanced', 3),

-- خيارات سؤال عدد المشاركين
(10, '1-5', '1-5', 1),
(10, '6-10', '6-10', 2),
(10, '11-20', '11-20', 3),
(10, '21-50', '21-50', 4),
(10, 'أكثر من 50', 'More than 50', 5),

-- خيارات سؤال نوع الاستشارة
(11, 'استشارة تقنية', 'Technical Consultation', 1),
(11, 'استشارة إدارية', 'Management Consultation', 2),
(11, 'استشارة تسويقية', 'Marketing Consultation', 3),
(11, 'استشارة مالية', 'Financial Consultation', 4),
(11, 'استشارة قانونية', 'Legal Consultation', 5),

-- خيارات سؤال حجم الشركة
(13, 'شركة ناشئة (1-10 موظف)', 'Startup (1-10 employees)', 1),
(13, 'شركة صغيرة (11-50 موظف)', 'Small Company (11-50 employees)', 2),
(13, 'شركة متوسطة (51-200 موظف)', 'Medium Company (51-200 employees)', 3),
(13, 'شركة كبيرة (أكثر من 200 موظف)', 'Large Company (more than 200 employees)', 4),

-- خيارات سؤال الميزانية
(14, 'أقل من 5000 جنيه', 'Less than 5000 EGP', 1),
(14, '5000-10000 جنيه', '5000-10000 EGP', 2),
(14, '10000-25000 جنيه', '10000-25000 EGP', 3),
(14, '25000-50000 جنيه', '25000-50000 EGP', 4),
(14, 'أكثر من 50000 جنيه', 'More than 50000 EGP', 5),

-- خيارات سؤال الجدول الزمني
(15, 'عاجل (أسبوع واحد)', 'Urgent (One Week)', 1),
(15, 'سريع (شهر واحد)', 'Fast (One Month)', 2),
(15, 'عادي (3 أشهر)', 'Normal (3 Months)', 3),
(15, 'مرن (6 أشهر)', 'Flexible (6 Months)', 4),

-- خيارات سؤال نوع التصميم
(16, 'شعار', 'Logo', 1),
(16, 'هوية بصرية', 'Visual Identity', 2),
(16, 'موقع ويب', 'Website', 3),
(16, 'تطبيق موبايل', 'Mobile App', 4),
(16, 'مطبوعات', 'Print Materials', 5),
(16, 'إعلانات', 'Advertisements', 6),

-- خيارات سؤال الأنماط المفضلة
(19, 'حديث', 'Modern', 1),
(19, 'كلاسيكي', 'Classic', 2),
(19, 'بسيط', 'Minimalist', 3),
(19, 'ملون', 'Colorful', 4),
(19, 'احترافي', 'Professional', 5),
(19, 'إبداعي', 'Creative', 6),

-- خيارات سؤال نوع خدمة التسويق
(21, 'إدارة وسائل التواصل الاجتماعي', 'Social Media Management', 1),
(21, 'تحسين محركات البحث', 'SEO Optimization', 2),
(21, 'الإعلانات المدفوعة', 'Paid Advertising', 3),
(21, 'التسويق عبر البريد الإلكتروني', 'Email Marketing', 4),
(21, 'تحليل البيانات', 'Data Analytics', 5),

-- خيارات سؤال المنصة المستهدفة
(22, 'فيسبوك', 'Facebook', 1),
(22, 'إنستغرام', 'Instagram', 2),
(22, 'تويتر', 'Twitter', 3),
(22, 'لينكد إن', 'LinkedIn', 4),
(22, 'يوتيوب', 'YouTube', 5),
(22, 'تيك توك', 'TikTok', 6),
(22, 'جوجل', 'Google', 7),

-- خيارات سؤال الميزانية الشهرية
(23, 'أقل من 1000 جنيه', 'Less than 1000 EGP', 1),
(23, '1000-3000 جنيه', '1000-3000 EGP', 2),
(23, '3000-5000 جنيه', '3000-5000 EGP', 3),
(23, '5000-10000 جنيه', '5000-10000 EGP', 4),
(23, 'أكثر من 10000 جنيه', 'More than 10000 EGP', 5);

-- =====================================================
-- إدخال بيانات تجريبية للمدربين
-- =====================================================
INSERT INTO trainers (username, password, name, email, specialization, phone, is_active) VALUES 
('trainer1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'أحمد محمد', 'ahmed@tiec.com', 'برمجة', '01012345678', 1),
('trainer2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'فاطمة علي', 'fatima@tiec.com', 'تصميم', '01087654321', 1),
('trainer3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'محمد حسن', 'mohamed@tiec.com', 'إدارة مشاريع', '01011223344', 1),
('trainer4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'سارة أحمد', 'sara@tiec.com', 'تسويق', '01055667788', 1),
('trainer5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'علي محمود', 'ali@tiec.com', 'قانون', '01099887766', 1);

-- =====================================================
-- إدخال بيانات تجريبية للمشاركين
-- =====================================================
INSERT INTO participants (name, national_id, governorate, gender, age, phone, whatsapp, participant_type, email, university, education_stage, faculty, work_employer, support_service, training_confirmation, qr_code) VALUES 
('أحمد محمد علي', '12345678901234', 'القاهرة', 'male', 25, '01012345678', '01012345678', 'student', 'ahmed@example.com', 'جامعة القاهرة', 'خريج', 'كلية الحاسبات والمعلومات', NULL, 'تطوير الويب', 1, 'QR_AHMED_001'),
('فاطمة أحمد محمد', '23456789012345', 'الإسكندرية', 'female', 22, '01023456789', '01023456789', 'student', 'fatima@example.com', 'جامعة الإسكندرية', 'طالب', 'كلية التجارة', NULL, 'تصميم جرافيكي', 1, 'QR_FATIMA_002'),
('محمد حسن أحمد', '34567890123456', 'الجيزة', 'male', 30, '01034567890', '01034567890', 'employee', 'mohamed@example.com', NULL, NULL, NULL, 'شركة التقنية المتقدمة', 'إدارة مشاريع', 1, 'QR_MOHAMED_003'),
('سارة محمود علي', '45678901234567', 'الشرقية', 'female', 28, '01045678901', '01045678901', 'employee', 'sara@example.com', NULL, NULL, NULL, 'مؤسسة التنمية', 'تسويق رقمي', 1, 'QR_SARA_004'),
('علي محمد حسن', '56789012345678', 'الغربية', 'male', 35, '01056789012', '01056789012', 'other', 'ali@example.com', NULL, NULL, NULL, NULL, 'استشارات تقنية', 0, 'QR_ALI_005'),
('نور أحمد محمد', '67890123456789', 'المنوفية', 'female', 24, '01067890123', '01067890123', 'student', 'nour@example.com', 'جامعة المنوفية', 'طالب', 'كلية الآداب', NULL, 'تصميم مواقع', 1, 'QR_NOUR_006'),
('حسن علي محمود', '78901234567890', 'القليوبية', 'male', 27, '01078901234', '01078901234', 'employee', 'hassan@example.com', NULL, NULL, NULL, 'شركة البرمجيات', 'تطوير تطبيقات', 1, 'QR_HASSAN_007'),
('مريم أحمد علي', '89012345678901', 'البحيرة', 'female', 26, '01089012345', '01089012345', 'student', 'mariam@example.com', 'جامعة دمنهور', 'طالب', 'كلية العلوم', NULL, 'قواعد البيانات', 1, 'QR_MARIAM_008'),
('يوسف محمد حسن', '90123456789012', 'كفر الشيخ', 'male', 32, '01090123456', '01090123456', 'employee', 'youssef@example.com', NULL, NULL, NULL, 'شركة الاتصالات', 'أمن المعلومات', 1, 'QR_YOUSSEF_009'),
('هدى محمود أحمد', '01234567890123', 'دمياط', 'female', 29, '01001234567', '01001234567', 'other', 'hoda@example.com', NULL, NULL, NULL, NULL, 'استشارات إدارية', 0, 'QR_HODA_010');

-- =====================================================
-- إنشاء مستخدم مشرف إضافي
-- =====================================================
INSERT INTO admins (username, password, name, email, role) VALUES 
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام', 'manager@tiec.com', 'admin');

-- =====================================================
-- رسالة نجاح الاستيراد
-- =====================================================
SELECT 'تم إنشاء قاعدة البيانات بنجاح!' AS message;
SELECT 'بيانات الدخول الافتراضية:' AS login_info;
SELECT 'المشرف الرئيسي - admin/admin' AS admin_login;
SELECT 'المدير - manager/admin' AS manager_login;
SELECT 'المدربين - trainer1/admin, trainer2/admin, إلخ' AS trainer_login; 